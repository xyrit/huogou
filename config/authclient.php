<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/15
 * Time: 上午10:06
 */
return [
    'class' => 'yii\authclient\Collection',
    'clients' => [
        'qq' => [
            'class'=>'app\oauth\QQOAuth',
            'clientId'=>'101263061',
            'clientSecret'=>'7229c0cc09b62d0c2e43a6da96d3ede5'
        ],
        'wechat' => [
            'class'=>'app\oauth\WechatOAuth',
            'clientId'=>'wxd2e1c893e6cdd045',
            'clientSecret'=>'646ff377df21d475038251d638fb1da6'
        ],
    ],
];