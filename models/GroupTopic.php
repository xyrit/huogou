<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\services\User as GetUser;
use app\helpers\DateFormat;
use app\models\Image as ModelImage;
use app\models\Friend;

/**
 * This is the model class for table "group_topic".
 *
 * @property string $id
 * @property string $group_id
 * @property string $subject
 * @property string $user_id
 * @property string $view_count
 * @property string $comment_count
 * @property string $last_post
 * @property string $last_comment_uid
 * @property integer $is_top
 * @property integer $digest
 * @property string $created_at
 */
class GroupTopic extends \yii\db\ActiveRecord
{
    public $message;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_topics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id'], 'required'],
            [['group_id', 'user_id','status', 'view_count', 'comment_count', 'last_comment_time', 'last_comment_uid', 'is_top', 'is_digest', 'created_at'], 'integer'],
            [['subject'], 'string', 'max' => 80]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'subject' => '话题',
            'user_id' => 'User ID',
        ];
    }

    public static function getListByType($groupId = '', $t = 3, $limit = 15, $verify_status = 0)
    {
        if($verify_status == 1){
            if($t == 1){
                if($groupId == ''){
                    $where = [];
                }else{
                    $where = ['group_id'=>$groupId];
                }
                $order = 'is_top desc, created_at desc, last_comment_time desc';
            }elseif($t == 2){
                if($groupId == ''){
                    $where = ['and','comment_count > 0' ];
                }else{
                    $where = ['group_id'=>$groupId];
                }
                $order = 'comment_count desc';
            }elseif($t == 3){
                if($groupId == ''){
                    $where = ['is_digest'=>1];
                }else{
                    $where = ['is_digest'=>1, 'group_id'=>$groupId];
                }
                $order = 'created_at desc';
            }
        }else{
            if($t == 1){
                if($groupId == ''){
                    $where = ['status'=>1];
                }else{
                    $where = ['group_id'=>$groupId, 'status'=>1];
                }
                $order = 'created_at desc, last_comment_time desc';
            }elseif($t == 2){
                if($groupId == ''){
                    $where = ['and','comment_count > 0', 'status = 1' ];
                }else{
                    $where = ['group_id'=>$groupId, 'status'=>1];
                }
                $order = 'comment_count desc';
            }elseif($t == 3){
                if($groupId == ''){
                    $where = ['is_digest'=>1, 'status'=>1];
                }else{
                    $where = ['is_digest'=>1, 'group_id'=>$groupId, 'status'=>1];
                }
                $order = 'created_at desc';
            }
        }


        $query = GroupTopic::find();
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->where($where)->count(), 'defaultPageSize' =>$limit ]);
        $list = $query->where($where)->orderBy($order)
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        $arr = [];
        $userids = ArrayHelper::getColumn($list, 'user_id');
        $user = GetUser::allInfo($userids);
        foreach($list as $key => $val){
            $userInfo = $user[$val['user_id']];
            $arr[$key]['username'] = $userInfo;
            $arr[$key]['city'] =$userInfo['hometown'];
            $arr[$key]['user_avatar'] = ModelImage::getUserFaceUrl($userInfo['avatar'], 160);
            $arr[$key]['group_id'] = $val['group_id'];
            $arr[$key]['id'] = $val['id'];
            $arr[$key]['comment_count'] = $val['comment_count'];
            $arr[$key]['view_count'] = $val['view_count'];
            $arr[$key]['user_id'] = $val['user_id'];
            $arr[$key]['top'] = $val['is_top'];
            $arr[$key]['digest'] = $val['is_digest'];
            $arr[$key]['subject'] = $val['subject'];
            $arr[$key]['status'] = $val['status'];
            $uid = \Yii::$app->user->id;
            if($uid){
                $arr[$key]['isFriend'] = Friend::find()->where(['and', 'user_id='.$val['user_id'], 'friend_userid='.$uid])->count();
            }
            $arr[$key]['last_comment_uid'] = $val['last_comment_uid'];
            $arr[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
            if($val['last_comment_time']){
                $arr[$key]['last_comment_time'] = DateFormat::formatTime($val['last_comment_time']);
            }
        }

        return ['list'=> $arr, 'pagination'=>$pagination];
    }

    public static function addTopic($post, $status = 0)
    {
        $model = new GroupTopic();
        $model->subject = strip_tags($post['Topic']['title']);
        $model->group_id = $post['Topic']['groupId'];
        $model->user_id = $post['Topic']['user_id'];
        $model->status = $status;
        $model->created_at = time();
        if ( $model->validate()) {
            if($model->save()){
                return $model->primaryKey;
            }else{
                return false;
            }
        }
    }

    public static function getLiveCity($city)
    {
        if($city){
            $area = explode(',',$city);
            if($area[0]){
                $province = Area::findOne($area[0]);
            }else{
                $province = [];
                $province['name'] = '';
            }
            if($area[1]){
                $city = Area::findOne($area[1]);
            }else{
                $city = [];
                $city['name'] = '';
            }
            return $province['name'].$city['name'];
        }
    }

    public static function getTopic()
    {
        $topics = GroupTopic::find()->all();
        return ArrayHelper::map($topics, 'id', 'subject');
    }

    public static function getGroupNews($groupId = '')
    {
        if($groupId){
            $where = ' where b.group_id = '.$groupId.' and a.status = 1';
        }else{
            $where = 'where a.status = 1';
        }
        $connection = \Yii::$app->db;
        $sql = "SELECT a.*, b.subject FROM `group_topic_comments` as a left join `group_topics` as b on a.topic_id = b.id ".$where." order by a.id desc limit 5";
        $command = $connection->createCommand($sql);
        $newTopic = $command->queryAll();
        return $newTopic;
    }

    //当前用户发表的话题数
    public static function topicNum($uid)
    {
        return GroupTopic::find()->where(['user_id'=>$uid, 'status'=>1])->count();
    }

    //圈子话题数
    public static function groupTopciCommentCount($commentId, $topicId, $status = 1)
    {
        $topic = GroupTopic::findOne($topicId);
        if($status == 1){
            $topic->comment_count = $topic['comment_count'] + 1;
        }else{
            $topic->comment_count = $topic['comment_count'] - 1;
        }
        GroupTopic::lastReplyTime($commentId, $topicId);

        return $topic->save();
    }

    //最后回帖时间
    public static function lastReplyTime($commentId, $topicId)
    {
        $commentModel = GroupTopicComment::findOne($commentId);
        $topicModel = GroupTopic::findOne($topicId);
        if($topicModel['last_comment_time']){
            if($commentModel['created_at'] > $topicModel['last_comment_time']){
                $topicModel->last_comment_uid = $commentModel['user_id'];
                $topicModel->last_comment_time = $commentModel['created_at'];
                $topicModel->save();
            }elseif(($topicModel['last_comment_time'] - $commentModel['created_at']  < 10)){
                $data = GroupTopicComment::find()->where('topic_id=:topicId', [':topicId'=>$commentModel['topic_id']])->andWhere('is_topic=0')->andWhere('id!=:id', [':id'=>$commentModel['id']])->orderBy('id desc')->one();

                if($data['id']){
                    $topicModel->last_comment_uid = $data['user_id'];
                    $topicModel->last_comment_time = $data['created_at'];
                    $topicModel->save();
                }else{
                    $topicModel->last_comment_uid = 0;
                    $topicModel->last_comment_time = 0;
                    $topicModel->save();
                }
            }
        }else{
            $topicModel->last_comment_uid = $commentModel['user_id'];
            $topicModel->last_comment_time = $commentModel['created_at'];
            $topicModel->save();
        }
    }

    //后台搜索
    public static function searchCondition($get)
    {
        $where = [];
        if(isset($get['id']) && $get['id'] != '') $where['group_id'] = $get['id'];

        if(isset($get['status']) && ($get['status'] != 3)) $where['status'] = $get['status'];

        if(isset($get['digest'])) $where['is_digest'] = $get['digest'];

        $andWhere = [];
        $andOther = [];
        if(isset($get['today'])){
            $start = strtotime("today");
            $end = strtotime(date("Y-m-d",strtotime("+1 day")));
            $andWhere = ['and', ['>=', 'created_at', $start], ['<=', 'created_at', $end]];
        }else{
            if(isset($get['start']) && ($get['start'] != '')) $andWhere = ['>=', 'created_at', strtotime($get['start'])];

            if(isset($get['end']) && ($get['end'] != '')) $andOther = ['<=', 'created_at', strtotime($get['end'])];
        }

        return ['where'=>$where, 'andWhere'=>$andWhere, 'andOther'=>$andOther];
    }

    //最新公告
    public static function getNewTopicList($groupId, $limit = 3)
    {
        $where['group_id'] = $groupId;
        $where['status'] = 1;
        $list = GroupTopic::find()->where($where)->orderBy('id desc')->limit($limit)->asArray()->all();
        foreach($list as $key=>$val){
            $list[$key]['id'] = $val['id'];
            $list[$key]['subject'] =mb_substr($val['subject'], 0, 18, 'utf-8');
        }

        return $list;
    }


}
