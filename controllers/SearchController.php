<?php

namespace app\controllers;

use Yii;
// use app\models\Product;
use app\services\Product;

class SearchController extends BaseController
{

    public function actionIndex()
    {
    	$keyWords = Yii::$app->request->get('q');
    	
        return $this->render('index',array('keyWords'=>$keyWords));
    }
}