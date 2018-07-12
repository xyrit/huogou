<?php

	namespace app\services;

	use app\services\Pay;
	use app\services\Thirdpay;
	use app\services\Coupon;
	use app\helpers\MyRedis;
	use yii\helpers\Json;

	/**
	* 选择支付方式
	*/
	class Payway
	{
		private $payType = ['recharge'=>1,'consume'=>2,'pk_consume'=>3];
    	private $payName = ['debit'=>1,'credit'=>2,'platform'=>3,'commssion'=>4,'huogoucard'=>5,'exchage'=>'6','send'=>'7'];
    	const ORDER_COUPON_KEY = 'ORDER_COUPON';
    	const RECHARGE_ORDER_KEY = 'RECHARGE_ORDER';

		public function chooseway($uid,$payType,$payName,$payBank,$point,$payMoney,$source,$coupons=''){
			if (!$payType || !isset($this->payType[$payType])) {

				return array('code'=>10011,'message'=>'支付类型不正确');
			}

			$useCoupon = [];
			if ($coupons) {
				$_couponList = explode(",", $coupons);
				$couponList = [];
				foreach ($_couponList as $key => $value) {
					$c = explode("_",$value);
					$couponList[$c[0]] = $c[1];
				}

				$check = Coupon::checkCoupons($uid,$couponList);
				foreach ($check as $key => $value) {
					if ($value['info']['type'] ==  1) {
						$useCoupon['coupon1']['coupon'] = $value['code'];
						$useCoupon['coupon1']['deduction'] = $value['deduction'];
						$useCoupon['coupon1']['user_code_id'] = $value['id'];
						$useCoupon['coupon1']['num'] = $value['info']['num'];
						$useCoupon['coupon1']['canuse'] = $value['nums'];
						$useCoupon['coupon1']['coupon_id'] = $value['info']['id'];
					}
					if ($value['info']['type'] == 2) {
						$useCoupon['coupon2']['coupon'] = $value['code'];
						$useCoupon['coupon2']['deduction'] = $value['deduction'];
						$useCoupon['coupon2']['user_code_id'] = $value['id'];
						$useCoupon['coupon2']['num'] = $value['info']['num'];
						$useCoupon['coupon2']['canuse'] = $value['nums'];
						$useCoupon['coupon2']['coupon_id'] = $value['info']['id'];
					}
				}	
			}
			
			if ($payName == 'balance') {
	            // 余额消费
	            $pay = new Pay($uid);
	            $order = $pay->createPayOrder($source,$point,1,$payBank);
	            if ($useCoupon) {
	            	$redis = new MyRedis();
	            	$redis->hset(self::ORDER_COUPON_KEY,[$order=>json_encode($useCoupon)]);	
	            }
	            
	            if (!$order) {
	            	return array('code'=>10013,'message'=>'订单创建失败');
	            }
	            return array('code'=>100,'type'=>'balance','order'=>$order);
	        }else{


	        	if (!isset($this->payName[$payName])) {
					return array('code'=>10012,'message'=>'支付方式不正确');
		        }

	            $name = $this->payName[$payName];
	        	$type = $this->payType[$payType];
	        	if (intval($payMoney) <= 0) {
	        		return array('code'=>10014,'message'=>'充值金额不正确');
	        	}
	        	if (!$payBank) {
	        		return array('code'=>10015,'message'=>'银行不能为空');
	        	}
	            $createOrder = new Thirdpay();
	            $order = $createOrder->createRechargeOrder($uid,$payMoney,$type,$name,$payBank,$source,$point);
	            if ($useCoupon) {
	            	$redis = new MyRedis();
	            	$redis->hset(self::RECHARGE_ORDER_KEY,[$order=>json_encode($useCoupon)]);
	            }
	            if (!$order) {
	            	return array('code'=>10013,'message'=>'订单创建失败');
	            }
	            return array('code'=>100,'type'=>'third','order'=>$order);
	        }
		}


		public function choosePkPayway($pkPeriodId, $pkBuySize, $pkPostNum, $uid,$payType,$payName,$payBank,$point,$payMoney,$source,$coupons='')
		{
			if ($payType != 'pk_consume') {

				return array('code'=>10011,'message'=>'支付类型不正确');
			}

			$useCoupon = [];
			if ($coupons) {
				$_couponList = explode(",", $coupons);
				$couponList = [];
				foreach ($_couponList as $key => $value) {
					$c = explode("_",$value);
					$couponList[$c[0]] = $c[1];
				}

				$check = PkCoupon::checkCoupons($uid,$couponList,$pkPeriodId, $pkPostNum);
				foreach ($check as $key => $value) {
					if ($value['info']['type'] ==  1) {
						$useCoupon['coupon1']['coupon'] = $value['code'];
						$useCoupon['coupon1']['deduction'] = $value['deduction'];
						$useCoupon['coupon1']['user_code_id'] = $value['id'];
						$useCoupon['coupon1']['num'] = $value['info']['num'];
						$useCoupon['coupon1']['canuse'] = $value['nums'];
						$useCoupon['coupon1']['coupon_id'] = $value['info']['id'];
					}
					if ($value['info']['type'] == 2) {
						$useCoupon['coupon2']['coupon'] = $value['code'];
						$useCoupon['coupon2']['deduction'] = $value['deduction'];
						$useCoupon['coupon2']['user_code_id'] = $value['id'];
						$useCoupon['coupon2']['num'] = $value['info']['num'];
						$useCoupon['coupon2']['canuse'] = $value['nums'];
						$useCoupon['coupon2']['coupon_id'] = $value['info']['id'];
					}
				}
			}

			if ($payName == 'pk_balance') {
				// pk余额消费
				$pay = new PkPay($uid);
				$order = $pay->createPayOrder($pkPeriodId,$pkBuySize,$pkPostNum,$source,$point,1,$payBank);
				if ($useCoupon) {
					$redis = new MyRedis();
					$redis->hset(self::ORDER_COUPON_KEY,[$order=>json_encode($useCoupon)]);
				}

				if (!$order) {
					return array('code'=>10013,'message'=>'订单创建失败');
				}
				return array('code'=>100,'type'=>'balance','order'=>$order);
			}else{


				if (!isset($this->payName[$payName])) {
					return array('code'=>10012,'message'=>'支付方式不正确');
				}

				$name = $this->payName[$payName];
				$type = $this->payType[$payType];
				if (intval($payMoney) <= 0) {
					return array('code'=>10014,'message'=>'充值金额不正确');
				}
				if (!$payBank) {
					return array('code'=>10015,'message'=>'银行不能为空');
				}
				$createOrder = new Thirdpay();
				$order = $createOrder->createRechargeOrder($uid,$payMoney,$type,$name,$payBank,$source,$point);
				if ($useCoupon) {
					$redis = new MyRedis();
					$redis->hset(self::RECHARGE_ORDER_KEY,[$order=>Json::encode($useCoupon)]);
				}
				if (!$order) {
					return array('code'=>10013,'message'=>'订单创建失败');
				}
				if ($payType == 'pk_consume') {
					$pkPay = new PkPay($uid);
					$payOrder = $pkPay->createPayOrder($pkPeriodId, $pkBuySize, $pkPostNum, $source, $point, 1, $payBank, $order);
				}
				return array('code'=>100,'type'=>'third','order'=>$order);
			}
		}

	}