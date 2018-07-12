<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/13
 * Time: 上午11:18
 */
namespace app\services;

use app\helpers\Message;
use app\helpers\MyRedis;
use app\models\ActivityProducts;
use app\models\Coupon;
use app\models\CouponCode;
use app\models\PaymentOrderDistribution;
use app\models\PaymentOrderItemDistribution;
use app\models\PkCurrentPeriod;
use app\models\PkLotteryCompute;
use app\models\PkOrders;
use app\models\PkPaymentOrderItemDistribution;
use app\models\PkPeriod as PkPeriodModel;
use app\models\PkPeriodBuylistDistribution;
use app\models\User as UserModel;
use app\models\UserCoupons;
use yii\helpers\ArrayHelper;

class PkLottery
{

    const PK_PRODUCT_LOTTERY_TIME_KEY = 'PK_PRODUCT_LOTTERY_TIME_KEY';//开奖时间 hash
    const PK_PRODUCT_LOTTERY_LOCK_KEY = 'PK_PRODUCT_LOTTERY_LOCK_KEY';//期数开奖lock

    /** 开奖
     * @param $periodInfo
     * @param bool $output
     */
    public static function draw($periodInfo, $output = true)
    {
        if (!$periodInfo) {
            $logStr = 'periodInfo为空';
            static::printLog($logStr, $output);
            return false;
        }
        $logStr = 'periodId:' . $periodInfo['id'] . '  开奖开始';
        static::printLog($logStr, $output);

        $lotteryCompute = PkLotteryCompute::findByTableId($periodInfo['table_id'])->select('data')->where(['period_id' => $periodInfo['id']])->one();
        if ($lotteryCompute) {
            $total = 0;
            $compute_data = unserialize($lotteryCompute['data']);
            foreach ($compute_data as $k => $v) {
                $time = explode(".", $v['buy_time']);
                $lastTime = isset($time[1]) ? substr($time[1], 0, 3) : '0';
                $timeData = date("His", $time[0]) . str_pad($lastTime, 3, 0, STR_PAD_RIGHT);
                $total += $timeData;
            }
        } else {
            $endTime = $periodInfo['end_time'];
            $lastBuy = static::lastBuy($endTime, 50);
            $total = 0;
            $compute_data = [];
            foreach ($lastBuy as $k => $v) {
                $time = explode(".", $v['item_buy_time']);
                $lastTime = isset($time[1]) ? substr($time[1], 0, 3) : '0';
                $timeData = date("His", $time[0]) . str_pad($lastTime, 3, 0, STR_PAD_RIGHT);
                $compute_data[] = array(
                    'buy_time' => $v['item_buy_time'],
                    'data' => $timeData,
                    'user_id' => $v['user_id'],
                    'buy_num' => $v['nums'],
                    'product_id' => $v['product_id'],
                    'period_id' => $v['period_id'],
                    'lastbuy_type' => $v['lastbuy_type'],
                );
                $total += $timeData;
            }
        }

        if (!$lotteryCompute) {
            $ld = new PkLotteryCompute($periodInfo['table_id']);
            $ld->period_id = $periodInfo['id'];
            $ld->data = serialize($compute_data);
            $l = $ld->save();
        }

        $luckyCode = $total % $periodInfo['price'] + 10000001;

        $logStr = 'periodId:' . $periodInfo['id'] . '  幸运码:' . $luckyCode;
        static::printLog($logStr, $output);

        $half = $periodInfo['price'] / 2 + 10000001 - 1;
        $luckySize = $luckyCode <= $half ? PkCurrentPeriod::BUY_SIZE_SMALL : PkCurrentPeriod::BUY_SIZE_BIG;
        $matchInfo = static::matchInfo($periodInfo);
        $matchNum = $matchInfo['match_num'];
        if ($luckySize == PkCurrentPeriod::BUY_SIZE_BIG) {
            $luckyMatchList = $matchInfo['match_big_list'];
            $noLuckyMatchList = $matchInfo['match_small_list'];
        } else {
            $luckyMatchList = $matchInfo['match_small_list'];
            $noLuckyMatchList = $matchInfo['match_big_list'];
        }
        $notMathList = $matchInfo['not_match_list'];
        $startNew = static::newPeriod($periodInfo, $luckyCode, $luckySize, $matchNum);
        if ($startNew) {
            $logStr = 'periodId:' . $periodInfo['id'] . '  开启新的一期成功';
            static::printLog($logStr, $output);
            static::awardOrders($periodInfo, $luckyMatchList);
            static::backMoney($periodInfo, $notMathList, $luckyMatchList, $noLuckyMatchList);
            static::addPointLog($periodInfo, $matchInfo['match_big_list'], $matchInfo['match_small_list']);
            static::jdActivity($periodInfo, $luckyMatchList, $noLuckyMatchList);
            return true;
        } else {
            $logStr = 'periodId:' . $periodInfo['id'] . '  开启新的一期失败';
            static::printLog($logStr, $output);
            return false;
        }
    }

    /** 中奖订单写入
     * @param $periodInfo
     * @param $luckyMatchList
     */
    public static function awardOrders($periodInfo, $luckyMatchList)
    {
        if (!$luckyMatchList) {
            $logStr = 'periodId:' . $periodInfo['id'] . ' 没有成功的匹配';
            static::printLog($logStr);
            return;
        }
        foreach ($luckyMatchList as $key => $one) {
            $userId = $one['user_id'];
            $buyTable = $one['buy_table'];
            $periodId = $one['period_id'];
            $productId = $one['product_id'];
            $buySize = $one['buy_size'];
            $lotteryOrder = new PkOrders();
            $lotteryOrder->product_id = $productId;
            $lotteryOrder->period_id = $periodId;
            $lotteryOrder->user_id = $userId;
            $lotteryOrder->price = $periodInfo['price'];
            $lotteryOrder->size = $buySize;
            $lotteryOrder->desk_id = $buyTable;
            $lotteryOrder->create_time = time();
            $lotteryOrder->last_modified = time();
            $saveOrder = $lotteryOrder->save(false);

            $listNum = $key + 1;
            if (!$saveOrder) {
                $logStr = 'periodId:' . $periodInfo['id'] . '  第' . $listNum . '位中奖用户订单写入失败';
                static::printLog($logStr);
                continue;
            }
            $oid = $lotteryOrder->id;//中奖订单ID

            $userInfo = User::baseInfo($userId);

            $logStr = 'periodId:' . $periodInfo['id'] . '  第' . $listNum . '位中奖用户:' . $userInfo['username'];
            static::printLog($logStr);

            $productInfo = PkProduct::info($productId);
            if (isset($productInfo['delivery_id']) && $productInfo['delivery_id'] == 3) {
                $sendMsgType = 38;
            } elseif (isset($productInfo['delivery_id']) && $productInfo['delivery_id'] == 8) {
                if ($userInfo['from'] == 2) {
                    $sendMsgType = 46;
                } else {
                    $sendMsgType = 45;
                }
            } else {
                $sendMsgType = 11;
            }
            $periodInfo = PkPeriodModel::findOne($periodId);
            Message::send($sendMsgType, $userInfo['id'], array('nickname' => $userInfo['username'], 'phone' => User::privatePhone($userInfo['phone']), 'periodNumber' => $periodInfo['period_no'], 'goodsName' => $productInfo['name'], 'time' => '[time]', 'goodsId' => $productId, 'orderNo' => $oid));
        }
    }

    /** 匹配失败的退回用户账户
     * @param $periodInfo
     * @param $notMatchList
     */
    public static function backMoney($periodInfo, $notMatchList, $luckyMatchList, $noLuckyMatchList)
    {
        if (!$notMatchList) {
            $logStr = 'periodId:' . $periodInfo['id'] . ' period_no:' . $periodInfo['period_no'] . ' 没有匹配失败的';
            static::printLog($logStr);
            return;
        }
        $oneTablePrice = ceil($periodInfo['price'] / 2);

        $usersRealMoney = [];
        if ($luckyMatchList || $noLuckyMatchList) {
            $allMatchList = array_merge($luckyMatchList, $noLuckyMatchList);
            foreach ($allMatchList as $matchInfo) {
                if (!isset($usersRealMoney[$matchInfo['user_id']])) {
                    $usersRealMoney[$matchInfo['user_id']] = $oneTablePrice;
                } else {
                    $usersRealMoney[$matchInfo['user_id']] += $oneTablePrice;
                }
            }
        }

        $usersBackMoney = [];
        foreach ($notMatchList as $key => $one) {
            $userId = $one['user_id'];
            if (!isset($usersBackMoney[$userId])) {
                $usersBackMoney[$userId] = $oneTablePrice;
            } else {
                $usersBackMoney[$userId] += $oneTablePrice;
            }
        }

        foreach ($usersBackMoney as $userId => $backMoney) {
            $userInfo = UserModel::find()->select('home_id')->where(['id' => $userId])->asArray()->one();
            $userPaymentOrderItems = PkPaymentOrderItemDistribution::findByTableId($userInfo['home_id'])->select('payment_order_id,nums')->where(['user_id' => $userId, 'period_id' => $periodInfo['id']])->asArray()->all();
            if (!$userPaymentOrderItems) {
                continue;
            }
            $paymentOrderIdsAndNums = [];
            foreach ($userPaymentOrderItems as $one) {
                $paymentOrderIdsAndNums[$one['payment_order_id']] = $one['nums'];
            }
            $paymentOrderIds = $paymentOrderIdsAndNums ? array_keys($paymentOrderIdsAndNums) : [];
            $userPaymentOrders = PaymentOrderDistribution::findByTableId($userInfo['home_id'])->select('id,deduction1,coupon1,deduction2,coupon2')->where(['id' => $paymentOrderIds, 'status' => 1])->orderBy('create_time asc')->asArray()->all();


            $backCouponIds = [];
            $backCouponDeduction = 0;
            $curPaymentPrice = 0;
            foreach ($userPaymentOrders as $onePaymentOrder) {
                $userCoupons = [];
                $couponOne = $onePaymentOrder['coupon1'];
                $couponTwo = $onePaymentOrder['coupon2'];
                if ($couponOne) {
                    $couponsCode = CouponCode::find()->select('coupon_id')->where(['code' => $couponOne])->asArray()->one();
                    $couponInfo = Coupon::getInfo($couponsCode['coupon_id']);
                    $condition = json_decode($couponInfo['condition'], true);
                    $userCoupons[] = [
                        'coupon_id' => $couponInfo['id'],
                        'need' => $condition['need'],
                        'deduction' => $onePaymentOrder['deduction1'],
                    ];
                }
                if ($couponTwo) {
                    $couponsCode = CouponCode::find()->select('coupon_id')->where(['code' => $couponTwo])->asArray()->one();
                    $couponInfo = Coupon::getInfo($couponsCode['coupon_id']);
                    $condition = json_decode($couponInfo['condition'], true);
                    $userCoupons[] = [
                        'coupon_id' => $couponInfo['id'],
                        'need' => $condition['need'],
                        'deduction' => $onePaymentOrder['deduction2'],
                    ];
                }
                $thisPaymentPrice = $paymentOrderIdsAndNums[$onePaymentOrder['id']] * $oneTablePrice;
                $curPaymentPrice += $thisPaymentPrice;
                $needMoney = 0;
                if ($userCoupons) {
                    foreach ($userCoupons as $ucKey => $userCoupon) {
                        if ($ucKey == 0) {
                            $needMoney = $userCoupon['need'];
                        } else{
                            $needMoney += $userCoupon['need'];
                        }

                        if (isset($usersRealMoney[$userId])
                            && $curPaymentPrice >= $usersRealMoney[$userId]
                            && ($thisPaymentPrice - ($curPaymentPrice - $usersRealMoney[$userId])) >= $needMoney) {
                            //如果之前订单金额加当前订单金额大于匹配的支付金额
                        } elseif (isset($usersRealMoney[$userId])
                            && $curPaymentPrice < $usersRealMoney[$userId]
                            && ($thisPaymentPrice  >= $needMoney)) {
                            //如果之前订单金额加当前订单金额小于匹配的支付金额
                        }  else {
                            $backCouponIds[] = $userCoupon['coupon_id'];
                            $backCouponDeduction += $userCoupon['deduction'];

                            $coupon = UserCoupons::findByUserId($userId)->where(['user_id' => $userId, 'coupon_id' => $userCoupon['coupon_id']])->orderBy('receive_time desc')->asArray()->one();
                            if ($coupon) {
                                $packetId = $coupon['packet_id'];
                                $rs = \app\services\Coupon::receivePacket($packetId, $userId, 'PK商品退回');
                                $logStr = "userId:{$userId} periodId:{$periodInfo['id']} 退回红包packetId:" . $packetId;
                                static::printLog($logStr);
                                if ($rs['code'] == '0') {
                                    $pid = $rs['data']['pid'];
                                    \app\services\Coupon::openPacket($pid, $userId);
                                }
                            }
                        }
                    }

                }
            }


            $backMoney -= $backCouponDeduction;
            $backPoint = $backMoney * 100;
            $productInfo = PkProduct::info($periodInfo['product_id']);
            $member = new Member(['id' => $userId]);
            $edit = $member->editPoint($backPoint, 11, 'PK商品编码('.$productInfo['bn'].')匹配失败退回');
            if ($edit) {
                $logStr = 'periodId:' . $periodInfo['id'] . ' 匹配失败退回用户 userId:' . $userId . ' 退回积分:' . $backPoint;
                static::printLog($logStr);
            }
        }
    }

    /** 积分经验发放
     * @param $matchBigList
     * @param $matchSmallList
     */
    public static function addPointLog($periodInfo, $matchBigList, $matchSmallList)
    {
        if (!$matchBigList && !$matchSmallList) {
            $logStr = 'periodId:' . $periodInfo['id'] . ' 没有匹配成功的';
            static::printLog($logStr);
            return;
        }
        $productInfo = PkProduct::info($periodInfo['product_id']);
        $oneTablePrice = ceil($periodInfo['price'] / 2);
        $twoSideList = array_merge($matchBigList, $matchSmallList);
        $userConsumeMoney = [];
        foreach ($twoSideList as $key => $one) {
            $userId = $one['user_id'];
            if (!isset($userConsumeMoney[$userId])) {
                $userConsumeMoney[$userId] = $oneTablePrice;
            } else {
                $userConsumeMoney[$userId] += $oneTablePrice;
            }
        }
        foreach ($userConsumeMoney as $userId => $consumeMoney) {
            $point = $consumeMoney;
            $exp = $consumeMoney * 10;
            $member = new Member(['id' => $userId]);
            $editPoint = $member->editPoint($point, 11, 'PK商品编码(' . $productInfo['bn'] . ')支付' . $consumeMoney . '元获得' . $point . '福分');
            $editExp = $member->editExperience($exp, 11, '购买PK商品');
            if ($editPoint && $editExp) {
                $logStr = 'periodId:' . $periodInfo['id'] . ' userId:' . $userId . ' 用户发放的积分:' . $point . ' 经验:' . $exp;
                static::printLog($logStr);
            }
        }
    }

    /** 开始新一期
     * @param $periodInfo
     * @param $luckyCode
     * @param $luckySize
     * @param $matchNum
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function newPeriod($periodInfo, $luckyCode, $luckySize, $matchNum)
    {
        $trans = \Yii::$app->db->beginTransaction();
        $completePeriod = new PkPeriodModel();
        $completePeriod->id = $periodInfo['id'];
        $completePeriod->table_id = $periodInfo['table_id'];
        $completePeriod->product_id = $periodInfo['product_id'];
        $completePeriod->period_number = $periodInfo['period_number'];
        $completePeriod->period_no = $periodInfo['period_no'];
        $completePeriod->lucky_code = $luckyCode;
        $completePeriod->price = $periodInfo['price'];
        $completePeriod->start_time = (string)$periodInfo['start_time'];
        $completePeriod->end_time = $periodInfo['end_time'];
        $completePeriod->exciting_time = (string)microtime(true);
        $completePeriod->size = $luckySize;
        $completePeriod->match_num = $matchNum;
        $result = $completePeriod->save(false);

        if (!$result) {
            $trans->rollBack();
            return false;
        }
        $delete = PkCurrentPeriod::deleteAll(['id' => $periodInfo['id']]);
        if (!$delete) {
            $trans->rollBack();
            return false;
        }
        $startTime = time();
        $productInfo = PkProduct::info($periodInfo['product_id']);
        $periodNumber = $periodInfo['period_number'] + 1;

        if ($productInfo['marketable'] == '0') {
            $trans->commit();
            $logStr = 'productId:' . $periodInfo['product_id'] . ' 已被下架';
            static::printLog($logStr);
            return false;
        }
        if ($periodNumber > $productInfo['store']) {
            ActivityProducts::updateAll(['marketable' => 0], ['id' => $productInfo['id']]);
            $trans->commit();
            $logStr = 'productId:' . $periodInfo['product_id'] . ' 已到达总期数';
            static::printLog($logStr);
            return false;
        }
        $endTime = $startTime + $productInfo['left_time'] * 60;
        $newCurPeriod = new PkCurrentPeriod();
        $newCurPeriod->product_id = $periodInfo['product_id'];
        $newCurPeriod->table_id = mt_rand(100, 109);
        $newCurPeriod->period_number = $periodNumber;
        $newCurPeriod->price = $productInfo['price'];
        $newCurPeriod->start_time = $startTime;
        $newCurPeriod->end_time = $endTime;
        $newCurPeriodSave = $newCurPeriod->save(false);
        if (!$newCurPeriodSave) {
            $trans->rollBack();
            return false;
        }
        $newCurPeriod->period_no = PkPeriod::getPeriodNo(ArrayHelper::toArray($newCurPeriod));
        $newCurPeriod->save(false);
        $trans->commit();
        $curPeriodId = $newCurPeriod->id;
        $redis = new MyRedis();
        $redis->hset(static::PK_PRODUCT_LOTTERY_TIME_KEY, [$curPeriodId => $endTime]);
        return true;
    }

    /** 匹配信息
     * @param $periodInfo
     * @return array
     */
    public static function matchInfo($periodInfo)
    {
        $periodId = $periodInfo['id'];
        $tableId = $periodInfo['table_id'];
        $query = PkPeriodBuylistDistribution::findByTableId($tableId)->where(['period_id' => $periodId]);
        $query->select([
            'id',
            'product_id',
            'period_id',
            'user_id',
            'buy_size',
            'buy_table',
            'ip',
            'source',
            'buy_time'
        ]);
        $orderBy = 'id asc';

        $bigQuery = clone $query;
        $smallQuery = clone $query;
        $bigQuery->andWhere(['buy_size' => PkCurrentPeriod::BUY_SIZE_BIG]);
        $smallQuery->andWhere(['buy_size' => PkCurrentPeriod::BUY_SIZE_SMALL]);

        $bigCountQuery = clone $bigQuery;
        $bigTotalCount = $bigCountQuery->count();

        $smallCountQuery = clone $smallQuery;
        $smallTotalCount = $smallCountQuery->count();

        $bigNotMathQuery = clone $bigQuery;
        $smallNotMathQuery = clone $smallQuery;

        if ($bigTotalCount > $smallTotalCount) {
            $matchNum = $smallTotalCount;
            if ($smallTotalCount > 0) {
                $bigMatchResult = $bigQuery->orderBy($orderBy)->limit($smallTotalCount)->asArray()->all();
                $smallMatchResult = $smallQuery->orderBy($orderBy)->asArray()->all();
                $notMathLimit = $bigTotalCount - $smallTotalCount;
                $notMathList = $bigNotMathQuery->orderBy($orderBy)->offset($smallTotalCount)->limit($notMathLimit)->asArray()->all();
            } else {
                $bigMatchResult = [];
                $smallMatchResult = [];
                $notMathList = $bigNotMathQuery->orderBy($orderBy)->asArray()->all();
            }
        } else {
            $matchNum = $bigTotalCount;
            if ($bigTotalCount > 0) {
                $bigMatchResult = $bigQuery->orderBy($orderBy)->asArray()->all();
                $smallMatchResult = $smallQuery->orderBy($orderBy)->limit($bigTotalCount)->asArray()->all();
                $notMathLimit = $smallTotalCount - $bigTotalCount;
                $notMathList = $smallNotMathQuery->orderBy($orderBy)->offset($bigTotalCount)->limit($notMathLimit)->asArray()->all();
            } else {
                $bigMatchResult = [];
                $smallMatchResult = [];
                $notMathList = $smallNotMathQuery->orderBy($orderBy)->asArray()->all();
            }
        }

        $info = [];
        $info['match_num'] = $matchNum;
        $info['match_big_list'] = $bigMatchResult;
        $info['match_small_list'] = $smallMatchResult;
        $info['not_match_list'] = $notMathList;

        return $info;
    }

    /** 打印开奖log
     * @param $log
     * @param bool $output
     */
    public static function printLog($log, $output = true)
    {
        $subStr = '---' . date('Y-m-d H:i:s') . PHP_EOL;
        if ($output) {
            if (is_array($log)) {
                echo PHP_EOL . "========================================" . PHP_EOL;
                foreach ($log as $v) {
                    echo $v . $subStr;
                }
                echo PHP_EOL . "========================================" . PHP_EOL;
            } else {
                echo PHP_EOL . "========================================" . PHP_EOL;
                echo $log . $subStr;
                echo PHP_EOL . "========================================" . PHP_EOL;
            }
        } else {
            @file_put_contents('/tmp/pk.lottery.draw.log', $log . $subStr, FILE_APPEND);
        }
    }

    /**最后购买记录
     * @param $endTime
     * @param int $num
     * @return array
     */
    public static function lastBuy($endTime, $num = 50)
    {
        $yyLastBuy = PaymentOrderItemDistribution::lastBuy($endTime, $num);
        $pkLastBuy = PkPaymentOrderItemDistribution::lastBuy($endTime, $num);
        foreach ($yyLastBuy as &$yyVal) {
            $yyVal['lastbuy_type'] = 'yy';
        }
        foreach ($pkLastBuy as &$pkVal) {
            $pkVal['lastbuy_type'] = 'pk';
        }
        $resultArr = array_merge($yyLastBuy, $pkLastBuy);
        ArrayHelper::multisort($resultArr, 'item_buy_time', SORT_DESC);
        return array_slice($resultArr, 0, 50);
    }

    /** 京东卡活动
     * @param $periodInfo
     * @param $luckyMatchList
     * @param $noLuckyMatchList
     */
    public static function jdActivity($periodInfo, $luckyMatchList, $noLuckyMatchList)
    {
        try {
            $oneTablePrice = ceil($periodInfo['price'] / 2);
            foreach ($luckyMatchList as $matchInfo) {
                JdcardActivity::JdcardRed($oneTablePrice, $periodInfo['product_id'], $matchInfo['user_id'], true);
            }
            foreach ($noLuckyMatchList as $matchInfo) {
                JdcardActivity::JdcardRed($oneTablePrice, $periodInfo['product_id'], $matchInfo['user_id'], false);
            }
        } catch(\Exception $e) {
            $logStr = 'periodId:' . $periodInfo['id'] . ' period_no:' . $periodInfo['period_no'] . ' 京东卡活动写入出错' . $e->getFile(). ' line:' . $e->getLine() . ' msg:' . $e->getMessage();
            static::printLog($logStr);
        }
    }


}