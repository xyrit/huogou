<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jdcard_buyback_list".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $user_id
 * @property string $mobile
 * @property string $pay_type
 * @property string $pay_accounts
 * @property string $pay_name
 * @property string $pay_money
 * @property string $pay_no
 * @property string $pay_desc
 * @property integer $pay_status
 * @property string $bank_name
 * @property integer $product_id
 * @property integer $add_time
 * @property integer $pay_time
 * @property integer $admin_id
 * @property integer $face_value
 * @property integer $period_id
 * @property integer $order_type
 */
class JdcardBuybackList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jdcard_buyback_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'mobile'], 'required'],
            [['order_type','order_id', 'user_id', 'pay_status', 'product_id', 'add_time', 'pay_time', 'admin_id', 'face_value', 'period_id'], 'integer'],
            [['pay_money'], 'number'],
            [['pay_desc'], 'string'],
            [['mobile'], 'string', 'max' => 15],
            [['pay_type'], 'string', 'max' => 20],
            [['pay_accounts'], 'string', 'max' => 30],
            [['pay_name'], 'string', 'max' => 10],
            [['pay_no'], 'string', 'max' => 60],
            [['bank_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'mobile' => 'Mobile',
            'pay_type' => 'Pay Type',
            'pay_accounts' => 'Pay Accounts',
            'pay_name' => 'Pay Name',
            'pay_money' => 'Pay Money',
            'pay_no' => 'Pay No',
            'pay_desc' => 'Pay Desc',
            'pay_status' => 'Pay Status',
            'bank_name' => 'Bank Name',
            'product_id' => 'Product ID',
            'add_time' => 'Add Time',
            'pay_time' => 'Pay Time',
            'admin_id' => 'Admin ID',
            'face_value' => 'Face Value',
            'period_id' => 'Period ID',
            'order_type' => 'order_type',
        ];
    }
}
