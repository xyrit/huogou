<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/9
 * Time: 下午12:37
 */
namespace app\modules\api\controllers;

class WechatController extends BaseController
{
    public function actionJsconfig()
    {
        $wechat = \Yii::$app->wechat;
        $config =  $wechat->jsApiConfig();
        return $config;
    }


}