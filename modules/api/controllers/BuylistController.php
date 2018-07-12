<?php

namespace app\modules\api\controllers;

use yii;
use app\models\PaymentOrderItemDistribution as Pay;
use app\helpers\DateFormat;
use app\services\Product;
use app\services\User;

class BuylistController extends BaseController
{
	public function actionIndex(){
		$perpage = 30;
		$params = Yii::$app->request;
		$page = $params->get('page');

		$startTime = strtotime($params->get('starttime'));
		$endTime = strtotime($params->get('endtime'));
		
		if ($endTime - $startTime > 3600) {
			$endTime = $startTime + 3600;
		}
		$where['starttime'] = $startTime;
		$where['endtime'] = $endTime;
		
		$list = Pay::getBuylist($where,$page,$perpage,'10','payment_order_id,item_buy_time,nums,product_id,period_number,user_id','item_buy_time desc');
		$users = $products = $data = '';
		foreach ($list['list'] as $key => $value) {
			$users[] = $value['user_id'];
			$products[] = $value['product_id'];
			// $data[$key]['buy_time'] = DateFormat::microDate($value['item_buy_time']);
			// $data[$key]['buy_nums'] = $value['nums'];
		}
		$userInfo = User::baseInfo($users);
		$productInfo = Product::info($products);
		
		foreach ($list['list'] as $key => $value) {
			$data[$key]['buy_time'] = DateFormat::microDate($value['item_buy_time']);
			$data[$key]['buy_nums'] = $value['nums'];
			$data[$key]['user_name'] = $userInfo[$value['user_id']]['username'];
			$data[$key]['user_id'] = $userInfo[$value['user_id']]['home_id'];
			$data[$key]['product'] = ''.$productInfo[$value['product_id']]['name'];
			$data[$key]['product_id'] = $value['product_id'];
		}

		return array('list'=>$data,'page'=>$page,'total'=>$list['pagination']->totalCount,'totalPage'=>ceil($list['pagination']->totalCount/$perpage));
	}

	public function actionNewBuyList(){
		$count = Yii::$app->request->get('num',100);
		$time = time();
		$where['starttime'] = $time - 3600*24;
		$where['endtime'] = $time;
		$list = Pay::getBuylist($where,1,$count,'10','payment_order_id,item_buy_time,nums,product_id,period_number,user_id','item_buy_time desc');
		$users = $products = $data = '';
		foreach ($list['list'] as $key => $value) {
			$users[] = $value['user_id'];
			$products[] = $value['product_id'];
		}
		$userInfo = User::baseInfo($users);
		$productInfo = Product::info($products);
		
		foreach ($list['list'] as $key => $value) {
			$data[$key]['buy_time'] = DateFormat::microDate($value['item_buy_time']);
			$data[$key]['buy_time2'] = DateFormat::formatTime($value['item_buy_time']);
			$data[$key]['buy_nums'] = $value['nums'];
			$data[$key]['user_name'] = $userInfo[$value['user_id']]['username'];
			$data[$key]['user_id'] = $userInfo[$value['user_id']]['home_id'];
			$data[$key]['product'] = ''.$productInfo[$value['product_id']]['name'];
			$data[$key]['product_id'] = $value['product_id'];
			$data[$key]['avatar'] = $userInfo[$value['user_id']]['avatar'];
		}

		return array('list'=>$data);
	}
}