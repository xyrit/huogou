<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "friend_apply".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $apply_userid
 * @property integer $status
 * @property integer $apply_time
 */
class FriendApply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'friend_apply';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'apply_userid', 'apply_time'], 'required'],
            [['user_id', 'apply_userid', 'status', 'apply_time'], 'integer']
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
            'apply_userid' => 'Apply Userid',
            'status' => 'Status',
            'apply_time' => 'Apply Time',
        ];
    }
}
