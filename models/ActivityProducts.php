<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_products".
 *
 * @property string $id
 * @property string $bn
 * @property string $barcode
 * @property string $name
 * @property string $price
 * @property integer $face_value
 * @property integer $marketable
 * @property string $cost
 * @property string $cat_id
 * @property string $brand_id
 * @property integer $total
 * @property string $store
 * @property integer $allow_share
 * @property integer $is_recommend
 * @property integer $list_order
 * @property string $brief
 * @property string $intro
 * @property string $picture
 * @property string $created_at
 * @property string $updated_at
 * @property string $delivery_id
 * @property string $order_manage_gid
 * @property string $tag
 * @property string $keywords
 * @property integer $admin_id
 * @property string $live_time
 * @property integer $app
 * @property integer $display
 * @property integer $activity_id
 * @property integer $left_time
 * @property integer $is_virtual
 */
class ActivityProducts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'price', 'cat_id', 'brand_id', 'picture', 'created_at', 'updated_at', 'order_manage_gid', 'left_time'], 'required'],
            [['price', 'face_value', 'marketable', 'cost', 'cat_id', 'brand_id', 'total', 'store', 'allow_share', 'is_recommend', 'list_order', 'created_at', 'updated_at', 'order_manage_gid', 'admin_id', 'live_time', 'app', 'display', 'activity_id', 'left_time', 'is_virtual'], 'integer'],
            [['intro'], 'string'],
            [['bn', 'name', 'brief', 'picture', 'tag', 'keywords'], 'string', 'max' => 255],
            [['barcode'], 'string', 'max' => 25],
            [['delivery_id'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bn' => 'Bn',
            'barcode' => 'Barcode',
            'name' => 'Name',
            'price' => 'Price',
            'face_value' => 'Face Value',
            'marketable' => 'Marketable',
            'cost' => 'Cost',
            'cat_id' => 'Cat ID',
            'brand_id' => 'Brand ID',
            'total' => 'Total',
            'store' => 'Store',
            'allow_share' => 'Allow Share',
            'is_recommend' => 'Is Recommend',
            'list_order' => 'List Order',
            'brief' => 'Brief',
            'intro' => 'Intro',
            'picture' => 'Picture',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'delivery_id' => 'Delivery ID',
            'order_manage_gid' => 'Order Manage Gid',
            'tag' => 'Tag',
            'keywords' => 'Keywords',
            'admin_id' => 'Admin ID',
            'live_time' => 'Live Time',
            'app' => 'App',
            'display' => 'Display',
            'activity_id' => 'Activity ID',
            'left_time' => 'Left Time',
            'is_virtual' => 'Is Virtual',
        ];
    }
}
