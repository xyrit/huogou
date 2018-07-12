<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/3
 * Time: 下午6:11
 */
namespace app\modules\passport\controllers;

use app\helpers\Message;
use app\services\Member;
use app\services\User;
use yii\captcha\CaptchaValidator;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FindpasswordController extends Controller
{

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $account = $request->post('account');
            $VCode = $request->post('vcode');
            $captchaValidator = new CaptchaValidator();
            $captchaValidator->captchaAction = '/api/user/captcha';
            $valid = $captchaValidator->validate($VCode);
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            if ($valid) {
                $send = User::sendCode($account, 2,true);
                $captchaAction = $captchaValidator->createCaptchaAction();
                $captchaAction->getVerifyCode(true);
                $userInfo = ['account'=>$account];
                $key = 'findpassword_'. (string)microtime(true) . (string)mt_rand(100000,999999);
                $key = md5($key);
                $cache = \Yii::$app->cache;
                $cache->set($key, $userInfo, 3600);
                $url = Url::to(['findpassword/verify', 'key'=>base64_encode($key)]);
                return ['error'=>0, 'url'=>$url];
            } else {
                return ['error'=>1, 'message'=>'验证码不正确'];
            }
        }
        return $this->render('index', [

        ]);
    }

    public function actionVerify()
    {
        $request = \Yii::$app->request;

        if ($request->isAjax) {
            $key = $request->post('key');
            $account = $request->post('account');
            $code = $request->post('code');
            $key = base64_decode($key);
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            if ($key) {
                $cache = \Yii::$app->cache;
                $userInfo = $cache->get($key);
                if ($userInfo && !empty($userInfo['account']) && $userInfo['account']==$account) {
                    $sendCode = User::getCode($account, 2);
                    if ($sendCode && $sendCode==$code) {
                        $cache->delete($key);
                        $key = 'findpassword_'. (string)microtime(true) . (string)mt_rand(100000,999999);
                        $key = md5($key);
                        $cache->set($key, $userInfo, 1800);

                        $url = Url::to(['findpassword/reset', 'key'=>base64_encode($key)]);
                        return ['error'=>0, 'url'=>$url];
                    }
                }
            }
            return ['error'=>1];
        } else {
            $key = $request->get('key');
            $key = base64_decode($key);
            if ($key) {
                $cache = \Yii::$app->cache;
                $userInfo = $cache->get($key);
                if ($userInfo && !empty($userInfo['account'])) {

                    return $this->render('verify', [
                        'account' => $userInfo['account'],
                        'privacyAccount' => User::privateAccount($userInfo['account']),
                        'key' => base64_encode($key),
                    ]);
                }
            }
        }
        throw new NotFoundHttpException('页面不合法');
    }

    public function actionReset()
    {

        $request = \Yii::$app->request;

        if ($request->isAjax) {
            $key = $request->post('key');
            $account = $request->post('account');
            $pwd = $request->post('password');
            $confirmPwd = $request->post('confirmPassword');
            $key = base64_decode($key);
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            if ($key && !empty($pwd) && $pwd==$confirmPwd) {
                $cache = \Yii::$app->cache;
                $userInfo = $cache->get($key);
                if ($userInfo && !empty($userInfo['account']) && $userInfo['account']==$account) {
                    $member = new Member(['account'=>$userInfo['account']]);
                    $change = $member->changePassword($pwd);
                    $url = Url::to(['findpassword/success']);
                    return $change ? ['error'=>0, 'url'=>$url] : ['error'=>1];
                }
            }
            return ['error'=>1];
        } else {
            $key = $request->get('key');
            $key = base64_decode($key);
            if ($key) {
                $cache = \Yii::$app->cache;
                $userInfo = $cache->get($key);
                if ($userInfo && !empty($userInfo['account'])) {

                    return $this->render('reset', [
                        'key' => base64_encode($key),
                        'account' => $userInfo['account']
                    ]);
                }
            }
        }
        throw new NotFoundHttpException('页面不合法');
    }

    public function actionSuccess()
    {

        return $this->render('success', [

        ]);
    }

}