<?php

namespace app\modules\member\models;

use Yii;

/**
 * This is the model class for table "cards".
 *
 * @property string $id
 * @property string $number
 * @property string $password
 * @property string $money
 * @property integer $status
 */
class Card extends \yii\db\ActiveRecord
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
            [['number', 'password'], 'required'],
            [['money', 'status'], 'integer'],
            [['number'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => 'Number',
            'password' => 'Password',
            'money' => 'Money',
            'status' => 'Status',
        ];
    }
}
