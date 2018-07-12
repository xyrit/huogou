<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_messages".
 *
 * @property string $id
 * @property string $to_userid
 * @property integer $type
 * @property string $message
 * @property integer $view
 * @property string $created_at
 */
class UserMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_userid', 'created_at'], 'required'],
            [['to_userid', 'type', 'view', 'created_at'], 'integer'],
            [['message'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_userid' => 'To Userid',
            'type' => 'Type',
            'message' => 'Message',
            'view' => 'View',
            'created_at' => 'Created At',
        ];
    }

    public static function addMessage($uid, $type, $message)
    {
        $model = new UserMessage();
        $model->to_userid = $uid;
        $model->type = $type;
        $model->view = 0;
        $model->created_at = time();
        $model->message = $message;
        $model->save();
    }
}
