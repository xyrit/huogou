<?php

namespace app\modules\admin\controllers;

use app\helpers\MyRedis;
use app\models\Product;
use app\modules\admin\models\PointLog;
use yii;
use app\models\Order;

class DefaultController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionIndexPage()
    {
        $conn = \Yii::$app->db;
        $return = [];

        //总收入
        $sql = '';
        for( $i=0;$i<10;$i++){
            $tableId = '10'.$i;
            if($i == 9){
                $sql .= '(SELECT sum(buy_num) as buy_num FROM period_buylist_'.$tableId.') ';
            }else{
                $sql .= '(SELECT sum(buy_num) as buy_num FROM period_buylist_'.$tableId.' ) union all';
            }
        }
        $incomesql = $conn->createCommand('SELECT sum(buy_num) as total FROM ('.$sql.') as t ');
        $incomeTotal = $incomesql->queryOne();
        $return['incomeTotal'] = $incomeTotal['total'];

        //总充值
        $totalrecharge = '';
        for( $i=0;$i<10;$i++){
            $tableId = '10'.$i;
            if($i == 9){
                $totalrecharge .= '(SELECT sum(money) as money FROM recharge_orders_'.$tableId.' where status = 1 ) ';
            }else{
                $totalrecharge .= '(SELECT sum(money) as money FROM recharge_orders_'.$tableId.' where status = 1 ) union all';
            }
        }
        $moneysql = $conn->createCommand('SELECT sum(money) as total FROM ('.$totalrecharge.') as t ');
        $moneyTotal = $moneysql->queryOne();
        $return['moneyTotal'] = $moneyTotal['total'];

        //账户余额
        $balsql = $conn->createCommand('SELECT sum(money) as balance FROM users ');
        $balance = $balsql->queryOne();
        $return['balance'] = $balance['balance'];

        //佣金余额
        $commsql = $conn->createCommand('SELECT sum(commission) as comm FROM users ');
        $comm = $commsql->queryOne();
        $return['comm'] = $comm['comm'] / 100;

        //福分消费
        $return['totalComsue'] = PointLog::point('point < 0');
        $return['comTotal'] = PointLog::point('type = 1 and point > 0');
        $return['taskTotal'] = PointLog::point('type = 5');
        $return['inviteTotal'] = PointLog::point('type = 2');
        $return['shareTotal'] = PointLog::point('type = 3');
        $return['modifyTotal'] = PointLog::point('type = 6');

        //福分余额
        $pointsql = $conn->createCommand('select sum(point) as totalPoint from users');
        $point = $pointsql->queryOne();
        $return['totalPoint'] = $point['totalPoint'];

        //一级分类
        $catesql = $conn->createCommand('SELECT count(1) as total FROM product_category where parent_id=0 and top_id = 0 ');
        $cateTotal = $catesql->queryOne();
        $return['cateTotal'] = $cateTotal['total'];

        //品牌
        $brandsql = $conn->createCommand('SELECT count(1) as total FROM brands ');
        $brandTotal = $brandsql->queryOne();
        $return['brandTotal'] = $brandTotal['total'];

        //商品总数量
        $productsql = $conn->createCommand('SELECT count(1) as total FROM products ');
        $productTotal = $productsql->queryOne();
        $return['productTotal'] = $productTotal['total'];

        //在售商品数量
        $onlinesql = $conn->createCommand('SELECT count(1) as total FROM products where marketable = 1');
        $onlineTotal = $onlinesql->queryOne();
        $return['onlineTotal'] = $onlineTotal['total'];

        //会员数量
        $usersql = $conn->createCommand('SELECT count(1) as total FROM users');
        $userTotal = $usersql->queryOne();
        $return['userTotal'] = $userTotal['total'];

        //今日新增会员
        $start = strtotime(date('Y-m-d',time()));
        $end = strtotime(date("Y-m-d",strtotime("+1 day")));
        $toadysql = $conn->createCommand('SELECT count(1) as total FROM users where created_at >= "'.$start.'" and created_at < "'.$end.'"');
        $toadyTotal = $toadysql->queryOne();
        $return['toadyTotal'] = $toadyTotal['total'];

        //今日收入
        $sql = '';
        for( $i=100;$i<110;$i++){
            if($i == 109){
                $sql .= '(SELECT sum(buy_num) as buy_num FROM period_buylist_'.$i.' where buy_time >= "'.$start.'" and buy_time < "'.$end.'") ';
            }else{
                $sql .= '(SELECT sum(buy_num) as buy_num FROM period_buylist_'.$i.' where buy_time >= "'.$start.'" and buy_time < "'.$end.'" ) union all';
            }
        }
        $todayincomesql = $conn->createCommand('SELECT sum(buy_num) as total FROM ('.$sql.') as t ');
        $todayIncomeTotal = $todayincomesql->queryOne();
        $return['todayIncomeTotal'] = $todayIncomeTotal['total'];

        //今日充值
        $sql = '';
        for( $i=100;$i<110;$i++){
            if($i == 109){
                $sql .= '(SELECT sum(money) as money FROM recharge_orders_'.$i.' where status = 1 and pay_time >= "'.$start.'" and pay_time < "'.$end.'") ';
            }else{
                $sql .= '(SELECT sum(money) as money FROM recharge_orders_'.$i.' where status = 1 and pay_time >= "'.$start.'" and pay_time < "'.$end.'" ) union all';
            }
        }
        $rechargesql = $conn->createCommand('SELECT sum(money) as total FROM ('.$sql.') as t ');
        $rechargeTotal = $rechargesql->queryOne();
        $return['rechargeTotal'] = $rechargeTotal['total'];

        //今日开奖
        $luckysql = $conn->createCommand('SELECT count(1) as total FROM orders where create_time >="'.$start.'" and create_time < "'.$end.'"');
        $luckyTotal = $luckysql->queryOne();
        $return['luckyTotal'] = $luckyTotal['total'];

        //今日发货
        $deliversql = $conn->createCommand('SELECT count(1) as total FROM deliver where deliver_time >="'.$start.'" and deliver_time < "'.$end.'"');
        $deliverTotal = $deliversql->queryOne();
        $return['deliverTotal'] = $deliverTotal['total'];

        //订单统计
        $order = Order::orderStatusCount();

        //热销商品
        // $hotsql = $conn->createCommand('SELECT DISTINCT product_id from (select * FROM periods Order by period_number DESC) tmp GROUP BY product_id Order by period_number DESC LIMIT 10');
        // $hotProductList = $hotsql->queryAll();
        // foreach($hotProductList as $key => $val){
        //     $maxperiod = $conn->createCommand('select max(period_number) as period_number from periods where product_id = '.$val['product_id'].'');
        //     $max = $maxperiod->queryOne();
        //     $product = Product::findOne($val['product_id']);
        //     $hotProductList[$key]['product_id'] = $product['name'];
        //     $hotProductList[$key]['period_number'] = $max['period_number'];
        // }
        $redis = new MyRedis();
        $yesterday = $redis->hget('YESTERDAY_COUNT_'.date('Y-m-d'), 'all');

        return $this->render('indexPage', [
            'return' => $return,
            'order' => $order,
            'yesterday' => $yesterday,
            'hotProductList' => '',
        ]);
    }

    public function actionLogout()
    {
        $adminUser = \Yii::$app->admin;
        $adminUser->logout();
        $adminUser->setReturnUrl(['/admin']);
        return $this->redirect($adminUser->loginUrl);
    }

    public function actionYesterday()
    {
        $redis = new MyRedis();
        $name = 'YESTERDAY_COUNT_'.date('Y-m-d');
        if(!$redis->isexist($name)){
            $conn = Yii::$app->db;
            $yend = strtotime(date('Y-m-d',time()));
            $ystart = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $ysql = $conn->createCommand('SELECT count(1) as total FROM users where created_at >= "'.$ystart.'" and created_at < "'.$yend.'"');
            $member = $ysql->queryOne();

            $sql = '';
            for( $i=100;$i<110;$i++){
                if($i == 109){
                    $sql .= '(SELECT sum(buy_num) as buy_num FROM period_buylist_'.$i.' where buy_time >= "'.$ystart.'" and buy_time < "'.$yend.'") ';
                }else{
                    $sql .= '(SELECT sum(buy_num) as buy_num FROM period_buylist_'.$i.' where buy_time >= "'.$ystart.'" and buy_time < "'.$yend.'" ) union all';
                }
            }
            $yincomesql = $conn->createCommand('SELECT sum(buy_num) as total FROM ('.$sql.') as t ');
            $yIncomeTotal = $yincomesql->queryOne();

            $sql = '';
            for( $i=100;$i<110;$i++){
                if($i == 109){
                    $sql .= '(SELECT sum(money) as money FROM recharge_orders_'.$i.' where status = 1 and pay_time >= "'.$ystart.'" and pay_time < "'.$yend.'") ';
                }else{
                    $sql .= '(SELECT sum(money) as money FROM recharge_orders_'.$i.' where status = 1 and pay_time >= "'.$ystart.'" and pay_time < "'.$yend.'" ) union all';
                }
            }
            $yrechargesql = $conn->createCommand('SELECT sum(money) as total FROM ('.$sql.') as t ');
            $yrechargeTotal = $yrechargesql->queryOne();

            $yluckysql = $conn->createCommand('SELECT count(1) as total FROM orders where create_time >="'.$ystart.'" and create_time < "'.$yend.'"');
            $yluckyTotal = $yluckysql->queryOne();

            $ydeliversql = $conn->createCommand('SELECT count(1) as total FROM deliver where deliver_time >="'.$ystart.'" and deliver_time < "'.$yend.'"');
            $ydeliverTotal = $ydeliversql->queryOne();
            $arr = ['member'=>$member['total'], 'income'=>$yIncomeTotal['total'], 'recharge'=>$yrechargeTotal['total'], 'lottery'=>$yluckyTotal['total'], 'delivery'=>$ydeliverTotal['total']];
            $redis->del('YESTERDAY_COUNT_'.date("Y-m-d",strtotime("-1 day")));
            $redis->hset($name, $arr);
        }
    }

}
