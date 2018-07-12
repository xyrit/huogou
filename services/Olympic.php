<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/27
 * Time: 上午11:55
 */
namespace app\services;

use app\helpers\DateFormat;
use app\models\Image;
use app\models\OlympicRank;
use app\models\OlympicRewardLog;
use app\models\OlympicSchedule;
use app\models\OlympicShareLog;
use app\models\Order;
use app\models\Packet;
use app\models\Period as PeriodModel;
use app\models\User as UserModel;
use app\models\UserBuylistDistribution;
use yii\data\Pagination;
use yii\db\Expression;

class Olympic
{

//    public static $medalProducts = [
//        'gold' => '153',
//        'silver' => '154',
//        'bronze' => '155',
//    ];

    public static $medalProducts = [
        'gold' => '237',
        'silver' => '238',
        'bronze' => '239',
    ];

    public static $medalsText = [
        'gold' => '金牌',
        'silver' => '银牌',
        'bronze' => '铜牌',
    ];

    public static $medalsScore = [
        'gold' => 100,
        'silver' => 50,
        'bronze' => 10,
    ];

//    public static $redPackets = [
//        1 => 45,
//        5 => 46,
//        10 => 47,
//        25 => 48,
//        50 => 49,
//    ];

    public static $redPackets = [
        1 => 59,
        5 => 60,
        10 => 61,
        25 => 62,
        50 => 63,
    ];

    public static $timeRand = [
        'start' => '20160806',
        'end' => '20160822',
    ];

    /** 用户参与记录
     * @param $uid
     */
    public static function getUserBuyList($uid, $page, $perpage)
    {

        $productIds = array_values(static::$medalProducts);
        $productMedals = array_flip(static::$medalProducts);
        $user = UserModel::find()->select('id,home_id')->where(['id' => $uid])->asArray()->one();
        $homeId = $user['home_id'];
        $query = UserBuylistDistribution::findByUserHomeId($homeId)->where(['user_id' => $uid]);
        $query->andWhere(['product_id' => $productIds]);
        $query->andWhere(['>', 'buy_num', 0]);
        $order = 'id DESC';
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $result = $query->orderBy($order)->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $buylist = [];
        foreach ($result as $one) {
            $productId = $one['product_id'];
            $periodId = $one['period_id'];
            $buyNum = $one['buy_num'];
            $curPeriodInfo = Product::curPeriod($periodId);
            $info = [];
            if ($curPeriodInfo) {
                $info['status'] = 0;
            } else {
                $info = Period::info($periodId);
                if (!$info) {
                    continue;
                }
            }
            $info['user_buy_num'] = $buyNum;
            $info['user_buy_time'] = DateFormat::microDate($one['buy_time']);
            $info['product_id'] = $productId;
            $info['period_id'] = $periodId;
            if (isset($info['uid']) && $info['uid'] == $uid) {
                $medal = $productMedals[$productId];
                $info['medalText'] =  static::$medalsText[$medal];
            } else {
                $info['medalText'] = '';
            }
            $buylist[] = $info;
        }

        $return['list'] = $buylist;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 用户获奖记录
     * @param $uid
     */
    public static function getUserOrderList($uid, $page, $perpage)
    {
        $productIds = array_values(static::$medalProducts);
        $user = UserModel::find()->select('id,home_id')->where(['id' => $uid])->asArray()->one();
        $query = Order::find()->where(['user_id' => $uid, 'product_id' => $productIds]);
        $orderBy = 'id desc';
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $orders = $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $productMedals = array_flip(static::$medalProducts);
        $list = [];
        foreach ($orders as $order) {
            $info = [];

            $userBuy = UserBuylistDistribution::findByUserHomeId($user['home_id'])->select('buy_num')->where(['user_id' => $uid, 'period_id' => $order['period_id']])->asArray()->one();
            $medal = $productMedals[$order['product_id']];
            $info['medal'] = static::$medalsText[$medal];
            $info['buy_num'] = $userBuy['buy_num'];
            $info['time'] = date('Y-m-d H:i:s', $order['create_time']);
            $info['period_id'] = $order['period_id'];
            $list[] = $info;
        }
        $result = [];
        $result['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();

        return $result;
    }


    const REWARD_RED = 1;
    const REWARD_GOLD = 2;
    const REWARD_SILVER = 3;
    const REWARD_BRONZE = 4;

    /** 用户领奖记录
     * @param $uid
     */
    public static function getUserRecieveList($uid, $page, $perpage)
    {
        $query = OlympicRewardLog::find()->where(['user_id' => $uid]);
        $orderBy = 'id desc';
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $logs = $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $list = [];
        foreach ($logs as $log) {
            $rewardName = '';
            if ($log['reward_obj'] == static::REWARD_RED) {
                $packetInfo = Packet::getInfo($log['obj_id']);
                $rewardName = $packetInfo['name'];
            } elseif ($log['reward_obj'] == static::REWARD_GOLD) {
                $rewardName = $log['price'] . '伙购币';
            } elseif ($log['reward_obj'] == static::REWARD_SILVER) {
                $rewardName = $log['price'] . '伙购币';
            } elseif ($log['reward_obj'] == static::REWARD_BRONZE) {
                $rewardName = $log['price'] . '伙购币';
            }

            $info = [];
            $info['reward_name'] = $rewardName;
            $info['time'] = date('Y-m-d H:i:s', $log['created_at']);
            $list[] = $info;
        }

        $result = [
            'list' => $list
        ];
        return $result;
    }

    /** 赛程列表
     * @param $start
     * @param $end
     * @return array
     */
    public static function getScheduleList($start, $end)
    {
        $query = OlympicSchedule::find()->where(['>=', 'date', $start]);
        $query->andWhere(['<=', 'date', $end]);
        $schedules = $query->orderBy('created_at asc,date asc')->asArray()->all();

        $list = [];
        foreach($schedules as $one) {
            $info = $one;
            $info['date_time'] = date('Y-m-d', strtotime($info['date']));
            $info['goldProduct'] = static::$medalProducts['gold'];
            $info['silverProduct'] = static::$medalProducts['silver'];
            $info['bronzeProduct'] = static::$medalProducts['bronze'];
            $list[$info['date_time']][] = $info;
        }

        $goldProductId = static::$medalProducts['gold'];
        $silverProductId = static::$medalProducts['silver'];
        $bronzeProductId = static::$medalProducts['bronze'];

        $saleList = [];
        $soldOutList = [];
        foreach($list as $date => $itemList) {
            $dayStart = strtotime($date);
            $dayEnd = $dayStart + 3600*24;

            if ($dayStart == strtotime(date('Y-m-d'))) {
                $goldNumToday = PeriodModel::find()->where(['product_id' => $goldProductId])->andWhere(['>=', 'start_time', $dayStart])->andWhere(['<', 'start_time', $dayEnd])->count();
                $silverNumToday = PeriodModel::find()->where(['product_id' => $silverProductId])->andWhere(['>=', 'start_time', $dayStart])->andWhere(['<', 'start_time', $dayEnd])->count();
                $bronzeNumToday = PeriodModel::find()->where(['product_id' => $bronzeProductId])->andWhere(['>=', 'start_time', $dayStart])->andWhere(['<', 'start_time', $dayEnd])->count();
            } else {
                $goldNumToday = 0;
                $silverNumToday = 0;
                $bronzeNumToday = 0;
            }

            $alNum = 0;
            foreach($itemList as $key => $item) {
                $alNum ++;

                $tempItem = $item;
                if ($goldNumToday > 0 && $goldNumToday >= $alNum) {
                    $tempItem['goldStatus'] = 1;
                } else {
                    $tempItem['goldStatus'] = 0;
                }

                if ($silverNumToday > 0 && $silverNumToday >= $alNum) {
                    $tempItem['silverStatus'] = 1;
                } else {
                    $tempItem['silverStatus'] = 0;
                }

                if ($bronzeNumToday > 0 && $bronzeNumToday >= $alNum) {
                    $tempItem['bronzeStatus'] = 1;
                } else {
                    $tempItem['bronzeStatus'] = 0;
                }

                if ($tempItem['goldStatus'] && $tempItem['silverStatus'] && $tempItem['bronzeStatus']) {
                    $soldOutList[$date][] = $tempItem;
                } else {
                    $saleList[$date][] = $tempItem;
                }
            }
        }

        $finalList = [];

        foreach($list as $date => $item) {
            $curDateSaleList = isset($saleList[$date]) ? $saleList[$date] : [];
            $curDateSoldOutList = isset($soldOutList[$date]) ? $soldOutList[$date] : [];
            $finalList[$date] = array_merge($curDateSaleList, $curDateSoldOutList);
        }

        $productProcessList = [];
        foreach(static::$medalProducts as $medal => $productId) {
            $peirodInfo = Product::curPeriodInfo($productId);
            $progress = floatval($peirodInfo['progress']/100000)*100;
            $progress = ceil($progress / 10) * 10;
            $productProcessList[$medal] = $progress;
        }
        $result = [
            'list' => $finalList,
            'process_list' => $productProcessList,
        ];
        return $result;
    }

    /** 最新奖牌用户
     * @param $num
     * @return array
     */
    public static function getNewestMedalUsers($num)
    {
        $productIds = array_values(static::$medalProducts);
        $productMedals = array_flip(static::$medalProducts);
        $orders = Order::find()->where(['product_id' => $productIds])->orderBy('id desc')->limit($num)->asArray()->all();

        $list = [];
        foreach($orders as $order) {
            $userInfo = \app\services\User::baseInfo($order['user_id']);
            $medal = $productMedals[$order['product_id']];
            $list[] = [
                'user_id' => $order['user_id'],
                'user_home_id' => $userInfo['home_id'],
                'nickname' => $userInfo['username'],
                'avatar' => Image::getUserFaceUrl($userInfo['avatar'], 80),
                'time' => date('Y-m-d H:i:s'),
                'medal' => static::$medalsText[$medal],
            ];
        }
        $result = [
            'list' => $list
        ];
        return $result;
    }

    /** 用户奖牌信息
     * @param $uid
     */
    public static function getUserMedalInfo($uid)
    {
        $info = OlympicRank::find()->where(['user_id' => $uid])->asArray()->one();

        $userInfo = \app\services\User::baseInfo($uid);
        $info['nickname'] = $userInfo['username'];
        $info['avatar'] = $userInfo['avatar'];
        $info['level'] = $userInfo['level'];

        return $info;
    }

    /** 用户排名信息
     * @param $uid
     */
    public static function getUserRankInfo($uid)
    {
        $db = \Yii::$app->db;

        $tableName = OlympicRank::tableName();
        $sql = "select * from (select (@rowno:=@rowno+1) as rowno, a.* from " . $tableName . " as a,(select @rowno:=0) t  order by a.score desc) as d where d.user_id = '{$uid}'";

        $info = $db->createCommand($sql)->queryOne();
        return $info;
    }

    /**
     * @param $uid
     * @param $productId
     */
    public static function addUserRank($uid, $productId)
    {
        $date = date('Ymd');
        if ($date < static::$timeRand['start'] || $date > static::$timeRand['end']) {
            return false;
        }
        $productMedals = array_flip(static::$medalProducts);
        if (!isset($productMedals[$productId]) ) {
            return false;
        }
        $curMedal = $productMedals[$productId];
        $rank = OlympicRank::find()->where(['user_id' => $uid])->one();
        if (!$rank) {
            $rank = new OlympicRank();
            foreach($productMedals as $medal) {
                if ($medal == $curMedal) {
                    $rank->$medal = 1;
                    $rank->score = static::$medalsScore[$medal];
                } else {
                    $rank->$medal = 0;
                }
            }
            $rank->user_id = $uid;
            $rank->created_at = time();
            $save = $rank->save(false);
        } else {
            $rank->$curMedal += 1;
            $rank->score += static::$medalsScore[$curMedal];
            $save = $rank->save(false);
        }
        return $save;
    }

    /** 积分排行榜
     * @param $limit
     */
    public static function getRankList($limit)
    {
        $list = OlympicRank::find()->orderBy('score desc')->limit($limit)->asArray()->all();

        foreach($list as &$one) {
            $userInfo = \app\services\User::baseInfo($one['user_id']);
            $one['nickname'] = $userInfo['username'];
            $one['avatar'] = $userInfo['avatar'];
            $one['home_id'] = $userInfo['home_id'];
        }
        $result = [
            'list' => $list,
        ];

        return $result;
    }

    /** 某几天奖牌榜
     * @param $date
     */
    public static function getMedalRankList($start, $end)
    {
        $start = strtotime($start);
        $end = strtotime($end) + 3600*24;
        $productIds = array_values(static::$medalProducts);
        $productMedals = array_flip(static::$medalProducts);

        $productIdsStr = implode(',', $productIds);
        $db = \Yii::$app->db;
        $sql = "select user_id,product_id,create_time,count(id) as cnt,from_unixtime(`create_time`,'%Y%m%d') as date_time from orders where product_id in (".$productIdsStr.") and create_time >= :start and create_time <= :end group by date_time,product_id,user_id order by cnt desc,create_time asc;";
        $rankList = $db->createCommand($sql, [':start' => $start, ':end'=>$end])->queryAll();
        $listByDate = [];
        foreach ($rankList as $rank) {
            $userInfo = \app\services\User::baseInfo($rank['user_id']);
            $medal = $productMedals[$rank['product_id']];
            $one = [
                'user_id' => $rank['user_id'],
                'home_id' => $userInfo['home_id'],
                'nickname' => $userInfo['username'],
                'time' => date('Y-m-d H:i:s', $rank['create_time']),
                'date_time' => $rank['date_time'],
                'date' => date('Y-m-d', strtotime($rank['date_time'])),
                'medal' =>  static::$medalsText[$medal],
                'num' => $rank['cnt'],
            ];

            $listByDate[$one['date']][] = $one;
        }
        $list = [];
        foreach($listByDate as $date => $item) {
            foreach($item as $it) {
                $one = $it;
                $list[$date][$one['medal']][] = $one;
            }
        }
        ksort($list);
        $finalList = [];

        foreach($list as $date => $oneList) {
            $finalList[$date] = [
                '金牌' => !empty($oneList['金牌']) ? $oneList['金牌'] : [],
                '银牌' => !empty($oneList['银牌']) ? $oneList['银牌'] : [],
                '铜牌' => !empty($oneList['铜牌']) ? $oneList['铜牌'] : [],
            ];
        }

        $result = [
            'list' => $finalList,
        ];

        return $result;
    }

    /** 获取可领取的伙购币信息
     * @param $uid
     */
    public static function getCanRecieveHgbInfo($uid)
    {
        $medalInfo = OlympicRank::find()->where(['user_id' => $uid])->asArray()->one();
        if (!$medalInfo) {
            $canRecieveInfo = [
                'gold' => 0,
                'silver' => 0,
                'bronze' => 0,
            ];
            $result = [
                'total' => [
                    'gold' => 0,
                    'silver' => 0,
                    'bronze' => 0,
                ],
                'left' => $canRecieveInfo
            ];
            return $result;
        }
        $alreadyGoldRecieve = OlympicRewardLog::find()->select('sum(obj_id) as total_num')->where(['user_id' => $uid, 'reward_obj' => static::REWARD_GOLD])->asArray()->one();
        $alreadySilverRecieve = OlympicRewardLog::find()->select('sum(obj_id) as total_num')->where(['user_id' => $uid, 'reward_obj' => static::REWARD_SILVER])->asArray()->one();
        $alreadyBronzeRecieve = OlympicRewardLog::find()->select('sum(obj_id) as total_num')->where(['user_id' => $uid, 'reward_obj' => static::REWARD_BRONZE])->asArray()->one();

        $alreadyGoldRecieve = $alreadyGoldRecieve ? $alreadyGoldRecieve['total_num'] : 0;
        $alreadySilverRecieve = $alreadySilverRecieve ? $alreadySilverRecieve['total_num'] : 0;
        $alreadyBronzeRecieve = $alreadyBronzeRecieve ? $alreadyBronzeRecieve['total_num'] : 0;

        $goldNum = $medalInfo['gold'];
        $silverNum = $medalInfo['silver'];
        $bronzeNum = $medalInfo['bronze'];

        $canRecieveInfo = [
            'gold' => $goldNum - $alreadyGoldRecieve,
            'silver' => $silverNum - $alreadySilverRecieve,
            'bronze' => $bronzeNum - $alreadyBronzeRecieve,
        ];

        $result = [
            'total' => [
                'gold' => $goldNum,
                'silver' => $silverNum,
                'bronze' => $bronzeNum,
            ],
            'left' => $canRecieveInfo
        ];
        return $result;
    }

    /** 领取伙购币奖励
     * @param $uid
     * @param $source
     * @param $goldNum
     * @param $silverNum
     * @param $bronzeNum
     */
    public static function recieveHgb($uid, $source, $goldNum, $silverNum, $bronzeNum)
    {
        $canRecieveHgb = static::getCanRecieveHgbInfo($uid);
        $canRecieveHgb = $canRecieveHgb['left'];
        $medalRecieveInfo = [
            'gold' => [
                'reward' => static::REWARD_GOLD,
                'num' => $goldNum,
                'price' => $goldNum * 66,
            ],
            'silver' => [
                'reward' => static::REWARD_SILVER,
                'num' => $silverNum,
                'price' => $silverNum * 33,
            ],
            'bronze' => [
                'reward' => static::REWARD_BRONZE,
                'num' => $bronzeNum,
                'price' => $bronzeNum * 6,
            ],
        ];

        $recieveMoney = 0;
        foreach ($medalRecieveInfo as $medal => $info) {
            if ($canRecieveHgb[$medal] < $info['num']) {
                return ['code' => 301, 'msg' => '超出可兑换数量'];
                break;
            }
            if ($info['num'] > 0) {
                $log = new OlympicRewardLog();
                $log->reward_obj = $info['reward'];
                $log->obj_id = $info['num'];
                $log->user_id = $uid;
                $log->price = $info['price'];
                $log->created_at = time();
                $log->save(false);
                $recieveMoney += $info['price'];
            }
        }

        if ($recieveMoney) {
            $member = new Member(['id' => $uid]);
            $member->editMoney($recieveMoney, 3, '奥运会活动领取' . $recieveMoney . '伙购币', $source);
        } else {
            return ['code' => '101', 'msg' => '没有可兑换的奖牌'];
        }

        return ['code' => '100', 'msg' => '兑换成功'];

    }

    /** 获取可领取红包信息
     * @param $uid
     */
    public static function getCanRecieveRedInfo($uid)
    {
        $productIds = array_values(static::$medalProducts);
        $periodIds = Order::find()->select('period_id')->where(['product_id' => $productIds])->andWhere(['<>', 'user_id', $uid])->asArray()->indexBy('period_id')->all();
        $periodIds = array_keys($periodIds);
        $user = UserModel::find()->select('id,home_id')->where(['id' => $uid])->asArray()->one();
        $query = UserBuylistDistribution::findByUserHomeId($user['home_id'])->select('sum(buy_num) total_sum')->where(['user_id' => $uid]);
        $query->andWhere(['period_id' => $periodIds]);
        $userBuy = $query->asArray()->one();
        $totalSum = $userBuy ? $userBuy['total_sum'] : 0;

        $aleadyRecieveRed = OlympicRewardLog::find()->select('sum(price) as total_price')->where(['user_id' => $uid, 'reward_obj' => static::REWARD_RED])->asArray()->one();
        $aleadyRecieveRed = $aleadyRecieveRed ? $aleadyRecieveRed['total_price'] : 0;
        $canRecieve = $totalSum - $aleadyRecieveRed;
        $canRecieve = $canRecieve > 0 ? $canRecieve : 0;
        return [
            'total' => (int)$totalSum,
            'left' => (int)$canRecieve,
        ];
    }

    /** 领取红包
     * @param $uid
     * @param $packetId
     */
    public static function recieveRed($uid, $packetId)
    {
        $redPricePacket = array_flip(static::$redPackets);
        $redPrice = $redPricePacket[$packetId];

        $canRecieve = static::getCanRecieveRedInfo($uid);
        $canRecieve = $canRecieve['left'];
        if ($canRecieve - $redPrice < 0) {
            return ['code' => 306, 'msg' => '可兑换金额不足'];
        }
        $rs = Coupon::receivePacket($packetId, $uid, 'olympic');
        if ($rs['code'] == '0') {
            $pid = $rs['data']['pid'];
            $info = Coupon::openPacket($pid, $uid);
            $result['code'] = $info['code'];
            if ($info['code'] == '0') {

                $log = new OlympicRewardLog();
                $log->reward_obj = static::REWARD_RED;
                $log->obj_id = $packetId;
                $log->user_id = $uid;
                $log->price = $redPrice;
                $log->created_at = time();
                $log->save(false);

                $result['msg'] = '领取成功';
            } else {
                $result['msg'] = $info['msg'];
            }
        } else {
            $result['code'] = $rs['code'];
            $result['msg'] = $rs['msg'];
        }
        return $result;
    }

    /** 用户分享
     * @param $uid
     */
    public static function share($uid, $type)
    {
        $typeScore = [
            1 => 1,
            2 => 2,
            3 => 2,
        ];

        if (!isset($typeScore[$type])) {
            return ['code' => 103, 'msg' => '分享类型不正确'];
        }
        $today = strtotime(date('Y-m-d'));
        $tomorrow = $today + 3600*24;
        if (in_array($type, [2,3])) {
            $logNum = OlympicShareLog::find()->where(['user_id' => $uid, 'type' => [2,3]])
                ->andWhere(['>=', 'created_at', $today])
                ->andWhere(['<', 'created_at', $tomorrow])
                ->count();

            $rewardNum = OlympicRewardLog::find()->where(['user_id' => $uid, 'reward_obj' => [2,3,4]])
                ->andWhere(['>=', 'created_at', $today])
                ->andWhere(['<', 'created_at', $tomorrow])
                ->count();

        } else {
            $logNum = OlympicShareLog::find()->where(['user_id' => $uid, 'type' => 1])
                ->andWhere(['>=', 'created_at', $today])
                ->andWhere(['<', 'created_at', $tomorrow])
                ->count();
            $rewardNum = 1;
        }
        if ($logNum >= $rewardNum) {
            return ['code' => 101, 'msg' => '超过分享上限'];
        }

        $rank = OlympicRank::find()->where(['user_id' => $uid])->one();
        $productMedals = array_flip(static::$medalProducts);
        if ($rank) {
            OlympicRank::updateAll(['score' => new Expression('score + :score', [':score' => $typeScore[$type]])], ['user_id' => $uid]);
        } else {
            $rank = new OlympicRank();
            foreach($productMedals as $medal) {
                $rank->$medal = 0;
            }
            $rank->score = $typeScore[$type];
            $rank->user_id = $uid;
            $rank->created_at = time();
            $rank->save(false);
        }

        $log = new OlympicShareLog();
        $log->type = $type;
        $log->user_id = $uid;
        $log->created_at = time();

        $save = $log->save(false);

        return $save ? ['code' => 100, 'msg' => '分享成功'] : ['code' => 102, 'msg' => '分享失败'];
    }


}