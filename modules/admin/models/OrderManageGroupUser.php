<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "order_manage_group_users".
 *
 * @property integer $id
 * @property integer $group_id
 * @property integer $user_id
 */
class OrderManageGroupUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_manage_group_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'user_id'], 'required'],
            [['group_id', 'user_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'user_id' => 'User ID',
        ];
    }
}
