<?php

/**
 * User: hechen
 * Date: 15/10/14
 * Time: 下午9:08
 */

namespace app\services;

use app\helpers\Brower;
use app\helpers\Curl;
use app\helpers\MyRedis;
use app\models\Invite;
use yii;
use app\models\User;
use app\models\RechargeOrderDistribution;
use app\components\Chatpay;
use app\components\Chinabank;
use app\models\ActQualification;
use app\models\Config;
use app\services\Coupon;
use app\models\RechargeReward;
use app\models\RechargeRewardLog;
use app\models\ActQualificationLog;

/**
* 第三方支付
*/
class Thirdpay
{
	const ORDER_COUPON_KEY = 'ORDER_COUPON';
    const RECHARGE_ORDER_KEY = 'RECHARGE_ORDER';

	public $productName = '伙购网';

	public function __construct()
	{
		$from = Brower::whereFrom();
		if ($from == 2) {
			$this->productName = '滴滴夺宝';
		}
	}

	public function pay($no,$paytype,$data=[])
	{

		$orderInfo = $this->getOrderByNo($no);
//		if ($paytype == 'chat') {
//			$data = array(
//					'product' => $this->productName,
//					'attach' => '',
//					'no' => $orderInfo['id'],
//					'money' => $orderInfo['post_money'],
//					'tag' => '',
//					'productId' => 1
//				);
//			return $this->payByChat($data,$orderInfo['source']);
//		} else
		if($paytype == 'brandchat') {
			$data = array(
				'product' => $this->productName,
				'attach' => '',
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'tag' => '',
				'productId' => 1
			);
			return $this->payByBrandChat($data);
		} elseif($paytype == 'commission') {
			$data = array(
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'uid' => $orderInfo['user_id'],
			);
			return $this->payByCommission($data);
		} elseif ($paytype == 'chinaBank') {
			$data = array(
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'bank' => $orderInfo['bank']
			);
			return $this->payByBank($data);
		} elseif ($paytype == 'iapp') {
			$payData = array(
				'product' => $this->productName,
				'userId' => $orderInfo['user_id'] + 10000,
				'custom' => $this->getCustomStr($orderInfo['id']),
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'productId' => 1,
			);
			$data = array_merge($payData, $data);

			return $this->payByIapp($data,$orderInfo['source']);
		} elseif ($paytype == 'zhifukachat') {
			$data = array(
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'custom' => $this->getCustomStr($orderInfo['id']),
			);
			return $this->payByZhifukaChat($data,$orderInfo['source']);
		} elseif ($paytype == 'zhifukaqq') {
			$data = array(
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'custom' => $this->getCustomStr($orderInfo['id']),
			);
			return $this->payByZhifukaQQ($data,$orderInfo['source']);
		} elseif ($paytype == 'jd') {
			$data = array(
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'custom' => $this->getCustomStr($orderInfo['id']),
				'time' => $orderInfo['create_time'],
			);
			return $this->payByJd($data,$orderInfo['source']);
		} elseif ($paytype == 'kq') {
			$data = array(
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'custom' => $this->getCustomStr($orderInfo['id']),
				'time' => $orderInfo['create_time'],
			);
			return $this->payByKq($data,$orderInfo['source']);
		} elseif ($paytype == 'union') {
			$data = array(
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'custom' => $this->getCustomStr($orderInfo['id']),
				'time' => $orderInfo['create_time'],
			);
			return $this->payByUnion($data,$orderInfo['source']);
		} elseif ($paytype == 'exchage') {
			$data['post_money'] = $orderInfo['post_money'];
			$data['order'] = $no;
			$data['uid'] = $orderInfo['user_id'];
			return $this->payByExchange($data);
		} elseif ($paytype == 'send') {
			$data['post_money'] = $orderInfo['post_money'];
			$data['order'] = $no;
			$data['uid'] = $orderInfo['user_id'];
			return $this->payBySend($data);
		} elseif ($paytype == 'alipay') {
			$data = [
				'no' => $orderInfo['id'],
				'money' => $orderInfo['post_money'],
				'custom' => $this->getCustomStr($orderInfo['id']),
			];
			return $this->payByAlipay($data, $orderInfo['source']);
		} elseif ($paytype == 'nowpay') {

			$data = [
					'no' => $orderInfo['id'],
					'money' => $orderInfo['post_money'],
					'custom' => $this->getCustomStr($orderInfo['id']),
					'name' => $this->productName,
			];

		 return	$this->payBynowpay($data, $orderInfo['source']);

		}
		return false;
	}

	/**
	 * 创建充值订单
	 * @param  int $uid      用户id
	 * @param  int $payMoney 金额
	 * @param  var $payType  类型
	 * @param  var $payName  充值方式
	 * @param  var $payBank  银行
	 * @return [type]           [description]
	 */
	public function createRechargeOrder($uid,$payMoney,$payType,$payName,$payBank,$source,$point){
		$userInfo = User::find()->select('home_id,nickname,phone,email,spread_source')->where(['id'=>$uid])->asArray()->one();

		$order = new RechargeOrderDistribution($userInfo['home_id']);

		$from = Brower::whereFrom();
		if ($from == 2) {
			$orderSub = 'D';
		} else {
			$orderSub = 'H';
		}
		$orderNum = $order->generateOrderId($userInfo['home_id'], $orderSub);

		$order->id = $orderNum;
		$order->user_id = $uid;
		$order->status = 0;
		$order->type = $payType;
		$order->post_money = $payMoney;
		$order->money = 0;
		$order->payment = $payName;
		$order->bank = $payBank;
		$order->source = $source;
		$order->point = $point;
		$order->create_time = time();
		$order->pay_time = time();
		$order->ip = ip2long(Yii::$app->request->userIp);
		$order->user_account = $userInfo['nickname'] ? : ($userInfo['phone'] ? : $userInfo['email']);
		$order->spread_source = $userInfo['spread_source'];
		$rs = $order->save();

		if ($rs) {
			return $orderNum;
		}

	}

	/**
	 * 获取订单信息
	 * @param  string $no 订单号
	 * @return [type]     [description]
	 */
	public static function getOrderByNo($no){
		$tableId = RechargeOrderDistribution::getTableIdByOrderId($no);
		$orderInfo = RechargeOrderDistribution::findByTableId($tableId)->where(['id'=>$no])->asArray()->one();
		return $orderInfo;
	}

	/**
	 * 微信支付
	 * @param  array $data 数据
	 * @return [type]       [description]
	 */
	public function payByChat($data,$source){
		$time_start = date("YmdHis",time());
		$time_expire = date("YmdHis",time()+3600);
		$notify = 'http://www.'.DOMAIN.'/chatpay/notify.html';
		if ($source == 1) {
			return Yii::$app->chatpay->pay($data['product'],$data['attach'],$data['no'],$data['money']*100,$time_start,$time_expire,$data['tag'],$notify,$data['productId']);
		}else if ($source == 2) {
			return Yii::$app->chatpay->jsPay($data['product'],$data['attach'],$data['no'],$data['money']*100,$time_start,$time_expire,$data['tag'],$notify,$data['productId']);
		}else if ($source == 3 || $source == 4) {
			return Yii::$app->chatpay->payForApp($data['product'],$data['attach'],$data['no'],$data['money']*100,$time_start,$time_expire,$data['tag'],$notify,$data['productId']);
		}
	}

	public function payByBrandChat($data)
	{
		$time_start = date("YmdHis",time());
		$time_expire = date("YmdHis",time()+3600);
		$notify = 'http://www.'.DOMAIN.'/chatpay/notify.html';
		return Yii::$app->chatpay->jsPay($data['product'],$data['attach'],$data['no'],$data['money']*100,$time_start,$time_expire,$data['tag'],$notify,$data['productId']);
	}

	/**
	 * 佣金充值
	 * @param  array $data 用户信息
	 * @return [type]       [description]
	 */
	public function payByCommission($data)
	{
		$money = (int)$data['money'];
		$chargeUserId = $data['uid'];
		$no = $data['no'];

		$save = Invite::commissionRecharge($chargeUserId, $money);
		if ($save) {
			$this->updateOrder($no, [
				'status'=>1,
				'money'=>$money,
				'pay_time'=>time(),
			]);
			return $save;
		}
		return false;
	}

	/**
	 * 网银在线
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function payByBank($data){
		return Yii::$app->chinabank->pay($data['no'],$data['money'],$data['bank']);
	}

	/** 爱贝支付
	 * @param $data
	 * @return mixed
	 */
	public function payByIapp($data,$source)
	{
		$iapppay = Yii::$app->iapppay;
		$data['notifyUrl'] = 'http://www.'.DOMAIN.'/iapppay/notify.html';//通知地址
		if ($source==1 || $source==2 || $source==5) {
			$iappOrderId = $iapppay->order($data['userId'],$data['product'],$data['productId'],$data['no'],$data['money'],$data['custom'],$data['notifyUrl']);

			if ($source==1) {
				$data['redirectUrl'] = 'http://www.'.DOMAIN.'/iapppay/redirect.html'; //回调地址
				$data['returnUrl'] = 'http://www.'.DOMAIN;//爱贝收银台返回商户地址
			}elseif($source==2) {
				$data['redirectUrl'] = 'http://www.'.DOMAIN.'/iapppay/redirect.html'; //回调地址
				$data['returnUrl'] = 'http://weixin.'.DOMAIN;
			}elseif($source==5) {
				$data['redirectUrl'] = 'http://www.'.DOMAIN.'/iapppay/redirect.html'; //回调地址
				$data['returnUrl'] = 'http://m.'.DOMAIN;
			}
			$url =  $iapppay->url($iappOrderId,$data['redirectUrl'],$data['returnUrl'],$source);
			Yii::$app->response->redirect($url);
			Yii::$app->end();
		}elseif($source==3 || $source==4) {
			$appOrderParams = $iapppay->getAppOrderParams($data['userId'],$data['product'],$data['productId'],$data['no'],$data['money'],$data['custom'],$data['notifyUrl']);
			return $appOrderParams;
		}
	}

	/** 今天支付
	 * @param $data
	 * @return mixed
	 */

	public function payBynowpay($data,$source){

		$from = Brower::whereFrom();
		if ($from == 2) {
			$config = require (Yii::getAlias('@app/config/didi_nowpay.php'));
			$nowpay = Yii::createObject($config);
		} else {
			$nowpay = Yii::$app->nowpay;
		}


		$time = time();
		$appId = $nowpay->app_id;
		$mhtOrderNo = $data['no'];
		$mhtOrderName = $data['name'];
		$mhtOrderType = '01';
		$money = $data['money']*100;
		$mhtOrderDetail = $data['name'];
		$mhtOrderStartTime = date('Ymdhis', $time);
		$notifyUrl = 'http://www.' . DOMAIN . '/nowpay/notify.html';
		$mhtCharset = 'UTF-8';
		$mhtCurrencyType = '156';
		$payChannelType=25;  //渠道


		$secure_key= md5($nowpay->secure_key);
		$url="appId=$appId&mhtCharset=$mhtCharset&mhtCurrencyType=$mhtCurrencyType&mhtOrderAmt=$money&mhtOrderDetail=$mhtOrderDetail&mhtOrderName=$mhtOrderName&mhtOrderNo=$mhtOrderNo&mhtOrderStartTime=$mhtOrderStartTime&mhtOrderType=$mhtOrderType&notifyUrl=$notifyUrl&payChannelType=$payChannelType";
		$mdurl=$url.'&'.$secure_key;
		$data['url']=$url.'&mhtSignType=MD5&mhtSignature='.md5($mdurl);
		return $data;

	}


	public function payByZhifukaChat($data,$source)
	{
		$from = Brower::whereFrom();
		if ($from == 2) {
			$config = require (Yii::getAlias('@app/config/didi_zhifuka.php'));
			$zhifuka = Yii::createObject($config);
		} else {
			$zhifuka = Yii::$app->zhifuka;
		}
		$sdcustomno = $data['no'];
		$orderAmount = $data['money']*100;//下单金额单位（分）
		$noticeurl= 'http://www.'.DOMAIN.'/chatpay/notify.html';
		$mark = $data['custom'];
		$remarks = '支付';
		$customStr = $this->getCustomStr($data['no']);
		if ($source==1) {
			$backurl = 'http://www.'.DOMAIN;
			$data = $zhifuka->qrcode($sdcustomno,$orderAmount,$noticeurl,$backurl,$mark,$remarks);
		} else if ($source==2 || $source==5) {
			if($source==2) {
				$backurl = 'http://weixin.'.DOMAIN.'/cart/weixinpayok-'.$data['no'].'-'.$customStr.'.html';
			}elseif($source==5) {
				$backurl = 'http://m.'.DOMAIN.'/cart/weixinpayok-'.$data['no'].'-'.$customStr.'.html';
			}
			$data = $zhifuka->wapPay($sdcustomno,$orderAmount,$noticeurl,$backurl,$mark,$remarks);
		} elseif($source==3 || $source==4) {
			$data = $zhifuka->getAppOrderParams($sdcustomno,$orderAmount,$noticeurl);
		}
		return $data;
	}

	public function payByZhifukaQQ($data,$source)
	{
		$zhifuka = Yii::$app->zhifuka;
		$sdcustomno = $data['no'];
		$orderAmount = $data['money']*100;//下单金额单位（分）
		$noticeurl= 'http://www.'.DOMAIN.'/chatpay/notify.html';
		$mark = $data['custom'];
		$remarks = '支付';
		if ($source==1) {
			$backurl = 'http://www.'.DOMAIN;
			$data = $zhifuka->qqQrcode($sdcustomno,$orderAmount,$noticeurl,$backurl,$mark,$remarks);
		}
		return $data;
	}

	public function payByJd($data,$source)
	{
		$jdpay = Yii::$app->jdpay;
		$no = $data['no'];
		$orderAmount = $data['money']*100;//下单金额单位（分）
		$noticeurl= 'http://www.'.DOMAIN.'/jdpay/notify.html';
		$remark = $data['custom'];
		$desc = $this->productName . '支付';
		$noName = $this->productName;
		$noTime = date('Y-m-d H:i:s',$data['time']);
		$customStr = $this->getCustomStr($data['no']);
		if ($source==1) {
			$successCallbackUrl = 'http://www.'.DOMAIN.'/jdpay/redirect-'.$data['no'].'.html';
			$data = $jdpay->pay($no,$orderAmount,$noticeurl,$successCallbackUrl,$remark,$desc,$noName,$noTime);
		} else if ($source==2 || $source==5) {
			if($source==2) {
				$successCallbackUrl = 'http://www.'.DOMAIN.'/jdpay/redirect-'.$data['no'].'.html';
				$failCallbackUrl = 'http://weixin.'.DOMAIN;
			}elseif($source==5) {
				$successCallbackUrl = 'http://www.'.DOMAIN.'/jdpay/redirect-'.$data['no'].'.html';
				$failCallbackUrl = 'http://m.'.DOMAIN;
			}
			$data = $jdpay->wapPay($no,$orderAmount,$noticeurl,$successCallbackUrl,$failCallbackUrl,$remark,$desc,$noName,$noTime);
		}
		return $data;

	}

	public function payByKq($data,$source)
	{
		$kqpay = Yii::$app->kqpay;
		$no = $data['no'];
		$orderAmount = $data['money']*100;//下单金额单位（分）
		$orderTime = $data['time'];
		$productName = Yii::$app->name;
		$bgUrl = 'http://www.'.DOMAIN.'/kqpay/notify.html';
		$pageUrl = 'http://www.'.DOMAIN.'/kqpay/redirect-'.$no.'.html';
		$ext1 = $data['custom'];
		$ext2 = '';
		if ($source==1) {
			$data = $kqpay->pay($productName,$no,$orderAmount,$orderTime,$pageUrl,$bgUrl,$ext1,$ext2);
		}
		return $data;

	}

	public function payByUnion($data,$source)
	{
		$unionpay = Yii::$app->unionpay;
		$no = $data['no'];
		$orderAmount = $data['money'];
		$backUrl = 'http://www.'.DOMAIN.'/unionpay/notify.html';
		$frontUrl = 'http://www.'.DOMAIN.'/unionpay/redirect-'.$no.'.html';
		if ($source==1) {
			$data = $unionpay->pay($no,$orderAmount,$frontUrl,$backUrl);
		} elseif ($source==2||$source==5) {
			$data = $unionpay->wapPay($no,$orderAmount,$frontUrl,$backUrl);
		} elseif ($source==3||$source==4) {
			$data = $unionpay->appPay($no,$orderAmount,$frontUrl,$backUrl);
		}
		return $data;
	}

	/**
	 * 充值卡直接兑换伙购币
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function payByExchange($data){
		if ($data['post_money'] ==  $data['money']) {
			$this->updateOrder($data['order'],array(
					'status' => 1,
					'money' => $data['money'],
					'pay_time' => time()
				));
			$userInfo = User::find()->where(['id'=>$data['uid']])->asArray()->one();
			User::updateAll(
					array('money'=>$userInfo['money']+$data['money']),
					"id='".$data['uid']."'"
				);
		}
	}

	/**
	 * 充值卡直接兑换伙购币
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function payBySend($data){
		if ($data['post_money'] ==  $data['money']) {
			$this->updateOrder($data['order'],array(
					'status' => 1,
					'money' => $data['money'],
					'pay_time' => time()
			));
			$userInfo = User::find()->where(['id'=>$data['uid']])->asArray()->one();
			User::updateAll(
					array('money'=>$userInfo['money']+$data['money']),
					"id='".$data['uid']."'"
			);
		}
	}


	/** 支付宝支付
	 * @param $data
	 * @param $source
	 * @return array
	 */
	public function payByAlipay($data, $source)
	{
		$from = Brower::whereFrom();
		if ($from == 2) {
			$subject = '火彦';
			$body = '';
		} else {
			$subject = '火彦';
			$body = '';
		}

		$alipay = Yii::$app->alipay;
		$no = $data['no'];
		if (DOMAIN=='5ykd.com') {
			$orderAmount = 0.1;
		} else {
			$orderAmount = $data['money'];
		}
		$noticeurl= 'http://www.'.DOMAIN.'/alipay/notify.html';

		if ($source==1) {
			$returnUrl = '';
			$data = $alipay->redirectPay($no, $orderAmount, $noticeurl, $returnUrl, $subject, $body);
		} else if ($source==2 || $source==5) {
			if($source==2) {
				$returnUrl = 'http://weixin.' . DOMAIN;
				$showUrl = 'http://weixin.' . DOMAIN;
			} else {
				$returnUrl = 'http://m.' . DOMAIN;
				$showUrl = 'http://m.' . DOMAIN;
			}
			$data = $alipay->wapPay($no, $orderAmount, $noticeurl, $returnUrl, $showUrl, $subject, $body);
		} elseif($source==3 || $source==4) {
			$data = $alipay->getAppOrderParams($no, $subject, $body, $orderAmount, $noticeurl);
		}
		return $data;
	}

	/**
	 * 更新订单情况
	 * @param  str $no   订单号
	 * @param  array $data 字段
	 * @return [type]       [description]
	 */
	public function updateOrder($no,$data)
	{
		$tableId = RechargeOrderDistribution::getTableIdByOrderId($no);
		$recharge = new RechargeOrderDistribution($tableId);
		$db = \Yii::$app->db;
		$result = $db->createCommand()->update($recharge::tableName(),$data,['id'=>$no])->execute();
		return $result;
	}

	/** 获取订单自定义加密字符
	 * @param $no
	 * @param $userId
	 */
	public function getCustomStr($no)
	{
		$no = $no ? : mt_rand(11111111,9999999999);
		$str = 'customstr_huogou';
		return md5("$no".'_'."$str");
	}

	/**
	 * 验证订单自定义加密字符
	 */
	public function validateCustomStr($customStr,$no)
	{
		if (empty($no) || empty($customStr)) {
			return false;
		}
		return $customStr == $this->getCustomStr($no);
	}

	/** 第三方支付，通知，跳转，结果操作
	 * @param $no
	 * @param $result
	 */
	public function result($resultType,$no,$cutomStr='',$resultData = [])
	{
		if (!in_array($resultType,['notice','redirect','result'])) {
			return false;
		}
		if ($resultType=='notice' && !$this->validateCustomStr($cutomStr,$no)) {
			return false;
		}
		if ($resultType=='redirect') {
			if (!$this->validateCustomStr($cutomStr,$no)) {
				$redirectDoNothing = true;
			} else {
				$redirectDoNothing = false;
			}
		}

		$orderInfo = $this->getOrderByNo($no);
		if (!$orderInfo) {
			return false;
		}

		if ($resultType=='notice') {
			if (function_exists('fastcgi_finish_request')) {
				fastcgi_finish_request();
			}
		}

		if ($orderInfo['status']=='0') {
			if ($resultType=='notice' || ($resultType=='redirect' && !$redirectDoNothing)) {
				$order_data['status'] = 1;
				$order_data['pay_time'] = microtime(true);
				$order_data['money'] = $orderInfo['post_money'];
				$order_data['result'] = yii\helpers\Json::encode($resultData);
				$this->updateOrder($no, $order_data);

				$userInfo = User::find()->where(['id' => $orderInfo['user_id']])->asArray()->one();
				$money = $userInfo['money'] + $orderInfo['post_money'];
				User::updateAll(['money' => $money], ['id' => $orderInfo['user_id']]);

				if ($orderInfo['type'] == 1) {
					//充值后相关操作
					$this->doRechargeSomeThing($orderInfo['user_id'], $orderInfo['post_money']);
				}
			}
		}

		if ($orderInfo['type'] == '1') {

			$data['type'] = 1;
			$data['order'] = $no;
			if ($resultType=='redirect' || ($resultType=='result' && $orderInfo['status']==1)) {
				$source = $orderInfo['source'];
				if ($source==1) {
					$data['url'] = yii\helpers\Url::to(['/member/recharge/money-log']);
				} else if ($source==2) {
					$data['url'] = yii\helpers\Url::to(['/weixin/member/consumption']).'#recharge';
				} else if($source==5) {
					$data['url'] = yii\helpers\Url::to(['/mobile/member/consumption']).'#recharge';
				} else {
					$data['url'] = true;
				}
			}
			return $data;

		}else if ($orderInfo['type'] == '2') {

			$redis = new MyRedis();
			$payOrderid = $redis->get((Pay::THIRD_PAY_KEY).$no);

			if (!$payOrderid && in_array($resultType,['notice','redirect'])) {
				if ($resultType=='notice') {

					$pay = new Pay($orderInfo['user_id']);
					$payOrderid = $pay->createPayOrder($orderInfo['source'],$orderInfo['point'],1,$orderInfo['bank'],$no);

					$redis->hset(self::ORDER_COUPON_KEY,[$payOrderid=>$redis->hget(self::RECHARGE_ORDER_KEY,$no)]);

					$pay->payByBalance($payOrderid);

	            	$redis->hdel(self::RECHARGE_ORDER_KEY,$no);

					return true;
				} elseif($resultType=='redirect' && !$redirectDoNothing) {
					$pay = new Pay($orderInfo['user_id']);
					$payOrderid = $pay->createPayOrder($orderInfo['source'],$orderInfo['point'],1,$orderInfo['bank'],$no);

	            	$redis->hset(self::ORDER_COUPON_KEY,[$payOrderid=>$redis->hget(self::RECHARGE_ORDER_KEY,$no)]);
	            	$redis->hdel(self::RECHARGE_ORDER_KEY,$no);
				}
			}

			$data['type'] = 2;
			$data['order'] = $payOrderid;


			if ($resultType=='redirect' && $redirectDoNothing) {
				$source = $orderInfo['source'];
				if ($source==1) {
					$data['url'] = yii\helpers\Url::to(['/pay/result.html','r'=>$no]);
				} else if ($source==2) {
					$data['url'] = yii\helpers\Url::to(['/weixin/pay/result','r'=>$no]);
				} else if($source==5) {
					$data['url'] = yii\helpers\Url::to(['/mobile/pay/result','r'=>$no]);
				}
			}else if(in_array($resultType,['redirect','result']) && $payOrderid) {
				$source = $orderInfo['source'];
				if ($source==1) {
					$data['url'] = yii\helpers\Url::to(['/pay/result.html','o'=>$payOrderid]);
				} else if ($source==2) {
					$data['url'] = yii\helpers\Url::to(['/weixin/pay/result','o'=>$payOrderid]);
				} else if($source==5) {
					$data['url'] = yii\helpers\Url::to(['/mobile/pay/result','o'=>$payOrderid]);
				} else {
					$data['url'] = true;
				}

			}
			return $data;
		} elseif ($orderInfo['type'] == '3') {
			$redis = new MyRedis();
			$payOrderid = $redis->get((Pay::THIRD_PAY_KEY).$no);

			if ($resultType=='notice') {

				$pkPay = new PkPay($orderInfo['user_id']);

				$redis->hset(self::ORDER_COUPON_KEY,[$payOrderid=>$redis->hget(self::RECHARGE_ORDER_KEY,$no)]);
				$pkPay->payByBalance($payOrderid);
				$redis->hdel(self::RECHARGE_ORDER_KEY,$no);

				return true;
			}

			$data['type'] = 3;
			$data['order'] = $payOrderid;


			if(in_array($resultType,['redirect','result']) && $payOrderid) {
				$data['url'] = true;
			}
			return $data;
		}

		return [];

	}

	/** 充值后相关操作
	 * @param $userId
	 * @param $rechargeMoney
	 */
	private function doRechargeSomeThing($userId, $rechargeMoney) {
		// 添加抽奖机会
		ActQualification::addNumByRecharge($userId, $rechargeMoney);
		//充值送礼
		$rechargeConfig = Config::getValueByKey('rechargeconfig');
		if ( isset($rechargeConfig['status']) && $rechargeConfig['status'] == 1) {
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
						$packet = Coupon::receivePacket($packetId, $userId, 'recharge');
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
