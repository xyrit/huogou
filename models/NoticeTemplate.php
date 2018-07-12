<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notice_template".
 *
 * @property integer $id
 * @property string $desc
 * @property string $notice_way
 * @property string $title
 * @property string $sms_content
 * @property string $email_content
 * @property string $sysmsg_content
 * @property string $wechat_content
 * @property string $app_content
 * @property integer $status
 * @property integer $op_user
 * @property integer $updated_at
 */
class NoticeTemplate extends \yii\db\ActiveRecord
{

    const WAY_SMS = 1;
    const WAY_EMAIL = 2;
    const WAY_SYSMSG = 3;
    const WAY_WECHAT = 4;
    const WAY_APP = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notice_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['desc', 'notice_way', 'title', 'op_user', 'updated_at'], 'required'],
            [['sms_content', 'email_content', 'sysmsg_content','wechat_content','app_content'], 'string'],
            [['status', 'op_user', 'updated_at'], 'integer'],
            [['desc', 'notice_way', 'title'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'desc' => 'Desc',
            'notice_way' => 'Notice Way',
            'title' => 'Title',
            'sms_content' => 'Sms Content',
            'email_content' => 'Email Content',
            'sysmsg_content' => 'Sysmsg Content',
            'wechat_content' => 'Wechat Content',
            'app_content' => 'App Content',
            'status' => 'Status',
            'op_user' => 'Op User',
            'updated_at' => 'Updated At',
        ];
    }
}
