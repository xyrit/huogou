<?php

	namespace app\modules\api\controllers;

	use app\services\PkCoupon;
	use Yii;
	use app\models\UserCoupons;
	use app\models\Coupon as CouponModel;
	use app\services\Coupon;
	use app\models\Lottery;
	use app\models\UserPacket;

	/**
	* 用户优惠券
	*/
	class UsercouponsController extends BaseController
	{
		
		/**
		 * 红包列表
		 * @return [type] [description]
		 */
		public function actionList()
		{
			if (!$this->userId) {
				return ['code' => 201, 'msg' => '未登录'];
			}

			$type = Yii::$app->request->get('type');
			$periodId = Yii::$app->request->get('period_id');
			$buyNum = Yii::$app->request->get('buy_num');

			if ($type == 'all') {
				$page = Yii::$app->request->get('page','1');
				$perpage = Yii::$app->request->get('perpage','20');
				$filter = Yii::$app->request->get('filter');
				$where = [];
				if ($filter == 1) {
					$where['status'] = 0;
				}else if ($filter == 2) {
					$where['status'] = [1,2,3,4];
				}

				$userCoupons = UserCoupons::getUserCoupons($this->userId, $page, $perpage,$where);
				$couponId = '';

				foreach ($userCoupons['list'] as $key => $value) {
					$couponId[] = $value['coupon_id'];
					if ($value['nums'] > 1) {
						for ($i=1; $i < $value['nums']; $i++) { 
							$userCoupons['list'][] = $value;
							$userCoupons['total'] += 1;
						}
					}
					// $userCodes[$value['coupon_id']] = ['id'=>$value['id'],'code'=>$value['code']];
				}

				$couponsInfo = CouponModel::getInfo($couponId);

				$activityList = Lottery::find()->asArray()->all();
				$actives = [];
				foreach ($activityList as $key => $value) {
					$actives[$value['id']] = $value['name'];
				}

				$userPacketList = UserPacket::find()->where(['user_id'=>$this->userId])->asArray()->all();
				$userPackets = [];
				foreach ($userPacketList as $key => $value) {
					if ($value['source'] == 'sign') {
						$userPackets[$value['packet_id']] = '签到红包';
					}else if ($value['source'] == 'reg') {
						$userPackets[$value['packet_id']] = '注册红包';
					}else if ($value['source'] == 'recharge') {
						$userPackets[$value['packet_id']] = '充值红包';
					}else{
						$_source = explode('_',$value['source']);
						if ($_source['0'] == 'activity') {
							$userPackets[$value['packet_id']] = isset($actives[$_source['1']]) ? $actives[$_source['1']] : '';
						}
					}
				}

				$couponList = ['list'=>'','total' => $userCoupons['total']];
				
				foreach ($userCoupons['list'] as $key => $value) {
					$couponList['list'][$key]['user_code_id'] = $value['id'];
					$couponList['list'][$key]['coupon_code'] = $value['code'];
					$couponList['list'][$key]['coupon_id'] = $value['coupon_id'];
					$couponList['list'][$key]['status'] = $value['status'];
					$couponList['list'][$key]['receive_time'] = $value['receive_time'];
					$couponList['list'][$key]['used_time'] = $value['used_time'];
					$couponList['list'][$key]['name'] = $couponsInfo[$value['coupon_id']]['name'];
					// $couponList['list'][$key]['icon'] = $couponsInfo[$value['coupon_id']]['icon'];
					$couponList['list'][$key]['desc'] = $couponsInfo[$value['coupon_id']]['desc'];
					$couponList['list'][$key]['valid_type'] = $couponsInfo[$value['coupon_id']]['valid_type'];
					if ($couponsInfo[$value['coupon_id']]['valid_type'] == 1) {
						$couponList['list'][$key]['start_time'] = $couponsInfo[$value['coupon_id']]['start_time'];
						$couponList['list'][$key]['end_time'] = $couponsInfo[$value['coupon_id']]['end_time'];
					}else if ($couponsInfo[$value['coupon_id']]['valid_type'] == 2){
						$couponList['list'][$key]['start_time'] = $value['receive_time'];
						$couponList['list'][$key]['end_time'] = $value['receive_time'] + $couponsInfo[$value['coupon_id']]['valid'];
						$couponList['list'][$key]['valid'] = $couponsInfo[$value['coupon_id']]['valid'];
					}

					$couponList['list'][$key]['source'] = isset($userPackets[$value['packet_id']]) ? $userPackets[$value['packet_id']] : '';

					$type = $couponsInfo[$value['coupon_id']]['type'];
					$couponList['list'][$key]['type'] = $type;
					$amount = json_decode($couponsInfo[$value['coupon_id']]['amount'],true);
					if ($type == '1') {
						$couponList['list'][$key]['amount'] =  $amount['money'];
					}else if ($type == '2') {
						$couponList['list'][$key]['amount'] = $amount['discount'];
					}else if ($type == '3') {
						if (isset($amount['point'])) {
							$couponList['list'][$key]['give'] = 'point';
							$couponList['list'][$key]['amount'] = $amount['point'];
						}
						if (isset($amount['money'])) {
							$couponList['list'][$key]['give'] = 'money';
							$couponList['list'][$key]['amount'] = $amount['money'];
						}
					}
					if ($couponsInfo[$value['coupon_id']]['type'] != 3) {
						$condition = json_decode($couponsInfo[$value['coupon_id']]['condition'],true);
						$couponList['list'][$key]['need'] = $condition['need'];
						$range = explode(',',$condition['range']);
						if (in_array(1,$range)) {
							$couponList['list'][$key]['range'] = '全场通用';
						} else {
							if (in_array(2,$range)) {
								$couponList['list'][$key]['range'] = '限购商品';
							}
							if (in_array(3,$range)) {
								$couponList['list'][$key]['range'] = isset($couponList['list'][$key]['range']) ? $couponList['list'][$key]['range'].'、10元专区' : '10元专区';
							}
							if (in_array(4,$range) || in_array(5,$range)) {
								$couponList['list'][$key]['range'] = isset($couponList['list'][$key]['range']) ? $couponList['list'][$key]['range']. '、' . $couponsInfo[$value['coupon_id']]['desc'] : $couponsInfo[$value['coupon_id']]['desc'];
							}
						}


					}
				}

				return $couponList;
			}else if ($type == 'use' || $type == 'pk_use') {
				if ($type == 'use') {
					$list = Coupon::getUserValidList($this->userId,'use');
				} else {
					$list = PkCoupon::getUserValidList($this->userId, 'pk_use', $periodId, $buyNum);
				}
				
				$coupons = [];
				foreach ($list as $key => $value) {
					$coupons[$key]['user_code_id'] = $value['id'];
					$coupons[$key]['coupon_code'] = $value['code'];
					$coupons[$key]['coupon_id'] = $value['info']['id'];
					$coupons[$key]['status'] = $value['status'];
					$coupons[$key]['receive_time'] = $value['receive_time'];
					$coupons[$key]['used_time'] = $value['used_time'];
					$coupons[$key]['name'] = $value['info']['name'];
					$coupons[$key]['desc'] = $value['info']['desc'];
					$coupons[$key]['valid_type'] = $value['info']['valid_type'];
					if ($value['info']['valid_type'] == 1) {
						$coupons[$key]['start_time'] = $value['info']['start_time'];
						$coupons[$key]['end_time'] = $value['info']['end_time'];
					}else if ($value['info']['valid_type'] == 2){
						$coupons[$key]['start_time'] = $value['receive_time'];
						$coupons[$key]['end_time'] = $value['receive_time'] + $value['info']['valid'];
						$coupons[$key]['valid'] = $value['info']['valid'];
					}
					$type = $value['info']['type'];
					$coupons[$key]['type'] = $type;
					$amount = json_decode($value['info']['amount'],true);
					if ($type == '1') {
						$coupons[$key]['amount'] =  $amount['money'];
					}else if ($type == '2') {
						$coupons[$key]['amount'] = $amount['discount'];
					}else if ($type == '3') {
						if (isset($amount['point'])) {
							$coupons[$key]['give'] = 'point';
							$coupons[$key]['amount'] = $amount['point'];
						}
						if (isset($amount['money'])) {
							$coupons[$key]['give'] = 'money';
							$coupons[$key]['amount'] = $amount['money'];
						}
					}
					$coupons[$key]['deduction'] = $value['deduction'];
					$condition = json_decode($value['info']['condition'],true);
					$coupons[$key]['need'] = $condition['need'];
					$range = explode(',',$condition['range']);
					if (in_array(1,$range)) {
						$coupons[$key]['range'] = '全场通用';
					} else {

						if (in_array(2,$range)) {
							$coupons[$key]['range'] = '限购商品';
						}
						if (in_array(3,$range)) {
							$coupons[$key]['range'] = isset($coupons[$key]['range']) ? $coupons[$key]['range'].'、10元专区' : '10元专区';
						}
						if (in_array(4,$range) || in_array(5,$range)) {
							$coupons[$key]['range'] = isset($coupons[$key]['range']) ? isset($coupons[$key]['range']) . '、' . $value['info']['desc'] : $value['info']['desc'];
						}

					}

					$coupons[$key]['date'] = date("Y-m-d",$coupons[$key]['end_time']);

				}

				return ['list'=>array_values($coupons),'total'=>count($coupons)];
			}
		}

		/**
		 * 打开红包
		 * @return [type] [description]
		 */
		public function actionPacket()
		{
			$packetId = Yii::$app->request->get('pid');
			if (!$packetId) {
				return ['code' => '301','msg'=>'红包不存在'];
			}

			$info = Coupon::openPacket($packetId,$this->userId);

			return $info;
		}

		/**
		 * 领取注册红包并打开
		 * @return [type] [description]
		 */
		public function actionGetRegPacket()
		{
			$rs = Coupon::receivePacket(1, $this->userId, 'sign');

			if ($rs['code'] == '0') {
				$pid = $rs['data']['pid'];

				$info = Coupon::openPacket($pid,$this->userId);

				return $info;				
			}else{
				return $rs;
			}
		}

		/**
		 * 兑换红包
		 * @return [type] [description]
		 */
		public function actionExchange()
		{
			if (!$this->userId) {
				return ['code'=>201,'msg'=>'未登陆'];
			}
			$couponId = Yii::$app->request->post('user_code_id');
			$couponCode = Yii::$app->request->post('coupon_code');
			$source = Yii::$app->request->post('source');
			if (!$couponId || !$couponCode) {
				return ['code'=>202,'msg'=>'参数错误'];
			}

			return Coupon::useCoupon($this->userId,$couponId,$couponCode,'3',$source);
		}

		public function actionSendSignPacket()
		{
			$packetId = Yii::$app->request->get('packet_id');
			$userId = Yii::$app->request->get('user_id');
			$source = Yii::$app->request->get('source');
			$password = Yii::$app->request->get('password');

			if ($password != 'huogou') {
				return false;
			}

			$rs = Coupon::receivePacket($packetId, $userId, $source);

			if ($rs['code'] == '0') {
				$pid = $rs['data']['pid'];

				$info = Coupon::openPacket($pid,$userId);

				return true;
			}else{
				return false;
			}
		}
		
	}