<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/19
 * Time: 下午1:32
 */
namespace app\modules\member\controllers;

use app\models\UserMessage;
use app\services\User;
use Yii;
use app\models\UserSystemMessage;

class BaseController extends \app\controllers\BaseController
{

    public function init()
    {
        parent::init();
        $user = \Yii::$app->user;
        if ($user->isGuest) {
            $user->loginRequired();
            Yii::$app->end();
        } else {
            $status = $user->identity->status;
            if ($status==1) {
                echo '账户被冻结';
                Yii::$app->end();
            }
        }
    }

    public function render($view, $params = [])
    {
        $userId = Yii::$app->user->id;

        $sysMsg = UserSystemMessage::find()->where(['to_userid'=>$userId, 'status'=>0])->count();
        $params['sysMsgCount'] = $sysMsg;

        $params['friendMsg'] = UserMessage::find()->where(['to_userid'=>$userId, 'type'=>1, 'view'=>0])->count();
        $params['commentMsg'] = UserMessage::find()->where(['to_userid'=>$userId, 'type'=>2, 'view'=>0])->count();
        $params['privMsg'] = UserMessage::find()->where(['to_userid'=>$userId, 'type'=>3, 'view'=>0])->count();
        $params['totalMsg'] = $sysMsg + $params['friendMsg'] + $params['commentMsg'] + $params['privMsg'];

        $user_id = \Yii::$app->user->id;
        $user = User::baseInfo($user_id);
        $params['level'] = $user['level'];
        $params['h'] = date("G");
        $params['username'] = $user['username'];
        $params['s_phone'] = $user['phone'] ? User::privatePhone($user['phone']) : '';

        return parent::render($view, $params);
    }

}