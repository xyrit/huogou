<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "euro_reward_log".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $order_id
 * @property string $reward_obj
 * @property string $obj_id
 * @property string $price
 * @property string $created_at
 */
class EuroRewardLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'euro_reward_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'reward_obj', 'obj_id', 'created_at'], 'required'],
            [['user_id', 'price'], 'integer'],
            [['order_id', 'reward_obj', 'obj_id'], 'string', 'max' => 8],
            [['created_at'], 'string', 'max' => 16],
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
            'order_id' => 'Order ID',
            'reward_obj' => 'Reward Obj',
            'obj_id' => 'Obj ID',
            'created_at' => 'Created At',
        ];
    }
}
