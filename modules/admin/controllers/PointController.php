<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/10/13
 * Time: 17:26
 */

namespace app\modules\admin\controllers;

use app\helpers\DateFormat;
use app\models\PaymentOrderDistribution;
use app\models\PointFollowDistribution;
use app\models\RechargeOrderDistribution;
use app\models\User;
use app\models\Order;
use Yii;
use app\helpers\Excel;
use app\helpers\Ex;
class PointController extends BaseController
{
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $get = $request->get();

        $username = $request->get('content', '');
        $userName = '';
        if($username){
            $user = User::find()->where('phone="' . $username . '" or email="' . $username . '"')->one();
            $userName = $user['id'];
        }
        $type = $request->get('type', 0);
        $start = $request->get('start_time', '');
        $end  = $request->get('end_time', '');
        $condition = ['content'=>$username, 'type'=>$type, 'startTime'=>$start, 'endTime'=>$end];

        $where = ['username'=>$userName, 'type'=>$type, 'start'=>$start, 'end'=>$end];

        $page = $request->get('page', 1);

        if(isset($get['excel']) && $get['excel'] == 'index'){
            $data = [];
            $list = PointFollowDistribution::pointList($where,$page, PHP_INT_MAX);
            $data[0] = ['phone'=>'手机', 'email'=>'邮箱','point'=>'福分','type'=>'类型','desc'=>'备注', 'time'=>'伙购时间'];
            foreach($list['list'] as $key => $val){
                $key = $key +1;
                $user = User::findOne($val['user_id']);
                $data[$key]['phone'] = $user['phone'];
                $data[$key]['email'] = $user['email'];
                $data[$key]['point'] = $val['point'];
                $type = PointFollowDistribution::getType($val['type']);
                $data[$key]['type'] = $type['name'];
                $data[$key]['desc'] = $val['desc'];
                $data[$key]['time'] = DateFormat::microDate($val['created_at']);
            }
            $excel = new Ex();
            $excel->download( $data, '福分流水-'.date('Y-m-d H:i:s').'.xls');
        }

        $list = PointFollowDistribution::pointList($where,$page, 25);

        foreach($list['list'] as $key => $val){
            $list['list'][$key]['type'] = PointFollowDistribution::getType($val['type']);
            $list['list'][$key]['user_id'] = User::findOne($val['user_id']);
        }

        if(empty($get)){
            $url = Yii::$app->request->getUrl().'?excel=index';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=index';
        }

        return $this->render('index', [
            'list' => $list,
            'condition' => $condition,
            'url' => $url
        ]);
    }

    public function actionCount()
    {
        $request = \Yii::$app->request;
        $get = $request->get();

        $id = $request->get('id', '');
        $username = $request->get('content', '');
        $start = $request->get('start_time', '');
        $end  = $request->get('end_time', '');
        $status  = $request->get('status', '0');
        $user = User::find()->where(['or', 'email="'.$username.'"', 'phone="'.$username.'"'])->one();
        $condition = ['content'=>$username, 'startTime'=>$start, 'endTime'=>$end, 'id'=>$id, 'status'=>$status];

        $where = ['user_id'=>$user['id'], 'start'=>$start, 'end'=>$end, 'id'=>$id, 'status'=>$status];

        $page = $request->get('page', 1);

        if(isset($get['excel']) && $get['excel'] == 'count'){
            $data = [];
            $list =PaymentOrderDistribution::getOrder($where, $page, $perpage = PHP_INT_MAX);
            $data[0] = ['id'=>'id','phone'=>'手机', 'email'=>'邮箱','total'=>'总金额','money'=>'实际金额','point'=>'福分','type'=>'支付方式','source'=>'支付来源', 'time'=>'伙购时间'];
            foreach($list['list'] as $key => $val){
                $key = $key +1;
                $user = User::findOne($val['user_id']);
                $data[$key]['id'] = $val['id'];
                $data[$key]['phone'] = $user['phone'];
                $data[$key]['email'] = $user['email'];
                $data[$key]['total'] = $val['total'];
                $data[$key]['money'] = $val['money'];
                $data[$key]['point'] = $val['point'];
                $type = RechargeOrderDistribution::getType($val['payment']);
                $data[$key]['type'] = $type['name'];
                $source = Order::getSource($val['source']);
                $data[$key]['source'] = $source['name'];
                $data[$key]['time'] = DateFormat::microDate($val['buy_time']);
            }
            $excel = new Ex();
            $excel->download( $data, '支付订单-'.date('Y-m-d H:i:s').'.xls');
        }

        $list = PaymentOrderDistribution::getOrder($where, $page, $perpage = 25);
        foreach($list['list'] as $key => $val){
            $list['list'][$key]['user_id'] = User::findOne($val['user_id']);
            $list['list'][$key]['buy_time'] = DateFormat::microDate($val['buy_time']);
            $list['list'][$key]['source'] = Order::getSource($val['source']);
            $list['list'][$key]['payment'] = RechargeOrderDistribution::getType($val['payment']);
        }

        if(empty($get)){
            $url = Yii::$app->request->getUrl().'?excel=count';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=count';
        }

        return $this->render('count', [
            'list' => $list,
            'condition' => $condition,
            'url' => $url
        ]);
    }
}