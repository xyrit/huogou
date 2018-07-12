<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/13
 * Time: 下午6:14
 */
namespace app\modules\api\controllers;

use app\helpers\MyRedis;
use app\models\PaymentOrderDistribution;
use app\models\PkCurrentPeriod;
use app\models\PkPaymentOrderItemDistribution;
use app\models\PkPeriod;
use app\models\PkPeriodBuylistDistribution;
use app\models\User;
use app\services\Pay;
use app\services\Payway;
use app\services\PkPay;
use app\services\PkProduct;
use app\services\Thirdpay;
use Yii;
use yii\helpers\Json;

class PkPayController extends BaseController
{

    public function actionCreateOrder()
    {
        $request = Yii::$app->request;
        if (!$this->userId) {
            return array('code'=>10001,'message'=>'未登录');
        }

        $payType = $request->post('payType');
        $payName = $request->post('payName','pk_balance');
        $payBank = $request->post('payBank');
        $point = $request->post('integral','0');
        $payMoney = $request->post('payMoney','0');
        $source = $request->post('userSource','1');
        $ppwd = $request->post('ppwd');
        $coupons = $request->post('coupons');
        $periodId = $request->post('periodId');
        $postNum = $request->post('postNum');
        $buySize = $request->post('buySize');

//        $payType = $request->get('payType');
//        $payName = $request->get('payName','pk_balance');
//        $payBank = $request->get('payBank', '');
//        $point = $request->get('integral','0');
//        $payMoney = $request->get('payMoney','0');
//        $source = $request->get('userSource','1');
//        $ppwd = $request->get('ppwd');
//        $coupons = $request->get('coupons');
//        $periodId = $request->get('periodId');
//        $postNum = $request->get('postNum');
//        $buySize = $request->get('buySize');

        $user = User::find()->select('status')->where(['id'=>$this->userId])->one();
        if ($user->status==1) {
            return ['code'=>10099, 'message'=>'账户已冻结,请联系客服.'];
        }
        $choosePay = new Payway();
        $result = $choosePay->choosePkPayway($periodId, $buySize, $postNum, $this->userId, $payType, $payName, $payBank, $point, $payMoney, $source,$coupons);
        try {

            if ($payName != 'pk_balance' && ($source == '3' || $source == '4')) {
                if ($result['code'] == '100') {
                    $pay = new Thirdpay();
                    $payResult = $pay->pay($result['order'],$payBank);
                    if ($payResult) {
                        $payResult['code'] = 100;
                    } else {
                        $payResult['code'] = 0;
                    }
                    return $payResult;
                }
            }else{
                return $result;
            }
        } catch (\Exception $e) {
            file_put_contents(Yii::getAlias('@app/a.txt'), $e->getLine().'_'.$e->getMessage());
        }
    }

    /**
     * 支付
     * @return [type] [description]
     */
    public function actionPayOrder(){
        $order = Yii::$app->request->post('o') ? : Yii::$app->request->get('o');

        if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();

        $pay = new PkPay($this->userId);
        $data = $pay->payByBalance($order);

        return $data;
    }

    /**
     * 支付结果
     * @return [type] [description]
     */
    public function actionResult(){
        $order = Yii::$app->request->get('o');
        if (!$order) {
            $data['code'] = 201;
            $data['message'] = '支付失败';
            return $data;
        }
        if (!$this->userId) {
            $data['code'] = 201;
            $data['message'] = '支付失败';
            return $data;
        }
        $user = User::find()->where(['id'=>$this->userId])->asArray()->one();

        $redis = new MyRedis();

        $orderInfo = Json::decode($redis->hget(Pay::ORDER_LIST_KEY,$order),true);


        if (!$orderInfo) {
            $orderInfo = PaymentOrderDistribution::findByTableId($user['home_id'])->where(['id'=>$order])->asArray()->one();
        }

        if (empty($orderInfo)) {
            $redis->del((Pay::THIRD_PAY_KEY).$orderInfo['recharge_orderid']);
            $data['code'] = 201;
            $data['message'] = '伙购失败';
            return $data;
        }else{
            if ($orderInfo['status'] == 0) {
                $data['code'] = 0;
                $data['message'] = '订单支付中...';
                return $data;
            }
            if ($orderInfo['status'] == 2) {
                $redis->del((Pay::THIRD_PAY_KEY).$orderInfo['recharge_orderid']);
                $data['code'] = 201;
                $data['message'] = '伙购失败';
                return $data;
            }

            $orderItems = $redis->hget(Pay::ORDER_ITEMS_KEY.$order,'all');
            if (!$orderItems) {
                $orderItems = PkPaymentOrderItemDistribution::findByTableId($user['home_id'])->where(['payment_order_id'=>$order])->asArray()->all();
            }
            $buyNums = 0;
            $buyInfos = [];
            foreach ($orderItems as $key => &$value) {
                if (!is_array($value)) {
                    $value = json_decode($value,true);
                }
                $buyNums += $value['nums'];
                $tables = $value['buy_tables'];
                foreach(explode(',', $tables) as $table) {
                    $buyInfos[] = [
                        'period_id' => $value['period_id'],
                        'buy_size' => $value['buy_size'],
                        'buy_table' => $table,
                    ];
                }
            }
            $matchNum = 0;
            $waitNum = 0;
            foreach($buyInfos as $one) {
                $periodId = $one['period_id'];
                $buySize = $one['buy_size'];
                $buyTable = $one['buy_table'];
                $period = PkCurrentPeriod::find()->select('table_id')->where(['id' => $periodId])->one();
                if (!$period) {
                    $period = PkPeriod::find()->select('table_id')->where(['id' => $periodId])->one();
                }
                $oppositeBuySize = $buySize == PkCurrentPeriod::BUY_SIZE_BIG ? PkCurrentPeriod::BUY_SIZE_SMALL : PkCurrentPeriod::BUY_SIZE_BIG;
                $oppositeWhere = ['period_id' => $periodId, 'buy_table' => $buyTable, 'buy_size' => $oppositeBuySize];
                $oppositeBuy = PkPeriodBuylistDistribution::findByTableId($period['table_id'])->where($oppositeWhere)->one();
                if ($oppositeBuy) {
                    $matchNum ++;
                } else {
                    $waitNum ++;
                }
            }

            $data['code'] = 100;
            $data['buy_nums'] = $buyNums;
            $data['match_num'] = $matchNum;
            $data['wait_num'] = $waitNum;

            return $data;
        }

    }

}