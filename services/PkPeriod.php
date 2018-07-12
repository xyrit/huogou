<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/11
 * Time: 下午3:20
 */
namespace app\services;

use app\helpers\DateFormat;
use app\helpers\Ip;
use app\models\PkCurrentPeriod;
use app\models\PkPeriod as PkPeriodModel;
use app\models\PkCurrentPeriod as PkCurrentPeriodModel;
use app\models\PkPeriodBuylistDistribution;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\UserBuylistDistribution;
class PkPeriod
{

    /** 获取桌号
     * @param $tableId
     * @param $periodBuyId
     * @param $periodId
     * @param $buySize
     * @return mixed
     */
    public static function getBuyTable($tableId, $periodBuyId, $periodId, $buySize)
    {
        $periodBuyList = new PkPeriodBuylistDistribution($tableId);
        $tableName = $periodBuyList::tableName();
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $lettersNum = strlen($letters);
        $randVarRowNo = '';
        for ($i=0; $i<6; $i++) {
            $randNum = mt_rand(0, $lettersNum-1);
            $randVarRowNo .= $letters[$randNum];
        }

        $sql = "select rowno from (select (@".$randVarRowNo.":=@".$randVarRowNo."+1) as rowno, a.* from " . $tableName . " as a,(select @".$randVarRowNo.":=0) t where period_id = '{$periodId}' and buy_size = '{$buySize}' order by a.id asc) as d where d.id = '{$periodBuyId}'";
        $db = \Yii::$app->db;
        $query = $db->createCommand($sql);
        $result = $query->queryOne();
        $buyTableNo = $result['rowno'];
        return $buyTableNo;
    }

    /** 期数详情
     * @param $periodId
     */
    public static function info($periodId)
    {

        $period = PkPeriodModel::find()->where(['id' => $periodId])->asArray()->one();
        $info = [];
        if ($period) {
            $info['lucky_code'] = $period['lucky_code'];
            $info['left_time'] = 0;
            $info['table_id'] = $period['table_id'];
            $info['size'] = $period['size'];
            $info['match_num'] = $period['match_num'];
            $productInfo = PkProduct::info($period['product_id']);
            $info['goods_id'] = $period['product_id'];
            $info['goods_name'] = $productInfo['name'];
            $info['goods_brief'] = $productInfo['brief'];
            $info['goods_picture'] = $productInfo['picture'];
            $info['price'] = sprintf('%.2f', $period['price']);
            $info['start_time'] = DateFormat::microDate($period['start_time']);
            $info['end_time'] = DateFormat::microDate($period['end_time']);
            $info['period_no'] = $period['period_no'];
            $info['period_id'] = $period['id'];
            $info['goods_info'] = $productInfo['intro'];
            $info['table_id'] = $period['table_id'];
            $info['time_type'] = ($period['end_time'] - $period['start_time'])/60;
        }
        return $info;
    }

    /** 获取期号
     * @param $period
     * @return string
     */
    public static function getPeriodNo($period)
    {
        $startTime = $period['start_time'];
        $periodId = $period['id'];
        $productId = $period['product_id'];
        $date = date('Y-m-d', intval($startTime));
        $start = strtotime($date);
        $end = $start + 3600*24;
        $sql = "select rowno,id,start_time from (select (@rowno:=@rowno+1) as rowno,a.* from ( select * from ((select id,start_time from pk_periods where product_id = '".$productId."' and start_time > '".$start."' and start_time < '".$end."' order by start_time asc) union all (select id,start_time from pk_current_periods where product_id = '".$productId."' and start_time > '".$start."' and start_time < '".$end."' order by start_time asc ) ) as k order by k.start_time asc) as a ,(select @rowno:=0) t) as b where b.id=".$periodId;
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
        $periodNo = 'PK' . $yearNum . $dateNum . $no;

        return $periodNo;
    }

    /** 某期参与记录
     * @param $id
     * @param $page
     * @param int $perpage
     * @return mixed
     */
    public static function buyList($id, $page, $perpage = 20)
    {
        $periodInfo = PkCurrentPeriodModel::findOne($id);
        if (!$periodInfo) {
            $periodInfo = PkPeriodModel::findOne($id);
        }

        $tableId = $periodInfo->table_id;
        $query = PkPeriodBuylistDistribution::findByTableId($tableId)->where(['period_id' => $id]);
        $query->select([
            'id',
            'product_id',
            'period_id',
            'user_id',
            'buy_size',
            'buy_table',
            'ip',
            'source',
            'buy_time'
        ]);
        $orderBy = 'id asc';

        $bigQuery = clone $query;
        $smallQuery = clone $query;
        $bigQuery->andWhere(['buy_size' => PkCurrentPeriod::BUY_SIZE_BIG]);
        $smallQuery->andWhere(['buy_size' => PkCurrentPeriod::BUY_SIZE_SMALL]);

        $bigCountQuery = clone $bigQuery;
        $bigTotalCount = $bigCountQuery->count();
        $bigPagination = new Pagination(['totalCount' => $bigTotalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);

        $smallCountQuery = clone $smallQuery;
        $smallTotalCount = $smallCountQuery->count();
        $smallPagination = new Pagination(['totalCount' => $smallTotalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);

        $bigResult = $bigQuery->orderBy($orderBy)->offset($bigPagination->offset)->limit($bigPagination->limit)->asArray()->all();
        $smallResult = $smallQuery->orderBy($orderBy)->offset($smallPagination->offset)->limit($smallPagination->limit)->asArray()->all();

        if ($bigTotalCount > $smallTotalCount) {
            $totalCount = $bigTotalCount;
            $totalPage = $bigPagination->getPageCount();
            $moreResult = $bigResult;
            $lessResult = $smallResult;
            $more = 'big';

            $succNum = $smallTotalCount;
            $waitNum = $bigTotalCount - $smallTotalCount;
        } else {
            $totalCount = $smallTotalCount;
            $totalPage = $smallPagination->getPageCount();
            $moreResult = $smallResult;
            $lessResult = $bigResult;
            $more = 'small';
            $succNum = $bigTotalCount;
            $waitNum = $smallTotalCount - $bigTotalCount;
        }

        $bigUserIds = ArrayHelper::getColumn($moreResult, 'user_id');
        $smallUserIds = ArrayHelper::getColumn($lessResult, 'user_id');
        $userIds = array_merge($bigUserIds, $smallUserIds);
        $usersBaseInfo = User::baseInfo($userIds);

        $list = [];
        foreach($moreResult as $key => $one) {
            $info = [];
            $first = [];
            $second = [];

            $first['buy_device'] = Period::getSource($one['source']);
            $first['buy_id'] = $one['id'];
            $first['buy_ip'] = long2ip($one['ip']);
            $first['buy_ip_addr'] = Ip::getAddressByIp(long2ip($one['ip']));
            $first['buy_size'] = $one['buy_size'];
            $first['buy_table'] = $one['buy_table'];
            $first['buy_time'] = DateFormat::microDate($one['buy_time']);
            $userBaseInfo = $usersBaseInfo[$one['user_id']];
            $first['user_name'] = $userBaseInfo['username'];
            $first['user_id'] = $userBaseInfo['id'];
            $first['user_home_id'] = $userBaseInfo['home_id'];
            $first['user_avatar'] = $userBaseInfo['avatar'];

            if (isset($lessResult[$key])) {
                $second['buy_device'] = Period::getSource($lessResult[$key]['source']);
                $second['buy_id'] = $lessResult[$key]['id'];
                $second['buy_ip'] = long2ip($lessResult[$key]['ip']);
                $second['buy_ip_addr'] = Ip::getAddressByIp(long2ip($lessResult[$key]['ip']));
                $second['buy_size'] = $lessResult[$key]['buy_size'];
                $second['buy_table'] = $lessResult[$key]['buy_table'];
                $second['buy_time'] = DateFormat::microDate($lessResult[$key]['buy_time']);
                $userBaseInfo = $usersBaseInfo[$lessResult[$key]['user_id']];
                $second['user_name'] = $userBaseInfo['username'];
                $second['user_id'] = $userBaseInfo['id'];
                $second['user_home_id'] = $userBaseInfo['home_id'];
                $second['user_avatar'] = $userBaseInfo['avatar'];
            } else {
                $second['buy_device'] = '';
                $second['buy_id'] = '';
                $second['buy_ip'] = '';
                $second['buy_ip_addr'] = '';
                $second['buy_size'] = '';
                $second['buy_table'] = '';
                $second['buy_time'] = '';
                $second['user_name'] = '';
                $second['user_id'] = '';
                $second['user_home_id'] = '';
                $second['user_avatar'] = '';
            }

            $info['left'] = $more == 'big' ? $first : $second;
            $info['right'] = $more == 'small' ? $first : $second;
            $list[] = $info;
        }

        $return['matchInfo'] = [
            'succ' => (int)$succNum,
            'wait' => (int)$waitNum,
            'bigNum' => (int)$bigTotalCount,
            'smallNum' => (int)$smallTotalCount,
        ];
        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $totalPage;
        return $return;
    }

    /** 某期某人的参与记录
     * @param $userId
     * @param $id
     * @param int $page
     * @param int $perpage
     * @return mixed
     */
    public static function myBuyList($userId, $id, $page = 1, $perpage = 10)
    {
        $periodInfo = PkCurrentPeriodModel::findOne($id);
        if (!$periodInfo) {
            $periodInfo = PkPeriodModel::findOne($id);
        }

        $tableId = $periodInfo->table_id;

        $myTableQuery = PkPeriodBuylistDistribution::findByTableId($tableId)->where(['period_id' => $id, 'user_id' => $userId]);
        $myTableQuery->select(['buy_table', 'buy_size']);
        $myTableResult = $myTableQuery->asArray()->indexBy('buy_table')->all();
        if ($myTableResult) {
            $myTables = array_keys($myTableResult);
            $myTableNum = count($myTables);
            $maxTable = max($myTables);
            $maxTableResult = $myTableResult[$maxTable];
        } else {
            $myTables = [];
            $myTableNum = 0;
            $maxTableResult = [];
        }

        if (!$maxTableResult) {
            $return['matchInfo'] = [
                'succ' => 0,
                'wait' => 0,
            ];
            $return['list'] = [];
            $return['totalCount'] = 0;
            $return['totalPage'] = 0;
            return $return;
        }

        $query = PkPeriodBuylistDistribution::findByTableId($tableId)->where(['period_id' => $id]);
        $query->select([
            'id',
            'product_id',
            'period_id',
            'user_id',
            'buy_size',
            'buy_table',
            'ip',
            'source',
            'buy_time'
        ]);
        $query->andWhere(['buy_table' => $myTables]);


        $bigQuery = clone $query;
        $smallQuery = clone $query;
        $bigQuery->andWhere(['buy_size' => PkCurrentPeriod::BUY_SIZE_BIG]);
        $smallQuery->andWhere(['buy_size' => PkCurrentPeriod::BUY_SIZE_SMALL]);

        $bigCountQuery = clone $bigQuery;
        $bigTotalCount = $bigCountQuery->count();
        $bigPagination = new Pagination(['totalCount' => $bigTotalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);

        $smallCountQuery = clone $smallQuery;
        $smallTotalCount = $smallCountQuery->count();
        $smallPagination = new Pagination(['totalCount' => $smallTotalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);

        $orderBy = 'id asc';
        $bigResult = $bigQuery->orderBy($orderBy)->offset($bigPagination->offset)->limit($bigPagination->limit)->asArray()->all();
        $smallResult = $smallQuery->orderBy($orderBy)->offset($smallPagination->offset)->limit($smallPagination->limit)->asArray()->all();

        if ($maxTableResult['buy_size'] == PkCurrentPeriod::BUY_SIZE_BIG) {
            $totalCount = $bigTotalCount;
            $totalPage = $bigPagination->getPageCount();
            $moreResult = $bigResult;
            $lessResult = $smallResult;
            $more = 'big';
        } else {
            $totalCount = $smallTotalCount;
            $totalPage = $smallPagination->getPageCount();
            $moreResult = $smallResult;
            $lessResult = $bigResult;
            $more = 'small';
        }

        $bigUserIds = ArrayHelper::getColumn($moreResult, 'user_id');
        $smallUserIds = ArrayHelper::getColumn($lessResult, 'user_id');
        $userIds = array_merge($bigUserIds, $smallUserIds);
        $usersBaseInfo = User::baseInfo($userIds);

        $list = [];
        foreach($moreResult as $key => $one) {
            $info = [];
            $first = [];
            $second = [];

            $first['buy_device'] = Period::getSource($one['source']);
            $first['buy_id'] = $one['id'];
            $first['buy_ip'] = long2ip($one['ip']);
            $first['buy_ip_addr'] = Ip::getAddressByIp(long2ip($one['ip']));
            $first['buy_size'] = $one['buy_size'];
            $first['buy_table'] = $one['buy_table'];
            $first['buy_time'] = DateFormat::microDate($one['buy_time']);
            $userBaseInfo = $usersBaseInfo[$one['user_id']];
            $first['user_name'] = $userBaseInfo['username'];
            $first['user_id'] = $userBaseInfo['id'];
            $first['user_home_id'] = $userBaseInfo['home_id'];
            $first['user_avatar'] = $userBaseInfo['avatar'];

            if (isset($lessResult[$key])) {
                $second['buy_device'] = Period::getSource($lessResult[$key]['source']);
                $second['buy_id'] = $lessResult[$key]['id'];
                $second['buy_ip'] = long2ip($lessResult[$key]['ip']);
                $second['buy_ip_addr'] = Ip::getAddressByIp(long2ip($lessResult[$key]['ip']));
                $second['buy_size'] = $lessResult[$key]['buy_size'];
                $second['buy_table'] = $lessResult[$key]['buy_table'];
                $second['buy_time'] = DateFormat::microDate($lessResult[$key]['buy_time']);
                $userBaseInfo = $usersBaseInfo[$lessResult[$key]['user_id']];
                $second['user_name'] = $userBaseInfo['username'];
                $second['user_id'] = $userBaseInfo['id'];
                $second['user_home_id'] = $userBaseInfo['home_id'];
                $second['user_avatar'] = $userBaseInfo['avatar'];
            } else {
                $second['buy_device'] = '';
                $second['buy_id'] = '';
                $second['buy_ip'] = '';
                $second['buy_ip_addr'] = '';
                $second['buy_size'] = '';
                $second['buy_table'] = '';
                $second['buy_time'] = '';
                $second['user_name'] = '';
                $second['user_id'] = '';
                $second['user_home_id'] = '';
                $second['user_avatar'] = '';
            }

            $info['left'] = $more == 'big' ? $first : $second;
            $info['right'] = $more == 'small' ? $first : $second;
            $list[] = $info;
        }


        $userSuccQuery = PkPeriodBuylistDistribution::findByTableId($tableId)->where(['period_id' => $id]);
        $otherBuySize = $more == 'big' ? PkCurrentPeriod::BUY_SIZE_SMALL : PkCurrentPeriod::BUY_SIZE_BIG;
        $userSuccQuery->andWhere(['buy_size' => $otherBuySize]);
        $userSuccQuery->andWhere(['buy_table' => $myTables]);
        $userSuccNum = $userSuccQuery->count();

        $waitNum = $myTableNum - $userSuccNum;
        $return['matchInfo'] = [
            'succ' => (int)$userSuccNum,
            'wait' => (int)$waitNum,
        ];
        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $totalPage;
        return $return;
    }




}