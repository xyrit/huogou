<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/19
 * Time: 下午1:41
 */
namespace app\modules\weixin\controllers;


use app\models\User;
use app\services\Thirdpay;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class CartController extends BaseController
{
    public function actionIndex()
    {

        return $this->render('index', [

        ]);
    }

    public function actionPayment()
    {
        if(\Yii::$app->user->isGuest) {
            return \Yii::$app->user->loginRequired();
        }
        return $this->render('payment', [

        ]);
    }

//    public function actionWeixinpay()
//    {
//        $order = \Yii::$app->request->get('o');
//        $pay = new Thirdpay();
//        $jsApiParameters = $pay->pay($order, 'brandchat');
//        if (!$jsApiParameters) {
//            return $this->redirect(Url::to(['/weixin/member/consumption']),302);
//        }
//        return $this->render('weixinpay', [
//            'orderId' => $order,
//            'jsApiParameters' => $jsApiParameters,
//        ]);
//
//
//    }
//
//    public function actionWeixinqrpay()
//    {
//        $order = \Yii::$app->request->get('o');
//        $orderInfo = Thirdpay::getOrderByNo($order);
//        if (!$orderInfo) {
//            throw new NotFoundHttpException('错误的订单信息');
//        }
//        $uid = \Yii::$app->user->id;
//        $payMoney = $orderInfo['post_money'];
//        $type = $orderInfo['type'];
//        $name = $orderInfo['payment'];
//        $payBank = $orderInfo['bank'];
//        $source = $orderInfo['source'];
//        $point = $orderInfo['point'];
//        $createOrder = new Thirdpay();
//        $newOrder = $createOrder->createRechargeOrder($uid,$payMoney,$type,$name,$payBank,$source,$point);
//        return $this->render('weixinqrpay', [
//            'newOrder' => $newOrder,
//            'qrUrl'=>Url::to(['/chatpay/qrcode','o'=>$newOrder]),
//            'payMoney'=>$payMoney,
//        ]);
//    }
//
//    public function actionWeixinpayok()
//    {
//        $order = \Yii::$app->request->get('o');
//        return $this->render('weixinpayok', [
//            'orderId' => $order,
//        ]);
//    }

    public function actionWeixinpay()
    {
        $order = \Yii::$app->request->get('o');
        $pay = new Thirdpay();
        $url = $pay->pay($order, 'zhifukachat');
        return $this->redirect($url);

    }

    public function actionWeixinpayok()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $customStr = $request->get('s');
        $pay = new Thirdpay();
        $data = $pay->result('redirect',$order,'');
        return $this->redirect($data['url']);
    }


    public function actionChinabankpay()
    {
        $order = \Yii::$app->request->get('o');
        $pay = new Thirdpay();
        $chinabackHtml = $pay->pay($order,'chinaBank');
        return $chinabackHtml;
    }

    public function actionIapppayok()
    {
        $respData = \Yii::$app->request->getQueryString();
        $iapppay = \Yii::$app->iapppay;
        $transdata = $iapppay->parseResponse($respData);
        if ($transdata && isset($transdata['result']) && $transdata['result']==0) {
            $no = $transdata['cporderid'];

            $appuserid = $transdata['appuserid'];
            $cpprivate = $transdata['cpprivate'];

            $third = new Thirdpay();
            $orderInfo = $third->getOrderByNo($no);

            if ($orderInfo && $transdata['money'] == $orderInfo['post_money']) {
                $data = $third->result('redirect',$no,$cpprivate,$transdata);
                if (isset($data['url'])) {
                    return $this->redirect($data['url']);
                }
            }
        }
        return $this->redirect(Url::to(['/weixin/member/consumption']));

    }





}