<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/13
 * Time: 下午6:46
 */
namespace app\controllers;

use app\helpers\Brower;

class LimitbuyController extends BaseController
{

    public function actionIndex()
    {
        $this->redirectDeviceUrl(['/weixin/limitbuy'], ['/mobile/limitbuy']);
        return $this->render('index');
    }

}