<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4
 * Time: 15:29
 * 现在支付
 */
namespace app\controllers;

use app\services\Thirdpay;
use yii;

class NowpayController extends BaseController
{

    public $enableCsrfValidation = false;
    /**
     * @return 测试现在支付
     */
    public function actionNotify()
    {   //QQ钱包通知

        $result = \Yii::$app->nowpay->notify([$this, 'notifyRechargeSuccess'], [$this, 'notifyRechargeFail']);
        return $result;
    }


    public function notifyRechargeSuccess($data)
    {
        if (!$data) {
            return false;
        }
        $out_trade_no = $data['mhtOrderNo'];
        $total_fee = $data['mhtOrderAmt'];
        $third = new Thirdpay();
        $orderInfo = $third->getOrderByNo($out_trade_no);
        if ($orderInfo && $total_fee / 100 == $orderInfo['post_money']) {
            return $third->result('notice', $out_trade_no, $third->getCustomStr($out_trade_no), $data);
        }
    }

    public function notifyRechargeFail($data)
    {

    }
}