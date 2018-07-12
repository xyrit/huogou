<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/15
 * Time: 上午11:01
 */
namespace app\services;

use app\models\Order;
use app\models\UserVirtual;
use Yii;

class PkOrder
{

    public static function productInfo($product, $uid)
    {
        $product['goods_id'] = $product['id'];
        $product['status_type'] = 0;
        if ($product['status'] == Order::STATUS_INIT || $product['status'] == Order::STATUS_REJECT) {
            if ($product['delivery_id'] == '3') {
                if ($product['status'] == Order::STATUS_INIT) {
                    $statusName = '选择运营商';
                } else {
                    $statusName = '收货信息错误，请重新确认';
                }
                $product['status_name'] = $statusName;
            } elseif (in_array($product['delivery_id'], [5,6,7,9,10]) ) {
                if ($product['status'] == Order::STATUS_INIT) {
                    $statusName = '等待填写信息';
                } else {
                    $statusName = '收货信息错误，请重新确认';
                }
                if (!in_array($product['delivery_id'], [9,10])) {
                    $product['tips_img'] = \Yii::$app->params['skinUrl'] . '/img/tips.jpg';
                    $product['tips_content'] = '虚拟商品目前是审核状态，发放时间是周一到周日上午9点到晚上9点，请耐心等待。';
                    if ($product['face_value'] > 200 || ($product['face_value']>50 && $product['delivery_id'] == 7) ) { //7-1 3.8分 200改为自动
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
            } else{
                if ($product['status'] == Order::STATUS_INIT) {
                    $statusName = '等待确认收货地址';
                } else {
                    $statusName = '收货信息错误，请重新确认';
                }
                $product['status_name'] = $statusName;
            }
        } else if ($product['status'] >= Order::STATUS_COMMIT_ADDRESS && $product['status'] <= Order::STATUS_PREPARE_GOODS) {
            if (in_array($product['delivery_id'], [5,6,7,9,10])) {
                $product['status_name'] = '等待商品派发';
                $product['tips_img'] = Yii::$app->params['skinUrl'] . '/img/tips.jpg';
                $product['tips_content'] = '虚拟商品目前是审核状态，发放时间是周一到周日上午9点到晚上9点，请耐心等待。';
            } else {
                $product['status_name'] = '等待商品派发';
            }
        } elseif ($product['status'] == Order::STATUS_SHIPPING) {
            if ($product['delivery_id'] == '3') {
                $product['status_name'] = '查看卡密';
            }else{
                $product['status_name'] = '商品已派发';
            }
        } elseif ($product['status'] == Order::STATUS_COMFIRM_RECEIVE) {
            if($product['is_exchange']){
                $product['status_name'] = '订单完成';
            }else{
                if ($product['delivery_id'] == '3') {
                    $product['status_name'] = '查看卡密';
                }else{
                    if ($product['allow_share'] == 1) {
                        $product['status_name'] = '待晒单';
                    }else {
                        $product['status_name'] = '订单完成';
                    }
                }
            }
        } else {
            if ($product['delivery_id'] == '3') {
                $used = UserVirtual::find()->where(['orderid'=>$product['order_id'],'uid'=>$uid])->asArray()->one();
                if ($used['type'] == 'hgb') {
                    $product['status_name'] = '已兑换伙购币';
                }else{
                    $product['status_name'] = '卡密已派发';
                }
            } elseif (in_array($product['delivery_id'], [5, 6, 7, 9,10])) {
                $product['status_name'] = '订单完成';
            } elseif ($product['delivery_id'] == 8) {
                $product['status_name'] = '已领取';
            } else {
                $product['status_name'] = '订单完成';
            }
        }
        return $product;
    }


}