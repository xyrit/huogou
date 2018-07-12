<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/3/8
 * Time: 下午5:42
 */
namespace app\services;

use app\helpers\Ip;
use app\helpers\MyRedis;
use app\models\FreeCurrentPeriod;
use app\models\FreeInvite;
use app\models\FreePeriod;
use app\models\FreePeriodBuylistDistribution;
use app\models\FreeProduct;
use app\models\FreeProductImage;
use app\models\FreeUserBuylistDistribution;
use app\models\PointFollowDistribution;
use app\models\User;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class FreeBuy
{

    const FIRST_CODE = 10000001;

    const PERIOD_CODE_KEY = 'FREE_PERIOD_CODE_';

    const USER_PRODUCT_BUYING_KEY = 'FREE_UESR_PRODUCT_BUYING_';

    const USER_PRODUCT_BUY_RESULT_KEY = 'FREE_UESR_PRODUCT_BUY_RESULT_';

    const PAY_TYPE_GIVE = 1;
    const PAY_TYPE_SHARE = 2;//分享
    const PAY_TYPE_SHARE_REG = 3;//分享注册
    const PAY_TYPE_POINT = 4;//福分兑换

    const PAY_BANK_SHARE_WECHAT = 1;
    const PAY_BANK_SHARE_QZONE = 2;


    /**
     *  零元购商品列表
     */
    public static function productList($page = 1, $perpage = 20)
    {
        $query = FreeCurrentPeriod::find()->select('free_current_periods.*,p.name,p.brief,p.picture,p.price')->leftJoin('free_products as p', 'free_current_periods.product_id=p.id');
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $result = $query->orderBy('start_time asc,id asc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $now = time();

        $startedList = [];
        $notStartList = [];
        foreach ($result as $one) {
            $startTime = $one['start_time'];
            $endTime = $one['end_time'];
            if ($startTime > $now) {
                $goodsStatus = 1; //商品还未开始
                $timestamp = strtotime(date('Y-m-d H:00:00', $startTime));
                $leftTime = $timestamp - $now;
            } elseif ($endTime > $now) {
                $goodsStatus = 2;//商品已经开始
                $timestamp = strtotime(date('Y-m-d H:00:00', $endTime));
                $leftTime = $timestamp - $now;
            }
            $info = [];
            $info['left_time'] = $leftTime > 0 ? $leftTime : 0;
            $info['start_time'] = date('m月d日H点开始',$one['start_time']);
            $info['goods_status'] = $goodsStatus;
            $info['goods_id'] = $one['product_id'];
            $info['goods_name'] = $one['name'];
            $info['goods_brief'] = $one['brief'];
            $info['price'] = $one['price'];
            $info['sales_num'] = $one['sales_num'];
            $info['goods_picture'] = $one['picture'];

            if ($goodsStatus==1) {
                $notStartList[] = $info;
            } elseif($goodsStatus==2) {
                $startedList[] = $info;
            }
        }

        $sort = function($a, $b) {
            if($a['left_time'] == $b['left_time']){
                return 0;
            }
            return($a['left_time']<$b['left_time']) ? -1 : 1;
        };
        usort($startedList, $sort);
        usort($notStartList, $sort);
        $list = array_merge($startedList, $notStartList);
        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     *  零元购商品详情
     */
    public static function productInfo($id)
    {
        $product = FreeProduct::find()->where(['id' => $id])->asArray()->one();
        $info = [];
        if ($product) {
            $currentPeriod = FreeCurrentPeriod::find()->where(['product_id' => $id])->asArray()->one();
            $now = time();
            $endTime = $currentPeriod['end_time'];
            $timestamp = strtotime(date('Y-m-d H:00:00', $endTime));
            $leftTime = $timestamp - $now;
            $info['left_time'] = $leftTime > 0 ? $leftTime : 0;
            $info['goods_id'] = $product['id'];
            $info['goods_name'] = $product['name'];
            $info['goods_brief'] = $product['brief'];
            $info['price'] = $product['price'];
            $info['sales_num'] = $currentPeriod['sales_num'];
            $info['period_id'] = $currentPeriod['id'];
            $info['table_id'] = $currentPeriod['table_id'];
            $info['goods_picture'] = $product['picture'];
            $info['start_time'] = $currentPeriod['start_time'];
            $info['end_time'] = $currentPeriod['end_time'];
            $info['raff_time'] = date('Y年m月d日H点', $currentPeriod['end_time']);
        }
        return $info;
    }

    /**商品详情
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function productIntro($id)
    {
        $product = FreeProduct::find()->select('intro')->where(['id' => $id])->asArray()->one();
        return $product['intro'];
    }

    /**
     * 商品所有图片
     * @param $id 商品ID
     */
    public static function productImages($id)
    {
        $picture = FreeProduct::find()->select('picture')->where(['id' => $id])->asArray()->one();
        $productImage = FreeProductImage::find()->select('basename')->where(['product_id' => $id])->asArray()->all();
        $images = ArrayHelper::getColumn($productImage, 'basename');
        $key = array_search($picture['picture'], $images);
        unset($images[$key]);
        array_unshift($images, $picture['picture']);
        return $images;
    }

    /**
     *  零元购往期揭晓列表
     */
    public static function periodList($page = 1, $perpage = 20)
    {
        $query = FreePeriod::find();
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $result = $query->orderBy('end_time desc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $productIds = ArrayHelper::getColumn($result, 'product_id');
        $productsInfo = FreeProduct::find()->where(['id' => $productIds])->indexBy('id')->asArray()->all();
        $list = [];
        foreach ($result as $one) {
            $product = $productsInfo[$one['product_id']];
            $info['goods_id'] = $product['id'];
            $info['period_id'] = $one['id'];
            $info['goods_name'] = $product['name'];
            $info['goods_brief'] = $product['brief'];
            $info['price'] = $product['price'];
            $info['sales_num'] = $one['sales_num'];
            $info['goods_picture'] = $product['picture'];
            $userInfo = \app\services\User::baseInfo($one['user_id']);
            $info['lucky_code'] = $one['lucky_code'];
            $info['user_name'] = $userInfo['username'];
            $info['user_home_id'] = $userInfo['home_id'];
            $info['user_avatar'] = $userInfo['avatar'];
            $info['user_addr'] = Ip::getAddressByIp(long2ip($one['ip']));
            $info['user_ip'] = long2ip($one['ip']);
            $info['raff_time'] = date('Y年m月d日H点', $one['end_time']);//揭晓时间
            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     *  零元购往期揭晓详情
     */
    public static function periodInfo($id)
    {
        $period = FreePeriod::find()->where(['id' => $id])->asArray()->one();
        $info = [];
        if ($period) {
            $product = FreeProduct::find()->where(['id' => $period['product_id']])->asArray()->one();
            $info['goods_id'] = $product['id'];
            $info['period_id'] = $period['id'];
            $info['table_id'] = $period['table_id'];
            $info['goods_name'] = $product['name'];
            $info['goods_brief'] = $product['brief'];
            $info['price'] = $product['price'];
            $info['sales_num'] = $period['sales_num'];
            $info['goods_picture'] = $product['picture'];
            $userInfo = \app\services\User::baseInfo($period['user_id']);
            $info['lucky_code'] = $period['lucky_code'];
            $info['user_name'] = $userInfo['username'];
            $info['user_home_id'] = $userInfo['home_id'];
            $info['user_id'] = $userInfo['id'];
            $info['user_avatar'] = $userInfo['avatar'];
            $info['user_addr'] = Ip::getAddressByIp(long2ip($period['ip']));
            $info['user_ip'] = long2ip($period['ip']);
            $info['start_time'] = $period['start_time'];
            $info['end_time'] = $period['end_time'];
            $info['raff_time'] = date('Y年m月d日H点', $period['end_time']);//揭晓时间

        }
        return $info;
    }

    /**
     *  零元购用户某期参与记录
     */
    public static function userBuyListByPeriodId($userId, $periodId)
    {
        $period = FreeCurrentPeriod::find()->where(['id' => $periodId])->asArray()->one();
        if (!$period) {
            $period = FreePeriod::find()->where(['id' => $periodId])->asArray()->one();
        }
        $tableId = $period['table_id'];
        $buyList = FreePeriodBuylistDistribution::findByTableId($tableId)->where(['period_id' => $periodId, 'user_id' => $userId])->orderBy('id asc')->asArray()->all();
        return [
            'list' => $buyList,
            'end_time' => $period['end_time'],
        ];
    }

    /** 零元购用户某期邀请列表
     * @param $userId
     * @param $periodId
     */
    public static function inviteList($userId, $periodId, $page, $perpage)
    {
        $query = FreeInvite::find()->where(['user_id' => $userId, 'period_id' => $periodId]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('id desc');
        $invite = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $userIds = ArrayHelper::getColumn($invite, 'invite_uid');
        $usersBaseInfo = \app\services\User::baseInfo($userIds);
        foreach ($invite as &$one) {
            $userBaseInfo = $usersBaseInfo[$one['invite_uid']];
            $one['user_nickname'] = $userBaseInfo['username'];
            $one['user_home_id'] = $userBaseInfo['home_id'];
            $one['user_avatar'] = $userBaseInfo['avatar'];
            $one['invite_time'] = date('Y-m-d H:i:s', $one['invite_time']);
            $one['code'] = '+' . $one['buy_num'] . '伙购码';
        }

        $return['list'] = $invite;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();

        return $return;
    }

    /** 零元购邀请人数
     * @param $userId
     * @return int|string
     */
    public static function inviteNum($userId, $periodId)
    {
        $count = FreeInvite::find()->where(['user_id' => $userId, 'period_id' => $periodId])->count();
        return $count;
    }

    /** 零元购用户邀请注册的购码
     * @param $userId
     * @param $periodId
     * @param $source
     * @return int
     */
    public static function buyByReg($userId, $periodId, $source)
    {
        $regBuyNum = 3;
        $buyNum = 0;
        for ($i = 0; $i < $regBuyNum; $i++) {
            $freeBuy = FreeBuy::buy($userId, $periodId, FreeBuy::PAY_TYPE_SHARE_REG, '0', $source);
            if ($freeBuy['code'] == 100) {
                $buyNum++;
            }
        }
        return $buyNum;
    }

    /**
     *  零元购用户购码
     */
    public static function buy($userId, $periodId, $payType, $payBank, $source)
    {
        $redis = new MyRedis();
        $userBuyresultKey = static::USER_PRODUCT_BUY_RESULT_KEY . $periodId . '_' . $userId;
        $userBuyresultDuration = 600;
        $redis->del($userBuyresultKey);
        if (!in_array($payType, [self::PAY_TYPE_GIVE, self::PAY_TYPE_SHARE, self::PAY_TYPE_SHARE_REG, self::PAY_TYPE_POINT])) {
            $return = ['code' => 206, 'msg' => '参数错误'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        } elseif ($payType == self::PAY_TYPE_SHARE && !in_array($payBank, [self::PAY_BANK_SHARE_WECHAT, self::PAY_BANK_SHARE_QZONE])) {
            $return = ['code' => 206, 'msg' => '参数错误'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        $currentPeriod = FreeCurrentPeriod::find()->where(['id' => $periodId])->asArray()->one();
        if (!$currentPeriod) {
            $return = ['code' => 204, 'msg' => '活动已结束'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }
        $startTime = (int)date('YmdH', $currentPeriod['start_time']);
        $endTime = (int)date('YmdH', $currentPeriod['end_time']);
        $nowTime = (int)date('YmdH');
        if ($startTime > $nowTime) {
            $return = ['code' => 203, 'msg' => '活动未开始'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        } elseif ($endTime <= $nowTime) {
            $return = ['code' => 203, 'msg' => '活动已结束'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        $tableId = $currentPeriod['table_id'];
        $user = User::find()->select('phone,home_id,point')->where(['id' => $userId])->asArray()->one();
        $userHomeId = $user['home_id'];
        $userPhone = $user['phone'];

        if (!$userPhone) {
            $return = ['code' => 203, 'msg' => '未绑定手机'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        if ($payType != self::PAY_TYPE_POINT) {
            if ($payType == self::PAY_TYPE_SHARE) {
                $buyWhere = ['period_id' => $currentPeriod['id'], 'user_id' => $userId, 'pay_type' => $payType, 'pay_bank' => $payBank];
            } else {
                $buyWhere = ['period_id' => $currentPeriod['id'], 'user_id' => $userId, 'pay_type' => $payType];
            }
            $buyNumQuery = FreePeriodBuylistDistribution::findByTableId($tableId)
                ->where($buyWhere);
            //每天赠送一次机会
//            if ($payType == self::PAY_TYPE_GIVE || $payType == self::PAY_TYPE_SHARE) {
//                $beginToday = strtotime(date('Y-m-d 00:00:00'));
//                $endToday = strtotime('+1 day', $beginToday);
//                $buyNumQuery->andWhere(['>=', 'buy_time', $beginToday])->andWhere(['<', 'buy_time', $endToday]);
//            }
            $buyNum = $buyNumQuery->count();
            switch ($payType) {
                case self::PAY_TYPE_GIVE:
                case self::PAY_TYPE_SHARE:
                    $maxBuyNum = 1;
                    break;
                case self::PAY_TYPE_SHARE_REG:
                    $maxBuyNum = 30;
                    break;
                default:
                    $maxBuyNum = 0;
                    break;

            }
            if ($payType == self::PAY_TYPE_SHARE) {
                $errMsg = '分享成功';
            } else {
                $errMsg = '购买超过限制';
            }
            if ($buyNum >= $maxBuyNum) {
                $return = ['code' => 202, 'msg' => $errMsg];
                $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
                return $return;
            }
        }

        $periodCodeKey = FreeBuy::PERIOD_CODE_KEY . $currentPeriod['id'];
        $buyCode = $redis->get($periodCodeKey);

        if (!$buyCode) {
            $return = ['code' => 207, 'msg' => '服务器错误'];
            $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
            return $return;
        }

        $buyTime = (string)microtime(true);

        if ($payType == self::PAY_TYPE_POINT) {
            if ($user['point'] < PointFollowDistribution::NUMBER_FREE_BUY) {
                $return = ['code' => 207, 'msg' => '福分不足'];
                $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
                return $return;
            }
            $member = new Member(['id' => $userId]);
            $desc = '兑换0元伙购码';
            $member->editPoint(-1 * PointFollowDistribution::NUMBER_FREE_BUY, PointFollowDistribution::POINT_FREE_BUY, $desc);
        }

        $transaction = \Yii::$app->db->beginTransaction();

        $periodBuylist = new FreePeriodBuylistDistribution($tableId);
        $periodBuylist->product_id = $currentPeriod['product_id'];
        $periodBuylist->period_id = $currentPeriod['id'];
        $periodBuylist->user_id = $userId;
        $periodBuylist->code = $buyCode ?: '';
        $periodBuylist->ip = ip2long(\Yii::$app->request->userIP);
        $periodBuylist->source = $source;
        $periodBuylist->pay_type = (int)$payType;
        $periodBuylist->pay_bank = (int)$payBank;
        $periodBuylist->buy_time = $buyTime;
        $save = $periodBuylist->save(false);


        if ($save) {
            $redis->incr($periodCodeKey);
            FreeCurrentPeriod::updateAll(['sales_num' => new Expression('sales_num+1')], ['id' => $currentPeriod['id']]);
            $userBuylist = FreeUserBuylistDistribution::findByUserHomeId($userHomeId)->where(['user_id' => $userId, 'period_id' => $currentPeriod['id']])->one();
            if ($userBuylist) {
                $userBuylist->buy_num += 1;
            } else {
                $userBuylist = new FreeUserBuylistDistribution($userHomeId);
                $userBuylist->buy_num = 1;
                $userBuylist->product_id = $currentPeriod['product_id'];
                $userBuylist->period_id = $currentPeriod['id'];
                $userBuylist->buy_time = $buyTime;
                $userBuylist->user_id = $userId;
            }
            $userBuylistSave = $userBuylist->save(false);
            if ($userBuylistSave) {
                $transaction->commit();
                $return = ['code' => 100];
                $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
                return $return;
            } else {
                $redis->decr($periodCodeKey);
                $transaction->rollBack();
            }
        }
        $return = ['code' => 201, 'msg' => '购买失败'];
        $redis->set($userBuyresultKey, Json::encode($return), $userBuyresultDuration);
        return $return;
    }

    /** 零元购购买结果
     * @param $userId
     * @param $periodId
     * @return array|mixed|string
     */
    public static function buyResult($userId, $periodId)
    {
        $redis = new MyRedis();
        $userBuyresultKey = FreeBuy::USER_PRODUCT_BUY_RESULT_KEY . $periodId . '_' . $userId;
        $getUserBuyresultTimesKey = 'FREE_GET_BUY_RESULT_TIMES_' . $periodId . '_' . $userId;//获取用户购买无结果次数
        $times = $redis->get($getUserBuyresultTimesKey);


        if ($result = $redis->get($userBuyresultKey)) {
            $result = Json::decode($result);
            return $result;
        } else {

            if ($times > 30) {
                $redis->set($getUserBuyresultTimesKey, 1, 60);
                $return = ['code' => 201, 'msg' => '购买失败'];
                return $return;
            } else {
                $redis->incr($getUserBuyresultTimesKey);
                $redis->expire($getUserBuyresultTimesKey, 60);
            }
            return [];
        }



    }


}