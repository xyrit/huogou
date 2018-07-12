<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/1/5
 * Time: 下午1:46
 */
namespace app\controllers;

use app\services\Thirdpay;

class UnionpayController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();

        return $pay->pay($order,'union');
    }

    public function actionNotify()
    {
        $unionpay = \Yii::$app->unionpay;
        return $unionpay->backNofity([$this,'notifyRechargeSuccess'],[$this,'notifyRechargeFail']);
    }

    public function actionRedirect()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();
        $data = $pay->result('redirect',$order);
        return $this->redirect($data['url']);
    }

    public function notifyRechargeSuccess($params)
    {
        $orderId = $params['orderId'];
        $txnAmt = $params['txnAmt'];
        $third = new Thirdpay();
        $orderInfo = $third->getOrderByNo($orderId);
        if ($orderInfo && $txnAmt/100==$orderInfo['post_money']) {
            $customStr = $third->getCustomStr($orderId);
            $result = $third->result('notice',$orderId,$customStr,$params);
        }
    }

    public function notifyRechargeFail($params)
    {
        return '';
    }


}