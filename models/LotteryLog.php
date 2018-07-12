<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "act_lottery_log".
 *
 * @property string $id
 * @property string $user_id
 * @property string $activity_id
 * @property string $reward_id
 * @property string $created_at
 */
class LotteryLog extends \yii\db\ActiveRecord
{
    private static $tableId ;

    public static function instantiate($row)
    {
        return new static(static::$tableId);
    }

    public function __construct($tableId, $config = [])
    {
        parent::__construct($config);
        static::$tableId = $tableId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_lottery_log_' . static::$tableId;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'reward_id', 'created_at'], 'required'],
            [['user_id', 'activity_id', 'reward_id', 'created_at', 'status'], 'integer']
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
            'activity_id' => 'Activity ID',
            'reward_id' => 'Reward ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param $tableId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByLotteryId($tableId) {
        $model = new static($tableId);
        return $model::find();
    }

    /**
     * @param $tableId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByLotteryId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findOne($condition);
    }

    /**
     * @param $tableId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllBylotteryId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findAll($condition);
    }

    public static function addLog($actId ,$uid, $rewarId, $status = 0)
    {
        $model = new LotteryLog($actId);
        $model->user_id = $uid;
        $model->reward_id = $rewarId;
        $model->created_at = time();
        $model->activity_id = $actId;
        $model->status = $status;
        $model->save();
        return $model->primaryKey;
    }
}
