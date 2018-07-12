<?php

namespace app\controllers;

use app\helpers\MyRedis;
use app\models\PaymentOrderDistribution;
use app\models\PaymentOrderItemDistribution;
use yii;
use app\models\PeriodBuylistDistribution;
use yii\console\Controller;
use app\models\UserBuylistDistribution;

/**
* 测试
*/
class ManualsyncController extends BaseController
{
	
	public function actionIndex(){
		$orderId = Yii::$app->request->get('id');
		if (!$orderId) {
			return 0;
		}
		$redis = new MyRedis();
		$orderInfo = $redis->hget('ORDER_LIST',$orderId);
		echo $orderId;
		if (!$orderInfo) {
			return false;
		}
		print_r($orderInfo);
		echo "<br/>";
		echo "============================================================".PHP_EOL;

		// $db = \Yii::$app->db;
		// $db->createCommand("delete from payment_orders_102 where id = '102201604220729016616521'")->execute();
		// $db->createCommand("delete from payment_order_items_102 where payment_order_id = '102201604220729016616521'")->execute();

		$orderTableId = substr($orderId,0,3);
		$model = new PaymentOrderDistribution($orderTableId);
		// $orderTableId = $model::getTableId($orderId);
		$exist = $model::findByTableId($orderTableId)->where(['id'=>$orderId])->asArray()->one();
		if ($exist) {
			print_r($exist);
			exit;
		}

		$v = json_decode($orderInfo,true);
		if (!is_array($v)) {
			return false;
		}
		
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
		if (isset($v['deduction1']) && isset($v['coupon1'])) {
			$orderSave->deduction1 = $v['deduction1'];
			$orderSave->coupon1 = $v['coupon1'];
		}
		if (isset($v['deduction2']) && isset($v['coupon2'])) {
			$orderSave->deduction2 = $v['deduction2'];
			$orderSave->coupon2 = $v['coupon2'];
		}
		$result = $orderSave->save();

		$orderItems = $redis->hget('ORDER_ITEMS_'.$v['id'],'all');
		$orderItemField = ['payment_order_id','product_id','period_id','period_number','user_id','post_nums','nums','codes','item_buy_time','source'];
		$orderItemValue = [];

		foreach ($orderItems as $k => $uv) { 
			$_v = json_decode($uv,true);
			$orderItemUserId = isset($_v['user_id']) ? $_v['user_id'] : 0;
			$orderItemSource = isset($_v['source']) ? $_v['source'] : 0;
			$orderItemValue[] = [$_v['payment_order_id'],$_v['product_id'],$_v['period_id'],$_v['period_number'],$orderItemUserId,$_v['post_nums'],$_v['nums'],$_v['codes'],$_v['item_buy_time'],$orderItemSource];

			$homeId = substr($orderId,0,3).$v['user_id'];
			$hasBuy = UserBuylistDistribution::findByUserHomeId($homeId)->where(['user_id'=>$_v['user_id'],'period_id'=>$_v['period_id']])->asArray()->one();
			if (!$hasBuy) {
				$userBuylistModel = new UserBuylistDistribution($homeId);
				$userBuylistModel->user_id = $_v['user_id'];
				$userBuylistModel->product_id = $_v['product_id'];
				$userBuylistModel->period_id = $_v['period_id'];
				$userBuylistModel->buy_num = $_v['nums'];
				$userBuylistModel->buy_time = $_v['item_buy_time'];
				$userBuylistModel->save(false);
			}
		}
		$db = \Yii::$app->db;
		$orderItem = new PaymentOrderItemDistribution($orderTableId);
		$itemsResult = $db->createCommand()->batchInsert($orderItem::tableName(),$orderItemField,$orderItemValue)->execute();

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
		echo 1;
	}
}