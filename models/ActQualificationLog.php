<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "act_qualification_log".
 *
 * @property string $id
 * @property string $user_id
 * @property string $num
 * @property integer $type
 * @property string $created_at
 */
class ActQualificationLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_qualification_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at'], 'required'],
            [['user_id', 'num', 'type', 'created_at'], 'integer']
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
            'num' => 'Num',
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }
}
