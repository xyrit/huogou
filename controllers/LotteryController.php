<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/13
 * Time: 下午5:50
 */
namespace app\controllers;


class LotteryController extends BaseController
{

    public function actionIndex()
    {
        $this->redirectDeviceUrl(['/weixin/lottery'], ['/mobile/lottery']);
        return $this->render('index');
    }


}