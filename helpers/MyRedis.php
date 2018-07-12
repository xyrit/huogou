<?php

/**
 * user : hechen
 * date : 2015/09/24
 * time : 10:48
 */

namespace app\helpers;

use Yii;

class MyRedis{

	public $myRedis;

	public function __construct(){
		$this->myRedis = new \Redis();
		$this->myRedis->connect(Yii::$app->redis->hostname,Yii::$app->redis->port);
	}

	public function command($name,$params = []){
		return $this->myRedis->executeCommand($name,$params);
	}
	/**
	 * key 是否存在
	 * @param  string $key
	 * @return 1|0      是否存在
	 */
	public function isexist($key){
		return $this->myRedis->exists($key);
	}

	/**
	 * 删除key
	 * @param  string | array $key
	 * @return int|0	   删除的个数
	 */
	public function del($key){
		return $this->myRedis->del($key);
	}

	/**
	 * 查找key,相当于like
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	public function keys($key){
		return $this->myRedis->keys($key);
	}

	/**
	 * 获取key类型
	 * @param  string $key [description]
	 * @return string      none | string | list | set | zset | hash
	 */
	public function type($key){
		return $this->myRedis->type($key);
	}

	/**
	 * 设置key-value
	 * @param string $key
	 * @param string $value
	 * @param int 	 $expire 过期时间，默认不过期
	 * @return ok
	 */
	public function set($key,$value,$expire = '0'){
		if ($expire != '0') {
			return $this->myRedis->set($key,$value,$expire);
		}
		return $this->myRedis->set($key,$value);
	}

	/**
	 * key-value格式
	 * @param  string|array $key
	 * @return string      [description]
	 */
	public function get($key){
		$func = is_array($key) ? 'mget' : 'get';
		return $this->myRedis->{$func}($key);
	}

	/**
	 * 设置hash类型
	 * @param  string $key
	 * @param  array $data 存入的数据
	 * @return ok
	 */
	public function hset($key,$data){
		return $this->myRedis->hmset($key,$data);
	}

	/**
	 * hash删除某字段
	 * @param  string $key
	 * @param  array $data 存入的数据
	 * @return ok
	 */
	public function hdel($key,$field){
		return $this->myRedis->hdel($key,$field);
	}

	/**
	 * 获取hash
	 * @param  string $key
	 * @param  string | array | all $field hash字段
	 * @return [type]      [description]
	 */
	public function hget($key,$field){
		if (is_array($field)) {
			return $this->myRedis->hmget($key,$field);
		}else if ($field == 'all') {
			return $this->myRedis->hgetall($key);
		}
		return $this->myRedis->hget($key,$field);
	}

	/**
	 * hash长度
	 * @param  string $key [description]
	 * @return int      [description]
	 */
	public function hlen($key){
		return $this->myRedis->llen($key);
	}

	/**
	 * 设置list
	 * @param  string $key  [description]
	 * @param  array $data [description]
	 * @return [type]       [description]
	 */
	public function lset($key,$data,$left='true'){
		if ($left) {
			return $this->myRedis->lpush($key,$data);
		}else{
			return $this->myRedis->rpush($key,$data);
		}
	}

	/**
	 * 获取list内容
	 * @param  string $key   [description]
	 * @param  int $start    开始index
	 * @param  int $end    	 结束index
	 * @return array        [description]
	 */
	public function lget($key,$start,$end){
		return $this->myRedis->lrange($key,$start,$end);
	}

	/**
	 * 删除左一或者右一
	 * @param  string $key   [description]
	 * @param  boole $left    删除左一
	 * @return array        [description]
	 */
	public function ldel($key,$left='true'){
		if ($left) {
			return $this->myRedis->lpop($key);
		}else{
			return $this->myRedis->rpop($key);
		}
	}

	/**
	 * 删除多个value
	 * @param  strint $key   key
	 * @param  string $value [description]
	 * @param  int $count [description]
	 * @return [type]        [description]
	 */
	public function lmDel($key,$value,$count){
		return $this->myRedis->lrem($key,$value,$count);
	}

	/**
	 * 获取list长度
	 * @param  string $key [description]
	 * @return int      [description]
	 */
	public function llen($key){
		return $this->myRedis->llen($key);
	}

	/** 管道
	 * @return mixed
	 */
	public function pipeline()
	{
		return $this->myRedis->pipeline();
	}

	/**
	 * 设置集合
	 * @param  string $key   [description]
	 * @param  string | array $value [description]
	 * @return [type]        [description]
	 */
	public function sset($key,$value){
		if (is_array($value)) {
			$pipe = $this->myRedis->pipeline();
			foreach ($value as $k => $v) {
				$pipe->sadd($key,$v);
			}
			return $pipe->exec();
		}else{
			return $this->myRedis->sadd($key,$value);
		}
	}

	/**
	 * 返回集合长度
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public function slen($key){
		return Yii::$app->redis->scard($key);
	}

	/**
	 * 获取set值
	 * @param  string $key   [description]
	 * @param  all | int $count all:返回所有值,count:随机弹出count个值
	 * @return null | string | array        [description]
	 */
	public function sget($key,$count){
		if ($count == 'all') {
			return $this->myRedis->smembers($key);
		}else{
			$pipe = $this->myRedis->pipeline();
			for ($i=0; $i < $count; $i++) {
				$pipe->spop($key);
			}

			$data = $pipe->exec();
			return $data;
		}
	}

	/**
	 * 移除set里边的一个值
	 * @param  string $key   [description]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function sdel($key,$value){
		if (is_array($value)) {
			$pipe = $this->myRedis->pipeline();
			foreach ($value as $key => $value) {
				$pipe->srem($key,$value);
			}
			return $pipe->exec();
		}else{
			$this->myRedis->srem($key,$value);
		}
	}

	/** 设置key过期时间
	 * @param $key
	 * @param $expire
	 * @return bool
	 */
	public function expire($key,$expire){
		return $this->myRedis->expire($key, $expire);
	}

	/** 递增key的整数值
	 * @param $key
	 * @return int
	 */
	public function incr($key){
		return $this->myRedis->incr($key);
	}

	/** Incrby 命令将 key 中储存的数字加上指定的增量值
	 * @param $key
	 * @param $value
	 * @return int
	 */
	public function incrBy($key, $value)
	{
		return $this->myRedis->incrBy($key, $value);
	}

	/** 递减key的整数值
	 * @param $key
	 * @return int
	 */
	public function decr($key){
		return $this->myRedis->decr($key);
	}

	/**判断剩余时间
	 * @param $key
	 * @return int
	 */
	public function ttl($key) {
		return $this->myRedis->ttl($key);
	}

}