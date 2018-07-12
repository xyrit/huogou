<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "olympic_reward_log".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $reward_obj
 * @property string $obj_id
 * @property integer $price
 * @property string $created_at
 */
class OlympicRewardLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'olympic_reward_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'reward_obj', 'obj_id', 'price', 'created_at'], 'required'],
            [['user_id', 'price'], 'integer'],
            [['reward_obj', 'obj_id'], 'string', 'max' => 8],
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
            'reward_obj' => 'Reward Obj',
            'obj_id' => 'Obj ID',
            'price' => 'Price',
            'created_at' => 'Created At',
        ];
    }
}
