<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/28
 * Time: 上午10:17
 */
return [
    'class' => 'yii\swiftmailer\Mailer',
    'view' => require(__DIR__ . '/view.php'),
    'htmlLayout' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.qq.com',
        'username' => 'home@huogou.com',
        'password' => 'shouye456',//shouye456 678
        'port' => '25',
        'encryption' => 'tls',
    ],
];