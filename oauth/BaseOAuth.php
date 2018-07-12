<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/15
 * Time: 上午11:40
 */
namespace app\oauth;

use yii\authclient\OAuth2;

class BaseOAuth extends OAuth2
{
    const OAUTH_QQ = 1;
    const OAUTH_WECHAT = 2;

    public function init()
    {
        parent::init();

        $this->scope = implode(',', $this->scope);
        $this->setCurlOptions([CURLOPT_SSL_VERIFYPEER => FALSE]);
    }


}