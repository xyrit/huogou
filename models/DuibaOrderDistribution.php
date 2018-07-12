<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "duiba_orders".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $order_no
 * @property integer $credits
 * @property string $appKey
 * @property string $timestamp
 * @property string $description
 * @property string $order_num
 * @property string $type
 * @property integer $face_price
 * @property integer $actual_price
 * @property string $ip
 * @property integer $wait_audit
 * @property integer $audit_status
 * @property string $params
 * @property string $sign
 * @property integer $status
 * @property string $error_msg
 * @property integer $created_at
 * @property integer $updated_at
 */
class DuibaOrderDistribution extends \yii\db\ActiveRecord
{

    private static $_tableId;

    public static function instantiate($row)
    {
        return new static(static::$_tableId);
    }

    public function __construct($tableId, $config = [])
    {
        parent::__construct($config);
        static::$_tableId = $tableId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $tableId = substr(static::$_tableId, 0, 3);
        return 'duiba_orders_' . $tableId;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_no', 'order_num', 'type', 'face_price', 'actual_price', 'sign', 'created_at', 'updated_at'], 'required'],
            [['user_id', 'credits', 'timestamp', 'face_price', 'actual_price', 'wait_audit', 'audit_status', 'status', 'created_at', 'updated_at'], 'integer'],
            [['order_no'], 'string', 'max' => 25],
            [['appKey', 'description', 'order_num', 'type', 'ip', 'params', 'sign', 'error_msg'], 'string', 'max' => 255]
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
            'order_no' => 'Order No',
            'credits' => 'Credits',
            'appKey' => 'App Key',
            'timestamp' => 'Timestamp',
            'description' => 'Description',
            'order_num' => 'Order Num',
            'type' => 'Type',
            'face_price' => 'Face Price',
            'actual_price' => 'Actual Price',
            'ip' => 'Ip',
            'wait_audit' => 'Wait Audit',
            'audit_status' => 'Audit Status',
            'params' => 'Params',
            'sign' => 'Sign',
            'status' => 'Status',
            'error_msg' => 'Error Msg',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /** 生成订单Id
     * @param $userHomeId
     * @return string
     */
    public static function generateOrderId($userHomeId)
    {
        list($sec, $usec) = explode('.', microtime(true));
        $usec = !empty($usec) ? substr($usec, 0, 3) : '0';
        $usec = str_pad($usec,3,'0',STR_PAD_RIGHT);
        $orderId = date('YmdHis') . $usec . mt_rand(1000, 9999) . '4';
        $orderId = substr($userHomeId, 0, 3) . $orderId;
        return $orderId;
    }

    /**
     * @param $userHomeId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByTableId($tableId) {
        $model = new static($tableId);
        return $model::find();
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByTableId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findOne($condition);
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByTableId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findAll($condition);
    }

}
