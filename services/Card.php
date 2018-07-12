<?php

namespace app\services;

use app\helpers\Brower;
use app\helpers\MyRedis;
use Yii;
use app\models\CardBatch;
use app\models\Cards;

class Card
{

    /**
     * 充值卡兑换
     * @param $userId
     * @param $num
     * @param $pwd
     * return array
     **/
    public static function cardConvert($userId, $num, $pwd)
    {
        if(!$userId) return ['code'=>'101', 'message'=>'请先登陆'];
        $redis = new MyRedis();
        $key = 'USER_CARD_'.$userId.'_'.$num;
        $userCardNum = $redis->incr($key);
        $redis->expire($key, 60);
        if($userCardNum > 1) return ['code'=>110, 'message'=>'正在兑换...'];
        $cardModel = Cards::find()->where(['card'=>$num, 'pwd'=>$pwd])->one();
        if(!$cardModel) return ['code'=>102, 'message'=>'充值卡信息错误'];
        if($cardModel['status'] == 1) return ['code'=>103, 'message'=>'充值卡已使用'];

        $bashModel = CardBatch::findOne($cardModel['batch_id']);
        if(!$bashModel || $bashModel['status'] != 1) return ['code'=>104, 'message'=>'充值卡无效'];
        if($bashModel['valid_type'] == 1){
            $time = time();
            if(!($time >= $bashModel['start_at'] && $time <= $bashModel['end_at'])){
                $cardModel->status = 2;
                if($cardModel->save()) return ['code'=>105, 'message'=>'充值卡已过期'];
            }
        }

        //充值金额限制
        if($bashModel['recharge_money_limit'] != 0){
            $money = Cards::find()->select('sum(price) as total')->where(['user_id'=>$userId, 'status'=>1])->all();
            $moneyCount = $money['total'] + $cardModel['price'];
            if($moneyCount >= $bashModel['recharge_money_limit']) return ['code'=>107, 'message'=>'充值总金额不能超过'.$bashModel['recharge_money_limit']];
        }

        //充值次数限制
        if($bashModel['recharge_num_limit'] != 0){
            $countNum = Cards::find()->where(['user_id'=>$userId, 'status'=>1])->count(1);
            if($countNum == $bashModel['recharge_num_limit']) return ['code'=>108, 'message'=>'充值总次数不能超过'.$bashModel['recharge_num_limit']].'次';
        }


        $memberModel = new Member();
        if($memberModel->editMoney($cardModel['price'], 5, '充值卡充值')){
            $cardModel->status = 1;
            $cardModel->user_id = $userId;
            $cardModel->used_at = time();
            if($cardModel->save()) return ['code'=>0, 'message'=>'充值成功'];
            else return ['code'=>106, 'message'=>'充值失败，请联系客服'];
        }

    }
}