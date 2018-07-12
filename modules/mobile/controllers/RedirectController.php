<?php
	
	namespace app\modules\mobile\controllers;

	use yii;
	use yii\web\Cookie;
	
	/**
	* 跳转页
	*/
	class RedirectController extends BaseController
	{

		public function actionIndex(){
			$t = \Yii::$app->request->get('t');
	        if ($t) {
	            \Yii::$app->response->cookies->add(new yii\web\Cookie(['name'=>'_utoken','value'=>$t,'domain' => '.' . DOMAIN]));
	        }
			$target = \Yii::$app->request->get("target");
			$this->redirect(urldecode($target));
		}
	}