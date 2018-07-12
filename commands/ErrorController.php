<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/1/18
 * Time: 下午8:19
 */

namespace app\commands;

use yii\console\Controller;

class ErrorController extends Controller
{
    public static $emails = [
        'fujun@huogou.com',
        'hechen@huogou.com',
        'admin@huogou.com',
        'home@huogou.com',
        'pengbo@huogou.com',
    ];

    public static $phones = [
        '17722518734',
        '15926603321',
        '13751111113',
        '18576694174',
    ];

    public function actionSms($num = 3)
    {
        $cache = \Yii::$app->cache;
        $key = '__sendSmsErrorNum__';
        if ($errInfo=$cache->get($key)) {
            $cache->delete($key);
            $count = count($errInfo);
            if ($count>$num) {
                $title = '伙购网短信发送出错报警';
                $content = '伙购网短信出错报警，半小时内'.$count.'次发送失败！';
                $content .= '<br/>';
                $content .= '<pre>';
                $content .= var_export($errInfo,true);
                $content .= '</pre>';
                $this->sendEmails($title, $content);
            }
        }
        $key = '__sendMessageQueueException__';
        $messageQueueException = $cache->get($key);
        if ($messageQueueException) {
            $cache->delete($key);
            $count = count($messageQueueException);
            if ($count>$num)  {
                $content = '通知消息队列出错,半小时内'.$count.'次出错！见/tmp/queue_error.txt';
                $this->sendSms($content);
            }
        }
    }

    private function sendEmails($title, $content)
    {
        foreach(static::$emails as $email) {
            \Yii::$app->email->send($email, $title, $content);
        }
    }

    private function sendSms($content)
    {
        $sms = \Yii::$app->sms;
        foreach(static::$phones as $phone) {
            $sms->send($phone,$content);
        }
    }

}