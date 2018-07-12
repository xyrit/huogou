<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午2:28
 */
namespace app\modules\api\controllers;

use app\models\PaymentOrderItemDistribution;
use app\models\ProductCategory;
use app\models\ShareTopic;
use app\services\User;
use app\services\Brand;
use app\services\Category;
use app\services\Period;
use app\models\Period as PeriodModel;
use yii;
use app\services\Product;
use app\helpers\DateFormat;
use app\models\FollowProduct;
use app\models\PeriodBuylistDistribution;

class ProductController extends BaseController
{

	//即将揭晓
	public function actionPublicGoods()
	{
		$request = Yii::$app->request;
		if($request->isGet){
			$page = $request->get('page', 1);
			$orderFlag = $request->get('orderFlag', 10);
			$perPage = $request->get('perpage', 24);
			if($orderFlag == 10){
				$data = Product::getList(0, 0, $page, $orderFlag, 'all',$perPage);
				$returnArr = [];
				foreach($data['list'] as $key => &$val){
					$returnArr[$key]['id'] = $val['id'];
					$returnArr[$key]['name'] = $val['name'];
					$returnArr[$key]['price'] = $val['period_price'];
					$returnArr[$key]['period_id'] = $val['period_id'];
					$returnArr[$key]['period_number'] = $val['period_number'];
					$returnArr[$key]['limit_num'] = $val['limit_num'];
					$returnArr[$key]['buy_unit'] = $val['buy_unit'];
					$returnArr[$key]['sales_num'] = $val['sales_num'];
					$returnArr[$key]['left_num'] = $val['left_num'];
					$returnArr[$key]['picture'] = $val['picture'];
				}
				return $returnArr;
			}
		}
	}


	/**
	 * 热门推荐
	 */
	public function actionRecommendProduct()
	{
		$request = Yii::$app->request;
		if($request->isGet){
			$get = $request->get();
			if($get['orderFlag'] == 60){
				$data = Product::getList(0, 0, $get['page'], $get['orderFlag'], 'all', $get['perpage']);
				$returnArr = [];
				foreach($data['list'] as $key => &$val){
					$returnArr[$key]['id'] = $val['id'];
					$returnArr[$key]['name'] = $val['name'];
					$returnArr[$key]['price'] = $val['period_price'];
					$returnArr[$key]['period_id'] = $val['period_id'];
					$returnArr[$key]['period_number'] = $val['period_number'];
					$returnArr[$key]['limit_num'] = $val['limit_num'];
					$returnArr[$key]['buy_unit'] = $val['buy_unit'];
					$returnArr[$key]['sales_num'] = $val['sales_num'];
					$returnArr[$key]['left_num'] = $val['left_num'];
					$returnArr[$key]['picture'] = $val['picture'];
				}
				return $returnArr;
			}
		}
	}


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
		$isLimit = $get->get('limit','all');
		$buyUnit = $get->get('buyUnit','all');
		$perpage = $get->get('perpage',20);
		$keyWords = $get->get('keywords','');

		$list = Product::getList($cid,$bid,$page,$orderFlag,$isLimit,$perpage,$keyWords,$buyUnit);

		$data = array();
		$data['list'] = [];
		foreach ($list['list'] as $key => $value) {
			$data['list'][$key]['product_id'] = $value['id'];
			$data['list'][$key]['price'] = $value['period_price'] ? : $value['price'];
			$data['list'][$key]['name'] = $value['name'];
			$data['list'][$key]['picture'] = $value['picture'];
			$data['list'][$key]['period_number'] = $value['period_number'];
			$data['list'][$key]['left_num'] = $value['left_num'];
			$data['list'][$key]['sales_num'] = $value['sales_num'];
			$data['list'][$key]['period_id'] = $value['period_id'];
			$data['list'][$key]['limit_num'] = $value['limit_num'];
			$data['list'][$key]['buy_unit'] = $value['buy_unit'];
		}

		if ($this->userId) {
			$follow = FollowProduct::find()->select('product_id')->where(['user_id'=>$this->userId])->asArray()->all();
			$followProductIds = yii\helpers\ArrayHelper::getColumn($follow, 'product_id');
			foreach ($data['list'] as $key=>$one) {
				$data['list'][$key]['followed'] = in_array($one['product_id'], $followProductIds) ? 1 : 0;
			}
		}

		$categoryInfo = Category::getCatByCid($cid);

		$data['page'] = $page;
		$data['totalCount'] = $list['totalCount'];
		$data['totalPage'] = $list['totalPage'];
		$data['cname'] = $categoryInfo['name'];
		return $data;
	}

	/**
	 * 商品信息
	 * @return jsonp [description]
	 */
	public function actionInfo(){
		$productId = Yii::$app->request->get('id');
		$productInfo = Product::info($productId);
		if (!$productInfo) {
			return false;
		}
		$periodInfo = Product::curPeriodInfo($productId);
		$lastPeriodNum = PeriodModel::find()->select("max(period_number) as last")->where(['product_id'=>$productId])->asArray()->one();
		$productInfo['last_period_num'] = $lastPeriodNum['last'] ?: 0;
		$photoList = Product::images($productId);
		foreach ($photoList as $key => $phone) {
			if ($productInfo['picture'] == $phone) {
				unset($photoList[$key]);
			}
		}
		$photoList = array_merge([$productInfo['picture']], $photoList);
		$catInfo = Category::getList($productInfo['cat_id']);
		$catNav = '';
		if (isset($catInfo['parent']) && count($catInfo['parent']) > 0) {
			foreach ($catInfo['parent'] as $key => $value) {
				$catNav .= '<i></i><a href="/list-'.$value['id'].'-0.html">'.$value['name'].'</a>';
			}
			$catNav .= '<i></i><a href="/list-'.$catInfo['id'].'-0.html">'.$catInfo['name'].'</a>';
		}else{
			$catNav .= '<i></i><a href="/list-'.$catInfo['id'].'-0.html">'.$catInfo['name'].'</a>';
		}
		$hasBuy = 0;
		if ($this->userId) {
			$hasBuy = Period::getUserHasBuyCount($this->userId,$periodInfo['id']);
			$followed = FollowProduct::find()->where(['user_id'=>$this->userId,'product_id'=>$productId])->asArray()->one();
		}

		if ($periodInfo) {
			//app 期数开始时间改为第一条购买时间;
			$tableId = $periodInfo['table_id'];
			$periodFirstBuy = PeriodBuylistDistribution::findByTableId($tableId)->where(['period_id'=>$periodInfo['id']])->orderBy('buy_time asc')->one();
			$periodInfo['start_time'] = $periodFirstBuy['buy_time'];
		}

		$productInfo['periodInfo'] = $periodInfo;
		$productInfo['photoList'] = $photoList;
		$productInfo['catNav'] = $catNav;
		$productInfo['userInfo'] = [
			'hasBuy'=>$hasBuy > 0 ? $hasBuy : 0,
			'followed'=>empty($followed) ? false : true ,
			'logined'=>empty($this->userId) ? false : true,
		];

		$productInfo['share_num'] = ShareTopic::find()->where(['product_id' => $productId, 'is_pass' => 1])->count();

		return $productInfo;
	}

	/**
	 * 期数列表
	 * @return [type] [description]
	 */
	public function actionPeriodlist(){
		$request = Yii::$app->request;
		$page = $request->get('page',0);
		$perpage = $request->get('perpage',8);
		$offset = $request->get('offset',0);

		$productId = $request->get('id');
		$type = $request->get('type','product');

        if ($type == 'period') {
           $period = \app\models\Period::find()->where(['id'=>$productId])->asArray()->one();
           $productId = $period['product_id'];
        }

		$perioadList = Product::perioadList($productId,$page,$perpage,$offset);

		return $perioadList;
	}

	/**
	 * 所有期数列表
	 * @return [type] [description]
	 */
	public function actionAllperiodlist(){
		$request = Yii::$app->request;
		$productId = $request->get('id');
		$page = $request->get('page', 0);
		$perpage = $request->get('perpage', 20);
		$showInfo = $request->get('showinfo', 0);
		if ($page * $perpage > 50) {
			$allPerioadList['list'] = [];
			$allPerioadList['page'] = 1;
			$allPerioadList['totalCount'] = 50;
			$allPerioadList['totalPage'] = 5;
		} else {
			$allPerioadList = Product::allPeriodList($productId, $page, $perpage, $showInfo);
		}
		if (isset($allPerioadList['totalCount']) && $allPerioadList['totalCount'] > 50) {
			$allPerioadList['totalCount'] = 50;
			$allPerioadList['totalPage'] = 5;
		}
		return $allPerioadList;
	}

	public function actionOldperiodlist() {
		$request = Yii::$app->request;
		$productId = $request->get('id');
		$page = $request->get('page',1);
		$perpage = $request->get('perpage',20);
		$showInfo = $request->get('showinfo',0);
		if ($page * $perpage > 50) {
			$oldPeriodList['list'] = [];
			$oldPeriodList['page'] = 1;
			$oldPeriodList['totalCount'] = 50;
			$oldPeriodList['totalPage'] = 5;
		} else {
			$oldPeriodList = Product::oldPeriodlist($productId,$page,$perpage,$showInfo);
		}
		if (isset($oldPeriodList['totalCount']) && $oldPeriodList['totalCount'] > 50) {
			$oldPeriodList['totalCount'] = 50;
			$oldPeriodList['totalPage'] = 5;
		}
		return $oldPeriodList;
	}

	/**
	 * 分类列表
	 * @return [type] [description]
	 */
	public function actionCatlist(){
		$catId = Yii::$app->request->get('catid','');
		$list = Category::getList(0);
		$currCatInfo = array();
		if ($catId) {
			$currCat = Category::getList($catId);
			$child = ProductCategory::findOne($catId);
			if (isset($currCat['parent']) && count($currCat['parent']) > 0) {
				$catName = '';
				foreach ($currCat['parent'] as $key => $value) {
					if ($value['level'] == '1') {
						$catName .= '<a href="/list-'.$value['id'].'-0.html">'.$value['name'].'</a>';
						//$currCatInfo['name'] = $value['name'];
						$currCatInfo['cid'] = $value['id'];
					}else{
						$catName .= ' <i></i> '. '<a href="/list-'.$value['id'].'-0.html">'.$value['name'].'</a>';
					}
				}
				$currCatInfo['name'] = $catName.' <i></i> '.$child['name'];
			}else{
				unset($currCat['son']);
				$currCatInfo['name'] = $currCat['name'];
				$currCatInfo['cid'] = $currCat['id'];
			}
		}
		
		
		return array('list'=>$list,'currCat'=>$currCatInfo);
	}

	/**
	 * 品牌列表
	 * @return [type] [description]
	 */
	public function actionBrandlist(){
		$request = Yii::$app->request;
		$catId = $request->get('cid', 0);
		$list = Brand::getList($catId);
		return array('list'=>$list);
	}

	/**
	 * 网站最新购买记录
	 */
	public function actionNewBuyList()
	{
		$request = Yii::$app->request;
		$limit = $request->get('num', 4);
		$time = $request->get('lasttime',microtime(true));
		$list = PaymentOrderItemDistribution::lastBuy($time, 200);
		$userIds = [];
		$count = 0;
		$arr = [];
		foreach($list as $key => $val){
			if ($count>=$limit) {
				break;
			}
			if (!in_array($val['user_id'], $userIds)) {

				$userInfo = User::baseInfo($val['user_id']);
				$productInfo = Product::info($val['product_id']);
				$arr[$key]['username'] = $userInfo['username'];
				$arr[$key]['avatar'] = $userInfo['avatar'];
				$arr[$key]['home_id'] = $userInfo['home_id'];
				$arr[$key]['product_id'] = $val['product_id'];
				$arr[$key]['name'] = $productInfo['name'];
				$arr[$key]['buy_time'] = $val['item_buy_time'];
				$arr[$key]['buy_num'] = $val['nums'];
				$arr[$key]['created_at'] = DateFormat::formatTime($val['item_buy_time']);


				$userIds[] = $val['user_id'];
				$count++;
			}
		}

		return $arr;
	}

	public function actionImages()
	{
		$request = Yii::$app->request;
		$id = $request->get('id');
		return Product::images($id);
	}

	public function actionIntro()
	{
		$productId = Yii::$app->request->get('id');
		$intro = \app\models\Product::find()->select('intro')->where(['id'=>$productId])->one();
		return ['intro'=>$intro['intro']];
	}

}