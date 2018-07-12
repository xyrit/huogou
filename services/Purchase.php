<?php

namespace app\services;

use Yii;
use app\models\Purchase as PurchaseModel;
use yii\helpers\Json;
use app\components\Zhifuka;
use app\components\Phonefee;
use app\models\VirtualPurchaseOrder;
use app\models\MobileCardLog;

/**
 * 自动购买虚拟卡
 */
class Purchase
{
	
	private $virtualProducts = array(
		'yd' => array(
			'10' => array('name' => '10元移动充值卡', 'parValue' => '10', 'per_Money' => '9.94', 'mark' => 'yd0006', 'type' => 'yd', 'perMoney' => '10'),
			'30' => array('name' => '30元移动充值卡', 'parValue' => '30', 'per_Money' => '29.4', 'mark' => 'yd0008', 'type' => 'yd', 'perMoney' => '30'),
			'50' => array('name' => '50元移动充值卡', 'parValue' => '50', 'per_Money' => '49.9', 'mark' => 'yd0009', 'type' => 'yd', 'perMoney' => '50'),
			'100' => array('name' => '100元移动充值卡', 'parValue' => '100', 'per_Money' => '99.8', 'mark' => 'yd0009', 'type' => 'yd', 'perMoney' => '100'),
		),
		'lt' => array(
			'30' => array('name' => '30元联通充值卡', 'parValue' => '30', 'per_Money' => '29.82', 'mark' => 'lt0003', 'type' => 'lt', 'perMoney' => '30'),
			'50' => array('name' => '50元联通充值卡', 'parValue' => '50', 'per_Money' => '49.7', 'mark' => 'lt0003', 'type' => 'lt', 'perMoney' => '50'),
			'100' => array('name' => '100元联通充值卡', 'parValue' => '100', 'per_Money' => '99.4', 'mark' => 'lt0003', 'type' => 'lt', 'perMoney' => '100'),
		),
		'dx' => array(
			'30' => array('name' => '30元电信充值卡', 'parValue' => '30', 'per_Money' => '28.95', 'mark' => 'dx0002', 'type' => 'dx', 'perMoney' => '30'),
			'50' => array('name' => '50元电信充值卡', 'parValue' => '50', 'per_Money' => '48.25', 'mark' => 'dx0002', 'type' => 'dx', 'perMoney' => '50'),
			'100' => array('name' => '100元电信充值卡', 'parValue' => '100', 'per_Money' => '96.5', 'mark' => 'dx0002', 'type' => 'dx', 'perMoney' => '100'),
		),
		'qbonline' => array(
			'1' => array('name' => 'Q币', 'parValue' => 1, 'per_money' => '0.97', 'mark' => 'qq0001', 'type' => 'qb', 'perMoney' => '1')
		)
	);
	
	public function autoBuy($type, $amount)
	{
		// if (\Yii::$app->request->userIP == '127.0.0.1') {
		// 	return false;
		// }
		$nums = 1;
		$parValue = 10;
		if ($amount >= 100) {
			$nums = intval($amount / 100);
			$parValue = 100;
		} else {
			if (in_array($amount, array(30, 50))) {
				$parValue = $amount;
			} else {
				return false;
				// $nums = 2;
			}
		}
		$productInfo = $this->virtualProducts[$type][$parValue];
		
		if (!$productInfo) {
			return array('code' => 0);
		}
		$vid = $this->getVid($type, $parValue);
		
		$schedule = array(
			array(
				'user' => 'system',
				'schedule' => '提交申请',
				'time' => date("Y-m-d H:i:s", time())
			),
			array(
				'user' => 'system',
				'schedule' => '通过申请',
				'time' => date("Y-m-d H:i:s", time())
			)
		);
		$purchase = new PurchaseModel();
		$purchase->admin_id = 0;
		$purchase->product_id = 0;
		$purchase->type = 2;
		$purchase->nums = $nums;
		$purchase->total = $parValue * $nums;
		$purchase->product_name = $productInfo['name'];
		$purchase->per_money = $productInfo['perMoney'];
		$purchase->status = 3;
		$time = time();
		$purchase->create_time = $time;
		$purchase->last_update_time = $time;
		$purchase->schedule = Json::encode($schedule);
		$purchase->extra = Json::encode(
			array(
				'vid' => $vid,
				'supplier' => '星启天',
				'interface' => 'zhifuka',
				'parValue' => $productInfo['parValue']
			)
		);
		$rs = $purchase->save();
		$purchaseId = $purchase->attributes['id'];
		
		$order = VirtualPurchaseOrder::createOrder($purchaseId, $vid, $parValue, $nums);
		if ($order) {
			$mark = $productInfo['mark'];
			$result = Yii::$app->zhifuka->buyCard($mark, $parValue, $nums, $order, \Yii::$app->request->userIP);
			if ($result['code'] == '100') {
				VirtualPurchaseOrder::updateAll(
					array(
						'status' => 1,
						'update_time' => time(),
						'exchange_no' => $result['msg']['exchange_id'],
						'result' => $result['msg']['result'],
					), array('orderid' => $result['msg']['orderid'])
				);
				$_schedule = array(
					'user' => 'system',
					'schedule' => '入库',
					'time' => date("Y-m-d H:i:s", time())
				);
				array_push($schedule, $_schedule);
				PurchaseModel::updateAll(
					array(
						'schedule' => Json::encode($schedule),
					), "id='" . $purchaseId . "'"
				);
				$virtualDepotField = ['orderid', 'card', 'pwd', 'par_value', 'type'];
				$virtualDepotValue = [];
				foreach ($result['msg']['cards'] as $key => $value) {
					$virtualDepotValue[] = [$order, $value['card'], $value['pwd'], $parValue, $type];
				}
				$db = \Yii::$app->db;
				$rc = $db->createCommand()->batchInsert('virtual_depot', $virtualDepotField, $virtualDepotValue)->execute();
				if ($rc) {
					return $result;
				}
			} else {
				VirtualPurchaseOrder::updateAll(
					array(
						'status' => '2',
						'update_time' => time(),
						'result' => $result['msg']
					), array('orderid' => $order)
				);
				return false;
			}
		}
	}
	
	public function onlinePay($payTo, $type, $nums, $winOrder)
	{
		if ($type == 'mobile_online') {
			if (!in_array($nums, array(10, 20, 30, 50, 100, 200, 300, 500))) {
				return false;
			}
			$getProduct = Yii::$app->zhifuka->getMobileCardProductId($payTo, $nums);
			
			$mobileCardLog = new MobileCardLog();
			$mobileCardLog->mobile = $payTo;
			$mobileCardLog->orderid = $winOrder;
			$mobileCardLog->message = $getProduct['data']['msg'];
			$mobileCardLog->result = $getProduct['data']['result'];
			$mobileCardLog->create_time = time();
			if ($getProduct['code'] != 100) {
				$mobileCardLog->save();
				return false;
			} else {
				$productInfo = [
					'parValue' => $getProduct['data']['parvalue'],
					'name' => $nums,
					'perMoney' => $getProduct['data']['parvalue'],
					'mark' => $getProduct['data']['productId']
				];
				$mobileCardLog->province = $getProduct['data']['province'];
				$mobileCardLog->type = $getProduct['data']['type'];
				$mobileCardLog->product_id = $getProduct['data']['productId'];
				$mobileCardLog->face_value = $nums;
				$mobileCardLog->save();
			}
		} else {
			$productInfo = $this->virtualProducts[$type]['1'];
		}
		$schedule = array(
			array(
				'user' => 'system',
				'schedule' => '提交申请',
				'time' => date("Y-m-d H:i:s", time())
			),
			array(
				'user' => 'system',
				'schedule' => '通过申请',
				'time' => date("Y-m-d H:i:s", time())
			)
		);
		if ($type == 'mobile_online') {
			$vid = 0;
		} else {
			$vid = $this->getVid($type, $productInfo['parValue']);
		}
		$purchase = new PurchaseModel();
		$purchase->admin_id = 0;
		$purchase->product_id = 0;
		$purchase->type = 2;
		if ($type == 'mobile_online') {
			$purchase->nums = 1;
			$purchase->total = $productInfo['parValue'] * 1;
		} else {
			$purchase->nums = $nums;
			$purchase->total = $productInfo['parValue'] * $nums;
		}
		$purchase->product_name = $productInfo['name'];
		$purchase->per_money = $productInfo['perMoney'];
		$purchase->status = 3;
		$time = time();
		$purchase->create_time = $time;
		$purchase->last_update_time = $time;
		$purchase->schedule = Json::encode($schedule);
		$purchase->extra = Json::encode(
			array(
				'vid' => $vid,
				'supplier' => '星启天',
				'interface' => 'zhifuka',
				'parValue' => $productInfo['parValue'],
				'winOrder' => $winOrder
			)
		);
		$rs = $purchase->save();
		$purchaseId = $purchase->attributes['id'];
		
		$order = VirtualPurchaseOrder::createOrder($purchaseId, $vid, $productInfo['parValue'], $nums);
		if ($order) {
			$mark = $productInfo['mark'];
			if ($type == 'qbonline') {
				$result = Yii::$app->zhifuka->onlinePay($payTo, $productInfo['mark'], $productInfo['parValue'], $nums, $order, \Yii::$app->request->userIP);
			} else if ($type == 'mobile_online') {

				$result = Yii::$app->zhifuka->onlinePay($payTo, $productInfo['mark'], 1, 1, $order, \Yii::$app->request->userIP);
			}
			if ($result['code'] == '100') {
				VirtualPurchaseOrder::updateAll(
					array(
						'status' => 1,
						'update_time' => time(),
						'result' => $result['msg']['result'],
					), array('orderid' => $result['msg']['orderid'])
				);
				$_schedule = array(
					'user' => 'system',
					'schedule' => '提交订单',
					'time' => date("Y-m-d H:i:s", time())
				);
				array_push($schedule, $_schedule);
				PurchaseModel::updateAll(
					array(
						'schedule' => Json::encode($schedule),
					), "id='" . $purchaseId . "'"
				);
				return true;
			} else {
				VirtualPurchaseOrder::updateAll(
					array(
						'status' => '2',
						'update_time' => time(),
						'result' => $result['msg']
					), array('orderid' => $order)
				);
				return false;
			}
		}
	}
	
	/**
	 * @param $payTo
	 * @param $type
	 * @param $nums
	 * @param $winOrder
	 * @return bool
	 * 话费直充
	 */
	public function onlineCharge($payTo, $type, $nums, $winOrder)
	{
		if ($type == 'mobile_online') {
			if (!in_array($nums, array(10, 20, 30, 50, 100, 300))) {
				return false;
			}

			$Config = require(\Yii::getAlias('@app/config/huafei.php'));
			$Phonefee = new Phonefee($Config['appkey'], $Config['openid']);
			$getProduct = $Phonefee->getProductInfo($payTo, $nums);

			$mobileCardLog = new MobileCardLog();
			$mobileCardLog->mobile = $payTo;
			$mobileCardLog->orderid = $winOrder;
			$mobileCardLog->message = isset($getProduct['result']['cardname'])?$getProduct['result']['cardname']:'无';
			$mobileCardLog->result = isset($getProduct['result'])?Json::encode($getProduct['result']):'无';
			$mobileCardLog->create_time = time();

			if ($getProduct['code'] != 100) {
				$mobileCardLog->save();
				return false;
			} else {
				$productInfo = [
					'parValue' => $getProduct['result']['inprice'],
					'name' => $nums,
					'perMoney' => $getProduct['result']['inprice'],
					'mark' => $getProduct['result']['cardid']
				];
				$mobileCardLog->province = mb_substr($getProduct['result']['game_area'], 0, 2, 'utf-8');
				$mobileCardLog->type = mb_substr($getProduct['result']['game_area'], 4, 2, 'utf-8');
				$mobileCardLog->product_id = $getProduct['result']['cardid'];
				$mobileCardLog->face_value = $nums;
				$mobileCardLog->save();

			}
		} else {
			$productInfo = $this->virtualProducts[$type]['1'];
		}
		$schedule = array(
			array(
				'user' => 'system',
				'schedule' => '提交申请',
				'time' => date("Y-m-d H:i:s", time())
			),
			array(
				'user' => 'system',
				'schedule' => '通过申请',
				'time' => date("Y-m-d H:i:s", time())
			)
		);
		if ($type == 'mobile_online') {
			$vid = 0;
		} else {
			$vid = $this->getVid($type, $productInfo['parValue']);
		}
		$purchase = new PurchaseModel();
		$purchase->admin_id = 0;
		$purchase->product_id = 0;
		$purchase->type = 2;
		if ($type == 'mobile_online') {
			$purchase->nums = 1;
			$purchase->total = $productInfo['parValue'] * 1;
		} else {
			$purchase->nums = $nums;
			$purchase->total = $productInfo['parValue'] * $nums;
		}
		$purchase->product_name = $productInfo['name'];
		$purchase->per_money = $productInfo['perMoney'];
		$purchase->status = 3;
		$time = time();
		$purchase->create_time = $time;
		$purchase->last_update_time = $time;
		$purchase->schedule = Json::encode($schedule);
		$purchase->extra = Json::encode(
			array(
				'vid' => $vid,
				'supplier' => '聚合数据',
				'interface' => 'zhifuka',
				'parValue' => $productInfo['parValue'],
				'winOrder' => $winOrder
			)
		);
		$rs = $purchase->save();
		$purchaseId = $purchase->attributes['id'];
  
		$order = VirtualPurchaseOrder::createOrder($purchaseId, $vid, $productInfo['parValue'], $nums);
		if ($order) {
			$mark = $productInfo['mark'];
			if ($type == 'qbonline') {
				$result = Yii::$app->zhifuka->onlinePay($payTo, $productInfo['mark'], $productInfo['parValue'], $nums, $order, \Yii::$app->request->userIP);
			} else if ($type == 'mobile_online') {
				if (DOMAIN == 'huogou.com' || DOMAIN == 'dddb.com') {
					$result = $Phonefee->onlinePay($payTo, $nums, $winOrder);
				}else{
					$result=['code'=>100,'result'=>['game_state'=>0,'sporder_id'=>date('YmdHis')]];
				}


			}
			if ($result['code'] == '100') {
				VirtualPurchaseOrder::updateAll(
					array(
						'status' => $result['result']['game_state'],
						'update_time' => time(),
						'result' => Json::encode($result),
						'exchange_no' => $result['result']['sporder_id'],
					), array('orderid' => $order)
				);
				$_schedule = array(
					'user' => 'system',
					'schedule' => '提交订单',
					'time' => date("Y-m-d H:i:s", time())
				);
				array_push($schedule, $_schedule);
				PurchaseModel::updateAll(
					array(
						'schedule' => Json::encode($schedule),
					), "id='" . $purchaseId . "'"
				);

				return true;
			} else {
				VirtualPurchaseOrder::updateAll(
					array(
						'status' => '2',
						'update_time' => time(),
						'result' => Json::encode($result)
					), array('orderid' => $order)
				);
				return false;
			}
		}
	}
	
	private function getVid($type, $parValue)
	{
		$vid = 0;
		if ($type == 'yd') {
			$vid = 0;
		} else if ($type == 'lt') {
			$vid = 3;
		} else if ($type == 'dx') {
			$vid = 6;
		} else if ($type == 'qbonline') {
			$vid = 9;
		}
		$l = array('1' => 0, '10' => 0, '30' => 1, '50' => 2, '100' => 3);
		return $vid + $l[$parValue];
	}
}