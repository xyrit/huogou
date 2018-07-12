<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/12
 * Time: 上午9:37
 */
namespace app\modules\api\controllers;

use app\helpers\DateFormat;
use app\models\PkLotteryCompute;
use app\services\PkPeriod;
use app\services\PkProduct;
use app\services\Product;
use app\services\User;

class PkPeriodController extends BaseController
{

    public function actionInfo()
    {
        $request = \Yii::$app->request;
        $pid = $request->get('pid');

        $periodInfo = PkPeriod::info($pid);
        $periodInfo['buy_count'] = PkProduct::curPeriodBuyCount($periodInfo['period_id'], $periodInfo['table_id']);
        return $periodInfo;
    }

    public function actionBuyList()
    {
        $request = \Yii::$app->request;
        $pid = $request->get('pid');
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 10);

        $result = PkPeriod::buyList($pid, $page, $perpage);

        return $result;
    }

    public function actionMyBuyList()
    {
        $request = \Yii::$app->request;
        $pid = $request->get('pid');
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 10);
        $result = PkPeriod::myBuyList($this->userId, $pid, $page, $perpage);

        return $result;
    }

    public function actionCompute()
    {
        $request = \Yii::$app->request;
        $pid = $request->get('pid');

        $periodInfo = \app\models\PkPeriod::findOne($pid);
        $compute = PkLotteryCompute::findByTableId($periodInfo['table_id'])->where(['period_id' => $pid])->asArray()->one();
        $data = unserialize($compute['data']);

        $yyProductIds = [];
        $pkProductIds = [];
        $userIds = [];
        foreach ($data as $key => $value) {
            if ($value['lastbuy_type'] == 'yy') {
                $yyProductIds[$value['product_id']] = $value['product_id'];
            } elseif ($value['lastbuy_type'] == 'pk') {
                $pkProductIds[$value['product_id']] = $value['product_id'];
            }
            $userIds[$value['user_id']] = $value['user_id'];
        }

        $yyProductsInfo = Product::info($yyProductIds);
        $pkProductsInfo = PkProduct::info($pkProductIds);
        $usersInfo = User::baseInfo($userIds);

        $total = 0;
        foreach ($data as $key => &$val) {
            if ($val['lastbuy_type'] == 'yy') {
                $val['product_name'] = $yyProductsInfo[$val['product_id']]['name'];
            } elseif ($val['lastbuy_type'] == 'pk') {
                $val['product_name'] = $pkProductsInfo[$val['product_id']]['name'];
            }
            $val['buy_time'] = DateFormat::microDate($val['buy_time']);
            $val['home_id'] = $usersInfo[$val['user_id']]['home_id'];
            $val['username'] = $usersInfo[$val['user_id']]['username'];
            $total += $val['data'];
        }
        $luckyCode = $periodInfo['lucky_code'];
        return [
            'total' => $total,
            'list' => $data,
            'price' => $periodInfo['price'],
            'endTime' => DateFormat::microDate($periodInfo['end_time']),
            'luckyCode' => $luckyCode
        ];
    }



}