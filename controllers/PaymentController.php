<?php
/**
 * User: hechen
 * Date: 15/10/8
 * Time: 上午10:06
 */

namespace app\controllers;

use Yii;
use app\models\User;
use app\services\Pay;
use yii\helpers\Url;
use app\services\Thirdpay;

class PaymentController extends BaseController
{
    private $payType = ['recharge'=>1,'consume'=>2];
    private $payName = ['debit'=>1,'credit'=>2,'platform'=>3,'commssion'=>4,'huogoucard'=>5];
    public $user;

	public function init(){
        parent::init();
        $user = User::findIdentityByAccessToken($this->token);
        if (!$user) {
            $this->redirect(array('/cart'),301);
        }
        $this->user = $user;
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {
            

        }

    	$csrf = Yii::$app->getRequest()->getCsrfToken();
        return $this->render('index',['csrf'=>$csrf,'token'=>isset($_COOKIE['_utoken']) ? $_COOKIE['_utoken'] : '']);
    }

}