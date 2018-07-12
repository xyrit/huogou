<?php

namespace app\modules\api\controllers;

use yii;
use app\services\Thirdpay;
use yii\helpers\Url;
use app\services\Pay;

class ChatpayController extends BaseController
{
    public function actionIndex(){
        $order = Yii::$app->request->get('o');
        $orderInfo = Thirdpay::getOrderByNo($order);
        if ($orderInfo['status'] == 1) {
        	$data['result'] = 1;
        	if ($orderInfo['type'] == '1') {
                $data['type'] = 1;
        		$data['url'] = Url::to(['/member/recharge/money-log']);
        	}else if ($orderInfo['type'] == '2') {
                $pay = new Pay($orderInfo['user_id']);
                $result = $pay->createPayOrder($orderInfo['source'],$orderInfo['point'],1,$orderInfo['bank'],$order);
        		$data['url'] = Url::to(['/pay/result.html','o'=>$result]);
                $data['type'] = 2;
                $data['order'] = $result;
        	}
            return $data;
        }
        return array('result'=>0);
    }
}