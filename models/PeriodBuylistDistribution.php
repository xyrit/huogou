<?php

namespace app\models;

use app\helpers\MyRedis;
use app\services\Pay;
use Yii;

/**
 * This is the model class for table "period_buylist_100".
 *
 * @property string $id
 * @property string $payment_order_item_id
 * @property integer $product_id
 * @property string $period_id
 * @property integer $user_id
 * @property string $buy_num
 * @property string $codes
 * @property integer $ip
 * @property integer $source
 * @property string $buy_time
 */
class PeriodBuylistDistribution extends \yii\db\ActiveRecord
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
        return 'period_buylist_' . $tableId;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'period_id', 'user_id', 'buy_num', 'ip', 'buy_time'], 'required'],
            [['product_id', 'period_id', 'user_id', 'buy_num', 'ip', 'source'], 'integer'],
            [['codes'], 'string'],
            [['buy_time'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_order_item_id' => 'Payment Order Item ID',
            'product_id' => 'Product ID',
            'period_id' => 'Period ID',
            'user_id' => 'User ID',
            'buy_num' => 'Buy Num',
            'codes' => 'Codes',
            'ip' => 'Ip',
            'source' => 'Source',
            'buy_time' => 'Buy Time',
        ];
    }

    /**
     * @param $tableId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByTableId($tableId)
    {
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
     * 总参与次数
     * @param  string $tableName [description]
     * @return [type]            [description]
     */
    public static function findTotalBuyCount($tableNum = '10'){
        $redis = new MyRedis();
        if ($userBuySum = $redis->get(Pay::USER_BUY_SUM_KEY)) {
            return $userBuySum;
        }
        $sql = "";
        for ($i=0; $i < $tableNum; $i++) { 
            $model = new static('10'.$i);
            $sql .= "(select sum(buy_num) as total from ".$model::tableName('10'.$i)." ) union all ";
        }
        $sql = "select sum(total) as total from (".substr($sql,0,-10).") a";

        $command = \Yii::$app->db->createCommand($sql);
        $result = $command->queryScalar();
        return $result;
    }

    /**
     * 购买期数列表
     * @param  string $tableName [description]
     * @return [type]            [description]
     */
    public static function getList($tableNum = '10', $startTime = '', $endTime = '' , $pageSize = 20, $type = 0){
        $sql = "";
        if($type == 1) $buy_num = ' max(total) ';
        else $buy_num = ' sum(total) ';

        for ($i=0; $i < $tableNum; $i++) {
            $sql .= "(select ".$buy_num." as total, buy_time ,user_id from payment_orders_10".$i." where status = 1 and buy_time > '".$startTime."' and buy_time < '".$endTime."' group by user_id order by total desc limit ".$pageSize." ) union ";
        }
        $sql = "select total,user_id from (".substr($sql,0,-6).") a order by total desc,buy_time desc limit ".$pageSize;

        $command = \Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }
}
