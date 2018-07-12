<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/5/23
 * Time: 12:52
 */
namespace app\services;

use app\helpers\Message;
use app\models\LotteryComputeDistribution;
use app\models\Order;
use app\models\PaymentOrderItemDistribution;
use app\models\Period as PeriodModel;
use app\models\PeriodBuylistDistribution;
use Yii;
use app\models\UserBuylistDistribution;

class Lottery
{

    /** 开奖方法
     * @param $period
     * @param bool $output
     * @throws \yii\db\Exception
     */
    public static function draw($period, $canUseNone = true, $output = true)
    {
        $end_time = $period['end_time'];
        $lotteryCompute = LotteryComputeDistribution::findByTableId($period['table_id'])->select('data,expect')->where(['period_id' => $period['id']])->asArray()->one();
        if ($lotteryCompute) {
            $total = 0;
            $compute_data = unserialize($lotteryCompute['data']);
            foreach ($compute_data as $k => $v) {
                $time = explode(".", $v['buy_time']);
                $lastTime = isset($time[1]) ? substr($time[1], 0, 3) : '0';
                $timeData = date("His", $time[0]) . str_pad($lastTime, 3, 0, STR_PAD_RIGHT);
                $total += $timeData;
            }
            $shishiQishu = $lotteryCompute['expect'];
            if (!$shishiQishu) {
                $shishiQishu = \app\services\Period::shishiQishu($end_time);
            }
        } else {
            $lastBuy = PaymentOrderItemDistribution::lastBuy($end_time, 50);
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
                    'period_number' => $v['period_number']
                );
                $total += $timeData;
            }
            $shishiQishu = \app\services\Period::shishiQishu($end_time);
        }


        $logStr = $shishiQishu . "期彩票!";
        static::printLog($logStr, $output);

        $shishiNum = \app\services\Period::shishiNum($shishiQishu);

        $logStr = $shishiQishu . "期彩票开奖号码:" . $shishiNum;
        static::printLog($logStr, $output);

        if (!$shishiNum) {
            if ($canUseNone) {
                $shishiNum = '00000';
                $logStr = $shishiQishu . "期彩票开奖数据不能获取!使用默认'00000'";
                static::printLog($logStr, $output);
            } else {
                $logStr = $shishiQishu . "期彩票开奖数据不能获取!";
                static::printLog($logStr, $output);
                return false;
            }
        }

        if ($lotteryCompute) {
            $l = LotteryComputeDistribution::updateAllByTableId($period['table_id'], ['expect' => $shishiQishu, 'shishi_num' => $shishiNum], ['period_id' => $period['id']]);
        } else {
            $ld = new LotteryComputeDistribution($period['table_id']);
            $ld->period_id = $period['id'];
            $ld->data = serialize($compute_data);
            $ld->expect = $shishiQishu;
            $ld->shishi_num = $shishiNum;
            $l = $ld->save(false);
        }

        $finalTotal = $total + $shishiNum;

        if ($output) {

            $logStr = [];
            $logStr[] = "productId:" . $period['product_id'] . ' -- periodId:' . $period['id'];
            $logStr[] = "总值：" . $total . "+" . $shishiNum . "=" . $finalTotal;
            static::printLog($logStr, $output);
        }

        $lucky_code = $finalTotal % $period['price'] + 10000001;

        $logStr = "中奖号码：" . $lucky_code;
        static::printLog($logStr, $output);

        $table_id = $period['table_id'];
        $buyer = PeriodBuylistDistribution::findByTableId($table_id)->select('user_id,ip')->where(['and', "period_id='" . $period['id'] . "'", ['like', 'codes', $lucky_code]])->asArray()->one();
        $uid = $buyer['user_id'];
        $ip = $buyer['ip'];

        $logStr = '中奖用户ID:' . $uid;
        static::printLog($logStr, $output);

        if ($uid > 0) {
            $userInfo = User::baseInfo($uid);

            $orderInfo = PaymentOrderItemDistribution::findByTableId(substr($userInfo['home_id'], 0, 3))->select('payment_order_id')->where(['and', "period_id='" . $period['id'] . "'", ['like', 'codes', $lucky_code]])->asArray()->one();
            $orderId = $orderInfo['payment_order_id'];

            $logStr = '支付订单:' . $orderId;
            static::printLog($logStr, $output);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $updateLottery = PeriodModel::updateAll(['lucky_code' => $lucky_code, 'ip' => $ip, 'user_id' => $uid, 'exciting_time' => microtime(true), 'result_time'=>Period::raffTime($end_time)], ['id' => $period['id']]);

                $lotteryOrder = new Order();
                $lotteryOrder->order_no = $orderId;
                $lotteryOrder->product_id = $period['product_id'];
                $lotteryOrder->period_id = $period['id'];
                $lotteryOrder->user_id = $uid;
                $lotteryOrder->price = $period['price'];
                $lotteryOrder->create_time = time();
                $lotteryOrder->last_modified = time();
                $saveOrder = $lotteryOrder->save(false);
                $oid = $lotteryOrder->attributes['id'];

                $logStr = '中奖订单ID:' . $oid;
                static::printLog($logStr, $output);

                if ($updateLottery && $saveOrder) {
                    $transaction->commit();

                    $logStr = 'periodId:' . $period['id'] . ' 开奖成功';
                    static::printLog($logStr, $output);

                    //经验为0的用户冻结
                    if ($userInfo['experience'] <= 0) {
                        \app\models\User::updateAll(['status' => 1], ['id' => $userInfo['id']]);
                    } else {
                        $productInfo = Product::info($period['product_id']);
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
                        $resultTime = $period['result_time'];
                        $afterSendTime = \app\services\Period::leftTime($resultTime) + \app\services\Period::COUNT_DOWN_FAULT_BIT_TIME;
                        Message::send($sendMsgType, $userInfo['id'], array('nickname' => $userInfo['username'], 'phone' => User::privatePhone($userInfo['phone']), 'periodNumber' => $period['period_no'], 'goodsName' => $productInfo['name'], 'time' => '[time]', 'goodsId' => $period['product_id'], 'orderNo' => $oid), $afterSendTime);
                    }
                    try {
                        Olympic::addUserRank($uid, $period['product_id']);
                    } catch(\Exception $e) {
                        $logStr = 'peirodId:' . $period['id'] . '---' . '奥运会----' . $e->getMessage() . '----' . $e->getLine();
                        static::printLog($logStr, $output);
                    }

                    return true;
                } else {
                    $logStr = 'periodId:' . $period['id'] . ' 开奖失败';
                    static::printLog($logStr, $output);
                    return false;
                }
            } catch (\Exception $e) {
                $transaction->rollback();//如果操作失败, 数据回滚
                $logStr = 'peirodId:' . $period['id'] . '---' . '开奖失败----' . $e->getMessage() . '----' . $e->getLine();

                static::printLog($logStr, $output);
                return false;
            }
        }

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
            @file_put_contents('/tmp/lottery.draw.log', $log . $subStr, FILE_APPEND);
        }
    }

    /**
     * 发放红包
     * @param  int $productId 商品ID
     * @return [type]            [description]
     */
    private static function grantCoupon($userInfo,$productId,$periodId)
    {

        if (date('Ymd')>'20160628') {
            return false;
        }

        $products = [
            '223' => 2 ,
            '224' => 4 ,
            '225' => 8 ,
            '227' => 12
        ];
        $coupons = [
            '1' => 37,
            '2' => 38,
            '3' => 39,
            '4' => 40,
            '5' => 41,
            '6' => 42,
            '7' => 43,
            '8' => 44,
            '9' => 45,
            '10' => 46,
            '11' => 47,
            '12' => 48
        ];

//        $products = [
//            '119' => 2 ,
//            '2' => 4 ,
//            '1' => 8 ,
//            '3' => 12
//        ];
//        $coupons = [
//            '1' => 14,
//            '2' => 15,
//            '3' => 25,
//            '4' => 16,
//            '5' => 17,
//            '6' => 26,
//            '7' => 27,
//            '8' => 28,
//            '9' => 29,
//            '10' => 18,
//            '11' => 30,
//            '12' => 31
//        ];


        try {

            $userId = $userInfo['id'];
            if (isset($products[$productId])) {
                if ($userInfo && $userInfo['from'] == 1) {

                    $buyTotal = UserBuylistDistribution::findByUserHomeId($userInfo['home_id'])->where(['product_id'=>$productId,'period_id'=>$periodId])->asArray()->one();
                    if ($buyTotal) {
                        $buyNums = $buyTotal['buy_num'];

                        if ($buyNums > $products[$productId]) {
                            $couponMoney = $products[$productId];
                            $couponId = $coupons[$products[$productId]];
                        }else{
                            $couponMoney = $buyNums;
                            $couponId = $coupons[$buyNums];
                        }

                        $hnmyMoneyInfo = Coupon::hnmyMoneyInfo($userId, $userInfo['home_id']);
                        if ($hnmyMoneyInfo['left_num']<$couponMoney) {
                            return false;
                        }

                        $logStr = 'peirodId:' . $periodId . '---' . '猴年马月送红包 id:'.$couponId;
                        static::printLog($logStr, false);

                        $rs = Coupon::receivePacket($couponId, $userId, 'awaken');
                        if ($rs['code'] == '0') {
                            $pid = $rs['data']['pid'];
                            $info = Coupon::openPacket($pid,$userId);
                        }
                    }
                }
            }
        } catch (\Exception $e) {

            $logStr = 'peirodId:' . $periodId . '---' . '猴年马月送红包失败----' . $e->getMessage() . '----' . $e->getLine();
            static::printLog($logStr, true);
            return false;
        }
    }
}