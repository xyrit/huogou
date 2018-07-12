<?php

	namespace app\models;

	use Yii;

	/**
	* 分享链接
	*/
	class ShareLink extends \yii\db\ActiveRecord
	{
		
		public static function tableName()
		{
			return 'sharelink';
		}

		public function rules()
	    {
	        return [
	            [['title', 'desc', 'img', 'template', 'time'], 'required'],
	        ];
	    }

	    public function attributeLabels(){
	    	return [
	    		'ID' => 'ID',
	    		'title' => '分享标题',
	    		'desc' => '分享简介',
	    		'img' => '分享图片',
	    		'template' => '分享模板',
	    		'time' => '分享时间',
	    		'user_id' => '分享者'
	    	];
	    }

	    public static function getInfoById($id)
	    {
	    	$data = ShareLink::find()->where(['id'=>$id])->asArray()->one();
	    	return $data;
	    }
	}