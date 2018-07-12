<?php
/**
 * 
 * @authors Your Name (you@example.org)
 * @date    2016-04-06 17:21:55
 * @version $Id$
 */
namespace app\controllers;

use yii;
use app\models\Config;
use app\models\RechargeReward;
use app\models\RechargeRewardLog;
use app\services\Coupon;
use app\models\ActQualificationLog;
use app\models\ActQualification;
use app\models\UserCoupons;

class TestrechargeController extends BaseController {
	public function actionIndex()
 	{
	    // $orderInfo = ['post_money'=>150,'user_id'=>6];
	    // $this->doRechargeSomeThing(6, 150);
	    // 
	    // RechargeRewardLog::updateAll(['level'=>1],['amount'=>10]);
	    // RechargeRewardLog::updateAll(['level'=>1],['amount'=>20]);
	    // RechargeRewardLog::updateAll(['level'=>3],['amount'=>50]);
	    // RechargeRewardLog::updateAll(['level'=>4],['amount'=>100]);
	    // 
	    // RechargeRewardLog::deleteAll(['<','id','26']);

	    $tables = ['664275'=>104,'675624'=>105];

	    foreach ($tables as $key => $value) {
	    	$userCouponsModel = new UserCoupons($value);
	    	$table = $userCouponsModel->tableName();
	    	if ($key == '676205' || $key == '690602') {

	    		$sql = "update ".$table." set packet_id=21,coupon_id=22,code='0NVUMO5TTQ' where user_id = '".$key."' and packet_id = 27 and coupon_id = 28";

	    	}else if ($key == '675624' || $key == '664275') {

	    		$sql = "update ".$table." set packet_id=23,coupon_id=24,code='OJ2E94UO2D' where user_id = '".$key."' and packet_id = 21 and coupon_id = 22";
	    		// $userCouponsModel->packet_id = 23;
		    	// $userCouponsModel->coupon_id = 22;
		    	// $userCouponsModel->code = 'OJ2E94UO2D';
	    	}
	    	$db = \Yii::$app->db;
			$db->createCommand($sql)->execute();
	    }


 	}
	private function doRechargeSomeThing($userId, $rechargeMoney) {
	 
		// 添加抽奖机会
	 
		// ActQualification::addNumByRecharge($userId, $rechargeMoney);
	 
		//充值送礼
	 
		$rechargeConfig = Config::getValueByKey('rechargeconfig');
	 
		if ($rechargeConfig['status'] == 1) {
	 
			$raId = $rechargeConfig['ra_id'];
	 
			$raInfo = RechargeReward::find()->where(['id'=>$raId])->asArray()->one();
	 
			$time = time();
	 
			if ($raInfo &&  $raInfo['status'] == 1) {
	 
				if ($time >= $raInfo['start_time'] && $time <= $raInfo['end_time']) {
	 
					$packetId = $giveTime = 0;
	 
					$prizeName = $canReceive = '';
	 
					$prizes = json_decode($raInfo['prizes'],true);
	 
					foreach ($prizes as $key => $value) {
	 
						if ($value['condition'] == 0) {
	 
							if ($rechargeMoney >= $value['min'] && ( !$value['max'] || $rechargeMoney <= $value['max'])) {
	 
								$value['level'] = $key;
	 
								$canReceive[$key] = $value;
	 
							}
	 
						}
	 
					}
	 
					if ($canReceive) {
	 
						$log = RechargeRewardLog::find()->where(['number'=>$raId,'user_id'=>$userId])->asArray()->all();
	 
						$canReceiveInfo = '';
	 
						if ($log) {
	 
							$completed = [];
	 
							foreach ($log as $key => $value) {
	 
								$completed[] = $value['level'];
	 
							}
	 
							foreach ($canReceive as $key => $value) {
	 
								if (!in_array($value['level'],$completed)) {
	 
									$canReceiveInfo = $value;
	 
								}
	 
							}
	 
						}else{
	 
							$canReceiveInfo = end($canReceive);
	 

	 
						}
	 
						if ($canReceiveInfo) {
	 
							$packetId = $canReceiveInfo['packets'];
	 
							$giveTime = $canReceiveInfo['givetime'];
	 
							$prizeName = $canReceiveInfo['prizename'];
	 
							$level = $canReceiveInfo['level'];
	 
						}
	 
					}
	 
					if ($packetId && $giveTime == 0) {
	 					echo $packetId;
						$packet = Coupon::receivePacket($packetId, $userId, 'recharge');
	 					print_r($packet);
						if ($packet['code'] == '0') {
	 
							$packetId = $packet['data']['pid'];
	 
							Coupon::openPacket($packetId, $userId);
	 
						}
	 
						$logModel = new RechargeRewardLog();
	 
						$logModel->number = $raId;
	 
						$logModel->user_id = $userId;
	 
						$logModel->level = $level;
	 
						$logModel->prize = $prizeName;
	 
						$logModel->amount = $rechargeMoney;
	 
						$logModel->create_time = time();
	 
						$logModel->notice = 0;
	 
						$logModel->save();
	 
					}
	 
				}else{
	 
					Config::updateAll(['value'=>json_encode(['ra_id'=>$raId,'status'=>0])],['key'=>'rechargeconfig']);
	 
				}
	 
			}
	 
		}
	 
	}

}