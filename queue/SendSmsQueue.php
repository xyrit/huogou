<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/16
 * Time: 下午2:12
 */
namespace app\queue;

use app\models\NoticeMessage;

class SendSmsQueue extends BaseQueue
{
    public function run()
    {
        $args = $this->args;
        $phone = $args['phone'];
        $content = $args['content'];
        $desc = $args['desc'];

        \Yii::$app->sms->send($phone, $content);

        NoticeMessage::addMessage($phone, 1, $desc, $content);
    }

}