<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/23
 * Time: 下午7:31
 */
namespace app\modules\ddweixin\controllers;
use app\helpers\DateFormat;

class LotteryController extends BaseController
{

    public function actionIndex()
    {
        return $this->render('index', []);
    }

     public function actionBuyDetail($periodId,$tpl="buydetail")
    {
         
         $periodInfo = \app\models\Period::findOne($periodId);
         // 购买信息
          
         $member = new \app\services\Member(['id' => $periodInfo->user_id]);
        $buyDetail = $member->getBuyDetail($periodId);
        $buyNumber = 0;
        foreach ($buyDetail as &$detail) {
            $detail['buy_time'] = DateFormat::microDate($detail['buy_time']);
            $detail['codes'] = explode(',', $detail['codes']);
            $buyNumber += $detail['buy_num'];
        }
        return $this->render($tpl, [
            'buyDetail'=>$buyDetail,
            'buyNumber'=>$buyNumber,
            "periodInfo"=> $periodInfo,
            ]);
    }


}