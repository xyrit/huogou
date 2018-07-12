<?php
/**
 * User: hechen
 * Date: 15/10/14
 * Time: 下午6:05
 */

namespace app\components;

use yii\base\Component;
use yii\base\Exception;
use yii\web\Cookie;

/**
* 微信支付
*/
class Chatpay extends Component
{
	public function init(){
		require (__DIR__ . '/chat/WxPay.Api.php');
		require (__DIR__ . '/chat/WxPay.NativePay.php');
		require (__DIR__ . '/chat/WxPay.Data.php');
		require (__DIR__ . '/chat/WxPay.JsApiPay.php');
		require (__DIR__ . '/chat/WxPay.Notify.Custom.php');
	}

	public function createPayUrl($productId){
		$notify = new \NativePay();
		return $notify->GetPrePayUrl($productId);
	}
	/**
	 * 扫码支付
	 * @param  [type] $body      [description]
	 * @param  [type] $attach    [description]
	 * @param  [type] $no        [description]
	 * @param  [type] $fee       [description]
	 * @param  [type] $start     [description]
	 * @param  [type] $expire    [description]
	 * @param  [type] $tag       [description]
	 * @param  [type] $notifyurl [description]
	 * @param  [type] $productId [description]
	 * @return [type]            [description]
	 */
	public function pay($body,$attach,$no,$fee,$start,$expire,$tag,$notifyurl,$productId){
		$notify = new \NativePay();

		$input = new \WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$input->SetOut_trade_no($no);
		$input->SetTotal_fee($fee);
		$input->SetTime_start($start);
		$input->SetTime_expire($expire);
		$input->SetGoods_tag($tag);
		$input->SetNotify_url($notifyurl);
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($productId);
		$result = $notify->GetPayUrl($input);
		$url = $result["code_url"];

		return $url;
	}
	/**
	 * 微信公众号支付
	 * @param  [type] $body      [description]
	 * @param  [type] $attach    [description]
	 * @param  [type] $no        [description]
	 * @param  [type] $fee       [description]
	 * @param  [type] $start     [description]
	 * @param  [type] $expire    [description]
	 * @param  [type] $tag       [description]
	 * @param  [type] $notifyurl [description]
	 * @param  [type] $productId [description]
	 * @return [type]            [description]
	 */
	public function jsPay($body,$attach,$no,$fee,$start,$expire,$tag,$notifyurl,$productId)
	{
		$jsApiPay = new \JsApiPay();
		$openId = $jsApiPay->GetOpenid();
		if (!$openId) {
			return false;
		}
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$input->SetOut_trade_no($no);
		$input->SetTotal_fee($fee);
		$input->SetTime_start($start);
		$input->SetTime_expire($expire);
		$input->SetGoods_tag($tag);
		$input->SetNotify_url($notifyurl);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$input->SetProduct_id($productId);
		$order = \WxPayApi::unifiedOrder($input);

		$jsApiParameters = $jsApiPay->GetJsApiParameters($order);
		return $jsApiParameters;

	}
	/**
	 * 统一下单
	 * @param  [type] $openId    [description]
	 * @param  [type] $body      [description]
	 * @param  [type] $attach    [description]
	 * @param  [type] $no        [description]
	 * @param  [type] $fee       [description]
	 * @param  [type] $start     [description]
	 * @param  [type] $expire    [description]
	 * @param  [type] $tag       [description]
	 * @param  [type] $notifyurl [description]
	 * @param  [type] $productId [description]
	 * @return [type]            [description]
	 */
	public function unifiedOrder($openId,$body,$attach,$no,$fee,$start,$expire,$tag,$notifyurl,$productId){

		$input = new \WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$input->SetOut_trade_no($no);
		$input->SetTotal_fee($fee);
		$input->SetTime_start($start);
		$input->SetTime_expire($expire);
		$input->SetGoods_tag($tag);
		$input->SetNotify_url($notifyurl);
		$input->SetTrade_type("APP");
		$input->SetOpenid($openId);
		$input->SetProduct_id($productId);
		$result = \WxPayApi::unifiedOrder($input);
		
		return $result;
	}
	/**
	 * app调用微信支付
	 * @param  [type] $body      [description]
	 * @param  [type] $attach    [description]
	 * @param  [type] $no        [description]
	 * @param  [type] $fee       [description]
	 * @param  [type] $start     [description]
	 * @param  [type] $expire    [description]
	 * @param  [type] $tag       [description]
	 * @param  [type] $notifyurl [description]
	 * @param  [type] $productId [description]
	 * @return [type]            [description]
	 */
	public function payForApp($body,$attach,$no,$fee,$start,$expire,$tag,$notifyurl,$productId){
		$openId = '';
		$result = self::unifiedOrder($openId,$body,$attach,$no,$fee,$start,$expire,$tag,$notifyurl,$productId);
		$data = array();
		if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
			$data['appid'] = $result['appid'];
	 		$data['partnerId'] = $result['mch_id'];
	 		$data['prepayId'] = $result['prepay_id'];
	 		$data['nonceStr'] = $result['nonce_str'];
	 		$data['timeStamp'] = time();
	 		$data['package'] = "Sign=WXPay";
	 		krsort($data);
	 		$str = http_build_query($data).'&key='.\WxPayConfig::APP_KEY;
	 		$data['sign'] = strtoupper(md5($str));

	 	}
	 	return $data;
	}

	public function notifyCallBack($openId,$body,$attach,$no,$fee,$start,$expire,$tag,$notifyurl,$productId){
		if (!$openId || !$productId) {
			return false;
		}
		$result = self::unifiedOrder($openId,$body,$attach,$no,$fee,$start,$expire,$tag,$notifyurl,$productId);
		if(!array_key_exists("appid", $result) || !array_key_exists("mch_id", $result) || !array_key_exists("prepay_id", $result)){
		 	return false;
		}
		$reply = new \WxPayNotifyReply();
		$reply->SetData("appid", $result["appid"]);
		$reply->SetData("mch_id", $result["mch_id"]);
		$reply->SetData("nonce_str", \WxPayApi::getNonceStr());
		$reply->SetData("prepay_id", $result["prepay_id"]);
		$reply->SetData("result_code", "SUCCESS");
		$reply->SetData("err_code_des", "OK");
		$reply->SetReturn_code("SUCCESS");
		$reply->SetReturn_msg("OK");
		$reply->SetSign();
		\WxpayApi::replyNotify($reply->ToXml());
	}

	public function getNotify($data){
		if ($data->return_code == 'SUCCESS' && $data->result_code == 'SUCCESS') {
			$transaction_id = $data->transaction_id;
			$input = new \WxPayOrderQuery();
			$input->SetTransaction_id($transaction_id);
			$result = \WxPayApi::orderQuery($input);
			if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
				return $result;
			}
		}
	}

	public function setNotifyCallback($callback, $needSign = true)
	{
		$notify = new \WxPayNotifyCustom();
		$notify->Handle($callback,$needSign);
	}
}