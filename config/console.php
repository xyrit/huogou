<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'name' => '伙购网',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'timeZone' => 'Asia/Shanghai',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'redis' => require(__DIR__ . '/redis.php'),
        'sms' => require(__DIR__ . '/sms.php'),
        'mailer' => require(__DIR__ . '/mailer.php'),
        'email' => require(__DIR__ . '/email.php'),
        'wechat' => require(__DIR__ . '/wechat.php'),
        'getui' => require(__DIR__ . '/getui.php'),
    ],
];
