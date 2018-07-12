<?php
/**
 * User: hechen
 * Date: 15/10/14
 * Time: 下午6:05
 */

namespace app\components;

use yii\base\Component;
use yii\base\Exception;


/**
* 银联支付
*/
class Unionpay extends Component
{
	public $pcMemberId;
	public $pcSignCertPath;
	public $pcSignCertPwd;

	public $wapMemberId;
	public $wapSignCertPath;
	public $wapSignCertPwd;
	
	function init(){
		require (__DIR__ . '/unionpay/common.php');
	}

	public function appPay($orderId,$amount,$frontUrl,$backUrl)
	{
		$params = array(
			//以下信息非特殊情况不需要改动
			'version' => '5.0.0',                 //版本号
			'encoding' => 'utf-8',				  //编码方式
			'certId' => getCertId(\Yii::getAlias($this->wapSignCertPath),$this->wapSignCertPwd),	      //证书ID
			'txnType' => '01',				      //交易类型
			'txnSubType' => '01',				  //交易子类
			'bizType' => '000201',				  //业务类型
			'frontUrl' =>  $frontUrl,  //前台通知地址
			'backUrl' => $backUrl,	  //后台通知地址
			'signMethod' => '01',	              //签名方法
			'channelType' => '08',	              //渠道类型，07-PC，08-手机
			'accessType' => '0',		          //接入类型
			'currencyCode' => '156',	          //交易币种，境内商户固定156

			//TODO 以下信息需要填写
			'merId' => $this->wapMemberId,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
			'orderId' => $orderId,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
			'txnTime' => date("YmdHis",time()),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
			'txnAmt' => $amount*100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
//			'orderDesc' => '订单描述',  //订单描述，可不上送，上送时控件中会显示该信息
			// 'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

			//TODO 其他特殊用法请查看 special_use_purchase.php
		);
		sign ( $params, \Yii::getAlias($this->wapSignCertPath), $this->wapSignCertPwd );

		// 发送信息到后台
		$result = sendHttpRequest ( $params, SDK_App_Request_Url );

		//返回结果展示
		$result_arr = convertStringToArray ( $result );

		return $result_arr;
	}

	public function pay($orderId,$amount,$frontUrl,$backUrl)
	{
		$params = array(
			//以下信息非特殊情况不需要改动
			'version' => '5.0.0',                 //版本号
			'encoding' => 'utf-8',				  //编码方式
			'certId' => getCertId(\Yii::getAlias($this->pcSignCertPath),$this->pcSignCertPwd),	      //证书ID
			'txnType' => '01',				      //交易类型
			'txnSubType' => '01',				  //交易子类
			'bizType' => '000201',				  //业务类型
			'frontUrl' =>  $frontUrl,  //前台通知地址
			'backUrl' => $backUrl,	  //后台通知地址
			'signMethod' => '01',	              //签名方法
			'channelType' => '07',	              //渠道类型，07-PC，08-手机
			'accessType' => '0',		          //接入类型
			'currencyCode' => '156',	          //交易币种，境内商户固定156
			
			//TODO 以下信息需要填写
			'merId' => $this->pcMemberId,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
			'orderId' => $orderId,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
			'txnTime' => date("YmdHis",time()),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
			'txnAmt' => $amount*100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
	 		// 'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

			//TODO 其他特殊用法请查看 special_use_purchase.php
		);
		sign ( $params, \Yii::getAlias($this->pcSignCertPath), $this->pcSignCertPwd);
		$uri = SDK_FRONT_TRANS_URL;
		$html_form = create_html ( $params, $uri );
		return $html_form;
	}

	public function wapPay($orderId,$amount,$frontUrl,$backUrl)
	{
		$params = array(
			//以下信息非特殊情况不需要改动
			'version' => '5.0.0',                 //版本号
			'encoding' => 'utf-8',				  //编码方式
			'certId' => getCertId(\Yii::getAlias($this->wapSignCertPath),$this->wapSignCertPwd),	      //证书ID
			'txnType' => '01',				      //交易类型
			'txnSubType' => '01',				  //交易子类
			'bizType' => '000201',				  //业务类型
			'frontUrl' =>  $frontUrl,  //前台通知地址
			'backUrl' => $backUrl,	  //后台通知地址
			'signMethod' => '01',	              //签名方法
			'channelType' => '08',	              //渠道类型，07-PC，08-手机
			'accessType' => '0',		          //接入类型
			'currencyCode' => '156',	          //交易币种，境内商户固定156

			//TODO 以下信息需要填写
			'merId' => $this->wapMemberId,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
			'orderId' => $orderId,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
			'txnTime' => date("YmdHis",time()),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
			'txnAmt' => $amount*100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
			// 'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

			//TODO 其他特殊用法请查看 special_use_purchase.php
		);
		sign ( $params, \Yii::getAlias($this->wapSignCertPath), $this->wapSignCertPwd );
		$uri = SDK_FRONT_TRANS_URL;
		$html_form = create_html ( $params, $uri );
		return $html_form;
	}

	public function backNofity($successCallback,$failCallback)
	{

		if (isset ( $_POST ['signature'] )) {
			if (verify($_POST)) {
				$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
				$respCode = $_POST ['respCode']; //判断respCode=00或A6即可认为交易成功
				if ($respCode=='00'||$respCode=='A6') {
					return call_user_func($successCallback,$_POST);
				}
			}
			//如果卡号我们业务配了会返回且配了需要加密的话，请按此方法解密
			//if(array_key_exists ("accNo", $_POST)){
			//	$accNo = decryptData($_POST["accNo"]);
			//}
		}

		return call_user_func($failCallback,$_POST);
	}

	public function frontNotify($successCallback,$failCallback)
	{

		if (isset ( $_POST ['signature'] )) {
			if (verify($_POST)) {
				$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
				$respCode = $_POST ['respCode']; //判断respCode=00或A6即可认为交易成功
				if ($respCode=='00'||$respCode=='A6') {
					return call_user_func($successCallback,$_POST);
				}
			}
			//如果卡号我们业务配了会返回且配了需要加密的话，请按此方法解密
			//if(array_key_exists ("accNo", $_POST)){
			//	$accNo = decryptData($_POST["accNo"]);
			//}
		}

		return call_user_func($failCallback,$_POST);
	}
}