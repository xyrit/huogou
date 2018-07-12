<?php
/**
 * 我的福分
 */
namespace app\modules\member\controllers;
use app\services\User;
use app\modules\member\controllers\BaseController;

use yii;

class PointsController extends BaseController
{
    public function actionIndex()  
    {
        $userInfo = User::baseInfo(Yii::$app->user->id);
        $userInfo['cash'] = sprintf("%.2f", $userInfo['point'] / 100);
        return $this->render('index', $userInfo);
    }
    
}
