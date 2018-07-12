<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午4:19
 */
namespace app\modules\api\controllers;

use app\models\GroupTopicComment;
use app\models\GroupTopic;
use app\models\Group;
use app\models\GroupUser;
use yii\helpers\ArrayHelper;
use app\services\User;
use app\helpers\DateFormat;
use app\models\Image;
use app\models\Friend;

class TopicController extends BaseController
{
    //话题列表
    public function actionTopicList()
    {
        $condition = \Yii::$app->request->get('t');
        if (!isset($condition)) $condition = 3;
        $groupTopic = GroupTopic::getListByType($g = '', $condition);
        $getGroup = Group::getGroup();

        $return = [];
        foreach($groupTopic['list'] as $key => $val){
            $return[$key]['title'] = $val['subject'];
            $return[$key]['id'] = $val['id'];
            $return[$key]['top'] = $val['top'];
            $return[$key]['time'] = $val['created_at'];
            $return[$key]['digest'] = $val['digest'];
            $return[$key]['comment'] = $val['comment_count'];
            $return[$key]['view'] = $val['view_count'];
            $return[$key]['group'] = $getGroup[$val['group_id']];
            $return[$key]['username'] = $val['username']['username'];
            $return[$key]['home'] = $val['username']['home_id'];
            $message = GroupTopicComment::find()->where(['topic_id'=>$val['id'], 'is_topic'=>1])->one();
            $message['message'] = GroupTopicComment::topicContentDeal($message['message']);
            preg_match('/<img.+src=\"?(.+(grouppic)?.+)\"?.+>/i',$message['message'],$match);
            if(!empty($match)){
                if(strstr($match[1], 'grouppic')){
                    $return[$key]['city'] = 1;
                }else{
                    $return[$key]['city'] = 0;
                }
            }else{
                $return[$key]['city'] = 0;
            }
        }

        return $return;
    }

    //热门话题
    public function actionRightTopicList()
    {
        $groupTopic = GroupTopic::getListByType($g = '', $topicId = '2', $limit = '5');

        $getGroup = Group::getGroup();
        $uid = \Yii::$app->user->id;
        $return = [];
        foreach($groupTopic['list'] as $key => $val){
            $return[$key]['id'] = $val['id'];
            $return[$key]['group'] = $getGroup[$val['group_id']];
            $return[$key]['comment'] = $val['comment_count'];
            $return[$key]['title'] = $val['subject'];
            $return[$key]['avatar'] = $val['user_avatar'];
            $return[$key]['username'] = $val['username']['username'];
            $return[$key]['home'] = $val['username']['home_id'];
            $return[$key]['grade_name'] = $val['username']['level']['name'];
            $return[$key]['grade_pic'] = $val['username']['level']['pic'];
            $return[$key]['city'] = $val['city'];
            $return[$key]['intro'] = $val['username']['intro'];
            $return[$key]['friend'] = $val['user_id'] == $uid ? '1' : (isset($val['isFriend']) ? $val['isFriend'] : '');
            $return[$key]['u'] = $uid;
        }

        return $return;
    }

    //活跃成员
    public function actionActiveUser()
    {
        $activeUser = [];
        $uid = \Yii::$app->user->id;
        $findUser = GroupTopicComment::activeUser();

        $userids = ArrayHelper::getColumn($findUser, 'user_id');
        $user = User::allInfo($userids);

        foreach($findUser as $key => $val){
            $all = $user[$val['user_id']];
            $activeUser[$key]['username'] = $all['username'];
            $activeUser[$key]['home'] = $all['home_id'];
            $activeUser[$key]['grade_name'] = $all['level']['name'];
            $activeUser[$key]['grade_pic'] = $all['level']['pic'];
            $activeUser[$key]['intro'] = $all['intro'];
            $activeUser[$key]['city'] = $all['hometown'];
            if($uid){
                if ($uid == $val['user_id']) {
                    $activeUser[$key]['friend'] = 1;
                } else {
                    $activeUser[$key]['friend'] = Friend::find()->where(['and', 'user_id='.$val['user_id'], 'friend_userid='.$uid])->count();
                }
            }
            $activeUser[$key]['avatar'] = Image::getUserFaceUrl($all['avatar'], 160);
            $activeUser[$key]['u'] = $uid;
        }

        return $activeUser;
    }

    public function actionNewJoin($groupId)
    {
        $newJoin = [];
        $findJoin = GroupUser::newJoinGroup($groupId);
        $uid = \Yii::$app->user->id;

        $userids = ArrayHelper::getColumn($findJoin, 'user_id');
        $user = User::allInfo($userids);

        foreach($findJoin as $key => $val){
            $all = $user[$val['user_id']];
            $newJoin[$key]['username'] = $all['username'];
            $newJoin[$key]['home'] = $all['home_id'];
            $newJoin[$key]['grade_name'] = $all['level']['name'];
            $newJoin[$key]['grade_pic'] = $all['level']['pic'];
            $newJoin[$key]['intro'] = $all['intro'];
            $newJoin[$key]['city'] = $all['hometown'];
            if($uid){
                $newJoin[$key]['friend'] = Friend::find()->where(['and', 'user_id='.$val['user_id'], 'friend_userid='.$uid])->count();
            }
            $newJoin[$key]['avatar'] = Image::getUserFaceUrl($all['avatar'], 160);
            $newJoin[$key]['u'] = $uid;
        }
        return $newJoin;
    }

    //圈子动态
    public function actionGroupNew($groupId)
    {
        $newTopic = [];
        $uid = \Yii::$app->user->id;

        $findTopic = GroupTopic::getGroupNews($groupId);
        $userids = ArrayHelper::getColumn($findTopic, 'user_id');
        $user = User::allInfo($userids);

        foreach($findTopic as $key=>$val){
            $all = $user[$val['user_id']];
            $newTopic[$key]['username'] = $all['username'];
            $newTopic[$key]['home'] = $all['home_id'];
            $newTopic[$key]['grade_name'] = $all['level']['name'];
            $newTopic[$key]['grade_pic'] = $all['level']['pic'];
            $newTopic[$key]['intro'] = $all['intro'];
            $newTopic[$key]['city'] = $all['hometown'];
            if($uid){
                $newTopic[$key]['friend'] = Friend::find()->where(['and', 'user_id='.$val['user_id'], 'friend_userid='.$uid])->count();
            }
            $newTopic[$key]['avatar'] = Image::getUserFaceUrl($all['avatar'], 160);
            $newTopic[$key]['u'] = $uid;
            $newTopic[$key]['id'] = $val['topic_id'];
            if($uid){
                $newTopic[$key]['isFriend'] = Friend::find()->where(['and', 'user_id='.$val['user_id'], 'friend_userid='.$uid])->count();
            }
            $newTopic[$key]['topic'] = $val['is_topic'];
            $newTopic[$key]['time'] = DateFormat::formatTime($val['created_at']);
            $newTopic[$key]['title'] = $val['subject'];
        }
        return $newTopic;
    }
}