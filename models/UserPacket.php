<?php

	namespace app\models;

	use Yii;


	/**
	* 红包
	*/
	class UserPacket extends \yii\db\ActiveRecord
	{
		public static function tableName()
		{
			return 'user_packet';
		}

		/**
		 * 用户领取的红包信息
		 * @param  int $id 红包ID
		 * @return [type]     [description]
		 */
		public static function getInfo($id)
		{
			return UserPacket::find()->where(['id'=>$id])->asArray()->one();
		}

		/**
		 * 获取用户领取的某一个红包
		 * @param  int $uid      用户ID
		 * @param  int $packetId 红包ID
		 * @return [type]           [description]
		 */
		public static function getPacketByUidByCid($uid,$packetId)
		{
			return UserPacket::find()->where(['user_id'=>$uid,'packet_id'=>$packetId])->asArray()->one();
		}

		/**
		 * 获取用户红包数量
		 * @param  int $uid      用户ID
		 * @param  int $packetId 红包ID
		 * @return [type]           [description]
		 */
		public static function getCountByUidByCid($uid,$packetId)
		{
			return UserPacket::find()->where(['user_id'=>$uid,'packet_id'=>$packetId])->count();
		}
	}