<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/4
 * Time: 上午10:02
 */
namespace app\modules\api\controllers;

use app\helpers\Brower;
use app\helpers\MyRedis;
use app\services\EuroCup;

class EuroCupController extends BaseController
{

    public function actionGamesList()
    {
        $list = EuroCup::getGamesList();
        return ['code' => 100, 'list' => $list];
    }

    public function actionPay()
    {
        $userId = $this->userId;
        if (!$userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $gameDate = $request->get('game_date');
        $team = $request->get('team');
        $money = $request->get('money');
        $deviceType = Brower::getDeviceType();
        if ($deviceType == 'android') {
            $source = 4;
        }else if ($deviceType == 'ios') {
            $source = 3;
        } else {
            return ['code' => 303, 'msg' => '未知客户端'];
        }
        fastcgi_finish_request();
        $pay = EuroCup::pay($gameDate, $team, $money, $userId, $source);
        return $pay;
    }

    public function actionPayResult()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $gameDate = $request->get('game_date');
        $result = EuroCup::payResult($gameDate, $this->userId);
        return $result;
    }

    public function actionUserPayList()
    {
        $request = \Yii::$app->request;
        $limit = $request->get('limit', 10);
        $list = EuroCup::getUserPayList($limit);
        return ['list' => $list];
    }

    public function actionMyOrder()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $list = EuroCup::getOrderListByUid($this->userId);

        return ['list' => $list];
    }

    public function actionMyOrderDetail()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $detail = EuroCup::getOrderDetail($id, $this->userId);

        return $detail;
    }

    public function actionGetReward()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $deviceType = Brower::getDeviceType();
        if ($deviceType == 'android') {
            $source = 4;
        }else if ($deviceType == 'ios') {
            $source = 3;
        } else {
            return ['code' => 303, 'msg' => '未知客户端'];
        }
        $key = 'EUROCUP_GET_REWARD_REQUEST_NUM_' . $id . '_' . $this->userId;
        $redis = new MyRedis();
        $requestNum = $redis->incr($key);
        $redis->expire($key, 5);
        if ($requestNum>1) {
            return ['code' => 304, 'msg' => '请求频繁'];
        }
        $result = EuroCup::getReward($id, $this->userId, $source, true);
        $redis->del($key);
        return $result;
    }

    public function actionRedList()
    {
        if ($this->userId == 0) {
            return ['list' => [], 'sum' => 0];
        }
        $red = EuroCup::getRedPacketListByUid($this->userId);

        $redList = [];
        foreach($red['list'] as $one) {
            $packetId = $one['packet_id'];
            $redOne = $one;
            unset($redOne['order_id']);
            $redList[$packetId] = $redOne;
        }
        return ['list' => $redList, 'sum' => $red['sum']];
    }

    public function actionGetRed()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $packetId = $request->get('packetid');
        $key = 'EUROCUP_GET_RED_REQUEST_NUM_' . $packetId . '_' . $this->userId;
        $redis = new MyRedis();
        $requestNum = $redis->incr($key);
        $redis->expire($key, 5);
        if ($requestNum>1) {
            return ['code' => 304, 'msg' => '请求频繁'];
        }
        $result = EuroCup::getRewardRedByPacketId($packetId, $this->userId);
        $result['code'] = 100;
        $redis->del($key);
        return $result;
    }

    public function actionOrderRank()
    {
        $request = \Yii::$app->request;
        $limit = $request->get('limit', 10);
        $limit = $limit > 100 ? 100 : $limit;
        $list = EuroCup::getOrderRank($limit);
        return ['list' => $list];
    }

    public function actionOrderRankByTeam()
    {
        $request = \Yii::$app->request;
        $team = $request->get('team');
        $limit = $request->get('limit', 10);
        $limit = $limit > 100 ? 100 : $limit;
        $list = EuroCup::getOrderRankByTeam($team, $limit);
        return ['list' => $list];
    }


}