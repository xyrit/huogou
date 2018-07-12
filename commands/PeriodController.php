<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/6/17
 * Time: 09:16
 */
namespace app\commands;

use app\models\CurrentPeriod;
use app\services\Period;
use yii\console\Controller;

class PeriodController extends Controller
{

    public function actionBuildNo()
    {
        set_time_limit(0);
        ini_set('memory_limit','1G');

        $db = \Yii::$app->db;
        $sql = "select min(start_time) as start_t from periods";
        $startDateTime = $db->createCommand($sql)->queryOne();

        $startDate = date('Y-m-d', intval($startDateTime['start_t']));
        $endDate = date('Y-m-d');

        echo PHP_EOL . "--------------------" . PHP_EOL;
        echo 'start :' . date('Y-m-d H:i:s');
        echo PHP_EOL . "--------------------" . PHP_EOL;


        $timeStart = strtotime($startDate);
        $timeEnd = strtotime($endDate);
        for ($time=$timeStart;$time<=$timeEnd;$time+=86400) {

            $start = $time;
            $end = $time + 3600*24;

            $sql = "select rowno,id,start_time from (select (@rowno:=@rowno+1) as rowno,a.* from ( select * from ((select id,start_time from periods where start_time > '".$start."' and start_time < '".$end."' order by start_time asc) union all (select id,start_time from current_periods where start_time > '".$start."' and start_time < '".$end."' order by start_time asc ) ) as k order by k.start_time asc) as a ,(select @rowno:=0) t) as b ;";
            $result = $db->createCommand($sql)->queryAll();

            echo PHP_EOL . "--------------------" . PHP_EOL;
            echo 'period start :' . date('Y-m-d H:i:s', $start);
            echo 'period end :' . date('Y-m-d H:i:s', $end);
            echo PHP_EOL . "--------------------" . PHP_EOL;
            foreach($result as $one) {
                $periodId = $one['id'];
                $no = Period::buildPeriodNo($one['start_time'], $one['rowno']);

                $updateSql = 'update periods set period_no = :no where id = :id';
                $update = $db->createCommand($updateSql, [':no'=>$no, ':id'=>$periodId])->execute();

                echo PHP_EOL . "--------------------" . PHP_EOL;
                echo 'periodId:'.$periodId.'  period_no:'.$no;
                echo PHP_EOL . "--------------------" . PHP_EOL;
            }
            $result = [];
        }

        $curPeriods = CurrentPeriod::find()->all();
        foreach($curPeriods as $curPeriod) {

            $periodId = $curPeriod['id'];
            $no = Period::getPeriodNo($curPeriod);

            $updateSql = 'update current_periods set period_no = :periodno where id = :id';
            $update = $db->createCommand($updateSql, [':periodno'=>$no, ':id'=>$periodId])->execute();

            echo PHP_EOL . "--------------------" . PHP_EOL;
            echo 'curPeriodId:'.$periodId.'  period_no:'.$no;
            echo PHP_EOL . "--------------------" . PHP_EOL;
        }
        echo 'end :' . date('Y-m-d H:i:s');

    }


    public function actionBuildNoByPeriodId($id)
    {
        $db = \Yii::$app->db;
        $period = \app\models\Period::find()->where(['id'=>$id])->one();
        $table = 'periods';
        if (!$period) {
            $period = CurrentPeriod::find()->where(['id'=>$id])->one();
            $table = 'current_periods';
            if (!$period) {

                echo PHP_EOL . "--------------------" . PHP_EOL;
                echo 'periodId:'.$id . ' 不存在!';
                echo PHP_EOL . "--------------------" . PHP_EOL;
            }
        }
        $no = Period::getPeriodNo($period);

        $updateSql = 'update '.$table.' set period_no = :periodno where id = :id';
        $update = $db->createCommand($updateSql, [':periodno'=>$no, ':id'=>$id])->execute();

        echo PHP_EOL . "--------------------" . PHP_EOL;
        echo 'periodId:'.$id.'  period_no:'.$no.' update:'.print_r($update, true);
        echo PHP_EOL . "--------------------" . PHP_EOL;
    }


}