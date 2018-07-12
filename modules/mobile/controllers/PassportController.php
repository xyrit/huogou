<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/18
 * Time: 下午4:36
 */
namespace app\modules\mobile\controllers;

use app\helpers\Brower;
use app\models\LoginLog;
use app\models\MpUser;
use app\modules\mobile\models\LoginForm;
use app\modules\mobile\models\RegisterForm;
use app\services\User;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\web\Response;
use Yii;

class PassportController extends BaseController
{

    public function actionRegister()
    {
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $phone = $request->post('phone');
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            User::sendCode($phone, 1);
            return ['error' => 0];
        }
        return $this->render('register', [

        ]);
    }

    public function actionRegisterCheck()
    {
        $request = \Yii::$app->request;
        $phone = $request->get('mobile');
        if ($request->isPost) {
            $code = $request->post('code');
            $phone = $request->post('mobile');
            $sendCode = User::getCode($phone, 1);

            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            if ($code && $sendCode==$code) {
                $registerInfo = [];
                $registerInfo['account'] = $phone;
                $registerInfo['smsCode'] = $code;
                $jsonInfo = Json::encode($registerInfo);
                $key = Yii::$app->security->generateRandomString();
                $cache = Yii::$app->cache;
                $cache->set($key, $jsonInfo, 1800);
                $url = Url::to(['/mobile/passport/register-bind', 'key'=>$key]);
                return ['error' => 0, 'url'=>$url];
            } else {
                return ['error' => 1, 'message'=>'验证码错误'];
            }
        }
        return $this->render('registercheck', [
            'phone' => $phone,
        ]);
    }

    public function actionRegisterBind()
    {
        $request = \Yii::$app->request;


        if ($request->isPost) {
            $key = $request->post('key');
            $account = $request->post('account');
            $password = $request->post('password');
            $confirmPassword = $request->post('repassword');
            $cache = Yii::$app->cache;
            $jsonInfo = $cache->get($key);
            $registerInfo = Json::decode($jsonInfo);
            $model = new RegisterForm();
            $model->username = $registerInfo['account'];
            $model->password = $password;
            $model->confirmPassword = $confirmPassword;
            $model->smsCode = $registerInfo['smsCode'];
            $model->setRegSource(5);
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            if ($model->validate() && $user = $model->register()) {

                Yii::$app->user->login($user, 1800);
                return ['error' => 0, 'url'=>Url::to(['/mobile/member'])];
            } else {
                return ['error' => 1];
            }
        } else {
            $key = $request->get('key');
            $cache = Yii::$app->cache;
            $jsonInfo = $cache->get($key);
            $registerInfo = Json::decode($jsonInfo);
        }
        return $this->render('registerbind', [
            'key'=>$key,
            'account'=>$registerInfo['account'],
        ]);
    }

    public function actionLogin()
    {
        $request = Yii::$app->request;
        if ($request->isGet) {
            if (!Yii::$app->user->isGuest) {
                return $this->redirect(['/mobile']);
            }
        }
        $response = Yii::$app->response;
        $model = new LoginForm();
        if ($request->isPost) {
            $model->username = $request->post('account');
            $model->password = $request->post('password');
            if ($model->validate()) {
                $user = $model->getUser();
                $model->login(1800);
                $response->format = Response::FORMAT_JSON;
                //添加用户登录日志
                LoginLog::addLog($user->id,0,2);
                return ['error' => 0];

            } else {
                $response->format = Response::FORMAT_JSON;
                return ['error' => 1, 'message' => $model->getFirstErrors()];
            }
        } else {
            $cookies = Yii::$app->request->cookies;
            $model->username = $cookies->getValue('_uname');
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        $user = Yii::$app->getUser();
        if ($user->isGuest) {
            return $this->redirect(['/mobile']);
        }
        //添加用户登出日志
        LoginLog::addLog($user->id,1,2);
        $user->logout();
        return $this->redirect(['/mobile']);
    }

    public function actionTerms()
    {
        return $this->render('terms', [

        ]);
    }

    //忘记密码
  public function actionFindpassword($tpl="findpassword")
    {
        return $this->render($tpl, [

        ]);
    }

}