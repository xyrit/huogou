<?php

	namespace app\models;

	use Yii;


	/**
	* 红包
	*/
	class CouponCode extends \yii\db\ActiveRecord
	{
		public static function tableName()
		{
			return 'coupon_code';
		}

		/**
		 * 获取优惠券码
		 * @param  int  $couponId 优惠券id
		 * @param  int $nums     获取数量
		 * @return [type]            [description]
		 */
		public static function getCodeByCid($couponId,$nums = 1,$packetId=0,$status = '0')
		{	
			$query = CouponCode::find()->where(['status'=>$status,'coupon_id'=>$couponId]);
			if ($packetId > 0) {
				$query = $query->andwhere(['packet_id'=>$packetId]);
			}
			return $query->limit($nums)->asArray()->all();
		}

		/**
		 * 获取优惠码信息
		 * @param  string $code 优惠券码
		 * @return [type]       [description]
		 */
		public static function getInfoByCode($code)
		{
			return CouponCode::find()->where(['code'=>$code])->asArray()->one();
		}
	}