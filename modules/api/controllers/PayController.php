<?php
/**
 * User: hechen
 * Date: 15/10/10
 * Time: 下午5:36
 */
namespace app\modules\api\controllers;

use app\models\Config;
use app\models\UserCoupons;
use yii;
use app\models\PaymentOrderDistribution;
use app\models\User;
use app\models\PaymentOrderItemDistribution;
use app\helpers\DateFormat;
use app\services\Pay;
use app\services\Cart;
use app\helpers\MyRedis;
use app\services\Product;
use app\models\PeriodBuylistDistribution;
use app\services\Payway;
use app\models\CurrentPeriod;
use app\services\Period;
use app\services\Thirdpay;
use app\helpers\Version;

class PayController extends BaseController
{
	const ORDER_LIST_KEY = 'ORDER_LIST';  //hash类型，order->orderinfo
	const ORDER_ITEMS_KEY = 'ORDER_ITEMS_';  //hash 订单详情 period_id->info _orderid
	const USER_BUY_LIST_KEY = 'USER_BUY_LIST_'; //用户购买记录
	/**
	 * 创建订单
	 * @return [type] [description]
	 */
	public function actionCreateOrder(){
		$request = Yii::$app->request;
        if (!$this->userId) {
            return array('code'=>10001,'message'=>'未登录');
        }

        $payType = $request->post('payType');
        $payName = $request->post('payName','balance');
        $payBank = $request->post('payBank');
        $point = $request->post('integral','0');
        $payMoney = $request->post('payMoney','0');
        $source = $request->post('userSource','1');
		$ppwd = $request->post('ppwd');
		$coupons = $request->post('coupons');


		$cartMoney = Cart::getCartMoneByUid($this->userInfo->id);
		if ( $this->userInfo->pay_password && $this->userInfo->micro_pay < ($cartMoney-$payMoney) && !$coupons && $payType=='consume') {
			if (empty($ppwd) || !Yii::$app->getSecurity()->validatePassword($ppwd,$this->userInfo->pay_password)) {
				return ['code'=>10002,'message'=>'支付密码错误'];
			}
		}

		//验证新手商品语音验证码是否通过
		$regconfig = Config::getValueByKey('regconfig');
		if ($regconfig) {
			$reg_red = UserCoupons::findByUserId($this->userId)->where(['status' => 0, 'user_id' => $this->userId, 'packet_id' => $regconfig['packet_id']])->asArray()->one();
			$isNew = 0;
			if ($regconfig['status'] == '1' && (!$regconfig['starttime'] || $regconfig['starttime'] < time()) && (!$regconfig['endtime'] || $regconfig['endtime'] > time())) {
				if ($reg_red) {
					$isNew = 1;
				}
			}
			if ($coupons) {
				$_couponList = explode(",", $coupons);
				$couponList = [];
				foreach ($_couponList as $key => $value) {
					$c = explode("_",$value);
					$couponList[] = $c[0];
				}
				if(in_array($reg_red['id'], $couponList)) {
					if (!\app\services\User::isCheckedCode($this->userInfo['phone'], 44)) {
						return ['code'=>10003,'message'=>'使用新手红包需要完成新手引导语音验证'];
					}
				}
			}
		}


		$user = User::find()->select('status')->where(['id'=>$this->userId])->one();
		if ($user->status==1) {
			return ['code'=>10099, 'message'=>'账户已冻结,请联系客服.'];
		}
		$choosePay = new Payway();
		$result = $choosePay->chooseway($this->userId, $payType, $payName, $payBank, $point, $payMoney, $source,$coupons);
		try {

			if ($payName != 'balance' && ($source == '3' || $source == '4')) {
				if ($result['code'] == '100') {
					$pay = new Thirdpay();
					$payResult = $pay->pay($result['order'],$payBank);
					if ($payResult) {
						$payResult['code'] = 100;
					} else {
						$payResult['code'] = 0;
					}
					return $payResult;
				}
			}else{
				return $result;
			}
		} catch (\Exception $e) {
			file_put_contents(Yii::getAlias('@app/a.txt'), $e->getLine().'_'.$e->getMessage());
		}

	}

	/**
	 * 检测支付密码
	 * @return [type] [description]
	 */
	public function actionCheckPpwd(){
		$ppwd = Yii::$app->request->get('pwd');
		$userInfo = User::find()->where(['id'=>$this->userId])->asArray()->one();

		if (!Yii::$app->getSecurity()->validatePassword($ppwd,$userInfo['pay_password'])) {
			return array('code'=>0,'msg'=>'fail');
		}
		return array('code'=>1,'msg'=>'success');
	}

	/**
	 * 支付
	 * @return [type] [description]
	 */
	public function actionPayOrder(){
		$order = Yii::$app->request->post('o') ? : Yii::$app->request->get('o');

		if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();

		$pay = new Pay($this->userId);
		if(Version::compare($this->version,'>=','2.0.3')){
			$data = $pay->payByBalance2($order);
		}else{
			$data = $pay->payByBalance($order);
		}

        return $data;
	}
	/**
	 * 支付结果
	 * @return [type] [description]
	 */
	public function actionResult(){
		$order = Yii::$app->request->get('o');
		if (!$order) {
			$data['code'] = 201;
			$data['message'] = '支付失败';
			return $data;
		}
		if (!$this->userId) {
			$data['code'] = 201;
			$data['message'] = '支付失败';
			return $data;
		}
		$user = User::find()->where(['id'=>$this->userId])->asArray()->one();

		$redis = new MyRedis();

		$orderInfo = json_decode($redis->hget(self::ORDER_LIST_KEY,$order),true);


		if (!$orderInfo) {
			$orderInfo = PaymentOrderDistribution::findByTableId($user['home_id'])->where(['id'=>$order])->asArray()->one();
		}

		$count = Cart::count($this->userId);

		if (empty($orderInfo)) {
			$redis->del((Pay::THIRD_PAY_KEY).$orderInfo['recharge_orderid']);
			$data['code'] = 201;
			$data['message'] = '伙购失败';
			return $data;	
		}else{
			if ($orderInfo['status'] == 0) {
				$data['code'] = 0;
				$data['message'] = '订单支付中...';
				return $data;	
			}
			if ($orderInfo['status'] == 2) {
				$redis->del((Pay::THIRD_PAY_KEY).$orderInfo['recharge_orderid']);
				$data['code'] = 201;
				$data['message'] = '伙购失败';
				return $data;	
			}
			$orderItems = $redis->hget(self::ORDER_ITEMS_KEY.$order,'all');
			if (!$orderItems) {
				$orderItems = PaymentOrderItemDistribution::findByTableId($user['home_id'])->where(['payment_order_id'=>$order])->asArray()->all();
			}


			$productId = array();
			foreach ($orderItems as $key => &$value) {
				if (!is_array($value)) {
					$value = json_decode($value,true);
				}
				$productId[] = $value['product_id'];
				$periodId[] = $value['period_id'];
			}
			$productInfo = Product::info($productId);
			$periodNos  = Period::getPeriodInfo($periodId);
			$success = $fail = $some = array();
			foreach ($orderItems as $k => $v) {
				unset($v['codes']);
				unset($v['payment_order_id']);
				$v['item_buy_time'] = DateFormat::microDate($v['item_buy_time']);
				$v['name'] = $productInfo[$v['product_id']]['name'];
				$v['picture'] = $productInfo[$v['product_id']]['picture'];
				$v['period_no']=$periodNos[$v['period_id']]['period_no'];
				if ($v['nums'] > 0) {
					if ($v['post_nums'] > $v['nums']) {
						$some[] = $v;
					}else{
						$success[] = $v;
					}
				}else {
					$v['nums'] = $v['post_nums'];
					$fail[] = $v;
				}
			}
			
			$data['code'] = 100;
			$data['success'] = $success;
			$data['fail'] = $fail;
			$data['some'] = $some;
			$data['count'] = $count['count'];
                        
			$user =new \app\services\Member(['id'=>$this->userId]);
			$buylist = $user->getBuyList("", "", -1, 1);
			$data['share_type'] = ($buylist and $buylist['totalCount'] > 3) ?  "button" : "pic" ;
                                                                
			return $data;
		}
		
	}

	/**
	 * 快速购买
	 * @return [type] [description]
	 */
	public function actionQuickBuy(){
		if (!$this->userId) {
			return array('logined'=>0);
		}

		$data['logined'] = 1;
		
		if ($this->userInfo->status==1) {
			$data['code'] = 10099;
			$data['message'] = '账户已冻结,请联系客服.';
			return $data;
		}

		$periodId = Yii::$app->request->get('pid');
        $periodInfo = CurrentPeriod::find()->where(['id'=>$periodId])->asArray()->one();
        if (!$periodInfo) {
        	$data['code'] = 101;
        	return $data;
        }
        $buy = 0;
        if ($periodInfo['limit_num'] > 0) {
        	$buy = Period::getUserHasBuyCount($this->userId,$periodId);
        	if ($buy >= $periodInfo['limit_num']) {
        		$data['code'] = 104;
        		return $data;		
        	}
        }
        $data['code'] = 100;
		$baseInfo = User::find()->where(['id'=>$this->userId])->asArray()->one();
		$data['left'] = $periodInfo['left_num'];
        $data['money'] = $baseInfo['money'] ? $baseInfo['money'] : '0';
        $data['point'] = $baseInfo['point'] ? $baseInfo['point'] : '0';
        $data['ppwd'] = $baseInfo['pay_password'] ? 1 : 0;
        $data['free'] = $baseInfo['micro_pay'];
        $data['canbuy'] = $periodInfo['limit_num'] > 0 ? ($periodInfo['limit_num'] - $buy) : $periodInfo['left_num'];
        return $data;
	}

	/**
	 * 快速余额支付
	 * @return [type] [description]
	 */
	public function actionQuickPay(){
		if (!$this->userId) {
            return array('code'=>10001,'message'=>'未登录');
        }

        $periodId = Yii::$app->request->get('pid');
        $buyNum = Yii::$app->request->get('num');
        $ppwd = Yii::$app->request->get('ppwd');

        if ($this->userInfo->status==1) {
			return ['code'=>10099, 'message'=>'账户已冻结,请联系客服.'];
		}

        if ( $this->userInfo->pay_password && $this->userInfo->micro_pay < $buyNum) {
            if (!Yii::$app->getSecurity()->validatePassword($ppwd,$this->userInfo->pay_password)) {
                return ['code'=>0];
            }
        }

        $periodInfo = CurrentPeriod::find()->where(['id'=>$periodId])->asArray()->one();
        if (!$periodInfo) {
        	$data['code'] = 101;
        	return $data;
        }
        Cart::add($this->userId,$periodInfo['product_id'],$buyNum);

		$payType = 'consume';
		$payName = 'balance';
		$payBank = '';
        $point = '0';
        $payMoney = '0';
        $source = '1';
		// $user = User::find()->select('status')->where(['id'=>$this->userId])->one();
		
		$choosePay = new Payway();
        $order = $choosePay->chooseway($this->userId, $payType, $payName, $payBank, $point, $payMoney, $source);
        // fastcgi_finish_request();
        $pay = new Pay($this->userId);
        $data = $pay->payByBalance($order['order']);
        return $order;
	}

}
