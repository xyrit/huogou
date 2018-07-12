<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "free_invite".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $invite_uid
 * @property string $period_id
 * @property integer $buy_num
 * @property integer $invite_time
 */
class FreeInvite extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'free_invite';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'invite_uid', 'period_id', 'invite_time'], 'required'],
            [['user_id', 'invite_uid', 'period_id', 'buy_num', 'invite_time'], 'integer']
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
            'invite_uid' => 'Invite Uid',
            'period_id' => 'Period ID',
            'buy_num' => 'Buy Num',
            'invite_time' => 'Invite Time',
        ];
    }
}
