<?php

	namespace app\models;

	use Yii;

	/**
	* 优惠券信息
	*/
	class Coupon extends \yii\db\ActiveRecord
	{
		
		public static function tableName()
		{
			return 'coupon';
		}

		/**
		 * 获取优惠券信息
		 * @param  int|array $cid 优惠券ID
		 * @return [type]      [description]
		 */
		public static function getInfo($cid){
			if (is_array($cid)) {
				$list = Coupon::find()->where(['in','id',$cid])->asArray()->all();
				$return = '';
				foreach ($list as $key => $value) {
					$return[$value['id']] = $value;
				}
				return $return;
			}else{
				return Coupon::find()->where(['id'=>$cid])->asArray()->one();
			}
		}
	}