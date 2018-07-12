<?php
/**
 * User: hechen
 * Date: 15/10/14
 * Time: 下午10:51
 */

namespace app\controllers;

use app\services\Thirdpay;
use Yii;

/**
* 网银在线回调接口
*/
class ChinabankController extends BaseController
{
	public $enableCsrfValidation = false;
	public $key;

	public function init()
	{
		$this->key = Yii::$app->chinabank->key;
	}

	public function actionReceive(){
		$key = $this->key;
		$request = Yii::$app->request;
		$order = $request->post('v_oid');
		$bank = $request->post('v_pmode');
		$backStatus = $request->post('v_pstatus');
		$pstring = $request->post('v_pstring');
		$money = $request->post('v_amount');
		$moneyType = $request->post('v_moneytype');
		$sign  = $request->post('v_md5str');
		
		if (!$order) {
			return false;
		}

		// $remark1   =trim($_POST['remark1' ]);      //备注字段1
		// $remark2   =trim($_POST['remark2' ]);     //备注字段2
		
		$md5string=strtoupper(md5($order.$backStatus.$money.$moneyType.$key));

		$third = new Thirdpay();
		$orderInfo = $third->getOrderByNo($order);

		$data = array(
			'order' => $order,
			'bank' =>$bank,
			'status' => $backStatus,
			'pstring' => urlencode(iconv("GBK","UTF8",$pstring)),
			'money' => $money,
			'moneyType' => $moneyType,
			'sign' => $sign
		);

		if ($orderInfo && $sign == $md5string && $backStatus == "20" && $orderInfo['post_money'] == $money) {
			$customStr = $third->getCustomStr($order);
			$data = $third->result('redirect',$order,$customStr,$data);
			return $this->redirect($data['url']);
		}
	}

	public function actionAutoReceive(){
		$key = $this->key;
		$request = Yii::$app->request;
		$order = $request->post('v_oid');
		$bank = $request->post('v_pmode');
		$backStatus = $request->post('v_pstatus');
		$pstring = $request->post('v_pstring');
		$money = $request->post('v_amount');
		$moneyType = $request->post('v_moneytype');
		$sign  = $request->post('v_md5str');
		
		if (!$order) {
			echo "error";
		}

		// $remark1   =trim($_POST['remark1' ]);      //备注字段1
		// $remark2   =trim($_POST['remark2' ]);     //备注字段2
		
		$md5string=strtoupper(md5($order.$backStatus.$money.$moneyType.$key));

		$third = new Thirdpay();
		$orderInfo = $third->getOrderByNo($order);

		$data = array(
			'order' => $order,
			'bank' =>$bank,
			'status' => $backStatus,
			'pstring' => urlencode(iconv("GBK","UTF8",$pstring)),
			'money' => $money,
			'moneyType' => $moneyType,
			'sign' => $sign
		);

		if ($orderInfo && $sign == $md5string && $backStatus == "20" && $orderInfo['post_money'] == $money) {
			$customStr = $third->getCustomStr($order);
			echo 'ok';
			$third->result('notice',$order,$customStr,$data);
		} else {
			echo "error";
		}
	}
}