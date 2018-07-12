<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/8
 * Time: 下午6:12
 */

namespace app\modules\api\controllers;

use app\helpers\Rename;
use yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;
use app\models\User;
use app\helpers\Brower;

class BaseController extends Controller
{
    public $userId=0;
    public $token = '';
    public $tokenSource;
    public $userInfo;
    public $from;
    public $appDirUrl;
    public $enableCsrfValidation = false;
    public $openKey = '1edeb98a851ffd1966a3895fb8b5ea95';
    public $version;

    public function init()
    {
        parent::init();
        $request = Yii::$app->request;
        $token = $request->get('token');
        $tokenSource = $request->get('tokenSource');
        if (!$token) {
            $token = $request->cookies->getValue('_utoken');
        }
        $this->tokenSource = $tokenSource;
        $this->version = $request->get('version');
        $this->from = Brower::whereFrom(); // 来源判断  是否滴滴
        if ($this->from == 2) {
            $this->appDirUrl = 'http://www.'.DOMAIN.'/didi_app/';
        } else {
            $this->appDirUrl = 'http://www.'.DOMAIN.'/app/';
        }
        $type = in_array($tokenSource,['__ios__','__android__']) ? 1 : null;
        if ($token) {
            $user = User::findIdentityByAccessToken($token,$type);
            if ($user) {
                $this->userId = $user->id;
                $this->token = $token;
                $this->userInfo = $user;

                if ($user['status'] == 1) {
                    $result = ['code'=> '666', 'msg'=>'账户已经冻结','message'=>'账户已经冻结'];
                    $response = \Yii::$app->response;
                    if ($callback = $request->get('callback')) {
                        $return['data'] = $result;
                        $return['callback'] = $callback;
                        $response->format = Response::FORMAT_JSONP;
                    } else if($response->format!=Response::FORMAT_RAW){
                        $response->format = Response::FORMAT_JSON;
                    }
                    $response->send();
                    Yii::$app->end();
                }
            }
        }
    }

    public function afterAction($action, $result)
    {
        $request = \Yii::$app->request;
        $response = \Yii::$app->response;
        $return = parent::afterAction($action, $result);
        if ($callback = $request->get('callback')) {
            $return['data'] = $result;
            $return['callback'] = $callback;
            $response->format = Response::FORMAT_JSONP;
        } else if($response->format!=Response::FORMAT_RAW){
            $response->format = Response::FORMAT_JSON;
        }
        $response->on(Response::EVENT_AFTER_PREPARE, [$this, 'replaceTextToDidi']);
        return $return;
    }

    public function checkValidation()
    {
        $request = Yii::$app->request;
        $deviceId = $request->get('deviceId');
        $time = $request->get('time');
        $sign = $request->get('sign');
        if (empty($deviceId) || empty($time) || empty($sign)) {
            return false;
        }
        $countSign = md5($this->openKey . $time . $deviceId . $this->userId);
        if ($sign != $countSign) {
            return false;
        }
        return true;
    }

    public function replaceTextToDidi()
    {
        $response = \Yii::$app->response;
        $response->content = Rename::duobao($response->content);
    }
}
