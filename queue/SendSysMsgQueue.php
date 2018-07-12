<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/25
 * Time: 上午9:22
 */
namespace app\queue;

use app\models\NoticeMessage;
use app\models\User;
use app\models\UserSystemMessage;

class SendSysMsgQueue extends BaseQueue
{
    public function run()
    {
        $args = $this->args;
        $uid = $args['uid'];
        $content = $args['content'];
        $desc = $args['desc'];

        $user = User::findOne($uid);
        if ($user) {
            $sysMsg = new UserSystemMessage();
            $sysMsg->to_userid = $uid;
            $sysMsg->message = $content;
            $sysMsg->created_at = time();
            $sysMsg->status = 0;
            $sysMsg->save();
            NoticeMessage::addMessage($uid, 3, $desc, $content);
        }
    }

}