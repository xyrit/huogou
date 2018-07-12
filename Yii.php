<?php
/**
 * Yii bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require(__DIR__ . '/vendor/yiisoft/yii2/BaseYii.php');

/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication the application instance
     */
    public static $app;
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = include(__DIR__ . '/vendor/yiisoft/yii2/classes.php');
Yii::$container = new yii\di\Container;

/**
 * Class WebApplication
 * Include only Web application related components here
 *
 * @property \app\modules\admin\components\Admin $admin The admin component. This property is read-only. Extended component.
 * @property \app\components\User $user The user component. This property is read-only. Extended component.
 * @property \app\components\Sms $sms The sms component. This property is read-only. Extended component.
 * @property \yii\redis\Connection $redis The redis component. This property is read-only. Extended component.
 * @property \app\components\SFtp $sftp The sftp component. This property is read-only. Extended component..
 * @property \app\components\wechat\MpWechat $wechat The wechat component. This property is read-only. Extended component.
 * @property \app\components\Iapppay $iapppay The iapppay component. This property is read-only. Extended component.
 * @property \app\components\Zhifuka $zhifuka The zhifuka component. This property is read-only. Extended component.
 * @property \app\components\Jdpay $jdpay The jdpay component. This property is read-only. Extended component.
 * @property \app\components\Kqpay $kqpay The kqpay component. This property is read-only. Extended component.
 * @property \app\components\Unionpay $unionpay The unionpay component. This property is read-only. Extended component.
 * @property \app\components\Getui $getui The getui component. This property is read-only. Extended component.
 * @property \app\components\Duiba $duiba The duiba component. This property is read-only. Extended component.
 * @property \app\components\Alipay $alipay The alipay component. This property is read-only. Extended component.
 * @property \app\components\nowpay $nowpay The nowpay component. This property is read-only. Extended component.
 * @property \app\components\Jdcard $jdcard The jdcard component. This property is read-only. Extended component.
 */
class WebApplication extends yii\web\Application
{
}
