<?php
/**
 * User: hechen
 * Date: 15/9/30
 * Time: 下午3:33
 */

namespace app\controllers;

use Yii;
use app\models\Fund;

class FundController extends BaseController
{

    public function actionIndex()
    {
    	$count = Fund::find()->asArray()->one();
        return $this->render('index',['fund'=>$count['count']]);
    }

}