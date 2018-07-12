<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wx_orders".
 *
 * @property string $id
 * @property integer $uid
 * @property string $mchid
 * @property string $device_info
 * @property string $openid
 * @property string $check_name
 * @property string $re_user_name
 * @property integer $amount
 * @property string $desc
 * @property string $spbill_create_ip
 * @property string $return_code
 * @property string $return_msg
 * @property string $payment_no
 * @property string $payment_time
 * @property integer $add_time
 * @property integer $status
 * @property string $sign
 * @property string partner_trade_no
 */
class WxOrderDistribution extends \yii\db\ActiveRecord
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
        return 'wx_orders_' . $tableId;
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid','amount'], 'integer'],
            [['id','mchid', 'device_info', 'openid', 'check_name', 're_user_name', 'sign','desc','spbill_create_ip','return_code','return_msg','payment_no','payment_time','sign'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '商户订单号',
            'uid' => '用户id',
            'mchid' => '商户号',
            'device_info' => '设备号',
            'openid' => '用户openid',
            'check_name' => '校验用户姓名选项',
            're_user_name' => '收款用户姓名',
            'amount' => '金额',
            'desc' => '企业付款描述信息',
            'spbill_create_ip' => 'Ip地址',
            'return_code' => '返回状态码',
            'return_msg' => '返回信息',
            'payment_no' => '微信订单号',
            'payment_time' => '微信支付成功时间',
            'status' => '微信支付成功时间',
            'add_time' => '创建时间',
            'sign' => '签名',
        ];
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
