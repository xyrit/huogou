<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require (__DIR__. '/../helpers/before_app_functioins.php');
//setcookie('XDEBUG_SESSION',1,time()+3600*24*30,'/');

$url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'www.hougou.com';
$domain = GetUrlToDomain($url);
define('DOMAIN', $domain);

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
