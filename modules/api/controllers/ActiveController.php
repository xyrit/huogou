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
use app\models\Active;
use app\models\ActivityJd;
use app\models\ActivityJdLog;
use app\models\ActivityProducts;
use app\models\ActQualification;
use app\models\Config;
use app\models\Coupon as ModelCoupon;
use app\models\CurrentPeriod;
use app\models\Honour;
use app\models\HonourDesc;
use app\models\Image;
use app\models\ActivityJdMlog;
use app\models\Lottery;
use app\models\LotteryLog;
use app\models\LotteryRewardLog;
use app\models\Order;
use app\models\Packet;
use app\models\PaymentOrderItemDistribution;
use app\models\Period;
use app\models\PeriodBuylistDistribution;
use app\models\PkPaymentOrderItemDistribution;
use app\models\Product;
use app\models\RechargeOrderDistribution;
use app\models\Reward;
use app\models\RichSet;
use app\models\User as UserModel;
use app\models\UserBuylistDistribution;
use app\models\UserSign;
use app\models\UserTask;
use app\services\Coupon;
use app\services\User;
use Yii;
use yii\data\Pagination;

class ActiveController extends BaseController
{
    const SIGN_IN_DEVICE_ID = 'sign_in_device_id_';
    const SIGN_IN_LOCK = 'sign_in_lock_';
    const USER_LOTTERY = 'ACTIVE_USER_LOTTERY_';
    //活动列表
    public function actionIndex()
    {

        //发现页 版本控制
        $version = Yii::$app->request->get('v','');
        $from = Brower::whereFrom();
        $list = Active::find()->where(['status'=>1, 'from'=>$from])->orderBy('list_order desc, id desc')->asArray()->all();
        foreach($list as $key => &$one){
            $one['icon'] = Image::getActiveInfoUrl($one['icon'], 'org');
            if ((!$version && in_array($one['id'],array(7,8,11))) || version_compare($one['min_ver'],$version,'>')) {
                unset($list[$key]);
            }
            unset($one['created_at']);
            unset($one['min_ver']);
        }
        return ['findList'=>array_values($list)];
    }

    //土豪榜
    public function actionRich(){
        $id = Yii::$app->request->get('id');
        $model = RichSet::findOne($id);
        if(!$model){
            return ['code'=>1, 'msg'=>'该活动不存在'];
        }

        if($model['status'] == 0) return ['code'=>2, 'msg'=>'该活动已关闭'];

        $start = '';
        $end = '';
        if($model['time_type'] == 0){
            $start = $model['start_time'];
            $end = $model['end_time'];
        }elseif($model['time_type'] == 1){
            $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $end = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        }elseif($model['time_type'] == 2){
            $start = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
            $end = mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        }elseif($model['time_type'] == 3){
            $start = mktime(0,0,0,date('m'),1,date('Y'));
            $end = mktime(23,59,59,date('m'),date('t'),date('Y'));
        }

        $list = PeriodBuylistDistribution::getList(10, $start, $end, 10, $model['time_type']);
        $return = [];
        foreach($list as $key => $val){
            $user = \app\services\User::baseInfo($val['user_id']);
            $return[$key]['username'] = $user['username'];
            $return[$key]['money'] = $val['total'];
        }
        return ['richer'=>$return];
    }

    // 任务列表
    public function actionTask()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $type = Yii::$app->request->get('type', 1);
        $level = Yii::$app->request->get('level', 0);
        $source = $this->tokenSource == '__ios__' ? 3 : 4;
        $result = UserTask::taskList($this->userId, $type, $level);

        $redis = new MyRedis();
        $result['desc'] = explode(';', $redis->get(UserTask::USER_TASK . $type));
        if ($source == 3) {
            array_push($result['desc'], '声明：所有奖品抽奖活动与苹果公司（Apple Inc.）无关');
        }

        if ($type == 1) {
            $img = 'new-task.png';
        } elseif ($type == 2) {
            $img = 'task-banner.png';
        } elseif ($type == 3) {
            $img = 'growth-banner.png';
        }
        $result['img'] = Yii::$app->params['skinUrl'] . '/img/active/' . ($source == 3 ? 'ios' : 'android') . '/' . $img;

        return $result;
    }

    // 完成任务
    public function actionCompleteTask()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $taskId = Yii::$app->request->get('task_id');
        if ($taskId == 1) {
            $userTask = UserTask::findOne(['user_id' => $this->userId, 'task_id' => $taskId]);
            if ($userTask && $userTask['status'] == 0) {
                $userTask->status = 1;
                $userTask->progress = 1;
                $userTask->save();
            } elseif (!$userTask) {
                $userTask = new UserTask();
                $userTask->user_id = $this->userId;
                $userTask->task_id = $taskId;
                $userTask->status = 1;
                $userTask->progress = 1;
                $userTask->save();
            } else {
                return ['code' => 101, 'msg' => '该任务已完成'];
            }
            return ['code' => 100];
        } elseif ($taskId == 8) {
            $key = UserTask::DAILY_TASK_KEY . date('Y-m-d') . '_' . $this->userId;
            $redis = new MyRedis();
            if ($redis->ttl($key) <= 0) {
                $time = strtotime("tomorrow") - time() - 1;
                $redis->expire($key, $time);
            }
            if (!$redis->hget($key, $taskId)) {
                $redis->hset($key, [$taskId => 1]);
                return ['code' => 100];
            } else {
                return ['code' => 101, 'msg' => '该任务已完成'];
            }
        }
        return ['code' => 101, 'msg' => '非法任务'];
    }

    // 领取任务奖励
    public function actionAwardTask()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $source = $this->tokenSource == '__ios__' ? 3 : 4;

        $taskId = Yii::$app->request->get('task_id');
        $status = UserTask::completeTask($this->userId, $taskId, $source);
        if (is_array($status)) {
            return ['code' => 100, 'list' => $status, 'msg' => '领取成功'];
        }
        if ($status == 0) {
            return ['code' => '101', 'msg' => '该任务未完成'];
        } elseif ($status == 1) {
            return ['code' => '100', 'msg' => '领取成功'];
        } elseif ($status == 2) {
            return ['code' => '101', 'msg' => '奖励已领取'];
        }
    }

    // 签到列表
    public function actionSignList()
    {
//        if ($this->userId == 0) {
//            return ['code' => 201, 'msg' => '未登录'];
//        }

        $source = $this->tokenSource == '__ios__' ? 3 : 4;

        $list = UserSign::getList($this->userId, $source);
        $list['code'] = 100;
        return $list;
    }

    // 签到
    public function actionSignIn()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $redis = new MyRedis();
        $lockKey = self::SIGN_IN_LOCK . $this->userId;
        $lockKeyNum = $redis->incr($lockKey);
        $redis->expire($lockKey, 60);
        if ($lockKeyNum > 1) {
            return ['code' => 101, 'msg' => '正在签到'];
        }

        $sign = Yii::$app->request->get('sign');
        if (!empty($sign)) {
            if (!$this->checkValidation()) {
                $redis->del($lockKey);
                return ['code' => 101, 'msg' => '参数有误'];
            }

            $deviceId = Yii::$app->request->get('deviceId');
            $key = self::SIGN_IN_DEVICE_ID . $deviceId;
            $num = $redis->get($key);
            if ($num >= 5) {
                $redis->del($lockKey);
                return ['code' => 101, 'msg' => '您的设备已超过签到次数'];
            }
        } else {
            $user = \app\models\User::findOne($this->userId);
            $tableId = RechargeOrderDistribution::getTableIdByUserHomeId($user['home_id']);
            $query = RechargeOrderDistribution::findByTableId($tableId)->where(['user_id' => $this->userId])->andWhere(['payment' => [1, 2, 3]]);
            $query->andWhere(['=', 'status', RechargeOrderDistribution::STATUS_PAID])->andWhere(['>=','create_time', strtotime(date('Ymd'))]);
            $query->andWhere(['<>', 'money', 0]);
            if (!$query->one()) {
                $redis->del($lockKey);
                return ['code' => 102, 'msg' => '请先充值'];
            }
        }



        $source = $this->tokenSource == '__ios__' ? 3 : 4;

        $userSign = UserSign::findOne(['user_id' => $this->userId]);
        if (empty($userSign) || $userSign['signed_at'] != date("Ymd")) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                if (empty($userSign)) {
                    $userSign = new UserSign();
                    $userSign->user_id = $this->userId;
                }
                $userSign->signed_at = date("Ymd");
                $userSign->continue = isset($userSign->continue) ? ($userSign->continue + 1) : 1;
                $userSign->total = isset($userSign->total) ? ($userSign->total + 1) : 1;
                if (!$userSign->save()) {
                    $trans->rollBack();
                    $redis->del($lockKey);
                    return ['code' => 101];
                }

                $icon = UserSign::award($this->userId, $userSign->continue, $source);
                if (!empty($sign)) {
                    $redis->incr($key);
                    if ($redis->ttl($key) <= 0) {
                        $time = strtotime("tomorrow") - time() - 1;
                        $redis->expire($key, $time);
                    }
                }
                $trans->commit();
                $redis->del($lockKey);
                return ['code' => 100, 'msg' => $icon];
            } catch (\Exception $e) {
                $trans->rollBack();
                $redis->del($lockKey);
                return ['code' => 101, 'msg' => $e->getMessage()];
            }
        }
        $redis->del($lockKey);
        return ['code' => 101, 'msg' => '已签到'];
    }

    // 发现页
    public function actionActiveList()
    {
        $list = Active::find()->where('status = 1')->all();
        return $list;
    }

    //抽奖页
    public function actionLotteryConfig()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Lottery::find()->where(['id'=>$id, 'status'=>1])->one();
        if(!$model){
            return ['code'=>1, 'msg'=>'该活动不存在'];
        }

        if(!(time() > $model['start_time'] && time() < $model['end_time'])){
            return ['code'=>2, 'msg'=>'该活动已过期'];
        }

        $list = Reward::find()->where(['lottery_id'=>$model['id'], 'del'=>0])->orderBy('id desc')->limit(8)->asArray()->all();
        $returnData = [];
        foreach($list as $key => $val){
            $returnData[$key]['id'] = $key;
            $returnData[$key]['name'] = $val['name'];
            // $returnData[$key]['total'] = $val['num'];
            //$returnData[$key]['left'] = $val['left'];
            //$returnData[$key]['pro'] =  $val['probability'];
            $returnData[$key]['icon'] = Image::getActiveInfoUrl($val['basename'], 'org');
        }

        return $returnData;
    }

    //抽奖页
    public function actionLotteryConfigNew()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Lottery::find()->where(['id'=>$id, 'status'=>1])->one();
        if(!$model){
            return ['code'=>1, 'msg'=>'该活动不存在'];
        }

        if(!(time() > $model['start_time'] && time() < $model['end_time'])){
            return ['code'=>2, 'msg'=>'该活动已过期'];
        }

        $list = Reward::find()->where(['lottery_id'=>$model['id'], 'del'=>0])->orderBy('id desc')->limit(8)->asArray()->all();
        $returnData = [];
        foreach($list as $key => $val){
            $returnData[$key]['id'] = $key;
            $returnData[$key]['name'] = $val['name'];
            // $returnData[$key]['total'] = $val['num'];
            //$returnData[$key]['left'] = $val['left'];
            //$returnData[$key]['pro'] =  $val['probability'];
            $returnData[$key]['icon'] = Image::getActiveInfoUrl($val['basename'], 'org');
        }

        $num = ActQualification::find()->where(['user_id'=>$this->userId])->one();
        if(!$num) $lotteryNum = 0;
        else $lotteryNum = $num['num'];

        return ['list'=>$returnData, 'num'=>$lotteryNum];
    }

    public function actionRewardList()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Lottery::findOne($id);
        if(!$model){
            return ['code'=>1, 'msg'=>'活动不存在'];
        }
        $list = LotteryRewardLog::find()->where(['activity_id'=>$id])->orderBy('id desc')->limit(50)->asArray()->all();
        $arr = [];
        foreach($list as $key => $val){
            $user = \app\services\User::baseInfo($val['user_id']);
            $reward = Reward::findOne($val['reward_id']);
            $arr[$key]['reward'] = $reward['name'];
            $arr[$key]['username'] = $user['username'];
        }
        return $arr;
    }

    public function actionLotteryRewardList()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 10);
        $model = Lottery::findOne($id);
        if(!$model){
            return ['code'=>1, 'msg'=>'活动不存在'];
        }

        if(!$this->userId){
            return ['code'=>2, 'msg'=>'用户未登录'];
        }

        $query = LotteryRewardLog::find()->where(['activity_id'=>$id, 'user_id'=>$this->userId]);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $list = $countQuery->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $arr = [];
        foreach($list as $key => $val){
            $user = \app\services\User::baseInfo($val['user_id']);
            $reward = Reward::findOne($val['reward_id']);
            $arr[$key]['time'] = DateFormat::microDate($val['created_at']);
            $arr[$key]['reward'] = $reward['name'];
            $arr[$key]['username'] = $user['username'];
        }

        return ['list'=>$arr, 'totalCount'=>$pagination->totalCount, 'totalPage'=>$pagination->getPageCount()];
    }

    public function actionRaffle()
    {
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

        $userModel = \app\models\User::findOne($this->userId);
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
                    Reward::rewardType($rewardModel['id'], $this->userId, $this->tokenSource);
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


    /**
     * 分享一天只有一次抽奖机会
     */
    public function actionAddLotteryNum()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        if (!Brower::isMobile()) {
            return ['code' => 201, 'msg' => '未登录!'];
        }

        $type = Yii::$app->request->get('type');
        if($type != 2){
            return ['code'=>201, 'msg'=>'类型不正确'];
        }

        $redis = new MyRedis();
        $userShareKey = 'SHARE_LOTTERY_NUM_' . $this->userId;
        $userShareKeyNum = $redis->incr($userShareKey);
        $redis->expire($userShareKey, 60);
        if ($userShareKeyNum > 1) {
            return ['code'=>203, 'message'=>'正在分享...'];
        }

        $return = ActQualification::addNum($this->userId, 2, 1);
        $redis->del($userShareKey);
        return $return;
    }

    /** 荣誉榜
     * @return array
     */
    public function actionHonour()
    {
        $periodId = Yii::$app->request->get('id');
        $period = CurrentPeriod::findOne($periodId);
        $detail = [];
        if ($period) {
            //沙发君
            $first = PeriodBuylistDistribution::findByTableId($period['table_id'])->select('user_id')->where(['period_id'=>$periodId])->orderBy('buy_time asc')->limit('1')->asArray()->one();
            $detail['first_userid'] = $first['user_id'];
            //土豪君
            $max = PeriodBuylistDistribution::findByTableId($period['table_id'])->select('sum(buy_num) as sum,user_id')->where(['period_id'=>$periodId])->groupBy('user_id')->orderBy('sum desc')->limit('1')->asArray()->one();
            $detail['buynum'] = $max['sum'];
            $detail['rich_userid'] = $max['user_id'];
            //包尾君
            $detail['end_userid'] = 0;
        } else {
            $period = Period::findOne($periodId);

            if ($period) {
                $honor = Honour::findOne(['period'=>$periodId]);
                if ($honor && $honor['end_userid']) {
                    //沙发君
                    $detail['first_userid'] = $honor['first_userid'];
                    //土豪君
                    $detail['buynum'] = $honor['buynum'];
                    $detail['rich_userid'] = $honor['rich_userid'];
                    //包尾君
                    $detail['end_userid'] = $honor['end_userid'];
                } else {
                    //沙发君
                    $first = PeriodBuylistDistribution::findByTableId($period['table_id'])->select('user_id')->where(['period_id'=>$periodId])->orderBy('buy_time asc')->limit('1')->asArray()->one();
                    $detail['first_userid'] = $first['user_id'];
                    //土豪君
                    $max = PeriodBuylistDistribution::findByTableId($period['table_id'])->select('sum(buy_num) as sum,user_id')->where(['period_id'=>$periodId])->groupBy('user_id')->orderBy('sum desc')->limit('1')->asArray()->one();
                    $detail['buynum'] = $max['sum'];
                    $detail['rich_userid'] = $max['user_id'];
                    //包尾君
                    $end = PeriodBuylistDistribution::findByTableId($period['table_id'])->select('user_id')->where(['period_id'=>$periodId])->orderBy('buy_time desc')->limit('1')->asArray()->one();
                    $detail['end_userid'] = $end['user_id'];

                    if (!$honor) {
                        $honor = new Honour();
                    }
                    if ($detail['rich_userid'] && $detail['buynum'] && $detail['first_userid'] && $detail['end_userid']) {
                        $honor->period = $periodId;
                        $honor->rich_userid = $detail['rich_userid'];
                        $honor->buynum = $detail['buynum'];
                        $honor->first_userid = $detail['first_userid'];
                        $honor->end_userid = $detail['end_userid'];
                        $honor->created_at = (int)$period['end_time'];
                        $honor->last_modify = time();
                        $honor->save(false);
                    }

                }
            }

        }

        $return = [
            'richest' => '',
            'rich_user_id' => '',
            'rich_avatar' => '',
            'rich_home_id' => '',
            'num' => '',
            'first' => '',
            'first_user_id' => '',
            'first_home_id' => '',
            'first_avatar' => '',
            'end_user_id' => '',
            'end_home_id' => '',
            'end' => '',
            'end_avatar' => '',
        ];
        if (!$detail) {
            return $return;
        }

        if ($detail['rich_userid']) {
            $user = User::baseInfo($detail['rich_userid']);
            $return['richest'] = $user['username'];
            $return['rich_user_id'] = $user['id'];
            $return['rich_avatar'] = $user['avatar'];
            $return['rich_home_id'] = $user['home_id'];
            $return['num'] = $detail['buynum'];
        }

        if ($detail['first_userid']) {
            $user = User::baseInfo($detail['first_userid']);
            $return['first'] = $user['username'];
            $return['first_user_id'] = $user['id'];
            $return['first_home_id'] = $user['home_id'];
            $return['first_avatar'] = $user['avatar'];
        }
        if ($detail['end_userid']) {
            $user = User::baseInfo($detail['end_userid']);
            $return['end_user_id'] = $user['id'];
            $return['end_home_id'] = $user['home_id'];
            $return['end'] = $user['username'];
            $return['end_avatar'] = $user['avatar'];
        }

        return $return;
    }

    /** 荣誉榜介绍
     * @return array
     */
    public function actionHonourDesc()
    {
        $list = HonourDesc::find()->where(['from'=>$this->from])->asArray()->all();
        foreach($list as &$one){
            $one['icon'] = Image::getActiveInfoUrl($one['icon'], 'org');
        }

        $desc = '获得称号的相应奖励规则即将推出，敬请期待。';
        return ['list'=>$list, 'desc'=>$desc];
    }

    /**
     * 猴年马月信息
     */
    public function actionHnmyInfo()
    {
        $return = Coupon::hnmyMoneyInfo($this->userId, $this->userInfo['home_id']);

        return $return;
    }


    //京东E卡活动
    public function actionActivityJd(){

        //活动时间
        $config = Config::getValueByKey('jdcardactionconfig');
        $end_time=$config['endtime'];
        $start_time=$config['starttime'];
        $status=$config['status'];
        //活动判断时间
        $time=time();
        if($time>$end_time || $time<$start_time || $status==0)
        {
            return ['code'=>202, 'msg'=>'活动已结束'];
        }
        $data=['year'=>date('Y',$end_time),'month'=>date('m',$end_time),'day'=>date('d',$end_time),'hour'=>date('H',$end_time),'minute'=>date('i',$end_time)];
        //获取用户id
       $user_id = $this->userId;
       // $user_id=358;
        if(!$user_id)
        {
            $data['code']=201;
            $data['msg']='用户不存在';
            return $data;
        }
        $homeid=$this->userInfo->home_id;
       // $homeid=104;
        //查询所有京东卡商品
        /*****查询普通商品*********/
        $jdproductlist= Product::find()->select('id')->where(['delivery_id'=>8])->asArray()->all();
        foreach($jdproductlist as $row)
        {
            $jdid_list[]=$row['id'];               //所有京东商品
        }
        //查询最后一次获取的期数id;
        $activtyjd = ActivityJd::find()->where(['user_id'=>$user_id])->one();
        $where=['user_id'=>$user_id,'product_id'=>$jdid_list];
        $Periodwhere=[];
        $orderwhere=[];
        //如果存在
        if($activtyjd)
        {
            $period_id=$activtyjd->period_id;        //最后查询的期数id

            if($period_id >0){
                $Periodwhere= ['>', 'id', $period_id];
                $orderwhere= ['>', 'period_id', $period_id];
            }else{
                $Periodwhere=['>','(LEFT(exciting_time,10))',$start_time];
                $orderwhere=['>','(LEFT(create_time,10))',$start_time];
            }
        }else
        {
            $Periodwhere=['>','(LEFT(exciting_time,10))',$start_time];
            $orderwhere=['>','(LEFT(create_time,10))',$start_time];

        }
        $endwhere=['<','(LEFT(exciting_time,10))',$end_time];
        $orderendwhere=['<','(LEFT(create_time,10))',$end_time];
        //查询时间段内的已开奖的期数
        $Period_arr=Period::find()->select(['id'])->where($Periodwhere)->andWhere(['product_id'=>$jdid_list])->andWhere($endwhere)->andWhere(['>','user_id',0])->orderBy('id DESC')->asArray()->all();
        $Period_arr = array_column($Period_arr, 'id');
        $yselect=['id','period_id','product_id'];         //期号,
        $s_money=0;       //总金额
        $yperiod_ids=[];
        //查询已中奖的 用户商品期数
        // 查询用户中奖的期数
        $ylist = Order::find()->select($yselect)->where($where)->andwhere($orderwhere)->andwhere($orderendwhere)->orderBy('id DESC')->asArray()->all();

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
                $s_money+=$red;   //中奖总的参与价格
                $yperiod_ids[]=$row['period_id'];
            }
        }


        //查询所有购买的未中奖的
//        foreach ($newyperiods as $key=>$row2)
//        {
//            foreach ($yperiod_ids as $row3)
//            {
//                    if($row2==$row3)
//                    {
//                       unset($newyperiods[$key]);
//                    }
//            }
//        }
        $newyperiods=array_diff($Period_arr,$yperiod_ids);
        $newyperiods= array_values($newyperiods);
        $nselect=['sum(nums) as nums'];
        $qwhere=['period_id'=>$newyperiods,'user_id'=>$this->userId,'product_id'=>$jdid_list];
        $nlist = PaymentOrderItemDistribution::findByTableId($homeid)->select($nselect)->where($qwhere)->asArray()->one();

        $ymoney=$s_money;       //已中奖金额
        $s_money += $nlist['nums'];
            if($nlist['nums'] >0 || $ymoney>0 ){
        $jdactivelog=new ActivityJdMlog();
        $jdactivelog->y_money=$ymoney;
        $jdactivelog->n_money=$nlist['nums']?$nlist['nums']:0;
        $jdactivelog->start_period=$activtyjd?$activtyjd->period_id:0;
        $jdactivelog->end_period=$Period_arr?$Period_arr[0]:$activtyjd->period_id;
        $jdactivelog->uptime=time();
        $jdactivelog->user_id=$this->userId;
        $jdactivelog->save();
            }
        if($activtyjd)
        {
            $return_money =$activtyjd->money+$s_money;
            $activtyjd->money=$return_money;
            $activtyjd->up_time=time();
            if($Period_arr)$activtyjd->period_id =$Period_arr[0];          //pk最大的购买记录id

            $rs=$activtyjd->save();
        }
        else
        {
            $return_money =$s_money;
            $activtyjdModel=new ActivityJd();
            $activtyjdModel->user_id=$user_id;
            $activtyjdModel->money=$s_money;
            $activtyjdModel->up_time=time();

            if($Period_arr)$activtyjdModel->period_id =$Period_arr[0];          //pk最大的购买记录id

            $rs=$activtyjdModel->save();

        }

        if($rs){
            $data['code']=200;
            $data['money']=$return_money;

            return $data;
        }else{
            return ['code'=>202, 'msg'=>'网络超时'];
        }

    }


    //领取京东E卡送红包活动
    public function actionGetJdred(){

        $request = Yii::$app->request;
        $red_id = $request->get('red_id');
        $user_id = $this->userId;
        $config = Config::getValueByKey('jdcardactionconfig');
        $end_time=$config['endtime'];
        $start_time=$config['starttime'];
        $status=$config['status'];
        //活动判断时间
        $time=time();
        if($time>$end_time || $time<$start_time || $status==0)
        {
            return ['code'=>201, 'message'=>'活动已结束'];
        }

        if(!$user_id)
        {
            return ['code'=>201, 'message'=>'未登录'];
        }
        $red_arr=['54','55','56','57'];

        //查询红包是否存在
        if(!in_array($red_id,$red_arr)){
            return ['code'=>201, 'message'=>'红包不存在'];
        }
        $packet=Packet::getInfo($red_id);
        $red=json_decode($packet['content'],1);

        $coupon_id=array_keys($red)[0]; //折扣劵id
        $coupon=ModelCoupon::findOne($coupon_id);  //折扣卷详情

        $coupon_amount=array_values(json_decode($coupon->amount,1))[0];            //折扣卷金额

        if(!$packet)
        {
            return ['code'=>201, 'message'=>'红包不存在'];
        }
        $activtyjd = ActivityJd::find()->where(['user_id'=>$user_id])->one();     //用户活动获取总金额
        if($activtyjd->money<$coupon_amount)
        {
            return ['code'=>201, 'message'=>'您的可兑换红包金额不足！'];
        }
        $trans = \Yii::$app->db->beginTransaction();
        try{
            //扣减余额
            $old_money=$activtyjd->money;
            $remain=$activtyjd->money-$coupon_amount; //已扣减余额
            $activtyjd->money=$remain;
            $activtyjd->up_time=time();
            $rs1= $activtyjd->save(false);
            //记录
            $log= new ActivityJdLog();
            $log->red_id=$packet['id'];
            $log->user_id=$user_id;
            $log->remain=array_values($red)[0];
            $log->add_time=time();
            $log->old_money=$old_money;
            $rs2= $log->save();
            //送红包
            $source='京东E卡送红包活动';
            $rs3= Coupon::receivePacket($packet['id'],$user_id,$source);
            $pid = $rs3['data']['pid'];
            $info = Coupon::openPacket($pid,$user_id);

            if($rs1 && $rs2){
                if($info['code']==0)
                {
                    $trans->commit();
                    return ['code' => 200,'balance'=>$remain, 'message' =>'恭喜您兑换成功！'];
                }
            }
            $trans->rollBack();
            return ['code' => 201, 'message' =>'网络错误'];
        }catch (\Exception $e) {
            $trans->rollBack();
            return ['code' => 201, 'message' => '网络错误'];
        }

    }

}