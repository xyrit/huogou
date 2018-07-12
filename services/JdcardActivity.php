<?php

namespace app\services;

use app\models\ActivityProducts;
use app\models\ActivityJd;
use app\models\Config;

class JdcardActivity
{
    /**
     * @param $productId
     * @param $user_id
     * @param $is_winners
     * @return bool
     */
    public static function JdcardRed($money,$productId, $user_id, $is_winners)
    {

        //活动时间
        $config = Config::getValueByKey('jdcardactionconfig');


        $end_time = $config['endtime'];
        $start_time = $config['starttime'];
        $status = $config['status'];
        //活动判断时间
        $time = time();
        if ($time > $end_time || $time < $start_time || $status == 0) {
            return true;
        }


        //查询商品
        $activtyproduct = ActivityProducts::findOne($productId);
        if ($activtyproduct->delivery_id == 8) {
            $face_value = $activtyproduct->face_value;
            if ($is_winners)   //是否中奖  1 是  0 否
            {
                switch ($face_value) {
                    case 1000:
                        $money = 30;
                        break;
                    case 500:
                        $money = 15;
                        break;
                    default:
                        $money = 3;
                        break;
                }
            }
            $activtyjd = ActivityJd::find()->where(['user_id' => $user_id])->one();
            if ($activtyjd) {
                $oldmoney = $activtyjd->money;
                $activtyjd->money = $oldmoney + $money;
                $activtyjd->up_time = time();
                $rs = $activtyjd->save();

            } else {
                $activtyjdModel = new ActivityJd();
                $activtyjdModel->user_id = $user_id;
                $activtyjdModel->money = $money;
                $activtyjdModel->up_time = time();
                $rs = $activtyjdModel->save();
            }

        }
        return true;
    }

}