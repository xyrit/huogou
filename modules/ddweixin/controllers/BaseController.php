<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/18
 * Time: 下午4:22
 */
namespace app\modules\ddweixin\controllers;

use app\helpers\Brower;
use app\models\MpUser;
use yii\web\Controller;
use Yii;

class BaseController extends Controller
{


    public $enableCsrfValidation = false;

    public $userId=0;
    public $token = '';


    public function init()
    {
        $user = \Yii::$app->user;
        $this->token = $user->accessToken;
        $request = Yii::$app->request;
        \Yii::$app->user->loginUrl = ['/ddweixin/passport/login'];
        if ($request->isGet) {
            if ($user->isGuest) {
                $openId = $this->getOpenId();
                $this->loginByOpenId($openId);
            } else {
                $status = $user->identity->status;
                if ($status==1) {
                    echo '账户被冻结';
                    Yii::$app->end();
                }
            }
        }
    }

    private $_jsSignPackage;
    public function getJsSdkSignPackage()
    {
        if ($this->_jsSignPackage) {
            return $this->_jsSignPackage;
        }

        $wechatConfig = require (\Yii::getAlias('@app/config/didi_wechat.php'));
        $wechat = \Yii::createObject($wechatConfig);
        $jsApiConfig = $wechat->jsApiConfig();

        return $this->_jsSignPackage =  [
            'appId'=>$jsApiConfig['appId'],
            'nonceStr'=>$jsApiConfig['nonceStr'],
            'timestamp'=>$jsApiConfig['timestamp'],
            'signature'=>$jsApiConfig['signature'],
        ];
    }



    public function getOpenId()
    {
        if (!Brower::isMcroMessager()) {
            return false;
        }
        $session = Yii::$app->session;
        $key = '__wechat__openid__';
        $openId = $session->get($key);
        if (!$openId) {
            $openId = $this->getOpenIdFromMp();
            $session->set($key, $openId);
        }
        return $openId;

    }

    public function loginByOpenId($openId)
    {
        $request = Yii::$app->request;
        $cookies = $request->cookies;
        $logout = $cookies->getValue('logout');
        if ($logout || !$openId) {
            return false;
        }
        $mpUser = MpUser::findOne(['open_id'=>$openId]);
        if ($mpUser) {
            $user = \app\models\User::findOne($mpUser->user_id);
            $logined =  Yii::$app->user->login($user, 1800);
            return $logined;
        }
        return false;
    }

    public function getOpenIdFromMp()
    {
        $wechatConfig = require (\Yii::getAlias('@app/config/didi_wechat.php'));
        $wechat = \Yii::createObject($wechatConfig);

        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $redirectUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $url = $wechat->getOauth2AuthorizeUrl($redirectUrl,'STATE');
            Yii::$app->response->redirect($url);
            Yii::$app->end();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $info = $wechat->getOauth2AccessToken($code);
            return isset($info['openid']) ? $info['openid']: false;
        }
    }

}