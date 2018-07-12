<?php
/**
 * Created by PhpStorm.
 * User: chenyi
 * Date: 2015/12/10
 * Time: 10:58
 */
namespace app\helpers;

use yii\helpers\Json;
use Yii;

class Express
{
	static private $express_url = 'http://www.kuaidi100.com/query';
	
	private static function getContent($url)
	{
		$ch = curl_init();
		$timeout = 10;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		$file_contents = file_get_contents($url);
		return $file_contents;
	}
	
	
	public static function getExpressName()
	{
		return self::getList();
	}
	
	public static function getOrder($name, $order)
	{

		$keywords = self::getList($name);
		if(!$keywords)return [];
		$url = self::$express_url . '?type=' . $keywords . '&postid=' . $order . '&id=1&temp=' . microtime(true);
		$result = self::getcontent($url);
		
		json_decode($result, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			return [];
		}
		$data = Json::decode($result);
		return $data;
	}
	
	/**
	 *获取快递公司代号
	 */
	public static function getList($name = '')
	{
		$cache = \Yii::$app->cache;
		$expressKey = 'logistics_express';
		$expressKey = md5($expressKey);
		$data = $cache->get($expressKey);
		if (!$data) {
			$connection = \Yii::$app->db;
			$command = $connection->createCommand('SELECT * FROM express');
			$result = $command->queryAll();
			$data = [];
			if(!$result)return $data;
			foreach ($result as $k => &$v) {
				$data[$v['name']] = $v['keyword'];
			}
			$cache->set($expressKey, $data, 24 * 3600);
		}
		if(!isset($data[$name]))return [];
		$data = (empty($name)) ? $data : $data[$name];

		return $data;

	}
}