<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/5/31
 * Time: 15:13
 */
namespace app\commands;

use app\services\Coupon;
use yii\console\Controller;

class CouponController extends Controller
{

    public function actionAdd($userId, $packetId, $source)
    {
        $coup = Coupon::receivePacket($packetId, $userId, $source);
        if(isset($coup['data']['pid'])){
            $result =  Coupon::openPacket($coup['data']['pid'], $userId);
        }else{
            $result =  $coup;
        }
        var_dump($result);
    }


}