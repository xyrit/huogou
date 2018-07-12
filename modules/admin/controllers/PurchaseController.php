<?php

	namespace app\modules\admin\controllers;

	use Yii;
	use app\models\Product;
	use app\models\Purchase;
	use yii\helpers\Json;
	use yii\components\Zhifuka;
	use app\models\VirtualPurchaseOrder;
	use app\models\VirtualDepot;

	/**
	* 采购
	*/
	class PurchaseController extends BaseController
	{
		private $virtualProducts = array(
					'0' => array('name'=>'10元移动充值卡','parValue'=>'10','perMoney'=>'9.94','mark'=>'yd0006','type' => 'yd'),
					'1' => array('name'=>'30元移动充值卡','parValue'=>'30','perMoney'=>'29.4','mark'=>'yd0008','type' => 'yd'),
					'2' => array('name'=>'50元移动充值卡','parValue'=>'50','perMoney'=>'49.4','mark'=>'yd0009','type' => 'yd'),
					'3' => array('name'=>'100元移动充值卡','parValue'=>'100','perMoney'=>'99.4','mark'=>'yd0009','type' => 'yd'),
					'4' => array('name'=>'30元联通充值卡','parValue'=>'30','perMoney'=>'29.4','mark'=>'lt0003','type'=>'lt'),
					'5' => array('name'=>'50元联通充值卡','parValue'=>'50','perMoney'=>'49.4','mark'=>'lt0003','type'=>'lt'),
					'6' => array('name'=>'100元联通充值卡','parValue'=>'100','perMoney'=>'99.4','mark'=>'lt0003','type'=>'lt'),
					'7' => array('name'=>'30元电信充值卡','parValue'=>'30','perMoney'=>'29.4','mark'=>'dx0002','type'=>'dx'),
					'8' => array('name'=>'50元电信充值卡','parValue'=>'50','perMoney'=>'49.4','mark'=>'dx0002','type'=>'dx'),
					'9' => array('name'=>'100元电信充值卡','parValue'=>'100','perMoney'=>'99.4','mark'=>'dx0002','type'=>'dx'),
					// '10' => array('name'=>'10元Q币充值卡','parValue'=>'10','perMoney'=>'9.4','mark'=>'yd0006','type'=>'qb'),
			);

		public function actionIndex(){
			$request = Yii::$app->request;
			$page = $request->get("page",'1');
			$type = $request->get('type');
			$startTime = $request->get('startTime');
			$endTime = $request->get('endTime');
			$where = 'vpo.status = 1';
			$condition = [];
			if ($type) {
				$where .= " and p.product_name = '".$type."'";
				$condition['type'] = $type;
			}
			if ($startTime) {
				$where .= " and vpo.create_time >= '".strtotime($startTime)."'";
				$condition['startTime'] = $startTime;
			}
			if ($endTime) {
				$where .= " and vpo.create_time <= '".strtotime($endTime)."'";
				$condition['endTime'] = $endTime;
			}
			$list = Purchase::getList($where,$page,'20');

			return $this->render('index', [
	            'list' => $list['list'],
	            'condition' => $condition,
	            'pagination' => $list['pagination']
	        ]);
		}

		public function actionAdd(){
			$productList = Product::find()->where(['and','marketable=1',['or','delivery_id=3','delivery_id=4']])->all();
			$products = array();
			foreach ($productList as $key => $value) {
				$products[$value['id']] = $value['name'];
			}
			if (Yii::$app->request->isPost) {
				
			}
			return $this->render('add', [
				'model' => new Purchase,
	            'productList' => $products
	        ]);
		}

		public function actionVirtualAdd(){
			$model = new Purchase();
			$request = Yii::$app->request;
			if ($request->isPost) {
				if ($model->load($request->post())) {
					$id = $request->post('Purchase')['product_name'];
					$model->admin_id = Yii::$app->admin->identity->id;
					$model->product_id = 0;
					$model->type = 2;
					$model->product_id = 0;
					$model->product_name = $this->virtualProducts[$id]['name'];
					$model->per_money = $this->virtualProducts[$id]['perMoney'];
					$model->status = 0;
					$time = time();
					$model->create_time = $time;
					$model->last_update_time = $time;
					$model->schedule = Json::encode(
							array(
								array(
									'user'=>Yii::$app->admin->identity->username,
									'schedule' => '提交申请',
									'time' => date("Y-m-d H:i:s",$time)
								)
							)
						);
					$model->extra = Json::encode(
							array(
								'vid' => $id,
								'supplier' => '星启天',
								'interface' => 'zhifuka',
								'parValue' => $this->virtualProducts[$id]['parValue']
							)
						);
					$rs = $model->save();

					if ($rs) {
						return $this->redirect(['/admin/purchase']);
					}
				}
			}
			$productList = array();
			foreach ($this->virtualProducts as $key => $value) {
				$productList[$key] = $value['name'];
			}
			return $this->render('virtual', [
				'model' => $model,
	            'productList' => $productList,
	            'virtualProducts' => $this->virtualProducts,
	            'jsProducts' => Json::encode($this->virtualProducts)
	        ]);
		}

		public function actionInfo(){
			$id = Yii::$app->request->get('id');
			$info = Purchase::getInfoById($id);
			
			$info['schedule'] = Json::decode($info['schedule'],true);

			if ($info['type'] == '2') {
				$order = VirtualPurchaseOrder::find()->where(['purchaseid' => $id])->asArray()->one();
				$cards = VirtualDepot::find()->where(['orderid'=>$order['orderid']])->asArray()->all();
				$info['order'] = $order;
				$info['cards'] = $cards;
			}

			return $this->render('info', [
	            'info' => $info
	        ]);
		}

		public function actionPass(){
			$id = Yii::$app->request->get('id');

			$info = Purchase::getInfoById($id);

			if ($info['type'] == '2' && $info['status'] == '0') {
				$config = Json::decode($info['extra'],true);
				if ($config['interface'] == 'zhifuka') {
					$vid = $config['vid'];
					$parValue = $config['parValue'];
					$order = VirtualPurchaseOrder::createOrder($vid,$parValue,$info['nums']);
					if ($order) {
						$mark = $this->virtualProducts[$config['vid']]['mark'];
						$result = Yii::$app->zhifuka->buyCard($mark,$parValue,$info['nums'],$order,\Yii::$app->request->userIP);
						if ($result['code'] == '100') {
							VirtualPurchaseOrder::updateAll(
									array(
										'status' => 1,
										'update_time' => time(),
										'exchange_no' => $result['msg']['exchange_id'],
										'result' => $result['msg']['result'],
									),array('orderid'=>$result['msg']['orderid'])
								);
							$schedule = Json::decode($info['schedule'],true);
							$newSchedule = array(
									'user'=>Yii::$app->admin->identity->username,
									'schedule' => '通过申请',
									'time' => date("Y-m-d H:i:s",time())
								);
							array_push($schedule, $newSchedule);
							Purchase::updateAll(
									array('status'=>2,'schedule'=>Json::encode($schedule)),
									array('id'=>$id)
								);
							$virtualDepotField = ['orderid','card','pwd','par_value','type'];
							$virtualDepotValue = [];
							foreach ($result['msg']['cards'] as $key => $value) {
								$virtualDepotValue[] = [$order,$value['card'],$value['pwd'],$parValue,$this->virtualProducts[$config['vid']]['type']];
							}
							$db = \Yii::$app->db;
							$db->createCommand()->batchInsert('virtual_depot',$virtualDepotField,$virtualDepotValue)->execute();
						}else{
							VirtualPurchaseOrder::updateAll(
								array(
									'update_time' => time(),
									'result' => $result['msg']
								),array('orderid'=>$order)
							);
						}
					}
				}
			}
		}
	}