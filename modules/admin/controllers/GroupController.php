<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/18
 * Time: 14:54
 */

namespace app\modules\admin\controllers;

use app\models\GroupTopic;
use app\models\GroupTopicComment;
use app\models\GroupUser;
use app\models\User;
use app\modules\admin\models\BackstageLog;
use app\modules\admin\models\Keyword;
use Yii;
use app\models\Group;
use yii\data\Pagination;
use app\modules\image\models\Image;
use app\modules\image\models\ImageRelation;
use app\modules\image\models\UploadForm;
use yii\web\UploadedFile;
use app\models\Image as PicImage;
use app\services\Member;
use yii\helpers\Json;

class GroupController extends BaseController
{
    //圈子列表
    public function actionIndex()
    {
        $query = Group::find();
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>10 ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    public function actionTopic()
    {
        $request = \Yii::$app->request;
        $query = GroupTopic::find();
        $get = [];
        if($request->isGet) {
            $get = $request->get();
            if(isset($get['id'])) $query->where(['group_id'=>$get['id']]);
            if(isset($get['digest']) && $get['digest'] != '2') $query->where(['is_digest'=>$get['digest']]);
            if(isset($get['status']) && $get['status'] != 3) $query->where(['status'=>$get['status']]);
            $start = strtotime("today");
            $end = strtotime(date("Y-m-d",strtotime("+1 day")));
            if(isset($get['today']) && $get['today'] == 1) $query->where(['and', 'created_at>='.$start, 'created_at<'.$end]);
            if(isset($get['start']) && $get['start'] != ''){
                $gt = ['>=', 'created_at', strtotime($get['start'])];
                $query->andWhere($gt);
            }
            if(isset($get['end']) && $get['end'] != ''){
                $lt = ['<=', 'created_at', strtotime($get['end'])];
                $query->andWhere($lt) ;
            }
        }

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>25 ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();

        foreach($list as &$val){
            $val['user_id'] = User::findOne($val['user_id']);
        }

        $groups = Group::getGroup();

        return $this->render('topic', [
            'list' => $list,
            'pagination' => $pagination,
            'groups' => $groups,
            'get' => $get
        ]);
    }

    //回帖列表
    public function actionComment()
    {
        $query = \app\models\GroupTopicComment::find()
            ->select('group_topic_comments.*, t.subject')
            ->leftJoin('group_topics t', 't.id = group_topic_comments.topic_id')
            ->where(['is_topic'=>0]);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>25 ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();

        foreach($list as &$val){
            $val['message'] = GroupTopicComment::commentDeal($val['message']);
            $val['user_id'] = User::findOne($val['user_id']);
        }

        return $this->render('comment', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    // 新增圈子
    public function actionAdd()
    {
        $model = new Group();
        $userModel = new GroupUser();
        $request = \Yii::$app->request;
        $model->group_closed = 0;
        $model->comment_closed = 0;
        $model->topic_closed = 0;
        $model->verify_status = 0;

        if($request->isPost){
            $post = $request->post();

            if(!empty($_FILES['imageFile']['name'])){
                $imagModel = new UploadForm();
                $imagModel->imageFile = UploadedFile::getInstanceByName('imageFile');
                $uploadData = $imagModel->uploadGroupIcon();
            }else{
                $uploadData['basename'] = '';
            }

            if ($model->load( $post) && $model->validate()) {
                $model->user_count = 1;
                $model->picture = $uploadData['basename'];
                $model->created_at = time();

                if($model->save()){
                    $adminuser = User::find()->where(['or', 'email="'.$model->adminuser.'"', 'phone="'.$model->adminuser.'"']);
                    $exist = GroupUser::find()->where(['user_id'=>$adminuser['id'], 'group_id'=>$id=$model->primaryKey])->one();
                    if(!$exist){
                        $userModel->user_id = $adminuser['id'];
                        $userModel->group_id = $model->id;
                        $userModel->created_at = time();
                        $userModel->save();
                    }
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '新增('.$post['Group[name]'].')圈子');
                    $this->redirect('index');
                }
            }
        }

        return $this->render('add', [
            'model' => $model,
        ]);

    }

    ///圈子修改
    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $model = Group::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException("页面未找到");
        }

        if($request->isPost){
            if(!empty($_FILES['imageFile']['name'])){
                $imagModel = new UploadForm();
                $imagModel->imageFile = UploadedFile::getInstanceByName('imageFile');
                $uploadData = $imagModel->uploadGroupIcon();
            }else{
                $uploadData['basename'] = $model->picture;
            }
            $post = $request->post();

            if($post['Group']['adminuser'] != $model['adminuser']){
                $find = User::find()->where(['or', 'email="'.$model['adminuser'].'"', 'phone="'.$model['adminuser'].'"'])->one();
                if($find){
                    $ont = GroupUser::find()->where(['and', 'user_id='.$find['id'], 'group_id='.$model['id']])->one();
                    if($ont['id']) $ont->delete();
                }

                $postuser = User::find()->where(['or', 'email="'.$post['Group']['adminuser'].'"', 'phone="'.$post['Group']['adminuser'].'"'])->one();
                $exist = GroupUser::find()->where(['and', 'user_id='.$postuser['id'], 'group_id='.$model['id']])->one();

                if(!$exist){
                    $userModel = new GroupUser();
                    $userModel->user_id = $postuser['id'];
                    $userModel->group_id = $model['id'];
                    $userModel->created_at = time();
                    $userModel->save();
                }
            }
            if ($model->load($request->post())) {

                if ($model->validate()) {
                    $model->picture = $uploadData['basename'];
                    $model->save();
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '修改('.$model['name'].')圈子');
                    return $this->redirect(['group/index']);
                }
            }
        }

        if($model['picture'] != ''){
            $image = PicImage::getGoupIconUrl($model['picture']);
        }else{
            $image = '';
        }

        $closedItems = [0=>'开',1=>'关'];
        $replyItems = [0=>'开',1=>'关'];

        return $this->render('edit', [
            'model' => $model,
            'image' => $image,
            'closedItems' => $closedItems,
            'replyItems' => $replyItems,
        ]);
    }

    //圈子删除
    public function actionDel()
    {
        $id = \Yii::$app->request->post('id');
        $response = \Yii::$app->response;
        $model = Group::findOne($id);
        GroupUser::deleteAll(['group_id'=>$id]);
        GroupTopic::deleteAll(['group_id'=>$id]);
        $topics = GroupTopic::findAll(['group_id'=>$id]);
        foreach($topics as $key => $val){
            GroupTopicComment::deleteAll(['topic_id'=>$val['id']]);
        }
        BackstageLog::addLog(Yii::$app->admin->id, 10, '删除('.$model['name'].')圈子');
        $delete = $model->delete();
        $response->format = \yii\web\Response::FORMAT_JSON;
        if ($delete) {
            return [
                'error' => 0,
                'message' => '删除成功'
            ];
        }
        return [
            'error' => 1,
            'message' => '删除失败'
        ];
    }

    //话题置顶
    public function actionSetTop()
    {
        $request = Yii::$app->request;
        if($request->isGet){
            $id = $request->get('id');
            $op = $request->get('op');

            if($op == 'cancel'){
                $where['id'] = $id;
                $where['is_top'] = 1;
            }else{
                $where['id'] = $id;
            }
            $model = GroupTopic::findOne($where);
            if($model){
                if($op){
                    $model->is_top = 0;
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '对话题('.$model['subject'].')取消置顶');
                } else{
                    $model->is_top = 1;
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '对话题('.$model['subject'].')置顶');
                    $username = User::userName($model['user_id']);
                    //Message::send(30, $model['user_id'], ['nickname'=>$username['username'], 'topicTitle'=>$model['subject'], 'experience'=>0]);
                }

                $model->save();
                return $this->redirect(Yii::$app->request->referrer);
            }
        }else{
            throw new NotFoundHttpException("页面未找到");
        }
    }

    //话题精华
    public function actionSetDigest()
    {
        $request = Yii::$app->request;
        if($request->isGet){
            $id = $request->get('id');
            $op = $request->get('op');

            if($op == 'cancel'){
                $where['id'] = $id;
                $where['is_digest'] = 1;
            }else{
                $where['id'] = $id;
            }
            $model = GroupTopic::findOne($where);
            $group = Group::findOne(['id'=>$model['group_id']]);
            if($model){
                if($op){
                    $model->is_digest = 0;
                    $group->digest_count = $group['digest_count'] - 1;
                    $member = new Member(['id' => $model['user_id']]);
                    $member->editExperience(-50, 6, '话题加精');
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '对话题('.$model['subject'].')取消加精');
                } else{
                    $model->is_digest = 1;
                    $group->digest_count = $group['digest_count'] + 1;
                    $username = User::userName($model['user_id']);
                    //Message::send(29, $model['user_id'], ['nickname'=>$username['username'], 'topicTitle'=>$model['subject'], 'experience'=>10]);
                    $member = new Member(['id' => $model['user_id']]);
                    $member->editExperience(50, 6, '话题加精');
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '对话题('.$model['subject'].')加精');
                }
                $model->save();
                $group->save();
                return $this->redirect(Yii::$app->request->referrer);
            }
        }else{
            throw new NotFoundHttpException("页面未找到");
        }
    }

    //话题删除
    public function actionDelTopic()
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        if($request->isPost){
            $id = $request->post('id');
            $model = GroupTopic::findOne($id);
            if($model){
                $username = User::userName($model['user_id']);
                //Message::send(27, $model['user_id'], ['nickname'=>$username['username'], 'topicTitle'=>$model['subject']]);
                $member = new Member(['id' => $model['user_id']]);
                $member->editExperience(-100, 5, '管理员删除话题');
                BackstageLog::addLog(Yii::$app->admin->id, 10, '删除话题('.$model['subject'].')');
                GroupTopicComment::deleteAll(['topic_id'=>$id]);
                $delete = $model->delete();
                $response->format = \yii\web\Response::FORMAT_JSON;
                if ($delete) {
                    if($model['status'] == 1) {
                        $group = Group::findOne(['id' => $model['group_id']]);
                        if (($group['topic_count'] - 1) < 0) $num = 0; else $num = $group['topic_count'] - 1;
                        $group->topic_count = $num;
                        $group->save();
                    }
                    return [
                        'error' => 0,
                        'message' => '删除成功'
                    ];
                }
                return [
                    'error' => 1,
                    'message' => '删除失败'
                ];
            }
        }
    }

    //获取话题内容
    public function actionTopicMess()
    {
        $id = Yii::$app->request->post('id');
        $mess = GroupTopicComment::find()->where(['topic_id'=>$id, 'is_topic'=>1])->one();
        return GroupTopicComment::topicContentDeal($mess['message']);
    }

    //话题审核
    public function actionVerify(){
        $id = Yii::$app->request->get('id');
        $model = GroupTopic::findOne($id);
        if($model){
            $status = Yii::$app->request->get('status');
            $commentModel = GroupTopicComment::find()->where(['topic_id'=>$model['id'], 'is_topic'=>1])->one();
            if($status){
                $model->status = $status;
                $model->save();
                if($status == 1){
                    Group::groupTopciCount($model['group_id']);
                    $commentModel->status = 1;
                    $commentModel->save();
                    $member = new Member();
                    $member->editExperience(50, 5, '发表话题成功');
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '话题('.$model['subject'].')通过');
                }else{
                    $commentModel->status = 2;
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '话题('.$model['subject'].')不通过');
                    $commentModel->save();
                }
                return $this->redirect(Yii::$app->request->referrer);
            }else{
                if($model['status'] == 1){
                    $model->status = 2;
                    Group::groupTopciCount($model['group_id'], 2);
                    $commentModel->status = 2;
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '话题内容('.$model['subject'].')不通过');
                    $commentModel->save();
                }elseif($model['status'] == 2){
                    $model->status = 1;
                    Group::groupTopciCount($model['group_id']);
                    $commentModel->status = 1;
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '话题内容('.$model['subject'].')通过');
                    $commentModel->save();
                }
                $model->save();
                return $this->redirect(Yii::$app->request->referrer);
            }

        }
    }

    //回帖审核
    public function actionVerifyComment(){
        $id = Yii::$app->request->get('id');
        $model = GroupTopicComment::findOne($id);
        if($model){
            $status = Yii::$app->request->get('status');
            if($status){
                $model->status = $status;
                if($status == 1){
                    $member = new Member();
                    $member->editExperience(5, 7, '回复成功');
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '评论('.$model['message'].')通过');
                }
                if($model->save()){
                    GroupTopic::groupTopciCommentCount($id, $model['topic_id'], $status);
                }
                return $this->redirect(Yii::$app->request->referrer);
            }else{
                if($model['status'] == 1){
                    $model->status = 2;
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '评论('.$model['message'].')不通过');
                    GroupTopic::groupTopciCommentCount($id, $model['topic_id'], 2);
                }elseif($model['status'] == 2){
                    $model->status = 1;
                    BackstageLog::addLog(Yii::$app->admin->id, 10, '评论('.$model['message'].')通过');
                    GroupTopic::groupTopciCommentCount($id, $model['topic_id']);
                }
                $model->save();
                return $this->redirect(Yii::$app->request->referrer);
            }

        }
    }

    //获取回帖内容
    public function actionCommentMess()
    {
        $id = Yii::$app->request->post('id');
        $mess = GroupTopicComment::findOne($id);
        return GroupTopicComment::commentDeal($mess['message']);
    }

    //删除回帖
    public function actionDelComment()
    {
        $id = Yii::$app->request->post('id');
        $mess = GroupTopicComment::findOne($id);
        $member = new Member(['id' => $mess['user_id']]);
        $member->editExperience(-20, 7, '管理员删除回复');
        BackstageLog::addLog(Yii::$app->admin->id, 10, '删除评论('.$mess['message'].')');
        $del = $mess->delete();
        if($del){
            $topic = GroupTopic::findOne($mess['topic_id']);
            $topic->comment_count = $topic['comment_count'] - 1;
            $topic->save();
            return 0;
        }else{
            return 1;
        }
    }

    //关键字过滤
    public function actionKeywords()
    {
        $list = Keyword::find()->orderBy('id desc')->all();

        return $this->render('keywords', [
            'list' => $list,
        ]);
    }

    //新增关键字
    public function actionAddKeyword()
    {
        $request = Yii::$app->request;
        if($request->isPost){
            $post = $request->post('Keywords');
            $model = new Keyword;
            $model->type = $post['type'];
            $model->content = $post['content'];
            if($model->save()){
                return $this->redirect(['group/keywords']);
            }
        }

        return $this->render('add-keyword');
    }

    //新增关键字
    public function actionEditKeyword()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Keyword::findOne($id);
        if(!$model){
            throw new NotFoundHttpException("页面未找到");
        }

        if($request->isPost){
            $post = $request->post('Keywords');
            $model->type = $post['type'];
            $model->content = $post['content'];
            if($model->save()){
                return $this->redirect(['group/keywords']);
            }
        }

        return $this->render('edit-keyword', [
            'model' => $model,
        ]);
    }

    //删除关键字
    public function actionDelKeyword()
    {
        $id = Yii::$app->request->post();
        $model = Keyword::findOne($id);
        if($model){
            $delete = $model->delete();
            if($delete) return 0;
            else return 1;
        }
    }

    public function actionTopicEdit()
    {
        $request = Yii::$app->request;

        if($request->isPost){
            $post = $request->post();
            $model = GroupTopic::findOne($post['id']);
            $model->subject = $post['subject'];
            if($model->save()){
                $msg = GroupTopicComment::find()->where(['topic_id'=>$model['id'], 'is_topic'=>1])->one();
                $msg->message = $post['content'];
                BackstageLog::addLog(Yii::$app->admin->id, 10, '修改话题（'.$model['subject'].')');
                if($msg->save()) return 1;
                else return 0;
            }else{
                return 0;
            }
        }

        $id = $request->get('id');
        $model = GroupTopic::findOne($id);
        if(!$model) return;
        $msg = GroupTopicComment::find()->where(['topic_id'=>$model['id'], 'is_topic'=>1])->one();
        $message = GroupTopicComment::topicContentDeal($msg['message']);
        return $this->render('topic-edit', [
            'model' => $model,
            'message' => $message
        ]);
    }

    public $enableCsrfValidation = false;
    public function actionUploadTopicImg()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('imgFile');
            if ($uploadData = $model->uploadGroupInfo()) {
                // file is uploaded successfully
                echo Json::encode($uploadData);
            }
        }
    }
}