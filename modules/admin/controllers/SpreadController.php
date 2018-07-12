<?php

	namespace app\modules\admin\controllers;

	use Yii;
	use app\models\User;
	use app\models\RechargeOrderDistribution;

	/**
	* 推广数据
	*/
	class SpreadController extends BaseController
	{
		public $channel = array(
				'huogou' => '自主注册',
				'3' => '微信',
				'wy_17' => '微易17',
				'wy_23' => '微易23',
				'wy_24' => '微易24'
			);

		public function actionIndex(){
			$list = array();
			$users = User::find()->select('count(1) as count,spread_source')->groupBy('spread_source')->asArray()->all();
			foreach ($users as $key => $value) {
				$source = '';
				if (!$value['spread_source']) {
					$source = 'huogou';
					$source_name = $this->channel['huogou'];
				}else {
					$source = $value['spread_source'];
					if (isset($this->channel[$source])) {
						$source_name = $this->channel[$source];
					}else{
						$source_name = $source;
					}
				}
				$list[$source] = array(
					'reg_nums'=>$value['count'],
					'spread_source' => $source,
					'spread_source_name'=>$source_name,
					'recharge_nums' => 0,
					'recharge_money' => 0,
					'consume_nums' => 0,
					'consume_money' => 0,
					'consume_point' => 0
				);
			}
			$db = \Yii::$app->db;
			$rechargeSql = "";
			for ($i=0; $i < 10; $i++) { 
				$rechargeSql .= "select sum(money) as recharge,spread_source,count(distinct user_id) as count from recharge_orders_10".$i." where status = 1 and payment <> 6 group by spread_source union all ";
			}
			$rechargeSql = substr($rechargeSql,0,-11);
			$rechargeSql = "select sum(recharge) as recharge,spread_source,sum(count) as count from (".$rechargeSql.") a group by spread_source";
			$recharge = $db->createCommand($rechargeSql)->queryAll();
			foreach ($recharge as $key => $value) {
				$source = '';
				if (!$value['spread_source']) {
					$source = 'huogou';
				}else{
					$source = $value['spread_source'];
				}
				$list[$source]['recharge_money'] = $value['recharge'];
				$list[$source]['recharge_nums'] = $value['count'];
			}

			$consumeSql = "";
			for ($i=0; $i < 10; $i++) { 
				$consumeSql .= "select sum(money) as consume,spread_source,count(distinct user_id) as count,sum(point) as point from payment_orders_10".$i." where status = 1 and payment <> 6 group by spread_source union all ";
			}
			$consumeSql = substr($consumeSql,0,-11);
			$consumeSql = "select sum(consume) as consume,spread_source,sum(count) as count,sum(point) as point from (".$consumeSql.") a group by spread_source";
			$consume = $db->createCommand($consumeSql)->queryAll();

			foreach ($consume as $key => $value) {
				$source = '';
				if (!$value['spread_source']) {
					$source = 'huogou';
				}else{
					$source = $value['spread_source'];
				}
				$list[$source]['consume_money'] = $value['consume'];
				$list[$source]['consume_nums'] = $value['count'];
				$list[$source]['consume_point'] = $value['point'];
			}

			return $this->render('index', [
	            'list' => $list,
	        ]);
		}
	}