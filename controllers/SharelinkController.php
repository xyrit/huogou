<?php

namespace app\controllers;

use Yii;
use app\models\ShareLink;


/**
* 分享链接
*/
class SharelinkController extends BaseController
{
	
	public function actionIndex()
	{
		$id = Yii::$app->request->get('id');

		$linkInfo = ShareLink::getInfoById($id);

		$template = $linkInfo['template'] ? : 'list';

		$path = 'spread/'.$template;

		return $this->render($template.'/index',['path'=>$path,'data'=>$linkInfo]);		
	}
}