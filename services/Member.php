<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午3:16
 */

namespace app\services;

use app\helpers\Brower;
use app\helpers\DateFormat;
use app\helpers\Ip;
use app\models\ActOrder;
use app\models\Area;
use app\models\CurrentPeriod;
use app\models\ExperienceFollowDistribution;
use app\models\FollowProduct;
use app\models\Friend;
use app\models\Invite;
use app\models\InviteApply;
use app\models\MoneyFollowDistribution;
use app\models\PaymentOrderItemDistribution;
use app\models\PkCurrentPeriod;
use app\models\PkOrders;
use app\models\PkPeriod as PkPeriodModel;
use app\models\PkPeriodBuylistDistribution;
use app\models\PkUserBuylistDistribution;
use app\models\StatsTask;
use app\models\UserTaskFollowDistribution;
use app\models\UserVirtualAddress;
use app\models\WxVirtualAddr;
use app\modules\admin\models\ExchangeOrder;
use app\services\User;
use app\models\UserAddress;
use app\models\Withdraw;
use app\modules\member\models\UserTransferAccount;
use yii;
use app\models\FriendApply;
use app\models\InviteCommission;
use app\models\InviteFriend;
use app\models\Order;
use app\models\PaymentOrderDistribution;
use app\models\PointFollowDistribution;
use app\models\Product as ProductModel;
use app\models\RechargeOrderDistribution;
use app\models\User as UserModel;
use app\models\Image;
use app\models\UserBuylistDistribution;
use yii\base\Object;
use yii\data\Pagination;
use app\models\Period as PeriodModel;
use app\models\PeriodBuylistDistribution;
use yii\helpers\ArrayHelper;
use app\models\GroupTopicComment;
use app\helpers\MyRedis;
use app\services\Pay;

/**
 *  登录用户相关
 * Class Member
 * @package app\services
 */
class Member extends Object
{

    public $id;
    public $homeId;
    public $account;

    public function init()
    {
        $from = Brower::whereFrom();
        if ($this->account) {
            $model = UserModel::find()->select('id,home_id')->where(['phone'=>$this->account, 'from'=>$from])->one();
            if (!$model) {
                $model = UserModel::find()->select('id,home_id')->where(['email'=>$this->account, 'from'=>$from])->one();
            }
            if ($model) {
                $this->id = $model->id;
                $this->homeId = $model->home_id;
            }
        } elseif($this->id) {
            $model = UserModel::find()->select('id,home_id')->where(['id'=>$this->id])->one();
            if ($model) {
                $this->homeId = $model->home_id;
            }
        } elseif ($this->homeId) {
            $model = UserModel::find()->select('id,home_id')->where(['home_id'=>$this->homeId])->one();
            if ($model) {
                $this->id = $model->id;
            }
        }
    }

    /**
     *  获取会员基本信息
     */
    public function getBaseInfo()
    {
        return User::baseInfo($this->id);
    }

    /** 获取会员所有信息
     * @return mixed
     */
    public function getAllInfo()
    {
        return User::allInfo($this->id);
    }

    /** 修改昵称
     * @param $nickname
     * @return int
     */
    public function editNickName($nickname)
    {
        return \app\models\User::updateAll(['nickname'=>$nickname],['id'=>$this->id]);
    }

    /**
     * 会员余额变动
     * @param $money
     * @param $type 1=签到 2=优惠券 3=活动 4=任务 5=充值卡 6=欧洲杯
     * @param $desc
     * @return bool
     * @throws yii\db\Exception
     */
    public function editMoney($money, $type, $desc, $source)
    {
        $user = UserModel::findOne($this->id);
        $money += 0;
        if ($money > 0) {
            $currentMoney = $user['money'] + $money;
        } elseif ($money < 0) {
            if ($user['money'] < -1 * $money) {
                return false;
            }
            $currentMoney = $user['money'] - (-1 * $money);
        } else {
            return true;
        }
        $trans = \Yii::$app->db->beginTransaction();
        try {
            $user->money = $currentMoney;
            if (!$user->save()) {
                $trans->rollBack();
                return false;
            }

            $moneyFollow = new MoneyFollowDistribution($user['home_id']);
            $moneyFollow->user_id = $this->id;
            $moneyFollow->money = $money;
            $moneyFollow->current_money = $currentMoney;
            $moneyFollow->type = $type;
            $moneyFollow->desc = $desc;
            $moneyFollow->created_at = time();
            if (!$moneyFollow->save()) {
                $trans->rollBack();
                return false;
            }

            switch ($type) {
                case 1: //签到
                    $choosePay = new Payway();
                    $result = $choosePay->chooseway($this->id, 'recharge', 'send', 'sign', 0, $money, $source);
                    break;
                case 2: //优惠券
                    $choosePay = new Payway();
                    $result = $choosePay->chooseway($this->id, 'recharge', 'send', 'coupon', 0, $money, $source);
                    break;
                case 3: //活动
                    $choosePay = new Payway();
                    $result = $choosePay->chooseway($this->id, 'recharge', 'send', 'active', 0, $money, $source);
                    break;
                case 4: //任务
                    $choosePay = new Payway();
                    $result = $choosePay->chooseway($this->id, 'recharge', 'send', 'task', 0, $money, $source);
                    break;
                case 5: //充值卡
                    $choosePay = new Payway();
                    $result = $choosePay->chooseway($this->id, 'recharge', 'send', 'card', 0, $money, $source);
                    break;
            }
            if (isset($result)) {
                if ($result['code'] != 100) {
                    $trans->rollBack();
                    return false;
                }
                $updateOrder = new Thirdpay();
                $updateOrder->updateOrder($result['order'], ['status' => 1, 'money' => $money, 'pay_time' => microtime(time())]);
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /** 会员更改福分
     * @param $point 加减福分
     * @param $type 福分变更类型 1=伙购消费，2=成功邀请好友并消费，3=成功晒单，4=晒单评论, 5=完善资料 11=PK消费
     * @param $desc 福分变更描述
     * @param string $flag 购买扣除福分只记录  不执行减福分操作
     * @return bool
     * @throws yii\db\Exception
     */
    public function editPoint($point, $type, $desc, $flag = '')
    {
        $baseInfo = $this->getBaseInfo();
        
        $point += 0;
        if ($point > 0) {
            $currentPoint = $baseInfo['point'] + $point;
        } elseif ($point < 0) {
            $currentPoint = $baseInfo['point'] - (-1 * $point);
        } else {
            return true;
        }
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            if ($type == 4 && $point > 0) {
                $key  = 'point_follow_' . date("Ymd") . '_' . $this->id;
                if (!$this->setKeyValue($key, 100)) {
                    return true;
                }
            }

            if ($flag == 'buy' && $point < 0) {
                $currentPoint = $baseInfo['point'];
            } else {
                UserModel::updateAll(['point' => $currentPoint], ['id' => $this->id]);
            }

            $pointFollow = new PointFollowDistribution($baseInfo['home_id']);
            $pointFollow->user_id = $this->id;
            $pointFollow->point = $point;
            $pointFollow->current_point = $currentPoint;
            $pointFollow->type = $type;
            $pointFollow->desc = $desc;
            $pointFollow->created_at = time();
            $pointFollow->save();
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            file_put_contents('point.err.log', $e->getFile() . ' line:' . $e->getLine() . ' message:' . $e->getMessage() . date('Y-m-d H:i:s') , FILE_APPEND);
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 福分流水列表
     * @param $startTime
     * @param $endTime
     * @param $page
     * @param int $perpage
     * @return mixed
     */
    public function getPointFollowList($startTime, $endTime, $page, $perpage = 20)
    {
        $baseInfo = $this->getBaseInfo();
        $query = PointFollowDistribution::findByUserHomeId($baseInfo['home_id'])->where(['user_id' => $this->id]);

        if ($startTime) {
            $query->andWhere(['>', 'created_at', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'created_at', $endTime]);
        }
        $order = 'id desc';

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $list = $query->orderBy($order)->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    public function changePassword($password)
    {
        $user = UserModel::findOne($this->id);
        if ($user) {
            $user->setPassword($password);
            $save =  $user->save();
            return $save;
        }
        return false;
    }

    /**
     * 修改用户经验
     * @param $experience
     * @param $type
     * @param $desc
     * @return bool
     * @throws yii\db\Exception
     */
    public function editExperience($experience, $type, $desc)
    {
        $baseInfo = $this->getBaseInfo();
        $experience += 0;
        if ($experience > 0) {
            $currentExpr = $baseInfo['experience'] + $experience;
        } elseif ($experience < 0) {
            $currentExpr = $baseInfo['experience'] - (-1 * $experience);
            $currentExpr = $currentExpr < 0 ? 0 : $currentExpr;
        } else {
            return true;
        }

        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            if ($type == 4 && $experience > 0) {
                $key  = 'experience_follow_' . date("Ymd") . '_' . $this->id;
                if (!$this->setKeyValue($key, 100)) {
                    return true;
                }
            }
            UserModel::updateAll(['experience' => $currentExpr], ['id' => $this->id]);
            $pointFollow = new ExperienceFollowDistribution($baseInfo['home_id']);
            $pointFollow->user_id = $this->id;
            $pointFollow->experience = $experience;
            $pointFollow->current_experience = $currentExpr;
            $pointFollow->type = $type;
            $pointFollow->desc = $desc;
            $pointFollow->created_at = time();
            $pointFollow->save();
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /** 经验流水表
     * @param $page
     * @param int $perpage
     */
    public function getExperienceFollowList($page, $perpage = 20)
    {

    }

    /**
     * 购买记录列表 ，伙购纪录
     * @param $startTime
     * @param $endTime
     * @param $page
     * @param int $perpage
     * @param int $status
     * @return mixed
     */
    public function getBuyList($startTime, $endTime, $page, $perpage = 20, $status = -1, $total = 'all')
    {
        $baseInfo = $this->getBaseInfo();
        $homeId = $baseInfo['home_id'];
        $userId = $baseInfo['id'];

        $userBuylistDistribution = new UserBuylistDistribution($homeId);
        $userBuyListTable = $userBuylistDistribution::tableName();
        if ($status != -1) {
            if ($status == 0) { //即将揭晓
                $query = UserBuylistDistribution::findByUserHomeId($homeId)
                    ->innerJoin("((SELECT id FROM current_periods) UNION (SELECT id FROM periods WHERE periods.result_time > ".time()." )) as p", 'p.id = ' . $userBuyListTable . '.period_id')
                    ->select($userBuyListTable . '.*')
                    ->where([$userBuyListTable . '.user_id' => $userId]);
            } elseif ($status == 1) { //已揭晓
                $query = UserBuylistDistribution::findByUserHomeId($homeId)
                    ->innerJoin('periods p', 'p.id = ' . $userBuyListTable . '.period_id')
                    ->select($userBuyListTable . '.*')
                    ->where([$userBuyListTable . '.user_id' => $userId])
                    ->andWhere(['<=', 'p.result_time', time()])
                    ->andWhere(['>', 'p.user_id', 0]);
            } elseif ($status == 2) { //已退购
                $query = UserBuylistDistribution::findByUserHomeId($homeId)
                    ->innerJoin('orders o', 'o.period_id = ' . $userBuyListTable . '.period_id')
                    ->select($userBuyListTable . '.*')
                    ->where([$userBuyListTable . '.user_id' => $userId, 'o.status' => 7]);
            }
            $query->andWhere(['>', $userBuyListTable . '.buy_num', 0]);

            $order = $userBuyListTable . '.buy_time desc';

            if ($startTime) {
                $query->andWhere(['>', $userBuyListTable . '.buy_time', $startTime]);
            }
            if ($endTime) {
                $query->andWhere(['<', $userBuyListTable . '.buy_time', $endTime]);
            }
        } else {
            //$curtime = microtime(true);
            //$select = [$userBuyListTable.".*", "IFNULL(p.end_time, ".$curtime.") as end_time"];
            //$query = UserBuylistDistribution::findByUserHomeId($homeId)->select($select)
            //    ->leftJoin('periods as p', 'p.id='.$userBuyListTable.'.period_id')->where([$userBuyListTable.'.user_id' => $userId]);
            $query = UserBuylistDistribution::findByUserHomeId($homeId)->where(['user_id' => $userId]);
            $query->andWhere(['>', 'buy_num', 0]);
            if ($startTime) {
                $query->andWhere(['>', 'buy_time', $startTime]);
            }
            if ($endTime) {
                $query->andWhere(['<', 'buy_time', $endTime]);
            }
            $order = 'buy_time DESC';
        }

        $countQuery = clone $query;
        if($total == 'all'){
            $totalCount = $countQuery->count();
            $limit = $perpage;
        }else{
            if($total == 'zero') $totalCount = 0;
            else $totalCount = $total;
            $num = $totalCount / $perpage;
            $curpage = ceil($num);
            if($curpage == $page && ($totalCount % $perpage != 0)){
                $limit = $totalCount % $perpage;
                if($limit < 1) $limit = $totalCount;
                else $limit = $limit;
            }else{
                if($totalCount == 0) $limit = 0;
                else $limit = $perpage;
            }
        }

        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);

        $result = $query->orderBy($order)->offset($pagination->offset)->limit($limit)->asArray()->all();

        $buylist = array();
        foreach ($result as $one) {
            $productId = $one['product_id'];
            $periodId = $one['period_id'];
            $buyNum = $one['buy_num'];
            $curPeriodInfo = Product::curPeriod($periodId);
            $info = [];
            if($curPeriodInfo){
                $productInfo = Product::info($productId);
                $info['goods_picture'] = $productInfo['picture'];
                $info['goods_name'] = $productInfo['name'];
                $info['period_number'] = $curPeriodInfo['period_number'];
                $info['period_no'] = $curPeriodInfo['period_no'];
                $info['status'] = 0;
                $info['code_sales'] = $curPeriodInfo['sales_num'];
                $info['progress'] = $curPeriodInfo['progress'];
                $info['left_num'] = $curPeriodInfo['left_num'];
                $info['code_quantity'] = $curPeriodInfo['price'];
                $info['code_price'] = sprintf('%.2f', $curPeriodInfo['price']);
                $info['limit_num'] = $curPeriodInfo['limit_num'];
                $info['buy_unit'] = $curPeriodInfo['buy_unit'];
            }else{
                $info = Period::info($periodId);
                if (!$info) {
                    continue;
                }
                $info['code_sales'] = $info['price'];
                $info['code_quantity'] = $info['price'];
                $info['lucky_code'] = isset($info['lucky_code']) ? $info['lucky_code'] : '';
                $info['code_price'] = sprintf('%.2f', $info['price']);
                $info['limit_num'] = $info['limit_num'];
                $info['buy_unit'] = $info['buy_unit'];

                if ($info['status']==2) {
                    $info['lucky_user_buy_num'] = $info['user_buy_num'];
                    $info['lucky_user_buy_time'] = $info['user_buy_time'];
                }

            }

            $info['user_buy_num'] = $buyNum;
            $info['product_id'] = $productId;
            $info['period_id'] = $periodId;
            $info['user_buy_time'] = $one['buy_time'];
            $buylist[] = $info;
        }

        $return['list'] = $buylist;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['pagination'] = $pagination;
        return $return;
    }

    /** 购买记录详情
     * @param $periodId 期数ID
     */
    public function getBuyDetail($periodId)
    {
        $periodInfo = CurrentPeriod::findOne($periodId);
        if (!$periodInfo) {
            $periodInfo = PeriodModel::findOne($periodId);
        }
        $tableId = $periodInfo->table_id;
        $query = PeriodBuylistDistribution::findByTableId($tableId)->where(['period_id' => $periodId, 'user_id' => $this->id]);
        $result = $query->orderBy('id desc')->asArray()->all();
        return $result;
    }

    /**
     * 换货商品
     * @param $startTime
     * @param $endTime
     * @param $page
     * @param int $perpage
     * @param int $status
     * @param string $total
     * @return mixed
     */
    public function getExchangeOrderList($startTime, $endTime, $page, $perpage = 20, $status = -1, $total = 'all')
    {
        $baseInfo = $this->getBaseInfo();
        $homeId = $baseInfo['home_id'];
        $userId = $baseInfo['id'];
        if ($status != -1) {
            $where['o.status'] = $status;
        }

        $where['o.user_id'] = $this->id;
        $query = ExchangeOrder::find()->select('o.*, exchange_orders.id as ex_id')->leftJoin('orders as o', 'exchange_orders.order_no=o.id')->leftJoin('periods as p', 'o.period_id=p.id')->where($where);

        if ($startTime) {
            $query->andWhere(['>', 'exchange_orders.created_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'exchange_orders.created_time', $endTime]);
        }

        $query->andWhere(['<=','p.result_time', time()]);
        $query->andWhere(['>','p.user_id', 0]);
        $countQuery = clone $query;

        if($total == 'all'){
            $totalCount = $countQuery->count();
            $limit = $perpage;
        }else{
            if($total == 'zero') $totalCount = 0;
            else $totalCount = $total;
            $num = $totalCount / $perpage;
            $curpage = ceil($num);
            if($curpage == $page && ($totalCount % $perpage != 0)){
                $limit = $totalCount % $perpage;
                if($limit < 1) $limit = $totalCount;
                else $limit = $limit;
            }else{
                if($totalCount == 0) $limit = 0;
                else $limit = $perpage;
            }
        }

        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $orders = $query->orderBy('exchange_orders.created_time desc')->offset($pagination->offset)->limit($limit)->asArray()->all();
        $periodIds = ArrayHelper::getColumn($orders, 'period_id');
        $productIds = ArrayHelper::getColumn($orders, 'product_id');
        $periodsInfo = PeriodModel::find()->where(['id' => $periodIds])->indexBy('id')->asArray()->all();
        $productsInfo = ProductModel::find()->where(['id' => $productIds])->indexBy('id')->asArray()->all();
        $list = array();
        foreach ($orders as $order) {
            $productInfo = $productsInfo[$order['product_id']];
            $periodInfo = $periodsInfo[$order['period_id']];
            $userBuyList = UserBuylistDistribution::findByUserHomeId($homeId)
                ->where(['user_id'=>$userId, 'period_id'=>$order['period_id']])
                ->asArray()
                ->one();
            $info = [];
            $info['ex_id'] = $order['ex_id'];
            $info['order_id'] = $order['id'];
            $info['order_no'] = $order['order_no'];
            $info['order_state'] = $order['status'];
            $info['period_id'] = $order['period_id'];
            $info['goods_picture'] = $productInfo['picture'];
            $info['goods_name'] = $productInfo['name'];
            $info['lucky_code'] = $periodInfo['lucky_code'];
            $info['start_time'] = DateFormat::microDate($periodInfo['start_time']);
            $info['end_time'] = DateFormat::microDate($periodInfo['end_time']);
            $info['price'] = sprintf('%.2f', $periodInfo['price']);
            $info['buy_num'] = $userBuyList['buy_num'];
            $info['is_exchange'] = $order['is_exchange'];
            //TODO
            $info['product_type'] = 0;
            $info['goods_id'] = $order['product_id'];
            $info['period_number'] = $periodInfo['period_number'];
            $info['period_no'] = $periodInfo['period_no'];
            $info['raff_time'] = DateFormat::microDate($periodInfo['end_time']);
            $info['allow_share'] = $productInfo['allow_share'];
            $info['limit_num'] = $productInfo['limit_num'];
            $info['status'] = $order['status'];
            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['pagination'] = $pagination;
        return $return;
    }

    /** 获得的商品列表
     * @param $page
     * @param int $perpage
     */
    public function getProductList($startTime, $endTime, $page, $perpage = 20, $status = -1, $total = 'all')
    {
        $baseInfo = $this->getBaseInfo();
        $homeId = $baseInfo['home_id'];
        $userId = $baseInfo['id'];
        if ($status != -1) {
            $where['orders.status'] = $status;
        }

        $where['orders.user_id'] = $this->id;
        $query = Order::find()->select('orders.*')->leftJoin('periods as p', 'orders.period_id=p.id')->where($where);

        if ($startTime) {
            $query->andWhere(['>', 'orders.create_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'orders.create_time', $endTime]);
        }

        $query->andWhere(['<=','p.result_time', time()]);
        $query->andWhere(['>','p.user_id', 0]);
        $countQuery = clone $query;

        if($total == 'all'){
            $totalCount = $countQuery->count();
            $limit = $perpage;
        }else{
            if($total == 'zero') $totalCount = 0;
            else $totalCount = $total;
            $num = $totalCount / $perpage;
            $curpage = ceil($num);
            if($curpage == $page && ($totalCount % $perpage != 0)){
                $limit = $totalCount % $perpage;
                if($limit < 1) $limit = $totalCount;
                else $limit = $limit;
            }else{
                if($totalCount == 0) $limit = 0;
                else $limit = $perpage;
            }
        }

        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $orders = $query->orderBy('orders.create_time desc')->offset($pagination->offset)->limit($limit)->asArray()->all();
        $periodIds = ArrayHelper::getColumn($orders, 'period_id');
        $productIds = ArrayHelper::getColumn($orders, 'product_id');
        $periodsInfo = PeriodModel::find()->where(['id' => $periodIds])->indexBy('id')->asArray()->all();
        $productsInfo = ProductModel::find()->where(['id' => $productIds])->indexBy('id')->asArray()->all();
        $list = array();
        foreach ($orders as $order) {
            $productInfo = $productsInfo[$order['product_id']];
            $periodInfo = $periodsInfo[$order['period_id']];
            $userBuyList = UserBuylistDistribution::findByUserHomeId($homeId)
                ->where(['user_id'=>$userId, 'period_id'=>$order['period_id']])
                ->asArray()
                ->one();
            $info = [];
            $info['order_id'] = $order['id'];
            $info['order_no'] = $order['order_no'];
            $info['order_state'] = $order['status'];
            $info['period_id'] = $order['period_id'];
            $info['goods_picture'] = $productInfo['picture'];
            $info['goods_name'] = $productInfo['name'];
            $info['cost'] = $productInfo['cost'];
            $info['lucky_code'] = $periodInfo['lucky_code'];
            $info['start_time'] = DateFormat::microDate($periodInfo['start_time']);
            $info['end_time'] = DateFormat::microDate($periodInfo['end_time']);
            $info['price'] = sprintf('%.2f', $periodInfo['price']);
            $info['buy_num'] = $userBuyList['buy_num'];
            $info['is_exchange'] = $order['is_exchange'];
            //TODO
            $info['product_type'] = 0;
            $info['goods_id'] = $order['product_id'];
            $info['period_number'] = $periodInfo['period_number'];
            $info['period_no'] = $periodInfo['period_no'];
            $info['raff_time'] = DateFormat::microDate($periodInfo['end_time']);
            $info['allow_share'] = $productInfo['allow_share'];
            $info['limit_num'] = $periodInfo['limit_num'];
            $info['buy_unit'] = $periodInfo['buy_unit'];
            $info['status'] = $order['status'];
            $info['delivery_id'] = $productInfo['delivery_id'];
            $info['face_value'] = $productInfo['face_value'];
            if ($productInfo['delivery_id'] == '3') {
                $info['par_value'] = $productInfo['cost'];
            }
            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['pagination'] = $pagination;
        return $return;
    }

    /** 活动奖品订单列表
     * @param $startTime
     * @param $endTime
     * @param $page
     * @param int $perpage
     * @param int $status
     * @return mixed
     */
    public function getActOrderList($startTime, $endTime, $page, $perpage = 20, $status = -1)
    {
        if ($status != -1) {
            $where['status'] = $status;
        }

        $where['user_id'] = $this->id;
        $query = ActOrder::find()->where($where);

        if ($startTime) {
            $query->andWhere(['>', 'create_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'create_time', $endTime]);
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();

        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $orders = $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $list = array();
        foreach ($orders as $order) {
            $info = [];
            $info['order_id'] = $order['id'];
            $info['status'] = $order['status'];
            $info['goods_picture'] = $order['picture'];
            $actTypeName = ActOrder::$type_name[$order['act_type']];
            $info['goods_name'] = "【".$actTypeName."】".$order['name'];
            $info['goods_type'] = $order['act_type'];
            $info['raff_time'] = DateFormat::microDate($order['create_time']);
            $info['act_obj_id'] = $order['act_obj_id'];
            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['pagination'] = $pagination;
        return $return;

    }

    /** 换货的商品列表
     * @param $page
     * @param int $perpage
     */
    public function getChangeList($page, $perpage = 20)
    {

    }

    /** 晒单列表
     * @param $page
     * @param int $perpage
     */
    public function getShareList( $page, $perpage = 20)
    {
        return Share::getListByType($page, 0, 10, $perpage, $this->id, 0);
    }

    /**
     *  充值纪录
     * @param $page
     * @param $startTime
     * @param $endTime
     * @param int $perpage
     */
    public function getRechargeRecord($startTime, $endTime, $page, $perpage = 20)
    {
        $baseInfo = $this->getBaseInfo();
        $tableId = RechargeOrderDistribution::getTableIdByUserHomeId($baseInfo['home_id']);
        $query = RechargeOrderDistribution::findByTableId($tableId)->where(['user_id' => $this->id]);
        $query->andWhere(['=', 'status', RechargeOrderDistribution::STATUS_PAID]);
        $query->andWhere(['<>', 'money', 0]);

        // 充值总额
        $totalQuery = clone $query;
        $totalMoney = $totalQuery->select('SUM(post_money) as totalMoney')->asArray()->one();

        if ($startTime != '') {
            $query->andWhere(['>', 'pay_time', $startTime]);
        }
        if ($endTime != '') {
            $query->andWhere(['<', 'pay_time', $endTime]);
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('create_time desc');
        $records = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $return['list'] = $records;
        $return['totalMoney'] = intval($totalMoney['totalMoney']);
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 消费纪录
     * @param $page
     * @param $startTime
     * @param $endTime
     * @param int $perpage
     */
    public function getPayRecord($startTime, $endTime, $page, $perpage = 20)
    {
        $baseInfo = $this->getBaseInfo();
        $tableId = PaymentOrderDistribution::getTableIdByUserHomeId($baseInfo['home_id']);
        $query = PaymentOrderDistribution::findByTableId($tableId)->where(['user_id' => $this->id]);
        $query->andWhere(['=', 'status', PaymentOrderDistribution::STATUS_PAID]);
        $query->andWhere(['<>', 'money', 0]);

        // 消费总额
        $totalQuery = clone $query;
        $totalMoney = $totalQuery->select('SUM(money) as totalMoney')->asArray()->one();

        if ($startTime != '') {
            $query->andWhere(['>', 'buy_time', $startTime]);
        }
        if ($endTime != '') {
            $query->andWhere(['<', 'buy_time', $endTime]);
        }


        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('create_time desc');
        $records = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $return['list'] = $records;
        $return['totalMoney'] = intval($totalMoney['totalMoney']);
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 关注的商品列表
     * @param $page
     * @param int $perpage
     */
    public function getFollowProductList($page, $perpage = 20)
    {
        $query = FollowProduct::find()->where(['user_id' => $this->id]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('id desc');
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $list = array();
        foreach ($result as $one) {
            $productInfo = Product::info($one['product_id']);
            $periodInfo = Product::curPeriodInfo($productInfo['id']);
            $info = [];
            $info['goods_id'] = $productInfo['id'];
            $info['goods_name'] = $productInfo['name'];
            $info['goods_picture'] = $productInfo['picture'];
            $info['period_id'] = $periodInfo['id'];
            $info['period_number'] = $periodInfo['period_number'];
            $info['period_no'] = $periodInfo['period_no'];
            $info['limit_buy'] = $periodInfo['limit_num'] <= 0 ? 0 : 1;
            $info['quantity'] = $periodInfo['price'];
            $info['sales'] = $periodInfo['sales_num'];
            $info['is_sale'] = $productInfo['marketable'];

            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     * 加入的圈子
     */
    public function getJoinedGroups()
    {

    }

    /** 发表的话题列表
     * @param $page
     * @param int $perpage
     */
    public function getTopicList($page, $perpage = 20)
    {

    }

    /** 发表的话题回复列表
     * @param $page
     * @param int $perpage
     */
    public function getTopicCommentList($page, $perpage = 20)
    {

    }

    /** 邀请的列表
     * @param $page
     * @param int $perpage
     */
    public function getInvitedList($page, $perpage = 20)
    {
        $query = Invite::find()->where(['user_id'=>$this->id]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new yii\data\Pagination(['totalCount'=>$totalCount, 'page'=>$page-1, 'defaultPageSize'=>$perpage]);
        $query->orderBy('id desc');
        $invite = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $userIds = yii\helpers\ArrayHelper::getColumn($invite, 'invite_uid');
        $usersBaseInfo = User::baseInfo($userIds);
        foreach ($invite as &$one) {
            $userBaseInfo = $usersBaseInfo[$one['invite_uid']];
            $one['user_nickname'] = $userBaseInfo['username'];
            $one['user_home_id'] = $userBaseInfo['home_id'];
            $one['user_avatar'] = $userBaseInfo['avatar'];
            $one['invite_time'] = date('Y-m-d H:i:s',$one['invite_time']);
        }

        $return['list'] = $invite;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 佣金明细列表
     * @param $page
     * @param int $perpage
     */
    public function getCommissionList($type, $startTime, $endTime, $page, $perpage = 20)
    {
        $query = InviteCommission::find()->where(['user_id'=>$this->id]);
        if ($startTime) {
            $query->andWhere(['>', 'created_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'created_time', $endTime]);
        }
        if ($type!=-1) {
            if ($type==InviteCommission::TYPE_PAY) {
                $query->andWhere(['=', 'type', InviteCommission::TYPE_PAY]);
            }elseif ($type==InviteCommission::TYPE_RECHARGE) {
                $query->andWhere(['=', 'type', InviteCommission::TYPE_RECHARGE]);
            }elseif ($type==InviteCommission::TYPE_WITHDRAW) {
                $query->andWhere(['=', 'type', InviteCommission::TYPE_WITHDRAW]);
            }
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new yii\data\Pagination(['totalCount'=>$totalCount, 'page'=>$page-1, 'defaultPageSize'=>$perpage]);
        $query->orderBy('id desc');
        $commissionList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $userIds = yii\helpers\ArrayHelper::getColumn($commissionList, 'action_user_id');
        $usersBaseInfo = User::baseInfo($userIds);
        foreach ($commissionList as &$one) {
            $userBaseInfo = $usersBaseInfo[$one['action_user_id']];
            $one['user_nickname'] = $userBaseInfo['username'];
            $one['user_home_id'] = $userBaseInfo['home_id'];
            $one['user_avatar'] = $userBaseInfo['avatar'];
            $one['commission'] = sprintf('%.2f', $one['commission'] / 100);
            if ($one['type'] == InviteCommission::TYPE_PAY) {
                $desc = unserialize($one['desc']);
                $periodId = $desc['periodId'];
                $info = CurrentPeriod::findOne($periodId);
                if ($info) {
                    $periodNumber = $info->period_number;
                    $productInfo = \app\models\Product::findOne($info->product_id);
                    $productName = $productInfo->name;
                } else {
                    $info = \app\models\Period::findOne($periodId);
                    $periodNumber = $info->period_number;
                    $productInfo = \app\models\Product::findOne($info->product_id);
                    $productName = $productInfo->name;
                }

                $one['desc'] = '<a target="_blank" href="'.yii\helpers\Url::to(['/product/lottery', 'pid'=>$periodId]).'">(第' . $periodNumber . '期)'.$productName.'</a>';
            } elseif($one['type'] == InviteCommission::TYPE_WITHDRAW) {
                $desc = unserialize($one['desc']);
                $bank = $desc['bank'];
                $bankNumber = $desc['bank_number'];
                $one['desc'] = '用户佣金提取到银行账户(' . $bank . ' ' . $bankNumber . ')';
            }
            if ($one['type'] == InviteCommission::TYPE_PAY) {
                $one['type'] = '收入';
            } elseif ($one['type'] == InviteCommission::TYPE_RECHARGE) {
                $one['type'] = '充值到账户';
            } elseif ($one['type'] == InviteCommission::TYPE_WITHDRAW) {
                $one['type'] = '提现';
            }
        }

        $return['list'] = $commissionList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 提现纪录
     * @param $page
     * @param $startTime
     * @param $endTime
     * @param int $perpage
     */
    public function getWithdrawList($startTime, $endTime, $page, $perpage = 20)
    {
        $query = Withdraw::find()->where(['user_id' => $this->id]);
        if ($startTime) {
            $query->andWhere(['>', 'apply_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'apply_time', $endTime]);
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('id desc');
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $return['list'] = $result;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }


    /** 好友列表
     * @param $page
     * @param int $perpage
     */
    public function getFirends($page, $perpage = 20, $user_id = '')
    {
        if($user_id){
            $query = Friend::find()->where(['user_id'=>$user_id])->andWhere('friend_userid!='.$user_id);
        }else{
            $query = Friend::find()->where(['user_id'=>$this->id])->andWhere('friend_userid!='.$this->id);
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('id desc');
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $userIds = ArrayHelper::getColumn($result, 'friend_userid');
        $usersInfo = User::allInfo($userIds);

        $list = [];
        foreach ($result as $one) {
            $userInfo = $usersInfo[$one['friend_userid']];

            $info = [];
            $info['user_id'] = $userInfo['id'];
            $info['user_name'] = $userInfo['username'];
            $info['user_home_id'] = $userInfo['home_id'];
            $info['friend_userid'] = $one['friend_userid'];
            $info['user_avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 160);
            //TODO
            $info['grade_pic'] = $userInfo['level']['pic']; //等级图片
            $info['grade_name'] = $userInfo['level']['name'];//等级名称
            $info['live'] = $userInfo['hometown'];
            $info['address'] = Ip::getAddressByIp(long2ip($userInfo['last_login_ip']));//地址
            $info['intro'] = $userInfo['intro'];//简介

            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['pagination'] = $pagination;
        return $return;
    }

    /**
     * 好友查找
     * @param $arr
     */
    public function findFriendList($arr)
    {
        $userIds = ArrayHelper::getColumn($arr, 'id');
        $usersInfo = User::allInfo($userIds);

        $list = [];
        foreach ($arr as $one) {
            $userInfo = $usersInfo[$one['id']];
            $existFriend = Friend::findOne(['and', 'user_id'=>Yii::$app->user->id, 'friend_userid'=>$one['id']]);
            $info = [];
            $info['user_id'] = $userInfo['id'];
            $info['user_name'] = User::baseInfo($userInfo['id']);
            $info['user_home_id'] = $userInfo['home_id'];
            $info['user_avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 160);
            $info['friend'] = $existFriend;
            //TODO
            $info['grade'] = ''; //经验等级
            $info['grade_name'] = $userInfo['level']['name'];//等级名称
            $info['address'] = $userInfo['hometown'];//地址
            $info['intro'] = $userInfo['intro'];//简介
            $list[] = $info;
        }

        return $list;
    }

    /**
     * 好友查找
     * status 0随机推送好友，1获得商品最多好友，2最活跃好友，3最新加入好友
     */
    public function randomFriend($status = 0,$userBase = 0, $limit = 8)
    {
        $conn = \Yii::$app->db;
        $list = [];
        if($status == 0 || $status == 3){
            if($status == 0){
                $command = $conn->createCommand("SELECT * FROM `users` AS a JOIN (SELECT MAX(ID) AS ID FROM `users`) AS b ON (a.ID >= FLOOR( b.ID*RAND() ) + ".$userBase." )  LIMIT ".$limit." ");
            }elseif($status == 3){
                $command = $conn->createCommand("select * from users order by id desc limit ".$limit);
            }
            $find = $command->queryAll();

            foreach ($find as $key => $one) {
                $userInfo = User::allInfo($one['id']);
                $username = User::baseInfo($one['id']);
                $existFriend = Friend::findOne(['and', 'user_id='.Yii::$app->user->id, 'friend_userid='.$one['id']]);
                $avatar = Image::getUserFaceUrl($userInfo['avatar'], 160);
                $list[$key]['user_id'] = $one['id'];
                $list[$key]['username'] = $username['username'];
                $list[$key]['user_home_id'] = $userInfo['home_id'];
                $list[$key]['user_avatar'] = $avatar;
                $list[$key]['friend'] = $existFriend['id'];
                //TODO
                $list[$key]['grade'] = ''; //经验等级
                $list[$key]['grade_name'] = $userInfo['level']['name'];//等级名称
                $list[$key]['address'] = $userInfo['hometown'];//地址
                $list[$key]['intro'] = $userInfo['intro'];//简介
            }
        }elseif($status == 1 || $status == 2){
            if($status == 1){
                $command = $conn->createCommand("select *,count(*) as num from orders group by user_id order by num desc limit ".$limit);
                $find = $command->queryAll();
            }elseif($status == 2){
                $find = GroupTopicComment::activeUser($limit);
            }

            foreach ($find as $key => $one) {
                $userInfo = User::allInfo($one['user_id']);
                $username = User::baseInfo($one['user_id']);
                $avatar = Image::getUserFaceUrl($userInfo['avatar'], 160);
                $existFriend = Friend::findOne(['and', 'user_id='.Yii::$app->user->id, 'friend_userid='.$one['user_id']]);
                $list[$key]['user_id'] = $one['user_id'];
                $list[$key]['username'] = $username['username'];
                $list[$key]['user_home_id'] = $userInfo['home_id'];
                $list[$key]['user_avatar'] = $avatar;
                $list[$key]['friend'] = $existFriend;
                //TODO
                $list[$key]['grade'] = ''; //经验等级
                $list[$key]['grade_name'] = $userInfo['level']['name'];//等级名称
                $list[$key]['address'] = $userInfo['hometown'];//地址
                $list[$key]['intro'] = $userInfo['intro'];//简介
            }
        }

        return $list;
    }

    /** 好友请求
     * @param $page
     * @param int $perpage
     */
    public function getFirendsApply($page, $perpage = 10)
    {
        $query = FriendApply::find()->where(['apply_userid'=>$this->id, 'status'=>0]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('id desc');
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $userIds = ArrayHelper::getColumn($result, 'user_id');
        $usersInfo = User::allInfo($userIds);

        $list = [];
        foreach ($result as $one) {
            $userInfo = $usersInfo[$one['user_id']];
            $info = [];
            $info['apply_id'] = $one['id'];
            $info['user_id'] = $userInfo['id'];
            $info['user_name'] = User::baseInfo($userInfo['id']);
            $info['user_home_id'] = $userInfo['home_id'];
            $info['user_avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 80);
            //TODO
            $info['grade'] = ''; //经验等级
            $info['grade_name'] = $userInfo['level'];//等级名称
            $info['address'] = Ip::getAddressByIp(long2ip($userInfo['last_login_ip']));//地址
            $info['intro'] = $userInfo['intro'];//简介
            $info['apply_time'] = DateFormat::formatTime($one['apply_time']);
            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['pagination'] = $pagination;
        return $return;
    }

    /** 系统消息
     * @param $page
     * @param int $perpage
     */
    public function getSystemMsg($page, $perpage = 20)
    {

    }

    /** 私信
     * @param $page
     * @param int $perpage
     */
    public function getPrivateMsg($page, $perpage = 20)
    {

    }

    /** 收货地址
     * @param $page
     * @param int $perpage
     */
    public function getAddressList($page, $perpage = 20)
    {
        $query = UserAddress::find()->where(['uid'=>$this->id]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('id desc');
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        foreach ($result as &$r) {
            $address = Area::find()->where(['id' => [$r['prov'], $r['city'], $r['area']]])->indexBy('id')->asArray()->all();
            $r['provName'] = $address[$r['prov']]['name'];
            $r['cityName'] = $address[$r['city']]['name'];
            $r['areaName'] = $address[$r['area']]['name'];
        }

        $return['list'] = $result;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 虚拟物品收货地址
     * @param $page
     * @param int $perpage
     */
    public function getVirtualAddressList($type, $page, $perpage = 20)
    {
        $query = UserVirtualAddress::find()->where(['user_id'=>$this->id]);

        $types = explode(',', $type);//dh,wx,tb,qb
        if ($type) {
            $query->andWhere(['type'=>$types]);
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('id desc');
        $result = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        foreach($result as &$row)
        {
            if($row['type']=='wx')
            {
              $wx_info=WxVirtualAddr::find()->where(['virtual_addr_id' => $row['id']])->one();;
                $row['nickname']=$wx_info['nickname'];
                $row['headimg']=$wx_info['headimg'];
            }
        }

        $return['list'] = $result;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     *  隐私设置信息
     */
    public function getPrivacySettings()
    {

    }

    /**
     *  接收消息设置
     */
    public function getNoticeSettings()
    {


    }

    /**
     * 转账记录
     * @param $startTime
     * @param $endTime
     * @param $page
     * @param int $perpage
     * @return mixed
     */
    public function getTransferRecord($startTime, $endTime, $page, $perpage = 20)
    {
        $query = UserTransferAccount::find()->where(['user_id' => $this->id])->orWhere(['to_userid' => $this->id]);
        if ($startTime != '') {
            $query->andWhere(['>', 'created_at', $startTime]);
        }
        if ($endTime != '') {
            $query->andWhere(['<', 'created_at', $endTime]);
        }
        $query->andWhere(['<>', 'account', 0]);

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $query->orderBy('created_at desc');
        $records = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        foreach ($records as &$record) {
            if ($record['user_id'] == $this->id) {
                $record['payment'] = "转出";
                $userInfo = UserModel::findOne(['id' => $record['to_userid']]);
                $record['to_account'] = $userInfo['phone'] ? User::privatePhone($userInfo['phone']) : User::privateEmail($userInfo['email']);
                $record['money'] = '-' . $record['account'];
            } else {
                $record['payment'] = "转入";
                $userInfo = UserModel::findOne(['id' => $record['user_id']]);
                $record['to_account'] = $userInfo['phone'] ? User::privatePhone($userInfo['phone']) : User::privateEmail($userInfo['email']);
                $record['money'] = $record['account'];
            }
        }

        $totalOutQuery = UserTransferAccount::find()->where(['user_id' => $this->id])->select('SUM(account) as totalMoney')->asArray()->one();
        $totalInQuery = UserTransferAccount::find()->where(['to_userid' => $this->id])->select('SUM(account) as totalMoney')->asArray()->one();

        $return['list'] = $records;
        $return['totalInMoney'] = intval($totalInQuery['totalMoney']);
        $return['totalOutMoney'] = intval($totalOutQuery['totalMoney']);
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    //本周最火达人
    public function hotOrderList($limit = 100)
    {
        //本周时间
        $date=date('Y-m-d');
        $first=1;
        $w=date('w',strtotime($date));
        $now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days'));
        $now = strtotime($now_start);
        $now_end=date('Y-m-d',strtotime("$now_start +7 days"));
        $end = strtotime($now_end);

        $conn = \Yii::$app->db;
        $command = $conn->createCommand("select *,count(*) as num from orders where create_time >=".$now." and create_time <=".$end." group by user_id order by num desc limit ".$limit);
        $find = $command->queryAll();
        $count = count($find);

        $arr = [];
        if($count > 0){
            if($count < 4){
                $arr = array_rand($find, $count);
            }else{
                $arr = array_rand($find, 4);
            }
        }

        $return = [];
        if($count == 1){
            $return[$arr] = $find[$arr];
        }else{
            foreach($arr as $key){
                $return[$key] = $find[$key];
            }
        }

        $returnArr = [];
        foreach ( $return as $key => $val ) {
            $userInfo = User::allInfo($val['user_id']);
            $baseInfo = User::baseInfo($val['user_id']);
            $avatar = Image::getUserFaceUrl($baseInfo['avatar'], 80);
            $existFriend = Friend::findOne(['user_id'=>$this->id, 'friend_userid'=>$val['user_id']]);
            $returnArr[$key]['user_id'] = $val['user_id'];
            $returnArr[$key]['home_id'] = $userInfo['home_id'];
            $returnArr[$key]['username'] = $baseInfo['username'];
            $returnArr[$key]['user_avatar'] = $avatar;
            $returnArr[$key]['grade_name'] = $baseInfo['level'];
            $returnArr[$key]['friend'] = $existFriend['id'];
            if($val['user_id'] == Yii::$app->user->id) $self = 1;
            else $self = 0;
            $returnArr[$key]['self'] = $self;
        }

        return $returnArr;
    }

    private function setKeyValue($key, $max)
    {
        $value = Yii::$app->cache->get($key);
        if ($value && $value >= $max) {
            return false;
        }
        $value = intval($value) + 1;
        $duration = strtotime(date("y-m-d", strtotime("+1 day"))) - 1 - time();
        Yii::$app->cache->set($key, $value, $duration);
        return true;
    }

    /**
     * 圈子热门话题 24小时内回复最多的5个话题
     * @param int $limit
     * @return array|yii\db\ActiveRecord[]
     */
    public function getTopic($limit = 5){
        $query = GroupTopicComment::find();
        $query->select(['topic_id', 'COUNT(*) as comment_num', 'g.*'])->groupBy('topic_id')->leftJoin('group_topics g', 'group_topic_comments.topic_id=g.id');
        $query->andWhere(['group_topic_comments.status' => 1]);
        $query->andWhere(['>', 'group_topic_comments.created_at', strtotime('-1 day')]);

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]);

        $result = $query->orderBy('comment_num DESC')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        return $result;
    }

    /**
     * 会员任务日志
     * @param $content
     * @param $source
     * @param $type
     * @param int $level
     * @param int $cate
     * @param int $num
     * @return bool
     */
    public function taskLog($content, $source, $type, $level = 0, $cate = 0, $num = 0)
    {
        $user = UserModel::findOne($this->id);

        $trans = Yii::$app->db->beginTransaction();
        try {
            $moneyFollow = new UserTaskFollowDistribution($user['home_id']);
            $moneyFollow->user_id = $this->id;
            $moneyFollow->content = $content;
            $moneyFollow->source = $source;
            $moneyFollow->type = $type;
            $moneyFollow->level = $level;
            $moneyFollow->cate = $cate;
            $moneyFollow->num = $num;
            $moneyFollow->created_at = time();

            if (!$moneyFollow->save()) {
                $trans->rollBack();
                return false;
            }

//            $date = date("Ymd");
//            $statsTask = StatsTask::findOne(['date' => $date, 'type' => $type, 'level' => $level, 'cate' => $cate, 'num' => $num]);
//            if ($statsTask) {
//                $statsTask->count += 1;
//                if (!$statsTask->save()) {
//                    $trans->rollBack();
//                    return false;
//                }
//            } else {
//                $statsTask = new StatsTask();
//                $statsTask->date = $date;
//                $statsTask->type = $type;
//                $statsTask->level = $level;
//                $statsTask->cate = $cate;
//                $statsTask->num = $num;
//                $statsTask->count = 1;
//                if (!$statsTask->save()) {
//                    $trans->rollBack();
//                    return false;
//                }
//            }

            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /** 用户PK纪录
     * @param int $page
     * @param int $perpage
     */
    public function getPkBuyList($page = 1, $perpage = 10,$status=0)
    {
        $query = PkUserBuylistDistribution::findByUserHomeId($this->homeId);
        $query->where(['user_id' => $this->id]);
        if($status)
        {
            $query->where(['status'=>0]);
        }
        $orderBy = 'buy_time desc';
        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);

        $result = $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $productIds = ArrayHelper::getColumn($result, 'product_id');
        $productsInfo = PkProduct::info($productIds);

        $list = [];
        foreach($result as $one) {
            $info = [];
            $prodcutId = $one['product_id'];
            $periodId = $one['period_id'];
            $buyTable = $one['buy_table'];
            $buySize = $one['buy_size'];
            $prodcutInfo = $productsInfo[$prodcutId];

            $periodInfo = PkCurrentPeriod::findOne($periodId);
            $orderProductInfo = [];
            $orderStatusText = '';
            if ($periodInfo) {
                $getStatus = 0;
                $getStatusText = '等待揭晓';
                $orderStatus = -1;
                $matchText = '等待';
            } else {
                $periodInfo = PkPeriodModel::findOne($periodId);
                $order = PkOrders::find()->where(['user_id' => $this->id, 'period_id' => $periodId, 'size'=> $buySize, 'desk_id' => $buyTable])->one();
                $getStatus = $order ? 1 : 2;
                $getStatusText = $order ? '成功获得' : '未获得';
                $orderStatus = $order ? $order['status'] : -2;

                if ($order) {
                    $prodcutInfo['status'] = $order['status'];
                    $orderProductInfo = PkOrder::productInfo($prodcutInfo, $this->id);
                    $orderStatusText = $orderProductInfo['status_name'];
                    $orderProductInfo['order_id'] = $order['id'];
                }

                if (!$order) {
                    $oppositeBuySize = $buySize == PkCurrentPeriod::BUY_SIZE_BIG ? PkCurrentPeriod::BUY_SIZE_SMALL : PkCurrentPeriod::BUY_SIZE_BIG;
                    $oppositeWhere = ['period_id' => $periodId, 'buy_table' => $buyTable, 'buy_size' => $oppositeBuySize];
                    $oppositeBuy = PkPeriodBuylistDistribution::findByTableId($periodInfo['table_id'])->where($oppositeWhere)->one();
                    $matchText = $oppositeBuy ? '成功' : '失败';
                } else {
                    $matchText = '成功';
                }

            }

            $info['period_id'] = $periodInfo['id'];
            $info['goods_id'] = $prodcutInfo['id'];
            $info['goods_name'] = $prodcutInfo['name'];
            $info['goods_picture'] = $prodcutInfo['picture'];
            $info['period_no'] = $periodInfo['period_no'];
            $info['price'] = $periodInfo['price'];
            $info['match_text'] = $matchText;

            $info['get_status'] = $getStatus;
            $info['get_status_text'] = $getStatusText;
            $info['order_status'] = $orderStatus;
            $info['order_status_text'] = $orderStatusText;
            $info['desk_id'] = $buyTable;
            $info['buy_size'] = $buySize;
            if ($orderProductInfo) {
                $info['order_product_info'] = $orderProductInfo;
            }

            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 用户PK幸运纪录
     * @param int $page
     * @param int $perpage
     * @return mixed
     */
    public function getPkLuckList($startTime, $endTime,$page = 1, $perpage = 10,$status = -1)
    {
        if ($status != -1)$where['status'] = $status;

        $where['user_id'] = $this->id;
        $query = PkOrders::find()->where($where);
        if ($startTime) {
            $query->andWhere(['>', 'create_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'create_time', $endTime]);
        }
        $orderBy = 'id desc';
        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);

        $result = $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $periodIds = ArrayHelper::getColumn($result,'period_id');
        $productIds = ArrayHelper::getColumn($result,'product_id');

        $periodsInfo = PkPeriodModel::findAll(['id' => $periodIds]);
        $periodsInfo = ArrayHelper::index($periodsInfo, 'id');
        $productsInfo = PkProduct::info($productIds);

        $list = [];
        foreach($result as $one) {
            $info = [];
            $prodcutInfo = $productsInfo[$one['product_id']];
            $periodInfo = $periodsInfo[$one['period_id']];
            $info['period_id'] = $periodInfo['id'];
            $info['goods_id'] = $prodcutInfo['id'];
            $info['goods_name'] = $prodcutInfo['name'];
            $info['goods_picture'] = $prodcutInfo['picture'];
            $info['period_no'] = $periodInfo['period_no'];
            $info['price'] = $periodInfo['price'];
            $info['match_text'] = '成功';

            $info['desk_id'] = $one['desk_id'];
            $info['buy_size'] = $one['size'];

            $prodcutInfo['status'] = $one['status'];
            $orderProductInfo = PkOrder::productInfo($prodcutInfo, $this->id);
            $orderProductInfo['order_id'] = $one['id'];
            $info['order_product_info'] = $orderProductInfo;
            $info['get_status'] = 1;
            $info['get_status_text'] = '成功获得';
            $info['order_status'] = $one['status'];
            $info['order_status_text'] = $orderProductInfo['status_name'];

            $list[] = $info;
        }

        $return['list'] = $list;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }




}