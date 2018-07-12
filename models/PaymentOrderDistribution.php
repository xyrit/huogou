<?php

namespace app\models;

use app\helpers\DateFormat;
use Yii;
use app\models\PaymentOrderItemDistribution as PayItem;
use yii\data\Pagination;

/**
 * This is the model class for table "payment_orders_100".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $status
 * @property integer $payment
 * @property string $bank
 * @property integer $money
 * @property integer $point
 * @property integer $total
 * @property integer $ip
 * @property integer $source
 * @property integer $create_time
 * @property integer $buy_time
 */
class PaymentOrderDistribution extends \yii\db\ActiveRecord
{
    const STATUS_PAID = 1;
    const STATUS_UNPAY = 0;

    const PAY_FOR_YYG = 0; //一元购支付
    const PAY_FOR_EURO = 1; //欧洲杯支付
    const PAY_FOR_PK = 2; //PK场支付

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
        return 'payment_orders_' . $tableId;
    }

    public static function getTableIdByOrderId($orderId)
    {
        return substr($orderId, 0, 3);
    }

    public static function getTableIdByUserHomeId($userHomeId)
    {
        return substr($userHomeId, 0, 3);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'payment', 'money', 'point', 'total', 'ip'], 'required'],
            [['user_id', 'status', 'payment', 'money', 'point', 'total', 'user_point', 'ip', 'source'], 'integer'],
            [['bank'], 'string', 'max' => 30]
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
            'status' => 'Status',
            'payment' => 'Payment',
            'bank' => 'Bank',
            'money' => 'Money',
            'point' => 'Point',
            'total' => 'Total',
            'user_point' => 'UserPoint',
            'ip' => 'Ip',
            'source' => 'Source',
            'create_time' => 'Create Time',
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

    /** 生成订单Id
     * @param $userHomeId
     * @return string
     */
    public static function generateOrderId($userHomeId, $orderSub = '2')
    {
        list($sec, $usec) = explode('.', microtime(true));
        $usec = !empty($usec) ? substr($usec, 0, 3) : '0';
        $usec = str_pad($usec,3,'0',STR_PAD_RIGHT);
        $orderId = date('YmdHis') . $usec . mt_rand(100, 999) . $orderSub;
        $orderId = substr($userHomeId, 0, 3) . $orderId;
        return $orderId;
    }

    /**
     * 获取所有订单
     * @param  string $time 时间
     * @param  string $type > < >= <= =
     * @return [type]       [description]
     */
    public static function fetchAllOrdersByTimes($time,$type,$limit,$orderBy="buy_time desc",$tableNum=10,$page = 1,$end_time=''){
        $listSql = $itemSql = "";
        $where = " where buy_time ".$type.$time;
        if ($end_time) {
            $where += " and buy_time <= ".$end_time;
        }
        for ($i=0; $i < $tableNum; $i++) { 
            $model = new static('10'.$i);
            $listSql .= "(select * from ".$model::tableName('10'.$i).$where.") union ";
            $itemModel = new PayItem('10'.$i);
            $itemSql .= "(select * from ".$itemModel::tableName('10'.$i).") union ";
        }
        $listSql = substr($listSql,0,-6);
        $itemSql = substr($itemSql,0,-6);
        $sql = "select * from (".$listSql.") a left join (".$itemSql.") b on a.id=b.payment_order_id order by ".$orderBy." limit ".$limit*($page-1).",".$limit;
       
        $command = \Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }

    public static function getOrder($where = [], $page = 1, $perpage = 25)
    {
        $listSql = "";
        for ($i=0; $i < 10; $i++) {
            $model = new static('10' . $i);
            $listSql .= "(select id,user_id,buy_time,money,point,total,source,payment from " . $model::tableName('10' . $i). ") union ";
        }
        $listSql = substr($listSql,0,-6);

        $condition = ' where 1=1 ';
        if (empty($where)) {
            $condition = '';
        } else {
            if(isset($where['user_id']) && $where['user_id'] != '') $condition .= ' and user_id = '.$where['user_id'].'';
            if(isset($where['id']) && $where['id'] != '') $condition .= ' and id = '.$where['id'].'';
            if(isset($where['status']) && $where['status'] != '0'){
                if($where['status'] == 1){
                    $condition .= ' and (point > 0 or money > 0) ';
                }elseif($where['status'] == 2){
                    $condition .= ' and (point = 0 and money = 0) ';
                }
            }
            if (isset($where['start']) && !empty($where['start']) && isset($where['end']) && !empty($where['end'])) {
                $condition .= ' and buy_time BETWEEN ' . strtotime($where['start']) . ' AND ' . strtotime($where['end']);
            }
        }

        $countsql = "select count(1) from (".$listSql.") as a " .$condition;
        $connection = \Yii::$app->db;
        $c = $connection->createCommand($countsql);
        $totalCount = $c->queryScalar();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1, 'defaultPageSize'=>$perpage,'pageSizeLimit'=>[1,$perpage]]);

        $sql = "select * from (".$listSql.") as a " .$condition.' order by buy_time desc limit '.  $pagination->offset . ',' . $pagination->limit;

        $command = $connection->createCommand($sql);
        $result = $command->queryAll();

        $totalsql = "select sum(money) as totalMoney,sum(point) as totalPoint, sum(total) as total from (".$listSql.") as a " .$condition;
        $tcommand = $connection->createCommand($totalsql);
        $total = $tcommand->queryOne();

        return ['list'=>$result, 'pagination'=>$pagination, 'total'=>$total];
    }
}
