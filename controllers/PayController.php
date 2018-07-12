<?php
/**
 * User: hechen
 * Date: 15/10/8
 * Time: 下午3:25
 */

namespace app\controllers;

use Yii;
use app\services\Pay;
use app\services\Thirdpay;
use app\models\User;
use yii\helpers\Url;
use app\services\Payway;
use app\services\Cart;
use app\services\Coupon;

class PayController extends BaseController
{

    public $user;

    public function init(){
        parent::init();
        $user = User::findIdentityByAccessToken($this->token);
        if ($user) {
            $this->user = $user;
        }
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;

        $payType = $request->post('payType');
        $payName = $request->post('payName','balance');
        $payBank = $request->post('payBank');
        $point = $request->post('integral','0');
        $payMoney = $request->post('payMoney','0');
        $source = $request->post('userSource','1');
        $ppwd = $request->post('ppwd');
        $couponCode = $request->post('couponCode');
        $couponId = $request->post('couponId');
        $coupons = $request->post('coupons');

        $cartMoney = Cart::getCartMoneByUid($this->user->id);

        if ( $this->user->pay_password && $this->user->micro_pay < ($cartMoney-$payMoney) && $payMoney < $cartMoney && $payType == 'consume') {
            if (empty($ppwd) || !Yii::$app->getSecurity()->validatePassword($ppwd,$this->user->pay_password)) {
                return false;
            }
        }


        $choosePay = new Payway();
        $result = $choosePay->chooseway($this->user->id, $payType, $payName, $payBank, $point, $payMoney, $source,$coupons);

        if ($result['code'] == '100') {
            if ($result['type'] == 'balance') {
                $this->redirect(array('/pay/result','o'=>$result['order']),301);
            }else if ($result['type'] == 'third') {
                $third = new Thirdpay();
                if ($request->post('payName') == 'debit' || $request->post('payName') == 'credit') {
                    echo $third->pay($result['order'],'chinaBank');
                    exit;
                }else if ($request->post('payName') == 'platform') {
                    if ($payBank == 'zhifukachat') {
                        return $this->redirect(array('/chatpay','o'=>$result['order']),301);
                    }elseif($payBank=='zhifukaqq') {
                        return $this->redirect(array('/chatpay/qqpay','o'=>$result['order']),301);
                    }else{
                        echo $third->pay($result['order'],$request->post('payBank'));
                        exit;
                    }
                }
            }

        }else{
            print_r($result);
            exit;
        }


    }

    public function actionResult(){
        $order = Yii::$app->request->get('o');
        $rorder = Yii::$app->request->get('r');

        echo $this->render('result',['order'=>$order,'rorder'=>$rorder]);

        if(function_exists('fastcgi_finish_request') ) fastcgi_finish_request();
        if ($order) {
            $pay = new Pay($this->user->id);
            $pay->payByBalance($order);
        }
    }

}