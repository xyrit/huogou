<?php
/**
 * Created by Joan.
 * User: Joan
 * Date: 2016/8/10
 * Time: 10:59
 * 聚合数据-手机话费充值API调用类
 */
namespace app\components;

use yii\base\Component;

class Phonefee extends Component
{
	
	private $appkey;
	
	private $openid;
	
	private $telCheckUrl = 'http://op.juhe.cn/ofpay/mobile/telcheck';
	
	private $telQueryUrl = 'http://op.juhe.cn/ofpay/mobile/telquery';
	
	private $submitUrl = 'http://op.juhe.cn/ofpay/mobile/onlineorder';
	
	private $staUrl = 'http://op.juhe.cn/ofpay/mobile/ordersta';
	
	public function __construct($appkey, $openid)
	{
		$this->appkey = $appkey;
		$this->openid = $openid;
	}
	
	/**
	 * 根据手机号码及面额查询是否支持充值
	 * @param  string $mobile [手机号码]
	 * @param  int $pervalue [充值金额]
	 * @return  boolean
	 */
	public function telcheck($mobile, $pervalue)
	{
		$params = 'key=' . $this->appkey . '&phoneno=' . $mobile . '&cardnum=' . $pervalue;
		$content = $this->juhecurl($this->telCheckUrl, $params);
		$result = $this->_returnArray($content);
		if ($result['error_code'] == '0') {
			return true;
		} else {
			return $result;
		}
	}
	
	/**
	 * 根据手机号码和面额获取商品信息
	 * @param  string $mobile [手机号码]
	 * @param  int $pervalue [充值金额]
	 * @return  array
	 */
	public function telquery($mobile, $pervalue)
	{
		$params = 'key=' . $this->appkey . '&phoneno=' . $mobile . '&cardnum=' . $pervalue;
		$content = $this->juhecurl($this->telQueryUrl, $params);
		return $this->_returnArray($content);
	}
	
	/**
	 * 提交话费充值
	 * @param  [string] $mobile   [手机号码]
	 * @param  [int] $pervalue [充值面额]
	 * @param  [string] $orderid  [自定义单号]
	 * @return  [array]
	 */
	public function telcz($mobile, $pervalue, $orderid)
	{
		$sign = md5($this->openid . $this->appkey . $mobile . $pervalue . $orderid);//校验值计算
		$params = 'key=' . $this->appkey . '&phoneno=' . $mobile . '&cardnum=' . $pervalue . '&orderid=' . $orderid . '&sign=' . $sign;
		$content = $this->juhecurl($this->submitUrl, $params, 1);
		return $this->_returnArray($content);
	}
	
	/**
	 * 查询订单的充值状态
	 * @param  [string] $orderid [自定义单号]
	 * @return  [array]
	 */
	public function sta($orderid)
	{
		$params = 'key=' . $this->appkey . '&orderid=' . $orderid;
		$content = $this->juhecurl($this->staUrl, $params);
		return $this->_returnArray($content);
	}
	
	/**
	 * 将JSON内容转为数据，并返回
	 * @param string $content [内容]
	 * @return array
	 */
	public function _returnArray($content)
	{
		return json_decode($content, true);
	}
	
	/**
	 * 请求接口返回内容
	 * @param  string $url [请求的URL地址]
	 * @param  string $params [请求的参数]
	 * @param  int $ipost [是否采用POST形式]
	 * @return  string
	 */
	public function juhecurl($url, $params = false, $ispost = 0)
	{
		$response = file_get_contents($url . '?' . $params);
		return $response;
		$httpInfo = array();
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($ispost) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_URL, $url);
		} else {
			if ($params) {
				curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
			} else {
				curl_setopt($ch, CURLOPT_URL, $url);
			}
		}
		$response = curl_exec($ch);
		if ($response === FALSE) {
			echo "CURL Error: " . curl_error($ch);
			return false;
		}
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$httpInfo = array_merge($httpInfo, curl_getinfo($ch));
		curl_close($ch);
		return $response;
	}
	
	/**
	 * @param $payTo
	 * @param $nums
	 * @return array
	 * 获取话费信息
	 */
	public function getProductInfo($payTo, $nums)
	{
		$getProduct = $this->telcheck($payTo, $nums);

		if ($getProduct) {
			$getProduct = $this->telquery($payTo, $nums);
			if (isset($getProduct['error_code']) && $getProduct['error_code'] == 0) {
				return ['code' => '100', 'result' => $getProduct['result'], 'reason' => $getProduct['reason']];
			}
		}
		return ['code' => $getProduct['error_code'], 'result' => $getProduct['result'], 'reason' => $getProduct['reason']];
		
	}
	
	/**
	 * @param $payTo
	 * @param $nums
	 * @param $winOrder
	 * @return array
	 * 话费充值
	 */
	public function onlinePay($payTo, $nums, $winOrder)
	{
		$winOrder = date('Ymd') . 'F' . $nums . 'U' . $winOrder;
		$getProduct = $this->telcz($payTo, $nums, $winOrder);
		$getProduct['result']['winOrder'] = $winOrder;
		if (isset($getProduct['error_code']) && $getProduct['error_code'] == 0) {
			return ['code' => '100', 'result' => $getProduct['result'], 'reason' => $getProduct['reason']];
		}
		return ['code' => $getProduct['error_code'], 'result' => $getProduct['result'], 'reason' => $getProduct['reason']];
		
	}
	
	/**
	 * 在线充值结果回调
	 * @param  [type] $params
	 * @return bool
	 */
	public function onlinePayBack($params)
	{
		
		if (!$params) exit('params is null');
		$orderInfo = \app\models\VirtualPurchaseOrder::findOne(['exchange_no' => $params['sporder_id']]);
		
		if (!$orderInfo) {
			return false;
		}
		
		$orderInfo->status = $params['sta'];
		$orderInfo->update_time = time();
		$orderInfo->save(false);
		$model = \app\models\Order::findOne(['id' => substr(strstr($params['orderid'], 'U'), 1, 30)]);
		
		if (intval($params['sta']) == 1) {
			
			$model->status = 8;
			$model->save(false);
			
		} else {
			$model->status = 1;
			$model->save(false);
		}
		return true;
	}
	
}