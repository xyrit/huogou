<?php

namespace app\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "recharge_orders_100".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $status
 * @property integer $post_money
 * @property integer $money
 * @property integer $payment
 * @property string $bank
 * @property string $result
 * @property integer $create_time
 * @property integer $pay_time
 */
class RechargeOrderDistribution extends \yii\db\ActiveRecord
{
    const STATUS_PAID = 1;
    const STATUS_UNPAY = 0;

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
        return 'recharge_orders_' . $tableId;
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
            [['id', 'user_id', 'post_money', 'money', 'payment', 'create_time', 'pay_time'], 'required'],
            [['user_id', 'status', 'type', 'post_money', 'money', 'payment', 'create_time', 'pay_time'], 'integer'],
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
            'post_money' => 'Post Money',
            'money' => 'Money',
            'payment' => 'Payment',
            'bank' => 'Bank',
            'create_time' => 'Create Time',
            'pay_time' => 'Pay Time',
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

    /** 生成订单Id
     * @param $userHomeId
     * @return string
     */
    public static function generateOrderId($userHomeId, $orderSub = '1')
    {

        list($sec, $usec) = explode('.', microtime(true));
        $usec = !empty($usec) ? substr($usec, 0, 3) : '0';
        $usec = str_pad($usec,3,'0',STR_PAD_RIGHT);
        $orderId = date('YmdHis') . $usec . mt_rand(100, 999) . $orderSub;
        $orderId = substr($userHomeId, 0, 3) . $orderId;
        return $orderId;
    }

    /**
     * 充值订单列表
     * @param array $where
     * @param int $page
     * @param int $perpage
     * @return array
     */
    public static function rechargeOrderList($where = [], $page = 1, $perpage = 10)
    {
        $condition = ' where 1=1 ';
        if (empty($where)) {
            $condition = '';
        } else {
            if (isset($where['status']) && $where['status'] != '-1') {
                $condition .= ' and status = ' . $where['status'];
            }
            if (isset($where['payment']) && $where['payment'] != '-1') {
                $condition .= ' and payment = ' . $where['payment'];
            }
            if (isset($where['source']) && $where['source'] != '0' && $where['payment'] == 3) {
                $condition .= ' and bank = "'.$where['source'].'"';
            }
            if (isset($where['id']) && $where['id'] != '') {
                $condition .= ' and id = "' . $where['id'] . '"';
            }
            if (isset($where['account']) && $where['account'] != '') {
                $condition .= ' and user_id = ' . $where['account'] . '';
            }
            if (isset($where['startTime']) && !empty($where['startTime']) && isset($where['endTime']) && !empty($where['endTime'])) {
                $condition .= ' and pay_time BETWEEN ' . $where['startTime'] . ' AND ' . $where['endTime'];
            }
        }

        $item_sql = '';
        for ($i = 100; $i <= 109; $i++) {
            $item_sql .= '(SELECT count(1) as cnt FROM recharge_orders_' . $i .$condition. ' ) union all';
        }

        $item_sql = rtrim($item_sql, 'union all');

        $connection = \Yii::$app->db;

        $countSql = 'SELECT sum(cnt) as total FROM (' . $item_sql . ') as r ';
        $c = $connection->createCommand($countSql);
        $totalCount = $c->queryScalar();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1, 'defaultPageSize'=>$perpage, 'pageSizeLimit'=>[1,$perpage]]);

        $select = 'id, user_id, post_money, result, payment, bank, source, status, pay_time, create_time';
        $items_sql = '';
        for ($i = 100; $i <= 109; $i++) {
            $items_sql .= '(SELECT '.$select.' FROM recharge_orders_' . $i .$condition. ' ORDER BY create_time DESC limit ' . $pagination->offset . ',' . $pagination->limit .') union all';
        }
        $items_sql = rtrim($items_sql, 'union all');
        $querySql = 'SELECT r.*, users.phone,users.email FROM (' . $items_sql . ') as r left join users on r.user_id = users.id ORDER BY r.create_time DESC limit ' . $pagination->offset . ',' . $pagination->limit;

        $command = $connection->createCommand($querySql);
        $result = $command->queryAll();

        $items_sql = '';
        for ($i = 100; $i <= 109; $i++) {
            $items_sql .= '(SELECT sum(money) as sumMoney FROM recharge_orders_' . $i .$condition. ' ) union all';
        }
        $items_sql = rtrim($items_sql, 'union all');

        $sumQuery = $connection->createCommand('SELECT sum(r.sumMoney) as total FROM (' . $items_sql . ') as r ');
        $sum = $sumQuery->queryOne();

        return ['list'=>$result, 'pagination'=>$pagination, 'money'=>$sum['total']];
    }

    /**
     * 订单详情
     **/
    public static function rechargeOrderDetail($orderId, $tableNum = 10)
    {
        $items_sql = '';

        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $items_sql .= '(SELECT * FROM recharge_orders_'.$tableId.') ';
            }else{
                $items_sql .= '(SELECT * FROM recharge_orders_'.$tableId.' ) union all';
            }
        }

        $connection = \Yii::$app->db;
        $querySql = 'SELECT * FROM ('.$items_sql.') as a where a.id= "'.$orderId.'"';
        $command = $connection->createCommand($querySql);
        $result = $command->queryOne();
        return $result;
    }

    public static function getType($type)
    {
        switch ($type) {
            case '1':
                return array('name'=>'储蓄卡');
                break;
            case '2':
                return array('name'=>'信用卡');
                break;
            case '3':
                return array('name'=>'充值平台');
                break;
            case '4':
                return array('name'=>'佣金');
                break;
            case '5':
                return array('name'=>'充值卡');
                break;
            case '6':
                return array('name'=>'兑换伙购币');
                break;
            case '7':
                return array('name'=>'平台赠送');
                break;
            default:
                return array('name'=>'充值平台');
                break;
        }
    }

    public static function getPaymentBank($bank)
    {
        switch ($bank) {
            case 'zhifukachat':
                return '微信';
                break;
            case 'jd':
                return '京东支付';
                break;
            case 'zhifukaqq':
                return '手q支付';
                break;
            case 'iapp':
                return '爱贝支付';
                break;
            case 'kq':
                return '快钱';
                break;
            case 'chinaBank':
                return '网银在线';
                break;
            case 'union':
                return '银联';
                break;
            default:
                return array('name'=>'充值平台');
                break;
        }
    }
}
