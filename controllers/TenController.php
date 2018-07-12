<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/1/28
 * Time: 下午3:40
 */
namespace app\controllers;


use app\helpers\Brower;

class TenController extends BaseController
{

    public function actionIndex()
    {

        $this->redirectDeviceUrl(['/weixin/ten'], ['/mobile/ten']);
        return $this->render('index');
    }

}