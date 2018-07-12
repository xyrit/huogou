<?php

namespace app\models;

use app\modules\admin\models\ExchangeOrder;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use app\modules\admin\models\Deliver;

/**
 * This is the model class for table "orders".
 *
 * @property string $id
 * @property integer $product_id
 * @property string $period_id
 * @property integer $ship_status
 * @property string $payment
 * @property integer $shipping_id
 * @property string $shipping
 * @property integer $user_id
 * @property integer $status
 * @property integer $confirm
 * @property string $ship_area
 * @property string $ship_name
 * @property integer $weight
 * @property string $tostr
 * @property string $ship_addr
 * @property string $ship_zip
 * @property string $ship_tel
 * @property string $ship_email
 * @property string $ship_time
 * @property string $ship_mobile
 * @property integer $price
 * @property string $memo
 * @property string $mark_text
 * @property integer $cost_freight
 * @property integer $create_time
 * @property integer $last_modified
 * @property integer $confirm_addr_time
 * @property integer $fail_type
 * @property string $fail_id
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_INIT = 0;
    const STATUS_COMMIT_ADDRESS = 1;
    const STATUS_COMFIRM_ADDRESS = 2;
    const STATUS_PREPARE_GOODS = 3;
    const STATUS_SHIPPING = 4;
    const STATUS_COMFIRM_RECEIVE = 5;
    const STATUS_REJECT = 6;

    const LIMIT_JUMP = 'ORDER_LIMIT_JUMP_'; //order_id

    public static $status_name = [
        0 => '已中奖',
        1 => '待确认',
        2 => '待备货',
        3 => '待发货',
        4 => '待收货',
        5 => '待晒单',
        6 => '换货',
        7 => '发货异常',
        8 => '已完成'
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'price', 'create_time', 'last_modified'], 'required'],
            [['product_id', 'period_id', 'user_id', 'status', 'confirm', 'price', 'is_exchange', 'create_time', 'last_modified','order_no'], 'integer'],
            [['mark_text'], 'string'],
            [[ 'ship_name', 'ship_time', 'ship_mobile'], 'string', 'max' => 25],
            [['ship_area', 'ship_addr'], 'string', 'max' => 255],
            [['ship_zip'], 'string', 'max' => 20],
            [['period_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'period_id' => 'Period ID',
            'ship_status' => 'Ship Status',
            'payment' => 'Payment',
            'shipping_id' => 'Shipping ID',
            'shipping' => 'Shipping',
            'user_id' => 'User ID',
            'status' => 'Status',
            'confirm' => 'Confirm',
            'ship_area' => 'Ship Area',
            'ship_name' => 'Ship Name',
            'weight' => 'Weight',
            'tostr' => 'Tostr',
            'ship_addr' => 'Ship Addr',
            'ship_zip' => 'Ship Zip',
            'ship_tel' => 'Ship Tel',
            'ship_email' => 'Ship Email',
            'ship_time' => 'Ship Time',
            'ship_mobile' => 'Ship Mobile',
            'price' => 'Price',
            'memo' => 'Memo',
            'mark_text' => 'Mark Text',
            'cost_freight' => 'Cost Freight',
            'create_time' => 'Create Time',
            'last_modified' => 'Last Modified',
        ];
    }

    //后台中奖订单列表
    public static function orderList($condition, $perpage = 10)
    {
        $query = new Query();
        $query->from('orders')
            ->select('
            orders.id,
            orders.product_id,
            orders.period_id,
            orders.payment,
            orders.user_id,
            orders.status,
            orders.ship_area,
            orders.ship_name,
            orders.ship_addr,
            orders.fail_type,
            orders.is_exchange,
            orders.delay,
            orders.confirm_addr_time,
            orders.last_modified,
            c.cat_id,
            c.lucky_code,
            c.end_time,
            c.price as goodprice,
            c.period_number,
            c.table_id,
            d.confirm_userid,
            d.prepare_userid,
            d.third_order,
            d.bill,
            d.bill_time,
            d.bill_num,
            d.deliver_userid,
            d.deliver_company,
            d.deliver_order,
            d.prepare_time,
            d.deliver_time,
            d.platform,
            d.payment,
            d.price as deliver_price,
            e.email,
            e.phone')
            ->leftJoin('periods c', 'orders.period_id = c.id')
            ->leftJoin('deliver d', 'orders.id = d.id')
            ->leftJoin('users e', 'orders.user_id = e.id');

        if($condition['status'] != 'all' && $condition['status'] != null){
            if($condition['status'] == 6){
                $query->andWhere('(orders.fail_type != 0 and orders.status = before_status) or orders.status = 6');
            }elseif($condition['status'] == 0){
                $query->andWhere(['orders.status'=>$condition['status'], 'fail_type'=>null]);
            }else{
                $query->andWhere(['orders.status'=>$condition['status']])->andWhere(['or', 'fail_type is null', 'fail_type=0']);
            }
            $_GET['status'] = $condition['status'];
        }

        if(isset($condition['order']) && $condition['order']){
            $query->andWhere(['orders.id'=>$condition['order']]);
        }

        if(isset($condition['name']) && $condition['name']){
            $query->andWhere(['orders.user_id'=>$condition['name']]);
        }

        if(isset($condition['prepare']) && $condition['prepare']){
            $query->andWhere(['in', 'orders.id', $condition['prepare']]);
        }

        if(isset($condition['deliver']) && $condition['deliver']){
            $query->andWhere(['in', 'orders.product_id', $condition['deliver']]);
        }
        if(isset($condition['startTime']) && $condition['startTime']){
            $query->andWhere(['>=', 'orders.create_time', strtotime($condition['startTime'])]);
        }
        if(isset($condition['endTime']) && $condition['endTime']){
            $query->andWhere(['<', 'orders.create_time', strtotime($condition['endTime'])]);
        }
        if(isset($condition['ids']) && $condition['ids']) {
            $query->andWhere(['in', 'orders.id', $condition['ids']]);
        }

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>$perpage, 'pageSizeLimit'=>[0, $perpage] ]);

        if($condition['status'] >= 1 && $condition['status'] < 4){
            $order = 'orders.confirm_addr_time asc';
        } elseif($condition['status'] >= 4){
            $order = 'orders.last_modified desc';
        } elseif($condition['status'] == 'all' || $condition['status'] == 0){
            $order = 'c.end_time desc';
        }
        $list = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy($order)
            ->all();
        //echo $query->createCommand()->getRawSql();die;
        return ['list'=>$list, 'pagination'=>$pagination];
    }

    //中奖订单状态统计
    public static function orderStatusCount()
    {
        $count['all'] = Order::find()->count();
        $count['one'] = Order::find()->where(['status'=>0, 'fail_type'=>null])->count();
        $count['two'] = Order::find()->where(['status'=>1])->andWhere(['or', 'fail_type is null', 'fail_type=0'])->count();
        $count['three'] = Order::find()->where(['status'=>2])->andWhere(['or', 'fail_type is null', 'fail_type=0'])->count();
        $count['four'] = Order::find()->where(['status'=>3])->andWhere(['or', 'fail_type is null', 'fail_type=0'])->count();
        $count['five'] = Order::find()->where(['status'=>4])->andWhere(['or', 'fail_type is null', 'fail_type=0'])->count();
        $count['six'] = Order::find()->where(['status'=>5])->andWhere(['or', 'fail_type is null', 'fail_type=0'])->count();
        $count['seven'] = Order::find()->where('(fail_type != 0 and status = before_status) or status = 6')->count();
        $count['eight'] = Order::find()->where(['status'=>7])->andWhere(['or', 'fail_type is null', 'fail_type=0'])->count();
        $count['nine'] = Order::find()->where(['status'=>8])->andWhere(['or', 'fail_type is null', 'fail_type=0'])->count();
        $count['deliver'] = Order::find()->where(['and' ,['or', 'status=2', 'status=3'], 'fail_type'=>0])->count();

        return $count;
    }

    public static function thirdStatusCount($deliverId = 1)
    {
        $count['three'] = Order::find()
            ->leftJoin('products as a', 'orders.product_id = a.id')->where(['orders.status'=>2, 'a.delivery_id'=>$deliverId])->count();
        $count['four'] = Order::find()
            ->leftJoin('products as a', 'orders.product_id = a.id')->where(['orders.status'=>3, 'a.delivery_id'=>$deliverId])->count();
        $count['deliver'] = Order::find()
            ->leftJoin('products as a', 'orders.product_id = a.id')->where(['or', ['orders.status'=>2, 'a.delivery_id'=>$deliverId], ['orders.status'=>3, 'a.delivery_id'=>$deliverId]])->count();
        return $count;
    }

    //驳回原因
    public static function orderFail($id, $post)
    {
        $model = Order::findOne($id);
        $deliverModel = Deliver::findOne($id);
        $trans = Yii::$app->db->beginTransaction();
        if(isset($post['confirm_reason']) && $post['confirm_reason'] != ''){
            $model->status = 6;
            $model->fail_type = 1;
            $model->fail_id = isset($post['other']) ? $post['other'] : $post['confirm_reason'];
        }elseif(isset($post['deliver_reason']) && $post['deliver_reason'] != ''){
            $model->status = 6;
            $model->fail_type = 2;
            $model->fail_id = isset($post['other']) ? $post['other'] : $post['deliver_reason'];

            $deliverModel->status = 6;
            if(!$deliverModel->save()){
                $trans->rollBack();
                return 0;
            }
        }elseif(isset($post['send_reason']) && $post['send_reason'] != ''){
            $model->status = 6;
            $model->fail_type = 3;
            $model->fail_id = isset($post['other']) ? $post['other'] : $post['send_reason'];

            $deliverModel->status = 6;
            if(!$deliverModel->save()){
                $trans->rollBack();
                return 0;
            }
        }elseif(isset($post['exchange_reason']) && $post['exchange_reason'] != ''){
            $model->status = 2;
            $model->fail_type = 4;
            $model->is_exchange = 1;
            $model->fail_id = isset($post['other']) ? $post['other'] : $post['exchange_reason'];

            $deliverModel->status = 2;
            $deliverModel->is_exchange = 1;
            if(!$deliverModel->save()){
                $trans->rollBack();
                return 2;
            }

            $exchange = new ExchangeOrder();
            $exchange->order_no = $model['id'];
            $exchange->admin_id = Yii::$app->admin->id;
            $exchange->created_time = time();
            $exchange->user_id = $model['user_id'];

            if(!$exchange->save()){
                $trans->rollBack();
                return 3;
            }
        }

        $model->last_modified = time();
        if($model->save()){
            $trans->commit();
            return 1;
        }else{
            $trans->rollBack();
            return 4;
        }
    }

    public static function getSource($source){
        switch ($source) {
            case '1':
                return array('ico'=>'pc','name'=>'PC电脑');
                break;
            case '2':
                return array('ico'=>'weixin','name'=>'微信公众平台');
                break;
            case '3':
                return array('ico'=>'iphone','name'=>'iOS客户端');
                break;
            case '4':
                return array('ico'=>'android','name'=>'Android客户端');
                break;
            case '5':
                return array('ico'=>'','name'=>'触屏版');
                break;
            default:
                return array('ico'=>'pc','name'=>'PC电脑');
                break;
        }
    }

    public static function getDeliver($deliver){
        switch ($deliver) {
            case '1':
                return array('name'=>'第三方平台');
                break;
            case '2':
                return array('name'=>'虚拟物品(手动发货)');
                break;
            case '3':
                return array('name'=>'虚拟物品(自动发货)');
                break;
            case '4':
                return array('name'=>'自建仓发货');
                break;
            case '5':
                return array('name'=>'供应商发货');
                break;
            default:
                return array('name'=>'第三方平台');
                break;
        }
    }

    public static function getStatus($status, $fail_type = 0){
        switch ($status) {
            case '0':
                return array('name'=>'已中奖');
                break;
            case '1':
                return array('name'=>'待确认');
                break;
            case '2':
                return array('name'=>'待备货');
                break;
            case '3':
                return array('name'=>'待发货');
                break;
            case '4':
                return array('name'=>'待收货');
                break;
            case '5':
                return array('name'=>'待晒单');
                break;
            case '6':
                return array('name'=>'异常订单');
                break;
            case '7':
                return array('name'=>'售后');
                break;
            case '8':
                return array('name'=>'已完成');
                break;
            default:
                return array('name'=>'已中奖');
                break;
        }
    }
}
