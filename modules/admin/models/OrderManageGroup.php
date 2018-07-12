<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "order_manage_groups".
 *
 * @property integer $id
 * @property string $name
 * @property string $by_uid
 * @property integer $created_at
 * @property integer $updated_at
 */
class OrderManageGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_manage_groups';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'by_uid', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at', 'by_uid'], 'integer'],
            [['name'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'by_uid' => 'By Uid',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
