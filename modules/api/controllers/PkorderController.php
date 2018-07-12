<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/12
 * Time: 上午9:37
 */
namespace app\modules\api\controllers;
use app\models\ActivityProducts;
use app\models\JdcardBuybackList;
use app\models\JdcardBuybackMobile;
use app\models\PkOrders;
use app\services\PkPeriod as PkPeriodService;
use app\services\PkProduct;
use app\helpers\DateFormat;
class PkorderController extends BaseController{


    //确认手机号
    public function actionConfirmOrder(){

        $request=\Yii::$app->request;
        $mobile=$request->get('mobile');
        $pk_oid=$request->get('id');
        if(!$mobile || !$pk_oid)
        {
            return ['code'=>201,'message'=>'手机号未填写'];
        }
        //查询订单
        $pkorderinfo=PkOrders::findOne($pk_oid);
        if($pkorderinfo)
        {
            if($pkorderinfo['status']==0 && $pkorderinfo['confirm']==0 && $pkorderinfo['user_id']==$this->userId)
            {
                //判断手机是否是回购手机号
                if(JdcardBuybackMobile::find()->where(['mobile'=>$mobile])->one())
                {
                    //生成回购单
                  $buyback=new JdcardBuybackList();
                    $buyback->order_id=$pk_oid;
                    $buyback->user_id=$pkorderinfo['user_id'];
                    $buyback->mobile=$mobile;
                    $buyback->product_id=$pkorderinfo['product_id'];
                    $buyback->add_time=time();
                    $buyback->pay_type='1';
                    $buyback->order_type=1;
                    $buyback->face_value=ActivityProducts::findOne($pkorderinfo['product_id'])->face_value;
                    $buyback->period_id=$pkorderinfo['period_id'];
                    if(!$buyback->save()){
                        return ['code'=>201,'message'=>$buyback->getErrors()];
                    }
                    $pkorderinfo->is_buyback=1;
                }

                $pkorderinfo->ship_mobile=$mobile;
                $pkorderinfo->confirm=1;
                $pkorderinfo->status=3;
                $pkorderinfo->last_modified=time();
                $pkorderinfo->confirm_addr_time=time();
                if($pkorderinfo->save(false))
                {
                    return ['code'=>200,'message'=>'提交成功'];
                }
            }
        }
                    return ['code'=>201,'message'=>'提交失败'];
    }

    //订单详情
    public function actionInfo()
    {
        $request = \Yii::$app->request;
        $orderId = $request->get('id');
        $orderInfo = PkOrders::find()->where(['id' => $orderId, 'user_id' => $this->userId])->asArray()->one();
        if (!$orderInfo) return ['user_id'=>$this->userId];
        $periodInfo = PkPeriodService::info($orderInfo['period_id']);
        $periodInfo['user_buy_num']=intval($periodInfo['price']/2);
        $productInfo = PkProduct::info($orderInfo['product_id']);
        $data['allow_share'] = $productInfo['allow_share'];
        $data['periodInfo'] = $periodInfo;
        $data['status'] = $orderInfo['status'];
        $data['status_type'] = 0;
        if ($orderInfo['status'] == PkOrders::STATUS_INIT || $orderInfo['status'] == PkOrders::STATUS_REJECT) {
          if ($productInfo['delivery_id'] == 8) { // 京东充值卡卡密
                $data['status_name'] = '等待领取';
            } else {
                $data['status_name'] = '等待确认收货地址';
            }
        } else if ($orderInfo['status'] >= PkOrders::STATUS_COMMIT_ADDRESS && $orderInfo['status'] <= PkOrders::STATUS_PREPARE_GOODS) {
            $data['status_name'] = '等待商品派发';
        } elseif ($orderInfo['status'] == PkOrders::STATUS_SHIPPING) {
            $data['status_name'] = '商品已派发';
        } elseif ($orderInfo['status'] == PkOrders::STATUS_COMFIRM_RECEIVE) {
            $data['status_name'] = '订单完成';
        } else {
            $data['status_name'] = '订单完成';
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

        $data['orderLogs'] = [];


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


        if (in_array($productInfo['delivery_id'], [8])) {

            if ($orderInfo['status'] == 0) {
                $detail['time'] = $orderInfo['confirm_addr_time']? DateFormat::microDate($orderInfo['confirm_addr_time']) : '';
                $detail['content'] = "确认充值信息";
                $detail['name'] = "";
                $detail['current'] = true;
                $detail['currentText'] = '';
                $data['orderLogs'][] = $detail;
            } else {
                $detail['time'] = $orderInfo['confirm_addr_time'] ? DateFormat::microDate($orderInfo['confirm_addr_time']) : '';
                $detail['content'] = "确认充值信息";
                $detail['name'] = "";
                $detail['current'] = false;
                $detail['currentText'] = '';
                $data['orderLogs'][] = $detail;
            }


            if (in_array($orderInfo['status'], [1, 2, 3, 4])) {
                $detail['time'] = '';
                $detail['name'] = '会员本人';
                if (!empty($duibaOrder)) {
                    $detail['time'] = DateFormat::microDate($orderInfo['confirm_addr_time'] );
                } else {
                    if ($orderInfo['confirm_addr_time'] ) {
                        $detail['time'] = DateFormat::microDate($orderInfo['confirm_addr_time'] );
                        $detail['name'] = '';
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
                    $detail['time'] = DateFormat::microDate($orderInfo['confirm_addr_time'] );
                } else {
                    if (!empty($duibaOrder)) {
                        $detail['time'] = DateFormat::microDate($duibaOrder['created_at']);
                    } else {
                        if ($orderInfo['confirm_addr_time'] ) {
                            $detail['time'] = DateFormat::microDate($orderInfo['confirm_addr_time'] );
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


}