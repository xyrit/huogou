<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "euro_cup_orders".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property string $game_date
 * @property string $team
 * @property integer $money
 * @property integer $created_at
 * @property integer $pay_at
 */
class EuroCupOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'euro_cup_orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'game_date', 'team', 'money', 'created_at', 'pay_at'], 'required'],
            [['user_id', 'status', 'money', 'created_at', 'pay_at'], 'integer'],
            [['game_date'], 'string', 'max' => 8],
            [['team'], 'string', 'max' => 25],
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
            'status' => 'Status',
            'game_date' => 'Game Date',
            'team' => 'Team',
            'money' => 'Money',
            'created_at' => 'Created At',
            'pay_at' => 'Pay At',
        ];
    }
}
