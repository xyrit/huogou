<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "share_topic_comments".
 *
 * @property string $id
 * @property string $pid
 * @property string $share_topic_id
 * @property string $content
 * @property string $user_id
 * @property integer $is_topic
 * @property string $reply_floor
 * @property string $ip
 * @property string $created_at
 */
class ShareTopicComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'share_topic_comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'reply_id', 'share_topic_id', 'user_id', 'is_topic', 'reply_floor', 'created_at'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
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
            'group_id' => 'Group ID',
            'reply_id' => 'Reply ID',
            'share_topic_id' => 'Topic ID',
            'content' => 'Content',
            'user_id' => 'User ID',
            'is_topic' => 'Is Topic',
            'reply_floor' => 'Reply Floor',
            'ip' => 'Ip',
            'created_at' => 'Created At',
        ];
    }
    
    /**
     * 根据晒单ID获取回复信息
     * @param int $topicId
     * @return unknown
     */
    public static function getListByTopicId($topicId, $is_topic = 1)
    {
        $query = ShareTopicComment::find();
        $shareTopicComment = $query->where(['share_topic_id' => $topicId, 'is_topic' => $is_topic])
                                ->orderBy('created_at DESC')
                                ->all();
        
        return $shareTopicComment;
    }
    
    /**
     * 添加评论
     * @param array $post
     */
    public static function addCommit($post)
    {
        $count = ShareTopicComment::find()->where([
                    'share_topic_id' => $post['share_topic_id'],
                    'group_id' => $post['group_id'],
                    'is_topic' => 0
                ])->count();
        
        $model = new ShareTopicComment();
        $model->group_id = isset($post['group_id']) ? $post['group_id'] : 0;
        $model->reply_id = isset($post['reply_id']) ? $post['reply_id'] : 0;
        $model->share_topic_id = $post['share_topic_id'];
        $model->content = $post['content'];
        $model->user_id = 1;
        $model->is_topic = $post['is_topic'];
        $model->reply_floor = $count + 1;
        $model->created_at = time();
        
        if ( $model->validate()) {
            if($model->save()){
                return $model->primaryKey;
            }else{
                return false;
            }
        }
    }
}
