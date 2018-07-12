<?php

namespace app\controllers;

use app\helpers\Brower;
use app\helpers\MyRedis;
use app\models\ActivityJd;
use app\models\ActivityJdLog;
use app\models\ActivityProducts;
use app\models\ActQualification;
use app\models\Lottery;
use app\models\Order;
use app\models\Packet;
use app\models\PaymentOrderItemDistribution;
use app\models\PkOrders;
use app\models\PkPaymentOrderItemDistribution;
use app\models\PkUserBuylistDistribution;
use app\models\Product;
use app\models\User;
use Yii;
use yii\web\NotFoundHttpException;
use app\models\LotteryLog;
use app\models\LotteryRewardLog;
use app\models\User as ModelUser;
use app\models\Reward;
use app\models\Image;
use yii\web\Response;
use app\models\Coupon as ModelCoupon;
use app\services\Coupon;

class ActiveController extends BaseController
{
    const USER_LOTTERY = 'ACTIVE_USER_LOTTERY_';

    public $userId=0;
    public $source = 0;

    public function init()
    {
        parent::init();
        $request = Yii::$app->request;
        $token = $request->get('token');
        if (!$token) {
            $token = $request->cookies->getValue('_utoken');
        }
        $tokenSource = $request->get('tokenSource');
        $type = in_array($tokenSource,['__ios__','__android__']) ? 1 : null;
        if ($token) {
            $user = User::findIdentityByAccessToken($token,$type);
            if ($user) {
                $this->userId = $user->id;
                $this->token = $token;
                if($tokenSource == '__ios__'){
                    $this->source = 3;
                }elseif($tokenSource == '__android__'){
                    $this->source = 4;
                }
            }
        }
    }

    public function actionLottery()
    {
        $uid = $this->userId;
        $get = Yii::$app->request->get();
        $num = ActQualification::findOne(['user_id'=>$uid]);
        $lotteryNum = isset($num) ? $num['num'] : 0;
        $id = Yii::$app->request->get('id');
        $model = Lottery::findOne($id);
        if(!$model){
            throw new NotFoundHttpException("页面未找到");
        }

        $get['token'] = isset($get['token']) ? urlencode($get['token']) : '';
        return $this->render('lottery', [
            'get' => $get,
            'uid' => $uid,
            'lottery_num' => $lotteryNum,
            'id' => $id
        ]);
    }

    public function actionRaffle()
    {
        $response = \Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        if (!$this->userId) {
            return ['code' => 1, 'msg' => '未登录'];
        }
        if(!Brower::isMobile()){
            return ['code'=>10, 'msg'=>'非法操作!'];
        }

        $redis = new MyRedis();
        $userLotteryKey = self::USER_LOTTERY.'_'.$this->userId;

        $requestNum = $redis->incr($userLotteryKey);
        $redis->expire($userLotteryKey, 60);
        if($requestNum > 1){
            return ['code'=>10, 'msg'=>'正在抽奖'];
        }

        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Lottery::find()->where(['id'=>$id, 'status'=>1])->one();
        if(!$model){
            $redis->del($userLotteryKey);
            return ['code'=>2, 'msg'=>'该活动不存在'];
        }

        $quaModel = ActQualification::find()->where(['user_id'=>$this->userId])->one();
        if($quaModel['num'] == 0) {
            $redis->del($userLotteryKey);
            return ['code'=>9, 'msg'=>'您的抽奖次数不够'];
        }

        $userModel = ModelUser::findOne($this->userId);
        if($model['consume'] > 0){
            if($userModel['point'] < $model['consume']) {
                $redis->del($userLotteryKey);
                return ['code'=>3, 'msg'=>'您的福分不够'];
            }
        }

        $reward = Reward::lotteryRaffle($model['id']);
        if(!$reward['number']) {
            $redis->del($userLotteryKey);
            return ['code'=>4, 'msg'=>'抽奖失败，请联系客服'];
        }

        //更新奖品表
        $rewardModel = Reward::findOne($reward['number']);
        if($rewardModel['left'] == 0){
            $rewardModel = Reward::find()->where('lottery_id = '.$model['id'])->orderBy('probability desc')->one();
        }

        if($rewardModel['id']){
            $trans = Yii::$app->db->beginTransaction();
            try{
                //更新奖品表
                if(($rewardModel['left'] - 1) > 0 ) $num = $rewardModel['left'] - 1;
                else $num = 0;
                $rewardModel->left = $num;
                if(!$rewardModel->save()){
                    $trans->rollBack();

                    $redis->del($userLotteryKey);
                    return ['code'=>3, 'msg'=>'抽奖失败'];
                }

                //更新用户福分表
                if($model['consume'] > 0){
                    $userModel->point = $userModel['point'] - $model['consume'];
                    if(!$userModel->save()){
                        $trans->rollBack();
                        $redis->del($userLotteryKey);
                        return ['code'=>4, 'msg'=>'抽奖失败'];
                    }
                }

                //根据类型写入数据
                if($rewardModel['type'] != 3) $status = 1;
                else $status = 0;
                $ret = LotteryLog::addLog($model['id'],$this->userId, $rewardModel['id'], $status);
                if(!$ret){
                    $trans->rollBack();
                    $redis->del($userLotteryKey);
                    return ['code'=>5, 'msg'=>'抽奖失败'];
                }
                if($rewardModel['type'] != 3){
                    $ret = LotteryRewardLog::addLog($this->userId, $rewardModel['id'], $model['id']);
                    if(!$ret){
                        $trans->rollBack();
                        $redis->del($userLotteryKey);
                        return ['code'=>12, 'msg'=>'抽奖失败'];
                    }
                    Reward::rewardType($rewardModel['id'], $this->userId, $this->source);
                }
                $quaModel->num = $quaModel['num'] - 1;
                if(!$quaModel->save()){
                    $trans->rollBack();
                    $redis->del($userLotteryKey);
                    return ['code'=>6, 'msg'=>'抽奖失败'];
                }
                $trans->commit();
                $return['pic'] = Image::getActiveInfoUrl($rewardModel['icon'], 'org');
                $returnId = $reward['id'] - 1;
                if($returnId == 3){
                    $actualId = 7;
                }elseif($returnId == 4){
                    $actualId = 3;
                }else if($returnId == 5){
                    $actualId = 6;
                }else if($returnId == 6){
                    $actualId = 5;
                }else if($returnId == 7){
                    $actualId = 4;
                }else{
                    $actualId = $returnId;
                }
                $redis->del($userLotteryKey);

                //file_put_contents('order.txt', print_r($rewardModel['name'], true).PHP_EOL, FILE_APPEND);
                return ['code'=>0, 'id'=>$actualId, 'name'=>$rewardModel['name'], 'pic'=>$return['pic'], 'type'=>$rewardModel['type']];
            }catch (\Exception $e){
                $trans->rollBack();
                $redis->del($userLotteryKey);
                return ['code'=>7, 'msg'=>'抽奖失败'];
            }

        }



    }

    public function actionActivityJd(){

        //获取用户id
        $response = \Yii::$app->response;
     $response->format = Response::FORMAT_JSON;
         $user_id = $this->userId;
        //$user_id=6;
        $user=User::findOne($user_id);
        if(!$user_id)
        {
            return ['code'=>201, 'msg'=>'用户不存在'];
        }
        //查询所有京东卡商品
        /*****查询普通商品*********/
      $jdproductlist= Product::find()->select('id')->where(['delivery_id'=>8])->asArray()->all();
       foreach($jdproductlist as $row)
       {
           $jdid_list[]=$row['id'];               //所有京东商品
       }
        $homeid=$user->home_id;
        //查询最后一次获取的期数id;
        $activtyjd = ActivityJd::find()->where(['user_id'=>$user_id])->one();
        $where=['user_id'=>$user_id,'product_id'=>$jdid_list];
       // $where[]=['product_id','product_id',['55']];
        $ywhere='';
        $nwhere='';
        //如果存在
        if($activtyjd)
        {
            $orderid=$activtyjd->orderid;        //查询的最后订单id
            $paymentid=$activtyjd->paymentid;        //查询的最后购买记录id


            if($orderid >0)
            {

               $ywhere=['>', 'id', $orderid];
            }
            if($paymentid >0)
            {

                $nwhere=['>', 'id', $paymentid];
            }
        }

        $nselect=['sum(nums) as nums','max(id) as id'];
        $yselect=['id','period_id','product_id'];         //期号,
        $s_money=0;       //总金额
        //查询已中奖的 用户商品期数

       $ylist = Order::find()->select($yselect)->where($where)->andwhere($ywhere)->orderBy('id DESC')->asArray()->all();

   //    $ylist = $ylist = Order::find()->select($yselect)->where($where)->andwhere($ywhere)->orderBy('id DESC')->createCommand()->getRawSql();

        if($ylist)
        {
            foreach($ylist as &$row){

                //查询面额
                $product=Product::findOne($row['product_id']);
                switch ($product->face_value)
                 {
                    case 1000:
                        $red=30;
                            break;
                    case 500:
                        $red=15;
                         break;
                    default:
                        $red=3;
                        break;
                 }
                  $s_money+=$red;
                $period_ids[]=$row['period_id'];
            }
                $nwhere=['not in','period_id',$period_ids];
        }
        //查询所有购买的

       $nlist = PaymentOrderItemDistribution::findByTableId($homeid)->select($nselect)->where($where)->andWhere($nwhere)->orderBy('id DESC')->asArray()->all();
        if($nlist)
        {
            $s_money+=$nlist[0]['nums'];
        }

        /*****查询普通商品结束*********/

        /*****查询pk商品开始*********/
        $jdpkproductlist= ActivityProducts::find()->select('id')->where(['is_virtual'=>1])->asArray()->all();
        foreach($jdpkproductlist as $row)
        {
            $pkjdid_list[]=$row['id'];               //所有京东商品
        }
        $pkwhere=['user_id'=>$user_id,'product_id'=>$pkjdid_list];
        $ypkwhere='';
        $npkwhere='';
        if($activtyjd)
        {

            $pkorderid=$activtyjd->pkorderid;        //查询的最后订单id
            $pkpaymentid=$activtyjd->pkpaymentid;        //查询的最后购买记录id

            if($pkorderid >0)
            {

                $ypkwhere=['>', 'id', $pkorderid];
            }
            if($pkpaymentid >0)
            {

                $npkwhere=['>', 'id', $pkpaymentid];
            }else{
                $npkwhere='';
            }

        }
        $npkselect=['sum(nums) as nums','max(id) as id'];
        $ypkselect=['id','period_id','product_id'];         //期号,
        //查询pk已中奖的 用户商品期数
        $ypklist = PkOrders::find()->select($ypkselect)->where($pkwhere)->andwhere($ypkwhere)->orderBy('id DESC')->asArray()->all();
        if($ypklist)
        {
            foreach($ypklist as &$row){

                //查询面额
                $product=ActivityProducts::findOne($row['product_id']);
                switch ($product->face_value)
                {
                    case 1000:
                        $pkred=30;
                        break;
                    case 500:
                        $pkred=15;
                        break;
                    default:
                        $pkred=3;
                        break;
                }
                $s_money+=$pkred;
                $pkperiod_ids[]=$row['period_id'];
            }
            $npkwhere=['not in','period_id',$pkperiod_ids];
        }

        $npklist = PkUserBuylistDistribution::findByUserHomeId($homeid)->select($npkselect)->where($where)->andWhere($npkwhere)->orderBy('id DESC')->asArray()->all();
        var_dump(PkUserBuylistDistribution::findByUserHomeId($homeid)->select($npkselect)->where($where)->andWhere($npkwhere)->orderBy('id DESC')->createCommand()->getRawSql());exit;
        if($npklist)
        {
            $s_money+=$npklist[0]['nums'];
        }
        /*****查询pk商品*********/
     //   $nlist = PaymentOrderItemDistribution::findByTableId($homeid)->select($nselect)->where($where)->andWhere($nwhere)->orderBy('id DESC')->createCommand()->getRawSql();
      //  var_dump($nlist);exit;
       // echo '<pre>';
       // print_r($nlist);exit;
        //插入记录

        if($activtyjd)
        {
            $return_money =$activtyjd->money+$s_money;
            $activtyjd->money=$return_money;
            $activtyjd->up_time=time();

            if($ylist)$activtyjd->orderid=$ylist[0]['id'];           //最大的订单id

            if($ypklist) $activtyjd->pkorderid=$ypklist[0]['id'];           //pk最大的订单id

            if($nlist[0]['id'])$activtyjd->paymentid=$nlist[0]['id'];          //pk最大的购买记录id

            if($npklist[0]['id'])$activtyjd->pkpaymentid=$npklist[0]['id'];          //pk最大的购买记录id

            $rs=  $activtyjd->save();

        }
        else
        {
            $return_money =$s_money;
            $activtyjdModel=new ActivityJd();
            $activtyjdModel->user_id=$user_id;
            $activtyjdModel->money=$s_money;
            $activtyjdModel->up_time=time();
            if($ylist)$activtyjdModel->orderid= $ylist[0]['id'];           //最大的订单id

            if($nlist[0]['id']) $activtyjdModel->paymentid = $nlist[0]['id'];          //最大的购买记录id

            if($ypklist)$activtyjdModel->pkorderid=$ypklist[0]['id'];           //pk最大的订单id

            if($npklist[0]['id'])$activtyjdModel->pkpaymentid =$npklist[0]['id'];          //pk最大的购买记录id

             $rs=$activtyjdModel->save();

        }

            $end_time='2016-08-01 12:00:00';
        $end_time=strtotime($end_time);
     //   $end_time=$end_time-time();
            if($rs){
                return ['code'=>200, 'money'=>$return_money,'year'=>date('Y',$end_time),'month'=>date('m',$end_time),'day'=>date('d',$end_time),'hour'=>date('h',$end_time),'minute'=>date('i',$end_time)];
            }else{
                return ['code'=>201, 'msg'=>'抽奖失败'];
            }

    }


    //领取京东E卡送红包活动
    public function actionGetJdred(){
        $response = \Yii::$app->response;
      $response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $red_id = $request->get('red_id');

        $user_id = $this->userId;
        $red_arr=['22,23,24,25'];
        //  $red_id=22;
    //    $user_id=6;
        //查询红包是否存在
        if(!in_array($red_id,$red_arr)){
            return ['code'=>201, 'message'=>'红包不存在'];
        }
        $packet=Packet::findOne($red_id);
        $red=json_decode($packet->content,1);

            $coupon_id=array_keys($red)[0]; //折扣劵id
            $coupon=ModelCoupon::findOne($coupon_id);  //折扣卷详情

        $coupon_amount=array_values(json_decode($coupon->amount,1))[0];            //折扣卷金额

       // var_dump(array_values($red)[0]);exit;
        if(!$packet)
        {
            return ['code'=>201, 'message'=>'红包不存在'];
        }
        $activtyjd = ActivityJd::find()->where(['user_id'=>$user_id])->one();     //用户活动获取总金额
        if($activtyjd->money<$coupon_amount)
        {
            return ['code'=>201, 'message'=>'余额不足'];
        }
        $trans = \Yii::$app->db->beginTransaction();
       try{
            //扣减余额
           $old_money=$activtyjd->money;
           $remain=$activtyjd->money-$coupon_amount;
           $activtyjd->money=$remain;
           $activtyjd->up_time=time();
           $rs1= $activtyjd->save();
           //记录
            $log= new ActivityJdLog();
            $log->red_id=$packet->id;
            $log->user_id=$user_id;
            $log->remain=array_values($red)[0];
            $log->add_time=time();
            $log->old_money=$old_money;
           $rs2= $log->save();
           //送红包
           $source='京东E卡送红包活动';
          $rs3= Coupon::receivePacket($packet->id,$user_id,$source);

        if($rs1 && $rs2){
            if($rs3['code']==0)
            {
                $trans->commit();
                return ['code' => 200, 'message' =>'红包已送到您的账户中'];
            }
        }
           $trans->rollBack();
           return ['code' => 200, 'message' =>'网络错误'];
       }catch (\Exception $e) {
           $trans->rollBack();
           return ['code' => 201, 'message' => ''];
       }

    }


}