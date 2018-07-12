<?php
/**
 * Created by PhpStorm.
 * User: chenyi
 * Date: 2015/10/15
 * Time: 16:56
 * 我的伙购
 */
namespace app\modules\api\controllers;

use app\helpers\DateFormat;
use app\helpers\Express;
use app\helpers\Message;
use app\helpers\TimeHelper;
use app\models\Active;
use app\models\ActOrder;
use app\models\ActRichLog;
use app\models\Area;
use app\models\CurrentPeriod;
use app\models\DuibaOrderDistribution;
use app\models\FreePeriod;
use app\models\Order;
use app\models\Period;
use app\models\ShareTopic;
use app\models\Sign;
use app\models\SignConf;
use app\models\UserAddress;
use app\models\UserAppInfo;
use app\models\UserSign;
use app\models\UserTask;
use app\models\UserVirtualAddress;
use app\models\UserVirtualHand;
use app\models\VirtualDepotJd;
use app\models\VirtualProductInfo;
use app\modules\admin\models\Deliver;
use app\modules\admin\models\ExchangeOrder;
use \app\modules\api\controllers\BaseController;
use app\services\Member;
use app\services\Member_m;
use app\services\Product;
use app\services\Share;
use app\services\User;
use app\models\VirtualDepot;
use app\models\UserVirtual;
use Yii;
use app\services\Purchase;
use app\models\User as UserModel;
use app\helpers\MyRedis;
use app\services\Payway;
use app\services\Thirdpay;
use yii\data\Pagination;
use yii\helpers\Json;
use app\models\CardSendLog;

class RecordController extends BaseController
{
	/**
	 * 我的伙购记录
	 */
	public function actionBuyList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 5);
		$status = Yii::$app->request->get('status', -1);
		$region = Yii::$app->request->get('region', '');
		$startTime = Yii::$app->request->get('start_time', '');
		$endTime = Yii::$app->request->get('end_time', '');
		
		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime . " 23:59:59");
		}
		
		if (!($startTime && $endTime) && $region) {
			list($startTime, $endTime) = DateFormat::formatConditionTime($region);
		}
		
		$member = new Member(['id' => $this->userId]);
		$buyList = $member->getBuyList($startTime, $endTime, $page, $perpage, $status);
		
		return $buyList;
	}
	
	/**
	 * 伙购提醒
	 */
	public function actionBuyTips()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = 1;
		$perpage = PHP_INT_MAX;
		$member = new Member(['id' => $this->userId]);
		$buyListTips = $member->getBuyList('', '', $page, $perpage);
		
		$tips = array(0, 0, 0);
		foreach ($buyListTips['list'] as $list) {
			$tips[$list['status']]++;
		}
		
		$buyTips['tips'] = $tips;
		return $buyTips;
	}
	
	/**
	 * 伙购详情
	 */
	public function actionBuyDetail()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$periodId = Yii::$app->request->get('id');
		$member = new Member(['id' => $this->userId]);
		
		// 购买信息
		$buyDetail = $member->getBuyDetail($periodId);
		$buyNumber = 0;
		foreach ($buyDetail as &$detail) {
			$detail['buy_time'] = DateFormat::microDate($detail['buy_time']);
			$detail['codes'] = explode(',', $detail['codes']);
			$buyNumber += $detail['buy_num'];
		}
		
		$periodInfo = \app\services\Period::info($periodId);
		if (empty($periodInfo)) {
			$periodInfo = CurrentPeriod::findOne($periodId)->toArray();
			$productInfo = Product::info($periodInfo['product_id']);
			$periodInfo['goods_picture'] = $productInfo['picture'];
			$periodInfo['goods_id'] = $productInfo['id'];
			$periodInfo['goods_name'] = $productInfo['name'];
			$periodInfo['period_id'] = $periodInfo['id'];
			$periodInfo['status'] = 0;
		}
		$periodInfo['user_buy_num'] = $buyNumber;
		$orderInfo = Order::findOne(['product_id' => $periodInfo['goods_id'], 'period_id' => $periodInfo['period_id'], 'user_id' => $this->userId]);
		
		$data['orderInfo'] = [];
		if (!empty($orderInfo) && $orderInfo['status'] == 0) {
			$data['orderInfo'] = $orderInfo;
		}
		
		$currentPeriodInfo = Product::curPeriodInfo($periodInfo['goods_id']);
		
		if ($currentPeriodInfo) {
			$periodInfo['cur_period_id'] = $currentPeriodInfo['id'];
			$periodInfo['cur_period_num'] = $currentPeriodInfo['period_number'];
		}
		
		$data['periodInfo'] = $periodInfo;
		$data['buyDetail'] = $buyDetail;
		
		return $data;
	}
	
	/**
	 * 获得伙购码
	 */
	public function actionGetBuyCode()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$periodId = Yii::$app->request->get('period_id');
		
		$member = new Member(['id' => $this->userId]);
		$buyDetail = $member->getBuyDetail($periodId);
		$buyCode = array();
		foreach ($buyDetail as $detail) {
			$buyCode = array_merge($buyCode, explode(',', $detail['codes']));
		}
		
		$buyCode = array_slice($buyCode, 0, 5);
		
		return ['buy_code' => $buyCode];
	}
	
	/**
	 * 换货商品
	 **/
	public function actionExchangeOrderList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 5);
		$status = Yii::$app->request->get('status', '');
		$region = Yii::$app->request->get('region', '');
		$startTime = Yii::$app->request->get('start_time', '');
		$endTime = Yii::$app->request->get('end_time', '');
		
		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime . " 23:59:59");
		}
		
		if (!($startTime && $endTime) && $region) {
			list($startTime, $endTime) = DateFormat::formatConditionTime($region);
		}
		
		if ($status == 0) { //全部
			$status = -1;
		} elseif ($status == 1) { //待确认地址
			$status = [Order::STATUS_INIT, Order::STATUS_REJECT];
		} elseif ($status == 2) { //待发货
			$status = [Order::STATUS_COMMIT_ADDRESS, Order::STATUS_COMFIRM_ADDRESS, Order::STATUS_PREPARE_GOODS];
		} elseif ($status == 3) { //待收货
			$status = Order::STATUS_SHIPPING;
		} elseif ($status == 4) { //待晒单
			$status = Order::STATUS_COMFIRM_RECEIVE;
		}
		
		$member = new Member(['id' => $this->userId]);
		$productList = $member->getExchangeOrderList($startTime, $endTime, $page, $perpage, $status);
		
		foreach ($productList['list'] as $key => &$product) {
			if ($product['status'] == Order::STATUS_INIT || $product['status'] == Order::STATUS_REJECT) {
				$product['status_name'] = '确认地址';
			} else if ($product['status'] >= Order::STATUS_COMMIT_ADDRESS && $product['status'] <= Order::STATUS_PREPARE_GOODS) {
				$product['status_name'] = '待发货';
			} elseif ($product['status'] == Order::STATUS_SHIPPING) {
				$product['status_name'] = '确认收货';
			} elseif ($product['status'] == Order::STATUS_COMFIRM_RECEIVE) {
				if ($product['is_exchange']) {
					$product['status_name'] = '已完成';
				} else {
					if ($product['allow_share'] == 1) {
						$product['status_name'] = '待晒单';
					} elseif ($status == Order::STATUS_COMFIRM_RECEIVE) {
						unset($productList['list'][$key]);
					} else {
						$product['status_name'] = '已完成';
					}
				}
			} else {
				$product['status_name'] = '已完成';
			}
		}
		
		return $productList;
	}
	
	/**
	 * 获得的商品
	 * @return mixed
	 */
	public function actionOrderList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 5);
		$status = Yii::$app->request->get('status', 0);
		$region = Yii::$app->request->get('region', '');
		$startTime = Yii::$app->request->get('start_time', '');
		$endTime = Yii::$app->request->get('end_time', '');
		
		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime . " 23:59:59");
		}
		
		if (!($startTime && $endTime) && $region) {
			list($startTime, $endTime) = DateFormat::formatConditionTime($region);
		}
		
		if ($status == 0) { //全部
			$status = -1;
		} elseif ($status == 1) { //待确认地址
			$status = [Order::STATUS_INIT, Order::STATUS_REJECT];
		} elseif ($status == 2) { //待发货
			$status = [Order::STATUS_COMMIT_ADDRESS, Order::STATUS_COMFIRM_ADDRESS, Order::STATUS_PREPARE_GOODS];
		} elseif ($status == 3) { //待收货
			$status = Order::STATUS_SHIPPING;
		} elseif ($status == 4) { //待晒单
			$status = Order::STATUS_COMFIRM_RECEIVE;
		} elseif($status == -1){//未填写收货信息
			$status = [Order::STATUS_INIT];
		}
		$member = new Member(['id' => $this->userId]);
		$productList = $member->getProductList($startTime, $endTime, $page, $perpage, $status);
		
		$redis = new MyRedis();
		foreach ($productList['list'] as $key => &$product) {
			$product['status_type'] = 0;
			if ($product['status'] == Order::STATUS_INIT || $product['status'] == Order::STATUS_REJECT) {
				if ($product['delivery_id'] == '3') {
					if ($product['status'] == Order::STATUS_INIT) {
						$statusName = '选择运营商';
					} else {
						$statusName = '收货信息错误，请重新确认';
					}
					$product['status_name'] = $statusName;
				} elseif (in_array($product['delivery_id'], [5, 6, 7, 9, 10])) {
					if ($product['status'] == Order::STATUS_INIT) {
						$statusName = '等待填写信息';
					} else {
						$statusName = '收货信息错误，请重新确认';
					}
					if (!in_array($product['delivery_id'], [9, 10])) {
						$product['tips_img'] = Yii::$app->params['skinUrl'] . '/img/tips.jpg';
						$product['tips_content'] = '虚拟商品目前是审核状态，发放时间是周一到周日上午9点到晚上9点，请耐心等待。';
						if ($product['face_value'] >= 200 || ($product['face_value'] > 0 && $product['delivery_id'] == 7)) {
							$product['status_type'] = 1;
							$product['status_name'] = $statusName;
						} else {
							$product['status_type'] = 2;
							$product['status_name'] = $statusName;
						}
					} else {
						$product['status_type'] = 1;
						$product['status_name'] = $statusName;
					}
					
				} elseif ($product['delivery_id'] == 8) { // 京东充值卡卡密
					if ($product['status'] == Order::STATUS_INIT) {
						$statusName = '等待领取';
					} else {
						$statusName = '收货信息错误，请重新确认';
					}
					$product['status_name'] = $statusName;
				} else {
					if ($product['status'] == Order::STATUS_INIT) {
						$statusName = '等待确认收货地址';
					} else {
						$statusName = '收货信息错误，请重新确认';
					}
					$product['status_name'] = $statusName;
				}
			} else if ($product['status'] >= Order::STATUS_COMMIT_ADDRESS && $product['status'] <= Order::STATUS_PREPARE_GOODS) {
				if (in_array($product['delivery_id'], [5, 6, 7, 9, 10])) {
					$product['status_name'] = '等待商品派发';
					$product['tips_img'] = Yii::$app->params['skinUrl'] . '/img/tips.jpg';
					$product['tips_content'] = '虚拟商品目前是审核状态，发放时间是周一到周日上午9点到晚上9点，请耐心等待。';
				} else {
					$product['status_name'] = '等待商品派发';
				}
			} elseif ($product['status'] == Order::STATUS_SHIPPING) {
				if ($product['delivery_id'] == '3') {
					$product['status_name'] = '查看卡密';
				} else {
					$product['status_name'] = '商品已派发';
				}
			} elseif ($product['status'] == Order::STATUS_COMFIRM_RECEIVE) {
				if ($product['is_exchange']) {
					$product['status_name'] = '订单完成';
				} else {
					if ($product['delivery_id'] == '3') {
						$product['status_name'] = '查看卡密';
					} else {
						if ($product['allow_share'] == 1) {
							$product['status_name'] = '待晒单';
						} elseif ($status == Order::STATUS_COMFIRM_RECEIVE) {
							unset($productList['list'][$key]);
						} else {
							$product['status_name'] = '订单完成';
						}
					}
				}
			} else {
				if ($product['delivery_id'] == '3') {
					$used = UserVirtual::find()->where(['orderid' => $product['order_id'], 'uid' => $this->userId])->asArray()->one();
					if ($used['type'] == 'hgb') {
						$product['status_name'] = '已兑换伙购币';
					} else {
						$product['status_name'] = '卡密已派发';
					}
				} elseif (in_array($product['delivery_id'], [5, 6, 7, 9, 10])) {
					$product['status_name'] = '订单完成';
				} elseif ($product['delivery_id'] == 8) {
					$product['status_name'] = '已领取';
				} else {
					$product['status_name'] = '订单完成';
				}
			}
		}
		if ($page == 1) {
			UserAppInfo::updateAll(['new_order_tip' => 0], ['uid' => $this->userId]);
			Order::updateAll(['push_msg' => 1], ['user_id' => $this->userId, 'push_msg' => 0]);
		}
		return $productList;
	}
	
	/**
	 *  兑吧兑换记录
	 */
	public function actionDuiHistory()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 5);
		
		$query = DuibaOrderDistribution::findByTableId($this->userInfo['home_id'])->where(['user_id' => $this->userId]);
		
		$countQuery = clone $query;
		$totalCount = $countQuery->count();
		
		$pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
		$duibaOrders = $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
		
		$list = [];
		foreach ($duibaOrders as $order) {
			$info = [];
			$info['id'] = $order['id'];
			$info['status'] = $order['status'];
			switch ($order['status']) {
				case 0:
					$statusName = '审核中';
					break;
				case 1:
					$statusName = '成功';
					break;
				case 2:
					$statusName = '失败';
					break;
				default:
					$statusName = '未知';
					break;
			}
			$info['type'] = $order['type'];
			switch ($order['type']) {
				case 'alipay':
					$typeName = '支付宝';
					break;
				case 'qb':
					$typeName = 'Q币';
					break;
				case 'phonebill':
					$typeName = '话费';
					break;
				case 'coupon':
					$typeName = '优惠券';
					break;
				default:
					$typeName = '未知';
					break;
			}
			$info['type_name'] = $typeName;
			$info['status_name'] = $statusName;
			$info['face_price'] = ($order['credits'] / 100) . '元';
			$info['account'] = $order['params'];
			$info['time'] = date('Y-m-d H:i:s', $order['created_at']);
			$list[] = $info;
		}
		
		$return['list'] = $list;
		$return['totalCount'] = $totalCount;
		$return['totalPage'] = $pagination->getPageCount();
		
		return $return;
	}
	
	
	/** 活动奖品订单
	 * @return array|mixed
	 */
	public function actionActOrderList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 5);
		$status = Yii::$app->request->get('status', '');
		$region = Yii::$app->request->get('region', '');
		$startTime = Yii::$app->request->get('start_time', '');
		$endTime = Yii::$app->request->get('end_time', '');
		
		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime . " 23:59:59");
		}
		
		if (!($startTime && $endTime) && $region) {
			list($startTime, $endTime) = DateFormat::formatConditionTime($region);
		}
		
		if ($status == 0) { //全部
			$status = -1;
		} elseif ($status == 1) { //待确认地址
			$status = [ActOrder::STATUS_INIT, ActOrder::STATUS_REJECT];
		} elseif ($status == 2) { //待发货
			$status = [ActOrder::STATUS_COMMIT_ADDRESS, ActOrder::STATUS_COMFIRM_ADDRESS, ActOrder::STATUS_PREPARE_GOODS];
		} elseif ($status == 3) { //待收货
			$status = ActOrder::STATUS_SHIPPING;
		} elseif ($status == 4) { //待晒单
			$status = ActOrder::STATUS_COMFIRM_RECEIVE;
		}
		
		$member = new Member(['id' => $this->userId]);
		$productList = $member->getActOrderList($startTime, $endTime, $page, $perpage, $status);
		
		foreach ($productList['list'] as $key => &$product) {
			if ($product['status'] == ActOrder::STATUS_INIT) {
				$product['status_name'] = '完善收货地址';
			} else if ($product['status'] >= ActOrder::STATUS_COMMIT_ADDRESS && $product['status'] <= ActOrder::STATUS_PREPARE_GOODS) {
				$product['status_name'] = '待发货';
			} elseif ($product['status'] == ActOrder::STATUS_SHIPPING) {
				$product['status_name'] = '确认收货';
			} elseif ($product['status'] == ActOrder::STATUS_DONE) {
				$product['status_name'] = '已完成';
			} elseif ($product['status'] == ActOrder::STATUS_OVERDUE) {
				$product['status_name'] = '已过期';
			}
			//如果是月土豪榜奖品 加入 排行字段
			if (in_array($product['goods_type'], [3, 4, 5])) {
				$richLog = ActRichLog::find()->select('rank')->where(['id' => $product['act_obj_id']])->one();
				$product['rich_rank'] = $richLog['rank'];
			} elseif ($product['goods_type'] == ActOrder::TYPE_FREE) {
				$period = FreePeriod::find()->select('sales_num')->where(['id' => $product['act_obj_id']])->one();
				$product['sales_num'] = $period['sales_num'];
			}
		}
		
		if ($page == 1) {
			UserAppInfo::updateAll(['new_act_order_tip' => 0], ['uid' => $this->userId]);
		}
		
		return $productList;
	}
	
	/**
	 * 我的晒单
	 * @return mixed
	 */
	public function actionTopicList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 10);
		$shareTopicList = Share::getListByType($page, 0, 10, $perpage, $this->userId, 0);
		
		return $shareTopicList;
	}
	
	/**
	 * 我的未晒单
	 * @return mixed
	 */
	public function actionNotTopicList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 10);
		
		$status = 5;
		$member = new Member(['id' => $this->userId]);
		$productList = $member->getProductList('', '', $page, $perpage, $status);
		
		return $productList;
	}
	
	/**
	 * 我的关注
	 * @return mixed
	 */
	public function actionCollectList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 12);
		$member = new Member(['id' => $this->userId]);
		$followProductList = $member->getFollowProductList($page, $perpage);
		
		return $followProductList;
	}
	
	public function actionAreaList()
	{
		$pid = Yii::$app->request->get('pid');
		$areaList = Area::findAll(['pid' => $pid]);
		return $areaList;
	}
	
	public function actionEditAreaList()
	{
		$provId = Yii::$app->request->get('provId');
		$cityId = Yii::$app->request->get('cityId');
		
		$data['provList'] = Area::findAll(['pid' => 0]);
		$data['cityList'] = Area::findAll(['pid' => $provId]);
		$data['areaList'] = Area::findAll(['pid' => $cityId]);
		
		return $data;
	}
	
	/**
	 * 确认收货地址
	 */
	public function actionSubmitAddress()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$userAddressId = Yii::$app->request->get('useraddressid');
		$ship_time = Yii::$app->request->get('ship_time');
		$mark_text = Yii::$app->request->get('mark_text');
		$orderId = Yii::$app->request->get('orderId');
		
		$order = Order::find()->where(['id' => $orderId, 'user_id' => $this->userId, 'status' => [0, 6]])->one();
		if (!$order) {
			return ['code' => 202, 'msg' => '订单不存在'];
		}
		$productInfo = Product::info($order['product_id']);
		if (in_array($productInfo['delivery_id'], [2, 3, 5, 6, 7, 8, 9, 10])) {
			return ['code' => 203, 'msg' => '请安装最新APP或在PC端,进行兑换'];
		}
		$userAddress = UserAddress::findOne(['id' => $userAddressId])->toArray();
		$area = Area::find()->where(['id' => [$userAddress['prov'], $userAddress['city'], $userAddress['area']]])->indexBy('id')->asArray()->all();
		$params = array();
		$params['ship_area'] = $area[$userAddress['prov']]['name'] . ' ' . $area[$userAddress['city']]['name'] . ' ' . $area[$userAddress['area']]['name'];
		$params['ship_name'] = $userAddress['name'];
		$params['ship_addr'] = $userAddress['address'];
		isset($userAddress['code']) && $params['ship_zip'] = $userAddress['code'];
		//isset($userAddress['telephone']) && $params['ship_tel'] = $userAddress['telephone'];
		!empty($ship_time) && $params['ship_time'] = $ship_time;
		$params['ship_mobile'] = $userAddress['mobilephone'];
		!empty($mark_text) && $params['mark_text'] = $mark_text;
		$params['last_modified'] = time();
		$params['status'] = 1;
		$params['confirm_addr_time'] = time();
		$params['fail_type'] = 0;
		$params['fail_id'] = '';
		
		if (Order::updateAll($params, ['id' => $orderId])) {
			$userInfo = User::baseInfo($this->userId);
			$orderInfo = Order::findOne($orderId);
			$productInfo = \app\models\Product::findOne($orderInfo['product_id']);
			$data['nickname'] = $userInfo['username'];
			$data['goodsName'] = $productInfo['name'];
			$data['orderNo'] = $orderId;
			$data['address'] = $params['ship_area'] . ' ' . $params['ship_addr'];
			$data['time'] = date("Y-m-d H:i:s");
			Message::send(13, $this->userId, $data);
			return ['code' => 100, 'msg' => '地址信息已提交'];
		}
		
		return ['code' => 101, 'msg' => '确认失败'];
	}
	
	/** 用户确认活动订单地址
	 * @return array
	 */
	public function actionSubmitActAddress()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$userAddressId = Yii::$app->request->get('useraddressid');
		$ship_time = Yii::$app->request->get('ship_time');
		$mark_text = Yii::$app->request->get('mark_text');
		$orderId = Yii::$app->request->get('orderId');
		
		$orderInfo = ActOrder::find()->where(['id' => $orderId, 'user_id' => $this->userId, 'status' => ActOrder::STATUS_INIT])->asArray()->one();
		if (!$orderInfo) {
			return ['code' => 202, 'msg' => '订单不存在'];
		}
		if ($orderInfo['create_time'] < time() - 3600 * 24 * 7) {
			return ['code' => 203, 'msg' => '奖品已过期'];
		}
		$userAddress = UserAddress::findOne(['id' => $userAddressId])->toArray();
		$area = Area::find()->where(['id' => [$userAddress['prov'], $userAddress['city'], $userAddress['area']]])->indexBy('id')->asArray()->all();
		$params = array();
		$params['ship_area'] = $area[$userAddress['prov']]['name'] . ' ' . $area[$userAddress['city']]['name'] . ' ' . $area[$userAddress['area']]['name'];
		$params['ship_name'] = $userAddress['name'];
		$params['ship_addr'] = $userAddress['address'];
		isset($userAddress['code']) && $params['ship_zip'] = $userAddress['code'];
		!empty($ship_time) && $params['ship_time'] = $ship_time;
		$params['ship_mobile'] = $userAddress['mobilephone'];
		!empty($mark_text) && $params['mark_text'] = $mark_text;
		$params['last_modified'] = time();
		$params['status'] = 1;
		$params['confirm_addr_time'] = time();
		
		if (ActOrder::updateAll($params, ['id' => $orderId])) {
			$userInfo = User::baseInfo($this->userId);
			$data['nickname'] = $userInfo['username'];
			$actTypeName = ActOrder::$type_name[$orderInfo['act_type']];
			$data['goodsName'] = "【" . $actTypeName . "】" . $orderInfo['name'];
			$data['orderNo'] = $orderId;
			$data['address'] = $params['ship_area'] . ' ' . $params['ship_addr'];
			$data['time'] = date("Y-m-d H:i:s");
			Message::send(13, $this->userId, $data);
			return ['code' => 100, 'msg' => '地址信息已提交'];
		}
		
		return ['code' => 101, 'msg' => '确认失败'];
	}
	
	/**
	 * 确认收货
	 */
	public function actionSubmitGoods()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$orderId = Yii::$app->request->get('id');
		$orderInfo = Order::findOne(['id' => $orderId, 'user_id' => $this->userId, 'status' => Order::STATUS_SHIPPING]);
		if (empty($orderInfo)) {
			return ['code' => 101, 'msg' => '无效订单'];
		}
		
		if ($orderInfo['status'] >= 5) {
			return ['code' => 101, 'msg' => '已确认收货'];
		}
		
		$params = array();
		$productInfo = \app\models\Product::findOne($orderInfo['product_id']);
//        if ($productInfo['allow_share'] == 0 || $orderInfo['is_exchange'] == 1) {
//            $params['status'] = 8;
//        } else {
//            $params['status'] = 5;
//        }
		$buyBack = \app\models\JdcardBuybackList::findOne(['order_id' => $orderId]);
		$params['status'] = isset($buyBack) ? 8 : 5;
		
		$params['last_modified'] = time();
		
		if ($orderInfo['is_exchange']) {
			$exchangeModel = ExchangeOrder::findOne(['order_no' => $orderInfo['id']]);
			$exchangeModel->confirm_goods_time = time();
			$exchangeModel->save();
		} else {
			$params['confirm_goods_time'] = time();
		}
		
		if (Order::updateAll($params, ['id' => $orderId])) {
			$userInfo = User::baseInfo($this->userId);
			$data['nickname'] = $userInfo['username'];
			$data['goodsName'] = $productInfo['name'];
			$data['orderNo'] = $orderId;
			$data['time'] = date("Y-m-d H:i:s");
			Message::send(18, $this->userId, $data);
			return ['code' => 100, 'msg' => '确认成功'];
		}
		
		return ['code' => 101, 'msg' => '确认失败'];
	}
	
	/** 确认活动奖品收货
	 * @return array
	 */
	public function actionSubmitActGoods()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$orderId = Yii::$app->request->get('id');
		$orderInfo = ActOrder::findOne(['id' => $orderId, 'user_id' => $this->userId, 'status' => ActOrder::STATUS_SHIPPING]);
		if (empty($orderInfo)) {
			return ['code' => 101, 'msg' => '无效订单'];
		}
		
		if ($orderInfo['status'] >= 5) {
			return ['code' => 101, 'msg' => '已确认收货'];
		}
		
		$params = [];
		$params['status'] = 8;
		$params['last_modified'] = time();
		$params['confirm_goods_time'] = time();
		
		if (ActOrder::updateAll($params, ['id' => $orderId])) {
			
			if ($orderInfo['act_type'] == ActOrder::TYPE_MONTH_RICH) {
				ActRichLog::updateAll(['status' => 1], ['id' => $orderInfo['act_obj_id']]);
			}
			
			$userInfo = User::baseInfo($this->userId);
			$data['nickname'] = $userInfo['username'];
			$actTypeName = ActOrder::$type_name[$orderInfo['act_type']];
			$data['goodsName'] = "【" . $actTypeName . "】" . $orderInfo['name'];
			$data['orderNo'] = $orderId;
			$data['time'] = date("Y-m-d H:i:s");
			Message::send(18, $this->userId, $data);
			return ['code' => 100, 'msg' => '确认成功'];
		}
		
		return ['code' => 101, 'msg' => '确认失败'];
	}
	
	public function actionPointsList()
	{
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 10);
		$region = Yii::$app->request->get('region', '');
		$startTime = Yii::$app->request->get('start_time', '');
		$endTime = Yii::$app->request->get('end_time', '');
		
		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime . " 23:59:59");
		}
		
		if ($region == 0) {
			$region = 2;
		}
		
		if (!($startTime && $endTime) && $region) {
			if ($region == 2) {
				$startTime = strtotime('-1 month');
				$endTime = time();
			} elseif ($region == 1) {
				$startTime = strtotime('-3 month');
				$endTime = time();
			}
		}
		
		$member = new Member(['id' => $this->userId]);
		$pointsList = $member->getPointFollowList($startTime, $endTime, $page, $perpage);
		foreach ($pointsList['list'] as &$points) {
			$points['created_at'] = DateFormat::microDate($points['created_at']);
		}
		// 获取用户可用福分
		$user = User::baseInfo($this->userId);
		$pointsList['totalPoints'] = $user['point'];
		return $pointsList;
	}
	
	/**
	 * 本周最火达人
	 */
	public function actionHotOrderList()
	{
		$member = new Member(['id' => $this->userId]);
		$list = $member->hotOrderList();
		return $list;
	}
	
	/**
	 * 获取物流信息
	 */
	public function actionGetDeliveryInfo()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$orderId = Yii::$app->request->get('orderId');
		
		$orderInfo = Order::findOne($orderId);
		if ($orderInfo['status'] < 4) {
			return ['code' => 101, 'msg' => '暂无信息'];
		}
		
		if ($orderInfo['is_exchange'] == 1) { //换货商品
			$deliverInfo = ExchangeOrder::findOne(['order_no' => $orderInfo['order_no']]);
		} else {
			$deliverInfo = Deliver::findOne($orderId);
		}
		
		if (isset($deliverInfo) && !empty($deliverInfo['deliver_company']) && !empty($deliverInfo['deliver_order'])) {
			$expressInfo = Express::getOrder($deliverInfo['deliver_company'], $deliverInfo['deliver_order']);
			if (isset($expressInfo['data'])) {
				return ['code' => 100, 'msg' => $expressInfo['data']];
			}
		}
		
		return ['code' => 101, 'msg' => '暂无信息'];
	}
	
	public function actionGetVirtual()
	{
		$id = Yii::$app->request->get('id');
		$type = Yii::$app->request->get('type');
		
		$redis = new MyRedis();
		$getCardKey = UserVirtual::GET_CARD_KEY . $id;
		$handle = $redis->sset($getCardKey, '1');
		$redis->expire($getCardKey, 60);
		if (!$handle) {
			return array('code' => 0);
		}

		if (!in_array($type, array('yd', 'lt', 'dx', 'hgb'))) {
			$redis->del($getCardKey);
			return array('code' => 0);
		}
		$orderInfo = Order::find()->where(['id' => $id])->asArray()->one();
		if ($orderInfo['status'] != 0) {
			$redis->del($getCardKey);
			return array('code' => 0);
		}
		$productInfo = Product::info($orderInfo['product_id']);
		
		if ($productInfo['delivery_id'] != 3) {
			$redis->del($getCardKey);
			return array('code' => 0);
		}

		$getResult = 0;
		
		if ($type == 'hgb') {
			$source = Yii::$app->request->get('source', '1');
			$payWay = new Payway();
			$result = $payWay->chooseway($orderInfo['user_id'], 'recharge', 'exchage', '充值卡兑换', '0', $productInfo['cost'], $source);
			
			if ($result['code'] != '100') {
				$redis->del($getCardKey);
				return array('code' => 0);
			}
			$payData = array(
				'money' => $productInfo['cost']
			);
			$pay = new Thirdpay();
			$pay->pay($result['order'], 'exchage', $payData);
			
			$userVirtual = new UserVirtual();
			$userVirtual->uid = $orderInfo['user_id'];
			$userVirtual->type = $type;
			$userVirtual->orderid = $id;
			$userVirtual->par_value = $productInfo['cost'];
			$userVirtual->card = $userVirtual->pwd = "兑换" . $productInfo['cost'] . '个伙购币';
			$userVirtual->status = 1;
			$userVirtual->create_time = $userVirtual->update_time = time();
			$userVirtual->save();
			
			Order::updateAll(array('status' => 8), array('id' => $id));
			
			$thirdOrder = $result['order'];
			$price = $productInfo['cost'];
			$platForm = '平台';
			
			$getResult = 1;
			
		} else {
			$nums = 1;
			$parValue = 0;
			if ($productInfo['cost'] > 100) {
				$nums = intval($productInfo['cost'] / 100);
				$parValue = 100;
			} else {
				// $nums = 1;
				$parValue = $productInfo['cost'];
			}
			// $parValue = 10;
			$cards = VirtualDepot::find()->where(['par_value' => $parValue, 'status' => 0, 'type' => $type])->limit($nums)->asArray()->all();
			$buyResult['msg']['exchange_id'] = '';
			if (!$cards) {
				$purchase = new Purchase();
				$buyResult = $purchase->autoBuy($type, $productInfo['cost']);
			}

			if ($cards || $buyResult['msg']['exchange_id']) {
				// $cards = VirtualDepot::find()->where(['par_value'=>$parValue,'status'=>0,'type'=>$type])->limit($nums)->asArray()->all();
				if (!$cards) {
					$cards = $buyResult['msg']['cards'];
				}
				if (!$cards || count($cards) < $nums) {
					$redis->del($getCardKey);
					return array('code' => 0);
				}

				$db = \Yii::$app->db;
				$userVirtualField = ['uid', 'type', 'orderid', 'par_value', 'card', 'pwd', 'create_time'];
				$userVirtualValue = [];
				foreach ($cards as $key => $value) {
					VirtualDepot::updateAll(array('status' => 1), array('card' => $value['card']));
					$userVirtualValue[] = [$orderInfo['user_id'], $type, $id, $parValue, $value['card'], $value['pwd'], time()];
				}
				$rs = $db->createCommand()->batchInsert('user_virtual', $userVirtualField, $userVirtualValue)->execute();
				
				if ($rs) {
					Order::updateAll(array('status' => 4), array('id' => $id));
					
					$thirdOrder = $buyResult['msg']['exchange_id'];
					$price = $parValue * $nums;
					$platForm = '星期天';
					
					$getResult = 1;
				}
			}
		}
		if ($getResult == '1') {
			$deliver = new Deliver();
			
			$deliver->id = $orderInfo['id'];
			$deliver->status = 4;
			$deliver->confirm_userid = 0;
			$deliver->confirm_time = time();
			$deliver->platform = $platForm;
			$deliver->third_order = $thirdOrder;
			$deliver->price = $price;
			$deliver->standard = '';
			$deliver->mark_text = '自动发货';
			$deliver->deliver_company = '0';
			$deliver->deliver_order = '0';
			$deliver->prepare_userid = 0;
			$deliver->prepare_time = time();
			$deliver->deliver_userid = 0;
			$deliver->deliver_time = time();
			$deliver->is_exchange = 0;
			$deliver->bill = '无';
			
			$deliver->save(false);
			
			$redis->del($getCardKey);
			return array('code' => 100);
		} else {
			$redis->del($getCardKey);
			return array('code' => 0);
		}
	}
	
	/**
	 * 卡列表
	 * @return [type] [description]
	 */
	public function actionGetVirtualList()
	{
		$id = Yii::$app->request->get('id');
		$list = UserVirtual::find()->where(['orderid' => $id, 'uid' => $this->userId])->asArray()->all();
		$data = array();
		$type = '';
		foreach ($list as $key => $value) {
			$data[$key]['id'] = $value['id'];
			$data[$key]['card'] = $value['card'];
			$data[$key]['pwd'] = '***************';
			$data[$key]['parvalue'] = $value['par_value'];
			$data[$key]['time'] = date("Y-m-d H:i:s", $value['create_time']);
			if ($value['type'] == 'yd') {
				$type = '中国移动';
			} else if ($value['type'] == 'lt') {
				$type = '中国联通';
			} else if ($value['type'] == 'dx') {
				$type = '中国电信';
			} else if ($value['type'] == 'hgb') {
				$type = '伙购币';
			}
		}
		return array('code' => 100, 'list' => $data, 'type' => $type);
	}
	
	/**
	 * 查看卡密
	 * @return [type] [description]
	 */
	public function actionGetPwd()
	{
		$id = Yii::$app->request->get('id');
		$pwd = UserVirtual::find()->where(['uid' => $this->userId, 'id' => $id])->asArray()->one();
		if ($pwd) {
			if ($pwd['status'] == '0') {
				UserVirtual::updateAll(
					array(
						'status' => 1,
						'update_time' => time()
					), array("id" => $id)
				);
				$all = UserVirtual::find()->where(['orderid' => $pwd['orderid']])->asArray()->all();
				$complete = 1;
				foreach ($all as $key => $value) {
					if ($value['status'] == '0') {
						$complete = 0;
						break;
					}
				}
				if ($complete) {
					Order::updateAll(array('status' => 8), array('id' => $pwd['orderid']));
				}
			}
			return array('code' => 100, 'result' => $pwd['pwd']);
		} else {
			return array('code' => 0);
		}
	}
	
	/**
	 * 虚拟物品确认地址
	 */
	public function actionSubmitAddressVirtual()
	{

		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$request = Yii::$app->request;
		$id = $request->get('id');
		$addressId = $request->get('addressId');
		
		$redis = new MyRedis();
		$limitKey = UserVirtualHand::VIRTUAL_HAND_LIMIT . $id;
		$handle = $redis->incr($limitKey);
		$redis->expire($limitKey, 60);
		if ($handle > 1) {
			return ['code' => '101', 'msg' => '正在确认'];
		}
		
		$virtualAddress = UserVirtualAddress::find()->where(['id' => $addressId])->asArray()->one();
		if (!$virtualAddress) {
			return ['code' => '101', 'msg' => '虚拟地址不存在'];
		}
		$orderInfo = Order::find()->where(['id' => $id, 'user_id' => $this->userId, 'status' => [0, 6]])->asArray()->one();
		if (!$orderInfo) {
			$redis->del($limitKey);
			return ['code' => '101', 'msg' => '订单不存在'];
		}
		
		$productInfo = Product::info($orderInfo['product_id']);
		if (!in_array($productInfo['delivery_id'], [5, 6, 7, 9, 10])) {
			$redis->del($limitKey);
			return ['code' => '101', 'msg' => '该商品不是虚拟商品'];
		}
		
		if ($virtualAddress['type'] == 'qb') {
			if (!in_array($productInfo['delivery_id'], [6, 9])) {
				return ['code' => '101', 'msg' => '充值地址类型与商品不匹配不正确'];
			}
		} elseif ($virtualAddress['type'] == 'tb' || $virtualAddress['type'] == 'wx') {
			if ($productInfo['delivery_id'] != 5) {
				return ['code' => '101', 'msg' => '充值地址类型与商品不匹配不正确'];
			}
		} elseif ($virtualAddress['type'] == 'dh') {
			if (!in_array($productInfo['delivery_id'], [7, 10])) {
				return ['code' => '101', 'msg' => '充值地址类型与商品不匹配不正确'];
			}
		} else {
			return ['code' => '101', 'msg' => '充值地址类型不正确'];
		}
		
		try {
			$virtualProductInfo = VirtualProductInfo::findOne(['order_id' => $id]);
			
			if (!$virtualProductInfo) {
				$virtualProductInfo = new VirtualProductInfo();
			}
			$virtualProductInfo->type = $virtualAddress['type'];
			$virtualProductInfo->account = $virtualAddress['account'];
			$virtualProductInfo->name = $virtualAddress['name'];
			$virtualProductInfo->order_id = $id;
			$virtualProductInfo->user_id = $this->userId;
			$virtualProductInfo->created_at = time();
			
			if (!$virtualProductInfo->save(false)) {
				$redis->del($limitKey);
				return ['code' => '1012', 'msg' => '确认失败'];
			}
			if ($productInfo['face_value'] >= 200 || ($productInfo['face_value'] > 0 && $productInfo['delivery_id'] == 7)) {
				$userVirtualHand = UserVirtualHand::findOne(['order_id' => $id]);
				if (!$userVirtualHand) {
					$userVirtualHand = new UserVirtualHand();
				}
				$userVirtualHand->user_id = $this->userId;
				$userVirtualHand->order_id = $id;
				$userVirtualHand->type = $virtualAddress['type'];
				$userVirtualHand->account = $virtualAddress['account'];
				$userVirtualHand->name = $virtualAddress['name'];
				$userVirtualHand->status = 0;
				$userVirtualHand->created_at = time();
				
				if (!$userVirtualHand->save(false)) {
					$redis->del($limitKey);
					return ['code' => '1012', 'msg' => '确认失败'];
				} else {
					Order::updateAll(['status' => 1, 'last_modified' => time(), 'confirm_addr_time' => time()], ['id' => $id]);
				}
			} else {
				if (($productInfo['delivery_id'] == 9 and $virtualAddress['type'] == 'qb') || ($productInfo['delivery_id'] == 10 && $virtualAddress['type'] == 'dh')) {
					$purchase = new Purchase();
					if ($productInfo['delivery_id'] == 10) {
						$onlinePay = $purchase->onlineCharge($virtualAddress['account'], 'mobile_online', $productInfo['face_value'], $id);
					} else {
						$onlinePay = $purchase->onlinePay($virtualAddress['account'], 'qbonline', $productInfo['face_value'], $id);
					}

					if (!$onlinePay) {
						$redis->del($limitKey);
						return ['code' => '1013', 'msg' => '确认失败'];
					} else {
						$redis->del($limitKey);
						Order::updateAll(['status' => 4, 'last_modified' => time()], ['id' => $id]);
						return ['code' => 100, 'msg' => '确认成功', 'url' => null];
					}
				} else {
					
					if ($virtualAddress['type'] == 'wx')  //如果是微信则自动充值到微信
					{
						
						$amount = $productInfo['face_value'];  //金额
						$desc = $productInfo['name']; // 中奖商品标题
						$openid = $virtualAddress['account']; // 对应appid 用户的openid
						$partner_trade_no = $orderInfo['order_no']; // 站内订单号，因为测试所以随便填的
						$re_user_name = $virtualAddress['name']; //用户微信的实名认证
						$result = \Yii::$app->wxpay->pay($partner_trade_no, $amount, $re_user_name, $desc, $openid, $this->userInfo['home_id'], $this->userId);
						
						if ($result && $result['code'] == 1) {
							$redis->del($limitKey);
							Order::updateAll(['status' => 8, 'last_modified' => time(), 'confirm_addr_time' => time()], ['id' => $id]);
							return ['code' => 100, 'msg' => '确认成功', 'url' => null];
						} else {
							$redis->del($limitKey);
							Order::updateAll(['status' => 6, 'last_modified' => time(), 'confirm_addr_time' => time()], ['id' => $id]);
							$msg = !empty($result['msg']) ? $result['msg'] : '确认失败';
							return ['code' => 10154, 'msg' => $msg];
						}
						
					} else {
						$return['url'] = 'http://www.' . DOMAIN . '/duiba/redirect.html?orderId=' . $id;
						$return['script'] = <<<EOF
var dataKey={$productInfo['face_value']};if(document.location.href.indexOf('http://www.duiba.com.cn/mobile/detail')>=0){ $('.need').hide();$('.tools').hide();$('.radio-group').hide();$('.radio-group[data-key="'+dataKey+'"]').show().css({marginRight:'0',width:'99.9%'});$('.radio-group[data-key="'+dataKey+'"]').trigger('click').unbind('click');$('input').unbind('focus');$('input').unbind('blur');$('.submit').removeAttr('disabled').text('确定');$('body').on('click',function(){var txt=$('.modal-header').text();if(typeof txt=='string'&&txt.indexOf('金币')>0){ $('.modal-header').text('您确定兑换到此账户吗?')}});$('.tip').remove();$('<p class="tip error" style="display: block;">虚拟商品目前是审核状态，发放时间是周一到周日上午9点到晚上9点，请耐心等待。</p>').prependTo($('#db-content'))}else{ $('footer').remove()}
EOF;
						$return['resultUrl'] = 'http://www.duiba.com.cn/crecord/recordDetail/';
					}
				}
			}
			
			
			$redis->del($limitKey);
			
			Order::updateAll(['last_modified' => time(), 'confirm_addr_time' => time()], ['id' => $id]);
			
			$return['code'] = 100;
			$return['msg'] = "确认成功";
			return $return;
		} catch (\Exception $e) {
			$redis->del($limitKey);
			return ['code' => 101, 'msg' => '确认失败'];
		}
	}

	public function actionGetVirtualJd()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$id = Yii::$app->request->get('id');
		$type = Yii::$app->request->get('type');
		$mode = Yii::$app->request->get('mode');
		$mobile = Yii::$app->request->get('mobile');
		
		$redis = new MyRedis();
		$getCardKey = UserVirtual::GET_CARD_KEY . $id;
		$handle = $redis->sset($getCardKey, '1');
		$redis->expire($getCardKey, 60);
		if (!$handle) {
			return ['code' => 101, 'msg' => '正在查看'];
		}
		
		if (!in_array($type, ['jd'])) {
			$redis->del($getCardKey);
			return ['code' => 101, 'msg' => '获取失败'];
		}
		
		$orderInfo = Order::find()->where(['id' => $id])->asArray()->one();
		if ($orderInfo['status'] != 0) {
			$redis->del($getCardKey);
			return ['code' => 101, 'msg' => '获取失败'];
		}
		
		$productInfo = Product::info($orderInfo['product_id']);
		if ($productInfo['delivery_id'] != 8) {
			$redis->del($getCardKey);
			return ['code' => 101, 'msg' => '获取失败'];
		}
		
		// 派发卡密
		$card = VirtualDepotJd::find()->where(['par_value' => $productInfo['face_value'], 'status' => 0])->one();
		if (!$card) {
			$redis->del($getCardKey);
			return ['code' => 101, 'msg' => '获取失败'];
		}
		
		$trans = Yii::$app->db->beginTransaction();
		try {
			VirtualDepotJd::updateAll(['status' => 1], ['id' => $card['id']]);
			
			//手机或显示卡密
			$userVirtual = new UserVirtual();
			$userVirtual->uid = $this->userId;
			$userVirtual->type = $type;
			$userVirtual->orderid = $id;
			$userVirtual->par_value = $productInfo['cost'];
			$userVirtual->card = $card['card'];
			$userVirtual->pwd = $card['pwd'];
			if ($mode == 'msg') {
				$userVirtual->status = 2;
				$userVirtual->mobile = $mobile;
			} else {
				$userVirtual->status = 0;
			}
			$userVirtual->create_time = time();
			if (!$userVirtual->save()) {
				$redis->del($getCardKey);
				$trans->rollBack();
				return ['code' => 101, 'msg' => '获取失败'];
			}
			
			if ($mode == 'msg') {
				$messageType = 41;
				$rs = Message::add($messageType, $this->userId, array('type' => '京东卡', 'card' => $card['card'], 'pwd' => $card['pwd']));
				if (!$rs) {
					$trans->rollBack();
					return ['code' => 101, 'msg' => '获取失败'];
				}
				$sendLog = new CardSendLog();
				$sendLog->orderid = $id;
				$sendLog->type = '京东卡';
				$sendLog->user_id = $this->userId;
				$sendLog->mobile = $mobile;
				$sendLog->create_time = time();
				if (!$sendLog->save()) {
					$trans->rollBack();
					return ['code' => 101, 'msg' => '获取失败'];
				}
			}
			
			Order::updateAll(['status' => 8], ['id' => $id]);
			
			$deliver = new Deliver();
			$deliver->id = $orderInfo['id'];
			$deliver->status = 4;
			$deliver->confirm_userid = 0;
			$deliver->confirm_time = time();
			$deliver->platform = '平台';
			$deliver->third_order = $id;
			$deliver->price = $productInfo['cost'];
			$deliver->standard = '';
			$deliver->mark_text = '自动发货';
			$deliver->deliver_company = '0';
			$deliver->deliver_order = '0';
			$deliver->prepare_userid = 0;
			$deliver->prepare_time = time();
			$deliver->deliver_userid = 0;
			$deliver->deliver_time = time();
			$deliver->is_exchange = 0;
			$deliver->bill = '无';
			
			if (!$deliver->save(false)) {
				$redis->del($getCardKey);
				$trans->rollBack();
				return ['code' => 101, 'msg' => '获取失败6'];
			}
			
			$redis->del($getCardKey);
			$trans->commit();
			$data = ['id' => $userVirtual->primaryKey, 'card' => $card['card'], 'pwd' => '***************', 'time' => $userVirtual['create_time']];
			$data['mode'] = $mode;
			if ($mode == 'msg') {
				$data['msg'] = '卡密已发送到' . $mobile;
				return ['code' => 100, 'result' => $data];
			} else {
				return ['code' => 100, 'result' => $data];
			}
		} catch (\Exception $e) {
			$redis->del($getCardKey);
			$trans->rollBack();
			return ['code' => 101, 'msg' => '获取失败7'];
		}
	}
	
	public function actionGetVirtualJdList()
	{
		$id = Yii::$app->request->get('id');
		$userVirtual = UserVirtual::find()->where(['orderid' => $id, 'uid' => $this->userId])->asArray()->one();
		$data = [];
		if ($userVirtual) {
			if ($userVirtual['status'] == '2') {
				$data['mode'] = 'msg';
				$data['msg'] = '卡密已发送到' . $userVirtual['mobile'];
			} else {
				$data['id'] = $userVirtual['id'];
				$data['card'] = $userVirtual['card'];
				$data['pwd'] = '***************';
				$data['parvalue'] = $userVirtual['par_value'];
				$data['time'] = date("Y-m-d H:i:s", $userVirtual['create_time']);
				$data['mode'] = 'check';
			}
		}
		return ['code' => 100, 'list' => $data];
	}
	
	public function actionGetPwdJd()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$id = Yii::$app->request->get('id');
		$pwd = UserVirtual::find()->where(['uid' => $this->userId, 'id' => $id])->asArray()->one();
		if ($pwd && $pwd['status'] != 2) {
			if ($pwd['status'] == '0') {
				UserVirtual::updateAll(['status' => 1, 'update_time' => time()], ['id' => $id]);
			}
			return ['code' => 100, 'result' => $pwd['pwd']];
		} else {
			return ['code' => 101, 'msg' => '查看失败'];
		}
	}
	
	/** 用户pk参与记录
	 * @return array
	 */
	public function actionPkBuyList()
	{
		if (!$this->userId) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = Yii::$app->request;
		$type = $request->get('type', 'all');//all=全部pk记录 lucky=幸运记录
		$page = $request->get('page', 1);
		$status = $request->get('status', 0);
		$perpage = $request->get('perpage', 10);
		$region = Yii::$app->request->get('region', '');
		$startTime = Yii::$app->request->get('start_time', '');
		$endTime = Yii::$app->request->get('end_time', '');
		
		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime . " 23:59:59");
		}
		
		if (!($startTime && $endTime) && $region) {
			list($startTime, $endTime) = DateFormat::formatConditionTime($region);
		}
		if ($status == 0) { //全部
			$status = -1;
		} elseif ($status == 1) { //待确认地址
			$status = [Order::STATUS_INIT, Order::STATUS_REJECT];
		} elseif ($status == 2) { //待发货
			$status = [Order::STATUS_COMMIT_ADDRESS, Order::STATUS_COMFIRM_ADDRESS, Order::STATUS_PREPARE_GOODS];
		} elseif ($status == 3) { //待收货
			$status = Order::STATUS_SHIPPING;
		} elseif ($status == 4) { //待晒单
			$status = Order::STATUS_COMFIRM_RECEIVE;
		} elseif($status == -1){//未填写收货信息
			$status = [Order::STATUS_INIT];
		}
		$member = new Member(['id' => $this->userId]);
		if ($type == 'all') {
			$result = $member->getPkBuyList($page, $perpage);
		} elseif ($type == 'lucky') {
			$result = $member->getPkLuckList($startTime, $endTime, $page, $perpage, $status);
		}
		
		return $result;
		
	}
	
}