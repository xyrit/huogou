<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/16
 * Time: 下午2:25
 */
namespace app\queue;

use app\models\NoticeMessage;

class SendEmailQueue extends BaseQueue
{
    public function run()
    {
        $args = $this->args;
        $title = $args['title'];
        $content = $args['content'];
        $email = $args['email'];
        $desc = $args['desc'];

//        $fromName = \Yii::$app->name;
//        $mailer = \Yii::$app->mailer;
//        $fromEmail = 'home@huogou.com';
//        $emailTemplate = 'common/index.html';
//        $mailer->compose($emailTemplate,['content'=>$content, 'data'=>$args])
//            ->setTo($email)
//            ->setFrom([$fromEmail => $fromName])
//            ->setSubject($title)
//            ->send();

       \Yii::$app->email->send($email, $title, $content);

        NoticeMessage::addMessage($email, 2, $desc, $content);
    }

}