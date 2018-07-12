<?php

namespace app\modules\api\controllers;

use yii;
use app\models\User as UserModel;
use app\services\User;
use app\models\PaymentOrderDistribution;
use app\validators\MobileValidator;
use yii\validators\EmailValidator;

/**
* 读取推广数据
*/
class SpreadController extends BaseController
{
	public $params=array();
	const KEY = 'x8r5kmOMTaCRUMNsZx1q';

	public function init()
	{
		parent::init();

		$this->params['time'] = Yii::$app->request->get('time');
		$this->params['account'] = Yii::$app->request->get('account');
		$this->params['terminal'] = Yii::$app->request->get('terminal');
		$sign = Yii::$app->request->get('sign');
		ksort($this->params);
		unset($this->params['sign']);
		
		$checkSign = md5(md5(http_build_query($this->params)).self::KEY);
		if (!$this->params || $checkSign != $sign) {
			echo json_encode(array('code'=>0,'message'=>'非法请求'));
			exit;
		}
	}

	public function actionGetMembers(){
		$where = array();
		if (!$this->params['terminal']) {
			return array('code'=>0,'message'=>'非法请求');
		}else{
			$where['reg_terminal'] = $this->params['terminal'];
		}
		if ($this->params['account']) {
			$where['spread_source'] = $this->params['account'];
		}
		if ($this->params['time']) {
			$where['created_at'] = $this->params['time'];
		}
		$query = UserModel::find()->select('id,email,phone,nickname,reg_ip,created_at,spread_source')->where($where);
		
		$users = $query->asArray()->all();
		
		foreach ($users as $key => &$value) {
			if ($value['nickname']) {
                $value['username'] = $value['nickname'];
            } elseif ($value['phone']) {
                $value['username'] = User::privatePhone($value['phone']);
            } elseif ($value['email']) {
                $value['username'] = User::privateEmail($value['email']);
            }
			unset($value['email']);
			unset($value['phone']);
			unset($value['nickname']);
		}
		return array('code'=>100,'data'=>$users);
	}

	public function actionGetRecharges(){
		$where = " ";
		if ($this->params['account']) {
			$where .= $where." and spread_source = '".$this->params['account']."'";
		}else{
			return [];
		}
		
		$sql = "";
		for ($i=0; $i < 10; $i++) { 
			$table = 'recharge_orders_10'.$i;
			$sql .= " (select user_id,user_account,money,pay_time,spread_source,bank from ".$table." where status = 1 and payment < 6 and (source = 3 or source = 4) and pay_time > ".$this->params['time'].$where.") union all ";
		}
		$sql = substr($sql,0,-11);
		$sql = "select * from (".$sql.")a";	
		$_payList = \Yii::$app->db->createCommand($sql)->queryAll();
		$payList = array();
		$uids = [];
		foreach ($_payList as $key => $value) {
			$payList[$key]['uid'] = $value['user_id'];
			$uids[] = $value['user_id'];
			
			$mobileValidator = new MobileValidator();
        	$mValid = $mobileValidator->validate($value['user_account']);
        	if ($mValid) {
				$payList[$key]['username'] = User::privatePhone($value['user_account']);
        	}
        	$emailValidator = new EmailValidator();
        	$eValid = $emailValidator->validate($value['user_account']);
        	if ($eValid) {
        		$payList[$key]['username'] = User::privateEmail($value['user_account']);
        	}

        	if (!isset($payList[$key]['username'])) {
        		$payList[$key]['username'] = $value['user_account'];
        	}

			$payList[$key]['amount'] = $value['money'];
			$payList[$key]['time'] = $value['pay_time'];
			$payList[$key]['spread_source'] = $value['spread_source'];
			$payList[$key]['bank'] = $value['bank'];
		}

		$_usersInfo = UserModel::find()->select('id,created_at')->where(['in','id',$uids])->asArray()->all();
		$usersInfo = [];
		foreach ($_usersInfo as $key => $value) {
			$usersInfo[$value['id']] = $value['created_at'];
		}

		foreach ($payList as $key => &$value) {
			$value['created_at'] = $usersInfo[$value['uid']];
		}


		return array('code'=>100,'data'=>$payList);
	}

	public function actionGetMemberInfo()
	{
		$uid = explode('_',$this->params['account'])[1];
		
		$userInfo = UserModel::find()->select('created_at,reg_ip')->where(['id'=>$uid])->asArray()->one();
		return array('code'=>100,'info'=>$userInfo);
	}
}