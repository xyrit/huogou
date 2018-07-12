<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/26
 * Time: 下午8:09
 */
namespace app\commands;

use app\helpers\MyRedis;
use app\models\CurrentPeriod;
use app\models\LotteryComputeDistribution;
use app\models\OlympicSchedule;
use app\models\Period;
use app\services\Lottery;
use app\services\Olympic;
use app\services\Pay;
use yii;
use yii\console\Controller;
use app\models\Period as PeriodModel;
use app\models\PeriodBuylistDistribution;
use app\models\Product as ProductModel;

class LotteryController extends Controller
{

	/**
	 * 开始新一期
	 */
    public function actionNewPeriod($pid)
    {
		$period = CurrentPeriod::find()->where(['id'=>$pid])->asArray()->one();
		if (!$period) {
			return false;
		}
		$tableId = $period['table_id'];
		$lastTime = PeriodBuylistDistribution::findByTableId($tableId)
			->select('buy_time')
			->where(['period_id'=>$period['id']])
			->orderBy('buy_time desc')
			->one();

		$time = $lastTime['buy_time'];

		echo PHP_EOL."========================================".PHP_EOL;
		$payService = new Pay('');
		$payService->newPeriod($period, $time);
		echo '开始新的一期,periodId:'.$period['id'];
		echo PHP_EOL."========================================".PHP_EOL;
    }

	/**
	 *  开奖
	 */
	public function actionWin()
	{
		$nowTime = strtotime(date('Y-m-d H:i:00'));

		$type = \app\services\Period::dayTypeByDrawTime($nowTime);

		if ($type == 'day') {
			if (($nowTime - (\app\services\Period::COUNT_DOWN_TIME_DAY + \app\services\Period::COUNT_DOWN_TIME_DAY_WAIT)) % \app\services\Period::COUNT_DOWN_TIME_DAY != 0) {
				return;
			}
			$end = $nowTime - \app\services\Period::COUNT_DOWN_TIME_DAY_WAIT;
			$start = $end - \app\services\Period::COUNT_DOWN_TIME_DAY;
		} elseif ($type == 'night') {
			if (($nowTime - (\app\services\Period::COUNT_DOWN_TIME_NIGHT + \app\services\Period::COUNT_DOWN_TIME_NIGHT_WAIT)) % \app\services\Period::COUNT_DOWN_TIME_NIGHT != 0) {
				return;
			}
			$end = $nowTime - \app\services\Period::COUNT_DOWN_TIME_NIGHT_WAIT;
			$start = $end - \app\services\Period::COUNT_DOWN_TIME_NIGHT;
		} else {
			return;
		}

		set_time_limit(0);

		$logStr = "type:" . $type . " ---- 开奖开始!";
		Lottery::printLog($logStr, true);

		$circleLotteryNum = 2;
		$canUseNone = false;
		for($i=0;$i<$circleLotteryNum;$i++) {
			$needLottery = PeriodModel::find()
				->where(['exciting_time' => 0, 'lucky_code' => 0, 'user_id' => 0])
				->andWhere(['>=', 'end_time', $start])
				->andWhere(['<', 'end_time', $end])
				->orderBy('end_time asc')
				->asArray()
				->all();

			if ($i==$circleLotteryNum-1) {
				$canUseNone = true;
			}
			$lotterySucessNum = 0;
			if ($needLottery) {
				$needLotteryNum = count($needLottery);
				foreach ($needLottery as $key => $value) {
					$lotteryResult = Lottery::draw($value, $canUseNone, true);
					if ($lotteryResult) {
						$lotterySucessNum ++;
					}
				}
				if ($lotterySucessNum<$needLotteryNum) {
					continue;
				} else {
					break;
				}
			} else {
				$logStr = date('Y-m-d H:i:s', $start)."-".date('Y-m-d H:i:s', $end)."没有满员的期数!";
				Lottery::printLog($logStr, true);
				break;
			}
		}

		$logStr = "type:" . $type . " ---- 开奖结束!";
		Lottery::printLog($logStr, true);
	}


	/**
	 * 开奖半小时之前的
	 */
	public function actionDraw()
	{
		set_time_limit(0);
		$end = time() - 1800;

		$logStr = "开奖开始!";
		Lottery::printLog($logStr, true);
		$circleLotteryNum = 2;
		$canUseNone = false;
		for($i=0;$i<$circleLotteryNum;$i++) {
			$needLottery = PeriodModel::find()
				->where(['exciting_time' => 0, 'lucky_code' => 0, 'user_id' => 0])
				->andWhere(['<', 'end_time', $end])
				->orderBy('end_time asc')
				->asArray()
				->all();
			if ($i==$circleLotteryNum-1) {
				$canUseNone = true;
			}
			$lotterySucessNum = 0;
			if ($needLottery) {
				$needLotteryNum = count($needLottery);
				foreach ($needLottery as $key => $value) {
					$lotteryResult = Lottery::draw($value, $canUseNone, true);
					if ($lotteryResult) {
						$lotterySucessNum ++;
					}
				}
				if ($lotterySucessNum<$needLotteryNum) {
					continue;
				} else {
					break;
				}
			} else {
				$logStr = date('Y-m-d H:i:s', $end)."之前没有满员的期数!";
				Lottery::printLog($logStr, true);
				break;
			}
		}

		$logStr = "开奖结束!";
		Lottery::printLog($logStr, true);
	}


	/**
	 *  期数码从购买记录恢复到redis
	 */
	public function actionPeriodCodeBackRedis()
	{
		set_time_limit(0);
		ini_set('memory_limit','1G');
		$currentPeriod = CurrentPeriod::find()->all();
		$redis = new MyRedis();
		foreach($currentPeriod as $period) {
			$periodId = $period['id'];
			$tableId = $period['table_id'];
			$periodBuylist = PeriodBuylistDistribution::findByTableId($tableId)
				->where(['period_id'=>$periodId])->all();
			$codes = [];
			foreach($periodBuylist as $buylist) {
				$code = explode(',', $buylist['codes']);
				$codes = array_merge($codes, $code);
			}

			$codeKey = \app\services\Pay::PERIOD_ALL_CODE_KEY.$periodId;
			if ($codes) {
				$salesKey = \app\services\Pay::PERIOD_SALED_KEY.$periodId;
				$redis->sset($salesKey, $codes);
			}
			if ($redis->slen($codeKey)+count($codes)==$period['price']) {
				echo $period['id'].'_'.$period['price'].PHP_EOL;
				continue;
			}
			echo 'periodId:'.$periodId.PHP_EOL;
			$start = 10000001;
			$end = $start + $period['price'];
			$pipe = $redis->pipeline();
			for ($i=10000001;$i<$end;$i++) {
				if (!in_array($i, $codes)) {
					$pipe->sadd($codeKey,$i);
				}
				$num = $i - $start + 1;
				if (($num > 0 && $num % 10000 == 0) || $i == ($end-1)) {
					$pipe->exec();
					if($i!=($end-1)) {
						$pipe = $redis->pipeline();
					}
				}
			}

		}


	}

	/**
	 * 奥运会 最大期数设置
	 */
	public function actionOlympicSchedule()
	{
		$date = date('Ymd');
		if ($date < Olympic::$timeRand['start'] || $date > Olympic::$timeRand['end']) {
			return false;
		}
		$productIds = array_values(Olympic::$medalProducts);
		$todayNum = OlympicSchedule::find()->where(['<=', 'date', $date])->count();
		if ($todayNum) {
			foreach($productIds as $productId) {
				$period = CurrentPeriod::find()->where(['product_id' => $productId])->one();
				if (!$period) {
					$period = PeriodModel::find()->where(['product_id' => $productId])->orderBy('id desc')->one();
				}
				$prductModel = ProductModel::findOne($productId);
				if (!$period) {
					$periodNumber = 0;
					static::initCurrentPeriodInfo($prductModel);
				} else {
					$periodNumber = $period['period_number'];
					static::initCurrentPeriodInfo($prductModel, $periodNumber + 1);
				}
				$update = ProductModel::updateAll(['marketable' => 1, 'store' => $todayNum], ['id' => $productId]);
				echo 'productId:' . $productId . ' update:' . print_r($update, true) .  PHP_EOL;
			}
		}
	}

	/**
	 * 初始化期数
	 * @param $productModel
	 * @param int $period_numer
	 */
	private static function initCurrentPeriodInfo($productModel, $period_numer = 1)
	{
		$currentPeriod = CurrentPeriod::findOne(['product_id' => $productModel->id]);
		if (!$currentPeriod) {
			$currentPeriod = new CurrentPeriod();
			$currentPeriod->table_id = mt_rand(100, 109);
			$currentPeriod->product_id = $productModel->id;
			$currentPeriod->price = $productModel->price;
			$currentPeriod->limit_num = $productModel->limit_num;
			$currentPeriod->buy_unit = $productModel->buy_unit;
			$currentPeriod->period_number = $period_numer;
			$currentPeriod->sales_num = 0;
			$currentPeriod->progress = 0;
			$currentPeriod->left_num = $productModel->price;
			$currentPeriod->start_time = microtime(true);
			$currentPeriod->save(false);

			$currentPeriod->period_no = \app\services\Period::getPeriodNo(yii\helpers\ArrayHelper::toArray($currentPeriod));
			$currentPeriod->save(false);

			$periodId = $currentPeriod->id;
			static::initCodes(yii\helpers\ArrayHelper::toArray($productModel), $periodId);

		}
	}

	private static function initCodes($product,$periodId)
	{

		$redis = new MyRedis();
		$codeKey = Pay::PERIOD_ALL_CODE_KEY.$periodId;

		$start = 10000001;
		$end = $start + $product['price'];
		$pipe = $redis->pipeline();
		for ($i=$start;$i<$end;$i++) {
			$pipe->sadd($codeKey,$i);
			$num = $i - $start + 1;
			if (($num > 0 && $num % 10000 == 0) || $i == ($end-1)) {
				$pipe->exec();
				if($i!=($end-1)) {
					$pipe = $redis->pipeline();
				}
			}
		}

		if ($redis->slen($codeKey) != $product['price']) {
			$redis->del($codeKey);
			static::initCodes($product,$periodId);
		}
	}


	public function actionMoveComputeToNew()
	{
		set_time_limit(0);
		ini_set('memory_limit', '900M');
		$db = Yii::$app->db;
		$redis = new MyRedis();
		$key = 'MOVE_COMPUTE_TO_NEW_MAX_PERIOD_ID';
		$maxPeriodId = $redis->get($key);

		$sql = "select * from lottery_compute where period_id > '{$maxPeriodId}' order by period_id asc";
		$query = $db->createCommand($sql)->query();
		while($row = $query->read()) {
			$periodId = $row['period_id'];
			$computeData = $row['data'];
			$expect = $row['expect'];
			$shishiNum = $row['shishi_num'];
			$period = Period::findOne($periodId);
			if (!$period) {
				continue;
			}
			$ld = new LotteryComputeDistribution($period['table_id']);
			$ld->period_id = $periodId;
			$ld->data = $computeData;
			$ld->expect = $expect;
			$ld->shishi_num = $shishiNum;
			$l = $ld->save(false);
			if ($l) {
				echo 'period_id : ' . $periodId . PHP_EOL;
				$redis->set($key, $periodId);
			}
		}
	}



}