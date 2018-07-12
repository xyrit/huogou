<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "act_deliver".
 *
 * @property string $id
 * @property integer $status
 * @property integer $confirm_userid
 * @property integer $confirm_time
 * @property string $send
 * @property string $platform
 * @property string $third_order
 * @property double $price
 * @property string $standard
 * @property string $mark_text
 * @property string $deliver_company
 * @property string $deliver_order
 * @property integer $prepare_userid
 * @property integer $prepare_time
 * @property integer $deliver_userid
 * @property integer $deliver_time
 * @property string $bill
 * @property string $bill_time
 * @property string $bill_num
 * @property string $payment
 * @property string $deliver_cost
 * @property integer $select_prepare
 */
class ActDeliver extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_deliver';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'confirm_userid', 'confirm_time'], 'required'],
            [['status', 'confirm_userid', 'confirm_time', 'prepare_userid', 'prepare_time', 'deliver_userid', 'deliver_time', 'select_prepare'], 'integer'],
            [['price'], 'number'],
            [['mark_text'], 'string'],
            [['id'], 'string', 'max' => 25],
            [['send', 'platform', 'deliver_company', 'bill_time', 'payment'], 'string', 'max' => 100],
            [['third_order', 'standard', 'deliver_order', 'bill_num'], 'string', 'max' => 255],
            [['bill', 'deliver_cost'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'confirm_userid' => 'Confirm Userid',
            'confirm_time' => 'Confirm Time',
            'send' => 'Send',
            'platform' => 'Platform',
            'third_order' => 'Third Order',
            'price' => 'Price',
            'standard' => 'Standard',
            'mark_text' => 'Mark Text',
            'deliver_company' => 'Deliver Company',
            'deliver_order' => 'Deliver Order',
            'prepare_userid' => 'Prepare Userid',
            'prepare_time' => 'Prepare Time',
            'deliver_userid' => 'Deliver Userid',
            'deliver_time' => 'Deliver Time',
            'bill' => 'Bill',
            'bill_time' => 'Bill Time',
            'bill_num' => 'Bill Num',
            'payment' => 'Payment',
            'deliver_cost' => 'Deliver Cost',
            'select_prepare' => 'Select Prepare',
        ];
    }
}
