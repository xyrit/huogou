<?php
/**
 * User: hechen
 * Date: 15/9/29
 * Time: 下午4:27
 */
namespace app\modules\api\controllers;

use yii;
use app\models\FollowProduct;
/**
* 关注
*/
class FollowController extends BaseController
{
	
	public function actionFollow(){
		if ($this->userId == 0) {
			return array('code'=>0,'logined'=>0);
		}
		$productId = Yii::$app->request->get('pid');
		$isExist = FollowProduct::find()->where(['user_id'=>$this->userId,'product_id'=>$productId])->asArray()->one();
		if (!$isExist) {
			$follow = new FollowProduct();
			$follow->user_id = $this->userId;
			$follow->product_id = $productId;
			$follow->follow_time = (string)microtime(time());
			$follow->save();
		}
		return array('code'=>1,'msg'=>'成功','f'=>'follow');
	}

	public function actionCancel(){
		if ($this->userId == 0) {
			return array('code'=>0,'logined'=>0);
		}
		$productId = Yii::$app->request->get('pid');
		FollowProduct::deleteAll(['user_id'=>$this->userId,'product_id'=>$productId]);
		return array('code'=>1,'msg'=>'成功','f'=>'cancel');	
	}

	public function actionIsFollowed(){
		if ($this->userId == 0) {
			return array('code'=>0,'logined'=>0);
		}
		$followed = 0;
		$productId = Yii::$app->request->get('pid');
		$isExist = FollowProduct::find()->where(['user_id'=>$this->userId,'product_id'=>$productId])->asArray()->one();
		if ($isExist) {
			$followed = 1;
		}
		return array('code'=>1,'followed'=>$followed);
	}
}