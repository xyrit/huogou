<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/6/29
 * Time: 下午3:34
 */
namespace app\services;

use app\helpers\MyRedis;
use app\models\EuroCupOrder;
use app\models\EuroRewardLog;
use app\models\EuroUserbuylist;
use app\models\Image;
use app\models\User;
use yii\helpers\Json;

class EuroCup
{

    public static $teams = [
        'portugal' => [
            'name' => '葡萄牙',
            'img' => 'teamimg_portugal.png',
        ],
        'wales' => [
            'name' => '威尔士',
            'img' => 'teamimg_wales.png',
        ],
        'germany' => [
            'name' => '德国',
            'img' => 'teamimg_germany.png',
        ],
        'french' => [
            'name' => '法国',
            'img' => 'teamimg_french.png',
        ],
        'eq' => [
            'name' => '平局',
            'img' => '',
        ],
    ];

    public static $games = [
        '20160707' => [
            'desc' => '半决赛',
            'list' => [
                'portugal' => 1.96,
                'eq' => 2.91,
                'wales' => 3.50,
            ],
        ],
        '20160708' => [
            'desc' => '半决赛',
            'list' => [
                'germany' => 2.45,
                'eq' => 2.75,
                'french' => 2.72,
            ]
        ],
        '20160711' => [
            'desc' => '决赛',
            'list' => [
                'portugal' => 1.8,
                'eq' => 3.5,
                'french' => 1.7,
            ]
        ],
    ];

    public static $gameResult = [
        '20160707' => [
            'portugal' => 2,
            'wales' => 0,
        ],
        '20160708' => [
            'germany' => 0,
            'french' => 2,
        ],
        '20160711' => [
            'portugal' => 0,
            'french' => 0,
        ],
    ];

    public static $redPacket = [
        '100' =>  [
            'id' => 53,
            'price' => 100,
            'num' => 1,
            'img' => 'red4.png',
        ],
        '50' =>  [
            'id' => 52,
            'price' => 50,
            'num' => 1,
            'img' => 'red3.png',
        ],
        '10' =>  [
            'id' => 51,
            'price' => 10,
            'num' => 1,
            'img' => 'red2.png',
        ],
        '5' =>  [
            'id' => 50,
            'price' => 5,
            'num' => 1,
            'img' => 'red1.png',
        ],
        '1' => [
            'id' => 49,
            'price' => 1,
            'num' => 1,
            'img' => 'red0.png',
        ],
    ];

    const GAME_BEGIN_HOUR_TIME = '0300';

    const EURO_CUP_PAY_LIMIT_KEY = 'EURO_CUP_PAY_LIMIT_KEY_';
    const EURO_CUP_PAY_RESULT_KEY = 'EURO_CUP_PAY_RESULT_KEY_';

    /**
     * 欧洲杯竞猜购买
     * @param $gameDate
     * @param $team
     * @param $money
     * @param $userId
     * @return array
     */
    public static function pay($gameDate, $team, $money, $userId, $source)
    {
        $redis = new MyRedis();

        $userBuyresultKey = static::EURO_CUP_PAY_RESULT_KEY . $gameDate . '_' . $userId;
        $userBuyresultDuration = 600;
        $redis->del($userBuyresultKey);

        $game = static::$games[$gameDate]['list'];
        if (!isset($game[$team])) {
            $return = ['code' => '201', 'msg' => '未知球队'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }
        if ($money <= 0) {
            $return = ['code' => '204', 'msg' => '错误的竞猜金额'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }
        $payLimitKey = static::EURO_CUP_PAY_LIMIT_KEY . $gameDate . '_' . $userId;
        $payRequestNum = $redis->incr($payLimitKey);
        $redis->expire($payLimitKey, 3);
        if ($payRequestNum > 1) {
            $return = ['code' => '202', 'msg' => '请求频率过高'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        if (date('YmdHi')>=$gameDate.static::GAME_BEGIN_HOUR_TIME) {
            $return = ['code' => '303', 'msg' => '已经不能竞猜,比赛已经开始'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        $user = User::find()->select('money')->where(['id' => $userId])->asArray()->one();
        if (!$user) {
            $return = ['code' => '301', 'msg' => '用户不存在'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }
        $userMoney = $user['money'];
        $money = ceil($money);
        $userAfterMoney = $userMoney - $money;
        if ($userAfterMoney < 0) {
            $return = ['code' => '203', 'msg' => '您好,您的余额不足<br>可前往个人中心充值'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        $payOrder = new EuroCupOrder();
        $payOrder->user_id = $userId;
        $payOrder->money = $money;
        $payOrder->status = 0;
        $payOrder->game_date = $gameDate;
        $payOrder->pay_at = 0;
        $payOrder->team = $team;
        $payOrder->created_at = time();
        $savePayOrder = $payOrder->save();

        if (!$savePayOrder) {
            $return = ['code' => '204', 'msg' => '订单创建失败'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }
//        $updateMoney = User::updateAll(['money' => $userAfterMoney], ['id' => $userId]);
        $member = new Member(['id' => $userId]);
        $updateMoney = $member->editMoney(-1 * $money, 6, '欧洲杯竞猜', $source);
        if (!$updateMoney) {
            $return = ['code' => '205', 'msg' => '扣款失败'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }
        $payOrderId = $payOrder->id;
        $successOrder = EuroCupOrder::updateAll(['status' => 1, 'pay_at' => time()], ['id' => $payOrderId]);
        if ($successOrder) {
            $buylist = EuroUserbuylist::find()->where(['game_date' => $gameDate, 'team' => $team, 'user_id' => $userId])->one();
            if ($buylist) {
                $buylist->buy_num += $money;
            } else {
                $buylist = new EuroUserbuylist();
                $buylist->buy_num = $money;
                $buylist->user_id = $userId;
                $buylist->game_date = $gameDate;
                $buylist->team = $team;
                $buylist->buy_time = time();
            }
            $buylist->save(false);
            $return = ['code' => '100', 'msg' => '购买成功'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        $return = ['code' => '302', 'msg' => '购买失败'];
        $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
        return $return;

    }

    /** 欧洲杯购买结果
     * @param $orderId
     * @param $userId
     * @return array|mixed|string
     */
    public static function payResult($gameDate, $userId)
    {
        $redis = new MyRedis();
        $userBuyresultKey = static::EURO_CUP_PAY_RESULT_KEY . $gameDate . '_' . $userId;

        if ($result = $redis->get($userBuyresultKey)) {
            $result = Json::decode($result);
            return $result;
        } else {
            return [];
        }
    }

    /** 获取球队列表
     * @return array
     */
    public static function getGamesList()
    {
        $teams = static::$teams;
        $games = static::$games;

        $gameNum = 0;
        $totalGame = count($games);
        $gameDate = date('Ymd');
        foreach ($games as $date => $game) {
            if ($gameDate.date('Hi') >= $date.static::GAME_BEGIN_HOUR_TIME) {
                if ($gameNum != $totalGame - 1) {
                    unset($games[$date]);
                }
            }
            $gameNum++;
        }

        $gamesList = [];

        foreach ($games as $date => $game) {
            $gamesSuport = static::_teamSuport($date);
            $list = [];
            foreach($game['list'] as $team => $odds) {
                $list[] = [
                    'name' => $teams[$team]['name'],
                    'odds' => $odds,
                    'img' => $teams[$team]['img'],
                    'suport' => $gamesSuport[$team],
                    'team' => $team,
                ];
            }
            $gamesList[$date] = [
                'desc' => $games[$date]['desc'],
                'dateText' => $date == date('Ymd') ? '今天03:00' : date('Y-m-d 03:00', strtotime($date)),
                'teams' => $list,
            ];
        }

        return $gamesList;
    }

    /**
     * @param $limit
     */
    public static function getUserPayList($limit)
    {
        $orderList = EuroCupOrder::find()->where(['status' => 1])->orderBy('created_at desc')->limit($limit*2)->all();
        $list = [];
        foreach($orderList as $one) {
            if ($one['team'] == 'eq') {
                continue;
            }
            $userInfo = \app\services\User::baseInfo($one['user_id']);
            $list[] = [
                'nickname' => $userInfo['username'],
                'money' => $one['money'],
                'team' => static::$teams[$one['team']]['name'],
            ];
        }
        return $list;
    }

    /**
     *  完整球迷排行
     */
    public static function getOrderRank($limit = 10)
    {
        $rank = EuroUserbuylist::find()->select('user_id,team,sum(buy_num) total_buy_num')->orderBy('total_buy_num desc')->limit($limit * 2)->groupBy('user_id,team')->asArray()->all();
        $list = [];
        foreach ($rank as $one) {
            $userId = $one['user_id'];
            $team = $one['team'];
            $buyNum = $one['total_buy_num'];
            if ($team == 'eq') {
                continue;
            }
            $userBaseInfo = \app\services\User::baseInfo($userId);
            $list[] = [
                'user_avatar' => Image::getUserFaceUrl($userBaseInfo['avatar'], 80),
                'user_homeid' => $userBaseInfo['home_id'],
                'user_id' => $userBaseInfo['id'],
                'user_nickname' => $userBaseInfo['username'],
                'team_name' => static::$teams[$team]['name'],
                'team_surp' => $buyNum,
            ];
        }
        return $list;
    }

    /**
     * 某队球迷排行榜
     */
    public static function getOrderRankByTeam($team, $limit)
    {
        $rank = EuroUserbuylist::find()->select('user_id,team,sum(buy_num) total_buy_num')->where(['team' => $team])->orderBy('total_buy_num desc')->limit($limit * 2)->groupBy('user_id')->asArray()->all();
        $list = [];
        foreach ($rank as $one) {
            $userId = $one['user_id'];
            $team = $one['team'];
            $buyNum = $one['total_buy_num'];
            if ($team == 'eq') {
                continue;
            }
            $userBaseInfo = \app\services\User::baseInfo($userId);
            $list[] = [
                'user_avatar' => Image::getUserFaceUrl($userBaseInfo['avatar'], 80),
                'user_homeid' => $userBaseInfo['home_id'],
                'user_id' => $userBaseInfo['id'],
                'user_nickname' => $userBaseInfo['username'],
                'team_name' => static::$teams[$team]['name'],
                'team_surp' => $buyNum,
            ];
        }
        return $list;
    }


    /** 球队支持率
     * @param $gameDate
     * @return array
     */
    private static function _teamSuport($gameDate)
    {
        $teams = array_keys(static::$games[$gameDate]['list']);
        $teamsSuport = [];
        $total = 0;
        foreach($teams as $team) {
            $teamMoney = EuroCupOrder::find()->where(['game_date' => $gameDate, 'team' => $team, 'status' => 1])->sum('money');
            $total += $teamMoney;
            $teamsSuport[$team] = $teamMoney;
        }
        foreach($teamsSuport as $t=>$tMoney) {
            $teamsSuport[$t] = $total ? round(floatval($tMoney/$total) * 100,1) : 0;
        }

        return $teamsSuport;
    }

    /** 判断哪个球队胜利
     * @param $gameDate
     * @return string
     */
    private static function _whichTeamWin($gameDate)
    {
        if (!isset(static::$gameResult[$gameDate])) {
            return null;
        }
        $gameResult = static::$gameResult[$gameDate];
        $scores = array_values($gameResult);
        $teams = array_keys($gameResult);
        if ($scores[0] == $scores[1]) {
            return 'eq';
        } else if ($scores[0] > $scores[1]) {
            return $teams[0];
        } else {
            return $teams[1];
        }
    }

    /** 判断是否竞猜中
     * @param $gameDate
     * @param $guessTeam
     * @return bool
     */
    private static function _isGuessWin($gameDate, $guessTeam)
    {
        $team = static::_whichTeamWin($gameDate);
        return $team == $guessTeam ? true : false;
    }

    /** 我的竞猜
     * @param $userId
     */
    public static function getOrderListByUid($userId)
    {
        $list = EuroCupOrder::find()->where(['user_id' => $userId, 'status'=>1])->orderBy('pay_at desc')->all();
        $orderList = [];
        foreach ($list as $one) {
            $guessTeam = $one['team'];
            $gameDate = $one['game_date'];

            $descText = '';
            $payTime = date('Y-m-d H:i:s', $one['created_at']);
            $payMoney = $one['money'];

            if (isset(static::$gameResult[$gameDate])) {
                $gameResult = static::$gameResult[$gameDate];
                $teamNum = 0;
                foreach($gameResult as $team=>$score) {
                    if ($teamNum==0) {
                        $descText .= static::$teams[$team]['name'] . ' '.$score;
                    } else {
                        $descText .= ':' . $score . ' ' . static::$teams[$team]['name'];
                    }
                    $teamNum ++;
                }
                $resultText = static::_isGuessWin($gameDate, $guessTeam) ? '已猜中' : '未猜中';
            } else {
                $game = static::$games[$gameDate]['list'];
                unset($game['eq']);
                $teams = array_keys($game);
                foreach($teams as $team) {
                    $descText .= static::$teams[$team]['name'] . ' ';
                }
                $resultText = '未开奖';
            }

            $orderList[] = [
                'order_id' => $one['id'],
                'desc_text' => $descText,
                'result_text' => $resultText,
                'pay_time' => $payTime,
                'pay_money' => $payMoney,
            ];
        }
        return $orderList;
    }

    /** 竞猜信息
     * @param $orderId
     * @param $userId
     * @return array
     */
    public static function getOrderDetail($orderId, $userId)
    {
        $order = EuroCupOrder::find()->where(['id' => $orderId, 'user_id' => $userId, 'status'=>1])->one();
        if (!$order) {
            return ['code'=>'404', 'msg' => '竞猜信息不存在'];
        }
        $guessTeam = $order['team'];
        $date = $order['game_date'];
        $money = $order['money'];
        $payTime = $order['pay_at'];
        $game = static::$games[$date];
        $odds = $game['list'][$guessTeam];
        $teamName = $guessTeam == 'eq' ? '平局' : static::$teams[$guessTeam]['name'] . ' 胜 ';
        $winTeam = static::_whichTeamWin($date);
        $disableBtn = true;
        if ($winTeam == null) {
            $gameResult = '待揭晓';
            $rewardText = '等待揭晓结果';
            $alreadGet = false;
            $rewardBtnText = '等待揭晓结果';
            $rewardType = null;
        } else {
            $gameResult = $winTeam == 'eq' ? '平局' : static::$teams[$winTeam]['name'];
            if ($winTeam == $guessTeam) {
                $rewardText = '猜中, 领取奖励';
                $alreadGet = EuroRewardLog::find()->where(['user_id' => $userId, 'order_id'=>$orderId])->one();
                if ($alreadGet) {
                    $rewardBtnText = '已领取奖励';
                } else {
                    $rewardBtnText = '领取奖励';
                    $disableBtn = false;
                }
                $rewardType = 'reward';
            } else {
                $rewardText = '未猜中, 领取红包';
                $alreadGetPrice = EuroRewardLog::find()->where(['user_id' => $userId, 'order_id'=>$orderId])->sum('price');
                if ($money>$alreadGetPrice) {
                    $alreadGet = false;
                    $rewardBtnText = '领取红包';
                    $disableBtn = false;
                } else {
                    $alreadGet = true;
                    $rewardBtnText = '已领取红包';
                    $disableBtn = false;
                }
                $rewardType = 'red';
            }
        }
        $info = [
            'my_guess' => $teamName . ' ' . $odds,
            'guess_result' => $gameResult,
            'money' => $money,
            'guess_time' => date('Y-m-d H:i:s', $payTime),
            'reward_text' => $rewardText,
            'alreay_get' => $alreadGet ? true : false,
            'reward_btn_text' => $rewardBtnText,
            'disable_btn' => $disableBtn,
            'reward_type' => $rewardType
        ];
        $teams = [];
        foreach($game['list'] as $team => $tOdds) {
            $teamInfo = static::$teams[$team];
            if ($winTeam === null or $team == 'eq') {
                $teamInfo['score'] = '';
            } else {
                $teamInfo['score'] = static::$gameResult[$date][$team];
            }
            $teams[] = $teamInfo;
        }
        return ['info' => $info, 'teams' => $teams];
    }

    /** 我的红包
     * @param $userId
     */
    public static function getRedPacketListByUid($userId)
    {
        $query = EuroCupOrder::find()->where(['user_id' => $userId, 'status' => 1]);

        $dateArr = [];
        foreach (static::$gameResult as $date=>$result) {
            $dateArr[] = $date;
        }
        $query->andWhere(['game_date' => $dateArr]);
        $list = $query->all();

        $redPacketList = [];
        $redSum = 0;
        foreach($list as $one) {
            $date = $one['game_date'];
            $team = $one['team'];
            $money = $one['money'];
            $orderId = $one['id'];
            $winTeam = static::_whichTeamWin($date);
            if ($winTeam === null || $winTeam == $team) {
                continue;
            }
            $alreadyGetPrice = EuroRewardLog::find()->where(['user_id' => $userId, 'order_id' => $orderId])->sum('price');
            $getPrice = $money - $alreadyGetPrice;
            if ($getPrice <= 0 ) {
                continue;
            }
            $packetList = static::_getRedPacketList($getPrice);
            foreach($packetList as $packetInfo) {
                $redPacketList[] = [
                    'img' => $packetInfo['img'],
                    'order_id' => $orderId,
                    'packet_id' => $packetInfo['packet_id'],
                    'price' => $packetInfo['price'],
                ];
                $redSum += $packetInfo['price'];
            }


        }
        return ['list' => $redPacketList, 'sum' => $redSum];
    }

    /** 领取奖励
     * @param $orderId
     * @param $userId
     */
    public static function getReward($orderId, $userId, $source, $noGetRed = false)
    {
        $order = EuroCupOrder::findOne(['id' => $orderId, 'status' => 1]);
        $team = $order['team'];
        $gameDate = $order['game_date'];

        //订单不是获奖球队发红包
        $winTeam = static::_whichTeamWin($gameDate);
        $result = [];
        if ($winTeam === null) {
            $result['result'] =  '';
            $result['type'] = 'null';
            $result['msg'] = '结果还未揭晓哦';
        }else if ($winTeam != $team) {
            if ($noGetRed) {
                $result['result'] =  true;
                $result['type'] = 'red';
                $result['msg'] = '';
            } else {
                $getResult = static::_getRedPacket($order, $userId);
                $result['result'] =  $getResult['code'] == 100 ? true : false;
                $result['type'] = 'red';
                $result['msg'] = $getResult['msg'];
            }
        } else if ($winTeam == $team)  {
            $odds = static::$games[$gameDate]['list'][$team];
            $getResult = static::_getIdealMoney($order, $odds, $userId, $source);
            $result['result'] =  $getResult['code'] == 100 ? true : false;
            $result['type'] = 'money';
            $result['msg'] = $getResult['msg'];
        }
        return $result;

    }

    /** 根据订单和红包ID领取红包
     * @param $orderId
     * @param $packetId
     * @param $userId
     * @return array
     */
    public static function getRewardRed($orderId, $packetId, $userId)
    {
        $order = EuroCupOrder::findOne(['id' => $orderId, 'status' => 1]);
        $team = $order['team'];
        $gameDate = $order['game_date'];

        //订单不是获奖球队发红包
        $winTeam = static::_whichTeamWin($gameDate);
        $result = [];
        if ($winTeam === null) {
            $result['result'] =  '';
            $result['type'] = 'null';
            $result['msg'] = '结果还未揭晓哦';
        }else if ($winTeam != $team) {
            $redList = static::getRedPacketListByUid($userId);
            $canOpen = false;
            $packetPrice = 0;
            foreach($redList['list'] as $value) {
                if ($value['packet_id'] == $packetId) {
                    $packetPrice = $value['price'];
                    $canOpen = true;
                }
            }
            if (!$canOpen) {
                $result['msg'] = '红包不存在';
            } else {
                $rs = Coupon::receivePacket($packetId, $userId, 'eurocup');
                if ($rs['code'] == '0') {
                    $pid = $rs['data']['pid'];
                    $info = Coupon::openPacket($pid,$userId);
                    if ($info['code'] == '0') {
                        static::_addRewardLog($userId, $orderId, static::REWARD_RED, $packetId, $packetPrice);
                        $result['msg'] = '领取成功';
                    } else {
                        $result['msg'] = $info['msg'];
                    }
                } else {
                    $result['msg'] = $rs['msg'];
                }
            }

            $result['result'] =  true;
            $result['type'] = 'red';
        } else if ($winTeam == $team)  {
            $result['result'] =  false;
            $result['type'] = 'money';
            $result['msg'] = '领取错误';
        }
        return $result;
    }

    /** 根据红包ID,自动判断订单Id领取红包
     * @param $packetId
     * @param $userId
     * @return mixed
     */
    public static function getRewardRedByPacketId($packetId, $userId)
    {
        $redList = static::getRedPacketListByUid($userId);
        $canOpen = false;
        $packetPrice = 0;
        $orderId = 0;
        foreach($redList['list'] as $value) {
            if ($value['packet_id'] == $packetId) {
                $packetPrice = $value['price'];
                $orderId = $value['order_id'];
                $canOpen = true;
            }
        }
        if (!$canOpen) {
            $result['msg'] = '红包不存在';
        } else {
            $rs = Coupon::receivePacket($packetId, $userId, 'eurocup');
            if ($rs['code'] == '0') {
                $pid = $rs['data']['pid'];
                $info = Coupon::openPacket($pid,$userId);
                if ($info['code'] == '0') {
                    static::_addRewardLog($userId, $orderId, static::REWARD_RED, $packetId, $packetPrice);
                    $result['msg'] = '领取成功';
                } else {
                    $result['msg'] = $info['msg'];
                }
            } else {
                $result['msg'] = $rs['msg'];
            }
        }

        $result['result'] =  true;
        $result['type'] = 'red';
        return $result;
    }

    /** 根据金额获取发放红包列表
     * @param $money
     * @return mixed
     */
    private static function _getRedPacketList($money)
    {
        $list = [];
        $redMoney = $money;
        foreach (static::$redPacket as $key => $value) {
            if ($redMoney >= $key) {
                $num = floor($redMoney/$key);
                for($i=0;$i<$num;$i++) {
                    $list[] = [
                        'packet_id' => $value['id'],
                        'price' => $key,
                        'img' => $value['img'],
                    ];
                }
                $redMoney = $redMoney - $key * $num;
            }
        }
        return $list;
    }

    /** 发红包
     * @param $money
     * @param $userId
     */
    private static function _getRedPacket($order, $userId)
    {
        $money = $order['money'];
        $orderId = $order['id'];

        $alreadyGetPrice = EuroRewardLog::find()->where(['user_id' => $userId, 'order_id' => $orderId])->sum('price');
        $getPrice = $money - $alreadyGetPrice;
        if ($getPrice <= 0) {
            return ['code'=> '303' , 'msg' => '此红包奖励已经领取过'];
        }
        $redPacketList = static::_getRedPacketList($getPrice);
        foreach($redPacketList as $value) {
            $packetId = $value['packet_id'];
            $price = $value['price'];
            $rs = Coupon::receivePacket($packetId, $userId, 'eurocup');
            if ($rs['code'] == '0') {
                $pid = $rs['data']['pid'];
                $info = Coupon::openPacket($pid,$userId);
                if ($info['code'] == '0') {
                    static::_addRewardLog($userId, $orderId, static::REWARD_RED, $packetId, $price);
                }
            }
        }

        return ['code'=> '100' , 'msg' => '领取成功'];
    }

    /** 发伙购币
     * @param $moeny
     * @param $odds
     * @param $userId
     */
    private static function _getIdealMoney($order, $odds, $userId, $source)
    {
        $money = $order['money'];
        $orderId = $order['id'];
        $alreadyGet = EuroRewardLog::find()->select('id')->where(['user_id' => $userId, 'order_id' => $orderId])->one();
        if ($alreadyGet) {
            return ['code'=> '303' , 'msg' => '奖励已经领取过'];
        }
        $rewardMoney = ceil($money * $odds);
        $member = new Member(['id' => $userId]);
        $edit =  $member->editMoney($rewardMoney, 3, '欧洲杯竞猜伙购币', $source);
        if ($edit) {
            static::_addRewardLog($userId, $orderId, static::REWARD_HGB, $rewardMoney, $rewardMoney);
        }
        return ['code'=> '100' , 'msg' => '领取成功'];
    }

    const REWARD_RED = 1;
    const REWARD_HGB = 2;

    /** 领取奖励记录
     * @param $userId
     * @param $orderId
     * @param $rewardType
     * @param $objId
     * @return bool
     */
    private static function _addRewardLog($userId, $orderId, $rewardType, $objId, $price)
    {
        $log = new EuroRewardLog();
        $log->user_id = $userId;
        $log->order_id = (string)$orderId;
        $log->reward_obj = $rewardType;
        $log->obj_id = $objId;
        $log->price = $price;
        $log->created_at = time();
        $save =  $log->save(false);
        return $save;
    }


}