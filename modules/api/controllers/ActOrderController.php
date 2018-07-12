<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/4/5
 * Time: 16:35
 */
namespace app\modules\api\controllers;

use app\models\ActDeliver;
use app\models\ActOrder;
use app\modules\admin\models\Deliver;
use Yii;
use app\helpers\DateFormat;

class ActOrderController extends BaseController
{

    public function actionInfo()
    {
        $request = \Yii::$app->request;
        $orderId = $request->get('id');

        $orderInfo = ActOrder::find()->where(['id' => $orderId, 'user_id' => $this->userId])->asArray()->one();
        if (!$orderInfo) return [];

        $actTypeName = ActOrder::$type_name[$orderInfo['act_type']];
        $data['productInfo'] = [
            'name' => "【" . $actTypeName . "】" . $orderInfo['name'],
            'picture' => $orderInfo['picture'],
            'raff_time' => DateFormat::microDate($orderInfo['create_time']),
        ];
        $data['status'] = $orderInfo['status'];
        $data['shipInfo'] = [
            'area' => $orderInfo['ship_area'],
            'addr' => $orderInfo['ship_addr'],
            'username' => $orderInfo['ship_name'],
            'zip' => $orderInfo['ship_zip'],
            'tel' => $orderInfo['ship_mobile'],
            'mark_text' => $orderInfo['mark_text'],
        ];
        $data['orderLogs'] = [];

        // 订单详细流程

        if ($orderInfo['status']==0) {
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

        if ($orderInfo['status']==0) {
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

        if (in_array($orderInfo['status'], [1,2,3])) {
            $detail['time'] = '';
            $detail['content'] = "商品派发";
            $detail['name'] = "";
            $detail['current'] = true;
            $detail['currentText'] = '请等待...';
            $data['orderLogs'][] = $detail;
        } else {
            $delivery = ActDeliver::find()->where(['id'=>$orderId])->one();
            $employee = Deliver::getEmployeeName();
            $detail['time'] = $delivery['prepare_time'] ? DateFormat::microDate($delivery['prepare_time']) : '';
            if ($orderInfo['status']>=4 ) {
                $detail['content'] = "商品派发";
                if ($delivery['deliver_company']) {
                    $detail['content'] .= "【{$delivery['deliver_company']}】{$delivery['deliver_order']}";
                }
            } else {
                $detail['content'] = "商品派发";
            }
            $detail['name'] = "";
            if ($delivery['prepare_userid'] && isset($employee[$delivery['prepare_userid']])) {
                $prepareAdmin = $employee[$delivery['prepare_userid']];
                $detail['name'] = mb_substr($prepareAdmin, 0, 1) .'***';
            }
            $detail['current'] = false;
            $detail['currentText'] = '';
            $data['orderLogs'][] = $detail;
        }

        if ($orderInfo['status']==4) {
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

        if ($orderInfo['status']==8) {
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

        //颜色设置
        $contentColor = '#333333';
        foreach($data['orderLogs'] as &$log) {
            if ($orderInfo['status']==8 && $log['current']) {
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