<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/19
 * Time: 16:58
 */

namespace app\modules\admin\controllers;

use app\helpers\MyRedis;
use app\models\VirtualProductInfo;
use app\modules\admin\models\Admin;
use app\modules\admin\models\BackstageLog;
use app\modules\admin\models\ExchangeOrder;
use app\services\Category;
use yii;
use app\services\Product;
use app\helpers\DateFormat;
use app\models\ProductCategory;
use yii\helpers\ArrayHelper;
use app\models\RechargeOrderDistribution;
use app\models\PaymentOrderItemDistribution;
use app\models\Order;
use app\models\Period;
use app\modules\admin\models\Deliver;
use app\models\User;
use app\models\ShareTopic;
use app\models\ShareTopicImage;
use app\models\Image;
use app\models\UserVirtual;
use app\models\Product as ModelProduct;
use app\helpers\Message;
use app\helpers\Ex;
use app\helpers\Excel;

class OrderController extends BaseController
{
    public function actionIndex()
    {
        $request = \Yii::$app->request;

        $where = [];
        if($request->isGet){
            $get = $request->get();
            if(isset($get['sub']) && $get['sub'] == 'sub'){
                $where['order'] = $get['order'];
                $deliver = Deliver::find()->select('id');
                if($get['startTime'] || $get['endTime']){
                    if($get['time'] == 2){
                        $get['startTime'] && $deliver->andWhere(['>=', 'prepare_time', strtotime($get['startTime'])]);
                        $get['endTime'] && $deliver->andWhere(['<', 'prepare_time', strtotime($get['endTime'])]);
                    }elseif($get['time'] == 3){
                        $get['startTime'] && $deliver->andWhere(['>=', 'deliver_time', strtotime($get['startTime'])]);
                        $get['endTime'] && $deliver->andWhere(['<', 'deliver_time', strtotime($get['endTime'])]);
                    }elseif($get['time'] == 4){
                        $get['startTime'] && $deliver->andWhere(['>=', 'unix_timestamp(bill_time)', strtotime($get['startTime'])]);
                        $get['endTime'] && $deliver->andWhere(['<', 'unix_timestamp(bill_time)', strtotime($get['endTime'])]);
                    }else{
                        $where['startTime'] = $get['startTime'];
                        $where['endTime'] = $get['endTime'];
                    }
                }
                if($get['prepare']){
                    $admin = Admin::findOne(['real_name'=>$get['prepare']]);
                    $deliver->andWhere(['prepare_userid'=>$admin['id']]);
                }
                if (!empty($deliver->where)) {
                    $ids = $deliver->all();
                    $ids && $where['ids'] = ArrayHelper::getColumn($ids, 'id');
                }

                $where['time'] = $get['time'];
                if($get['name']){
                    $user = User::find()->where(['or', 'email="'.$get['name'].'"',  'phone="'.$get['name'].'"', 'nickname="'.$get['name'].'"'])->one();
                    $where['name'] = $user['id'];
                }

                if($get['deliver']){
                    $product = ModelProduct::find()->select('id')->where(['delivery_id'=>$get['deliver']])->all();
                    foreach($product as $key => $val){
                        $arr[$key] = $val['id'];
                    }
                    $where['deliver'] = isset($arr) ? $arr : '';
                }
            }
        }

        $status = $request->get('status');
        $where['status'] = $status;
        $cats = ProductCategory::find()->all();
        $cats = ArrayHelper::map($cats, 'id', 'name');

        if(isset($get['excel']) && $get['excel'] == 'order'){
            ini_set('memory_limit', '1500M');
            $list = Order::orderList($where, PHP_INT_MAX);
            $data[0] = ['id'=>'订单号', 'name'=>'商品名称', 'catone'=>'分类', 'phone'=>'手机', 'email'=>'邮箱','num_period'=>'第几期','period'=>'商品期数','code'=>'伙购码','status'=>'状态', 'deliver'=>'发货方式', 'time'=>'中奖时间', 'ship_name'=>'收货人', 'ship_area'=>'收货地址', 'confirm_userid'=>'确认人', 'prepare_userid'=>'备货人','price'=>'成本','goodprice'=>'伙购价格','platform'=>'平台', 'third_order'=>'第三方订单号','payment'=>'支付方式','bill'=>'发票', 'bill_time'=>'发票时间','bill_num'=>'发票号','deliver_userid'=>'发货人', 'deliver_company'=>'快递公司', 'deliver_order'=>'快递单号','prepare_time'=>'备货时间','deliver_time'=>'发货时间','total_point'=>'福分总额'];
            $person = Deliver::getEmployeeName();
            $productName = Product::getProductName();
            $productDeliver = Product::getProductDeliver();
            $productCat = Product::getProductCate();
            $catArr = Category::getCateList();
            $periodArr = '';
            foreach($list['list'] as $key => $val){
                $key = $key +1;
                $periodArr .= $val['period_id'].',';
                $data[$key]['id'] = $val['id'];
                $data[$key]['name'] = $productName[$val['product_id']];
                $data[$key]['catone'] = $catArr[$productCat[$val['product_id']]];
                $data[$key]['phone'] = $val['phone'];
                $data[$key]['email'] = $val['email'];
                $data[$key]['num_period'] = $val['period_number'];
                $data[$key]['period'] = $val['period_id'];
                $data[$key]['code'] = $val['lucky_code'];
                $status =  Order::getStatus($val['status']);
                $data[$key]['status'] = $status['name'];
                $deliver = Order::getDeliver($productDeliver[$val['product_id']]);
                $data[$key]['deliver'] = $deliver['name'];
                $data[$key]['time'] = DateFormat::microDate($val['end_time']);
                $data[$key]['ship_name'] = $val['ship_name'];
                $data[$key]['ship_area'] = $val['ship_area'].$val['ship_addr'];
                if($val['confirm_userid']) $data[$key]['confirm_userid'] = $person[$val['confirm_userid']];
                else $data[$key]['confirm_userid'] = '';
                if($val['prepare_userid']) $data[$key]['prepare_userid'] = $person[$val['prepare_userid']];
                else $data[$key]['prepare_userid'] = '';
                $data[$key]['price'] = $val['deliver_price'];
                $data[$key]['ship_name'] = $val['ship_name'];
                $data[$key]['goodprice'] = $val['goodprice'];
                $data[$key]['platform'] = $val['platform'];
                $data[$key]['third_order'] = $val['third_order'];
                $data[$key]['payment'] = $val['payment'];
                $data[$key]['bill'] = $val['bill'];
                $data[$key]['bill_time'] = $val['bill_time'];
                $data[$key]['bill_num'] = $val['bill_num'];
                if($val['deliver_userid']) $data[$key]['deliver_userid'] = $person[$val['deliver_userid']];
                else $data[$key]['deliver_userid'] = '';
                $data[$key]['deliver_company'] = $val['deliver_company'];
                $data[$key]['deliver_order'] = $val['deliver_order'];
                if($val['prepare_time']) $data[$key]['prepare_time'] = DateFormat::microDate($val['prepare_time']);
                else $data[$key]['prepare_time'] = '';
                if($val['deliver_time']) $data[$key]['deliver_time'] = DateFormat::microDate($val['deliver_time']);
                else $data[$key]['deliver_time'] = '';
            }

            $totalPoint = PaymentOrderItemDistribution::getOrderTotalPoint(substr($periodArr, 0, -1));
            $pointArr = [];
            foreach($totalPoint as $val){
                $pointArr[$val['period_id']] = $val['totalPoint'];
            }

            foreach($data as $key => $val){
                if($key != 0) $data[$key]['total_point'] = $pointArr[$val['period']];
            }
            /*$excel = new Excel();
            $excel->writerExcel($data[0], $data, '中奖数据'.date('Y-m-d H:i:s').'.xls');*/
            $excel = new Ex();
            $excel->download( $data, '中奖数据'.date('Y-m-d H:i:s').'.xls');
            unset($data);
        }
        $list = Order::orderList($where, 25);
        $count = Order::orderStatusCount();

        $arr = [];
        foreach($list['list'] as $key=>$val){
            $goodInfo = Product::info($val['product_id']);
            $arr[$key]['id'] = $val['id'];
            $arr[$key]['name'] = $goodInfo['name'];
            $arr[$key]['cat_id'] = $val['cat_id'];
            $arr[$key]['code'] = $val['lucky_code'];
            $arr[$key]['period_id'] = $val['period_id'];
            $arr[$key]['period_number'] = $val['period_number'];
            $arr[$key]['status'] = Order::getStatus($val['status'], $val['fail_type']);
            $arr[$key]['is_exchange'] = $val['is_exchange'];
            $user = User::findOne($val['user_id']);
            $arr[$key]['user_id'] = $user;
            $arr[$key]['delivery'] = Order::getDeliver($goodInfo['delivery_id']);
            $arr[$key]['fail_type'] = $val['fail_type'];
            $arr[$key]['delay'] = DateFormat::microDate($val['delay']);
            if($val['confirm_addr_time']) $arr[$key]['confirm_addr_time'] = DateFormat::microDate($val['confirm_addr_time']);
            $arr[$key]['create_time'] = DateFormat::microDate($val['end_time']);
            $arr[$key]['last_modified'] = DateFormat::microDate($val['last_modified']);
        }

        if(empty($get)){
            $url = Yii::$app->request->getUrl().'?excel=order';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=order';
        }

        if(isset($where['prepare']) && $where['prepare']){
            $admin = Admin::findOne($where['prepare']);
            $where['prepare'] = $admin['real_name'];
        }else{
            $where['prepare'] = '';
        }

        $where['name'] = isset($get['name']) ? $get['name'] : '';
        $where['prepare'] = isset($get['prepare']) ? $get['prepare'] : 0;
        $where['deliver'] = isset($get['deliver']) ? $get['deliver'] : 0;
        $where['startTime'] = isset($get['startTime']) ? $get['startTime'] : '';
        $where['endTime'] = isset($get['endTime']) ? $get['endTime'] : '';
        return $this->render('index',[
            'list' => $arr,
            'pagination' => $list['pagination'],
            'catName' => $cats,
            'count' => $count,
            'status' => $status,
            'condition' => $where,
            'url' => $url
        ]);
    }

    public function actionView()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');

        $detail = Order::findOne($id);
        $status = Order::getStatus($detail['status']);
        $user = User::findOne($detail['user_id']);
        $tableId = substr($user['home_id'], 0, 3);
        $goodInfo = ModelProduct::findOne($detail['product_id']);
        $periodInfo = Period::find()->where(['product_id'=>$detail['product_id'], 'id'=>$detail['period_id']])->one();
        $periodInfo['end_time'] = DateFormat::microDate($periodInfo['end_time']);
        $cats = ProductCategory::find()->all();
        $catName = ArrayHelper::map($cats, 'id', 'name');
        $deliverInfo = Deliver::findOne($id);
        $exchange = ExchangeOrder::find()->where(['order_no'=>$detail['id']])->one();
        if($exchange){
            $exchange['admin_id'] = Admin::findOne($exchange['admin_id']);
            $exchange['deliver_userid'] = Admin::findOne($exchange['deliver_userid']);
        }
        //虚拟平台
        $virtual = [];
        if(in_array($goodInfo['delivery_id'], [5,6,7,9])){
            $virtual = VirtualProductInfo::findOne(['order_id'=>$detail['id']]);
        }

        $newVirtual = [];
        if($goodInfo['delivery_id'] == '3'){
            $newVirtual = UserVirtual::find()->where(['orderid'=>$detail['id']])->asArray()->all();
        }

        $newVirtualStr = $cardType = '';
        foreach ($newVirtual as $key => $value) {
            $newVirtualStr .= $value['card'].PHP_EOL;
            if ($value['type'] == 'yd') {
                $cardType = '移动';
            }else if ($value['type'] == 'lt') {
                $cardType = '联通';
            }else if ($value['type'] == 'dx') {
                $cardType = '电信';
            }
        }
        $newVirtualStr = substr($newVirtualStr, 0,-1);
        //来源
        $conn = Yii::$app->db;
        $sql =  $conn->createCommand('select source,buy_time from period_buylist_'.$periodInfo['table_id'].' where period_id = '.$periodInfo['id'].' and FIND_IN_SET('.$periodInfo['lucky_code'].', codes)');
        $periodTable = $sql->queryOne();
        $periodTable['source'] = Order::getSource($periodTable['source']);
        $periodTable['buy_time'] = DateFormat::microDate($periodTable['buy_time']);

        $person = Deliver::getEmployeeName();
        $detail['user_id'] = User::userName($detail['user_id']);

        //获取晒单信息
        $shareTopic = ShareTopic::find()->where(['period_id'=>$detail['period_id']])->one();
        $shareImg = ShareTopicImage::find()->where(['share_topic_id'=>$shareTopic['id']])->all();
        foreach($shareImg as $key => $val){
            $shareImg[$key]['basename'] = Image::getShareInfoUrl($val['basename'], 'share');
        }

        $csql = "select sum(a.money) as totalMoney, sum(a.point) as totalPoint, sum(a.nums) as total from payment_order_items_".$tableId." as a left join payment_orders_".$tableId." as b on a.payment_order_id = b.id where b.user_id = ".$periodInfo['user_id']." and a.period_id = ".$detail['period_id']."";

        $con = $conn->createCommand($csql);
        $result = $con->queryOne();
        $consume['money'] = $result['totalMoney'];
        $consume['point'] = $result['totalPoint'];
        $consume['total'] = $result['total'];

        if(!$deliverInfo['bill_time']) $deliverInfo['bill_time'] = date('Y-m-d H:i:s', time());

        return $this->render('view', [
            'detail' => $detail,
            'goodInfo' => $goodInfo,
            'periodInfo' => $periodInfo,
            'catName' => $catName,
            'deliverInfo' => $deliverInfo,
            'person' => $person,
            'periodTable' => $periodTable,
            'shareTopic' => $shareTopic,
            'shareImg' => $shareImg,
            'exchange' => $exchange,
            'virtual' => $virtual,
            'consume' => $consume,
            'status' => $status,
            'cardType' => $cardType,
            'newVirtual' => $newVirtualStr
        ]);
    }

    public function actionAllOrder()
    {
        $request = \Yii::$app->request;
        $page = $request->get('page', 1);

        $where = [];
        $condition = [];
        if($request->isGet){
            $get = $request->get();
            if(isset($get['sub']) && $get['sub'] == 'sub'){
                $where['orderId'] = $get['orderId'];
                if($get['content']) $user = User::find()->where(['or', 'email="'.$get['content'].'"', 'phone="'.$get['content'].'"'])->one();
                $where['user_id'] = isset($user) ? $user['id'] : '';
                $where['starttime'] = strtotime($get['start_time']);
                $where['endtime'] = strtotime($get['end_time']);

                $condition['orderId'] = $get['orderId'];
                $condition['content'] = $get['content'];
                $condition['endTime'] = $get['end_time'];
                $condition['startTime'] = $get['start_time'];
            }
        }

        $cats = ProductCategory::find()->all();
        $cat_ids = ArrayHelper::map($cats, 'id', 'name');

        if(isset($get['excel']) && $get['excel'] == 'allorder'){
            $data = [];
            $list = PaymentOrderItemDistribution::newList($where, $page, PHP_INT_MAX);
            $data[0] = ['id'=>'订单号', 'name'=>'商品名称', 'cat'=>'分类','price'=>'价格', 'phone'=>'手机', 'email'=>'邮箱','num'=>'次数','money'=>'金额','point'=>'福分','period'=>'期数','source'=>'来源', 'time'=>'伙购时间'];
            foreach($list['list'] as $key => $val){
                $key = $key +1;
                $admin = User::findOne($val['user_id']);
                $goodInfo = Product::info($val['product_id']);
                $data[$key]['id'] = $val['id'];
                $data[$key]['name'] = $goodInfo['name'];
                $data[$key]['cat'] = isset($cat_ids[$goodInfo['cat_id']]) ? $cat_ids[$goodInfo['cat_id']] : '';
                $data[$key]['price'] = $goodInfo['price'];
                $data[$key]['phone'] = $admin['phone'];
                $data[$key]['email'] = $admin['email'];
                $data[$key]['num'] = $val['nums'];
                $data[$key]['money'] = $val['money'];
                $data[$key]['point'] = $val['point'];
                $data[$key]['period'] = $val['period_number'];
                $source =  Order::getSource($val['source']);
                $data[$key]['source'] = $source['name'];
                $data[$key]['time'] = DateFormat::microDate($val['item_buy_time']);
            }
            $excel = new Ex();
            $excel->download( $data, '伙购订单-'.date('Y-m-d H:i:s').'.xls');
        }

        $list = PaymentOrderItemDistribution::newList($where, $page, 25);

        $returnData = [];
        foreach($list['list'] as $key=>$val){
            $goodInfo = Product::info($val['product_id']);
            $returnData[$key]['id'] = $val['payment_order_id'];
            $returnData[$key]['name'] = $goodInfo['name'];
            $returnData[$key]['cat'] = $goodInfo['cat_id'];
            $returnData[$key]['price'] = $goodInfo['price'];
            $returnData[$key]['user_id'] = User::userName($val['user_id']);
            $returnData[$key]['product_id'] = $val['product_id'];
            $returnData[$key]['nums'] = $val['nums'];
            //$returnData[$key]['period_id'] = $val['period_id'];
            $returnData[$key]['period_number'] = $val['period_number'];
            $returnData[$key]['money'] = $val['money'];
            $returnData[$key]['point'] = $val['point'];
            $returnData[$key]['source'] = Order::getSource($val['source']);
            //$returnData[$key]['status'] = $val['status'];
            $returnData[$key]['created_at'] = DateFormat::microDate($val['item_buy_time']);
        }

        if(empty($get)){
            $url = Yii::$app->request->getUrl().'?excel=allorder';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=allorder';
        }

        return $this->render('order',[
            'list' => $returnData,
            'pagination' => $list['pagination'],
            'cat_ids' => $cat_ids,
            'condition' => $condition,
            'url' => $url
        ]);
    }

    //订单详情页
    public function actionOrderDetail()
    {
        $orderId = \Yii::$app->request->get('id');
        $productId = \Yii::$app->request->get('productId');
        $detail = PaymentOrderItemDistribution::getOrderDetail($orderId, $productId);
        $user = User::findOne($detail['user_id']);
        $detail['created_at'] = DateFormat::microDate($detail['item_buy_time']);
        $detail['user_id'] = User::userName($detail['user_id']);
        $detail['source'] = Order::getSource($detail['source']);
        $goodInfo = Product::info($detail['product_id']);

        $cats = ProductCategory::find()->all();
        $cat_name = ArrayHelper::map($cats, 'id', 'name');

        return $this->render('detail', [
            'cat_name' => $cat_name,
            'detail' => $detail,
            'goodInfo' => $goodInfo,
        ]);
    }

    //充值订单列表
    public function actionRecharge()
    {
        $request = \Yii::$app->request;
        $get = $request->get();
        $status = Yii::$app->request->get('status', '-1');
        $start_time = Yii::$app->request->get('start_time', '');
        $end_time = Yii::$app->request->get('end_time', '');
        $name = Yii::$app->request->get('account', '');
        $account = '';
        if($name){
            $user = User::find()->where('phone="' . $name . '" or email="' . $name . '"')->one();
            $account = $user['id'];
        }
        $payment = Yii::$app->request->get('payment', '-1');
        $source = Yii::$app->request->get('source', '0');
        $id = Yii::$app->request->get('id', '');
        $excel = Yii::$app->request->get('excel', '');
        $condition = ['status' => $status, 'start_time' => $start_time, 'end_time' => $end_time, 'account' => $name, 'payment' => $payment, 'source'=>$source, 'id'=>$id];

        if ($start_time != '') {
            $start_time = strtotime($start_time);
        }
        if ($end_time != '') {
            $end_time = strtotime($end_time);
        }

        $where = [
            'status' => $status,
            'startTime' => $start_time,
            'endTime' => $end_time,
            'payment' => $payment,
            'account' => $account,
            'source' => $source,
            'id' => $id
        ];

        $page = $request->get('page', 1);

        if(isset($excel) && $excel == 'recharge'){
            $data = [];
            ini_set('memory_limit', '1500M');
            $list = RechargeOrderDistribution::rechargeOrderList($where, $page, PHP_INT_MAX);
            $data[0] = ['id'=>'订单号', 'result'=>'流水号', 'phone'=>'手机', 'email'=>'邮箱','money'=>'金额','payment'=>'支付方式','source'=>'来源','status'=>'', 'time'=>'充值时间'];
            foreach($list['list'] as $key => $val){
                $key = $key +1;
                //$user = User::findOne($val['user_id']);
                $data[$key]['id'] = $val['id'];
                if($val['result'] && $val['bank'] == 'chat'){
                    $result = json_decode($val['result']);
                    $data[$key]['result'] = isset($result->transaction_id) ? $result->transaction_id : '' ;
                }else{
                    $data[$key]['result'] = '';
                }
                $data[$key]['phone'] = $val['phone'];
                $data[$key]['email'] = $val['email'];
                $data[$key]['money'] = $val['post_money'];
                $type = RechargeOrderDistribution::getType($val['payment']);
                if($type['name'] == '充值平台'){
                    $name = RechargeOrderDistribution::getPaymentBank($val['bank']);
                }else{
                    $name = '';
                }
                $data[$key]['payment'] = isset($type['name']) ? $type['name'] : ''.$name;
                $source =  Order::getSource($val['source']);
                $data[$key]['source'] = $source['name'];
                if($val['status'] == 1){
                    $status = '已支付';
                }else{
                    $status = '未支付';
                }
                $data[$key]['status'] = $status;
                $data[$key]['time'] = DateFormat::microDate($val['pay_time']);
            }

            $excel = new Ex();
            $excel->download($data, '充值订单-'.date('Y-m-d H:i:s').'.xls');
        }

        $list = RechargeOrderDistribution::rechargeOrderList($where, $page, 25);

        foreach($list['list'] as $key => $val){
            $list['list'][$key]['user_id'] = User::findOne($val['user_id']);
            $list['list'][$key]['pay_time'] = DateFormat::microDate($val['pay_time']);
            $list['list'][$key]['source'] = Order::getSource($val['source']);
            $list['list'][$key]['payment'] = RechargeOrderDistribution::getType($val['payment']);
            if($val['result'] && $val['bank'] == 'chat'){
                $result = json_decode($val['result']);
                $list['list'][$key]['result'] = isset($result->transaction_id) ? $result->transaction_id : '';
            }elseif($val['result'] && $val['bank'] == 'iapp'){
                $result = json_decode($val['result']);
                $list['list'][$key]['result'] = isset($result->transid) ? $result->transid : '';
            }else{
                $list['list'][$key]['result'] = '';
            }
        }

        if(empty($get)){
            $url = Yii::$app->request->getUrl().'?excel=recharge';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=recharge';
        }

        return $this->render('recharge', [
            'list' => $list,
            'condition' => $condition,
            'url' => $url
        ]);
    }

    //充值订单详情
    public function actionRechargeDetail()
    {
        $orderId = \Yii::$app->request->get('id');
        $detail = RechargeOrderDistribution::rechargeOrderDetail($orderId);
        $detail['user_id'] = User::userName($detail['user_id']);
        $detail['source'] = Order::getSource($detail['source']);
        $detail['pay_time'] = DateFormat::microDate($detail['pay_time']);

        return $this->render('recharge-detail', [
            'detail' => $detail,
        ]);
    }

    //其他确认情况
    public function actionConfirm()
    {
        $request = \Yii::$app->request;
        if($request->isPost){
            $id = $request->post('id');
            $post = $request->post();
            $model = Order::findOne($id);
            $user = User::userName($model['user_id']);
            if($model){
                if(isset($post['confirm_reason']) && $post['confirm_reason'] != ''){
                    $ret = Order::orderFail($id, $post);
                    BackstageLog::addLog(\Yii::$app->admin->id, 1, '确认订单'.$model['id'].'失败');
                    if($ret == 1){
                        return 1;
                    }else{
                        return 0;
                    }
                }elseif(isset($post['deliver_reason']) && $post['deliver_reason'] != ''){
                    $ret = Order::orderFail($id, $post);
                    BackstageLog::addLog(\Yii::$app->admin->id, 1, '发货失败-订单'.$model['id']);
                    if($ret == 1){
                        return 1;
                    }else{
                        return 0;
                    }
                }elseif(isset($post['send_reason']) && $post['send_reason'] != ''){
                    $ret = Order::orderFail($id, $post);
                    BackstageLog::addLog(\Yii::$app->admin->id, 1, '送货失败-订单'.$model['id']);
                    if($ret == 1){
                        return 1;
                    }else{
                        return 0;
                    }
                }elseif(isset($post['exchange_reason']) && $post['exchange_reason'] != ''){
                    $ret = Order::orderFail($id, $post);
                    BackstageLog::addLog(\Yii::$app->admin->id, 1, '同意换货-订单'.$model['id']);
                    if($ret == 1){
                        Message::send(19, $model['user_id'], ['nickname'=>$user['username']]);
                        return 1;
                    }else{
                        return $ret;
                    }
                }elseif(isset($post['success_id']) && $post['success_id'] != ''){
                    $model->status = 5;
                    $model->last_modified = time();
                    $model->save();

                    $deliverModel = Deliver::findOne($id);
                    $deliverModel->status = 5;
                    $deliverModel->save();

                    BackstageLog::addLog(\Yii::$app->admin->id, 1, '收货成功-订单'.$model['id']);
                    return 0;
                }
            }
        }
    }

    //确认收货地址
    public function actionConfirmOrder()
    {
        $request = \Yii::$app->request;
        if($request->isPost){
            $id = $request->post('id');
            $model = Order::findOne($id);
            $product = Product::Info($model['product_id']);
            if($model){
                $model->status = 2;
                $model->confirm = 1;
                $model->last_modified = time();
                $trans = Yii::$app->db->beginTransaction();
                $user = User::userName($model['user_id']);
                if($model->save()){
                    $deliver = Deliver::findOne($model['id']);
                    if($deliver && $deliver['status'] == 1){
                        $deliver->status = 2;
                        $deliver->confirm_userid = \Yii::$app->admin->id;
                        $deliver->confirm_time = time();
                        if(!$deliver->save()){
                            $trans->rollBack();
                            return 1;
                        }
                        BackstageLog::addLog(\Yii::$app->admin->id, 1, '重新确认订单'.$model['id']);
                    }else{
                        //$deliverModel = new Deliver();  keli 2016-02-01 fix this 修复 id重复造成的sql错误问题
                        $deliverModel = ($deliver) ? $deliver : new Deliver();
                        $deliverModel->id = $id;
                        $deliverModel->confirm_userid = \Yii::$app->admin->id;
                        $deliverModel->confirm_time = time();
                        $deliverModel->status = 2;
                        if(!$deliverModel->save()){
                            $trans->rollBack();
                            return 1;
                        }
                        BackstageLog::addLog(\Yii::$app->admin->id, 1, '确认订单'.$model['id']);
                    }
                    $trans->commit();
                    Message::send(15, $model['user_id'], ['nickname'=>$user['username'], 'goodsName'=>$product['name'], 'orderNo'=>$model['id'],'time'=>date('Y-m-d H:i:s')]);
                    return 0;
                }else{
                    $trans->rollBack();
                    return 1;
                }
            }
        }
    }

    public function actionChange()
    {
        $redis = new MyRedis();
        $all = $redis->keys('POINT_USE_*');

        foreach($all as $val){
            $one = $redis->hget($val, 'all');
            $tableId = substr($val, 10, 3);
            $orderId = substr($val, 10);
            if(!empty($all)){
                $conn = Yii::$app->db;
                foreach($one as $key => $value){
                    $arr = json_decode($value);
                    $sql = $conn->createCommand('update payment_order_items_'.$tableId.' set money='.$arr->money.',point='.$arr->point.' where payment_order_id = "'.$orderId.'"  and period_id = '.$key.'');
                    $sql->query();
                }
            }
            $redis->del($val);
        }
        return 1;
    }

    public function actionRemark()
    {
        $get = Yii::$app->request->post();

        $model = Order::findOne($get['id']);
        if($model){
            $admin = Admin::findOne(Yii::$app->admin->id);
            $model->remark = $get['remark'].' 时间：'.date('Y-m-d H:i:s').', 操作人：'.$admin['real_name']."  ";
            if($model->save()){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 2;
        }
    }

    public function actionBill()
    {
        $get = Yii::$app->request->post();

        $model = Deliver::findOne($get['id']);
        if($model){
            $model->bill_time = $get['billtime'];
            $model->bill_num = $get['num'];
            $model->bill = $get['bill'];
            if($model->save()){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 2;
        }
    }
}