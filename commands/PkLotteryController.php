<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/13
 * Time: 上午11:19
 */
namespace app\commands;

use app\helpers\FileHelper;
use app\helpers\MyRedis;
use app\models\PkCurrentPeriod;
use app\services\PkLottery;
use app\services\PkPay;
use yii\console\Controller;

class PkLotteryController extends Controller
{


    public function actionWin()
    {
        $sec = 0;
        $redis = new MyRedis();
        while (true) {
            $memoryLimit = FileHelper::computerFileSize(ini_get('memory_limit'));
            $maxRunMemory = $memoryLimit/5;
            if ($sec >= 10 || memory_get_usage(true) >= $maxRunMemory) {
                break;
            }
            $periodsEndTime = $redis->hget(PkLottery::PK_PRODUCT_LOTTERY_TIME_KEY, 'all');
            try {
                if (!$periodsEndTime) {
                    $logStr = "没有需要开奖的期数";
                    PkLottery::printLog($logStr);

                    $sec ++;
                    sleep(1);
                    continue;
                }
                foreach($periodsEndTime as $periodId => $periodEndTime) {
                    if ($periodEndTime <= time()) {

                        $sec ++;
                        //用户正在购买不进行开奖
                        $periodIdBuying = $redis->hget(PkPay::PK_PERIOD_BUYING_KEY . $periodId, 'all');
                        if ($periodIdBuying) {
                            $logStr = "periodId:" . $periodId . ' 用户正在购买';
                            PkLottery::printLog($logStr);
                            continue;
                        }
                        $periodInfo = PkCurrentPeriod::findOne($periodId);
                        if (!$periodInfo) {
                            $redis->hdel(PkLottery::PK_PRODUCT_LOTTERY_TIME_KEY, $periodId);
                            continue;
                        }
                        $lotteryResult = PkLottery::draw($periodInfo);
                        if ($lotteryResult) {
                            $redis->hdel(PkLottery::PK_PRODUCT_LOTTERY_TIME_KEY, $periodId);
                        }
                    }
                }
            } catch (\Exception $e) {
                $logStr = "PK场 ---- 开奖出错 file:" . $e->getFile() . ' line:' . $e->getLine() . ' message:' . $e->getMessage();
                PkLottery::printLog($logStr);
            }
        }


    }


    /**
     * 商品开奖时间信息写入redis
     */
    public function actionProductLotteryTimeBackRedis()
    {
        $periods = PkCurrentPeriod::find()->all();

        $redis = new MyRedis();
        foreach ($periods as $period) {
            $periodId = $period['id'];
            $endTime = $period['end_time'];
            $redis->hset(PkLottery::PK_PRODUCT_LOTTERY_TIME_KEY, [$periodId=>$endTime]);
            echo 'periodId:'.$periodId.PHP_EOL;
        }
    }


}