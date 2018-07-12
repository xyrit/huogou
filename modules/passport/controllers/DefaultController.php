<?php

namespace app\modules\passport\controllers;

use app\helpers\Brower;
use app\models\LoginLog;
use app\models\User;
use app\modules\passport\models\LoginForm;
use app\modules\passport\models\RegisterForm;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends Controller
{

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'app\actions\CaptchaAction',
                'maxLength' => 5,
                'minLength' => 5,

            ],
        ];
    }


    public function actionLogin()
    {
        $request = Yii::$app->request;

        if ($request->isGet) {
            if (!$request->cookies->getValue('pcview')) {
                if (Brower::isMcroMessager()) {
                    return $this->redirect(['/weixin/passport/login']);
                } elseif(Brower::isMobile()) {
                    return $this->redirect(['/mobile/passport/login']);
                }
            }
            if (!Yii::$app->user->isGuest) {
                return $this->redirect(['/']);
            }
        }

        $response = Yii::$app->response;
        $isIframe = $request->get('iframe','0');
        $model = new LoginForm();
        if ($request->isPost) {
            if ($model->load($request->post()) && $model->login()) {

                $forward = Yii::$app->user->returnUrl;
                if ($forward == '/' or empty($forward)) {
                    $url = Url::to(['/']);
                } else {
                    $url = Url::to($forward);
                }

                //添加用户登录日志
                LoginLog::addLog($uid = Yii::$app->user->id);

                if ($isIframe) {
                    $response->format = Response::FORMAT_JSON;
                    return ['url'=>$url, 'error'=>0, 'isframe'=>1];
                }else{
                    $response->format = Response::FORMAT_JSON;
                    return ['url'=>$url, 'error'=>0];
                }
            } else {
                $response->format = Response::FORMAT_JSON;
                return ['error'=>1, 'message'=>$model->getFirstErrors()];
            }
        } else {
            $cookies = Yii::$app->request->cookies;
            $model->username = $cookies->getValue('_uname');
        }
        if (!$isIframe) {
            return $this->render('login',[
                'model' => $model,
            ]);    
        }else{
            return $this->render('simplelogin',[
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            //添加用户登录日志
            LoginLog::addLog($uid = Yii::$app->user->id, 1);
            Yii::$app->getUser()->logout();
            return $this->redirect(['/']);
        }
        return $this->redirect(['/']);
    }

    public function actionRegister()
    {

        $request = Yii::$app->request;
        if ($request->isGet) {
            if (!$request->cookies->getValue('pcview')) {
                if (Brower::isMcroMessager()) {
                    return $this->redirect(['/weixin/passport/register']);
                } elseif(Brower::isMobile()) {
                    return $this->redirect(['/mobile/passport/register']);
                }
            }
            if (!Yii::$app->user->isGuest) {
                return $this->redirect(['/']);
            }
        }
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/']);
        }

        $model = new RegisterForm(['scenario'=>'register']);
        if ($request->isPost) {
            if ($model->load($request->post()) && $model->validate()) {
                if ($registerInfo = $model->getRegisterInfo()) {

                     $model->sendRegisterCode();
                     $jsonInfo = Json::encode($registerInfo);
                     $cache = Yii::$app->cache;
                     $key = 'register_info_'. (string)microtime(true) . (string)mt_rand(100000,99999999);
                     $key = md5($key);
                     $cache->set($key, $jsonInfo, 1800);
                     return $this->redirect(['/passport/default/register-check','t'=>$key]);
                }
            }

            return $this->render('register', [
                'model' => $model,
            ]);
        }
        return $this->render('register', [
            'model' => $model,
        ]);
    }


    public function actionRegisterCheck()
    {
        $request = Yii::$app->request;
        $t = $request->get('t');
        $returnUrl = $request->get('return');
        if ($t) {
            $cache = Yii::$app->cache;
            $jsonInfo = $cache->get($t);
            if ($jsonInfo) {
                $registerInfo = Json::decode($jsonInfo);
                $username = $registerInfo['username'];
                if ($request->isPost) {
                    $code = $request->post('code', 0);
                    $registerInfo['code'] = $code;
                    $user = $this->signup($registerInfo);
                    if ($user) {
                        Yii::$app->user->login($user, 1800);
                    }
                    return $this->redirect(['/']);
                }
                return $this->render('registercheck', [
                    'username' => $username,
                    'privateUsername' => \app\services\User::privateAccount($username),
                ]);
            }
        }
        throw new NotFoundHttpException('页面停留时间过长，请重新填写注册信息');
    }

    private function signup($registerInfo)
    {
        $model = new RegisterForm(['scenario'=>'registerCheck']);
        $model->username = $registerInfo['username'];
        $model->password = $registerInfo['password'];
        $model->confirmPassword = $registerInfo['password'];
        $model->smsCode = $registerInfo['code'];
        $model->setRegSource(1);
        if ($model->validate()) {
            return $model->register();
        }
        return false;
    }
}
