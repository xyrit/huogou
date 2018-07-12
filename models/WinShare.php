<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "win_share".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $share
 * @property integer $status
 * @property integer $red_id
 * @property integer $add_time
 */
class WinShare extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'win_share';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'status', 'red_id', 'add_time'], 'integer'],
            [['share'], 'string']
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
            'share' => 'Share',
            'status' => 'Status',
            'red_id' => 'Red ID',
            'add_time' => 'Add Time',
        ];
    }
}