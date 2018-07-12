<?php
/**
 * User: hechen
 * Date: 15/10/14
 * Time: 下午10:51
 */

namespace app\controllers;

use Yii;
use app\services\Thirdpay;
use dosamigos\qrcode\QrCode;
use app\services\NativeNotifyCallBack;
use app\models\User;
use app\services\Pay;
use yii\web\Response;

class ChatpayController extends BaseController
{

    public $enableCsrfValidation = false;

    public function actionIndex(){
        $order = Thirdpay::getOrderByNo(Yii::$app->request->get('o'));
        return $this->render('index', ['order'=>$order]);
    }

    public function actionQrcode()
    {
    	// $url = Yii::$app->chatpay->createPayUrl('1');
        $order = Yii::$app->request->get('o');
        $pay = new Thirdpay();
        $url = $pay->pay($order,'chat');

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
    	return QrCode::png($url,false,'L','11','0');
    }


    public function actionNotify()
    {
        Yii::$app->chatpay->setNotifyCallback([$this,'notify'], false);
    }

    public function notify($data, &$msg)
    {
        if ($data) {
            $third = new Thirdpay();
            $orderInfo = $third->getOrderByNo($data['out_trade_no']);
            $backMoney = $data['total_fee']/100;
            if ($backMoney == $orderInfo['post_money']) {
                if (isset($orderInfo['status']) && $orderInfo['status']==0) {
                    $order_data['status'] = 1;
                    $order_data['pay_time'] = microtime(true);
                    $order_data['money'] = $orderInfo['post_money'];
                    $order_data['result'] = json_encode($data);
                    $third->updateOrder($data['out_trade_no'],$order_data);

                    $userInfo = User::find()->where(['id'=>$orderInfo['user_id']])->asArray()->one();
                    $money = $userInfo['money']+$backMoney;
                    User::updateAll(['money'=>$money],['id'=>$orderInfo['user_id']]);
                }
            }else{
                $order_data['status'] = 0;
                $msg = "输入参数不正确";
                return false;
            }
            return true;
        } else {
            $msg = "输入参数不正确";
            return false;
        }
    }

    public function actionResult(){
        $orderId = Yii::$app->request->get('o');
        $orderInfo = Thirdpay::getOrderByNo($orderId);
        $pay = new Pay($orderInfo['user_id']);
        $pay->payByBalance($orderId);
        return $this->render('result',['order'=>$orderId]);
    }

}