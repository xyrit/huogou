<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/8/1
 * Time: 上午9:24
 */
namespace app\modules\api\controllers;

use app\helpers\MyRedis;
use app\models\OlympicSchedule;
use app\models\Order;
use app\models\Period;
use app\services\Olympic;
use app\services\User;

class OlympicController extends BaseController
{
    public static $medal = [
        1 => 'gold',
        2 => 'silver',
        3 => 'bronze',
    ];

    public function actionMyRankInfo()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $result = Olympic::getUserRankInfo($this->userId);
        $date = date('Ymd');
        $result['dialog_reward'] = $date > '20160822' ? '1' : '0';
        return $result;
    }

    /** 积分榜
     * @return array
     */
    public function actionRankList()
    {
        $request = \Yii::$app->request;
        $num = $request->get('num', 3);
        $num = $num > 100 ? 100 : $num;
        $result = Olympic::getRankList($num);
        return $result;
    }

    /** 某天奖牌榜单
     * @return array
     */
    public function actionMedalRankList()
    {
        $start = Olympic::$timeRand['start'];
        $end = date('Y-m-d');
        $result = Olympic::getMedalRankList($start, $end);
        return $result;
    }

    /** 赛程列表
     * @param $start
     * @param $end
     */
    public function actionScheduleList()
    {
        $start = date('Ymd');
        if ($start > Olympic::$timeRand['end']) {
            $start = Olympic::$timeRand['end'];
        }
        $end = date('Ymd', strtotime('+ 3 days', strtotime($start)));
        $result = Olympic::getScheduleList($start, $end);

        return $result;
    }

    /** 最新奖牌用户
     * @return array
     */
    public function actionNewestMedalUsers()
    {
        $request = \Yii::$app->request;
        $num = $request->get('num', 3);
        $result = Olympic::getNewestMedalUsers($num);
        return $result;
    }

    /** 用户奖牌信息
     * @return array|null|\yii\db\ActiveRecord
     */
    public function actionUserMedalInfo()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $result = Olympic::getUserMedalInfo($this->userId);

        return $result;
    }

    /** 用户参与记录
     * @return array
     */
    public function actionUserBuyList()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 20);
        $result = Olympic::getUserBuyList($this->userId, $page, $perpage);

        return $result;
    }

    /** 用户获奖记录
     * @return array
     */
    public function actionUserOrderList()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 20);
        $result = Olympic::getUserOrderList($this->userId, $page, $perpage);

        return $result;
    }

    public function actionUserRewardList()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 20);
        $result = Olympic::getUserRecieveList($this->userId, $page, $perpage);

        return $result;
    }

    /**获取可领取的伙购币信息
     * @return array
     */
    public function actionCanRecieveHgbInfo()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $result = Olympic::getCanRecieveHgbInfo($this->userId);

        return $result;
    }

    /** 兑换伙购币
     * @return array
     */
    public function actionRecieveHgb()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $gold = $request->get('gold', 1);
        $silver = $request->get('silver', 1);
        $bronze = $request->get('bronze', 1);

        $redis = new MyRedis();
        $key = 'OLYMPIC_REVIEVE_HGB_LIMIT_REQUEST_' . $this->userId;

        $num = $redis->incr($key);
        if ($num == 1) {
            $redis->expire($key, 3);
        } else {
            return ['code' => 202, 'msg' => '请求频繁,稍后再试'];
        }

        $source = $this->tokenSource == '__ios__' ? 3 : 4;
        $result = Olympic::recieveHgb($this->userId, $source, $gold, $silver, $bronze);
        $redis->del($key);
        return $result;
    }

    /** 获取可领取的红包信息
     * @return array
     */
    public function actionCanRecieveRedInfo()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $result = Olympic::getCanRecieveRedInfo($this->userId);

        return $result;
    }

    /** 领取红包
     * @return array
     */
    public function actionRecieveRed()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $redPrice = $request->get('red');

        if (!isset(Olympic::$redPackets[$redPrice])) {
            return ['code' => 203, 'msg' => '红包不存在'];
        }
        $packetId = Olympic::$redPackets[$redPrice];

        $redis = new MyRedis();
        $key = 'OLYMPIC_REVIEVE_REG_LIMIT_REQUEST_' . $this->userId;
        $num = $redis->incr($key);
        if ($num == 1) {
            $redis->expire($key, 3);
        } else {
            return ['code' => 202, 'msg' => '请求频繁,稍后再试'];
        }

        $result = Olympic::recieveRed($this->userId, $packetId);
        $redis->del($key);
        return $result;
    }

    /**
     * @return array
     */
    public function actionShare()
    {
        if (!$this->userId) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $type = $request->get('type');

        $redis = new MyRedis();
        $key = 'OLYMPIC_SHARE_LIMIT_REQUEST_' . $this->userId;
        $num = $redis->incr($key);
        if ($num == 1) {
            $redis->expire($key, 3);
        } else {
            return ['code' => 202, 'msg' => '请求频繁,稍后再试'];
        }

        $result = Olympic::share($this->userId, $type);
        $redis->del($key);
        return $result;
    }

    /**
     * 当天奖牌领取信息
     */
    public function actionTodayMedalInfo()
    {
        $medalInfo = [];
        $productIds = array_values(Olympic::$medalProducts);
        $totalMedal = OlympicSchedule::find()->where(['date' => date('Ymd')])->count();
        $totalMedal *= 3;

        $start = strtotime(date('Y-m-d'));
        $end = $start + 3600*24;
        $query = Order::find()->where(['product_id' => $productIds])->andWhere(['>=', 'create_time', $start])->andWhere(['<', 'create_time', $end]);
        $alreadyMedal = $query->count();
        $left = $totalMedal - $alreadyMedal;
        $medalInfo['today']['total'] = $totalMedal;
        $medalInfo['today']['left'] = $left > 0 ? $left : 0;

        $tomorrowMedal = OlympicSchedule::find()->where(['date' => date('Ymd', strtotime('+1 day'))])->count();
        $medalInfo['tomorrow'] = $tomorrowMedal * 3;

        return ['medalInfo' => $medalInfo];
    }

    /**
     * 用户消费信息
     */
    public function actionMarqueeInfo()
    {
        $productIds = array_values(Olympic::$medalProducts);
        $productMedals = array_flip(Olympic::$medalProducts);

        $productIdsStr = implode(',', $productIds);
        $sql = 'select * from (';
        for($i=100;$i<110;$i++) {
            $tableName = 'payment_order_items_' . $i;
            if ($i!=109) {
                $unionAllStr = 'union all';
            } else {
                $unionAllStr = '';
            }
            $sql .= '(select product_id,nums,user_id from ' . $tableName . ' where product_id in ('.$productIdsStr.') order by id limit 10) ' . $unionAllStr;

        }
        $sql .= ') as a';

        $db = \Yii::$app->db;
        $list = $db->createCommand($sql)->queryAll();
        foreach($list as &$one) {
            $medal = $productMedals[$one['product_id']];
            $one['medal'] = Olympic::$medalsText[$medal];

            $userInfo = User::baseInfo($one['user_id']);
            $one['nickname'] = $userInfo['username'];
        }


        return ['buyList' => $list];
    }


}