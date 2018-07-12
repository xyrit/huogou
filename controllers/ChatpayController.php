<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/23
 * Time: 下午7:45
 */
namespace app\controllers;

use app\helpers\Brower;
use app\models\User;
use app\services\Pay;
use app\services\Thirdpay;
use Yii;
use yii\web\Response;

class ChatpayController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionIndex(){
        $order = Thirdpay::getOrderByNo(Yii::$app->request->get('o'));
        $pay = new Thirdpay();
        $qrcode = $pay->pay($order['id'], 'zhifukachat');
        return $this->render('index', ['order'=>$order,'qrcode'=>$qrcode]);
    }

    public function actionQqpay()
    {
        $order = Thirdpay::getOrderByNo(Yii::$app->request->get('o'));
        $pay = new Thirdpay();
        $qrcode = $pay->pay($order['id'], 'zhifukaqq');
        return $this->render('qqpay', ['order'=>$order,'qrcode'=>$qrcode]);
    }

//    public function actionQrcode()
//    {
//
//        $order = Yii::$app->request->get('o');
//        $pay = new Thirdpay();
//        $image = $pay->pay($order, 'zhifukachat');
//        if ($image) {
//            $response = Yii::$app->response;
//            $response->format = Response::FORMAT_RAW;
//            header("Content-type: image/png");
//            echo $image;
//        } else {
//            return false;
//        }
//    }

    public function actionNotify()
    {
        $from = Brower::whereFrom();
        if ($from == 2) {
            $config = require (Yii::getAlias('@app/config/didi_zhifuka.php'));
            $zhifuka = Yii::createObject($config);
        } else {
            $zhifuka = Yii::$app->zhifuka;
        }
        return $zhifuka->notify([$this, 'notifyRechargeSuccess'], [$this, 'notifyRechargeFail']);
    }

    public function notifyRechargeSuccess($data)
    {
        if (!$data) {
            return false;
        }

        $state = $data["state"];            // 1:充值成功 2:充值失败
        $customerid = $data["customerid"];    //商户注册的时候，网关自动分配的商户ID
        $sd51no = $data["sd51no"];          //该订单在网关系统的订单号
        $sdcustomno = $data["sdcustomno"];  //该订单在商户系统的流水号
        $ordermoney = $data["ordermoney"];  //商户订单实际金额单位：（元）
        $cardno = $data["cardno"];          //支付类型，为固定值 32
        $mark = $data["mark"];              //商户自定义字符串

        $third = new Thirdpay();
        $orderInfo = $third->getOrderByNo($sdcustomno);

        if ($orderInfo && $ordermoney*100/100 == $orderInfo['post_money']) {
            $third->result('notice',$sdcustomno,$third->getCustomStr($sdcustomno),$data);
        }

    }

    public function notifyRechargeFail($data)
    {

    }

    public function actionOnlinepayNotify()
    {
        $params = Yii::$app->request->post();
        $from = Brower::whereFrom();
        if ($from == 2) {
            $config = require (Yii::getAlias('@app/config/didi_zhifuka.php'));
            $zhifuka = Yii::createObject($config);
        } else {
            $zhifuka = Yii::$app->zhifuka;
        }
        $result = $zhifuka->onlinePayBack($params);
        echo "<result>OK</result>";
    }

}