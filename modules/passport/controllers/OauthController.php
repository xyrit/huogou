<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/15
 * Time: ����10:09
 */
namespace app\modules\passport\controllers;

use app\models\Oauth;
use app\models\User;
use app\modules\passport\models\RegisterForm;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OauthController extends Controller
{

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function onAuthSuccess($client)
    {
        $attributes = $client->getUserAttributes();
        $clientId = $client->getId();

        if ($clientId==1) {
            //qq
            $userInfo['source_id'] = $attributes['openid'];
            $userInfo['nickname'] = $attributes['nickname'];
            $userInfo['avatar'] = $attributes['figureurl_qq_2'];
            $userInfo['source'] = $clientId;
            $userInfo['unionid'] = '';
        } elseif($clientId==2) {
            //wechat
            $userInfo['source_id'] = $attributes['openid'];
            $userInfo['nickname'] = $attributes['nickname'];
            $userInfo['avatar'] = $attributes['headimgurl'];
            $userInfo['source'] = $clientId;
            $userInfo['unionid'] = $attributes['unionid'];
        }
        $auth = Oauth::findOne(['source_id'=>$userInfo['source_id'], 'source'=>$userInfo['source']]);
        if ($auth) {
            $user = User::findOne($auth->user_id);
            if ($user) {
                Yii::$app->user->login($user, 1800);
            }
            if (strpos(Yii::$app->request->userAgent, 'MicroMessenger') === false) {
                return $this->redirect(['/']);
            } else {
                return $this->redirect(['/weixin']);
            }
        }
        $userInfoJson = Json::encode($userInfo);
        $cache = Yii::$app->cache;
        $security = Yii::$app->security;
        $key = $security->generateRandomString(32);
        $key = md5(__CLASS__ . '_' . $key);
        $cache->set($key, $userInfoJson, 1800);
        $userInfoEncryptStr = $security->encryptByKey($userInfoJson, $key);
        $url = Url::to(['/passport/oauth/bind', 'key' => base64_encode($key), 'userinfo' => base64_encode($userInfoEncryptStr)]);
        return $this->redirect($url);
    }

    public function actionBind()
    {
        $request = Yii::$app->request;
        $key = $request->get('key');
        $userInfoEncryptStr = $request->get('userinfo');
        $key = base64_decode($key);
        $userInfoEncryptStr = base64_decode($userInfoEncryptStr);
        if ($key && $userInfoEncryptStr) {
            $cache = Yii::$app->cache;
            $userInfoJson = $cache->get($key);
            if ($userInfoJson) {
                $security = Yii::$app->security;
                $userInfoDecryptStr = $security->decryptByKey($userInfoEncryptStr, $key);
                if ($security->compareString($userInfoJson, $userInfoDecryptStr)) {
                    $userInfo = Json::decode($userInfoJson);
                    $model = new RegisterForm(['scenario'=>'register']);
                    if ($request->isPost) {
                        if ($model->load($request->post())  ) {

                            $response = Yii::$app->response;
                            $response->format = Response::FORMAT_JSON;

                            if ($user = User::findByEmail($model->username) or $user = User::findByPhone($model->username) ) {
                                if (!empty($model->password) && $user->validatePassword($model->password)) {
                                    $auth = Oauth::findOne(['source_id'=>$userInfo['source_id'], 'source'=>$userInfo['source']]);
                                    if (!$auth) {
                                        $auth = new Oauth();
                                        $auth->source = $userInfo['source'];
                                        $auth->source_id = $userInfo['source_id'];
                                        $auth->user_id = $user->id;
                                        $auth->save(false);
                                    }
                                    Yii::$app->user->login($user, 1800);
                                    return ['error'=>0, 'url'=>Url::to(['/passport/oauth/success'])];
                                } else {
                                    $model->addError('password', '登录密码错误');
                                    return ['error'=>1, 'message'=>$model->getFirstErrors()];
                                }
                            } else {
                                if ($model->validate()) {
                                    $userInfoVerify = $userInfo;
                                    $userInfoVerify['registerAttributes'] = $model->attributes;
                                    $userInfoVerify['rebindUrl'] = $request->absoluteUrl;
                                    $userInfoVerifyJson = Json::encode($userInfoVerify);
                                    $verifyKey = $security->generateRandomString(32);
                                    $verifyKey = md5(__CLASS__ . '_' . $verifyKey);
                                    $cache->set($verifyKey, $userInfoVerifyJson, 1800);
                                    $userInfoEncryptStr = $security->encryptByKey($userInfoVerifyJson, $verifyKey);
                                    \app\services\User::sendCode($model->username, 1, false);
                                    return ['error'=>0, 'url'=>Url::to(['oauth/verify', 'key'=>base64_encode($verifyKey), 'userinfo'=>base64_encode($userInfoEncryptStr) ])];
                                } else {
                                    return ['error'=>1, 'message'=>$model->getFirstErrors()];
                                }
                            }


                        }
                    }

                    return $this->render('bind', [
                        'userInfo'=>$userInfo,
                        'model'=>$model
                    ]);
                }
            }

        }

        throw new NotFoundHttpException('关联信息过期，请重新关联登录');

    }

    public function actionVerify()
    {
        $request = Yii::$app->request;
        $key = $request->get('key');
        $userInfoEncryptStr = $request->get('userinfo');
        $key = base64_decode($key);
        $userInfoEncryptStr = base64_decode($userInfoEncryptStr);
        if ($key && $userInfoEncryptStr) {
            $cache = Yii::$app->cache;
            $userInfoJson = $cache->get($key);
            if ($userInfoJson) {
                $security = Yii::$app->security;
                $userInfoDecryptStr = $security->decryptByKey($userInfoEncryptStr, $key);
                if ($security->compareString($userInfoJson, $userInfoDecryptStr)) {
                    $userInfo = Json::decode($userInfoJson);
                    $registerInfo = $userInfo['registerAttributes'];
                    $rebindUrl = $userInfo['rebindUrl'];
                    $model = new RegisterForm(['scenario'=>'registerCheck']);
                    $model->setAttributes($registerInfo, false);
                    $model->setRegSource(1);
                    $account = $model->username;
                    if ($request->isPost) {
                        $code = $request->post('code');
                        $model->smsCode = $code;
                        if ($model->validate() && $model->register()) {
                            $user = User::findByAccount($account);
                            $auth = Oauth::findOne(['source_id'=>$userInfo['source_id'], 'source'=>$userInfo['source']]);
                            if (!$auth) {
                                $auth = new Oauth();
                                $auth->source = $userInfo['source'];
                                $auth->source_id = $userInfo['source_id'];
                                $auth->user_id = $user->id;
                                $auth->save(false);
                            }
                            Yii::$app->user->login($user, 1800);
                            return $this->redirect(['/passport/oauth/success']);
                        }
                    }

                    return $this->render('verify', [
                        'key' => $key,
                        'account'=>$account,
                        'privacyAccount' => \app\services\User::privateAccount($account),
                        'rebindUrl'=>$rebindUrl,
                    ]);
                }
            }

        }

        throw new NotFoundHttpException('关联信息过期，请重新关联登录');
    }

    public function actionSuccess()
    {
        return $this->render('success', [

        ]);
    }

}