<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pk_orders".
 *
 * @property string $id
 * @property string $product_id
 * @property string $period_id
 * @property string $desk_id
 * @property integer $ship_status
 * @property string $is_virtual
 * @property string $shipping_id
 * @property string $shipping
 * @property string $user_id
 * @property integer $status
 * @property integer $confirm
 * @property string $ship_mobile
 * @property integer $price
 * @property string $memo
 * @property string $mark_text
 * @property integer $fail_type
 * @property string $fail_id
 * @property string $create_time
 * @property string $last_modified
 * @property integer $confirm_addr_time
 * @property integer $confirm_goods_time
 * @property string $remark
 * @property integer $push_msg
 * @property integer $delay
 * @property integer $before_status
 * @property string $ship_addr
 * @property string $ship_zip
 * @property string $ship_email
 * @property string $ship_time
 * @property string $ship_area
 * @property string $ship_name
 * @property string $size
 */
class PkOrders extends \yii\db\ActiveRecord
{

    const STATUS_INIT = 0;
    const STATUS_COMMIT_ADDRESS = 1;
    const STATUS_COMFIRM_ADDRESS = 2;
    const STATUS_PREPARE_GOODS = 3;
    const STATUS_SHIPPING = 4;
    const STATUS_COMFIRM_RECEIVE = 5;
    const STATUS_REJECT = 6;

    const LIMIT_JUMP = 'ORDER_LIMIT_JUMP_'; //order_id

    public static $status_name = [
        0 => '已中奖',
        1 => '待确认',
        2 => '待备货',
        3 => '待发货',
        4 => '待收货',
        5 => '待晒单',
        6 => '换货',
        7 => '发货异常',
        8 => '已完成'
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pk_orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['size','product_id', 'period_id', 'desk_id', 'ship_status', 'is_virtual', 'shipping_id', 'user_id', 'status', 'confirm', 'price', 'fail_type', 'create_time', 'last_modified', 'confirm_addr_time', 'confirm_goods_time', 'push_msg', 'delay', 'before_status'], 'integer'],
            [['user_id', 'price', 'create_time'], 'required'],
            [['memo', 'mark_text', 'remark'], 'string'],
            [['shipping'], 'string', 'max' => 100],
            [['ship_mobile', 'ship_email', 'ship_time', 'ship_area', 'ship_name'], 'string', 'max' => 25],
            [['fail_id', 'ship_addr'], 'string', 'max' => 255],
            [['ship_zip'], 'string', 'max' => 20],
            [['period_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'period_id' => 'Period ID',
            'desk_id' => 'Desk ID',
            'ship_status' => 'Ship Status',
            'is_virtual' => 'Is Virtual',
            'shipping_id' => 'Shipping ID',
            'shipping' => 'Shipping',
            'user_id' => 'User ID',
            'status' => 'Status',
            'confirm' => 'Confirm',
            'ship_mobile' => 'Ship Mobile',
            'price' => 'Price',
            'memo' => 'Memo',
            'mark_text' => 'Mark Text',
            'fail_type' => 'Fail Type',
            'fail_id' => 'Fail ID',
            'create_time' => 'Create Time',
            'last_modified' => 'Last Modified',
            'confirm_addr_time' => 'Confirm Addr Time',
            'confirm_goods_time' => 'Confirm Goods Time',
            'remark' => 'Remark',
            'push_msg' => 'Push Msg',
            'delay' => 'Delay',
            'before_status' => 'Before Status',
            'ship_addr' => 'Ship Addr',
            'ship_zip' => 'Ship Zip',
            'ship_email' => 'Ship Email',
            'ship_time' => 'Ship Time',
            'ship_area' => 'Ship Area',
            'ship_name' => 'Ship Name',
        ];
    }
}
