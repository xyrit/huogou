<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "olympic_share_log".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $type
 * @property integer $created_at
 */
class OlympicShareLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'olympic_share_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'created_at'], 'required'],
            [['user_id', 'type', 'created_at'], 'integer'],
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
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }
}
