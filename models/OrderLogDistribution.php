<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order_log_100".
 *
 * @property integer $id
 * @property string $order_id
 * @property integer $op_id
 * @property string $op_name
 * @property integer $alt_time
 * @property integer $behavior
 * @property integer $result
 * @property string $log_text
 * @property string $addon
 */
class OrderLogDistribution extends \yii\db\ActiveRecord
{

    private static $_orderId;

    public static function instantiate($row)
    {
        return new static(static::$_orderId);
    }

    public function __construct($orderId, $config = [])
    {
        parent::__construct($config);
        static::$_orderId = $orderId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $tableId = substr(static::$_orderId, -1);
        return 'order_log_' . (int)$tableId;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'result'], 'required'],
            [['op_id', 'alt_time', 'behavior', 'result'], 'integer'],
            [['addon'], 'string'],
            [['order_id', 'op_name'], 'string', 'max' => 25],
            [['log_text'], 'string', 'max' => 255]
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
            'op_id' => 'Op ID',
            'op_name' => 'Op Name',
            'alt_time' => 'Alt Time',
            'behavior' => 'Behavior',
            'result' => 'Result',
            'log_text' => 'Log Text',
            'addon' => 'Addon',
        ];
    }

    /**
     * @param $orderId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByOrderId($orderId) {
        $model = new static($orderId);
        return $model::find();
    }

    /**
     * @param $orderId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByOrderId($orderId, $condition)
    {
        $model = new static($orderId);
        return $model::findOne($condition);
    }

    /**
     * @param $orderId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByOrderId($orderId, $condition)
    {
        $model = new static($orderId);
        return $model::findAll($condition);
    }
}
