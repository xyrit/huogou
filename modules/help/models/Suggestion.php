<?php

namespace app\modules\help\models;

use Yii;

/**
 * This is the model class for table "suggestions".
 *
 * @property string $id
 * @property integer $type
 * @property string $nickname
 * @property integer $phone
 * @property string $email
 * @property string $content
 * @property string $created_at
 */
class Suggestion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'suggestions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'phone', 'created_at'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
            [['nickname'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'nickname' => 'Nickname',
            'phone' => 'Phone',
            'email' => 'Email',
            'content' => 'Content',
            'created_at' => 'Created At',
        ];
    }
}
