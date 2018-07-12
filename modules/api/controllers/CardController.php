<?php

namespace app\modules\api\controllers;

use app\services\Card;
use Yii;

class CardController extends BaseController
{
    /**
     * 充值卡兑换
     * @param cardnum 卡号
     * @param cardpwd 卡密
     **/
    public function actionConvert()
    {
        $request = Yii::$app->request;
        $num = $request->get('cardnum');
        $pwd = $request->get('cardpwd');
        $card = new Card();
        $result = $card->cardConvert($this->userId, $num, $pwd);

        return $result;
    }
}