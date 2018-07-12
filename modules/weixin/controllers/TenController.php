<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/3/14
 * Time: 下午1:41
 */
namespace app\modules\weixin\controllers;

class TenController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}