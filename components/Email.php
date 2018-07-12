<?php
/**
 * Created by PhpStorm.
 * User: chenyi
 * Date: 2015/11/19
 * Time: 10:56
 */

namespace app\components;

use Yii;
use yii\base\Component;

class Email extends Component
{
    public $APIUrl;
    public $LoginEmail;
    public $APIKey;
    public $FromEmail;
    public $FromName;

    private $_client;

    public function init()
    {
        $this->_client = new \SoapClient($this->APIUrl);
    }

    public function send($toEmail, $title, $content)
    {
        $sendParam = array(
            'LoginEmail'    => $this->LoginEmail,
            'Password'      => $this->APIKey,
            //'CampaignName'  => DOMAIN.'_act_'.date( 'Y-n-j' ),
            'From'          => $this->FromEmail,
            'FromName'      => $this->FromName,
            'To'            => $toEmail,
            'Subject'       => $title,
            'Body'          => $content
        );

        $sendResult = $this->_client->EmailSend($sendParam);
        return $sendResult;
    }

    /**
     * 添加发送者邮箱地址
     * @param $sender
     * @return int
     */
    public function addSendUser($sender)
    {
        $sendArr = array(
            'loginEmail' => $this->LoginEmail,
            'password'   => $this->APIKey,
            'SenderEmail'=> $sender
        );

        $flag = $this->_client->AddSenderEmail($sendArr);

        if (empty($flag)) {
            return -1;
        }

        return 0;
    }
}