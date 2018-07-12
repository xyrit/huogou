<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'name' => '伙购网',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Shanghai',
    'language' => 'zh-CN',
    'layout' => false,
    'aliases' => [
        '@adminModule' => '@app/modules/admin',
        '@groupModule' => '@app/modules/group',
        '@passportModule' => '@app/modules/passport',
        '@imageModule' => '@app/modules/image',
        '@memberModule' => '@app/modules/member',
        '@userModule' => '@app/modules/user',
        '@shareModule' => '@app/modules/share',
        '@apiModule' => '@app/modules/api',
        '@helpModule' => '@app/modules/help',
        '@weixinModule' => '@app/modules/weixin',
        '@mobileModule' => '@app/modules/mobile',
        '@ddhelpModule' => '@app/modules/ddhelp',
        '@ddweixinModule' => '@app/modules/ddweixin',

    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'group' => [
            'class' => 'app\modules\group\Module',
        ],
        'image' => [
            'class' => 'app\modules\image\Module',
        ],
        'member' => [
            'class' => 'app\modules\member\Module',
        ],
        'passport' => [
            'class' => 'app\modules\passport\Module',
        ],
        'user' => [
            'class' => 'app\modules\user\Module',
        ],
        'share' => [
            'class' => 'app\modules\share\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
        'help' => [
            'class' => 'app\modules\help\Module',
        ],
        'ddhelp' => [
            'class' => 'app\modules\ddhelp\Module',
        ],
        'weixin' => [
            'class' => 'app\modules\weixin\Module',
        ],
        'ddweixin' => [
            'class' => 'app\modules\ddweixin\Module',
        ],
        'mobile' => [
            'class' => 'app\modules\mobile\Module',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => require(__DIR__ . '/route.php'),
            'routeParam' => 'routeParam',
        ],
        'view' => require(__DIR__ . '/view.php'),
        'request' => [
            'class' => 'app\components\Request',
            'enableCookieValidation' => false,
            'csrfCookie' => ['httpOnly' => true, 'domain' => '.' . DOMAIN]
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
        ],
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => 'redis',
            'cookieParams' => ['domain' => '.' . DOMAIN, 'lifetime' => 0],//配置会话ID作用域 生命期和超时
            'timeout' => 3600,
        ],
        'user' => [
            'class' => 'app\components\User',
            'identityClass' => 'app\models\User',
            'loginUrl' => ['/passport/default/login'],
        ],
        'admin' => [
            'class' => 'app\modules\admin\components\Admin',
            'identityClass' => 'app\modules\admin\models\Admin',
            'enableAutoLogin' => true,
            'loginUrl' => ['/admin/login/index'],
            'identityCookie' => ['name' => '_admin_identity', 'httpOnly' => true, 'domain'=> 'admin.' . DOMAIN, ],
            'idParam' => '__admin_uid',
        ],
        'errorHandler' => [
            'errorAction' => '/error/index',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'redis' => require(__DIR__ . '/redis.php'),
        'sms' => require(__DIR__ . '/sms.php'),
        'mailer' => require(__DIR__ . '/mailer.php'),
        'sftp' => require(__DIR__ . '/sftp.php'),
        'getui' =>require(__DIR__.'/getui.php'),
        'chatpay' => require(__DIR__ . '/chatpay.php'),
        'unionpay' => require(__DIR__ . '/unionpay.php'),
        'chinabank' => require(__DIR__ . '/chinabank.php'),
        'authClientCollection' => require(__DIR__ . '/authclient.php'),
        'email' => require(__DIR__ . '/email.php'),
        'wechat' => require(__DIR__ . '/wechat.php'),
        'iapppay' => require(__DIR__ . '/iapppay.php'),
        'zhifuka' => require(__DIR__.'/zhifuka.php'),
        'jdpay' =>require(__DIR__.'/jdpay.php'),
        'kqpay' =>require(__DIR__.'/kqpay.php'),
        'duiba' =>require(__DIR__.'/duiba.php'),
        'wxpay' =>require(__DIR__.'/wxpay.php'),// 微信企业付款
        'nowpay' =>require(__DIR__.'/nowpay.php'),// 现在付款
        'alipay' =>require(__DIR__.'/alipay.php'),
        'jdcard' =>require(__DIR__.'/jdcard.php'),     //聚合礼品卡
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
