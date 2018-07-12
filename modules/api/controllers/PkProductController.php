<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午2:28
 */
namespace app\modules\api\controllers;

use app\models\ShareTopic;
use app\services\Category;
use app\services\Period;
use app\models\Period as PeriodModel;
use app\services\PkPeriod;
use yii;
use app\services\PkProduct;
use app\models\FollowProduct;
use app\models\PeriodBuylistDistribution;
use app\models\ActivityProducts;
use app\helpers\Brower;
class PkProductController extends BaseController
{

	/**
	 * 商品列表
	 * @return jsonp [description]
	 */
	public function actionList(){
		$get = Yii::$app->request;
		$cid = $get->get('cid');
		$bid = $get->get('bid');
		$page = $get->get('page',1);
		$orderFlag = $get->get('orderFlag',10);
		$perpage = $get->get('perpage',20);
		$keyWords = $get->get('keywords','');

		$list = PkProduct::getList($cid,$bid,$page,$orderFlag,$perpage,$keyWords);

		$data = array();
		$data['list'] = [];
		foreach ($list['list'] as $key => $value) {
			$data['list'][$key]['product_id'] = $value['id'];
			$data['list'][$key]['price'] = $value['period_price'] ? : $value['price'];
			$data['list'][$key]['name'] = $value['name'];
			$data['list'][$key]['picture'] = $value['picture'];
			$data['list'][$key]['period_no'] = $value['period_no'];
			$data['list'][$key]['period_id'] = $value['period_id'];
			$leftTime = $value['end_time'] - time();
			$leftTime = $leftTime > 0 ? $leftTime : 0;
			$data['list'][$key]['left_time'] = $leftTime;
			$data['list'][$key]['time_type'] = $value['left_time'];
			$data['list'][$key]['buy_count'] = PkProduct::curPeriodBuyCount($value['period_id'], $value['table_id']);
		}
		$data['page'] = $page;
		$data['totalCount'] = $list['totalCount'];
		$data['totalPage'] = $list['totalPage'];
		return $data;
	}

	/**
	 * 商品信息
	 * @return jsonp [description]
	 */
	public function actionInfo(){
		$productId = Yii::$app->request->get('id');
		$productInfo = PkProduct::info($productId);
		if (!$productInfo) {
			return false;
		}

		$periodInfo = PkProduct::curPeriodInfo($productId);
		if (!$periodInfo) {
			return false;
		}
		$leftTime = $periodInfo['end_time'] - time();
		$leftTime = $leftTime > 0 ? $leftTime : 0;
		$periodInfo['left_time'] = $leftTime;
		unset($periodInfo['end_time']);

		$photoList = PkProduct::images($productId);
		foreach ($photoList as $key => $phone) {
			if ($productInfo['picture'] == $phone) {
				unset($photoList[$key]);
			}
		}
		$photoList = array_merge([$productInfo['picture']], $photoList);
		$periodInfo['buy_count'] = PkProduct::curPeriodBuyCount($periodInfo['id'], $periodInfo['table_id']);
		$productInfo['periodInfo'] = $periodInfo;
		$productInfo['photoList'] = $photoList;
		$productInfo['userInfo'] = [
			'logined'=>empty($this->userId) ? false : true,
		];

		return $productInfo;
	}

	public function actionIntro()
	{
		$productId = Yii::$app->request->get('id');
		$intro = PkProduct::intro($productId);
		return ['intro'=>$intro];
	}

	/** 往期揭晓
	 * @return mixed
	 */
	public function actionOldPeriodList()
	{
		$request = \Yii::$app->request;
		$pid = $request->get('id');
		$page = $request->get('page', 1);
		$perpage = $request->get('perpage', 10);

		$result = PkProduct::oldPeriodList($pid, $page, $perpage);
		return $result;
	}

	/*
	 * 获取单个商品最新一期
	 */
	public function actionPkOne(){
		$productId = Yii::$app->request->get('id');
		if($productId){
			$from=Brower::whereFrom();
			$where['activity_products.id']=$productId;
			$value = ActivityProducts::find()->select('activity_products.*,c.end_time end_time,c.id period_id,c.period_no period_no,c.table_id table_id,c.price period_price')->rightJoin('pk_current_periods c', 'c.product_id = activity_products.id')->where($where)->andWhere(['in','activity_products.display',[0,$from]])->asArray()->one();

			if(isset($value))
			{
				$data['product_id'] = $value['id'];
				$data['price'] = $value['period_price'] ? : $value['price'];
				$data['name'] = $value['name'];
				$data['picture'] = $value['picture'];
				$data['period_no'] = $value['period_no'];
				$data['period_id'] = $value['period_id'];
				$leftTime = $value['end_time'] - time();
				$leftTime = $leftTime > 0 ? $leftTime : 0;
				$data['left_time'] = $leftTime;
				$data['time_type'] = $value['left_time'];
				$data['buy_count'] = PkProduct::curPeriodBuyCount($value['period_id'], $value['table_id']);
				$data['code']=200;
			}else{
				$data['code']=201;
				$data['meg']='商品不存在';
			}
		}
			else{
				$data['code']=201;
				$data['meg']='商品不存在';
			}


		return $data;
	}



}