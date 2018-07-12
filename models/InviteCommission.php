<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invite_commission".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $action_user_id
 * @property integer $money
 * @property integer $commission
 * @property integer $type
 * @property string $desc
 * @property integer $created_time
 */
class InviteCommission extends \yii\db\ActiveRecord
{
    const TYPE_PAY = 1;//消费
    const TYPE_RECHARGE = 2;//充值到余额
    const TYPE_WITHDRAW = 3;//提现

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite_commission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'action_user_id', 'money', 'commission', 'type', 'desc', 'created_time'], 'required'],
            [['user_id', 'action_user_id', 'money', 'commission', 'type', 'created_time'], 'integer'],
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
            'action_user_id' => 'Action User ID',
            'money' => 'Money',
            'commission' => 'Commission',
            'type' => 'Type',
            'desc' => 'Desc',
            'created_time' => 'Created Time',
        ];
    }
}
