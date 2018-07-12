<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/30
 * Time: 14:40
 */
namespace app\modules\group\controllers;

use app\models\GroupTopicComment;
use app\models\GroupTopic;
use app\helpers\Ip;
use app\models\Image;
use app\models\Friend;
use app\services\User;
use app\models\GroupUser;
use app\helpers\DateFormat;

class BaseController extends \app\controllers\BaseController
{
    public function render($view, $params = [])
    {
        $uid = \Yii::$app->user->id;

        //个人信息
        $userBaseInfo = User::baseInfo($uid);
        $userBaseInfo['avatar'] = Image::getUserFaceUrl($userBaseInfo['avatar'], 80);

        //用户加入的圈子
        $joinGroup = GroupUser::joinGroup($uid);

        $topicNum = GroupTopic::topicNum($uid);
        $commentNum = GroupTopicComment::commentNum($uid);

        $params['joinGroup'] = $joinGroup;
        $params['topicNum'] = $topicNum;
        $params['commentNum'] = $commentNum;
        $params['user_info'] = $userBaseInfo;

        return parent::render($view, $params);
    }
}