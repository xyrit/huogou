<?php

namespace app\commands;

use yii;
use yii\console\Controller;
use app\models\PaymentOrderDistribution;
use app\models\PaymentOrderItemDistribution;
use app\helpers\MyRedis;
use app\models\PeriodBuylistDistribution;

/**
* 数据同步
*/
class DatasyncController extends Controller
{
	const ORDER_LIST_KEY = 'ORDER_LIST';  //hash类型，order->orderinfo
	const ORDER_ITEMS_KEY = 'ORDER_ITEMS_';  //hash 订单详情 period_id->info _orderid
	const USER_BUY_LIST_KEY = 'USER_BUY_LIST_'; //用户购买记录

	public function actionSync(){
		echo PHP_EOL."========================停止同步===========================".PHP_EOL;
		return;

		$redis = new MyRedis();

		$time = date("Ymd",time());
		$keys = $redis->keys(self::USER_BUY_LIST_KEY.'*'.$time.'*');

		foreach ($keys as $key => $value) {
			$_order = explode('_',$value);
			$order = end($_order);
			$orderInfo = $redis->hget(self::ORDER_LIST_KEY,$order);
			$productInfo = json_decode($orderInfo,true);
			if ($productInfo['status'] == 0) {
				continue;
			}
			$orderId = $productInfo['id'];
			echo '订单号：'.$orderId;
			echo PHP_EOL."============================================================".PHP_EOL;
			$tableId = PaymentOrderDistribution::getTableIdByOrderId($orderId);
			$isexist = PaymentOrderDistribution::findByTableId($tableId)->where(['id'=>$orderId])->asArray()->one();
			if (!$isexist) {
				$orderItems = $redis->hget(self::ORDER_ITEMS_KEY.$orderId,'all');
				$rs = $this->_orderDataToMysql($productInfo,$orderItems);
				echo $rs.PHP_EOL;
			}else{
				$redis->hdel(self::ORDER_LIST_KEY,$orderId);
				$redis->del(self::ORDER_ITEMS_KEY.$orderId);
				echo '已存在'.PHP_EOL;
				echo PHP_EOL."============================================================".PHP_EOL;
			}
		}

	}

	/**
	 * 订单信息入库
	 * @return [type] [description]
	 */
	private function _orderDataToMysql($orderInfo,$orderItems){
		$redis = new MyRedis();

		$transaction= Yii::$app->db->beginTransaction();

		try{
			$orderTableId = PaymentOrderDistribution::getTableIdByOrderId($orderInfo['id']);
			echo '用户ID：'.$orderInfo['user_id'];
			echo PHP_EOL."============================================================".PHP_EOL;
			//订单详情
			$orderSave = new PaymentOrderDistribution($orderTableId);
			$orderSave->id = $orderInfo['id'];
			$orderSave->user_id = $orderInfo['user_id'];
			$orderSave->status = $orderInfo['status'];
			$orderSave->payment = $orderInfo['payment'];
			$orderSave->bank = $orderInfo['bank'];
			$orderSave->money = $orderInfo['money'];
			$orderSave->point = $orderInfo['point'];
			$orderSave->total = $orderInfo['total'];
			$orderSave->user_point = $orderInfo['point'];
			$orderSave->ip = $orderInfo['ip'];
			$orderSave->source = $orderInfo['source'];
			$orderSave->create_time = $orderInfo['create_time'];
			$orderSave->buy_time = $orderInfo['buy_time'];
			$orderSave->recharge_orderid = $orderInfo['recharge_orderid'];
			$orderSave->user_account = $orderInfo['user_account'];
			$orderSave->spread_source = $orderInfo['spread_source'];
			if (isset($orderInfo['deduction1']) && isset($orderInfo['coupon1'])) {
				$orderSave->deduction1 = $orderInfo['deduction1'];
				$orderSave->coupon1 = $orderInfo['coupon1'];
			}
			if (isset($orderInfo['deduction2']) && isset($orderInfo['coupon2'])) {
				$orderSave->deduction2 = $orderInfo['deduction2'];
				$orderSave->coupon2 = $orderInfo['coupon2'];
			}
			$result = $orderSave->save();
			echo '订单结果：'.$result;
			echo PHP_EOL."============================================================".PHP_EOL;
			//订单详情
			$db = \Yii::$app->db;
			$orderItemField = ['payment_order_id','product_id','period_id','period_number','user_id','post_nums','nums','codes','item_buy_time','source'];
			$orderItemValue = [];
			$periodBuyTime = array();
			echo '期数：';
			foreach ($orderItems as $key => $value) { 
				$v = json_decode($value,true);
				$orderItemUserId = isset($v['user_id']) ? $v['user_id'] : 0;
				$orderItemSource = isset($v['source']) ? $v['source'] : 0;
				$orderItemValue[] = [$v['payment_order_id'],$v['product_id'],$v['period_id'],$v['period_number'],$orderItemUserId,$v['post_nums'],$v['nums'],$v['codes'],$v['item_buy_time'],$orderItemSource];
				$periodBuyTime[$v['period_id']] = $v['item_buy_time'];
				echo $v['period_id'].',';
			}
			echo PHP_EOL."============================================================".PHP_EOL;
			$orderItem = new PaymentOrderItemDistribution($orderTableId);
			$itemsResult = $db->createCommand()->batchInsert($orderItem::tableName(),$orderItemField,$orderItemValue)->execute();
			echo '订单详情：'.$itemsResult;
			echo PHP_EOL."============================================================".PHP_EOL;

			$periodBuyField = ['product_id','period_id','user_id','buy_num','codes','ip','source','buy_time'];
			$userBuyInfo = $redis->hget(self::USER_BUY_LIST_KEY.$orderInfo['id'],'all');
			$periodResult = 0;
			foreach ($userBuyInfo as $key => $value) {
				$v = json_decode($value,true);

				if ($v['count'] > 0) {
					$periodBuyList = new PeriodBuylistDistribution($v['table_id']);
					$periodBuyList->product_id = $v['product_id'];
					$periodBuyList->period_id = $v['period_id'];
					$periodBuyList->user_id = $orderInfo['user_id'];
					$periodBuyList->buy_num = $v['count'];
					$periodBuyList->codes = implode(',',$v['codes']);
					$periodBuyList->ip = $orderInfo['ip'];
					$periodBuyList->source = $orderInfo['source'];
					$periodBuyList->buy_time = $periodBuyTime[$v['period_id']];
					$periodResult = $periodBuyList->save(false);
					if (!$periodResult) {
						break;
					}
				}
			}
			
			echo '期数结果：'.$periodResult;
			echo PHP_EOL."============================================================".PHP_EOL;
			if ($result && $itemsResult && $periodResult) {
				$transaction->commit();
				$redis->hdel(self::ORDER_LIST_KEY,$orderInfo['id']);
				$redis->del(self::ORDER_ITEMS_KEY.$orderInfo['id']);
				return 'success';
			}else{
				$transaction->rollback();//如果操作失败, 数据回滚	
			}
		}catch(\Exception $e) {
			$transaction->rollback();//如果操作失败, 数据回滚
			return 'fail';
		}
	}
}