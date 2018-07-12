<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mp_user".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $open_id
 * @property string $union_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class MpUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mp_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['open_id'], 'string', 'max' => 50],
            [['user_id'], 'unique'],
            [['open_id'], 'unique'],
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
            'open_id' => 'Open ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
