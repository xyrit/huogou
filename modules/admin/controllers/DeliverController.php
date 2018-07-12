<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/18
 * Time: 14:54
 */

namespace app\modules\admin\controllers;

use app\helpers\Ex;
use app\modules\admin\models\BackstageLog;
use app\modules\admin\models\ExchangeOrder;
use app\services\User;
use Yii;
use app\modules\admin\models\Deliver;
use app\services\Product;
use app\helpers\DateFormat;
use app\models\ProductCategory;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use app\models\Order;
use app\models\Period;
use app\models\VirtualProductInfo;
use app\models\User as ModelUser;
use app\helpers\Message;
use app\models\Product as ModelProduct;
use app\helpers\Express;
use app\modules\admin\models\Admin;
use app\helpers\Excel;

class DeliverController extends BaseController
{
    public function actionIndex()
    {
        $status = Yii::$app->request->get('status');
        $request = Yii::$app->request;

        $where = [];
        if($request->isGet){
            $get = $request->get();
            if(isset($get['sub']) && $get['sub'] == 'sub'){
                $where['endTime'] = $get['endTime'];
                $where['startTime'] = $get['startTime'];
                $where['name'] = $get['name'];
                $where['content'] = $get['content'];
                if(isset($get['prepare']) && $get['prepare']){
                    $admin = Admin::findOne(['real_name'=>$get['prepare']]);
                }
                $where['prepare'] = isset($admin) ? $admin['id'] : '';
            }
        }

        $list = Deliver::thirdPlatformDeliverList($status, 25, 1, $where);
        $count = Order::thirdStatusCount($deliverId = 1);

        if(isset($get['excel']) && $get['excel'] == 'deliver'){
            $data = [];
            $list = Deliver::thirdPlatformDeliverList(3, PHP_INT_MAX, 1, $where);
            $data[0] = ['adminuser'=>'经手人', 'platformId'=>'采购平台订单号', 'name'=>'商品名称', 'number'=>'开奖期数', 'id'=>'订单号','product_price'=>'伙购价格','price'=>'备货成本'];
            foreach($list['list'] as $key => $val){
                $key = $key +1;
                $admin = Admin::findOne($val['prepare_userid']);
                $period = Period::findOne($val['period_id']);
                $data[$key]['adminuser'] = $admin['real_name'];
                $data[$key]['platformId'] = $val['third_order'];
                $data[$key]['name'] = $val['name'];
                $data[$key]['number'] = $period['period_number'];
                $data[$key]['id'] = $val['id'];
                $data[$key]['product_price'] = $val['product_price'];
                $data[$key]['price'] = $val['price'];
            }
            $excel = new Ex();
            $excel->download($data, '采购数据-'.date('Y-m-d H:i:s').'.xls');
        }

        $arr = [];
        foreach($list['list'] as $key=>$val){
            $goodInfo = Product::info($val['product_id']);
            $period = Period::findOne($val['period_id']);
            $arr[$key]['id'] = $val['id'];
            $arr[$key]['order_no'] = $val['order_no'];
            $arr[$key]['name'] = $goodInfo['name'];
            $arr[$key]['cat_id'] = $goodInfo['cat_id'];
            $arr[$key]['period_number'] = $period['period_number'];
            $arr[$key]['status'] = $val['status'];
            $arr[$key]['user_id'] = ModelUser::findOne($val['user_id']);
            $arr[$key]['ship_name'] = $val['ship_name'];
            $arr[$key]['ship_mobile'] = $val['ship_mobile'];
            $arr[$key]['exchange'] = $val['is_exchange'];
            $arr[$key]['select_prepare'] = $val['select_prepare'];
            $arr[$key]['create_time'] = DateFormat::microDate($val['confirm_addr_time']);
        }

        $cats = ProductCategory::find()->all();
        $cats = ArrayHelper::map($cats, 'id', 'name');

        if(empty($get)){
            $url = Yii::$app->request->getUrl().'?excel=deliver';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=deliver';
        }

        $where['prepare'] = isset($get['prepare']) ? $get['prepare'] : '';
        $person = Deliver::getEmployeeName();
        return $this->render('index',[
            'list' => $arr,
            'pagination' => $list['pagination'],
            'catName' => $cats,
            'count' => $count,
            'status' => $status,
            'condition' => $where,
            'url' => $url,
            'person' => $person
        ]);
    }

    public function actionView()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');

        $detail = Deliver::orderDetail($id);
        $user = ModelUser::findOne($detail['user_id']);
        $tableId = substr($user['home_id'], 0, 3);
        $detail['user_id'] = ModelUser::userName($detail['user_id']);
        $goodInfo = Product::info($detail['product_id']);
        $periodInfo = Period::find()->where(['id'=>$detail['period_id']])->one();
        $cats = ProductCategory::find()->all();
        $cats = ArrayHelper::map($cats, 'id', 'name');
        $person = Deliver::getEmployeeName();
        $exchange = ExchangeOrder::findOne(['order_no'=>$detail['id']]);
        $express = Express::getExpressName();

        //来源
        $conn = Yii::$app->db;
        //$sql =  $conn->createCommand('select * from period_buylist_'.$periodInfo['table_id'].' where period_id = '.$periodInfo['id'].' and user_id = '.$periodInfo['user_id'].'');
        $sql =  $conn->createCommand('select * from period_buylist_'.$periodInfo['table_id'].' where period_id = '.$periodInfo['id'].' and FIND_IN_SET('.$periodInfo['lucky_code'].', codes)');
        $periodTable = $sql->queryOne();
        $periodTable['source'] = Order::getSource($periodTable['source']);
        $periodTable['buy_time'] = DateFormat::microDate($periodTable['buy_time']);

        //$csql = "select sum(a.money) as totalMoney, sum(a.point) as totalPoint, sum(a.nums) as total from payment_order_items_".$periodInfo['table_id']." as a left join payment_orders_".$periodInfo['table_id']." as b on a.payment_order_id = b.id where b.user_id = ".$periodInfo['user_id']." and a.period_id = ".$detail['period_id']."";
        $csql = "select sum(a.money) as totalMoney, sum(a.point) as totalPoint, sum(a.nums) as total from payment_order_items_".$tableId." as a left join payment_orders_".$tableId." as b on a.payment_order_id = b.id where b.user_id = ".$periodInfo['user_id']." and a.period_id = ".$detail['period_id']."";

        $con = $conn->createCommand($csql);
        $result = $con->queryOne();
        $consume['money'] = $result['totalMoney'];
        $consume['point'] = $result['totalPoint'];
        $consume['total'] = $result['total'];

        return $this->render('view', [
            'detail' => $detail,
            'goodInfo' => $goodInfo,
            'catName' => $cats,
            'periodInfo' => $periodInfo,
            'person' => $person,
            'periodTable' => $periodTable,
            'exchange' => $exchange,
            'express' => $express,
            'consume' => $consume
        ]);
    }

    //备货
    public function actionPrepare()
    {
        $request = Yii::$app->request;
        if($request->isPost){
            $post = $request->post();
            $model = Deliver::findOne($post['id']);
            if(Yii::$app->admin->id != $model['select_prepare']) return 3;
            $orderModel = Order::findOne($post['id']);
            if($model){
                $username = User::baseInfo($orderModel['user_id']);
                $product = ModelProduct::findOne($orderModel['product_id']);
                $trans = Yii::$app->db->beginTransaction();
                try {
                    if(isset($post['prepareId']) && $post['prepareId']){
                        $model->platform = isset($post['other']) ? $post['other'] : $post['platform'];
                        $model->bill = isset($post['bill']) ? $post['bill'] : '无';
                        $model->status = 3;
                        $model->payment = isset($post['paymentother']) ? $post['paymentother'] : $post['payment'];
                        $model->third_order = $post['order'];
                        $model->price = $post['price'];
                        $model->standard = $post['standard'];
                        $model->mark_text = $post['comment'];
                        $model->prepare_time = time();
                        $model->prepare_userid = Yii::$app->admin->id;
                        if (!$model->save()) {
                            $trans->rollBack();
                            return 0;
                        }

                        $orderModel->status = 3;
                        $orderModel->last_modified = time();
                        if(!$orderModel->save()){
                            $trans->rollBack();
                            return 0;
                        }

                        $trans->commit();
                        BackstageLog::addLog(Yii::$app->admin->id, 9, '备货-订单'.$model['id']);
                        Message::send(16, $orderModel['user_id'], ['nickname'=>$username['username'], 'time'=>date('Y-m-d H:i:s'),'goodsName'=>$product['name'],'orderNo'=>$model['id']]);
                        return 1;
                    }elseif(isset($post['deliverId']) && $post['deliverId']){
                        if($product['delivery_id'] == 2){
                            $model->status = 4;
                            $model->deliver_userid = Yii::$app->admin->id;
                            $model->deliver_time = time();
                        }else{
                            $company = isset($post['other']) ? $post['other'] : $post['company'];
                            if($orderModel['is_exchange']){
                                $model->status = 4;
                                $exchange = ExchangeOrder::findOne(['order_no'=>$model['id']]);
                                $exchange->deliver_company = $company;
                                $exchange->deliver_order = $post['deliver_order'];
                                $exchange->deliver_time = time();
                                $exchange->deliver_userid = Yii::$app->admin->id;
                                if (!$exchange->save()) {
                                    $trans->rollBack();
                                    return 0;
                                }
                            }else{
                                $model->status = 4;
                                $model->deliver_company = $company;
                                $model->deliver_order = $post['deliver_order'];
                                $model->deliver_time = time();
                                $model->deliver_userid = Yii::$app->admin->id;
                            }
                        }
                        if (!$model->save()) {
                            $trans->rollBack();
                            return 0;
                        }

                        $orderModel->status = 4;
                        $orderModel->last_modified = time();

                        if(!$orderModel->save()){
                            $trans->rollBack();
                            return 0;
                        }

                        $trans->commit();
                        BackstageLog::addLog(Yii::$app->admin->id, 9, '发货-订单'.$model['id']);
                        if($product['delivery_id'] != 2){
                            Message::send(17, $orderModel['user_id'], ['nickname'=>$username['username'], 'time'=>date('Y-m-d H:i:s'),'goodsName'=>$product['name'], 'expressCompany'=>$company, 'expressNo'=>$post['deliver_order'], 'orderNo'=>$model['id']]);
                        }
                        return 1;
                    }elseif(isset($post['unsuccessId']) && $post['unsuccessId']){
                        $model->status = 6;
                        if (!$model->save()) {
                            $trans->rollBack();
                            return 0;
                        }

                        $orderModel->status = 6;
                        $orderModel->last_modified = time();
                        if (!$orderModel->save()) {
                            $trans->rollBack();
                            return 0;
                        }

                        $trans->commit();
                        return 1;
                    } else {
                        $trans->rollBack();
                        return 0;
                    }
                } catch (Exception $e) {
                    $trans->rollBack();
                    return 0;
                }
            }
        }
    }

    //虚拟物品发货
    public function actionVirtual()
    {
        $status = Yii::$app->request->get('status');
        $request = Yii::$app->request;

        $where = [];
        if($request->isGet){
            $get = $request->get();
            if(isset($get['sub']) && $get['sub'] == 'sub'){
                $where['endTime'] = $get['endTime'];
                $where['startTime'] = $get['startTime'];
                $where['name'] = $get['name'];
                $where['content'] = $get['content'];
            }
        }

        if(isset($get['excel']) && $get['excel'] == 'virtual'){
            $data = [];
            $list = Deliver::thirdPlatformDeliverList(3, PHP_INT_MAX, 1, $where);
            $data[0] = ['adminuser'=>'经手人', 'platformId'=>'采购平台订单号', 'name'=>'商品名称', 'number'=>'开奖期数', 'id'=>'订单号','product_price'=>'伙购价格','price'=>'备货成本'];
            foreach($list['list'] as $key => $val){
                $key = $key +1;
                $admin = Admin::findOne($val['prepare_userid']);
                $period = Period::findOne($val['period_id']);
                $data[$key]['adminuser'] = $admin['real_name'];
                $data[$key]['platformId'] = $val['third_order'];
                $data[$key]['name'] = $val['name'];
                $data[$key]['number'] = $period['period_number'];
                $data[$key]['id'] = $val['id'];
                $data[$key]['product_price'] = $val['product_price'];
                $data[$key]['price'] = $val['price'];
            }
            $excel = new Ex();
            $excel->download($data, '采购数据-'.date('Y-m-d H:i:s').'.xls');
        }

        $list = Deliver::thirdPlatformDeliverList($status, 25, $delivery = 2, $where);
        $count = Order::thirdStatusCount($deliverId = 2);

        $arr = [];
        foreach($list['list'] as $key=>$val){
            $goodInfo = Product::info($val['product_id']);
            $period = Period::findOne($val['period_id']);
            $info = VirtualProductInfo::findOne(['order_id'=>$val['id']]);
            $arr[$key]['id'] = $val['id'];
            $arr[$key]['order_no'] = $val['order_no'];
            $arr[$key]['name'] = $goodInfo['name'];
            $arr[$key]['cat_id'] = $goodInfo['cat_id'];
            $arr[$key]['period_number'] = $period['period_number'];
            $arr[$key]['status'] = $val['status'];
            $arr[$key]['user_id'] = ModelUser::findOne($val['user_id']);
            $arr[$key]['ship_name'] = $info['account'];
            $arr[$key]['ship_mobile'] = $val['ship_mobile'];
            $arr[$key]['create_time'] = DateFormat::microDate($val['confirm_addr_time']);
        }

        $cats = ProductCategory::find()->all();
        $catName = ArrayHelper::map($cats, 'id', 'name');

        if(empty($get)){
            $url = Yii::$app->request->getUrl().'?excel=virtual';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=virtual';
        }


        return $this->render('virtual',[
            'list' => $arr,
            'pagination' => $list['pagination'],
            'catName' => $catName,
            'status' => $status,
            'condition' => $where,
            'count' => $count,
            'url' => $url
        ]);
    }

    public function actionVirtualDetail()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');

        $detail = Deliver::orderDetail($id);
        $detail['user_id'] = ModelUser::userName($detail['user_id']);
        $goodInfo = Product::info($detail['product_id']);
        $periodInfo = Period::find()->where(['id'=>$detail['period_id']])->one();
        $cats = ProductCategory::find()->all();
        $recharge = VirtualProductInfo::findOne(['order_id'=>$id]);
        $recharge['user_id'] = ModelUser::userName($recharge['user_id']);
        $cats = ArrayHelper::map($cats, 'id', 'name');
        $person = Deliver::getEmployeeName();
        $exchange = ExchangeOrder::findOne(['order_no'=>$detail['id']]);
        $express = Express::getExpressName();

        //来源
        $conn = Yii::$app->db;
        $sql =  $conn->createCommand('select source,buy_time, sum(buy_num) as totalBuy from period_buylist_'.$periodInfo['table_id'].' where period_id = '.$periodInfo['id'].' and user_id = '.$periodInfo['user_id'].'');
        $periodTable = $sql->queryOne();
        $periodTable['source'] = Order::getSource($periodTable['source']);
        $periodTable['buy_time'] = DateFormat::microDate($periodTable['buy_time']);

        return $this->render('virtual_detail', [
            'detail' => $detail,
            'goodInfo' => $goodInfo,
            'catName' => $cats,
            'periodInfo' => $periodInfo,
            'person' => $person,
            'periodTable' => $periodTable,
            'exchange' => $exchange,
            'express' => $express,
            'recharge' => $recharge
        ]);
    }

    public function actionModifySend()
    {
        $id = Yii::$app->request->get('id');
        $detail = Deliver::findOne($id);
        $express = Express::getExpressName();

        $request = Yii::$app->request;
        if($request->isPost){
            $post = $request->post();
            $model = Deliver::findOne($post['id']);
            if (!$model) return 0;
            if($model['status'] == 4){
                $model->deliver_company = isset($post['comother']) ? $post['comother'] : $post['company'];;
                $model->deliver_order = $post['deliver_order'];
            }
            $model->platform = isset($post['other']) ? $post['other'] : $post['platform'];
            $model->payment = isset($post['paymentother']) ? $post['paymentother'] : $post['payment'];
            $model->third_order = $post['order'];
            $model->bill = $post['bill'];
            $model->price = $post['price'];
            $model->prepare_userid = Yii::$app->admin->id;
            $model->standard = $post['standard'];
            $model->mark_text = $post['comment'];
            if($model->save()){
                BackstageLog::addLog(Yii::$app->admin->id, 9, '修改备发货信息-订单'.$model['id']);
                return 1;
            } else{
                return 0;
            }
        }

        return $this->render('modify', [
            'detail' => $detail,
            'express' => $express
        ]);
    }

    public function actionSelectPrepare()
    {
        $post = Yii::$app->request->post();
        foreach($post['checkArr'] as $value){
            $model = Deliver::find()->where(['id'=>$value])->one();
            if(!$model['id']) return 1;
            $person = Admin::find()->where(['real_name'=>$post['prepareName']])->one();
            if(!$person['id']) return 1;
            $model->select_prepare = $person['id'];
            $model->save();
        }
        return 0;
    }
}