<?php

namespace app\modules\api\controllers;

use yii;
use app\models\ShareLink;

/**
* 分享链接
*/
class SharelinkController extends BaseController
{
	
	public function actionIndex()
	{
		$request = Yii::$app->request;
		$title = $request->post('title');
		$desc = $request->post('desc');
		$img = $request->post('img');

		$template = 'dial';

		$shareLink = new ShareLink();
		$shareLink->title = $title;
		$shareLink->desc = $desc;
		$shareLink->img = $img;
		$shareLink->template = $template;
		$shareLink->time = time();
		$shareLink->user_id = $this->userId;
		$shareLink->save(false);

		$shareId = $shareLink->attributes['id'];

		return ['code'=>100,'url'=>'http://www'.DOMAIN.'/sharelink.html?id='.$shareId];
	}

}