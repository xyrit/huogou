<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/22
 * Time: 17:23
 */

namespace app\modules\admin\controllers;


class SmsController extends BaseController
{
    public function actionIndex()
    {
        $this->render('index');
    }
}