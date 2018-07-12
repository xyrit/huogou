<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/3/8
 * Time: 上午9:45
 */
namespace app\commands;

use app\helpers\Message;
use app\helpers\MyRedis;
use app\models\ActOrder;
use app\models\FreeCurrentPeriod;
use app\models\FreePeriod;
use app\models\FreePeriodBuylistDistribution;
use app\models\FreeProduct;
use app\services\FreeBuy;
use app\services\User;
use yii\console\Controller;

class FreeController extends Controller
{

    public function actionLottery()
    {
        set_time_limit(0);

        $now = date('YmdH');

        //开奖
        $periodList  = FreeCurrentPeriod::find()->where(['<=','end_time',time()])->asArray()->all();
        foreach($periodList as $period) {
            $endTime = $period['end_time'];
            if ($now == date('YmdH', $endTime)) {
                $product = FreeProduct::find()->where(['id'=>$period['product_id']])->asArray()->one();
                $this->lottery($product);
            }
        }

    }

    private function lottery($product)
    {
        $currentPeriod = FreeCurrentPeriod::find()->where(['product_id' => $product['id']])->asArray()->one();
        if (!$currentPeriod) {
            return;
        }

        echo PHP_EOL . "========================================" . PHP_EOL;
        echo '商品ID' . $product['id'] . '第' . $currentPeriod['period_number'] . '期开奖---start'.'  '.date('Y-m-d H:i:s');
        echo PHP_EOL . "========================================" . PHP_EOL;

        $endTime = $currentPeriod['end_time'];
        $salesNum = $currentPeriod['sales_num'] > 0 ?  $currentPeriod['sales_num'] : $endTime;
        $luckyCode = intval(date('YmdH',$endTime)) % $salesNum + 10000001;

        //查找获奖者
        $tableId = $currentPeriod['table_id'];
        $periodBuylist = FreePeriodBuylistDistribution::findByTableId($tableId)
            ->where(['period_id' => $currentPeriod['id'], 'code' => $luckyCode])
            ->asArray()
            ->one();

        if ($periodBuylist) {
            $userId = $periodBuylist['user_id'];
            $ip = $periodBuylist['ip'];

            $time = time();
            //新增活动订单
            $saveOrder = ActOrder::add($userId, ActOrder::TYPE_FREE, $currentPeriod['id'], $product['name'], $product['picture']);

            $period = new FreePeriod();
            $period->id = $currentPeriod['id'];
            $period->table_id = $currentPeriod['table_id'];
            $period->product_id = $product['id'];
            $period->period_number = $currentPeriod['period_number'];
            $period->start_time = $currentPeriod['start_time'];
            $period->end_time = $currentPeriod['end_time'];
            $period->lucky_code = $luckyCode;
            $period->user_id = $userId;
            $period->sales_num = $currentPeriod['sales_num'];
            $period->ip = $ip;
            $period->exciting_time = $time;
            $savePeriod = $period->save(false);
            if ($savePeriod && $saveOrder) {
                FreeCurrentPeriod::deleteAll(['id' => $currentPeriod['id']]);
                $redis = new MyRedis();
                $redis->del(FreeBuy::PERIOD_CODE_KEY . $currentPeriod['id']);

                // 用户中奖通知
                $userInfo = User::baseInfo($userId);
                Message::send(40, $userId, [
                    'nickname'=>$userInfo['username'],
                    'phone'=>User::privatePhone($userInfo['phone']),
                    'activeName'=>'0元伙购',
                    'goodsName'=>$product['name'],
                    'time'=>'[time]',
                ]);
            }
        } else {
            //没有获奖者,结束时间延迟一小时
            FreeCurrentPeriod::updateAll(['end_time' => $currentPeriod['end_time'] + 3600], ['id' => $currentPeriod['id']]);
            echo PHP_EOL . "========================================" . PHP_EOL;
            echo '商品ID' . $product['id'] . '第' . $currentPeriod['period_number'] . '没有获奖者,结束时间延迟一小时'.'  '.date('Y-m-d H:i:s');
            echo PHP_EOL . "========================================" . PHP_EOL;
        }

        echo PHP_EOL . "========================================" . PHP_EOL;
        echo '商品ID' . $product['id'] . '第' . $currentPeriod['period_number'] . '期开奖---end'.'  '.date('Y-m-d H:i:s');
        echo PHP_EOL . "========================================" . PHP_EOL;

        //写入下一期
        if ($product['start_type'] == 0) {
            $start = strtotime(date('Y-m-d H:00:00',time()));
            $end = intval($start) + $product['after_end'] * 3600;
            $this->startNew($start, $end, $product);
        } else {
            $startType = $product['start_type'];
            $startTime = $product['start_time'];
            $afterEnd = $product['after_end'];

            if ($startType == 1) { //每天
                preg_match('/([0-9]{2}):[0-9]{2}:[0-9]{2}/', $startTime, $matches);
                if (isset($matches[1])) {
                    $hour = $matches[1];

                    $nowTime = strtotime(date('Y-m-d H:00:00'));
                    $thisTime = strtotime(date('Y-m-d '.$hour.':00:00'));

                    if ($nowTime>$thisTime) {
                        $start = strtotime('+1 day',$thisTime);
                    } else {
                        $start = $thisTime;
                    }

                    $end = $start + 3600 * $afterEnd;
                }
            } elseif ($startType == 2) { //每周
                preg_match('/([0-6])\s([0-9]{2}):[0-9]{2}:[0-9]{2}/', $startTime, $matches);
                if (isset($matches[1]) && isset($matches[2])) {
                    $week = $matches[1];
                    $hour = $matches[2];

                    $weeks = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                    $nowTime = strtotime(date('Y-m-d H:00:00'));
                    $thisTime = strtotime(date('Y-m-d '.$hour.':00:00', strtotime('this '.$weeks[$week])));
                    if ($nowTime>$thisTime) {
                        $startTime = date('Y-m-d ' . $hour . ':00:00', strtotime('next '.$weeks[$week]));
                        $start = strtotime($startTime);
                    }else {
                        $start = $thisTime;
                    }
                    $end = $start + 3600 * $afterEnd;

                }
            } elseif ($startType == 3) { //每月
                preg_match('/([1-28])\s([0-9]{2}):[0-9]{2}:[0-9]{2}/', $startTime, $matches);
                if (isset($matches[1]) && isset($matches[2])) {
                    $day = $matches[1];
                    $hour = $matches[2];

                    $nowTime = strtotime(date('Y-m-d H:00:00'));
                    $thisTime = strtotime(date('Y-m-'.$day.' '.$hour.':00:00'));
                    if ($nowTime>$thisTime) {
                        $startTime = date('Y-m-d ' . $hour . ':00:00', strtotime('next month', $thisTime));
                        $start = strtotime($startTime);
                    }else {
                        $start = $thisTime;
                    }
                    $end = $start + 3600 * $afterEnd;

                }
            }

            $this->startNew($start, $end, $product);
        }


    }

    private function startNew($start, $end, $product)
    {
        //不是上架状态 不进行下一期
        if ($product['marketable'] != 1) {
            return;
        }
        $currentPeriod = FreeCurrentPeriod::find()->where(['product_id' => $product['id']])->asArray()->one();
        if ($currentPeriod) {
            echo PHP_EOL . "========================================" . PHP_EOL;
            echo '商品ID' . $product['id'] . '第' . $currentPeriod['period_number'] . '期还未结束,不能进行新一期'.'  '.date('Y-m-d H:i:s');
            echo PHP_EOL . "========================================" . PHP_EOL;
            return;
        }
        $oldPeriod = FreePeriod::find()->where(['product_id' => $product['id']])->orderBy('id desc')->asArray()->one();

        if ($oldPeriod) {
            $periodNumber = $oldPeriod['period_number'] + 1;
        } else {
            $periodNumber = 1;
        }
        //达到总期数 进行下架
        if ($periodNumber > $product['total_period']) {
            FreeProduct::updateAll(['marketable'=>0],['id'=>$product['id']]);
            echo PHP_EOL . "========================================" . PHP_EOL;
            echo '商品ID' . $product['id'] . '到达总期数'.$product['total_period'].',进行下架'.'  '.date('Y-m-d H:i:s');
            echo PHP_EOL . "========================================" . PHP_EOL;
            return;
        }

        echo PHP_EOL . "========================================" . PHP_EOL;
        echo '商品ID' . $product['id'] . '第' . $periodNumber . '期开始---start'.'  '.date('Y-m-d H:i:s');
        echo PHP_EOL . "========================================" . PHP_EOL;

        $model = new FreeCurrentPeriod();
        $model->table_id = FreeCurrentPeriod::generateTableId();
        $model->product_id = $product['id'];
        $model->period_number = $periodNumber;
        $model->sales_num = 0;
        $model->start_time = $start;
        $model->end_time = $end;
        $save = $model->save();

        if ($save) {
            $periodId = $model->id;
            $redis = new MyRedis();
            $redis->set(FreeBuy::PERIOD_CODE_KEY . $periodId, FreeBuy::FIRST_CODE);
        }

        echo PHP_EOL . "========================================" . PHP_EOL;
        echo '商品ID' . $product['id'] . '第' . $periodNumber . '期开始---end'.'  '.date('Y-m-d H:i:s');
        echo PHP_EOL . "========================================" . PHP_EOL;
        return $save;
    }


}