<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/23
 * Time: 17:03
 */

namespace app\modules\member\controllers;

use app\services\Member;
use app\models\Group;
use app\models\GroupTopic;
use app\models\GroupTopicComment;
use app\services\User;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\Image;
use app\helpers\DateFormat;
use app\models\User as ModelUser;
use app\models\UserMessage;

class GroupController extends BaseController
{
    public function actionIndex()
    {
        $id = \Yii::$app->user->id;
        $where = ' where a.user_id = '.$id;
        $conn = \Yii::$app->db;
        $sql = "SELECT  b.* FROM `group_users` as a left join `groups` as b on a.group_id = b.id".$where." order by a.id desc";

        $command = $conn->createCommand($sql);
        $joinGroup = $command->queryAll();
        foreach($joinGroup as $key => $val){
            $joinGroup[$key]['picture'] = Image::getGoupIconUrl($val['picture']);
        }

        return $this->render('index', [
            'joinGroup' => $joinGroup,
        ]);
    }

    public function actionTopic()
    {
        $query = GroupTopic::find();

        $countQuery = clone $query;
        $user_id = \Yii::$app->user->id;
        $where['user_id'] = $user_id;

        $count = $countQuery->where($where)->count();
        $pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' =>10 ]);
        $topicList = $query->where($where)->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();

        foreach($topicList as $key => $val){
            $topicList[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
        }

        $replyList = GroupTopicComment::getTopicMessage('', '0', 0, $user_id);

        $groups = Group::find()->all();
        $groups = ArrayHelper::map($groups, 'id', 'name');

        return $this->render('topic', [
            'list' => $topicList,
            'topicNum' => $count,
            'replyNum' => $replyList['pagination'],
            'pagination' => $pagination,
            'groups' => $groups,
            'topic' => 'topic'
        ]);
    }

    public function actionComment()
    {
        $user_id = \Yii::$app->user->id;

        $topicNum = GroupTopic::find()->where(['status'=>1, 'user_id'=>$user_id])->count();
        $topics = GroupTopic::getTopic();
        $groups = Group::find()->all();
        $groups = ArrayHelper::map($groups, 'id', 'name');
        $replyList = GroupTopicComment::getTopicMessage('', '0', 0, $user_id);
        foreach($replyList['list'] as $key => $val){
            $replyList['list'][$key]['created_at'] = DateFormat::formatTime($val['created_at']);
            $replyList['list'][$key]['message'] = GroupTopicComment::commentDeal($val['message']);
        }

        return $this->render('topic', [
            'replyList' => $replyList['list'],
            'topicNum' => $topicNum,
            'replyNum' => $replyList['pagination'],
            'pagination' => $replyList['pagination'],
            'groups' => $groups,
            'topics' => $topics,
            'reply' => 'reply'
        ]);
    }

    public function actionTopicComment()
    {
        $user_id = \Yii::$app->user->id;
        $conn = \Yii::$app->db;
        UserMessage::updateAll(['view'=>1], ['to_userid'=>$user_id, 'type'=>2]);

        $countsql = 'select count(*) as total from ((select g.user_id, t.message as subject, g.message, g.topic_id from group_topic_comments g left join group_topic_comments t on g.reply_floor=t.floor and
        g.topic_id=t.topic_id where g.reply_userid='.$user_id.') union (select  t.user_id, g.subject, t.message, t.topic_id from group_topics g left
         join group_topic_comments t on t.topic_id=g.id where g.user_id = '.$user_id.' and t.is_topic=0)) as a';
        $command = $conn->createCommand($countsql);
        $count = $command->queryOne();

        $pagination = new Pagination(['totalCount' => $count['total'], 'defaultPageSize' =>10 ]);

        $sql = 'select * from ((select g.user_id, t.message as subject, t.created_at, g.message, g.topic_id from group_topic_comments g left join group_topic_comments t on g.reply_floor=t.floor and
        g.topic_id=t.topic_id where g.reply_userid='.$user_id.') union (select  t.user_id, g.subject, t.created_at, t.message, t.topic_id from group_topics g left
         join group_topic_comments t on t.topic_id=g.id where g.user_id = '.$user_id.' and t.is_topic=0 order by t.id desc)) as a order by created_at desc limit '.$pagination->offset.', '.$pagination->limit;
        $command = $conn->createCommand($sql);
        $find = $command->queryAll();

        $return = [];
        foreach($find as $key => $val){
            $return[$key]['user_id'] = User::baseInfo($val['user_id']);
            $return[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
            $return[$key]['subject'] = GroupTopicComment::commentDeal($val['subject']);
            $return[$key]['message'] = GroupTopicComment::commentDeal($val['message']);
            $return[$key]['topic_id'] = $val['topic_id'];
        }

        return $this->render('comment', [
            'list' => $return,
            'pagination' => $pagination
        ]);
    }

    //编辑话题
    public function actionEdit()
    {
        $topicId = \Yii::$app->request;

        $id = $topicId->get('editId');
        $topic['subject'] = GroupTopic::findOne($id);
        $topic['content'] = GroupTopicComment::findOne(['topic_id'=>$id]);
        if(empty($topic)){
            throw new NotFoundHttpException("页面未找到");
        }

        $post = \Yii::$app->request;
        if($post->isPost){
            $postTopic = $post->post('Topic');
            $topic['subject']->subject = $postTopic['title'];
            $topic['content']->message = $postTopic['content'];
            if($topic['subject']->validate() && $topic['content']->validate()){
                $topic['subject']->save();
                $topic['content']->save();
                return $this->redirect(['group/topic']);
            }
        }

        return $this->render('edit', [
            'topic' => $topic,
        ]);

    }

    /*
     * 删除话题
     * **/
    public function actionDel()
    {
        $id = \Yii::$app->request->get('delId');
        $topicSubject = GroupTopic::findOne($id);
        $username = ModelUser::userName($topicSubject['user_id']);
        GroupTopicComment::deleteAll(['topic_id'=>$id]);
        $member = new Member(['id' => $topicSubject['user_id']]);
        $member->editExperience(-50, 5, '删除话题');
        if($topicSubject->delete()){
            $group = Group::findOne(['id'=>$topicSubject['group_id']]);
            $group->topic_count = $group['topic_count'] - 1;
            $group->save();
            return 0;
        }else{
            return 1;
        }
    }

    //删除评论
    public function actionDelPost()
    {
        $request = \Yii::$app->request;
        $user_id = \Yii::$app->user->id;
        if($request->isGet){
            $id = $request->get('id');
            $where['user_id'] = $user_id;
            $where['id'] = $id;
            $model = GroupTopicComment::find()->where($where)->one();
            if(!$model){
                throw new NotFoundHttpException("页面未找到");
            }else{
                $lastReply = GroupTopic::find()->where(['id'=>$model['topic_id'], 'last_comment_uid'=>$model['user_id']])->one();
                if($lastReply){
                    $errorLimit = $lastReply['last_comment_time'] - $model['created_at'];
                    if($errorLimit < 10){
                        $data = GroupTopicComment::find()->where('topic_id=:topicId', [':topicId'=>$model['topic_id']])->andWhere('is_topic=0')->andWhere('id!=:id', [':id'=>$model['id']])->orderBy('id desc')->one();

                        if($data['id']){
                            $lastReply->last_comment_uid = $data['user_id'];
                            $lastReply->last_comment_time = $data['created_at'];
                            $lastReply->save();
                        }else{
                            $lastReply->last_comment_uid = 0;
                            $lastReply->last_comment_time = 0;
                            $lastReply->save();
                        }
                    }
                }
                $member = new Member(['id' => $user_id]);
                $member->editExperience(-10, 7, '删除回复');
                if($model->delete()){
                    return 1;
                }

            }
        }
    }


}