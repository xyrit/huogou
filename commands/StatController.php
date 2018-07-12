<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/3/17
 * Time: 15:23
 */
namespace app\commands;

use app\models\SalesNumStat;
use yii\console\Controller;

class StatController extends Controller
{

    public function actionSalesNum()
    {
        $db = \Yii::$app->db;
        $result = $db->createCommand('select sum(sales_num) as result from current_periods')->queryScalar();

        $day = date('Ymd');
        $hour = date('H');
        $stat = SalesNumStat::find()->where(['day'=>$day,'hour'=>$hour])->one();
        if (!$stat) {
            $stat = new SalesNumStat();
            $stat->day = $day;
            $stat->hour = $hour;
            $stat->result = $result;
            $stat->created_at = time();
            $stat->save(false);
        }
    }


}