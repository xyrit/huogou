<?php

namespace app\models;

use app\helpers\MyRedis;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "share_comments".
 *
 * @property string $id
 * @property string $share_topic_id
 * @property string $content
 * @property string $user_id
 * @property string $ip
 * @property string $created_at
 */
class ShareComment extends \yii\db\ActiveRecord
{
    const NEW_TIPS = 'share_comment_';
    const SWITCH_SHARE = 'switch_share';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'share_comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['share_topic_id', 'user_id', 'created_at'], 'integer'],
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
            'share_topic_id' => 'Share Topic ID',
            'content' => 'Content',
            'user_id' => 'User ID',
            'ip' => 'Ip',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 添加评论
     * @param $post
     * @return bool|mixed
     */
    public static function addCommit($post)
    {
        $db = Yii::$app->db;
        $trans = $db->beginTransaction();
        try {
            $model = new ShareComment();
            $model->share_topic_id = $post['share_topic_id'];
            $model->content = $post['content'];
            $model->user_id = $post['user_id'];
            $model->ip = Yii::$app->request->userIP;
            $model->created_at = time();

            $redis = new MyRedis();
            $key = self::SWITCH_SHARE;
            $status = $redis->get($key);
            $model->status = $status ? 0 : 1;

            if ( $model->validate()) {
                if($model->save()){
                    $result = $model->primaryKey;
                }else{
                    $trans->rollBack();
                    return false;
                }
            } else {
                $trans->rollBack();
                return false;
            }
                //$model->status = 1;//新添加
            if ($model->status == 1) {
                if (!ShareTopic::addColumnByNum($post['share_topic_id'], 'comment_num', 1)) {
                    $trans->rollBack();
                    return false;
                }
            }

            $trans->commit();

            // 该晒单有新评论提示
            $redis = new MyRedis();
            $key = ShareComment::NEW_TIPS . $post['share_topic_id'];
            $redis->set($key, 1);

            return $result;
        } catch (Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    public static function getList($id, $limit = 10)
    {
        $where = ['share_topic_id' => $id];
        $query = ShareComment::find();
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->where($where)->count(), 'defaultPageSize' => $limit]);
        $list = $query->where($where)->orderBy('created_at DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        return ['list' => $list, 'pagination' => ArrayHelper::toArray($pagination)];
    }

    /**
     * 后台晒单评论列表
     * @param $id
     * @param string $start_time
     * @param string $end_time
     * @param string $account
     * @param int $page
     * @param int $perpage
     * @return mixed
     */
    public static function adminCommentList($id, $start_time = '', $end_time = '', $account = '', $perpage = 10)
    {
        $query = ShareComment::find()->leftJoin('users u', 'share_comments.user_id=u.id')->select('share_comments.*, u.phone, u.email')->where(['share_topic_id' => $id]);

        if ($start_time != '') {
            $query->andWhere(['>', 'share_comments.created_at', $start_time]);
        }
        if ($end_time != '') {
            $query->andWhere(['<', 'share_comments.created_at', $end_time]);
        }
        if ($account != '') {
            $query->andWhere(['or', 'u.phone="' . $account . '"', 'u.email="' . $account . '"']);
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $perpage]);
        $result = $query->orderBy('share_comments.created_at DESC')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        foreach ($result as &$comment) {
            $comment['reply_num'] = ShareReply::find()->where(['share_comment_id' => $comment['id']])->count();
        }

        $return['list'] = $result;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['pagination'] = $pagination;
        return $return;
    }

    /**
     * 判断用户对评论是否已羡慕
     * @param int $id
     * @param int $flag  没有羡慕时，当flag=1进行羡慕
     * @return number
     */
    public static function is_up($id, $flag = 0)
    {
        $deviceId = Yii::$app->request->get('deviceId', 0);
        if ($deviceId === 0) {
            return 0;
        }
        $key = 'SHARE_COMMENT_' . $deviceId . '_' . $id;
        $redis = new MyRedis();
        if ($redis->get($key)) {
            return 1; //已羡慕
        } else {
            if ($flag == 1) {
                $redis->set($key, 1);
            }
            return 0;
        }
    }
}
