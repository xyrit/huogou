<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/30
 * Time: 下午4:09
 */
namespace app\controllers;

use app\services\Thirdpay;
use yii\helpers\Url;

class KqpayController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();

        return $pay->pay($order,'kq');
    }

    public function actionNotify()
    {
        $kqpay = \Yii::$app->kqpay;
        $orderId = $_REQUEST['orderId'];
        $redirectUrl = 'http://www.'.DOMAIN.'/kqpay/redirect-'.$orderId.'.html';
        $kqpay->notify([$this, 'notifyRechargeSuccess'], [$this, 'notifyRechargeFail'], $redirectUrl);
    }

    public function notifyRechargeSuccess($params)
    {
        $no = $params['orderId'];
        $payMoney = $params['payAmount'];
        $customStr = $params['ext1'];
        $third = new Thirdpay();
        $orderInfo = $third->getOrderByNo($no);
        if ($orderInfo && $payMoney/100==$orderInfo['post_money']) {
            $third->result('notice',$no,$customStr,$params);
        }
    }

    public function notifyRechargeFail($params)
    {

    }

    public function actionRedirect()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();
        $data = $pay->result('redirect',$order);
        return $this->redirect($data['url']);
    }


}