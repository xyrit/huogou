<?php

namespace app\commands;

use app\helpers\DateFormat;
use app\models\ActOrder;
use app\models\ActRichLog;
use app\models\Packet;
use app\models\PeriodBuylistDistribution;
use app\models\RichSet;
use yii;
use yii\console\Controller;
use app\services\User;
use app\helpers\Message;

/**
 * 土豪榜
 */
class RichController extends Controller
{
    public function actionIndex()
    {
        $setList = RichSet::find()->where(['status'=>1])->all();
        foreach($setList as $val){
            $timeType = $val['time_type'];
            if($timeType == 0){ //时间段
                $start = $val['start_time'];
                $end = $val['end_time'];

                $now = time();
                if ($now>=$end) {
                    if ($now - $end <= 3600*24) {
                        $list = PeriodBuylistDistribution::getList(10, $start, $end + 3600*24, 100, $timeType);
                        foreach($list as $key => $value){
                            ActRichLog::addLog($value['user_id'], $value['total'], $timeType, $key+1, date('Y-m-d', $start).'--'.date('Y-m-d', $end));
                        }

                        $list = ActRichLog::find()->where(['type'=>0,'datetime'=>date('Y-m-d', $start).'--'.date('Y-m-d', $end)])->all();
                        $rewards = ActRichLog::rewards('richseasonconfig');
                        foreach($list as $key => $val){
                            $r = $rewards[$val['rank']];
                            if($r['type'] == 1){
                                $exist = ActOrder::find()->where(['act_obj_id'=>$val['id'], 'act_type'=>ActOrder::TYPE_SEASON_RICH])->one();
                                if(!$exist) {
                                    ActOrder::add($val['user_id'], ActOrder::TYPE_SEASON_RICH, $val['id'], $r['name'], $r['picture']);
                                    ActRichLog::updateAll(['status'=>1, 'last_modify'=>time()],['id'=>$val['id']]);
                                }
                            }
                        }
                    }
                }
            }
            if($timeType == 1){ //每天
                $start = strtotime(date('Y-m-d',strtotime('-1 day')));
                $end = strtotime(date('Y-m-d'));
                $e = ActRichLog::find()->where(['datetime'=>date('Y-m-d', $start)])->count(1);
                if($e == 0){
                    $list = PeriodBuylistDistribution::getList(10, $start, $end, 10, $timeType);
                    foreach($list as $key => $value){
                        ActRichLog::addLog($value['user_id'], $value['total'], $timeType, $key+1, date('Y-m-d', $start));
                    }
                }else{
                    echo 'day exist'.PHP_EOL;
                }

            }elseif($timeType == 2){ //每周

                list($start, $end) = DateFormat::rangeTime('lastWeek');

                if(date('Ymd', $end) == date('Ymd', time())){
                    $list = PeriodBuylistDistribution::getList(10, $start, $end, 10, $timeType);
                    foreach($list as $key => $value){
                        ActRichLog::addLog($value['user_id'], $value['total'], $timeType, $key+1, date('Y-m-d', $start).'--'.date('Y-m-d', $end-3600*24));
                    }

                    $list = ActRichLog::find()->where(['type'=>0,'datetime'=>date('Y-m-d', $start).'--'.date('Y-m-d', $end-3600*24)])->all();
                    $rewards = ActRichLog::rewards('richweekconfig');
                    foreach($list as $key => $val){
                        $r = $rewards[$val['rank']];
                        if($r['type'] == 1){
                            $exist = ActOrder::find()->where(['act_obj_id'=>$val['id'], 'act_type'=>ActOrder::TYPE_WEEK_RICH])->one();
                            if(!$exist) {
                                ActOrder::add($val['user_id'], ActOrder::TYPE_WEEK_RICH, $val['id'], $r['name'], $r['picture']);
                                ActRichLog::updateAll(['status'=>1, 'last_modify'=>time()],['id'=>$val['id']]);
                            }
                        }
                    }
                }
            }elseif($timeType == 3){ //每月

                list($start, $end) = DateFormat::rangeTime('lastMonth');

                if(date('Ymd', $end) == date('Ymd', time())){

                    $e = ActRichLog::find()->where(['datetime'=>date('Y-m', $start)])->count(1);
                    if($e == 0){
                        $list = PeriodBuylistDistribution::getList(10, $start, $end, 10, $timeType);
                        foreach($list as $key => $value){
                            ActRichLog::addLog($value['user_id'], $value['total'], $timeType, $key+1, date('Y-m', $start));
                        }

                        $list = ActRichLog::find()->where(['datetime'=>date('Y-m', $start)])->all();
                        $rewards = ActRichLog::rewards('richmonthconfig');
                        foreach($list as $key => $val){
                            $r = $rewards[$val['rank']];
                            if($r['type'] == 1){
                                $exist = ActOrder::find()->where(['act_obj_id'=>$val['id'], 'act_type'=>ActOrder::TYPE_MONTH_RICH])->one();
                                if(!$exist) {
                                    ActOrder::add($val['user_id'], ActOrder::TYPE_MONTH_RICH, $val['id'], $r['name'], $r['picture']);
                                    ActRichLog::updateAll(['status'=>1, 'last_modify'=>time()],['id'=>$val['id']]);
                                }
                            }
                        }
                    }else{
                        echo 'month exist'.PHP_EOL;
                    }
                }
            }
        }
    }

    public function actionSend()
    {
        $time = strtotime(date('Y-m-d', time()));
        $list = ActRichLog::find()->where('created_at >= '.$time)->asArray()->all();
        $seasonRewards = ActRichLog::rewards('richseasonconfig');
        $dayRewards = ActRichLog::rewards('richdayconfig');
        $weekRewards = ActRichLog::rewards('richweekconfig');
        $monthRewards = ActRichLog::rewards('richmonthconfig');

        foreach($list as $val){
            $userInfo = User::baseInfo($val['user_id']);

            $activeName = '';
            if ($val['type'] == 0) {
                $reward = $seasonRewards[$val['rank']];
                $activeName = '季土豪榜';
            }elseif($val['type'] == 1){
                $reward = $dayRewards[$val['rank']];
                $activeName = '每日土豪榜';
            }elseif( ['type'] == 2){
                $reward = $weekRewards[$val['rank']];
                $activeName = '周度土豪榜';
            }elseif( ['type'] == 3){
                $reward = $monthRewards[$val['rank']];
                $activeName = '月度土豪榜';
            }

            if($reward['type'] == 1){
                $name = $reward['name'];
                $id = 40;
            }elseif($reward['type'] == 2){
                $name = '伙购币'.$reward['name'];
                $id = 41;
            }elseif($reward['type'] == 3){
                $name = '返现单笔消费的'.$reward['name'].'%';
                $id = 41;
            }elseif($reward['type'] == 4){
                $packet = Packet::findOne($reward['name']);
                $name = $packet['name'];
                $id = 41;
            }

            Message::send($id, $val['user_id'], [
                'nickname'=>$userInfo['username'],
                'phone'=>User::privatePhone($userInfo['phone']),
                'activeName'=>$activeName,
                'goodsName'=>$name,
                'time'=>'[time]',
            ]);
        }
    }
}