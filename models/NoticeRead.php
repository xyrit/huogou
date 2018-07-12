<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/25
 * Time: 17:16
 */

namespace app\models;

use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "actives".
 *
 * @property string $id
 * @property integer user_id
 * @property integer type
 * @property integer notice_id
 * @property integer created_time
 */
class NoticeRead extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'notice_read';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['user_id', 'type', 'notice_id'], 'required'],
			[['user_id', 'type', 'notice_id', 'view', 'open', 'created_time'], 'integer']
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => '编号',
			'user_id' => '用户id',
			'type' => '公告类型',
			'notice_id' => '公告id',
			'view' => '是否查看',
			'open' => '是否开启',
			'created_time' => '更新时间'
		];
	}
	
	/**
	 * 添加已查看公告
	 */
	public static function addRead($data = [])
	{
		$NoticeModel = new NoticeRead();
		$info = $NoticeModel::findOne(["user_id" => $data['user_id'], "notice_id" => $data['notice_id']]);
		if ($info) return 1;
		$NoticeModel->user_id = $data['user_id'];
		$NoticeModel->type = $data['type'];
		$NoticeModel->notice_id = $data['notice_id'];
		$NoticeModel->created_time = $data['created_time'];
		$rs = $NoticeModel->save();
		return $rs ? 1 : 0;
	}
	
	/**
	 * 是否查看公告
	 */
	public static function isRead($userId, $id)
	{

		$info = NoticeRead::findOne(['user_id' => $userId, 'notice_id' => $id]);
		return $info['view'] ? 1 : 0;
	}
}