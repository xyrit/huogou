<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/11
 * Time: 下午1:31
 */
namespace app\commands;

use app\models\EuroCupOrder;
use app\models\PaymentOrderDistribution;
use app\services\Member;
use app\services\User;
use yii\console\Controller;

class EuroController extends Controller
{

    public function actionMovePaymentOrder()
    {
        $db = \Yii::$app->db;

        $sql = "select * from euro_cup_orders where payment_order_id = ''";
        $query = $db->createCommand($sql)->query();
        while($row = $query->read()) {
            $status = $row['status'];
            if ($status != 1) {
                continue;
            }
            $euroOid = $row['id'];
            $userId = $row['user_id'];
            $money = $row['money'];
            $createAt = $row['created_at'];
            $payAt = $row['pay_at'];
            $userInfo = User::baseInfo($userId);
            $userHomeId = $userInfo['home_id'];
            $paymentOrder = new PaymentOrderDistribution($userHomeId);
            $orderNum = PaymentOrderDistribution::generateOrderId($userHomeId);
            $attrs = [
                'id' => $orderNum,
                'user_id' => $userId,
                'status' => 1,
                'payment' => 1,
                'bank' => '',
                'money' => $money,
                'point' => 0,
                'total' => $money,
                'user_point' => 0,
                'ip' => '0',
                'source' => '',
                'create_time' => $createAt,
                'buy_time' => $payAt,
                'recharge_orderid' => '',
                'user_account' => $userInfo['nickname'] ? : ($userInfo['phone'] ? : $userInfo['email']),
                'spread_source' => $userInfo['spread_source'],
                'pay_for' => PaymentOrderDistribution::PAY_FOR_EURO,
            ];
            $paymentOrder->setAttributes($attrs, false);
            $save = $paymentOrder->save(false);
            if ($save) {
                EuroCupOrder::updateAll(['payment_order_id' => $orderNum], ['id' => $euroOid]);
            }
            echo 'EURO_ORDER_ID:'. $euroOid  . PHP_EOL;
        }
    }


}