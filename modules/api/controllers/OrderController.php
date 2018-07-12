<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/25
 * Time: 下午8:15
 */
namespace app\modules\api\controllers;

use app\helpers\DateFormat;
use app\helpers\Express;
use app\helpers\MyRedis;
use app\models\DuibaOrderDistribution;
use app\models\Order;
use app\models\ShareTopic;
use app\models\UserVirtual;
use app\models\UserVirtualHand;
use app\models\VirtualProductInfo;
use app\modules\admin\models\Deliver;
use app\services\Period as PeriodService;
use app\services\Product;
use Yii;
use app\modules\admin\models\ExchangeOrder;
use app\models\Product as ProductModel;
use app\models\JdcardBuybackMobile;
use app\models\JdcardBuybackList;

class OrderController extends BaseController
{
	//订单详情
	public function actionInfo()
	{
		$request = \Yii::$app->request;
		$orderId = $request->get('id');
		
		$orderInfo = Order::find()->where(['id' => $orderId, 'user_id' => $this->userId])->asArray()->one();
		if (!$orderInfo) return [];
		$periodInfo = PeriodService::info($orderInfo['period_id']);
		$productInfo = Product::info($orderInfo['product_id']);
		$data['allow_share'] = $productInfo['allow_share'];
		$data['periodInfo'] = $periodInfo;
		$data['status'] = $orderInfo['status'];
		$data['status_type'] = 0;
		if ($orderInfo['status'] == Order::STATUS_INIT || $orderInfo['status'] == Order::STATUS_REJECT) {
			if ($productInfo['delivery_id'] == '3') {
				$data['status_name'] = '选择运营商';
			} elseif (in_array($productInfo['delivery_id'], [5, 6, 7, 9, 10])) {
				if ($productInfo['status'] == Order::STATUS_INIT) {
					$statusName = '等待填写信息';
				} else {
					$statusName = '收货信息错误，请重新确认';
				}
				if (!in_array($productInfo['delivery_id'], [9, 10])) {
					if ($productInfo['face_value'] >= 200 || ($productInfo['face_value'] > 0 && $productInfo['delivery_id'] == 7)) {
						$data['status_type'] = 1;
						$data['status_name'] = $statusName;
					} else {
						$data['status_type'] = 2;
						$data['status_name'] = $statusName;
					}
				} else {
					$data['status_type'] = 1;
					$data['status_name'] = $statusName;
				}
				
			} elseif ($productInfo['delivery_id'] == 8) { // 京东充值卡卡密
				$data['status_name'] = '等待领取';
			} else {
				$data['status_name'] = '等待确认收货地址';
			}
		} else if ($orderInfo['status'] >= Order::STATUS_COMMIT_ADDRESS && $orderInfo['status'] <= Order::STATUS_PREPARE_GOODS) {
			if (in_array($productInfo['delivery_id'], [5, 6, 7, 9, 10])) {
				$data['status_name'] = '等待商品派发';
			} else {
				$data['status_name'] = '等待商品派发';
			}
		} elseif ($orderInfo['status'] == Order::STATUS_SHIPPING) {
			if ($productInfo['delivery_id'] == '3') {
				$data['status_name'] = '商品已派发';
			} else {
				$data['status_name'] = '商品已派发';
			}
		} elseif ($orderInfo['status'] == Order::STATUS_COMFIRM_RECEIVE) {
			if ($orderInfo['is_exchange']) {
				$data['status_name'] = '订单完成';
			} else {
				if ($productInfo['delivery_id'] == '3') {
					$data['status_name'] = '订单完成';
				} else {
					if ($productInfo['allow_share'] == 1) {
						$product['status_name'] = '等待晒单(最高获取1500福分奖励)';
					} elseif ($orderInfo['status'] == Order::STATUS_COMFIRM_RECEIVE) {
						
					} else {
						$data['status_name'] = '订单完成';
					}
				}
			}
		} else {
			if ($productInfo['delivery_id'] == '3') {
				$used = UserVirtual::find()->where(['orderid' => $orderInfo['id'], 'uid' => $this->userId])->asArray()->one();
				if ($used['type'] == 'hgb') {
					$data['status_name'] = '已兑换伙购币';
				} else {
					$data['status_name'] = '查看卡密';
				}
			} elseif (in_array($productInfo['delivery_id'], [5, 6, 7, 9, 10])) {
				$data['status_name'] = '订单完成';
			} elseif ($productInfo['delivery_id'] == 8) {
				$data['status_name'] = '订单完成';
			} else {
				$data['status_name'] = '订单完成';
			}
		}
		$data['shipInfo'] = [
			'area' => $orderInfo['ship_area'],
			'addr' => $orderInfo['ship_addr'],
			'username' => $orderInfo['ship_name'],
			'zip' => $orderInfo['ship_zip'],
			'tel' => $orderInfo['ship_mobile'],
			'mark_text' => $orderInfo['mark_text'],
		];
		$data['delivery_id'] = $productInfo['delivery_id'];
		$virtualProductInfo = VirtualProductInfo::findOne(['order_id' => $orderId]);
		if ($productInfo['delivery_id'] == 8) {
			$virtualProductInfo['created_at'] = $orderInfo['confirm_addr_time'];
			$virtualProductInfo['account'] = $orderInfo['ship_mobile'];
			$virtualProductInfo['type'] = 'jd';
		}
		$data['addressInfo'] = $virtualProductInfo;
		$data['orderLogs'] = [];
		
		if ($orderInfo['status'] == 0 || $orderInfo['status'] == 6) {
			if ($orderInfo['status'] == 6) { // 驳回原因
				if (!empty($orderInfo['fail_id'])) {
					$orderInfo['fail_info'] = $orderInfo['fail_id'];
					$detail['time'] = DateFormat::microDate($orderInfo['create_time']);
					$detail['content'] = $orderInfo['fail_info'];
					$detail['name'] = "伙购系统";
					$detail['current'] = true;
					$detail['currentText'] = '';
					$data['orderLogs'][] = $detail;
				}
			}
			return ['data' => $data];
		}
		
		// 订单详细流程
		
		if ($orderInfo['status'] == 0) {
			$detail['time'] = DateFormat::microDate($orderInfo['create_time']);
			$detail['content'] = "恭喜您获得商品";
			$detail['name'] = "伙购系统";
			$detail['current'] = true;
			$detail['currentText'] = '';
			$data['orderLogs'][] = $detail;
		} else {
			$detail['time'] = DateFormat::microDate($orderInfo['create_time']);
			$detail['content'] = "恭喜您获得商品";
			$detail['name'] = "伙购系统";
			$detail['current'] = false;
			$detail['currentText'] = '';
			$data['orderLogs'][] = $detail;
		}
		
		
		if (in_array($productInfo['delivery_id'], [5, 6, 7, 8, 9, 10])) {
			
			if ($orderInfo['status'] == 0) {
				if (in_array($productInfo['delivery_id'], [5, 6, 7])) {
					$duibaOrder = DuibaOrderDistribution::findByTableId($this->userInfo['home_id'])->where(['order_no' => $orderId])->orderBy('created_at asc')->one();
					$detail['time'] = $duibaOrder ? DateFormat::microDate($duibaOrder['created_at']) : '';
					$detail['content'] = "确认收货信息";
					$detail['name'] = "会员本人";
					$detail['current'] = true;
					$detail['currentText'] = '';
					$data['orderLogs'][] = $detail;
				} else {
					$detail['time'] = $virtualProductInfo ? DateFormat::microDate($virtualProductInfo['created_at']) : '';
					$detail['content'] = "确认收货信息";
					$detail['name'] = "";
					$detail['current'] = true;
					$detail['currentText'] = '';
					$data['orderLogs'][] = $detail;
				}
			} else {
				
				if (in_array($productInfo['delivery_id'], [5, 6, 7])) {
					$duibaOrder = DuibaOrderDistribution::findByTableId($this->userInfo['home_id'])->where(['order_no' => $orderId])->orderBy('created_at asc')->one();
					$detail['time'] = DateFormat::microDate($duibaOrder['created_at']);
					$detail['content'] = "确认收货信息";
					$detail['name'] = "会员本人";
					$detail['current'] = false;
					$detail['currentText'] = '';
					$data['orderLogs'][] = $detail;
				} else {
					$detail['time'] = $virtualProductInfo ? DateFormat::microDate($virtualProductInfo['created_at']) : '';
					$detail['content'] = "确认收货信息";
					$detail['name'] = "";
					$detail['current'] = false;
					$detail['currentText'] = '';
					$data['orderLogs'][] = $detail;
				}
			}
			
			
			if (in_array($orderInfo['status'], [1, 2, 3, 4])) {
				$detail['time'] = '';
				$detail['name'] = '会员本人';
				
				if ($productInfo['delivery_id'] == '9') {
					$detail['time'] = DateFormat::microDate($virtualProductInfo['created_at']);
				} else {
					if (!empty($duibaOrder)) {
						$detail['time'] = DateFormat::microDate($duibaOrder['created_at']);
					} else {
						if ($virtualProductInfo) {
							$detail['time'] = DateFormat::microDate($virtualProductInfo['created_at']);
							$detail['name'] = '';
						}
					}
				}
				$detail['content'] = "商品派发";
				$detail['current'] = true;
				$detail['currentText'] = '请等待...';
				$data['orderLogs'][] = $detail;
			} else {
				$detail['time'] = '';
				$detail['name'] = "伙购系统";
				
				if ($productInfo['delivery_id'] == '9') {
					$detail['time'] = DateFormat::microDate($virtualProductInfo['created_at']);
				} else {
					if (!empty($duibaOrder)) {
						$detail['time'] = DateFormat::microDate($duibaOrder['created_at']);
					} else {
						if ($virtualProductInfo) {
							$detail['time'] = DateFormat::microDate($virtualProductInfo['created_at']);
							$detail['name'] = '';
						}
					}
				}
				$detail['content'] = "商品派发";
				$detail['current'] = false;
				$data['orderLogs'][] = $detail;
			}
			
			if ($orderInfo['status'] == 8) {
				$detail['time'] = DateFormat::microDate($orderInfo['last_modified']);
				$detail['content'] = "订单完成";
				$detail['name'] = "伙购系统";
				$detail['current'] = true;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			} else {
				$detail['time'] = '';
				$detail['content'] = "订单完成";
				$detail['name'] = "伙购系统";
				$detail['current'] = false;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			}
			
		} else {
			
			if ($orderInfo['status'] == 0) {
				$detail['time'] = $orderInfo['confirm_addr_time'] ? DateFormat::microDate($orderInfo['confirm_addr_time']) : '';
				$detail['content'] = "确认收货信息";
				$detail['name'] = "";
				$detail['current'] = true;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			} else {
				$detail['time'] = $orderInfo['confirm_addr_time'] ? DateFormat::microDate($orderInfo['confirm_addr_time']) : '';
				$detail['content'] = "确认收货信息";
				$detail['name'] = $orderInfo['confirm_addr_time'] ? "会员本人" : '';
				$detail['current'] = false;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			}
			
			if (in_array($orderInfo['status'], [1, 2, 3])) {
				$detail['time'] = '';
				$detail['content'] = "商品派发";
				$detail['name'] = "";
				$detail['current'] = true;
				$detail['currentText'] = '请等待...';
				$data['orderLogs'][] = $detail;
			} else {
				$delivery = Deliver::find()->where(['id' => $orderId])->one();
				$employee = Deliver::getEmployeeName();
				$detail['time'] = $delivery['prepare_time'] ? DateFormat::microDate($delivery['prepare_time']) : '';
				if ($orderInfo['status'] >= 4 && $productInfo['delivery_id'] == 1) {
					$detail['content'] = "商品派发";
//                    if ($delivery['deliver_company']) {
//                        $detail['content'] .= "【{$delivery['deliver_company']}】{$delivery['deliver_order']}";
//                    }
				} else {
					$detail['content'] = "商品派发";
				}
				$detail['name'] = "";
				if ($delivery['prepare_userid'] && isset($employee[$delivery['prepare_userid']])) {
					$prepareAdmin = $employee[$delivery['prepare_userid']];
					$detail['name'] = mb_substr($prepareAdmin, 0, 1) . '***';
				}
				$detail['current'] = false;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			}
			
			if ($orderInfo['status'] == 4) {
				$detail['time'] = '';
				$detail['content'] = "确认收货";
				$detail['name'] = "";
				$detail['current'] = true;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			} else {
				$detail['time'] = $orderInfo['confirm_goods_time'] ? DateFormat::microDate($orderInfo['confirm_goods_time']) : '';
				$detail['content'] = "确认收货";
				$detail['name'] = "会员本人";
				$detail['current'] = false;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			}
			
			if ($productInfo['allow_share']) {
				if ($orderInfo['status'] == 5) {
					$detail['time'] = '';
					$detail['content'] = "晒单(最高获取1500福分奖励)";
					$detail['name'] = "";
					$detail['current'] = true;
					$detail['currentText'] = '';
					$data['orderLogs'][] = $detail;
				} else if ($orderInfo['status'] < 5) {
					$share = ShareTopic::find()->where(['period_id' => $orderInfo['period_id']])->asArray()->one();
					$detail['time'] = $share ? DateFormat::microDate($share['created_at']) : '';
					$detail['content'] = "晒单(最高获取1500福分奖励)";
					$detail['name'] = "会员本人";
					$detail['current'] = false;
					$detail['currentText'] = '';
					$data['orderLogs'][] = $detail;
				}
			}
			
			if ($orderInfo['status'] == 8) {
				$detail['time'] = !empty($share) ? DateFormat::microDate($share['created_at']) : DateFormat::microDate($orderInfo['last_modified']);
				$detail['content'] = "订单完成";
				$detail['name'] = "伙购系统";
				$detail['current'] = true;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			} else {
				$detail['time'] = '';
				$detail['content'] = "订单完成";
				$detail['name'] = "伙购系统";
				$detail['current'] = false;
				$detail['currentText'] = '';
				$data['orderLogs'][] = $detail;
			}
			
			if (!empty($delivery['deliver_company']) && !empty($delivery['deliver_order'])) {
				$data['deliveryInfo'] = [
					'company' => !empty($delivery['deliver_company']) ? $delivery['deliver_company'] : '',
					'no' => !empty($delivery['deliver_order']) ? $delivery['deliver_order'] : '',
				];
			} else {
				$data['deliveryInfo'] = [
					'company' => '',
					'no' => '',
				];
			}
			
			
		}
		
		//颜色设置
		$contentColor = '#333333';
		foreach ($data['orderLogs'] as &$log) {
			if ($orderInfo['status'] == 8 && $log['current']) {
				$contentColor = '#3BBD41';
			}
			$log['contentColor'] = $contentColor;
			if ($log['current']) {
				$contentColor = '#cccccc';
			}
		}
		
		return ['data' => $data];
	}
	
	//物流信息
	public function actionShip()
	{
		$request = \Yii::$app->request;
		$orderId = $request->get('id');
		$deliverInfo = Deliver::find()->where(['id' => $orderId])->asArray()->one();
		if (!$deliverInfo) return [];
		$express = Express::getOrder($deliverInfo['deliver_company'], $deliverInfo['deliver_order']);
		return $express;
		
	}
	
	// 确认手机号
	public function actionConfirmOrder()
	{
		
		$request = \Yii::$app->request;
		$mobile = $request->get('mobile');
		$oid = $request->get('id');
		if (!$mobile || !$oid) {
			return ['code' => 201, 'message' => '手机号未填写或订单不存在'];
		}
		//查询订单
		$orderinfoModel = Order::findOne($oid);
		if ($orderinfoModel) {
			$orderinfo = $orderinfoModel->toArray();
			$ProductModel = ProductModel::findOne($orderinfo['product_id']);
			$productinfo = $ProductModel->toArray();
			
			if ($productinfo['delivery_id'] != 8) {
				return ['code' => 201, 'message' => '商品发货方式不对' . '商品id' . $orderinfo['product_id'] . '发货方式' . $productinfo['delivery_id']];
			}
			if (in_array($orderinfo['status'],[0,6]) && $orderinfo['user_id'] == $this->userId) {

				//判断手机是否是回购手机号
				if (JdcardBuybackMobile::find()->where(['mobile' => $mobile])->one()) {
					//生成回购单
					
					$buyback = new JdcardBuybackList();
					$buyback->order_id = $oid;
					$buyback->user_id = $orderinfo['user_id'];
					$buyback->mobile = $mobile;
					$buyback->product_id = $orderinfo['product_id'];
					$buyback->add_time = time();
					$buyback->face_value = $productinfo['face_value'];
					$buyback->period_id = $orderinfo['period_id'];
					$buyback->save();
				}
				
				$orderinfoModel->ship_mobile = $mobile;
				$orderinfoModel->confirm = 1;
				$orderinfoModel->last_modified = time();
				$orderinfoModel->status = 1;
				$orderinfoModel->confirm_addr_time = time();
				$orderinfoModel->fail_type = 0;
				$orderinfoModel->fail_id = '';
				$orderinfoModel->ship_name = $mobile;
				if ($orderinfoModel->save(false)) {
					return ['code' => 200, 'message' => '提交成功'];
				}
			}
		}
		return ['code' => 201, 'message' => '提交失败'];
	}
	
	
}