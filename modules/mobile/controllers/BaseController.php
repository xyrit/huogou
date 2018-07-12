<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/18
 * Time: 下午4:22
 */
namespace app\modules\mobile\controllers;

use app\helpers\Brower;
use yii\web\Controller;
use app\models\User;

class BaseController extends Controller
{


    public $enableCsrfValidation = false;

    public $userId=0;
    public $token = '';

    public $jsSdkSignPackage = [];

    public function init()
    {
        if (Brower::whereFrom() == 2) {
            $this->redirect(['/ddweixin']);
            \Yii::$app->end();
        }
        $user = \Yii::$app->user;
        $this->token = $user->accessToken;
        \Yii::$app->user->loginUrl = ['/mobile/passport/login'];
        \Yii::$app->errorHandler->errorAction = '/mobile/error/index';
        if (!$user->isGuest) {
            $status = $user->identity->status;
            if ($status==1) {
                echo '账户被冻结';
                \Yii::$app->end();
            }
        }
    }

}