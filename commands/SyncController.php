<?php

namespace app\commands;

use app\helpers\MyRedis;
use app\models\PaymentOrderDistribution;
use app\models\PaymentOrderItemDistribution;
use yii;
use app\models\PeriodBuylistDistribution;
use yii\console\Controller;

/**
* 测试
*/
class SyncController extends Controller
{
	
	public function actionIndex(){
		$redis = new MyRedis();
		$orders = $redis->hget('ORDER_LIST','all');

		echo date('Y-m-d H:i:s',time()).PHP_EOL;
		echo "============================================================".PHP_EOL;
		if (!$orders) {
			echo "无".PHP_EOL;
			return false;
		}

		$mysqlOrders = PaymentOrderDistribution::fetchAllOrdersByTimes((time()-7200), '>=', '10000');
		$o = array();
		foreach ($mysqlOrders as $key => $value) {
			$o[] = $value['payment_order_id'];
		}

		$l = array();
		foreach ($orders as $key => $value) {
			$_v = json_decode($value,true);
			if (!in_array($key,$o) && $_v['status'] != 0 && $_v['buy_time'] >= (time()-7200)) {
				$l[] = $key;
			}
		}

		// print_r($l);
		$db = \Yii::$app->db;
		$orderField = ['id','user_id','status','payment','bank','money','point','total','user_point','ip','source','create_time','buy_time','recharge_orderid'];
		$orderValue = array();
		foreach ($l as $key => $value) {
			echo $value.PHP_EOL;
			echo "============================================================".PHP_EOL;
			$v = json_decode($orders[$value],true);
			$orderValue[] = [$v['id'],$v['user_id'],$v['status'],$v['payment'],$v['bank'],$v['money'],$v['point'],$v['total'],$v['user_point'],$v['ip'],$v['source'],$v['create_time'],$v['buy_time'],$v['recharge_orderid']];

			//订单id
			$orderTableId = PaymentOrderDistribution::getTableIdByOrderId($v['id']);
			//同步订单信息
			$orderSave = new PaymentOrderDistribution($orderTableId);
			$orderSave->id = $v['id'];
			$orderSave->user_id = $v['user_id'];
			$orderSave->status = $v['status'];
			$orderSave->payment = $v['payment'];
			$orderSave->bank = $v['bank'];
			$orderSave->money = $v['money'];
			$orderSave->point = $v['point'];
			$orderSave->total = $v['total'];
			$orderSave->user_point = $v['point'];
			$orderSave->ip = $v['ip'];
			$orderSave->source = $v['source'];
			$orderSave->create_time = $v['create_time'];
			$orderSave->buy_time = $v['buy_time'];
			$orderSave->recharge_orderid = $v['recharge_orderid'];
			$result = $orderSave->save();
			//订单详细信息
			$orderItems = $redis->hget('ORDER_ITEMS_'.$v['id'],'all');
			$orderItemField = ['payment_order_id','product_id','period_id','period_number','user_id','post_nums','nums','codes','item_buy_time','source'];
			$orderItemValue = [];

			foreach ($orderItems as $k => $uv) { 
				$_v = json_decode($uv,true);
				$orderItemUserId = isset($_v['user_id']) ? $_v['user_id'] : 0;
				$orderItemSource = isset($_v['source']) ? $_v['source'] : 0;
				$orderItemValue[] = [$_v['payment_order_id'],$_v['product_id'],$_v['period_id'],$_v['period_number'],$orderItemUserId,$_v['post_nums'],$_v['nums'],$_v['codes'],$_v['item_buy_time'],$orderItemSource];
			}
			$orderItem = new PaymentOrderItemDistribution($orderTableId);
			$itemsResult = $db->createCommand()->batchInsert($orderItem::tableName(),$orderItemField,$orderItemValue)->execute();

			//用户购买记录
			$userBuyInfo = $redis->hget('USER_BUY_LIST_'.$v['id'],'all');
			//同步期数购买
			foreach ($userBuyInfo as $pk => $pv) {
				$_pv = json_decode($pv,true);

				if ($_pv['count'] > 0) {
					$periodBuyList = new PeriodBuylistDistribution($_pv['table_id']);
					$periodBuyList->product_id = $_pv['product_id'];
					$periodBuyList->period_id = $_pv['period_id'];
					$periodBuyList->user_id = $v['user_id'];
					$periodBuyList->buy_num = $_pv['count'];
					$periodBuyList->codes = implode(',',$_pv['codes']);
					$periodBuyList->ip = $v['ip'];
					$periodBuyList->source = $v['source'];
					$periodBuyList->buy_time = $v['buy_time'];
					$periodResult = $periodBuyList->save(false);
					if (!$periodResult) {
						break;
					}
				}
			}
		}
		
	}
}