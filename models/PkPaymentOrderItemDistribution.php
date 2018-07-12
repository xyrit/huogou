<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pk_payment_order_items_100".
 *
 * @property string $id
 * @property string $payment_order_id
 * @property integer $product_id
 * @property string $period_id
 * @property string $period_no
 * @property integer $user_id
 * @property integer $post_nums
 * @property string $nums
 * @property string $buy_tables
 * @property string $buy_size
 * @property string $item_buy_time
 * @property integer $source
 */
class PkPaymentOrderItemDistribution extends \yii\db\ActiveRecord
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
        return 'pk_payment_order_items_' . $tableId;
    }

    public static function getTableIdByOrderId($orderId)
    {
        return substr($orderId, 0, 3);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_order_id', 'product_id', 'period_id', 'period_no', 'post_nums','buy_size', 'nums'], 'required'],
            [['product_id', 'period_id', 'user_id', 'post_nums', 'nums', 'buy_size', 'source'], 'integer'],
            [['buy_tables'], 'string'],
            [['payment_order_id', 'period_no'], 'string', 'max' => 25],
            [['item_buy_time'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_order_id' => 'Payment Order ID',
            'product_id' => 'Product ID',
            'period_id' => 'Period ID',
            'period_no' => 'Period No',
            'user_id' => 'User ID',
            'post_nums' => 'Post Nums',
            'nums' => 'Nums',
            'buy_tables' => 'Buy Tables',
            'item_buy_time' => 'Item Buy Time',
            'source' => 'Source',
        ];
    }

    /**
     * @param $tableId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByTableId($tableId) {
        $model = new static($tableId);
        return $model::find();
    }

    /**
     * @param $tableId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByTableId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findOne($condition);
    }

    /**
     * @param $tableId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByTableId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findAll($condition);
    }

    /**
     * @param $tableId
     * @param $attributes
     * @param string $condition
     * @param array $params
     * @return mixed
     */
    public static function updateAllByTableId($tableId, $attributes, $condition = '', $params = [])
    {
        $model = new static($tableId);
        return $model::updateAll($attributes, $condition, $params);
    }

    /**
     * 最后购买记录
     * @param  int  $time 最后时间
     * @param  int $num  数量
     * @return [type]        [description]
     */
    public static function lastBuy($time,$num = 100,$tableNum = 10)
    {
        $items_sql = $orders_sql = '';
        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $items_sql .= '(SELECT payment_order_id,item_buy_time,nums,product_id,period_no,period_id,user_id FROM pk_payment_order_items_'.$tableId.' where item_buy_time <= '.$time.' and item_buy_time > 0 and nums > 0 ORDER BY item_buy_time desc limit 0,'.$num.') ';
            }else{
                $items_sql .= '(SELECT payment_order_id,item_buy_time,nums,product_id,period_no,period_id,user_id FROM pk_payment_order_items_'.$tableId.' where item_buy_time <= '.$time.' and item_buy_time > 0 and nums > 0 ORDER BY item_buy_time desc limit 0,'.$num.' ) union all';
            }
        }

        $querySql = 'SELECT * FROM ('.$items_sql.') as a ORDER BY a.item_buy_time desc limit 0,'.$num;

        $connection = Yii::$app->db;
        $command = $connection->createCommand($querySql);
        $result = $command->queryAll();

        return $result;
    }
}
