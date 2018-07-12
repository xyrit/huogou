<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/19
 * Time: 14:54
 */


namespace app\modules\group\controllers;

use app\models\Group;
use app\models\GroupTopic;
use app\models\GroupTopicComment;
use app\models\GroupUser;
use app\models\UserMessage;
use app\modules\admin\models\Keyword;
use app\services\User;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\Image;
use app\helpers\DateFormat;
use app\modules\image\models\UploadForm;
use yii\web\UploadedFile;
use yii\helpers\Json;
use app\models\User as ModelUser;
use yii\web\NotFoundHttpException;
use app\services\Member;

class TopicController extends BaseController
{
    public function actionIndex()
    {
        $query = GroupTopic::find();

        $countQuery = clone $query;
        $where['user_id'] = 1;  //用户id

        $count = $countQuery->where($where)->count();
        $pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' =>10 ]);
        $topicList = $query->where($where)->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();
        $topics = GroupTopic::getTopic();

        $replyList = GroupTopicComment::getTopicMessage('', '0', '1');

        $groups = Group::find()->all();
        $groups = ArrayHelper::map($groups, 'id', 'group_name');

        return $this->render('index', [
            'list' => $topicList,
            'replyList' => $replyList,
            'pagination' => $pagination,
            'groups' => $groups,
            'topics' => $topics,
        ]);
    }

    //话题详情页面
    public function actionView()
    {

        $request = \Yii::$app->request;
        $id = $request->get('id');

        $uid = \Yii::$app->user->id;
        $info = User::baseInfo($uid);
        $userAvatar = Image::getUserFaceUrl($info['avatar'], 160);

        $topicDetail = GroupTopic::findOne(['id'=>$id, 'status'=>1]);
        if($topicDetail){
            $groupDetail = Group::findOne($topicDetail['group_id']);
        }else{
            throw new NotFoundHttpException("页面未找到");
        }

        $topicDetail->view_count = $topicDetail['view_count'] + 1;
        $topicDetail->save();

        $replyMessage = GroupTopicComment::getTopicMessage($id, $groupDetail['verify_status']);
        $comment = [];
        foreach($replyMessage['list'] as $key=>$val){
            $userInfo = User::baseInfo($val['user_id']);
            $comment[$key]['username'] = $userInfo;
            $comment[$key]['user_avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 80);
            $comment[$key]['reply_floor'] = $val['reply_floor'];
            $comment[$key]['message'] = GroupTopicComment::commentDeal($val['message']);
            $comment[$key]['status'] = $val['status'];
            $comment[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
            $comment[$key]['user_id'] = $val['user_id'];
            $comment[$key]['id'] = $val['id'];
        }

        $mes = GroupTopicComment::findOne(['topic_id'=>$id]);
        $topicMessage = GroupTopicComment::topicContentDeal($mes['message']);
        $img = strpos($topicMessage, "grouppic/org/");
        if($img) $img = 1; else $img = 0;
        $groups = Group::getGroup();
        $isJoin = GroupUser::findOne(['user_id'=>$uid, 'group_id'=>$topicDetail['group_id']]);
        $groupDetail['picture'] = Image::getGoupIconUrl($groupDetail['picture']);
        $groupDetail['created_at'] = DateFormat::formatTime($groupDetail['created_at']);

        $topicDetail['created_at'] = DateFormat::formatTime($topicDetail['created_at']);
        $topicDetail['user_id'] = User::baseInfo($topicDetail['user_id']);
        $topicAvatar = Image::getUserFaceUrl($topicDetail['user_id']['avatar'], 80);

        return $this->render('view', [
            'topicDetail' => $topicDetail,
            'topicMessage'=> $topicMessage,
            'groupDetail' => $groupDetail,
            'replyMessage' => $replyMessage,
            'comment' => $comment,
            'getGroup' => $groups,
            'isJoin' => $isJoin,
            'avatar' => $topicAvatar,
            'userAvatar' => $userAvatar,
            'img' => $img
        ]);
    }

    //发表回复
    public function actionComment()
    {
        $request = \Yii::$app->request;
        $uid = \Yii::$app->user->id;
        $id = $request->post('id');
        $topicDetail = GroupTopic::findOne(['id'=>$id]);
        if($topicDetail){
            $groupDetail = Group::findOne($topicDetail['group_id']);
        }

        if($request->isPost && $groupDetail['comment_closed'] == 0){
            $post = $request->post();
            if(isset($post['floor']) && $post['floor'] != 0){
                $floor = $post['floor'];
                $userId = ModelUser::find()->where(['home_id'=>$post['homeId']])->one();
                $homeId = $userId['id'];
            }else{
                $floor = 0;
                $homeId = 0;
            }

            $post['Topic']['content'] = $post['content'];
            $post['Topic']['user_id'] = $uid;

            if($groupDetail['verify_status'] == 1){
                $commentStatus = 1;
            }else{
                $commentStatus = 0;
            }
            $key = Keyword::keywords($post['Topic']['content']);
            if($key == 1){
                return 2;
            }

            if(GroupTopicComment::addTopicPost($topicDetail['id'], $post, '0', $floor, $commentStatus, $homeId)){
                if($groupDetail['verify_status'] == 1){
                    $topicDetail->comment_count = $topicDetail['comment_count'] + 1;
                    $topicDetail->last_comment_time = time();
                    $topicDetail->last_comment_uid = $uid;
                    $topicDetail->save();
                    UserMessage::addMessage($topicDetail['user_id'], 2, $topicDetail['subject'].'话题被评论');
                    //Message::send(21, $topicDetail['user_id'], ['nickname'=>$username['username'], 'oppositeNickname'=>$commname['username'], 'topicTitle'=>$topicDetail['subject']]);
                    $member = new Member(['id' => $uid]);
                    $member->editExperience(10, 7, '回复成功');
                }
                return 0;
            }else{
                return 1;
            }
        }

    }

    public function actionTest()
    {
        $content = "gfhf[s:0]ghf[s:17]ghgf[s:45]";
        GroupTopicComment::commentDeal($content);
    }

    //删除评论
    public function actionDelComment()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $id = $request->get('id');
            $where['user_id'] = \Yii::$app->user->id;
            $where['id'] = $id;
            $model = GroupTopicComment::find()->where($where)->one();
            if(!$model){
                return 0;
            }else{
                $lastReply = GroupTopic::find()->where(['id'=>$model['topic_id'], 'last_comment_uid'=>$model['user_id']])->one();
                if($lastReply){
                    $errorLimit = $lastReply['last_comment_time'] - $model['created_at'];
                    if($errorLimit < 10){
                        $data = GroupTopicComment::find()->where('topic_id=:topicId', [':topicId'=>$model['topic_id']])->andWhere('is_topic=0')->andWhere('id!=:id', [':id'=>$model['id']])->orderBy('id desc')->one();

                        if($data['id']){
                            $lastReply->comment_count = $lastReply['comment_count'] -1;
                            $lastReply->last_comment_uid = $data['user_id'];
                            $lastReply->last_comment_time = $data['created_at'];
                            $lastReply->save();
                        }else{
                            $lastReply->comment_count = $lastReply['comment_count'] -1;
                            $lastReply->last_comment_uid = 0;
                            $lastReply->last_comment_time = 0;
                            $lastReply->save();
                        }
                    }
                }
                if($model->delete()){
                    return 1;
                }

            }
        }
    }

    public $enableCsrfValidation = false;
    //话题图片上传
    public function actionUploadTopicImage()
    {
        $model = new UploadForm();

        if (\Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('imgFile');

            if ($uploadData = $model->uploadGroupInfo()) {
                // file is uploaded successfully
                echo Json::encode(['error'=>0, 'url'=>Image::getGroupInfoUrl($uploadData['basename'], 'org')]);
            }
        }
    }
}