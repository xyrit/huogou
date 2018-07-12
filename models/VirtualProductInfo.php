<?php

namespace app\models;

use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "virtual_product_info".
 *
 * @property string $id
 * @property string $user_id
 * @property string $order_id
 * @property integer $type
 * @property string $account
 * @property string $name
 * @property string $created_at
 */
class VirtualProductInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'virtual_product_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'account', 'created_at'], 'required'],
            [['user_id', 'type', 'created_at'], 'integer'],
            [['order_id'], 'string', 'max' => 25],
            [['account', 'name'], 'string', 'max' => 32],
            [['order_id'], 'unique']
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
            'order_id' => 'Order ID',
            'type' => 'Type',
            'account' => 'Account',
            'name' => 'Name',
            'created_at' => 'Created At',
        ];
    }
}
