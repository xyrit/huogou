<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/26
 * Time: 13:56
 */

namespace app\modules\help\controllers;

use app\controllers\BaseController;
use yii\web\Controller;

class InfoController extends BaseController
{

    public function actionBusiness()
    {
        return $this->render('business');
    }

    public function actionNewbie()
    {
        return $this->render('newbie');
    }

    //伙购公益
    public function actionFund()
    {

        return $this->render('fund');
    }

}
