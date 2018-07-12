<?php
	
	namespace app\services;

	use yii;
	use app\models\UserCoupons;
	use app\models\Coupon as CouponModel;
	use app\models\CouponCode;
	use app\models\Packet;
	use app\models\UserPacket;
	use app\models\User as UserModel;
	use app\models\Cart as CartModel;

	/**
	* 优惠券相关操作
	*/
	class Coupon
	{
		/**
		 * 获取用户有效优惠券并更新已过期
		 * @param  int $uid 用户ID
		 * @return [type]      [description]
		 */
		public static function getUserValidList($uid,$type='use')
		{
			if (!$uid) {
				return ['code'=>201,'msg'=>'未登录'];
			}

			$validCoupon = UserCoupons::getValidCoupons($uid);
			$validCouponInfo = self::getCouponInfo($validCoupon);
			
			$validCouponInfo = self::_checkCoupons($uid,$type,$validCouponInfo);
			return $validCouponInfo;
		}

		/**
		 * 获取优惠券信息
		 * @param  array $userCoupons 用户优惠券列表ID
		 * @return [type]              [description]
		 */
		public static function getCouponInfo($userCoupons){
			$couponId = '';
			foreach ($userCoupons as $key => $value) {
				$couponId[] = $value['coupon_id'];
			}

			$couponInfo = CouponModel::getInfo($couponId);

			foreach ($userCoupons as $key => &$value) {
				$value['info'] = $couponInfo[$value['coupon_id']];
			}

			return $userCoupons;
		}
		/**
		 * 打开红包
		 * @param  int $id  用户的红包id
		 * @param  int $uid 用户ID
		 * @return [type]      [description]
		 */
		public static function openPacket($id,$uid)
		{
			if (!$uid) {
				return ['code'=>201,'msg'=>'未登录'];
			}

			$info = UserPacket::getInfo($id);
			if ( !$info || $info['user_id'] != $uid) {
				return ['code'=>302,'msg'=>'红包不存在'];
			}
			$packetInfo = Packet::getInfo($info['packet_id']);
			
			$coupons = json_decode($packetInfo['content'],true);

			$couponId = array_keys($coupons);
			$couponInfo = CouponModel::getInfo($couponId);

			$coupontContent = '';

			foreach ($couponInfo as $key => $value) {
				$coupontContent[] = ['name'=>$value['name'],'nums'=>$coupons[$value['id']]];
			}

			if ($info['status'] == 1 && $info['open_time'] > 0) {
				return ['code'=>'307','msg'=>'红包已经打开','data'=>$coupontContent];
			}

			$couponList = '';
			foreach ($coupons as $key => $value) {
				$_couponList['num'] = $value;
				if ($couponInfo[$key]['num'] == 0) {
					$_couponList['codes'] = CouponCode::getCodeByCid($key,$value);					
				}else{
					$_couponList['codes'] = CouponCode::getCodeByCid($key,$value,$info['packet_id'],5);
				}
				$_couponList['nums'] = 1;
				$_couponList['coupon_id'] = $key;
				$couponList[] = $_couponList;
				if ($value > 1) {
					for ($i=1; $i < $value; $i++) { 
						$couponList[] = $_couponList;						
					}
				}
				$sendNum = CouponModel::findOne(['id'=>$key]);
				if ($sendNum) {
					$sendNum->send_num += $value;
					if ($couponInfo[$key]['num'] > 0) {
						$sendNum->left_num -= $value; 
					}
					$sendNum->save();
				}

			}

			
			$transaction= \Yii::$app->db->beginTransaction();
			try {
				$userCouponsField = ['user_id','coupon_id','code','receive_time','nums','packet_id'];
				$userCouponsValue = '';
				$time = time();
				$codes = [];
				foreach ($couponList as $key => $value) {
					foreach ($value['codes'] as $k => $v) {
						$userCouponsValue[] = [$uid,$value['coupon_id'],$v['code'],$time,$value['nums'],$info['packet_id']];
						if ($couponInfo[$v['coupon_id']]['num'] != 0) {
							$codes[] = $v['code'];	
						}
					}
				}
				$homeId = UserModel::find()->select('home_id')->where(['id'=>$uid])->asArray()->one();
				$tableId = substr($homeId['home_id'],0,3);
				$userCouponsModel = new UserCoupons($tableId);
				$userCouponsTableName = $userCouponsModel->tableName();
				$db = \Yii::$app->db;
				$rs = $db->createCommand()->batchInsert($userCouponsTableName,$userCouponsField,$userCouponsValue)->execute();
				if ($rs) {
					$upCode = '';
					if (count($codes) > 0) {
						$upCode = CouponCode::updateAll([
								'status' => 1,
								'user_id' => $uid,
								'receive_time' => time()
							],['in','code',$codes]
						);						
					}

					$upUserPacket = UserPacket::updateAll([
								'status' => 1,
								'open_time' => $time
							],['id'=>$id]
						);
				}
				if (($rs && $upUserPacket && $upCode && count($codes)>0) || ($rs && $upUserPacket)) {
					$transaction->commit();
					return ['code'=>0,'result'=>$coupontContent,'msg'=>'打开成功'];
				}else{
					$transaction->rollback();
					return ['code'=>302,'msg'=>'红包打开失败'];	
				}
			} catch (\Exception $e) {
				$transaction->rollback();
				return ['code'=>302,'msg'=>'红包打开失败'];
			}
			
		}

		/**
		 * 领取红包
		 * @param  int $packetId 红包ID
		 * @param  int $uid      用户ID
		 * @param  string $source   领取原因
		 * @return [type]           [description]
		 */
		public static function receivePacket($packetId,$uid,$source='')
		{
			if (!$uid) {
				return ['code'=>201,'msg'=>'未登录'];
			}
			$packetInfo = Packet::getInfo($packetId);
			if (!$packetInfo) {
				return ['code'=>303,'msg'=>'红包不存在'];
			}
			
			if ( $packetInfo['num'] > 0 && ($packetInfo['left_num'] == 0 || $packetInfo['send_num'] >= $packetInfo['num'])) {
				return ['code'=>306,'msg'=>'红包已被领光'];
			}
			if ($packetInfo['receive_limit'] > 0) {
				$received = UserPacket::getCountByUidByCid($uid, $packetId);
				if ($received == $packetInfo['receive_limit']) {
					return ['code'=>304,'msg'=>'已经领取过此红包'];
				}
			}

			$transaction = \Yii::$app->db->beginTransaction();
			try {
				$receivePacket = new UserPacket();
				$receivePacket->user_id = $uid;
				$receivePacket->packet_id = $packetId;
				$receivePacket->source = $source;
				$receivePacket->status = 0;
				$receivePacket->receive_time = time();
				$receivePacket->save(false);

				$pid = $receivePacket->attributes['id'];
				if ($pid) {
					$packet = Packet::findOne(['id'=>$packetId]);
					$packet->send_num += 1;
					if ($packet['num'] > 0) {
						$packet->left_num -= 1;
					}
					$rs = $packet->save(false);
				}
				if ($pid && $rs) {
					$transaction->commit();
					return ['code'=>0,'msg'=>'红包领取成功','data'=>['pid'=>$pid,'name'=>$packetInfo['name']]];
				}
				$transaction->rollback();
				return ['code'=>305,'msg'=>'红包领取失败!'];
			} catch (\Exception $e) {
				$transaction->rollback();
				file_put_contents('coupon.txt', $e->getLine(). '_' .$e->getMessage(), FILE_APPEND);
				return ['code'=>305,'msg'=>'红包领取失败'];
			}
		}

		/**
		 * 使用优惠券
		 * @param  int $couponId 优惠券ID
		 * @param  int $uid      用户ID
		 * @return [type]           [description]
		 */
		public static function useCoupon($uid,$couponId,$couponCode,$type,$source)
		{
			if (!$uid) {
				return ['code'=>201,'msg'=>'未登录'];
			}

			$coupon = UserCoupons::getUserCouponById($uid,$couponId);
			if (!$coupon || $coupon['user_id'] != $uid || $coupon['code'] != $couponCode) {
				return ['code' => 400,'msg'=>'优惠券不存在'];
			}
			if ($coupon['status'] == '1') {
				return ['code' => 401,'msg'=>'优惠券已被使用'];
			}
			if ($coupon['status'] == '2') {
				return ['code' => 402,'msg'=>'优惠券失效'];	
			}
			if ($coupon['status'] == '3') {
				return ['code' => 403,'msg'=>'优惠券已过期'];	
			}
			if ($coupon['status'] == '4') {
				return ['code' => 404,'msg'=>'优惠券已经被冻结'];	
			}

			if ($coupon['status'] == '0') {
				$couponInfo = CouponModel::getInfo($coupon['coupon_id']);
				if ($couponInfo['type'] != $type) {
					return ['code' => 400,'msg'=>'优惠券不存在'];
				}
				$time = time();
				if ($couponInfo['valid_type'] == 1) {
					if ($time < $couponInfo['start_time'] || $time > $couponInfo['end_time']) {
						UserCoupons::updateAll([
								'status' => 3
							],['id'=>$coupon['id']]
						);
						CouponCode::updateAll([
								'status' => 3
							],['user_id'=>$uid,'code'=>$couponCode,'coupon_id'=>$coupon['coupon_id']]
						);
						return ['code'=>405,'msg'=>'优惠券已过期'];
					}	
				}else if ($couponInfo['valid_type'] == 2) {
					if (($couponInfo['valid']+$coupon['receive_time']) < $time) {
						UserCoupons::updateAll([
								'status' => 3
							],['id'=>$coupon['id']]
						);
						CouponCode::updateAll([
								'status' => 3
							],['user_id'=>$uid,'code'=>$couponCode,'coupon_id'=>$coupon['coupon_id']]
						);
						return ['code'=>405,'msg'=>'优惠券已过期'];
					}
				}

				if ($type == '1' || $type == '2') {
					// UserCoupons::updateAll([
					// 		'status' => 1,
					// 		'used_time' => $time
					// 	],['id'=>$coupon['id']]
					// );
					// CouponCode::updateAll([
					// 		'status' => 1
					// 	],['user_id'=>$uid,'code'=>$couponCode,'coupon_id'=>$coupon['coupon_id']]
					// );
					return ['code'=>0,'msg'=>'可以使用','data'=>$couponInfo];
				}else if ($type == '3') {
					$amount = json_decode($couponInfo['amount'],true);

					$transaction = \Yii::$app->db->beginTransaction();
					try {
						$member = new Member(['id'=>$uid]);
						if ($amount['type'] == 'point') {
							$msg = $amount['amount'].'福分';
							$addRs = $member->editPoint($amount['amount'],'10','礼品券兑换');
							$data = ['type'=>'福分','value'=>$amount['amount']];
						}else if ($amount['type'] == 'money') {
							$msg = $amount['amount'].'伙购币';
							$addRs = $member->editMoney($amount['amount'],'2','礼品券兑换',$source);
							$data = ['type'=>'伙购币','value'=>$amount['amount']];
						}
						$userCodeRs = UserCoupons::updateAll([
								'status' => 1,
								'used_time' => $time
							],['id'=>$coupon['id']]
						);
						$codeRs = '';
						if ($couponInfo['num'] > 0) {
							$codeRs = CouponCode::updateAll([
									'status' => 1
								],['code'=>$couponCode,'coupon_id'=>$coupon['coupon_id']]
							);							
						}
						if (($addRs && $codeRs && $userCodeRs && $couponInfo['num'] > 0) || ($addRs && $userCodeRs)) {
							$transaction->commit();
							return ['code'=>0,'msg'=>'使用成功，增加'.$msg,'data'=>$data];
						}else{
							$transaction->rollBack();
							return ['code'=>406,'msg'=>'使用失败'];	
						}
					} catch (\Exception $e) {
						$transaction->rollBack();
						return ['code'=>406,'msg'=>'使用失败'];
					}
					
				}
			}
			return [];

		}

		/**
		 * 检测优惠券
		 * @param  array $coupons 优惠券码
		 * @return [type]              [description]
		 */
		public static function checkCoupons($uid,$coupons)
		{
			$userCoupon = UserCoupons::getCodeInfo($uid,array_keys($coupons));
			$valid = [];
			foreach ($userCoupon as $key => $value) {
				if ($value['code'] != $coupons[$value['id']] || $value['status'] != 0) {
					unset($userCoupon[$key]);
				}
			}
			$couponInfo = self::getCouponInfo($userCoupon);

			return  self::_checkCoupons($uid,'use',$couponInfo);
		}
		
		/**
		 * 检测优惠券
		 * @param  array $coupons 优惠券信息
		 * @return [type]          [description]
		 */
		private static function _checkCoupons($uid,$type,$coupons){
			$invalidList = '';
			$time = time();
			if ($type == 'use') {
				$cartInfo = CartModel::find()->where(['user_id'=>$uid,'is_buy'=>1])->asArray()->all();
				$total = $limitTotal = $buyUnitTotal = 0;
				foreach ($cartInfo as $key => $value) {
					$total += $value['nums'];
					if ($value['limit_nums'] > 0) {
						$limitTotal += $value['nums'];
					}
					if ($value['buy_unit'] > 1) {
						$buyUnitTotal += $value['nums'];
					}
				}
				if ($total == 0) {
					return [];
				}
			}

			foreach ($coupons as $key => $value) {
				//是否过期
				if ($value['info']['valid_type'] == 1) {
					if ($time < $value['info']['start_time'] || $time > $value['info']['end_time']) {
						$invalidList[] = $value['id'];
						unset($coupons[$key]);
					}	
				}else if ($value['info']['valid_type'] == '2') {
					if ( ($value['info']['valid']+$value['receive_time']) < $time) {
						$invalidList[] = $value['id'];
						unset($coupons[$key]);
					}
				}
				if ($type == 'use' && isset($coupons[$key])) {
					if ($value['info']['type'] == 3) {
						unset($coupons[$key]);
						continue;
					}
					$condition = json_decode($value['info']['condition'],true);
					$amount = json_decode($value['info']['amount'],true);
					$range = explode(',',$condition['range']);
					if ($value['info']['type'] == 1 && $total < $amount['money']) {
						unset($coupons[$key]);
						continue;
					}
					if (in_array(1,$range)) {
						if ($condition['need'] >= 0 && $condition['need'] <= $total ) {
							if ($value['info']['type'] == 1) {
								$coupons[$key]['deduction'] = $amount['money'];	
							}else if ($value['info']['type'] == 2) {
								$coupons[$key]['deduction'] = intval($total*(1-$amount['discount']/100));
							}
						}else{
							unset($coupons[$key]);	
						}
					}else{
						$coupons[$key]['deduction'] = 0;
						$invalid = 0;
						if (in_array(2,$range)) {
							if ($condition['need'] >= 0 && $condition['need'] <= $limitTotal) {
								if ($value['info']['type'] == 1) {
									$coupons[$key]['deduction'] = $amount['money'];	
								}else if ($value['info']['type'] == 2) {
									$coupons[$key]['deduction'] += intval($limitTotal*(1-$amount['discount']/100));
								}
							}else{
								$invalid = 1;
							}
						}
						if (in_array(3,$range)) {
							if ($condition['need'] >= 0 && $condition['need'] <= $buyUnitTotal ) {
								$invalid = 0;
								if ($value['info']['type'] == 1) {
									$coupons[$key]['deduction'] = $amount['money'];	
								}else if ($value['info']['type'] == 2) {
									$coupons[$key]['deduction'] += intval($buyUnitTotal*(1-$amount['discount']/100));
								}
							}else{
								$invalid = 1;
							}
						}
						if (in_array(4,$range)) {
							if ($condition['need'] >= 0) {
								$productsTotal = 0;
								foreach ($cartInfo as $k => $v) {
									if (in_array($v['product_id'],explode(',',$condition['products']))) {
										$productsTotal += $v['nums'];
									}
								}

								if ($productsTotal > 0 && $condition['need'] <= $productsTotal) {
									$invalid = 0;
									if ($value['info']['type'] == 1) {
										$coupons[$key]['deduction'] = $amount['money'];	
									}else if ($value['info']['type'] == 2) {
										$coupons[$key]['deduction'] += intval($productsTotal*(1-$amount['discount']/100));
									}	
								}else{
									$invalid = 1;
								}
							}else{
								$invalid = 1;		
							}
						}elseif (in_array(5,$range)) {
							$invalid = 1;
						}
						if ($invalid) {
							unset($coupons[$key]);
						}
					}
				}
			}

			if ($invalidList) {
				UserCoupons::updateAll(['status'=>3],['in','id',$invalidList]);
			}

			return $coupons;
		}


		/** 用户猴年马月 红包领取金额 信息
		 * @param $homeId
		 * @return array
		 */
		public static function hnmyMoneyInfo($uid,$homeId)
		{

			$packetsAndPrice = [
				'37' => 1,
				'38' => 2,
				'39' => 3,
				'40' => 4,
				'41' => 5,
				'42' => 6,
				'43' => 7,
				'44' => 8,
				'45' => 9,
				'46' => 10,
				'47' => 11,
				'48' => 12,
			];
//
//			$packetsAndPrice = [
//				'14' => 1,
//				'15' => 2,
//				'25' => 3,
//				'16' => 4,
//				'17' => 5,
//				'26' => 6,
//				'27' => 7,
//				'28' => 8,
//				'29' => 9,
//				'18' => 10,
//				'30' => 11,
//				'31' => 12,
//			];

			$packets = array_keys($packetsAndPrice);
			$sum = 0;
			if ($uid) {
				$coupons = UserCoupons::findByTableId($homeId)->where(['packet_id'=>$packets, 'user_id'=>$uid])->all();
				foreach($coupons as $coupon) {
					$sum += $packetsAndPrice[$coupon['packet_id']];
				}
			}
			$leftNum = 2016 - $sum;
			$leftNum = $leftNum > 0 ? $leftNum : 0;
			return ['num'=>$sum, 'left_num'=>$leftNum];
		}
	}