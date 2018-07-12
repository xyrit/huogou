<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "free_products".
 *
 * @property integer $id
 * @property string $name
 * @property string $brief
 * @property string $intro
 * @property integer $price
 * @property integer $marketable
 * @property string $picture
 * @property string $bn
 * @property string $barcode
 * @property integer $delivery_id
 * @property integer $order_manage_gid
 * @property integer $total_period
 * @property integer $list_order
 * @property integer $start_type
 * @property string $start_time
 * @property integer $after_end
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $admin_id
 */
class FreeProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'free_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'price', 'picture', 'delivery_id', 'order_manage_gid', 'start_time', 'after_end', 'created_at', 'updated_at'], 'required'],
            [['price', 'marketable', 'delivery_id', 'order_manage_gid', 'total_period', 'list_order', 'start_type', 'after_end', 'created_at', 'updated_at', 'admin_id'], 'integer'],
            [['name', 'brief', 'picture', 'bn'], 'string', 'max' => 255],
            [['barcode'], 'string', 'max' => 25],
            [['start_time'], 'string', 'max' => 20],
            [['intro'], 'string'],
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
            'brief' => 'Brief',
            'price' => 'Price',
            'marketable' => 'Marketable',
            'picture' => 'Picture',
            'bn' => 'Bn',
            'barcode' => 'Barcode',
            'delivery_id' => 'Delivery ID',
            'order_manage_gid' => 'Order Manage Gid',
            'total_period' => 'Total Period',
            'list_order' => 'List Order',
            'start_type' => 'Start Type',
            'start_time' => 'Start Time',
            'after_end' => 'After End',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'admin_id' => 'Admin ID',
        ];
    }
}
