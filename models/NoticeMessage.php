<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notice_messages".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $mode
 * @property string $type_name
 * @property string $message
 * @property integer $status
 * @property string $created_at
 */
class NoticeMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notice_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at'], 'required'],
            [[ 'mode', 'status', 'created_at'], 'integer'],
            [['type_name', 'user_id'], 'string', 'max' => 100],
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
            'mode' => 'Mode',
            'type_name' => 'Type Name',
            'message' => 'Message',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    public static function addMessage($uid, $mode, $type_name, $message, $ip, $status = 0)
    {
        $model = new NoticeMessage();
        $model->user_id = $uid;
        $model->mode = $mode;
        $model->type_name = $type_name;
        $model->message = $message;
        $model->status = $status;
        $model->ip = $ip;
        $model->created_at = time();
        $model->save(false);
    }
}
