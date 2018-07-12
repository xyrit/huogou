<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/16
 * Time: 下午2:21
 */
namespace app\helpers;

use yii\helpers\Json;

class Queue
{
    /** 添加消息队列
     * @param $name
     * @param $class
     * @param $data
     * @param null $time
     */
    public static function add($name, $class, $data, $after = null)
    {
        $redis = \Yii::$app->redis;
        $key = static::getQueueKey($name);
        $now = time();
        $time = $now + $after ? $now + $after : $now;
        $data['__CALL_CLASS__'] = $class;
        $data['__QUEUE_ADD_TIME__'] = $now;
        $data['__QUEUE_RUN_TIME__'] = $time;
        $data = Json::encode($data);
        return $redis->zadd($key, $time, $data);
    }

    /** 执行消息队列任务
     * @param $name
     * @param int $before
     * @param null $time
     */
    public static function dequeue($name, $num)
    {
        $start = 0;
        $stop = $num - 1 > 0 ? $num - 1 : 0;
        $queueList = static::get($name, $start, $stop);
        $now = time();
        $minQueueTime = $now - 1800;
        foreach ($queueList as $key=>$queueInfo) {
            $queueInfo = Json::decode($queueInfo);
            $score = $queueInfo['__QUEUE_RUN_TIME__'];
            if ($score<=$now) { //小于当前时间的 队列任务 删除
                static::removeByScore($name, $score, $score);
            }
        }
        foreach ($queueList as $key=>$queueInfo) {
            try {
                $queueInfo = Json::decode($queueInfo);
                $score = $queueInfo['__QUEUE_RUN_TIME__'];
                if ($minQueueTime < $score && $score<=$now) { //半个小时内的 队列任务 执行
                    $callClass = $queueInfo['__CALL_CLASS__'];
                    $class = new $callClass($queueInfo);
                    $class->run();
                }
            } catch(\Exception $e) {
                $exception = [
                    'line'=>$e->getLine(),
                    'file'=>$e->getFile(),
                    'message'=>$e->getMessage(),
                    'queueInfo'=>$queueInfo,
                    'time'=>date('Y-m-d H:i:s')
                ];
                file_put_contents('/tmp/queue_error.txt',print_r($exception,true),FILE_APPEND);
                if (strpos($name, 'message')===0) {
                    $cache = \Yii::$app->cache;
                    $key = '__sendMessageQueueException__';
                    $messageQueueException = $cache->get($key);
                    if ($messageQueueException) {
                        $messageQueueException[] = $exception;
                        $cache->set($key,$messageQueueException,3600);
                    } else {
                        $messageQueueException = [];
                        $messageQueueException[] = $exception;
                        $cache->set($key,$messageQueueException,3600);
                    }
                }
            }
        }

        return false;
    }

    public static function get($name, $start, $stop)
    {
        $redis = \Yii::$app->redis;
        $key = static::getQueueKey($name);
        return $redis->zrange($key, $start, $stop);
    }

    public static function remove($name, $start, $stop)
    {
        $redis = \Yii::$app->redis;
        $key = static::getQueueKey($name);
        return $redis->zremrangebyrank($key, $start, $stop);
    }

    public static function removeByScore($name, $start, $stop)
    {

        $redis = \Yii::$app->redis;
        $key = static::getQueueKey($name);
        return $redis->zremrangebyscore($key, $start, $stop);
    }

    public static function getQueueKey($name)
    {
        $key = __CLASS__ . '__' . $name;
        $key = md5($key);
        return $key;
    }


}