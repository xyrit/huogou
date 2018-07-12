<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/6
 * Time: 下午4:16
 */

namespace app\helpers;

use app\models\NoticeMessage;
use app\models\NoticeTemplate;
use app\models\User;
use app\models\UserSystemMessage;
use app\validators\MobileValidator;
use yii\validators\EmailValidator;
use Yii;

class Message
{



    /**
     * @param $type 类型 类型 即 notice_template表id
     * @param $to 接收者，手机，邮箱，用户id
     * @param $data 传递的数据，例如array('code'=>$code);即传递了验证码
     * @return bool
     */
    public static function send($type, $to, $data, $time = null)
    {
        $sendData = [];
        $sendData['type'] = $type;
        $sendData['to'] = $to;
        $sendData['data'] = $data;
        try {
            $sendData['ip'] = Yii::$app->request->userIP;
            if (defined('DOMAIN')) {
                $sendData['from'] = Brower::whereFrom();
            }
        } catch (\Exception $e) {

        }
        $messageN = rand(1,4);
        $name = 'message'.$messageN;
        return Queue::add($name,'\app\queue\SendMessageQueue',$sendData,$time);

    }









}