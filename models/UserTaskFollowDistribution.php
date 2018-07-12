<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_task_follow_100".
 *
 * @property string $id
 * @property string $user_id
 * @property string $content
 * @property integer $source
 * @property integer $type
 * @property integer $level
 * @property integer $cate
 * @property integer $num
 * @property integer $created_at
 */
class UserTaskFollowDistribution extends \yii\db\ActiveRecord
{
    /**
     * 任务类型
     */
    const TASK_SIGN = 1;    //签到
    const TASK_NEW = 2;     //新手任务
    const TASK_DAILY = 3;   //日常任务
    const TASK_GROW = 4;    //成长任务

    /**
     * 成长任务类型
     */
    const GROW_GLORY = 1;   //称号
    const GROW_PAYMENT = 2; //充值
    const GROW_LEVEL = 3;   //等级

    private static $_tableId;

    public static function instantiate($row)
    {
        return new static(static::$_tableId);
    }

    public function __construct($tableId, $config = [])
    {
        parent::__construct($config);
        static::$_tableId = $tableId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $tableId = substr(static::$_tableId, 0, 3);
        return 'user_task_follow_' . $tableId;
    }

    public static function getTableIdByOrderId($orderId)
    {
        return substr($orderId, 0, 3);
    }

    public static function getTableIdByUserHomeId($userHomeId)
    {
        return substr($userHomeId, 0, 3);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'source', 'type', 'level', 'cate', 'num', 'created_at'], 'integer'],
            [['content'], 'string', 'max' => 100]
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
            'content' => 'Content',
            'source' => 'Source',
            'type' => 'Type',
            'level' => 'Level',
            'cate' => 'Cate',
            'num' => 'Num',
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
