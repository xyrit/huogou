<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "act_lottery_reward_log".
 *
 * @property string $id
 * @property string $user_id
 * @property string $activity_id
 * @property string $reward_id
 * @property string $created_at
 */
class LotteryRewardLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_lottery_reward_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'reward_id', 'created_at'], 'required'],
            [['user_id', 'activity_id', 'reward_id', 'created_at'], 'integer']
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

    public static function addLog($uid, $rewarId, $actId)
    {
        $model = new LotteryRewardLog();
        $model->user_id = $uid;
        $model->reward_id = $rewarId;
        $model->created_at = time();
        $model->activity_id = $actId;
        $model->save();
        return $model->primaryKey;
    }
}
