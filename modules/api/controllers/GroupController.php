<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午4:19
 */
namespace app\modules\api\controllers;

use app\models\Friend;
use app\models\UserMessage;
use Yii;
use app\models\GroupTopic;
use app\models\User;
use app\models\FriendApply;
use app\models\GroupUser;
use app\models\UserPrivateMessage;
use app\models\UserLimit;
use yii\helpers\Json;
use app\modules\image\models\UploadForm;
use yii\web\UploadedFile;

class GroupController extends BaseController
{
    public $uid;

    public function init()
    {
        $this->uid = Yii::$app->user->id;
    }


    public function actionNewTopicList()
    {
        $request = Yii::$app->request;
        if($request->isGet){
            $groupId = $request->get('groupId');
            $limit = $request->get('perpage');

            $list = GroupTopic::getNewTopicList($groupId, $limit);
            return $list;
        }
    }

    //发送好友请求
    public function actionAddFriend()
    {
        $request = Yii::$app->request;
        $home_id = $request->get('id');
        $user = User::findOne(['home_id'=>$home_id]);
        if(!$this->uid){
            return ['code'=>105, 'msg' => '请先登录'];
        }
        if(!$user){
            return ['code'=> 101, 'msg'=>'该用户不存在'];
        }
        if($this->uid == $user['id']){
            return ['code' => 104, 'msg'=>'本人不能加好友'];
        }

        $toplimit = Friend::find()->where(['user_id'=>$user['id']])->count();
        if($toplimit == 100){
            return ['code'=>112, 'msg'=>'用户好友已达上限'];
        }

        $isFriend = Friend::findOne(['user_id'=>$this->uid, "friend_userid"=>$user['id']]);
        $exits = FriendApply::findOne(['apply_userid'=>$user['id'], 'user_id'=>$this->uid]);
        
        if($exits['status'] == 0 && $exits['status'] !== null){
            return ['code' => 102, 'msg'=>'申请成功,请等待对方通过'];
        }elseif($exits['status'] == 1 || $isFriend){
            return ['code' => 103, 'msg' => '已是好友'];
        }else{
            $conn = Yii::$app->db;
            $start = strtotime(date('Y-m-d',time()));
            $nums = $conn->createCommand('select count(1) as total from friend_apply where user_id = '.$this->uid.' and apply_time >= '.$start.' and apply_time < '.time().'');
            $queryNum = $nums->queryOne();

            if($queryNum['total'] > 29){
                return ['code' => 110, 'msg' => '频繁操作'];
            }

            $model = new FriendApply();
            $model->user_id = $this->uid;
            $model->apply_userid = $user['id'];
            $model->apply_time = time();
            if($model->save()){
                UserMessage::addMessage($user['id'], 1, '请求加好友');
                return ['code' => 100, 'msg' => '请求已发送'];
            }
        }
    }

    //发表话题
    public function actionTopicMsg()
    {
        $groupId = Yii::$app->request->get('id');
        if(!$this->uid){
            return ['code' => 101, 'msg' => '请先登录'];
        }
        $join = GroupUser::findOne(['group_id'=>$groupId, 'user_id'=>$this->uid]);
        if(!$join['id']){
            return ['code' => 102, 'msg' => '请先加入该圈子'];
        }
    }

    //发送私信
    public function actionPrvMsg()
    {
        $home_id = Yii::$app->request->get('id');
        $content = Yii::$app->request->get('content');
        $user = User::findOne(['home_id' => $home_id]);
        if(!$this->uid){
            return ['code' => 101, 'msg' => '请先登录'];
        }
        if($content != strip_tags($content)) return ['code'=>115, 'msg'=>'您有广告嫌疑'];

        if($this->uid == $user['id']){
            return ['code' => 104, 'msg'=>'本人不能发送'];
        }
        if(!$user){
            return ['code' => 102, 'msg' => '该用户不存在'];
        }
        $isFriend = Friend::findOne(['user_id'=>$this->uid, 'friend_userid'=>$user['id']]);
        if(!$isFriend['id']){
            return ['code' => 103, 'msg' => '请先成为好友'];
        }
        $limit = UserLimit::findOne(['user_id'=>$user['id']]);
        if($limit['id'] && $limit['private_letter'] == 0) return ['code' => 111, 'msg' => '用户禁止发送私信'];

        $acturl = substr($home_id, 0 , 3);
        $conn = \Yii::$app->db;
        $balsql = $conn->createCommand('SELECT count(1) as total FROM user_buylist_'.$acturl.' where user_id = '.$this->uid.'');
        $balance = $balsql->queryOne();

        $start = strtotime(date('Y-m-d',time()));
        $numsql = $conn->createCommand('SELECT count(1) as total FROM user_private_messages where user_id = '.$this->uid.' and created_at >='.$start.' and created_at < '.time().'');
        $num = $numsql->queryOne();
        if($balance['total']){
            if($num['total'] > 19) return ['code'=> 110, 'msg' => '操作频繁'];
        }else{
            if($num['total'] > 9) return ['code'=> 110, 'msg' => '操作频繁'];
        }

        $model = new UserPrivateMessage();
        $model->user_id = $this->uid;
        $model->reply_userid = $user['id'];
        $model->content = $content;
        $model->created_at = time();
        if($model->save()){
            UserMessage::addMessage($user['id'], 3, '私信消息');
            return ['code' => 100, 'msg' => '发送成功'];
        }
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