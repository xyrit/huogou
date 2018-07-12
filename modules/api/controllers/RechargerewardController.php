<?php
/**
 * 充值活动
 * @authors hechen
 * @date    2016-04-07 10:00:47
 * @version $Id$
 */
namespace app\modules\api\controllers;

use yii;
use app\models\RechargeReward;
use app\models\RechargeRewardLog;
use app\models\Config;
use app\services\User;

class RechargerewardController extends BaseController {

	public $ra_id;

	public function init()
	{
        parent::init();
		$config = Config::getValueByKey('rechargeconfig');
    	$this->ra_id = $config['ra_id'];
	}
    
    public function actionIndex()
    {
    	$rechargeRewardInfo = RechargeReward::find()->where(['id'=>$this->ra_id])->asArray()->one();
    	$prizes = json_decode($rechargeRewardInfo['prizes'],true);
    	$rechargeLog = RechargeRewardLog::find()->where(['user_id'=>$this->userId,'number'=>$this->ra_id])->asArray()->all();
    	$log = [];
    	foreach ($rechargeLog as $key => $value) {
    		$log[$value['level']] = $value;
    	}
    	$data = [];
    	foreach ($prizes as $key => $value) {
    		$data[$key]['demand'] = $value['min'];
    		if ($value['max']) {
    			$data[$key]['demand'] .= '~'.$value['max'].'元';
    		}else{
    			$data[$key]['demand'] .= '元以上';
    		}
    		$data[$key]['prize'] = $value['prizename'];
    		if (isset($log[$key])) {
    			$data[$key]['status'] = 1;
    			$data[$key]['notice'] = $log[$key]['notice'];
    			if ($log[$key]['notice'] == 0) {
    				RechargeRewardLog::updateAll(['notice'=>1],['id'=>$log[$key]['id']]);
    			}
    		}else{
    			$data[$key]['status'] = 0;
    			$data[$key]['notice'] = 0;
    		}
    	}
    	return $data;
    }

    public function actionList()
    {
    	$rechargeLog = RechargeRewardLog::find()->select('recharge_reward_log.amount,recharge_reward_log.prize,recharge_reward_log.user_id,users.home_id,users.phone,users.email,users.nickname')->leftJoin('users','recharge_reward_log.user_id = users.id')->where(['number'=>$this->ra_id])->limit(10)->orderBy('create_time desc')->asArray()->all();
    	$data = [];
    	foreach ($rechargeLog as $key => $value) {
    		$data[$key]['amount'] = $value['amount'];
    		$data[$key]['prize'] = $value['prize'];
    		$data[$key]['user_id'] = $value['user_id'];
    		$data[$key]['home_id'] = $value['home_id'];
    		if ($value['nickname']) {
                $data[$key]['user_name'] = $value['nickname'];
            } elseif ($value['phone']) {
                $data[$key]['user_name'] = User::privatePhone($value['phone']);
            } elseif ($value['email']) {
                $data[$key]['user_name'] = User::privateEmail($value['email']);
            }
    	}
    	return $data;
    }

}