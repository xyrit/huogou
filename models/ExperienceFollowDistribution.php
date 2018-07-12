<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "experience_follow_100".
 *
 * @property string $id
 * @property string $user_id
 * @property string $current_experience
 * @property integer $experience
 * @property integer $type
 * @property string $desc
 * @property integer $created_at
 */
class ExperienceFollowDistribution extends \yii\db\ActiveRecord
{
    // 1 消费  2 邀请好友并消费   3 晒单   4 晒单评论  5 发表话题 6 话题加精  7 回复话题  8 加好友   9 圈主
    const EXPE_BUY = 1;
    const EXPR_FRIEND_BUY = 2;
    const EXPR_SHARE = 3;
    const EXPR_SHARE_COMMENT = 4;
    const EXPR_TOPIC = 5;
    const EXPR_TOPIC_DIGEST = 6;
    const EXPR_TOPIC_COMMENT = 7;
    const EXPR_ADD_FRIEND = 8;
    const EXPR_GROUP = 9;

    const NUMBER_BUY = 10;
    const NUMBER_FRIEND_BUY = 50;
    const NUMBER_SHARE = 500;
    const NUMBER_SHARE_COMMENT = 10;
    const NUMBER_TOPIC = 50;
    const NUMBER_TOPIC_DIGEST = 50;
    const NUMBER_TOPIC_COMMENTY = 10;
    const NUMBER_ADD_FRIEND = 5;
    const NUMBER_GROUP = 1000;

    private static $_userHomeId;

    public static function instantiate($row)
    {
        return new static(static::$_userHomeId);
    }

    public function __construct($userHomeId, $config = [])
    {
        parent::__construct($config);
        static::$_userHomeId = $userHomeId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $tableId = substr(static::$_userHomeId, 0, 3);
        return 'experience_follow_' . $tableId;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'current_experience', 'experience', 'type', 'desc', 'created_at'], 'required'],
            [['user_id', 'current_experience', 'experience', 'type', 'created_at'], 'integer'],
            [['desc'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'current_experience' => 'Current Experience',
            'experience' => 'Experience',
            'type' => 'Type',
            'desc' => 'Desc',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param $userHomeId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByUserHomeId($userHomeId) {
        $model = new static($userHomeId);
        return $model::find();
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByUserHomeId($userHomeId, $condition)
    {
        $model = new static($userHomeId);
        return $model::findOne($condition);
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByUserHomeId($userHomeId, $condition)
    {
        $model = new static($userHomeId);
        return $model::findAll($condition);
    }
}
