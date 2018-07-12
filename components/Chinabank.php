<?php
/**
 * User: hechen
 * Date: 15/10/14
 * Time: 下午6:05
 */

namespace app\components;

use yii\base\Component;

/**
* 网银在线支付
*/
class Chinabank extends Component
{
	public $memberId;
	public $backUrl;
	public $remarkUrl;
	public $key;

	public function pay($order,$amount,$bank){
		$data['memberId'] = $this->memberId;
		$data['order'] = $order;
		$data['amount'] = $amount;
		$data['moneyType'] = 'CNY';
		$data['backUrl'] = $this->backUrl;

		$str = $amount.$data['moneyType'].$order.$this->memberId.$this->backUrl.$this->key;//加密串
		$data['sign'] = strtoupper(md5($str));
		$data['bankId'] = $bank;
		$data['remarkUrl'] = '[url:='.$this->remarkUrl.']';
		
		return $this->createHtml($data);
	}
	

	public function createHtml($data){
		$html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		$html .= '<html>';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=gb2312">';
		$html .= '<title>支付跳转中...</title>';
		$html .= '</head>';
		$html .= '<body onLoad="javascript:document.E_FORM.submit()">';
		$html .= '<div>支付跳转中...</div>';
		$html .= '<form method="post" name="E_FORM" action="https://Pay3.chinabank.com.cn/PayGate?encoding=UTF-8">';
		$html .= '<input type="hidden" name="v_mid" value="'.$data['memberId'].'">';
		$html .= '<input type="hidden" name="v_oid" value="'.$data['order'].'">';
		$html .= '<input type="hidden" name="v_amount" value="'.$data['amount'].'">';
		$html .= '<input type="hidden" name="v_moneytype" value="'.$data['moneyType'].'">';
		$html .= '<input type="hidden" name="v_url" value="'.$data['backUrl'].'">';
		$html .= '<input type="hidden" name="v_md5info" value="'.$data['sign'].'">';
		if (intval($data['bankId']) > 0) {
			$html .= '<input type="hidden" name="pmode_id" value="'.$data['bankId'].'">';
		}
		$html .= '<input type="hidden" name="remark2" value="'.$data['remarkUrl'].'">';
		$html .= '</form>';
		$html .= '</body>';
		$html .= '</html>';

		return $html;
	}
}