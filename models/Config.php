<?php

	namespace app\models;

	use yii;
	use app\helpers\MyRedis;

	/**
	* 配置
	*/
	class Config extends \yii\db\ActiveRecord
	{
		const CONFIG_KEY = 'CONFIG';

		public static function tableName()
		{
			return 'config';
		}

		public function rules()
	    {
	        return [
	            [['key', 'value'], 'required'],
	        ];
	    }

	    /**
	     * @inheritdoc
	     */
	    public function attributeLabels()
	    {
	        return [
	            'id' => 'ID',
	            'key' => 'Key',
	            'value' => 'Value',
	        ];
	    }

		public static function getValueByKey($key)
		{
			$redis = new Myredis();
			$data = $redis->hget(self::CONFIG_KEY,$key);
			if (!$data) {
				$_data = Config::find()->where(['key'=>$key])->asArray()->one();
				$data = $_data['value'];
				$redis->hset(self::CONFIG_KEY,[$key=>$data]);
			}
			return json_decode($data,true);
		}
	}