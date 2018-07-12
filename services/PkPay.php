<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/13
 * Time: 上午11:11
 */
namespace app\services;

use app\helpers\Brower;
use app\helpers\FileHelper;
use app\helpers\MyRedis;
use app\models\Invite;
use app\models\PaymentOrderDistribution;
use app\models\PkCurrentPeriod;
use app\models\PkPaymentOrderItemDistribution;
use app\models\PkPeriod as PkPeriodModel;
use app\models\PkPeriodBuylistDistribution;
use app\models\PkUserBuylistDistribution;
use app\models\UserCoupons;
use yii\helpers\Json;
use app\models\User as UserModel;

class PkPay
{

    public $total;
    public $userHomeId;
    public $buyNums = 0;
    public $totalMoney = 0;
    public $realMoney = 0;
    public $realPoint = 0;
    public $needMoney = 0;
    public $needPoint = 0;
    public $deduction;
    public $userInfo;
    public $orderInfo;
    public $orderItems;
    public $order;
    public $coupons;

    public $redis;
    public $userId;

    const PK_PERIOD_BUYING_KEY = 'PK_PERIOD_BUYING_KEY_'; //hash

    public function __construct($uid){
        $this->redis = new MyRedis();
        $this->userId = $uid;
    }


    public function createPayOrder($periodId, $buySize, $postNum, $source,$point,$payType='1',$bank='',$recharge_orderid='')
    {
        $userInfo = User::baseInfo($this->userId);

        if (!$userInfo) {
            return false;
        }
        $this->userInfo = $userInfo;
        $this->userHomeId = $userInfo['home_id'];
        $time = microtime(true);
        $from = Brower::whereFrom();
        if ($from == 2) {
            $orderSub = 'DP';
        } else {
            $orderSub = 'HP';
        }
        $payOrderNum = PaymentOrderDistribution::generateOrderId($userInfo['home_id'], $orderSub);

        $userIp = ip2long(\Yii::$app->request->userIp);
        if ($recharge_orderid) {
            $rechargeOrderInfo = Thirdpay::getOrderByNo($recharge_orderid);
            $userIp = $rechargeOrderInfo['ip'];
        }
        $periodInfo = PkCurrentPeriod::findOne($periodId);
        if (!$periodInfo) {
            $oldPeriodInfo = PkPeriodModel::findOne($periodId);
            if (!$oldPeriodInfo) {
                return false;
            }
            $productId = $oldPeriodInfo['product_id'];
            $periodInfo = PkCurrentPeriod::find()->where(['product_id' => $productId])->one();
            if (!$periodInfo) {
                return false;
            }
        }
        $endTime = $periodInfo['end_time'];
        $nowTime = time();
        if ($nowTime >= $endTime) {
            return false;
        }

        $price = $periodInfo['price'];
        $money = ceil($price/2) * intval($postNum);
        $needMoney = $money - intval($point/100);
        $orderInfo = array(
            'id' => $payOrderNum,
            'user_id' => $this->userId,
            'status' => 0,
            'payment' => $payType,
            'bank' => $bank,
            'money' => $needMoney,
            'point' => $point,
            'total' => $money,
            'user_point' => $point,
            'ip' => $userIp,
            'source' => $source,
            'create_time' => $time,
            'buy_time' => $time,
            'recharge_orderid' => $recharge_orderid,
            'user_account' => $userInfo['nickname'] ? : ($userInfo['phone'] ? : $userInfo['email']),
            'spread_source' => $userInfo['spread_source'],
            'pay_for' => PaymentOrderDistribution::PAY_FOR_PK,
        );

        $setOrder = $this->redis->hset(Pay::ORDER_LIST_KEY, [$payOrderNum=>Json::encode($orderInfo)]);
        if ($setOrder) {
            $itmes = [];

            $itmes[$periodInfo['id']] = Json::encode([
                'payment_order_id' => $payOrderNum,
                'product_id' => $periodInfo['product_id'],
                'period_id' => $periodInfo['id'],
                'period_no' => $periodInfo['period_no'],
                'user_id' => $this->userId,
                'post_nums' => $postNum,
                'nums' => $postNum,
                'buy_tables' => '',
                'buy_size' => $buySize,
                'item_buy_time' => '',
                'source' => $source,

            ]);
            $this->redis->hset(Pay::ORDER_ITEMS_KEY.$payOrderNum,$itmes);
        }

        if ($recharge_orderid) {
            $this->redis->set(Pay::THIRD_PAY_KEY.$recharge_orderid,$payOrderNum,3600);
        }

        return $payOrderNum;
    }

    public function payByBalance($payOrderId)
    {
        set_time_limit(0);

        $this->order = $payOrderId;
        $this->userInfo = User::baseInfo($this->userId);
        if (!$this->userInfo) {
            return false;
        }
        $this->userHomeId = $this->userInfo['home_id'];

        // 获取订单信息
        $this->orderInfo = Json::decode($this->redis->hget(Pay::ORDER_LIST_KEY,$this->order));

        if (!$this->orderInfo) {
            $data['code'] = 201;
            $data['message'] = '订单不存在';
            return $data;
        }

        $isexist = $this->redis->sset(Pay::ORDER_HAND_LIST_KEY,$this->order);
        if (!$isexist) {
            return false;
        }

        if ($this->orderInfo['status'] == '1') {
            $data['code'] = 100;
            $data['message'] = '订单成功';
            return $data;
        }

        try {

            if ($this->orderInfo['total'] > 0) {
                // 计算需要的金额
                $usermoney = $this->userInfo['money'];
                $userPoint = $this->userInfo['point'];
                $pointMoney = 0; //积分转换成的金额
                if ($this->orderInfo['user_point'] > 0 && $this->orderInfo['user_point'] % 100 == 0 && $this->orderInfo['user_point'] <= $userPoint) {
                    $pointMoney = $this->orderInfo['user_point']/100;
                }
                //获取订单优惠券信息
                $this->coupons = Json::decode($this->redis->hget(Pay::ORDER_COUPON_KEY,$this->order));
                $this->redis->hdel(Pay::ORDER_COUPON_KEY,$this->order);
                $deduction = 0;
                if ($this->coupons) {
                    foreach ($this->coupons as $key => $value) {
                        $deduction += $value['deduction'];
                    }
                }
                // 余额+积分+优惠券优惠金额>订单总额
                if (($usermoney+$pointMoney+$deduction) >= $this->orderInfo['total']) {
                    $needMoney = $this->orderInfo['total'];
                    $needPoint = 0;
                    if ($pointMoney > 0) {
                        $needMoney = $this->orderInfo['total']-$pointMoney;
                        $needPoint = $pointMoney*100;
                    }
                    //总需金额减去两张优惠券优惠金额
                    $needMoney -= $deduction;

                    //扣除用户余额及积分
                    $userMoneyPoint = ['point'=>$this->userInfo['point'],'money'=>$this->userInfo['money']];
                    $rs = $this->_deduction($userMoneyPoint,$needMoney,$needPoint);

                    $this->needMoney = $needMoney;
                    $this->needPoint = $needPoint;
                    $this->deduction = $deduction;

                    //扣除成功
                    if ($rs) {
                        $this->updateUserBuy($this->order);
                        $this->addPointLog($this->userId,$pointMoney);
                    }else{
                        $this->orderInfo['status'] = 2;
                        $this->orderInfo['buy_time'] = microtime(true);
                        $this->redis->hset(Pay::ORDER_LIST_KEY,array($this->order=>Json::encode($this->orderInfo)));
                    }
                }else{
                    $this->orderInfo['status'] = 2;
                    $this->orderInfo['buy_time'] = microtime(true);
                    $this->redis->hset(Pay::ORDER_LIST_KEY,array($this->order=>Json::encode($this->orderInfo)));
                }
                $this->_orderDataToMysql();
                $this->payOffCommission($pointMoney,$deduction);//发放佣金
            }
            $this->redis->sdel(Pay::ORDER_HAND_LIST_KEY,$this->order);
        } catch (\Exception $e) {
            $echo = $e->getMessage().'_line'.$e->getLine().date('Y-m-d H:i:s')."\r\n";
            file_put_contents('pk.pay.txt',$echo,FILE_APPEND);
        }

    }

    public function updateUserBuy()
    {

        $orderItems = $this->redis->hget(Pay::ORDER_ITEMS_KEY.$this->order, 'all');

        $status = 2;
        $updateUserBuy = false;

        $_time = explode('.', microtime(true));
        $preTime = $_time[0];
        $lastTime = isset($_time[1]) ? substr($_time[1], 0, 3) : '0';
        $time = $preTime.'.'.str_pad($lastTime,3,0,STR_PAD_RIGHT);

        $memoryLimit = FileHelper::computerFileSize(ini_get('memory_limit'));
        $leftRunMemory = $memoryLimit/5;

        foreach($orderItems as $periodId => $item) {
            $itemInfo = Json::decode($item);

            $postNums = $itemInfo['post_nums'];
            $periodInfo = PkCurrentPeriod::findOne($periodId);

            if (!$periodInfo) {
                continue;
            }

            $this->redis->hset(static::PK_PERIOD_BUYING_KEY . $periodId, [$this->userId => 1]);

            $buyTablesStr = '';
            for($i=0;$i<$postNums;$i++) {
                if ($memoryLimit - memory_get_usage(true) <= $leftRunMemory) {
                    break;
                }
                if (time() >= $periodInfo['end_time']) {
                    break;
                }

                $_time = explode('.', microtime(true));
                $preTime = $_time[0];
                $lastTime = isset($_time[1]) ? substr($_time[1], 0, 3) : '0';
                $time = $preTime.'.'.str_pad($lastTime,3,0,STR_PAD_RIGHT);

                $periodBuyList = new PkPeriodBuylistDistribution($periodInfo['table_id']);
                $periodBuyListAttrs = [
                    'product_id' => $periodInfo['product_id'],
                    'period_id' => $periodId,
                    'user_id' => $this->userId,
                    'buy_size' => $itemInfo['buy_size'],
                    'buy_table' => '0',
                    'ip' => $this->orderInfo['ip'],
                    'source' => $itemInfo['source'],
                    'buy_time' => $time,
                ];
                $periodBuyList->setAttributes($periodBuyListAttrs, false);
                $periodBuyListSave = $periodBuyList->save(false);
                if (!$periodBuyListSave) {
                    continue;
                }
                $buyTable = PkPeriod::getBuyTable($periodInfo['table_id'], $periodBuyList->id, $periodId, $itemInfo['buy_size']);

                PkPeriodBuylistDistribution::updateAllByTableId($periodInfo['table_id'], ['buy_table' => $buyTable], ['id' => $periodBuyList['id']]);
                $userBuyList = new PkUserBuylistDistribution($this->userHomeId);
                $userBuyListAttr = [
                    'product_id' => $periodInfo['product_id'],
                    'period_id' => $periodId,
                    'user_id' => $this->userId,
                    'buy_size' => $itemInfo['buy_size'],
                    'buy_table' => $buyTable,
                    'buy_time' => $time,
                ];
                $userBuyList->setAttributes($userBuyListAttr, false);
                $userBuyListSave = $userBuyList->save(false);

                if ($periodBuyListSave && $userBuyListSave) {
                    $this->buyNums ++;
                    $buyTablesStr .= $buyTable . ',';
                    $updateUserBuy = true;
                    $status = 1;
                }
            }

            $buyTablesStr = rtrim($buyTablesStr, ',');
            $newItem = $itemInfo;
            $newItem['nums'] = $this->buyNums;
            $newItem['buy_tables'] = $buyTablesStr;
            $newItem['item_buy_time'] = $time;
            $updateOrderItem = $this->redis->hset(Pay::ORDER_ITEMS_KEY.$this->order,array($periodId=>Json::encode($newItem)));

            $this->totalMoney += $this->buyNums * ceil($periodInfo['price']/2);

        }
        if ($this->totalMoney > 0) {
            $this->_useCoupon();
        }
        $this->_backMoney();

        //更新订单状态
        $this->orderInfo['status'] = $status;
        $this->orderInfo['buy_time'] = $time;
        $this->orderInfo['money'] = $this->realMoney;
        $this->orderInfo['point'] = $this->realPoint;
        $this->redis->hset(Pay::ORDER_LIST_KEY,array($this->order=>Json::encode($this->orderInfo)));

        if ($updateUserBuy) {
            $this->redis->del(Pay::USER_BUY_LIST_KEY.$this->order);
        }

    }

    public function addPointLog($uid,$pointMoney)
    {
        $items = $this->redis->hget(Pay::ORDER_ITEMS_KEY.$this->order, 'all');

        $exp = 0;
        $pointLog = new Member(['id' => $uid]);
        $productIds = [];
        foreach ($items as $key => $value) {
            $val = Json::decode($value);
            $productIds[] = $val['product_id'];
        }
        $productInfo = PkProduct::info($productIds);
        $deduction = 0;
        if ($this->coupons) {
            foreach ($this->coupons as $key => $value) {
                $deduction += $value['deduction'];
            }
        }

        foreach ($items as $key => $item) {
            $item = Json::decode($item);
            $periodInfo = PkCurrentPeriod::find()->select('price')->where(['id' => $key])->one();
            $price = $periodInfo['price'];
            $count = $item['nums'] * ceil($price/2);
            $usePoint = 0;
            if ($pointMoney > 0) {
                if ($pointMoney >= $count) {
                    $pointMoney = $pointMoney - $count;
                    $usePoint = $count;
                    $count = 0;
                }else{
                    $count = $count-$pointMoney;
                    $usePoint = $pointMoney;
                    $pointMoney = 0;
                }
                $pointLog->editPoint((0-$usePoint*100), 11, 'PK商品编码('.$productInfo[$item['product_id']]['bn'].')福分抵扣','buy');
            }
            $this->redis->hset(Pay::POINT_USE_KEY.$this->order,array($key=>Json::encode(array('money'=>$count,'point'=>$usePoint*100))));
        }
    }

    private function _useCoupon()
    {
        $db = \Yii::$app->db;
        if ($this->coupons) {
            $this->orderInfo['deduction1'] = isset($this->coupons['coupon1']) ? $this->coupons['coupon1']['deduction'] : '';
            $this->orderInfo['coupon1'] = isset($this->coupons['coupon1']) ? $this->coupons['coupon1']['coupon'] : '';
            $this->orderInfo['deduction2'] = isset($this->coupons['coupon2']) ? $this->coupons['coupon2']['deduction'] : '';
            $this->orderInfo['coupon2'] = isset($this->coupons['coupon2']) ? $this->coupons['coupon2']['coupon'] : '';
            foreach ($this->coupons as $ck => $cv) {
                $tableId = substr($this->userInfo['home_id'],0,3);
                $userCouponsModel = new UserCoupons($tableId);
                $userCouponsTableName = $userCouponsModel->tableName();
                if ($cv['canuse'] == 1) {
                    UserCoupons::updateAll([
                        'status'=>1,
                        'used_time'=>time()
                    ],['id'=>$cv['user_code_id'],'code'=>$cv['coupon']]
                    );
                    $sql = "update ".$userCouponsTableName." set status = 1 and used_time = '".time()."' where id = '".$cv['user_code_id']."' and code = '".$cv['coupon']."'";
                    $db->createCommand($sql)->execute();
                }else{
                    // UserCoupons::updateAll(
                    // 		['nums' => $cv['canuse']-1],['id'=>$cv['user_code_id'],'code'=>$cv['coupon']]
                    // 	);
                }
                if ($cv['num'] > 0) {
                    UserCoupons::updateAll([
                        'status'=>1
                    ],['code'=>$cv['coupon'],'coupon_id'=>$cv['coupon_id']]
                    );
                }
            }
        }
    }

    private function _orderDataToMysql()
    {
        $transaction= \Yii::$app->db->beginTransaction();

        $orderInfo = $this->orderInfo;
        $orderItems = $this->redis->hget(Pay::ORDER_ITEMS_KEY.$orderInfo['id'],'all');
        $orderTableId = PaymentOrderDistribution::getTableIdByOrderId($orderInfo['id']);
        //订单
        $orderSave = new PaymentOrderDistribution($orderTableId);
        $orderSave->id = $orderInfo['id'];
        $orderSave->user_id = $orderInfo['user_id'];
        $orderSave->status = $orderInfo['status'];
        $orderSave->payment = $orderInfo['payment'];
        $orderSave->bank = $orderInfo['bank'];
        $orderSave->money = $orderInfo['money'];
        $orderSave->point = $orderInfo['point'];
        $orderSave->total = $orderInfo['total'];
        $orderSave->user_point = $orderInfo['point'];
        $orderSave->ip = $orderInfo['ip'];
        $orderSave->source = $orderInfo['source'];
        $orderSave->create_time = $orderInfo['create_time'];
        $orderSave->buy_time = $orderInfo['buy_time'];
        $orderSave->recharge_orderid = $orderInfo['recharge_orderid'];
        $orderSave->user_account = $orderInfo['user_account'];
        $orderSave->spread_source = $orderInfo['spread_source'];
        $orderSave->pay_for = $orderInfo['pay_for'];
        if (isset($orderInfo['deduction1']) && isset($orderInfo['coupon1'])) {
            $orderSave->deduction1 = $orderInfo['deduction1'];
            $orderSave->coupon1 = $orderInfo['coupon1'];
        }
        if (isset($orderInfo['deduction2']) && isset($orderInfo['coupon2'])) {
            $orderSave->deduction2 = $orderInfo['deduction2'];
            $orderSave->coupon2 = $orderInfo['coupon2'];
        }
        $result = $orderSave->save(false);

        //订单详情
        $db = \Yii::$app->db;
        $orderItemField = ['payment_order_id','product_id','period_id','period_no','user_id','post_nums','nums','buy_tables','buy_size','item_buy_time','source'];
        $orderItemValue = [];

        $periodIds = [];
        foreach ($orderItems as $key => $value) {
            $v = Json::decode($value);
            $orderItemUserId = isset($v['user_id']) ? $v['user_id'] : 0;
            $orderItemSource = isset($v['source']) ? $v['source'] : 0;
            $orderItemValue[] = [$v['payment_order_id'],$v['product_id'],$v['period_id'],$v['period_no'],$orderItemUserId,$v['post_nums'],$v['nums'],$v['buy_tables'],$v['buy_size'],$v['item_buy_time'],$orderItemSource];
            $periodIds[$v['period_id']] = $v['period_id'];
        }

        $orderItem = new PkPaymentOrderItemDistribution($orderTableId);
        $itemsResult = $db->createCommand()->batchInsert($orderItem::tableName(),$orderItemField,$orderItemValue)->execute();

        if ($result && $itemsResult) {
            $transaction->commit();
            $this->redis->hdel(Pay::ORDER_LIST_KEY,$orderInfo['id']);
            $this->redis->del(Pay::ORDER_ITEMS_KEY.$orderInfo['id']);
            foreach($periodIds as $onePeriodId) {
                $this->redis->hdel(static::PK_PERIOD_BUYING_KEY . $onePeriodId, $this->userId);
            }
        }else{
            foreach($periodIds as $onePeriodId) {
                $this->redis->hdel(static::PK_PERIOD_BUYING_KEY . $onePeriodId, $this->userId);
            }
            $transaction->rollback();//如果操作失败, 数据回滚
        }
    }

    private function payOffCommission($pointMoney,$deduction)
    {
        $items = $this->redis->hget(Pay::ORDER_ITEMS_KEY.$this->order, 'all');
        if (empty($items)) {
            return false;
        }
        foreach ($items as $key => &$value) {
            $value = Json::decode($value);
            $count = $value['nums'];
            if ($deduction > 0) {
                if ($deduction >= $count) {
                    $deduction -= $count;
                    $count = 0;
                }else{
                    $count -= $deduction;
                    $deduction = 0;
                }
            }
            if ($pointMoney > 0) {
                if ($pointMoney >= $count) {
                    $pointMoney = $pointMoney - $count;
                    $count = 0;
                }else{
                    $count = $count-$pointMoney;
                    $pointMoney = 0;
                }
            }
            if ($count > 0) {
                Invite::commissionPayoff($this->userId, $count, $key);
            }
        }
    }

    /**
     * 扣费
     * @param  array $source 现有余额及积分
     * @param  int $money  扣除的金额
     * @param  int $point  扣除的积分
     * @return [type]         [description]
     */
    private function _deduction($userMoneyPoint,$money,$point){
        if ($money == 0 && $point == 0) {
            return 1;
        }
        $surplusMoney = $userMoneyPoint['money']-$money;
        $surplusPoint = $userMoneyPoint['point']-$point;
        return UserModel::updateAll(['money'=>$surplusMoney,'point'=>$surplusPoint],['id'=>$this->userId]);
    }

    private function _backMoney()
    {
        $userMoneyPoint = $this->_getUserMoneyPoint();
        $totalMoney = $this->totalMoney;
        $money = $this->needMoney;
        $point = $this->needPoint;
        $deduction = $this->deduction;
        if ($totalMoney <= ($money+$point/100+$deduction)) {
            $totalMoney -= $deduction;
            $totalMoney = $totalMoney > 0 ? $totalMoney : 0;
            if ($totalMoney < intval($point/100)) {
                $this->realPoint = $totalMoney*100;
                $this->realMoney = 0;
            }else{
                $this->realPoint = $point;
                $this->realMoney = $totalMoney-($point/100);
            }
            $addPoint = $point-$this->realPoint;
            $addMoney = $money-$this->realMoney;
            $surplusMoney = $userMoneyPoint['money']+$addMoney;
            $surplusPoint = $userMoneyPoint['point']+$addPoint;
            UserModel::updateAll(['money'=>$surplusMoney,'point'=>$surplusPoint],['id'=>$this->userId]);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取用户余额
     * @return int      [description]
     */
    private function _getUserMoneyPoint(){
        return UserModel::find()->select('point,money')->where(['id'=>$this->userId])->one();
    }



}