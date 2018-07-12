<?php
/**
 * @name  SendChatQueue
 * @version 1.0
 * @date 2015-12-1
 * @author  keli <liwanglai@gmail.com>
 * 发送微信消息队列
 */
namespace app\queue;

use app\models\MpUser;
use app\models\Oauth;
use yii\helpers\Json;

class SendWechatQueue extends BaseQueue
{
    function run()
    {
        $args = $this->args;
        $uid = $args['uid'];
        $content = $args['content'];
//        $data = Json::decode($content);
        $content = strtr($content,['[time]'=>date('Y-m-d H:i:s')]);
        $mpUser = MpUser::findOne(['user_id'=>$uid]);
        if (!$mpUser) {return;}
        if (empty($mpUser->open_id)) {return;}
        $openId = $mpUser->open_id;
        $data['wid'] = 1;
        $data['open_id'] = $openId;
        $data['data'] = $content;
        $url = 'http://chat.huogou.com/wechat/api/sendtplmsg';
        $result = $this->do_post($url, $data);

    }

    function do_post($url,$data = '') {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }
}
