<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cards".
 *
 * @property string $id
 * @property string $batch_id
 * @property string $card
 * @property string $pwd
 * @property string $price
 * @property integer $status
 * @property string $user_id
 * @property string $used_at
 */
class Cards extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cards';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_id', 'card', 'pwd'], 'required'],
            [['batch_id', 'price', 'status', 'user_id', 'used_at'], 'integer'],
            [['card'], 'string', 'max' => 50],
            [['pwd'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_id' => 'Batch ID',
            'card' => 'Card',
            'pwd' => 'Pwd',
            'price' => 'Price',
            'status' => 'Status',
            'user_id' => 'User ID',
            'used_at' => 'Used At',
        ];
    }
}
