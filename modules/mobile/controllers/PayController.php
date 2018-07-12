<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/28
 * Time: 上午11:06
 */
namespace app\modules\mobile\controllers;


use app\models\User;
use app\services\Thirdpay;

class PayController extends BaseController
{
    private $payType = ['recharge'=>1,'consume'=>2];
    private $payName = ['debit'=>1,'credit'=>2,'platform'=>3,'commssion'=>4,'huogoucard'=>5];

    public $userId;

    public function init(){
        parent::init();
        $user = User::findIdentityByAccessToken($this->token);
        if ($user) {
            $this->userId = $user->id;
        }
    }

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $o = $request->post('o');
        $third = new Thirdpay();


        //TODO
    }

    public function actionResult()
    {
        $orderId = \Yii::$app->request->get('o');
        $rorderId = \Yii::$app->request->get('r');
        return $this->render('result',[
            'orderId'=>$orderId,
            'rorderId'=>$rorderId,
        ]);
    }

}