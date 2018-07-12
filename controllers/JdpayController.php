<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/28
 * Time: 下午3:19
 */
namespace app\controllers;

use app\services\Thirdpay;
use app\components\Phonefee;

class JdpayController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();

        return $pay->pay($order,'jd');
    }

    public function actionNotify()
    {
        $jdpay = \Yii::$app->jdpay;
        $jdpay->notify([$this, 'notifyRechargeSuccess'], [$this, 'notifyRechargeFail']);
    }

    public function notifyRechargeSuccess($params)
    {
        if (!$params['data']) {
            echo 'fail';
        }
        $data = $params['data'];
        $tradeInfo = $data['TRADE'];
        $amount = $tradeInfo['AMOUNT'];
        $no = $tradeInfo['ID'];
        $status = $tradeInfo['STATUS'];
        if ($status==0) {
            $third = new Thirdpay();
            $orderInfo = $third->getOrderByNo($no);
            if ($orderInfo && $amount/100==$orderInfo['post_money']) {
                $customStr = $third->getCustomStr($no);
                echo 'success';
                $result = $third->result('notice',$no,$customStr,$data);
            }
        }
        echo 'fail';
    }

    public function notifyRechargeFail($params)
    {
        echo 'fail';
    }

    public function actionRedirect()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();
        $data = $pay->result('redirect',$order);
        return $this->redirect($data['url']);
    }

    /**
     * 话费充值结果回调
     */
    public function actionRechargeNotify()
    {    
        $params = \Yii::$app->request->post();
        $Config = require(\Yii::getAlias('@app/config/huafei.php'));
        $Phonefee = new Phonefee($Config['appkey'], $Config['openid']);
        if($Phonefee->onlinePayBack($params))echo 'success';
        else echo 'failed';

    }

}