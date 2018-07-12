<?php

namespace app\controllers;

use Yii;

class IpController extends BaseController
{

	public function actionIndex(){
		echo \Yii::$app->request->userIP;
	}

}