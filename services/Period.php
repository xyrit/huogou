<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/29
 * Time: 下午2:42
 */

namespace app\services;

use app\helpers\Cqssc;
use app\helpers\DateFormat;
use app\helpers\Ip;
use app\models\CurrentPeriod;
use app\models\Period as PeriodModel;
use app\models\PeriodBuylistDistribution;
use app\models\ProductCategory;
use app\models\ShareTopic;
use app\models\UserBuylistDistribution;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\User as UserModel;

class Period
{
    /**
     *  倒计时秒数,白天时时彩
     */
    const COUNT_DOWN_TIME_DAY = 600;
    const COUNT_DOWN_TIME_DAY_WAIT = 180;
    const COUNT_DOWN_TIME_DAY_MORE = 60;

    /**
     *  倒计时秒数,夜场时时彩
     */
    const COUNT_DOWN_TIME_NIGHT = 300;
    const COUNT_DOWN_TIME_NIGHT_WAIT = 180;
    const COUNT_DOWN_TIME_NIGHT_MORE = 60;

    /**
     * 倒计时秒数,没有时时彩
     */
    const COUNT_DOWN_TIME_NO_SHI_SHI = 30;
    const COUNT_DOWN_TIME_NO_SHI_SHI_WAIT = 0;
    const COUNT_DOWN_TIME_NO_SHI_SHI_MORE = 0;

    /**
     *  倒计时误差时间
     */
    const COUNT_DOWN_FAULT_BIT_TIME = 2;

    /** 根据商品最后购买时间获取 揭晓时间
     * @param $endTime
     * @return int
     */
    public static function raffTime($endTime)
    {
        $endTime = intval($endTime);
        $date = date('Y-m-d', $endTime);

        $todayBegin = strtotime(date($date . ' 00:00:00'));
        $nightEnd = strtotime(date($date . ' 02:00:00'));
        $dayBegin = strtotime(date($date . ' 10:00:00'));
        $nightBegin = strtotime(date($date . ' 22:00:00'));
        $todayEnd = strtotime('+1 day', $todayBegin);

        if ($endTime >= ($nightEnd - static::COUNT_DOWN_TIME_NIGHT) && $endTime < $dayBegin) {
            $seeTime = static::COUNT_DOWN_TIME_NO_SHI_SHI + static::COUNT_DOWN_TIME_NO_SHI_SHI_WAIT + static::COUNT_DOWN_TIME_NO_SHI_SHI_MORE + $endTime;
        }elseif (($endTime>= $todayBegin && $endTime < ($nightEnd - static::COUNT_DOWN_TIME_NIGHT)) || ($endTime>= $nightBegin && $endTime < $todayEnd)) { //夜场
            $seeTime = static::COUNT_DOWN_TIME_NIGHT + static::COUNT_DOWN_TIME_NIGHT_WAIT + static::COUNT_DOWN_TIME_NIGHT_MORE + ($endTime - ($endTime % static::COUNT_DOWN_TIME_NIGHT));
        }elseif ($endTime >= $dayBegin && $endTime < $nightBegin) { //白天场
            $seeTime = static::COUNT_DOWN_TIME_DAY + static::COUNT_DOWN_TIME_DAY_WAIT + static::COUNT_DOWN_TIME_DAY_MORE + ($endTime - ($endTime % static::COUNT_DOWN_TIME_DAY));
        } else {
            $seeTime = $endTime + 24 * 3600;
        }
        return $seeTime;
    }

    /**
     *  引入时时彩后倒计时剩余时间
     * @param $endTime
     * @return int
     */
    public static function leftTime($resultTime)
    {
        $leftTime = (int)$resultTime - time();
        return $leftTime > 0 ? $leftTime : 0;
    }

    /**
     * 时时彩 类型  夜场 白天 没有时时彩 根据满员时间
     * @param $time
     * @return string
     */
    public static function dayTypeByEndTime($time)
    {
        $endTime = intval($time);
        $h = date('G', $endTime);
        if ($h>=0 && $h<2) {
            if ($h=='1' && date('i', $endTime)>='55') {
                return 'none';
            }
            return 'night';
        } else if ($h>=10 && $h<22) {
            return 'day';
        } else if ($h>=22 && $h<=23) {
            return 'night';
        } else {
            return 'none';
        }
    }

    /**
     * 时时彩 类型  夜场 白天 没有时时彩 根据实际开奖时间
     * @param $drawTime
     * @return string
     */
    public static function dayTypeByDrawTime($drawTime)
    {
        $drawTime = intval($drawTime);
        $date = date('Y-m-d', $drawTime);

        $todayBegin = strtotime(date($date . ' 00:00:00'));
        $nightEnd = strtotime(date($date . ' 02:00:00'));
        $dayBegin = strtotime(date($date . ' 10:00:00'));
        $nightBegin = strtotime(date($date . ' 22:00:00'));
        $todayEnd = strtotime('+1 day', $todayBegin);

        if ($drawTime>=($todayBegin+static::COUNT_DOWN_TIME_NIGHT_WAIT) && $drawTime<=($nightEnd-static::COUNT_DOWN_TIME_NIGHT+static::COUNT_DOWN_TIME_NIGHT_WAIT)) {
            $type = 'night';
        }elseif ($drawTime>=($dayBegin+static::COUNT_DOWN_TIME_DAY+static::COUNT_DOWN_TIME_DAY_WAIT) && $drawTime<=($nightBegin+static::COUNT_DOWN_TIME_DAY_WAIT)) {
            $type = 'day';
        }elseif ($drawTime>=($nightBegin+static::COUNT_DOWN_TIME_NIGHT+static::COUNT_DOWN_TIME_NIGHT_WAIT) && $drawTime<=($todayEnd+static::COUNT_DOWN_TIME_NIGHT_WAIT)) {
            $type = 'night';
        }else {
            $type = '';
        }
        return $type;
    }

    /** 对应时时彩哪一期
     * @param $endTime
     * @return int|string
     */
    public static function shishiQishu($endTime)
    {
        $endTime = intval($endTime);
        $date = date('Y-m-d', $endTime);

        $todayBegin = strtotime(date($date . ' 00:00:00'));
        $nightEnd = strtotime(date($date . ' 02:00:00'));
        $dayBegin = strtotime(date($date . ' 10:00:00'));
        $nightBegin = strtotime(date($date . ' 22:00:00'));
        $todayEnd = strtotime('+1 day', $todayBegin);

        $qishu = '';
        if ($endTime >= ($nightEnd - static::COUNT_DOWN_TIME_NIGHT) && $endTime < $dayBegin) {
            return 'none';
        }else {
            if ($endTime>= $todayBegin && $endTime < ($nightEnd - static::COUNT_DOWN_TIME_NIGHT)) {
                $qishu = intval(($endTime - $todayBegin) / static::COUNT_DOWN_TIME_NIGHT) + 1;
            } elseif ($endTime >= $dayBegin && $endTime < $nightBegin) {
                $qishu = intval(($endTime - $dayBegin) / static::COUNT_DOWN_TIME_DAY) + 1 + 24;
            } elseif ($endTime>= $nightBegin && $endTime < $todayEnd) {
                $qishu = intval(($endTime - $nightBegin) / static::COUNT_DOWN_TIME_NIGHT) + 1 + 72 + 24;
            }
            if ($qishu) {
                $qishu = str_pad($qishu, 3, '0', STR_PAD_LEFT);
                $qishu = date('Ymd',$endTime) . $qishu;
            }
        }

        return $qishu;
    }

    /** 时时彩开奖号码
     * @param $qishu
     * @return string
     */
    public static function shishiNum($qishu)
    {
        if ($qishu=='none') {
            return '00000';
        } elseif (!$qishu) {
            return '';
        }
        $cache = \Yii::$app->cache;
        $key = 'SHISHICAI_NUMBER_'.$qishu;
        $shishiNum = $cache->get($key);
        if ($shishiNum) {
            return $shishiNum;
        }
        $cqssc = new Cqssc();
        $shishiNum = $cqssc->getExpectNum($qishu);
        if ($shishiNum) {
            $cache->set($key, $shishiNum, 60);
        }
        return $shishiNum;
    }


    /**
     * 已满员期数信息
     * @param $id 期数ID
     */
    public static function info($id)
    {
        $period = PeriodModel::find()->where(['id' => $id])->asArray()->one();
        $info = [];
        if ($period) {

            $leftTime = static::leftTime($period['result_time']);

            if ($leftTime>=static::COUNT_DOWN_FAULT_BIT_TIME) {
                $info['left_time'] = (int)$leftTime;
                $info['status'] = 1;
            } else {
                if ($period['user_id']>0) {
                    $userInfo = User::baseInfo($period['user_id']);
                    $userBuyInfo = UserBuylistDistribution::findByUserHomeId($userInfo['home_id'])
                        ->where(['user_id' => $userInfo['id'], 'period_id' => $period['id']])
                        ->asArray()
                        ->one();
                    $info['status'] = 2;
                    $info['lucky_code'] = $period['lucky_code'];
                    $info['user_buy_ip'] = long2ip($period['ip']);
                    $info['user_name'] = $userInfo['username'];
                    $info['user_home_id'] = $userInfo['home_id'];
                    $info['uid'] = $userInfo['id'];
                    $info['user_avatar'] = $userInfo['avatar'];
                    $info['user_addr'] = Ip::getAddressByIp(long2ip($period['ip']));
                    $info['user_buy_num'] = $userBuyInfo['buy_num'];
                    $info['user_buy_time'] = DateFormat::microDate($userBuyInfo['buy_time']);

                    $raffTime = Period::raffTime($period['end_time']);
                    $info['raff_time'] = DateFormat::microDate($raffTime); //揭晓时间
                    $info['raff_time2'] = DateFormat::formatTime($raffTime); //揭晓时间
                    $info['left_time'] = 0;
                    $info['table_id'] = $period['table_id'];
                } else {
                    $info['status'] = 3;
                    $info['statusText'] = '开奖故障';
                    $info['left_time'] = 1800;
                }
            }

            $productInfo = Product::info($period['product_id']);
            $info['goods_id'] = $period['product_id'];
            $info['goods_name'] = $productInfo['name'];
            $info['goods_brief'] = $productInfo['brief'];
            $info['goods_picture'] = $productInfo['picture'];
            $info['goods_catid'] = $productInfo['cat_id'];
            $info['price'] = sprintf('%.2f', $period['price']);
            $info['start_time'] = DateFormat::microDate($period['start_time']);
            $info['end_time'] = DateFormat::microDate($period['end_time']);
            $info['period_number'] = $period['period_number'];
            $info['period_no'] = $period['period_no'];
            $info['period_id'] = $period['id'];
            $info['goods_info'] = $productInfo['intro'];
            $info['limit_num'] = $productInfo['limit_num'];
            $info['buy_unit'] = $period['buy_unit'];
            $info['table_id'] = $period['table_id'];
        }
        return $info;
    }

    public static function getStartRaffleList($time)
    {
        $time = empty($time) ? microtime(true) : $time;
        $query = \app\models\Period::find();
        $query->andWhere(['>', 'end_time', $time]);
        $query->orderBy('end_time asc');
        $query->limit(50);
        $result = $query->asArray()->all();
        $list = [];
        $now = microtime(true);
        if ($result) {
            $productIds = ArrayHelper::getColumn($result, 'product_id');
            $productsInfo = Product::info($productIds);
            foreach ($result as $key=>$one) {
                $productInfo = $productsInfo[$one['product_id']];
                $info = [];
                $info['period_id'] = $one['id'];
                $info['period_no'] = $one['period_no'];
                $info['goods_picture'] = $productInfo['picture'];
                $info['goods_name'] = $productInfo['name'];
                $info['period_number'] = $one['period_number'];
                $info['price'] = sprintf('%.2f', $one['price']);
                $info['goods_id'] = $one['product_id'];
                $info['limit_num'] = $one['limit_num'];
                $info['buy_unit'] = $one['buy_unit'];

                $leftTime = static::leftTime($one['result_time']);
                $info['left_time'] = $leftTime > 0 ? (int)$leftTime : 0;
                $endTime = $one['end_time'];
                $list[] = $info;
            }
        }

        $result = [];
        $result['list'] = $list;
        $result['maxSeconds'] = isset($endTime) ? $endTime : $now;
        $result['time'] = $time;

        return $result;
    }


    /** 某期的参与纪录/购买记录
     * @param $id 期数ID
     * @param $page
     * @param int $perpage
     */
    public static function buyList($id, $page, $perpage = 20)
    {
        $periodInfo = CurrentPeriod::findOne($id);
        if (!$periodInfo) {
            $periodInfo = PeriodModel::findOne($id);
        }

        if (!$periodInfo) {
            $return['list'] = [];
            $return['totalCount'] = 0;
            $return['totalPage'] = 0;
            return $return;
        }

        $tableId = $periodInfo->table_id;
        $query = PeriodBuylistDistribution::findByTableId($tableId)->where(['and','period_id='.$id,['>','buy_num',0]]);
        $query->select([
            'id',
            'product_id',
            'period_id',
            'user_id',
            'buy_num',
            'ip',
            'source',
            'buy_time'
        ]);
        $query->orderBy('buy_time desc');
         
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $userIds = ArrayHelper::getColumn($result, 'user_id');
        $usersBaseInfo = User::baseInfo($userIds);
        $buyList = [];
        foreach ($result as $one) {
            $info = [];
            $info['buy_device'] = static::getSource($one['source']);
            $info['buy_id'] = $one['id'];
            $info['buy_ip'] = long2ip($one['ip']);
            $info['buy_ip_addr'] = Ip::getAddressByIp(long2ip($one['ip']));
            $info['buy_num'] = $one['buy_num'];
            $info['buy_time'] = DateFormat::microDate($one['buy_time']);
            $userBaseInfo = $usersBaseInfo[$one['user_id']];
            $info['user_name'] = $userBaseInfo['username'];
            $info['user_id'] = $userBaseInfo['id'];
            $info['user_home_id'] = $userBaseInfo['home_id'];
            $info['user_avatar'] = $userBaseInfo['avatar'];
            $buyList[] = $info;
        }


        $return['list'] = $buyList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 根据buyId和期数Id获取用户购买的码
     * @param $periodId
     * @param $buyId
     * @return mixed|string
     */
    public static function getUserBuyCodesByBuyId($periodId, $buyId)
    {
        $periodInfo = CurrentPeriod::findOne($periodId);
        if($periodInfo['left_num'] != 0) return '';
        if (!$periodInfo) {
            $periodInfo = PeriodModel::findOne($periodId);
        }
        $tableId = $periodInfo->table_id;
        $result = PeriodBuylistDistribution::findByTableId($tableId)->select(['codes'])->where(['id' => $buyId])->one();
        if ($result) {
            return $result->codes;
        }
        return '';
    }

    /** 期数列表
     * @param $catId    商品分类id
     * @param int $isRevealed 是否已揭晓 all=全部,0=正在揭晓，1=已揭晓
     * @param $page
     * @param int $perpage
     * @param  $isLimit 是否限购 all=全部,0=不限购，1=限购
     * @return array
     */
    public static function getList($catId, $isRevealed = 'all', $page, $perpage = 20, $isLimit = 'all',$buyUnit = 'all')
    {
        $query = PeriodModel::find();

        if ($catId) {
            $categoryChildren = ProductCategory::allOrderList($catId);
            $catIds = [];
            if ($categoryChildren) {
                $catIds = ArrayHelper::getColumn($categoryChildren, 'id');
            }
            array_unshift($catIds, $catId);
            $query->where(['cat_id' => $catIds]);
        }

        if ($isRevealed !== 'all') {
            if ($isRevealed) {
                $query->andWhere(['>', 'user_id', 0]);
                $query->andWhere(['<=', 'result_time', time()]);
            } else {
                $query->andWhere(['>', 'result_time', time()]);
            }
        }

        if ($isLimit !== 'all') {
            if ($isLimit) {
                $query->andWhere(['<>','limit_num',0]);
            } else {
                $query->andWhere(['=','limit_num',0]);
            }
        }
        if ($buyUnit !== 'all') {
            if ($buyUnit) {
                $query->andWhere(['=','buy_unit',$buyUnit]);
            }
        }
        $query->orderBy('end_time desc');

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        if ($isRevealed) {
            $userIds = ArrayHelper::getColumn($result, 'user_id');
            $usersInfo = User::baseInfo($userIds);
        }
        $productIds = ArrayHelper::getColumn($result, 'product_id');
        $productsInfo = Product::info($productIds);
        $list = [];
        foreach ($result as $one) {
            if (empty($productsInfo[$one['product_id']])) {
                continue;
            }
            $productInfo = $productsInfo[$one['product_id']];
            $info = [];
            $info['period_id'] = $one['id'];
            $info['period_no'] = $one['period_no'];
            $info['goods_picture'] = $productInfo['picture'];
            $info['goods_name'] = $productInfo['name'];
            $info['period_number'] = $one['period_number'];
            $info['price'] = sprintf('%.2f', $one['price']);
            $info['goods_id'] = $one['product_id'];
            $info['limit_num'] = $one['limit_num'];
            $info['buy_unit'] = $one['buy_unit'];

            $leftTime = static::leftTime($one['result_time']);

            if ($leftTime>=static::COUNT_DOWN_FAULT_BIT_TIME) {
                $info['status'] = 1;
                $info['left_time'] = $leftTime;
                $info['end_time'] = $one['end_time'];
            } else {
                if ($one['user_id']>0) {
                    $userInfo = $usersInfo[$one['user_id']];
                    $userBuyInfo = UserBuylistDistribution::findByUserHomeId($userInfo['home_id'])
                        ->where(['user_id' => $userInfo['id'], 'period_id' => $one['id']])
                        ->asArray()
                        ->one();

                    $info['status'] = 2;
                    $raffTime = Period::raffTime($one['end_time']);
                    $info['raff_time'] = DateFormat::microDate($raffTime);
                    $info['user_name'] = $userInfo['username'];
                    $info['user_home_id'] = $userInfo['home_id'];
                    $info['user_id'] = $userInfo['id'];
                    $info['user_avatar'] = $userInfo['avatar'];
                    $info['publish_time'] = DateFormat::microDate($one['end_time']);
                    $info['user_addr'] = Ip::getAddressByIp(long2ip($one['ip']));
                    $info['user_ip'] = long2ip($one['ip']);
                    $info['lucky_code'] = $one['lucky_code'];
                    $info['user_buy_num'] = $userBuyInfo['buy_num'];
                    $info['user_buy_time'] = DateFormat::microDate($userBuyInfo['buy_time']);
                    $share = ShareTopic::findOne(['period_id'=>$info['period_id'],'is_pass'=>1]);
                    if ($share) {
                        $info['share_id'] = $share->id;
                    } else {
                        $info['share_id'] = 0;
                    }
                    $info['left_time'] = 0;
                    $info['end_time'] = $one['end_time'];
                } else {
                    $info['status'] = 3;
                    $info['left_time'] = (int)$one['end_time'] + 1800 - time();
                    $info['end_time'] = $one['end_time'];
                    $info['statusText'] = '时时彩开奖故障';
                }
            }

            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     * 获取用户单期购买记录
     * @param  int $uid 用户id
     * @param  int $pid 期数id
     * @return [type]      [description]
     */
    public static function getCodeByUser($uid,$pid){
        $periodInfo = CurrentPeriod::find()->where(['id'=>$pid])->asArray()->one();
        if (!$periodInfo) {
            $periodInfo = PeriodModel::find()->where(['id'=>$pid])->one();
        }
        if (!$periodInfo) {
            return [];
        }
        $table_id = $periodInfo['table_id'];
        $codes = array();
        $periodBuylist = new PeriodBuylistDistribution($table_id);
        $codesInfo = $periodBuylist->find()->where(['period_id'=>$pid,'user_id'=>$uid])->orderBy('buy_time desc')->asArray()->all();
        foreach ($codesInfo as $key => $value) {
            $codes[$key]['time'] = DateFormat::microDate($value['buy_time']);
            $codes[$key]['codes'] = $value['codes'];
        }
        return $codes;
    }


    /**
     * 来源
     * @param  int $source 来源id
     * @return [type]         [description]
     */
    public static function getSource($source){
        switch ($source) {
            case '1':
                return array('ico'=>'pc','name'=>'PC电脑','url'=>'javascript:;');
                break;
            case '2':
                return array('ico'=>'weixin','name'=>'微信公众平台','url'=>'http://help.huogou.com/wechat.html');
                break;
            case '3':
                return array('ico'=>'iphone','name'=>'iOS客户端','url'=>'http://help.huogou.com/app.html');
                break;
            case '4':
                return array('ico'=>'android','name'=>'Android客户端','url'=>'http://help.huogou.com/app.html');
                break;
            case '5':
                return array('ico'=>'touch','name'=>'触屏版','url'=>'http://help.huogou.com/app.html');
                break;
            default:
                return array('ico'=>'pc','name'=>'PC电脑','url'=>'javascript:;');
                break;
        }
    }

    public static function getLotteryCodes($pid){
        $periodInfo = PeriodModel::findOne(['id'=>$pid]);
        $periodBuylist = new PeriodBuylistDistribution($periodInfo['table_id']);
        $buyList = $periodBuylist->find()->where(['period_id'=>$periodInfo['id'],'user_id'=>$periodInfo['user_id']])->orderBy('buy_time desc')->asArray()->all();
        foreach ($buyList as $key => &$value) {
            $value['buy_time'] = DateFormat::microDate($value['buy_time']);
            $value['lucky_code'] = $periodInfo['lucky_code'];
        }
        return $buyList;
    }

    /**
     * 用户购买某期商品数量
     * @param  int $pid 购买数量
     * @return [type]      [description]
     */
    public static function getUserHasBuyCount($uid,$pid){
        $home = UserModel::find()->select('home_id')->where(['id'=>$uid])->asArray()->one();
        $buy = UserBuylistDistribution::findByUserHomeId($home['home_id'])->select('buy_num')->where(['user_id'=>$uid,'period_id'=>$pid])->asArray()->one();
        return $buy['buy_num'];
    }

    /**
     * 获取最新购买记录
     * @param  int $time 时间戳
     * @return [type]       [description]
     */
    public static function getNewBuyList($time,$pid){
        $periodInfo = currentPeriod::find()->select('table_id')->where(['id'=>$pid])->asArray()->one();
        if (!$periodInfo) {
            $periodInfo = PeriodModel::find()->select(['table_id'])->where(['id'=>$pid])->asArray()->one();
        }
        $model = new PeriodBuylistDistribution($periodInfo['table_id']);
        $list = $model->find()->select('user_id,buy_num,buy_time')->where(['and','period_id='.$pid,['>','buy_time',$time]])->asArray()->all();
        return $list;
    }


    /** 获取期号
     * @param $period
     * @return string
     */
    public static function getPeriodNo($period)
    {
        $startTime = $period['start_time'];
        $periodId = $period['id'];
        $date = date('Y-m-d', intval($startTime));
        $start = strtotime($date);
        $end = $start + 3600*24;
        $sql = "select rowno,id,start_time from (select (@rowno:=@rowno+1) as rowno,a.* from ( select * from ((select id,start_time from periods where start_time > '".$start."' and start_time < '".$end."' order by start_time asc) union all (select id,start_time from current_periods where start_time > '".$start."' and start_time < '".$end."' order by start_time asc ) ) as k order by k.start_time asc) as a ,(select @rowno:=0) t) as b where b.id=".$periodId;
        $db = \Yii::$app->db;
        $query = $db->createCommand($sql);
        $result = $query->queryOne();

        $no = $result['rowno'];

        $periodNo = static::buildPeriodNo($startTime, $no);

        return $periodNo;
    }

    /** 生成期号
     * @param $startTime
     * @param $rowNo
     * @return string
     */
    public static function buildPeriodNo($startTime, $rowNo)
    {
        $no = str_pad($rowNo, 5, 0, STR_PAD_LEFT);
        $startYear = 2010;
        $yearNum = date('Y', $startTime) - $startYear;
        $dateNum = date('md', $startTime);
        $periodNo = $yearNum . $dateNum . $no;

        return $periodNo;
    }

    public static function getPeriodInfo($id)
    {
        /** 期数信息
         * @param $id 商品ID
         * @return array|null
         */
            if (is_array($id)) {
                $Period = PeriodModel::find()->where(['id'=>$id])->indexBy('id')->asArray()->all();
                $CurrentPeriod = CurrentPeriod::find()->where(['id'=>$id])->indexBy('id')->asArray()->all();
            }else{
                return false;
            }
           $com = array_merge($Period,$CurrentPeriod);
           $ret_arr=array();
           foreach($com as $k =>$v){
             $ret_arr[$v['id']]=$v;
           }

        return $ret_arr;

    }
}
