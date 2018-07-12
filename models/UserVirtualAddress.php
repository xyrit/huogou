<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_virtual_address".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $type
 * @property string $account
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserVirtualAddress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_virtual_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id',   'created_at', 'updated_at'], 'integer'],
            [['account', 'name'], 'string', 'max' => 64]
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
            'account' => 'Account',
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Update At',
        ];
    }
}
