<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/6/30
 * Time: 下午5:23
 */
namespace app\controllers;

use app\services\Thirdpay;

class AlipayController extends BaseController
{

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();

        return $pay->pay($order,'alipay');
    }

    public function actionNotify()
    {
        try {

            $alipay = \Yii::$app->alipay;
            $alipay->notify([$this, 'notifyRechargeSuccess'], [$this, 'notifyRechargeFail'], [$this, 'notifyRechargeFinish']);

        } catch (\Exception $e) {
            file_put_contents(\Yii::getAlias('@app/web/alipay.error.txt'),$e->getLine().'-'.$e->getMessage(),FILE_APPEND);
        }
    }

    public function notifyRechargeSuccess($data)
    {
        if (!$data) {
            return false;
        }
        $out_trade_no = $data['out_trade_no'];

        //支付宝交易号

        $trade_no = $data['trade_no'];
        $total_fee = $data['total_fee'];

        //交易状态
        $trade_status = $data['trade_status'];

        $third = new Thirdpay();
        $orderInfo = $third->getOrderByNo($out_trade_no);

        if (DOMAIN=='5ykd.com') {
            if ($orderInfo && $total_fee*100/100 == 0.1) {
                $third->result('notice',$out_trade_no,$third->getCustomStr($out_trade_no),$data);
            }
            return;
        }
        if ($orderInfo && $total_fee*100/100 == $orderInfo['post_money']) {
            $third->result('notice',$out_trade_no,$third->getCustomStr($out_trade_no),$data);
        }

    }

    public function notifyRechargeFail($data)
    {

    }

    public function notifyRechargeFinish($data)
    {

    }

}