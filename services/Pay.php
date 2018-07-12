<?php
/**
 * User: hechen
 * Date: 15/10/9
 * Time: 上午9:50
 */
namespace app\services;

use app\helpers\Brower;
use app\models\LotteryCompute;
use yii;
use app\models\Cart as CartModel;
use app\models\CurrentPeriod;
use app\models\Period as PeriodModel;
use app\models\Product as ProductModel;
use app\models\PaymentOrderDistribution;
use app\models\PaymentOrderItemDistribution;
use app\models\PeriodBuylistDistribution;
use app\models\UserBuylistDistribution;
use app\models\Invite;
use app\models\Fund;
use app\models\User as UserModel;
use app\helpers\MyRedis;
use app\models\Coupon as CouponModel;
use app\models\UserCoupons;

/**
* 支付
*/
class Pay
{
	public $userId;
	public $total;
	public $userHomeId;
	public $cart;
	public $buyNums = 0;
	public $realMoney = 0;
	public $realPoint = 0;
	public $userInfo;
	public $orderInfo;
	public $orderItems;
	public $order;
	public $redis;
	public $codes;
	public $coupons;
	public $fullPeriods;//本次购买已完结的期数

	const PERIOD_ALL_CODE_KEY = 'PERIOD_ALL_CODE_';  // set 码表  _periodid
	const PERIOD_SALED_KEY = 'PERIOD_SALED_';  //set 已售出码 _period	
	const PERIOD_BUY_LIST_KEY = 'PERIOD_BUY_LIST_'; //hash 期数购买记录 _period  orderid->信息
	const USER_BUY_LIST_KEY = 'USER_BUY_LIST_';    // hash period_id->codes _orderid  每个订单一条
	const USER_BUY_SUM_KEY = 'USER_BUY_SUM';    // SET 用户购买总数
	const GET_CODE_LIST_KEY = 'GET_CODE_LIST_';  //list  _periodid
	const ORDER_HAND_LIST_KEY = 'ORDER_HAND_LIST'; //set
	const ORDER_LIST_KEY = 'ORDER_LIST';  //hash类型，order->orderinfo
	const ORDER_ITEMS_KEY = 'ORDER_ITEMS_';  //hash 订单详情 period_id->info _orderid
	const NEW_PERIOD_KEY = 'NEW_PERIOD';//set 开始新一期
	const PERIOD_ORDERS_KEY = 'PERIOD_ORDERS_'; //hash  _period  期数订单，用于数据同步
	const POINT_USE_KEY = 'POINT_USE_'; //hash _order  period_id => money,point
	const THIRD_PAY_KEY = 'THIRD_PAY_'; // set，recharge_ordierid => pay_orderid
	const ORDER_COUPON_KEY = 'ORDER_COUPON';

	public function __construct($uid){
		$this->redis = new MyRedis();
		$this->userId = $uid;
	}

	/**
	 * 创建支付订单
	 * @return [type] [description]
	 */
	public function createPayOrder($source,$point,$payType='1',$bank='',$recharge_orderid=''){
		$userInfo = User::baseInfo($this->userId);

		if (!$userInfo) {
			return false;
		}

		$time = microtime(true);
		$from = Brower::whereFrom();
		if ($from == 2) {
			$orderSub = 'D';
		} else {
			$orderSub = 'H';
		}
		$orderNum = PaymentOrderDistribution::generateOrderId($userInfo['home_id'], $orderSub);

		$cartInfo = $this->_getCartInfo();

		if ($cartInfo) {
			$userIp = ip2long(Yii::$app->request->userIp);
			if ($recharge_orderid) {
				$rechargeOrderInfo = Thirdpay::getOrderByNo($recharge_orderid);
				$userIp = $rechargeOrderInfo['ip'];
			}
			$orderInfo = array(
					'id' => $orderNum,
					'user_id' => $this->userId,
					'status' => 0,
					'payment' => $payType,
					'bank' => $bank,
					'money' => 0,
					'point' => 0,
					'total' => $this->_getTotal(),
					'user_point' => $point,
					'ip' => $userIp,
					'source' => $source,
					'create_time' => $time,
					'buy_time' => $time,
					'recharge_orderid' => $recharge_orderid,
					'user_account' => $userInfo['nickname'] ? : ($userInfo['phone'] ? : $userInfo['email']),
					'spread_source' => $userInfo['spread_source']
				);
			$order = $this->redis->hset(self::ORDER_LIST_KEY,array($orderNum=>yii\helpers\Json::encode($orderInfo)));
			if ($order) {
				$itmes = array();
				foreach ($cartInfo as $key => $value) {
					$itmes[$value['period_id']] = yii\helpers\Json::encode(array(
						'payment_order_id' => $orderNum,
						'product_id' => $value['product_id'],
						'period_id' => $value['period_id'],
						'period_number' => $value['period_number'],
						'user_id' => $value['user_id'],
						'post_nums' => $value['nums'],
						'nums' => 0,
						'codes'=>'',
						'item_buy_time'=>'',
						'source'=>$source,
					));
				}
				$this->redis->hset(self::ORDER_ITEMS_KEY.$orderNum,$itmes);
			}
			if ($recharge_orderid) {
				$this->redis->set(self::THIRD_PAY_KEY.$recharge_orderid,$orderNum,3600);
			}
		}
		return $orderNum;
	}

	/**
	 * 支付
	 * @param  int $uid 用户ID
	 * @return bool      [description]
	 */
	public function payByBalance($order){

		set_time_limit(0);

		try {
			$this->order = $order;
			$this->userInfo = User::baseInfo($this->userId);

			if (!$this->userInfo) {
				return false;
			}
			// 获取订单信息
			$this->orderInfo = yii\helpers\Json::decode($this->redis->hget(self::ORDER_LIST_KEY,$this->order),true);
			if (!$this->orderInfo) {
				$data['code'] = 201;
				$data['message'] = '订单不存在';
				return $data;
			}

			$isexist = $this->redis->sset(self::ORDER_HAND_LIST_KEY,$this->order);
			if ($isexist == 0) {
				return false;
			}

			if ($this->orderInfo['status'] == '1') {
				$data['code'] = 100;
				$data['message'] = '订单成功';
				return $data;
			}
			if ($this->orderInfo && $this->orderInfo['total'] > 0) {
				// 计算需要的金额
				$usermoney = $this->userInfo['money'];
				$userPoint = $this->userInfo['point'];
				$pointMoney = 0; //积分转换成的金额
				if ($this->orderInfo['user_point'] > 0 && $this->orderInfo['user_point'] % 100 == 0 && $this->orderInfo['user_point'] <= $userPoint) {
					$pointMoney = $this->orderInfo['user_point']/100;
				}
				//获取订单优惠券信息
				$this->coupons = yii\helpers\Json::decode($this->redis->hget(self::ORDER_COUPON_KEY,$this->order),true);
				$this->redis->hdel(self::ORDER_COUPON_KEY,$this->order);
				$deduction = 0;
				if ($this->coupons) {
					foreach ($this->coupons as $key => $value) {
						$deduction += $value['deduction'];
					}
				}

				// 余额+积分+优惠券优惠金额>订单总额
				if (($usermoney+$pointMoney+$deduction) >= $this->orderInfo['total']) {
					$needMoney = $this->orderInfo['total'];
					$needPoint = 0;
					if ($pointMoney > 0) {
						$needMoney = $this->orderInfo['total']-$pointMoney;
						$needPoint = $pointMoney*100;
					}
					//总需金额减去两张优惠券优惠金额
					$needMoney -= $deduction;
					//扣除用户余额及积分
					$userMoneyPoint = ['point'=>$this->userInfo['point'],'money'=>$this->userInfo['money']];
					$rs = $this->_deduction($userMoneyPoint,$needMoney,$needPoint);
					//扣除成功
					if ($rs) {
						$this->codes = $this->redis->hget(self::USER_BUY_LIST_KEY.$this->order,'all');
						if (!$this->codes) {
							$this->_getCodes();
						}else{
							foreach ($this->codes as $key => &$value) {
								$value = yii\helpers\Json::decode($value,true);
							}
						}
						$confirm = $this->_confirmMoney($needMoney,$needPoint,$deduction);
						if ($confirm) {

							$this->updateUserBuy($this->order);
							$this->addPointLog($this->userId,$pointMoney);
							// Fund::addFund($this->realMoney*0.01+($this->realPoint)/100*0.01);
						}else{
							foreach ($this->codes as $key => $value) {
								$this->rollBack($value['period_id']);
							}
							$this->orderInfo['status'] = 2;
							$this->orderInfo['buy_time'] = microtime(true);
							$this->redis->hset(self::ORDER_LIST_KEY,array($this->order=>yii\helpers\Json::encode($this->orderInfo)));
						}
					}else{
						$this->orderInfo['status'] = 2;
						$this->orderInfo['buy_time'] = microtime(true);
						$this->redis->hset(self::ORDER_LIST_KEY,array($this->order=>yii\helpers\Json::encode($this->orderInfo)));

					}
				}else{
					$this->orderInfo['status'] = 2;
					$this->orderInfo['buy_time'] = microtime(true);
					$this->redis->hset(self::ORDER_LIST_KEY,array($this->order=>yii\helpers\Json::encode($this->orderInfo)));

				}
				$this->_orderDataToMysql();
				$this->payOffCommission($pointMoney,$deduction);//发放佣金
				$this->_lotteryDraw();
			}

			$this->redis->sdel(self::ORDER_HAND_LIST_KEY,$this->order);
		} catch (\Exception $e) {
			$echo = $e->getMessage().'_line'.$e->getLine().date('Y-m-d H:i:s')."\r\n";
			file_put_contents('pay.txt',$echo,FILE_APPEND);
		}
	}

	/**
	 * 支付
	 * @param  int $uid 用户ID
	 * @return bool      [description]
	 */
	public function payByBalance2($order){

		set_time_limit(0);

		try {
			$this->order = $order;
			$this->userInfo = User::baseInfo($this->userId);

			if (!$this->userInfo) {
				return false;
			}
			// 获取订单信息
			$this->orderInfo = yii\helpers\Json::decode($this->redis->hget(self::ORDER_LIST_KEY,$this->order),true);
			if (!$this->orderInfo) {
				$data['code'] = 201;
				$data['message'] = '订单不存在';
				return $data;
			}

			$isexist = $this->redis->sset(self::ORDER_HAND_LIST_KEY,$this->order);
			if ($isexist == 0) {
				return false;
			}

			if ($this->orderInfo['status'] == '1') {
				$data['code'] = 100;
				$data['message'] = '订单成功';
				return $data;
			}
			if ($this->orderInfo && $this->orderInfo['total'] > 0) {
				// 计算需要的金额
				$usermoney = $this->userInfo['money'];
				$userPoint = $this->userInfo['point'];
				$pointMoney = 0; //积分转换成的金额
				if ($this->orderInfo['user_point'] > 0 && $this->orderInfo['user_point'] % 100 == 0 && $this->orderInfo['user_point'] <= $userPoint) {
					$pointMoney = $this->orderInfo['user_point']/100;
				}
				//获取订单优惠券信息
				$this->coupons = yii\helpers\Json::decode($this->redis->hget(self::ORDER_COUPON_KEY,$this->order),true);
				$this->redis->hdel(self::ORDER_COUPON_KEY,$this->order);
				$deduction = 0;
				if ($this->coupons) {
					foreach ($this->coupons as $key => $value) {
						$deduction += $value['deduction'];
					}
				}

				// 余额+积分+优惠券优惠金额>订单总额
				if (($usermoney+$pointMoney+$deduction) >= $this->orderInfo['total']) {
					$needMoney = $this->orderInfo['total'];
					$needPoint = 0;
					if ($pointMoney > 0) {
						$needMoney = $this->orderInfo['total']-$pointMoney;
						$needPoint = $pointMoney*100;
					}
					//总需金额减去两张优惠券优惠金额
					$needMoney -= $deduction;
					//扣除用户余额及积分
					$userMoneyPoint = ['point'=>$this->userInfo['point'],'money'=>$this->userInfo['money']];
					$rs = $this->_deduction($userMoneyPoint,$needMoney,$needPoint);
					//扣除成功
					if ($rs) {
						$this->codes = $this->redis->hget(self::USER_BUY_LIST_KEY.$this->order,'all');
						if (!$this->codes) {
							$this->_getCodes2();
						}else{
							foreach ($this->codes as $key => &$value) {
								$value = yii\helpers\Json::decode($value,true);
							}
						}
						$confirm = $this->_confirmMoney($needMoney,$needPoint,$deduction);
						if ($confirm) {

							$this->updateUserBuy($this->order);
							$this->addPointLog($this->userId,$pointMoney);
							// Fund::addFund($this->realMoney*0.01+($this->realPoint)/100*0.01);
						}else{
							foreach ($this->codes as $key => $value) {
								$this->rollBack($value['period_id']);
							}
							$this->orderInfo['status'] = 2;
							$this->orderInfo['buy_time'] = microtime(true);
							$this->redis->hset(self::ORDER_LIST_KEY,array($this->order=>yii\helpers\Json::encode($this->orderInfo)));
						}
					}else{
						$this->orderInfo['status'] = 2;
						$this->orderInfo['buy_time'] = microtime(true);
						$this->redis->hset(self::ORDER_LIST_KEY,array($this->order=>yii\helpers\Json::encode($this->orderInfo)));

					}
				}else{
					$this->orderInfo['status'] = 2;
					$this->orderInfo['buy_time'] = microtime(true);
					$this->redis->hset(self::ORDER_LIST_KEY,array($this->order=>yii\helpers\Json::encode($this->orderInfo)));

				}
				$this->_orderDataToMysql();
				$this->payOffCommission($pointMoney,$deduction);//发放佣金
				$this->_lotteryDraw();
			}

			$this->redis->sdel(self::ORDER_HAND_LIST_KEY,$this->order);
		} catch (\Exception $e) {
			$echo = $e->getMessage().'_line'.$e->getLine().date('Y-m-d H:i:s')."\r\n";
			file_put_contents('pay.txt',$echo,FILE_APPEND);
		}
	}
	/**
	 * 获取伙购码
	 * @return [type] [description]
	 */
	private function _getCodes2(){
		$userBuy = array();
		$db = \Yii::$app->db;
		//订单详情
		$this->orderItems = $this->redis->hget(self::ORDER_ITEMS_KEY.$this->order,'all');

		foreach ($this->orderItems as $key => $value) {
			$value = yii\helpers\Json::decode($value,true);
			$this->orderItems[$key] = $value;
			$periodId[] = $value['period_id'];
		}
		//当期期数信息
		$_periodInfo = CurrentPeriod::find()->where(['in','id',$periodId])->asArray()->all();
		$periodInfo = array();
		foreach ($_periodInfo as $key => $value) {
			$periodInfo[$value['id']] = $value;
		}
		foreach ($this->orderItems as $key => $value) {
			$codes = array();

			if(!isset($periodInfo[$key])){
				// 自动获取最新的一期。
				$newPeriod= CurrentPeriod::find()->where(['product_id'=>$value['product_id']])->asArray()->one();
				if($newPeriod){
					$oldkey=$key;
					unset($this->orderItems[$oldkey]);
					$key=$newPeriod['id'];
					$value['period_id']=$key;
					$value['period_number']=$newPeriod['period_number'];
					$this->orderItems[$key]=$value;
					$periodInfo[$key]=$newPeriod;
					$this->redis->hset(self::ORDER_ITEMS_KEY.$this->order,array($key=>yii\helpers\Json::encode($value)));
					$this->redis->hdel(self::ORDER_ITEMS_KEY.$this->order,$oldkey);
				}

			}

			$userBuy[$key] = array(
					'period_id' => $key,
					'period_number' => $value['period_number'],
					'product_id' => $value['product_id'],
					'count' => 0,
					'left_num' => 0,
					'codes' => $codes
				);

			if (isset($periodInfo[$key])) {
				$userBuy[$key]['table_id'] = $periodInfo[$key]['table_id'];
				$saledNumsKey = self::PERIOD_SALED_KEY.$key;
				$codeKey = self::PERIOD_ALL_CODE_KEY.$key;

				if ($this->redis->slen($codeKey) > 0) {
					$listKey = self::GET_CODE_LIST_KEY.$value['period_id'];
					$this->redis->lset($listKey,$this->userId,'false');

					$canGetCode = $this->beginGetCode($key,$value['post_nums']);

					while (!$canGetCode) {
						if ($this->redis->slen($codeKey) == 0) {
							$this->redis->del(self::GET_CODE_LIST_KEY.$key);
							break;
						}
						usleep(50);
						$canGetCode = $this->beginGetCode($key,$value['post_nums']);
					}


					$codes = array_filter($this->redis->sget(self::PERIOD_ALL_CODE_KEY.$key,$value['post_nums']));
					$left_num = $this->redis->slen($codeKey);
					$this->redis->lmdel(self::GET_CODE_LIST_KEY.$key,$this->userId,1); //删除该user 获取码的资格
					$this->redis->sset($saledNumsKey,$codes);
					$userBuy[$key]['count'] = count($codes);
					$userBuy[$key]['codes'] = $codes;
					$userBuy[$key]['left_num'] = $left_num;

					CurrentPeriod::updateAll(
						[
							'sales_num'=>$this->redis->slen($saledNumsKey),
							'left_num'=>$left_num,
							'progress'=>round($this->redis->slen($saledNumsKey)/$periodInfo[$key]['price'],6)*100000
						],[
							'id'=>$key
						]
					);
				}else{
					$this->redis->del(self::GET_CODE_LIST_KEY.$key);
				}
			}else{
				$this->redis->del(self::GET_CODE_LIST_KEY.$key);
				$completePeriodInfo = PeriodModel::find()->where(['id'=>$key])->asArray()->one();
				$userBuy[$key]['table_id'] = $completePeriodInfo['table_id'];
			}
		}

		$data = array();
		$totalBuy = 0;
		foreach ($userBuy as $key => $value) {
			$data[$key] = yii\helpers\Json::encode($value);
			$totalBuy += $value['count'];
		}

		$this->redis->hset(self::USER_BUY_LIST_KEY.$this->order,$data);
		if ($totalBuy > 0) {
			if ($this->coupons) {
				$this->orderInfo['deduction1'] = isset($this->coupons['coupon1']) ? $this->coupons['coupon1']['deduction'] : '';
				$this->orderInfo['coupon1'] = isset($this->coupons['coupon1']) ? $this->coupons['coupon1']['coupon'] : '';
				$this->orderInfo['deduction2'] = isset($this->coupons['coupon2']) ? $this->coupons['coupon2']['deduction'] : '';
				$this->orderInfo['coupon2'] = isset($this->coupons['coupon2']) ? $this->coupons['coupon2']['coupon'] : '';
				foreach ($this->coupons as $ck => $cv) {
					$tableId = substr($this->userInfo['home_id'],0,3);
					$userCouponsModel = new UserCoupons($tableId);
					$userCouponsTableName = $userCouponsModel->tableName();
					if ($cv['canuse'] == 1) {
						UserCoupons::updateAll([
								'status'=>1,
								'used_time'=>time()
							],['id'=>$cv['user_code_id'],'code'=>$cv['coupon']]
						);
						$sql = "update ".$userCouponsTableName." set status = 1 and used_time = '".time()."' where id = '".$cv['user_code_id']."' and code = '".$cv['coupon']."'";
						$db->createCommand($sql)->execute();
					}else{
						// UserCoupons::updateAll(
						// 		['nums' => $cv['canuse']-1],['id'=>$cv['user_code_id'],'code'=>$cv['coupon']]
						// 	);
					}
					if ($cv['num'] > 0) {
						UserCoupons::updateAll([
								'status'=>1
							],['code'=>$cv['coupon'],'coupon_id'=>$cv['coupon_id']]
						);			
					}
				}
			}
		}

		// $this->codes = $userBuy;
	}
	/**
	 * 获取伙购码
	 * @return [type] [description]
	 */
	private function _getCodes(){
		$userBuy = array();
		$db = \Yii::$app->db;
		//订单详情
		$this->orderItems = $this->redis->hget(self::ORDER_ITEMS_KEY.$this->order,'all');
		foreach ($this->orderItems as $key => $value) {
			$value = yii\helpers\Json::decode($value,true);
			$this->orderItems[$key] = $value;
			$periodId[] = $value['period_id'];
		}
		//当期期数信息
		$_periodInfo = CurrentPeriod::find()->where(['in','id',$periodId])->asArray()->all();
		$periodInfo = array();
		foreach ($_periodInfo as $key => $value) {
			$periodInfo[$value['id']] = $value;
		}
		foreach ($this->orderItems as $key => $value) {
			$codes = array();
			$userBuy[$key] = array(
				'period_id' => $key,
				'period_number' => $value['period_number'],
				'product_id' => $value['product_id'],
				'count' => 0,
				'left_num' => 0,
				'codes' => $codes
			);
			if (isset($periodInfo[$key])) {
				$userBuy[$key]['table_id'] = $periodInfo[$key]['table_id'];
				$saledNumsKey = self::PERIOD_SALED_KEY.$key;
				$codeKey = self::PERIOD_ALL_CODE_KEY.$key;
				if ($this->redis->slen($codeKey) > 0) {
					$listKey = self::GET_CODE_LIST_KEY.$value['period_id'];
					$this->redis->lset($listKey,$this->userId,'false');

					$canGetCode = $this->beginGetCode($key,$value['post_nums']);
					while (!$canGetCode) {
						if ($this->redis->slen($codeKey) == 0) {
							$this->redis->del(self::GET_CODE_LIST_KEY.$key);
							break;
						}
						usleep(50);
						$canGetCode = $this->beginGetCode($key,$value['post_nums']);
					}

					$codes = array_filter($this->redis->sget(self::PERIOD_ALL_CODE_KEY.$key,$value['post_nums']));
					$left_num = $this->redis->slen($codeKey);
					$this->redis->lmdel(self::GET_CODE_LIST_KEY.$key,$this->userId,1);
					$this->redis->sset($saledNumsKey,$codes);
					$userBuy[$key]['count'] = count($codes);
					$userBuy[$key]['codes'] = $codes;
					$userBuy[$key]['left_num'] = $left_num;

					CurrentPeriod::updateAll(
						[
							'sales_num'=>$this->redis->slen($saledNumsKey),
							'left_num'=>$left_num,
							'progress'=>round($this->redis->slen($saledNumsKey)/$periodInfo[$key]['price'],6)*100000
						],[
							'id'=>$key
						]
					);
				}else{
					$this->redis->del(self::GET_CODE_LIST_KEY.$key);
				}
			}else{
				$this->redis->del(self::GET_CODE_LIST_KEY.$key);
				$completePeriodInfo = PeriodModel::find()->where(['id'=>$key])->asArray()->one();
				$userBuy[$key]['table_id'] = $completePeriodInfo['table_id'];
			}
		}
		$data = array();
		$totalBuy = 0;
		foreach ($userBuy as $key => $value) {
			$data[$key] = yii\helpers\Json::encode($value);
			$totalBuy += $value['count'];
		}
		$this->redis->hset(self::USER_BUY_LIST_KEY.$this->order,$data);
		if ($totalBuy > 0) {
			if ($this->coupons) {
				$this->orderInfo['deduction1'] = isset($this->coupons['coupon1']) ? $this->coupons['coupon1']['deduction'] : '';
				$this->orderInfo['coupon1'] = isset($this->coupons['coupon1']) ? $this->coupons['coupon1']['coupon'] : '';
				$this->orderInfo['deduction2'] = isset($this->coupons['coupon2']) ? $this->coupons['coupon2']['deduction'] : '';
				$this->orderInfo['coupon2'] = isset($this->coupons['coupon2']) ? $this->coupons['coupon2']['coupon'] : '';
				foreach ($this->coupons as $ck => $cv) {
					$tableId = substr($this->userInfo['home_id'],0,3);
					$userCouponsModel = new UserCoupons($tableId);
					$userCouponsTableName = $userCouponsModel->tableName();
					if ($cv['canuse'] == 1) {
						UserCoupons::updateAll([
							'status'=>1,
							'used_time'=>time()
						],['id'=>$cv['user_code_id'],'code'=>$cv['coupon']]
						);
						$sql = "update ".$userCouponsTableName." set status = 1 and used_time = '".time()."' where id = '".$cv['user_code_id']."' and code = '".$cv['coupon']."'";
						$db->createCommand($sql)->execute();
					}else{
						// UserCoupons::updateAll(
						// 		['nums' => $cv['canuse']-1],['id'=>$cv['user_code_id'],'code'=>$cv['coupon']]
						// 	);
					}
					if ($cv['num'] > 0) {
						UserCoupons::updateAll([
							'status'=>1
						],['code'=>$cv['coupon'],'coupon_id'=>$cv['coupon_id']]
						);
					}
				}
			}
		}
		// $this->codes = $userBuy;
	}


	/**
	 * 更新购买
	 * @param  array $codes [description]
	 * @return [type]        [description]
	 */
	private function updateUserBuy()
	{
		//用户购买记录
		$userBuyField = ['user_id','product_id','period_id','buy_num','buy_time'];
		$db = \Yii::$app->db;
		$userBuyList = new UserBuylistDistribution($this->userInfo['home_id']);

		$this->codes = $this->redis->hget(self::USER_BUY_LIST_KEY.$this->order,'all');
		foreach ($this->codes as $key => &$value) {
			$value = yii\helpers\Json::decode($value,true);
		}

		$status = 2;

		$updateUserBuy = true;
		foreach ($this->codes as $key => $v) {
			$_time = explode('.',microtime(true));
			$preTime = $_time[0];
			$lastTime = isset($_time[1]) ? substr($_time[1], 0, 3) : '0';
			$time = $preTime.'.'.str_pad($lastTime,3,0,STR_PAD_RIGHT);
			$codesStr = $v['codes'] ? implode(',',$v['codes']) : '';
			$newItem = $this->orderItems[$v['period_id']];
			$newItem['nums'] = $v['count'];
			$newItem['codes'] = $codesStr;
			$newItem['item_buy_time'] = $time;
			$updateOrderItem = $this->redis->hset(self::ORDER_ITEMS_KEY.$this->order,array($v['period_id']=>yii\helpers\Json::encode($newItem)));

			if (!empty($v['codes']) && $v['table_id']) {

				//更新用户购买记录入库
				$exist = UserBuylistDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['user_id'=>$this->userId,'period_id'=>$v['period_id']])->asArray()->one();
				if (empty($exist)) {
					$userBuyValue = [];
					$userBuyValue[] = [$this->userId,$v['product_id'],$v['period_id'],$v['count'],$time];
					$userBuy = $db->createCommand()->batchInsert($userBuyList::tableName(),$userBuyField,$userBuyValue)->execute();
				}else{
					$buy_num = $exist['buy_num']+$v['count'];
					$userBuy = $db->createCommand()->update($userBuyList::tableName(),['buy_num'=>$buy_num,'buy_time'=>$time],['user_id'=>$this->userId,'period_id'=>$v['period_id']])->execute();
				}
				$periodBuyList['product_id'] = $v['product_id'];
				$periodBuyList['period_id'] = $v['period_id'];
				$periodBuyList['user_id'] = $this->userId;
				$periodBuyList['buy_num'] = $v['count'];
				$periodBuyList['codes'] = $codesStr;
				$periodBuyList['ip'] = $this->orderInfo['ip'];
				$periodBuyList['source'] = $this->orderInfo['source'];
				$periodBuyList['buy_time'] = $time;
				$periodBuy = $this->redis->hset(self::PERIOD_BUY_LIST_KEY.$v['period_id'],array($this->order=>yii\helpers\Json::encode($periodBuyList)));

				if ($v['count'] > 0) {
					//期数购买记录入库
					$periodBuyListModel = new PeriodBuylistDistribution($v['table_id']);
					$periodBuyListModel->setAttributes($periodBuyList, false);
					$savePeriodBuy = $periodBuyListModel->save(false);
					if ($savePeriodBuy) {
						$this->redis->hdel(self::PERIOD_BUY_LIST_KEY.$v['period_id'], $this->order);
					}
				}

				if ($userBuy) {
					$this->redis->incrBy(self::USER_BUY_SUM_KEY, $v['count']);
				} else {
					$updateUserBuy = false;
				}
				$status = 1;
			}

			if ($v['left_num'] <= 0 && $v['count'] > 0) {
				$period = CurrentPeriod::find()->where(['product_id'=>$v['product_id'],'id'=>$v['period_id']])->asArray()->one();
				$this->newPeriod($period,$time);
			}

		}

		CartModel::deleteAll(['user_id'=>$this->userId,'is_buy'=>1]);

		//更新订单状态
		$this->orderInfo['status'] = $status;
		$this->orderInfo['buy_time'] = $time;
		$this->orderInfo['money'] = $this->realMoney;
		$this->orderInfo['point'] = $this->realPoint;
		$this->redis->hset(self::ORDER_LIST_KEY,array($this->order=>yii\helpers\Json::encode($this->orderInfo)));

		if ($updateUserBuy) {
			$this->redis->del(self::USER_BUY_LIST_KEY.$this->order);
		}
	}

	/** 相关信息入库
	 * @throws yii\db\Exception
	 */
	private function _orderDataToMysql()
	{
		$transaction= Yii::$app->db->beginTransaction();

		$orderInfo = $this->orderInfo;
		$orderItems = $this->redis->hget(self::ORDER_ITEMS_KEY.$orderInfo['id'],'all');
		$orderTableId = PaymentOrderDistribution::getTableIdByOrderId($orderInfo['id']);
		//订单
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
		$result = $orderSave->save(false);

		//订单详情
		$db = \Yii::$app->db;
		$orderItemField = ['payment_order_id','product_id','period_id','period_number','user_id','post_nums','nums','codes','item_buy_time','source'];
		$orderItemValue = [];
		$periodBuyTime = [];

		foreach ($orderItems as $key => $value) {
			$v = yii\helpers\Json::decode($value,true);
			$orderItemUserId = isset($v['user_id']) ? $v['user_id'] : 0;
			$orderItemSource = isset($v['source']) ? $v['source'] : 0;
			$orderItemValue[] = [$v['payment_order_id'],$v['product_id'],$v['period_id'],$v['period_number'],$orderItemUserId,$v['post_nums'],$v['nums'],$v['codes'],$v['item_buy_time'],$orderItemSource];
			$periodBuyTime[$v['period_id']] = $v['item_buy_time'];
		}

		$orderItem = new PaymentOrderItemDistribution($orderTableId);
		$itemsResult = $db->createCommand()->batchInsert($orderItem::tableName(),$orderItemField,$orderItemValue)->execute();

		if ($result && $itemsResult) {
			$transaction->commit();
			$this->redis->hdel(self::ORDER_LIST_KEY,$orderInfo['id']);
			$this->redis->del(self::ORDER_ITEMS_KEY.$orderInfo['id']);
		}else{
			$transaction->rollback();//如果操作失败, 数据回滚
		}

	}

	/**
	 *  立即开奖
	 */
	private function _lotteryDraw()
	{
		if ($this->fullPeriods) {
			foreach($this->fullPeriods as $period) {
				$type = Period::dayTypeByEndTime($period['end_time']);
				if ($type=='none') {
					Lottery::draw($period, true, false);
				}
			}
		}
	}

	/**
	 * 扣费
	 * @param  array $source 现有余额及积分
	 * @param  int $money  扣除的金额
	 * @param  int $point  扣除的积分
	 * @return [type]         [description]
	 */
	private function _deduction($userMoneyPoint,$money,$point){
		if ($money == 0 && $point == 0) {
			return 1;	
		}
		$surplusMoney = $userMoneyPoint['money']-$money;
		$surplusPoint = $userMoneyPoint['point']-$point;
		return UserModel::updateAll(['money'=>$surplusMoney,'point'=>$surplusPoint],['id'=>$this->userId]);
	}

	private function _confirmMoney($money,$point,$deduction){
		$userMoneyPoint = $this->_getUserMoneyPoint();
		$buyNums = 0;
		$codes = $this->redis->hget(self::USER_BUY_LIST_KEY.$this->order,'all');
		foreach ($codes as $key => $value) {
			$v = yii\helpers\Json::decode($value,true);
			$buyNums += $v['count'];
		}

		if ($buyNums <= ($money+$point/100+$deduction)) {
			$buyNums -= $deduction;
			$buyNums = $buyNums > 0 ? $buyNums : 0;
			if ($buyNums < intval($point/100)) {
				$this->realPoint = $buyNums*100;
				$this->realMoney = 0;
			}else{
				$this->realPoint = $point;
				$this->realMoney = $buyNums-($point/100);
			}
			$addPoint = $point-$this->realPoint;
			$addMoney = $money-$this->realMoney;
			$surplusMoney = $userMoneyPoint['money']+$addMoney;
			$surplusPoint = $userMoneyPoint['point']+$addPoint;
			UserModel::updateAll(['money'=>$surplusMoney,'point'=>$surplusPoint],['id'=>$this->userId]);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 添加佣金
	 * @param  array $codes      码
	 * @param  int $pointMoney 积分/100
	 * @return [type]             [description]
	 */
	private function payOffCommission($pointMoney,$deduction)
	{
		if (empty($this->codes)) {
			return false;
		}
		foreach ($this->codes as $key => $value) {
			$count = $value['count'];
			if ($deduction > 0) {
				if ($deduction >= $count) {
					$deduction -= $count;
					$count = 0;
				}else{
					$count -= $deduction;
					$deduction = 0;
				}
			}
			if ($pointMoney > 0) {
				if ($pointMoney >= $count) {
					$pointMoney = $pointMoney - $count;
					$count = 0;
				}else{
					$count = $count-$pointMoney;
					$pointMoney = 0;
				}
			}
			if ($count > 0) {
				Invite::commissionPayoff($this->userId, $count, $key);
			}
		}
	}

	/**
	 * 添加福分记录
	 * @param array $codes      码
	 * @param int $pointMoney 积分/100
	 */
	private function addPointLog($uid,$pointMoney){
		$exp = 0;
		$pointLog = new Member(['id' => $uid]);
		$productIds = array();
		foreach ($this->codes as $key => $value) {
			$productIds[] = $value['product_id'];
		}
		$productInfo = Product::info($productIds);
		$deduction = 0;
		if ($this->coupons) {
			foreach ($this->coupons as $key => $value) {
				$deduction += $value['deduction'];
			}
		}

		foreach ($this->codes as $key => $value) {
			$count = $value['count'];
			$usePoint = 0;
			if ($pointMoney > 0) {
				if ($pointMoney >= $count) {
					$pointMoney = $pointMoney - $count;
					$usePoint = $count;
					$count = 0;
				}else{
					$count = $count-$pointMoney;
					$usePoint = $pointMoney;
					$pointMoney = 0;
				}
				$pointLog->editPoint((0-$usePoint*100), 1, '伙购商品编码('.$productInfo[$value['product_id']]['bn'].')福分抵扣','buy');
			}
			$exp += $count;
			if (($count-$deduction) > 0) {
				$pointLog->editPoint(($count-$deduction), 1, '伙购商品编码('.$productInfo[$value['product_id']]['bn'].')支付'.($count-$deduction).'元获得'.($count-$deduction).'福分');
				$deduction = 0;
			}else{
				$deduction -= $count;
			}
			$this->redis->hset(self::POINT_USE_KEY.$this->order,array($key=>yii\helpers\Json::encode(array('money'=>$count,'point'=>$usePoint*100))));
		}	
		$pointLog->editExperience($exp*10,'1','购买商品');
	}
	/**
	 * 开始新一期
	 * @param  array $period 当期数据
	 * @return [type]         [description]
	 */
	public function newPeriod($period,$time){
		if (!$period) {
			return false;
		}
		$exist = $this->redis->sset(self::NEW_PERIOD_KEY,$period['id']);

		if ($exist) {
			$productInfo = ProductModel::find()->where(['id'=>$period['product_id']])->asArray()->one();
	
			$completePeriod = new PeriodModel();

			$completePeriod->id = $period['id'];
			$completePeriod->table_id = $period['table_id'];
			$completePeriod->product_id = $period['product_id'];
			$completePeriod->limit_num = $period['limit_num'];
			$completePeriod->buy_unit = $period['buy_unit'];
			$completePeriod->cat_id = $productInfo['cat_id'];
			$completePeriod->period_number = $period['period_number'];
			$completePeriod->lucky_code = 0;
			$completePeriod->user_id = 0;
			$completePeriod->price = $period['price'];
			$completePeriod->start_time = (string)$period['start_time'];
			$completePeriod->end_time = $time;
			$completePeriod->exciting_time = '0';
			$completePeriod->result_time = Period::raffTime($time);
			$completePeriod->period_no = $period['period_no'];
			
			$result = $completePeriod->save(false);
			if ($result) {

				$this->fullPeriods[] = yii\helpers\ArrayHelper::toArray($completePeriod);

				$del = CurrentPeriod::deleteAll(['id'=>$period['id']]);
				if (!$productInfo) {
					$this->redis->sdel(self::NEW_PERIOD_KEY, $period['id']);
					return false;
				}

				if ($productInfo['marketable'] == '0') {
					$this->redis->sdel(self::NEW_PERIOD_KEY, $period['id']);
					return false;
				}

				if (($period['period_number']+1) > $productInfo['store'] ) {
					ProductModel::updateAll(['marketable'=>0],['id'=>$productInfo['id']]);
					$this->redis->sdel(self::NEW_PERIOD_KEY, $period['id']);
					return false;
				}

				if ($del) {
					$newPeriod = new CurrentPeriod();
					$newPeriod->product_id = $period['product_id'];
					$newPeriod->table_id = rand(100,109);
					$newPeriod->limit_num = $productInfo['limit_num'];
					$newPeriod->buy_unit = $productInfo['buy_unit'];
					$newPeriod->period_number = $period['period_number']+1;
					$newPeriod->price = $productInfo['price'];
					$newPeriod->sales_num = 0;
					$newPeriod->left_num = $productInfo['price'];
					$newPeriod->start_time = (string)microtime(true);

					$newPeriodSave = $newPeriod->save(false);

					if ($newPeriodSave) {

						$newPeriodId = $newPeriod->attributes['id'];
						$newPeriod->period_no = Period::getPeriodNo(yii\helpers\ArrayHelper::toArray($newPeriod));
						$newPeriod->save(false);

						$this->initCodes($productInfo,$newPeriodId);
						$this->redis->sdel(self::NEW_PERIOD_KEY, $period['id']);
					}

				}
			}	
		}		
	}
	/**
	 * 初始化code
	 * @param  [type] $product  [description]
	 * @param  [type] $periodId [description]
	 * @return [type]           [description]
	 */
	private function initCodes($product,$periodId){

		$codeKey = self::PERIOD_ALL_CODE_KEY.$periodId;

		$start = 10000001;
		$end = $start + $product['price'];
		$pipe = $this->redis->pipeline();
		for ($i=10000001;$i<$end;$i++) {
			$pipe->sadd($codeKey,$i);
			$num = $i - $start + 1;
			if (($num > 0 && $num % 10000 == 0) || $i == ($end-1)) {
				$pipe->exec();
				if($i!=($end-1)) {
					$pipe = $this->redis->pipeline();
				}
			}
		}
		
		$this->redis->del(self::PERIOD_SALED_KEY.$periodId);
		if ($this->redis->slen($codeKey) != $product['price']) {
			$this->redis->del($codeKey);
			$this->initCodes($product,$periodId);
		}
	}
	/**
	 * 回滚
	 * @param  int $periodId 期数id
	 * @return [type]           [description]
	 */
	private function rollBack($periodId){
		$this->redis->sset(self::PERIOD_ALL_CODE_KEY.$periodId,$this->codes[$periodId]['codes']);
		$this->redis->sdel(self::PERIOD_SALED_KEY.$periodId,$this->codes[$periodId]['codes']);
		$this->redis->sdel(self::GET_CODE_LIST_KEY.$periodId,$this->userId);
		$this->redis->del(self::USER_BUY_LIST_KEY.$this->order);
		$this->redis->sdel(self::ORDER_HAND_LIST_KEY,$this->order);
	}

	/**
	 * 开始取码
	 * @param  int $productId 期数id
	 * @return [type]            [description]
	 */
	private function beginGetCode($periodId){
		$listKey = self::GET_CODE_LIST_KEY.$periodId;
		if ($this->redis->isexist($listKey)) {
			$uid = $this->redis->lget($listKey,0,0);
			if ($uid[0] == $this->userId || !$uid) {
				return 1;
			}else{
				return 0;
			}
		}
		return 1;
	}

	/**
	 * 获取用户支付总额
	 * @return int      [description]
	 */
	private function _getTotal(){
		$total = CartModel::find()->select("sum(nums) as total")->where(['user_id'=>$this->userId,'is_buy'=>1])->asArray()->one();
		return $total['total'];
	}

	/**
     * 获取用户余额
     * @return int      [description]
     */
    private function _getUserMoneyPoint(){
            return UserModel::find()->select('point,money')->where(['id'=>$this->userId])->one();
    }

    /**
     * 获取购物车内容
     * @return [type] [description]
     */
	private function _getCartInfo(){
		return CartModel::find()->where(['user_id'=>$this->userId,'is_buy'=>1])->asArray()->all();
	}
}