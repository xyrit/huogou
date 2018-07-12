<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;

/**
 * This is the model class for table "group_topic_post".
 *
 * @property string $id
 * @property string $topic_id
 * @property string $message
 * @property string $user_id
 * @property integer $is_topic
 * @property string $reply_floor
 * @property string $ip
 * @property string $created_at
 */
class GroupTopicComment extends \yii\db\ActiveRecord
{
    public $subject;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_topic_comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['topic_id', 'user_id', 'is_topic', 'floor' ,'created_at'], 'integer'],
            [['message'], 'required'],
            [['message'], 'string'],
            [['ip'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'topic_id' => 'Topic ID',
            'message' => '内容',
            'user_id' => 'User ID',
            'is_topic' => 'Is Topic',
            'reply_floor' => 'Reply Floor',
            'ip' => 'Ip',
            'created_at' => 'Create At',
        ];
    }

    public static function addTopicPost($topicId, $post, $is_topic = 0, $floor = 0, $status = 0, $floorUserId = 0)
    {
        $exist = GroupTopicComment::find()->where(['topic_id'=>$topicId, 'is_topic'=>0])->orderBy('id desc')->one();
        if($exist){
            $num = $exist['floor'] + 1;
        }else{
            $num = 1;
        }
        $model = new GroupTopicComment();
        $model->topic_id = $topicId;
        $model->message = $post['Topic']['content'];
        $model->user_id = $post['Topic']['user_id'];
        $model->status = $status;
        $model->is_topic = $is_topic;
        $model->floor = $num;
        $model->reply_floor = $floor;
        $model->reply_userid = $floorUserId;
        $model->ip = \Yii::$app->request->getUserIP();
        $model->created_at = time();
        if ( $model->validate()) {
            if($model->save()){
                return true;
            }else{
                return false;
            }
        }
    }

    public static function getMessage()
    {
        $message = GroupTopicComment::find()->all();
        return ArrayHelper::map($message, 'topic_id', 'message');
    }

    //话题分类
    public static function getTopicMessage($topicId = '',$status = 0, $type=0, $user_id = '')
    {
        $query = GroupTopicComment::find();

        $countQuery = clone $query;

        $where = [];
        if($user_id){
            $where['user_id'] = $user_id;
            $where['is_topic'] = 0;
            if($status == 0){
                $where['status'] = 1;
            }
        }

        if($topicId != ''){
            $where['topic_id'] = $topicId;
            $where['is_topic'] = 0;
            if($status == 0){
                $where['status'] = 1;
            }
        }

        $count = $countQuery->where($where)->count();
        $pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' =>10 ]);
        $list = $query->where($where)->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();

        $page = \Yii::$app->request->get('page');
        if(!isset($page) || $page > ceil($count / 10)){
            $page = 1;
        }
        $floor = $count - ($page - 1) * 10;

        return ['list'=>$list, 'pagination'=>$pagination, 'floor'=>$floor];

    }

    //圈子活跃成员列表
    public static function activeUser($limit = 12)
    {
        return GroupTopicComment::find()->select('count(*) as num, user_id')->groupBy('user_id')->orderBy('num desc')->limit($limit)->asArray()->all();
    }

    //发表的评论数
    public static function commentNum($uid)
    {
        return GroupTopicComment::find()->where(['user_id'=>$uid, 'is_topic'=>0, 'status'=>1])->count();
    }

    //话题内容处理(图文分离)
    public static function messageDeal($commentId)
    {
        $comment = GroupTopicComment::findOne($commentId);
        $comment['message'] = GroupTopicComment::topicContentDeal($comment['message']);

        preg_match_all("/<img([^>]*)\s*src=('|\")([^'\"]+)('|\")/",$comment['message'],$matches);
        foreach($matches[0] as $key => $val){
            if(preg_match('/grouppic/i', $val)){
                $data['images'][$key] = $val;
            }
        }
        $data['content'] = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", strip_tags($comment['message']));
        return $data;
    }

    //评论内容处理
    public static function commentDeal($content)
    {
        preg_match_all("/\[s:(\d*)\]/", $content, $matches);
        foreach($matches[1] as $key => $val){
            $img = "<img src='http://skin.huogou.com/js/keditor/plugins/emoticons/images/".$val.".gif' />";
            $content = str_replace('[s:'.$val.']', $img,  $content);
        }
        return $content;
    }

    //话题内容匹配
    public static function topicContentDeal($content)
    {
        preg_match_all("/\[s:(\d*)\]/", $content, $matches);
        foreach($matches[1] as $key => $val){
            $img = "<img src='http://skin.huogou.com/js/keditor/plugins/emoticons/images/".$val.".gif' />";
            $content = str_replace('[s:'.$val.']', $img,  $content);
        }
        $content = str_replace('[img]', '<img src="http://img.huogou.com/grouppic/org/',  $content);
        $content = str_replace('[/img]', '">',  $content);
        $content = str_replace('[url=', '<a href="',  $content);
        $content = str_replace('[/url]', '</a>',  $content);
        $content = str_replace(']', '">',  $content);

        return $content;
    }
}
