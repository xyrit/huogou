<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/4/11
 * Time: 10:03
 */
namespace app\commands;

use app\models\ActOrder;
use app\models\DuibaOrderDistribution;
use app\models\User;
use yii\console\Controller;

class OrderController extends Controller
{

    /**
     * 活动订单自动过期
     */
    public function actionOverdue()
    {
        $nowTime = strtotime(date('Y-m-d H:00:00'));

        $attrs = ['status' => ActOrder::STATUS_OVERDUE];
        $cond = 'status = :status and create_time < :time';
        $params = [':status'=>ActOrder::STATUS_INIT, ':time' => $nowTime - 3600*24*7];

        ActOrder::updateAll($attrs, $cond, $params);


    }


    public function actionDuibaMove()
    {
        $db = \Yii::$app->db;
        $sql = "select * from duiba_orders";
        $query = $db->createCommand($sql)->query();
        while($row = $query->read()) {
            $userId = $row['user_id'];
            $userInfo = User::findOne($userId);
            $homeId = $userInfo['home_id'];
            $model = new DuibaOrderDistribution($homeId);
            $model->id = substr($homeId,0,3).$row['id'];
            $model->user_id = $userId;
            $model->order_no = $row['order_no'];
            $model->credits = $row['credits'];
            $model->appKey = $row['appKey'];
            $model->description = $row['description'];
            $model->order_num = $row['order_num'];
            $model->timestamp = $row['timestamp'];
            $model->type = $row['type'];
            $model->face_price = $row['face_price'];
            $model->actual_price = $row['actual_price'];
            $model->ip = $row['ip'];
            $model->wait_audit = $row['wait_audit'];
            $model->audit_status = $row['audit_status'];
            $model->sign = $row['sign'];
            $model->params = $row['params'];
            $model->error_msg = $row['error_msg'];
            $model->status = $row['status'];
            $model->created_at = $row['created_at'];
            $model->updated_at = $row['updated_at'];
            $save = $model->save();

        }
    }


}