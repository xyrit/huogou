<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/16
 * Time: 下午1:57
 */
namespace app\commands;

use app\helpers\Queue;
use yii\console\Controller;
use yii\helpers\Json;

class QueueController extends Controller
{

    public function actionDequeue($name, $num)
    {
        try {
            Queue::dequeue($name, $num);
        } catch(\Exception $e) {
            $exception = [
                'line'=>$e->getLine(),
                'file'=>$e->getFile(),
                'message'=>$e->getMessage(),
                'time'=>date('Y-m-d H:i:s')
            ];
            file_put_contents('/tmp/queue_error.txt', print_r($exception,true),FILE_APPEND);
        }
    }

    public function actionIndex($name, $start, $stop)
    {
        $queue = Queue::get($name, $start, $stop);
        print_r($queue);
        echo "\r\n";
    }

    public function actionRemove($name, $start, $stop)
    {
        $queue = Queue::remove($name, $start, $stop);
        print_r($queue);
        echo "\r\n";
    }

    public function actionRemovebyscore($name, $start, $stop)
    {
        $queue = Queue::removeByScore($name, $start, $stop);
        print_r($queue);
        echo "\r\n";
    }


}