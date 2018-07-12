<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/1/19
 * Time: 上午10:49
 */
namespace app\modules\mobile\controllers;

use yii\web\Controller;

class ErrorController extends Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }

}