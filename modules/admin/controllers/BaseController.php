<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/18
 * Time: 15:04
 */

namespace app\modules\admin\controllers;


use yii\web\Controller;

class BaseController extends Controller
{
    public function init()
    {
        parent::init();
        \Yii::$app->errorHandler->errorAction = '/admin/error/index';
        $admin = \Yii::$app->admin;
        if ($admin->isGuest) {
            return $admin->loginRequired();
        }
    }
}