<?php
/**
 * Created by PhpStorm.
 * User: chenyi
 * Date: 2015/10/15
 * Time: 16:56
 * 我的伙购
 */
namespace app\modules\api\controllers;

use app\helpers\Brower;
use app\helpers\DateFormat;
use app\helpers\MyRedis;
use app\models\ActOrder;
use app\models\ActRichLog;
use app\models\Image;
use app\models\Packet;
use app\models\PeriodBuylistDistribution;
use app\models\RichSet;
use app\services\Coupon;
use app\services\User;
use Yii;
use app\services\Member;

class RichController extends BaseController
{
    public function actionDetail(){
        $type = Yii::$app->request->get('t');
        if($type == 'day'){
            $model = RichSet::find()->where(['time_type'=>1])->orderBy('id desc')->one();
            $img = 'daily_banner.png';
            $select = 'max(total)';
        }elseif($type == 'month'){
            $model = RichSet::find()->where(['time_type'=>3])->orderBy('id desc')->one();
            $img = 'monthly_banner.png';
            $select = 'sum(total)';
        }elseif($type=='season') {
            $model = RichSet::find()->where(['time_type'=>0])->orderBy('id desc')->one();
            $img = 'season_banner.png';
            $select = 'sum(total)';
        }else{
            return ['error'=>5, 'message'=>'类型不正确'];
        }

        /*$id = Yii::$app->request->get('id');
        $model = RichSet::findOne($id);*/
        if(!$model){
            echo json_encode(['error'=>1, 'message'=>'该活动不存在']);
            Yii::$app->end();
        }

        if(!$this->userId) return ['error'=>1, 'message'=>'请先登录'];

        $start = '';
        $end = '';
        $richNum = 10;
        if($model['time_type'] == 0){
            $start = $model['start_time'];
            $end = $model['end_time'] + 3600*24;
            $richNum = 100;
        }elseif($model['time_type'] == 1){
            list($start, $end) = DateFormat::rangeTime('day');
        }elseif($model['time_type'] == 2){
            list($start, $end) = DateFormat::rangeTime('week');
        }elseif($model['time_type'] == 3){
            list($start, $end) = DateFormat::rangeTime('month');
        }

//        if ($end<time()) {
//            return ['list'=>[], 'desc'=>[], 'img'=>'', 'append'=>[]];
//        }
        $cache = Yii::$app->cache;
        $key = 'RICH_RANK_LIST_DATA_'.$type;
        $list = $cache->get($key);
        if (!$list) {
            $list = PeriodBuylistDistribution::getList(10, $start, $end, $richNum, $model['time_type']);
            $cache->set($key, $list,600);
        }
        $return = [];
        $append = true;
        foreach($list as $key => $val){
            $user = \app\services\User::baseInfo($val['user_id']);
            $return[$key]['id'] = $key;
            $return[$key]['username'] = $user['username'];
            $return[$key]['money'] = $val['total'];
            $return[$key]['home_id'] = $user['home_id'];
            $return[$key]['user_id'] = $val['user_id'];
            $return[$key]['avatar'] = Image::getUserFaceUrl($user['avatar'], 80);
            if($val['user_id'] == $this->userId){
                $append = false;
            }
        }

        $self = '';
        if($append == true){
            $money = 0;
            $user = \app\services\User::baseInfo($this->userId);
            $sql = "select ".$select." as total from payment_orders_".substr($user['home_id'],0, 3)." where buy_time > '".$start."' and buy_time < '".$end."' and user_id = ".$this->userId;
            $command = \Yii::$app->db->createCommand($sql);
            $result = $command->queryOne();
            if($result['total']) $money = $result['total'];
            $self = ['username'=>$user['username'], 'money'=>$money, 'home_id'=>$user['home_id'], 'avatar'=>Image::getUserFaceUrl($user['avatar'], 80)];
        }

        return ['list'=>$return, 'desc'=>explode(';', $model['comment']), 'img'=>$img, 'append'=>$self];
    }

    //往期榜单
    public function actionPastList()
    {
        $type = Yii::$app->request->get('t');
        $page = Yii::$app->request->get('page', 1);
        $perpage = Yii::$app->request->get('perpage', 10);
        $return = [];
        if($type == 'day'){
            $list = ActRichLog::pastList(1, $page, $perpage);
            foreach($list['list'] as $key => $val){
                $return[$key]['time'] = $val['datetime'];
            }
        }elseif($type == 'month'){
            $list = ActRichLog::pastList(3, $page, $perpage);
            foreach($list['list'] as $key => $val){
                $start_date = date('Y-m-d', mktime(00, 00, 00, date('m', strtotime($val['datetime'])), 01));
                $end_date = date('Y-m-d', mktime(23, 59, 59, date('m', strtotime($val['datetime']))+1, 00));
                $return[$key]['time'] = $start_date.'--'.$end_date;
                $return[$key]['month'] = $val['datetime'];
            }
        }elseif($type == 'season') {
            $list = ActRichLog::pastList(0, $page, $perpage);
            foreach($list['list'] as $key => $val){
                $return[$key]['time'] = $val['datetime'];
            }
        }

        return ['list' => $return, 'totalCount'=>$list['totalCount'], 'totalPage'=>$list['totalPage']];
    }

    //往期榜单详情
    public function actionListDetail()
    {
        $time = Yii::$app->request->get('time');
        $t = Yii::$app->request->get('t');
        if(isset($t) && $t == 'month'){
            $time = substr($time, 0, 7);
        }
        $seasons = ActRichLog::find()->where(['type'=>0, 'rank'=>1])->orderBy('id asc')->asArray()->all();
        $seasonNum = 1;
        foreach($seasons as $key=>$value) {
            if ($value['datetime']==$time) {
                $seasonNum = $key+1;
                break;
            }
        }
        $list = ActRichLog::find()->where(['`datetime`'=>$time])->all();
        $return = [];
        foreach($list as $key => $val){
            $user = User::baseInfo($val['user_id']);
            $return[$key]['username'] = $user['username'];
            $return[$key]['rank'] = $val['rank'];
            $return[$key]['money'] = $val['money'];
            $return[$key]['home_id'] = $user['home_id'];
            $return[$key]['user_id'] = $user['id'];
            $return[$key]['avatar'] = Image::getUserFaceUrl($user['avatar'], 80);
        }

        return ['list'=>$return, 'season'=>$seasonNum];
    }

    //中奖记录
    public function actionLottery()
    {
        if(!$this->userId) return ['code'=>1, 'message'=>'请先登陆'];
        $type = Yii::$app->request->get('t');
        $page = Yii::$app->request->get('page', 1);
        $perpage = Yii::$app->request->get('perpage', 10);
        $return = [];
        $rewards = ActRichLog::rewards('rich'.$type.'config');
        if($type == 'day'){
            $list = ActRichLog::userReward($this->userId, 1, $page, $perpage);
            foreach($list['list'] as $key => $val){
                $return[$key]['time'] = date('Y-m-d',strtotime($val['datetime'].' +1 day'));
                $return[$key]['id'] = $val['id'];
                $return[$key]['rank'] = $val['rank'];
                $type = $rewards[$val['rank']]['type'];
                $return[$key]['type'] = $type;
                $return[$key]['status'] = $val['status'];
                if($type == 2){
                    $name = '伙购币'.$rewards[$val['rank']]['name'];
                }elseif($type == 3){
                    $name = '消费返现'.$rewards[$val['rank']]['name'].'%';
                }elseif($type == 1){
                    $name = $rewards[$val['rank']]['name'];
                } elseif ($type == 4) {
                    $name = $rewards[$val['rank']]['name'];

                    $packet = Packet::findOne($name);
                    $name = $packet['name'];
                }
                $return[$key]['name'] = $name;
            }
        }elseif($type == 'month'){
            $list = ActRichLog::userReward($this->userId, 3, $page, $perpage);
            foreach($list['list'] as $key => $val){
                $order = 0;
                $orderId = 0;
                //$start_date = date('Y-m-d', mktime(00, 00, 00, date('m', strtotime($val['datetime']))+1, 01));
                //$end_date = date('Y-m-d', mktime(23, 59, 59, date('m', strtotime($val['datetime']))+2, 00));
                $return[$key]['time'] = date('Y-m-01',strtotime($val['datetime'].' +1 month'));;
                $return[$key]['id'] = $val['id'];
                $return[$key]['rank'] = $val['rank'];
                $type = $rewards[$val['rank']]['type'];
                $return[$key]['type'] = $type;
                $return[$key]['status'] = $val['status'];
                if($type == 2){
                    $name = '伙购币'.$rewards[$val['rank']]['name'];
                }elseif($type == 3){
                    $name = '消费返现'.$rewards[$val['rank']]['name'].'%';
                }elseif($type == 1){
                    $name = $rewards[$val['rank']]['name'];
                    $orderModel = ActOrder::find()->where(['act_obj_id'=>$val['id']])->one();
                    $order = $orderModel['status'];
                    $orderId = $orderModel['id'];
                } elseif ($type == 4) {
                    $name = $rewards[$val['rank']]['name'];

                    $packet = Packet::findOne($name);
                    $name = $packet['name'];
                }
                $return[$key]['name'] = $name;
                $return[$key]['order'] = $order;
                $return[$key]['orderId'] = $orderId;
            }
        }elseif($type == 'season') {
            $list = ActRichLog::userReward($this->userId, 0, $page, $perpage);
            foreach($list['list'] as $key => $val){
                $order = 0;
                $orderId = 0;

                $return[$key]['time'] = date('Y-m-d', $val['created_at']);
                $return[$key]['id'] = $val['id'];
                $return[$key]['rank'] = $val['rank'];
                $type = $rewards[$val['rank']]['type'];
                $return[$key]['type'] = $type;
                $return[$key]['status'] = $val['status'];
                if($type == 2){
                    $name = '伙购币'.$rewards[$val['rank']]['name'];
                }elseif($type == 3){
                    $name = '消费返现'.$rewards[$val['rank']]['name'].'%';
                }elseif($type == 1){
                    $name = $rewards[$val['rank']]['name'];
                    $orderModel = ActOrder::find()->where(['act_obj_id'=>$val['id']])->one();
                    $order = $orderModel['status'];
                    $orderId = $orderModel['id'];
                } elseif ($type == 4) {
                    $name = $rewards[$val['rank']]['name'];

                    $packet = Packet::findOne($name);
                    $name = $packet['name'];
                }
                $return[$key]['name'] = $name;
                $return[$key]['order'] = $order;
                $return[$key]['orderId'] = $orderId;
            }
        }
        return ['list' => $return, 'totalCount'=>$list['totalCount'], 'totalPage'=>$list['totalPage']];
    }

    //兑奖
    public function actionGetLottery()
    {
        if(!$this->userId) {
            return ['code'=>1, 'message'=>'请先登陆'];
        }
        if(!Brower::isMobile()){
            return ['code'=>6, 'msg'=>'非法操作!'];
        }

        $id = Yii::$app->request->get('id');
        //$type = Yii::$app->request->get('type');
        $model = ActRichLog::find()->where(['user_id'=>$this->userId, 'id'=>$id])->andWhere('status = 0')->one();
        if(!$model) return ['code'=>2, 'message'=>'记录不存在'];

        $redis = new MyRedis();
        $userGetLotteryKey = 'RICH_GET_LOTTERY_'. $id .'_' . $this->userId;
        $userGetLotteryRequest = $redis->incr($userGetLotteryKey);
        $redis->expire($userGetLotteryKey, 60);
        if ($userGetLotteryRequest > 1) {
            return ['code'=>7, 'message'=>'正在领奖...'];
        }


        if($model['type'] == 0){
            $type = 'richseasonconfig';
        }else if($model['type'] == 1){
            $type = 'richdayconfig';
        }else if($model['type'] == 3){
            $type = 'richmonthconfig';
        }else{
            $redis->del($userGetLotteryKey);
            return ['code'=>'3', 'message'=>'奖品不存在'];
        }
        $rewards = ActRichLog::rewards($type);

        if (!isset($rewards[$model['rank']])) {
            $redis->del($userGetLotteryKey);
            return ['code'=>5, 'message'=>'发放失败,请联系客服'];
        }
        $r = $rewards[$model['rank']];

        if($r['type'] == 1){
            //实物，创建订单
            $redis->del($userGetLotteryKey);
            return ['code'=>4, 'message'=>'实物已领取'];
        }elseif($r['type'] == 2){
            //现金，发放到账户
            $member = new Member(['id'=>$model['user_id']]);
            $member->editMoney($r['name'], 3, '土豪榜现金奖励', 6);
        }elseif($r['type'] == 3){
            //返现，发放到账户（小数福分补足）
            $total = sprintf("%.2f", ($model['money'] * ($r['name'] / 100)));
            $arr = explode('.', $total);
            $first = floor($arr[0]);
            $second = floor($arr[1]);
            $member = new Member(['id'=>$model['user_id']]);
            if(isset($first) && $first != 0){
                $member->editMoney($first, 3, '土豪榜返现奖励', 6);
            }
            if(isset($second) && $second != 0){
                $member->editPoint($second, 12, '土豪榜返现补充福分');
            }
        } elseif($r['type'] == 4){

            $rs = Coupon::receivePacket($r['name'], $model['user_id'], 'activity_rich');
            if ($rs['code'] == '0') {
                $pid = $rs['data']['pid'];
                $info = Coupon::openPacket($pid,$model['user_id']);
            } else {
                $redis->del($userGetLotteryKey);
                return ['code'=>5, 'message'=>!empty($rs['msg']) ? $rs['msg'] : '发放失败,请联系客服'];
            }
        } else {
            $redis->del($userGetLotteryKey);
            return ['code'=>5, 'message'=>'发放失败,请联系客服'];
        }
        $model->status = 1;
        $model->last_modify = time();
        if(isset($total) && $total >= 1) $status = 1;
        else $status = 0;
        if($model->save()){
            $redis->del($userGetLotteryKey);
            return ['code'=>0, 'message'=>'发放奖品成功', 'total'=>$status];
        }else{
            $redis->del($userGetLotteryKey);
            return ['code'=>5, 'message'=>'发放失败,请联系客服'];
        }
    }
}