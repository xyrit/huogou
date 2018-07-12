<?php

namespace app\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "payment_order_items_100".
 *
 * @property string $id
 * @property string $payment_order_id
 * @property integer $product_id
 * @property string $period_id
 * @property integer $post_nums
 * @property string $nums
 * @property string $codes
 */
class PaymentOrderItemDistribution extends \yii\db\ActiveRecord
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
        return 'payment_order_items_' . $tableId;
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
            [['payment_order_id', 'product_id', 'period_id', 'post_nums', 'nums'], 'required'],
            [['product_id', 'period_id', 'post_nums', 'nums'], 'integer']
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
            'post_nums' => 'Post Nums',
            'nums' => 'Nums',
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
     * 获取所有订单
     * tableNum 分表数
     **/
    public static function getBuylist($where = [], $page = 1, $perpage = 10, $tableNum = 10,$itemField = '*',$orderby = 'item_buy_time desc')
    {
        $items_sql = '';
        $extraWhere = '';

        if (isset($where['starttime']) && $where['starttime']) {
            $extraWhere .= " where item_buy_time >= '".$where['starttime']."'";
        }

        if (isset($where['endtime']) && $where['endtime']) {
            $extraWhere .= isset($where['starttime']) && !empty($where['starttime']) ? ' and ' : ' where ';
            $extraWhere .= " item_buy_time <= '".$where['endtime']."' and item_buy_time > 0";
        }

        if (isset($where['period_id']) && $where['period_id']) {
            $extraWhere .= $extraWhere ? ' and ' : ' where ';
            $extraWhere .= "  period_id = '".$where['period_id']."'";
        }

        if (isset($where['product_id']) && $where['product_id']) {
            $extraWhere .= $extraWhere ? ' and ' : ' where ';
            $extraWhere .= "  product_id = '".$where['product_id']."'";
        }

        $extraWhere .= $extraWhere ? ' and nums > 0 ' : ' where nums > 0';

        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $items_sql .= '(SELECT id FROM payment_order_items_'.$tableId.$extraWhere.' ) ';
            }else{
                $items_sql .= '(SELECT id FROM payment_order_items_'.$tableId.$extraWhere.' ) union all';
            }
        }

        $connection = \Yii::$app->db;

        $condition = '';

        $countSql = 'SELECT count(1) FROM ('.$items_sql.') as a ';
        $c = $connection->createCommand($countSql);
        $totalCount = $c->queryScalar();
        if(isset($where['orderId']) && $where['orderId'] != ''){
            $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage,'pageSizeLimit'=>[1,$perpage]]);
        }else{
            $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage,'pageSizeLimit'=>[1,$perpage]]);
        }

        if(isset($where['user_id']) && $where['user_id'] != ''){
            $condition .= $condition ? ' and ' : ' where ';
            $condition .= ' user_id = "'.$where['user_id'].'"';
        }
        $items_sql = '';
        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $items_sql .= '(SELECT '.$itemField.' FROM payment_order_items_'.$tableId.$extraWhere.' ) ';
            }else{
                $items_sql .= '(SELECT '.$itemField.' FROM payment_order_items_'.$tableId.$extraWhere.' ) union all';
            }
        }

        if($condition){
            $querySql = 'SELECT * FROM ('.$items_sql.') as a '.$condition.' ORDER BY '.$orderby.' limit '.$pagination->offset.','.$pagination->limit;
        }else{
            $querySql = 'SELECT * FROM ('.$items_sql.') as a ORDER BY '.$orderby.' limit '.$pagination->offset.','.$pagination->limit;
        }
        
        $command = $connection->createCommand($querySql);
        $result = $command->queryAll();
        return ['list'=>$result, 'pagination'=>$pagination];
    }

    /**
     * 获取单个订单详情
     * param orderId  订单号
     * param productId  产品id
     **/
    public static function getOrderDetail($orderId, $productId, $tableNum = 10)
    {
        $items_sql = '';
        $orders_sql = '';

        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $items_sql .= '(SELECT *, id as child, money as actualMoney, point as actualPoint FROM payment_order_items_'.$tableId.') ';
            }else{
                $items_sql .= '(SELECT *, id as child, money as actualMoney, point as actualPoint FROM payment_order_items_'.$tableId.') union all';
            }
        }

        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $orders_sql .= '(SELECT id,user_id,source,buy_time,status,payment,money,point FROM payment_orders_'.$tableId.') ';
            }else{
                $orders_sql .= '(SELECT id,user_id,source,buy_time,status,payment,money,point FROM payment_orders_'.$tableId.' ) union all';
            }
        }

        $connection = \Yii::$app->db;
        $querySql = 'SELECT * FROM ('.$items_sql.') as a left join ('.$orders_sql.') as b on a.payment_order_id=b.id
        where a.payment_order_id = "'.$orderId.'" and a.product_id = "'.$productId.'"';
        $command = $connection->createCommand($querySql);
        $result = $command->queryOne();
        return $result;
    }

    public static function getOrderTotalPoint($period_id)
    {
        $orders_sql = '';
        for( $i=0;$i<10;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $orders_sql .= '(SELECT sum(point) as point,period_id FROM payment_order_items_'.$tableId.' where period_id in ('.$period_id.') group by period_id) ';
            }else{
                $orders_sql .= '(SELECT sum(point) as point,period_id FROM payment_order_items_'.$tableId.' where period_id in ('.$period_id.') group by period_id) union all';
            }
        }
        $connection = Yii::$app->db;
        $command = $connection->createCommand('select sum(point) as totalPoint,period_id from ('.$orders_sql.') as a group by period_id');
        $result = $command->queryAll();
        return $result;
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
                $items_sql .= '(SELECT payment_order_id,item_buy_time,nums,product_id,period_number,period_id,user_id FROM payment_order_items_'.$tableId.' where item_buy_time <= '.$time.' and item_buy_time > 0 and nums > 0 ORDER BY item_buy_time desc limit 0,'.$num.') ';
            }else{
                $items_sql .= '(SELECT payment_order_id,item_buy_time,nums,product_id,period_number,period_id,user_id FROM payment_order_items_'.$tableId.' where item_buy_time <= '.$time.' and item_buy_time > 0 and nums > 0 ORDER BY item_buy_time desc limit 0,'.$num.' ) union all';
            }
        }

        $querySql = 'SELECT * FROM ('.$items_sql.') as a ORDER BY a.item_buy_time desc limit 0,'.$num;

        $connection = Yii::$app->db;
        $command = $connection->createCommand($querySql);
        $result = $command->queryAll();

        return $result;
    }

    /**
     * 获取所有订单
     * tableNum 分表数
     **/
    public static function getAllOrder($where = [], $page = 1, $perpage = 10, $tableNum = 10,$itemField = '*',$orderby = 'b.buy_time desc')
    {
        $items_sql = '';
        $orders_sql = '';
        $extraWhere = '';

        if (isset($where['starttime']) && $where['starttime']) {
            $extraWhere .= " where item_buy_time >= '".$where['starttime']."'";
        }

        if (isset($where['endtime']) && $where['endtime']) {
            $extraWhere .= isset($where['starttime']) && !empty($where['starttime']) ? ' and ' : ' where ';
            $extraWhere .= " item_buy_time <= '".$where['endtime']."' and item_buy_time > 0";
        }


        if(isset($where['orderId']) && $where['orderId']){
            $extraWhere .= $extraWhere ? ' and payment_order_id = "'.$where['orderId'].'"' : ' where payment_order_id = "'.$where['orderId'].'"';
        }

        $extraWhere .= $extraWhere ? ' and nums > 0 ' : ' where nums > 0';
        $condition = '';
        if(isset($where['user_id']) && $where['user_id']){
            $condition .=  ' and user_id =  '.$where['user_id'];
        }

        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $items_sql .= '(SELECT  payment_order_id,product_id, period_number, nums, item_buy_time, money, point FROM payment_order_items_'.$tableId.$extraWhere.') ';
            }else{
                $items_sql .= '(SELECT payment_order_id,product_id, period_number, nums, item_buy_time, money, point  FROM payment_order_items_'.$tableId.$extraWhere.' ) union all';
            }
        }

        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;

            if($i == 9){
                $orders_sql .= '(SELECT id,user_id,source,buy_time FROM payment_orders_'.$tableId.') ';
            }else{
                $orders_sql .= '(SELECT id,user_id,source,buy_time FROM payment_orders_'.$tableId.' ) union all';
            }
        }

        $connection = \Yii::$app->db;
        $countSql = 'SELECT count(1) FROM ('.$items_sql.') as a left join ('.$orders_sql.') as b on a.payment_order_id=b.id '.$extraWhere.$condition;
        $c = $connection->createCommand($countSql);
        $totalCount = $c->queryScalar();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage,'pageSizeLimit'=>[1,$perpage]]);

        $querySql = 'SELECT * FROM ('.$items_sql.') as a left join ('.$orders_sql.') as b on a.payment_order_id=b.id'.$extraWhere.$condition.' ORDER BY '.$orderby.' limit '.$pagination->offset.','.$pagination->limit;

        $command = $connection->createCommand($querySql);
        $result = $command->queryAll();

        return ['list'=>$result, 'pagination'=>$pagination];
    }

    public static function newList($where = [], $page = 1, $perpage = 10)
    {
        $extraWhere = '';
        if (isset($where['starttime']) && $where['starttime']) {
            $extraWhere .= " where item_buy_time >= '".$where['starttime']."'";
        }

        if (isset($where['endtime']) && $where['endtime']) {
            $extraWhere .= isset($where['starttime']) && !empty($where['starttime']) ? ' and ' : ' where ';
            $extraWhere .= " item_buy_time <= '".$where['endtime']."' and item_buy_time > 0";
        }

        if(isset($where['orderId']) && $where['orderId']){
            $extraWhere .= $extraWhere ? ' and payment_order_id = "'.$where['orderId'].'"' : ' where payment_order_id = "'.$where['orderId'].'"';
        }

        $extraWhere .= $extraWhere ? ' and nums > 0 ' : ' where nums > 0';
        if(isset($where['user_id']) && $where['user_id']){
            $extraWhere .=  ' and user_id =  '.$where['user_id'];
        }

        $sql = "";
        for ($i=100; $i < 109; $i++) {
            $sql .= "(SELECT id FROM payment_order_items_".$i.$extraWhere." ) union all ";
        }
        $sql = substr($sql,0,-10);
        $connection = \Yii::$app->db;
        $countSql = 'SELECT count(1) FROM ('.$sql.') as a ';
        $c = $connection->createCommand($countSql);
        $totalCount = $c->queryScalar();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage,'pageSizeLimit'=>[1,$perpage]]);

        $sql = "";
        for ($i=100; $i < 109; $i++) {
            $sql .= '(SELECT id,payment_order_id,product_id,period_number,user_id,nums,item_buy_time,source,money,point FROM payment_order_items_'.$i.$extraWhere.' order by item_buy_time desc limit '.$pagination->offset.','.$pagination->limit.') union all ';
        }
        $sql = substr($sql,0,-10);
        $sql = 'select * from ('.$sql.') as a order by item_buy_time desc limit '.$pagination->offset.','.$pagination->limit;

        $command = \Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();
        return ['list'=>$result, 'pagination'=>$pagination];
    }
}
