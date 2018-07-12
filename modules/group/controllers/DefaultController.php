<?php

namespace app\modules\group\controllers;

use app\models\Group;
use app\models\GroupUser;
use app\models\GroupTopic;
use app\models\GroupTopicComment;
use app\services\Member;
use yii\web\NotFoundHttpException;
use app\services\User;
use app\modules\admin\models\Keyword;
use app\helpers\DateFormat;
use app\models\User as ModelUser;
use app\models\Image as ModelImage;

class DefaultController extends BaseController
{
    /**
     * 伙购圈首页
     */
    public function actionIndex()
    {
        $groups = Group::find()->all();
        foreach($groups as &$val){
            $count = GroupTopic::find()->where(['group_id'=>$val['id'], 'status'=>1])->count();
            $val['topic_count'] = $count;
        }

        return $this->render('index', [
            'groups' => $groups,
        ]);
    }

    /**
     * 圈子详情页
     */
    public function actionView()
    {
        $topicId = \Yii::$app->request->get('t');
        if (!isset($topicId)) $topicId = 1;
        $groupId = \Yii::$app->request->get('id');

        $groupDetail = Group::find()->where(['id' => $groupId])->one();
        if (!$groupDetail) {
            throw new NotFoundHttpException("页面未找到");
        }

        $topic = GroupTopic::getListByType($groupId, $topicId, $limit = 10, $verify_status = $groupDetail['verify_status']);
        $topicArr = [];
        foreach($topic['list'] as $key => $val){
            $topicArr[$key] = $val;
            $message = GroupTopicComment::find()->where(['topic_id'=>$val['id']])->one();
            $msgcontent = GroupTopicComment::messageDeal($message['id']);
            $topicArr[$key]['message'] = $msgcontent;
            $topicArr[$key]['commentuser'] = User::baseInfo($val['last_comment_uid']);
            if(isset($msgcontent['images']) && !empty($msgcontent['images'])){
                $topicArr[$key]['img'] = 1;
            }else{
                $topicArr[$key]['img'] = 0;
            }
        }

        $uid = \Yii::$app->user->id;
        $isJoin = GroupUser::isJoin($uid, $groupId);
        $groupAdmin = ModelUser::find()->where(['or', 'email="'.$groupDetail['adminuser'].'"', 'phone="'.$groupDetail['adminuser'].'"'])->one();
        $groupDetail['adminuser'] = User::baseInfo($groupAdmin['id']);
        $groupAvatar = ModelImage::getUserFaceUrl($groupDetail['adminuser']['avatar'], 160);
        $groupDetail['created_at'] = DateFormat::formatTime($groupDetail['created_at']);
        $getGroup = Group::getGroup();
        $topicCount = GroupTopic::find()->where(['group_id'=>$groupDetail['id'], 'status'=>1])->count();
        $groupDetail['topic_count'] = $topicCount;
        $groupDetail['digest_count'] = GroupTopic::find()->where(['group_id'=>$groupDetail['id'], 'status'=>1])->andWhere('is_digest = 1')->count();

        return $this->render('view', [
            'detail' => $groupDetail,
            'topic' => $topicArr,
            'pagination' => $topic['pagination'],
            'isJoin' => $isJoin,
            'topicId' => $topicId,
            'getGroup' => $getGroup,
            'avatar' => $groupAvatar
        ]);
    }

    /*
     * 新增话题
     * */
    public function actionAddTopic()
    {
        $request = \Yii::$app->request;

        if ($request->isPost) {
            $post = $request->post();
            $groupDetail = Group::find()->where(['id' => $post['Topic']['groupId']])->one();
            $post['Topic']['user_id'] = \Yii::$app->user->id;
            if($groupDetail['verify_status'] == 1){
                $topicStatus = 1;
                $topicMessageStatus = 1;
            }else{
                $topicStatus = 0;
                $topicMessageStatus = 0;
            }
            $titleKey = Keyword::keywords($post['Topic']['title']);
            $contentKey = Keyword::keywords($post['Topic']['content']);
            if($titleKey == 1 || $contentKey == 1){
                return 0;
            }

            $topicAddId = GroupTopic::addTopic($post, $topicStatus);
            if ($topicAddId) {
                GroupTopicComment::addTopicPost($topicAddId, $post, 1, 0, $topicMessageStatus);
                if($topicStatus == 1){
                    Group::groupTopciCount($groupDetail['id'], $topicStatus);
                    $uid = \Yii::$app->user->id;
                    $member = new Member(['id' => $uid]);
                    $member->editExperience(50, 5, '发表话题成功');
                }
                if($groupDetail['verify_status'] == 1)return $topicAddId;
                else return 2;
            }
        }

    }

    //加入圈子
    public function actionJoinGroup()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $id = $request->get('id');
            $group = Group::findOne($id, 'group_closed=0');
            if($group){
                $where['group_id'] = $group['id'];
                $where['user_id'] = \Yii::$app->user->id;
                $model = GroupUser::find()->where($where)->one();
                if($model){
                    throw new NotFoundHttpException("用户已加入");
                }else{
                    $model = new GroupUser();
                    $model->group_id = $id;
                    $model->user_id = \Yii::$app->user->id;
                    $model->created_at = time();
                    if($model->validate()){
                        $model->save();
                        $group->user_count = $group['user_count'] + 1;
                        $group->save();
                        return 1;
                    }
                }
            }else{
                throw new NotFoundHttpException("页面未找到");
            }
        }
    }

    //退出圈子
    public function actionQuit()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $id = $request->get('id');
            $uid = \Yii::$app->user->id;
            $model = GroupUser::isJoin($uid, $id);
            $group = Group::findOne($id);
            if($model){
                $model->delete();
                $group->user_count = $group['user_count'] - 1;
                $group->save();
                return 1;
            }else{
                throw new NotFoundHttpException("页面未找到");
            }
        }
    }

    //验证话题关键字
    public function actionCheck()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $post = $request->get('title');
            $model = Keyword::findOne(['type'=>0]);
            $arr = explode('|', $model['content']);
            foreach($arr as $val){
                if(strstr($post, $val)){
                    return $val;exit;
                }
            }
        }
    }

    //验证回帖关键字
    public function actionCheckComment()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $post = $request->get('content');
            $model = Keyword::findOne(['type'=>1]);
            $arr = explode('|', $model['content']);
            foreach($arr as $val){
                if(strstr($post, $val)){
                    return $val;exit;
                }
            }
        }
    }
}
