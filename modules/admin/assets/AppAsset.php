<?php
namespace app\modules\admin\assets;

use yii\web\AssetBundle;

class JqueryAsset extends \yii\web\JqueryAsset
{
    public $jsOptions = [
         'position' => \yii\web\View::POS_HEAD  
    ];
}

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
    ];
    public $depends = [
        //'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'app\modules\admin\assets\JqueryAsset'
        //['yii\web\JqueryAsset',["jsOptions"=>[ 'position' => \yii\web\View::POS_HEAD]  ]]
    ];
    
//    function init()
//    {
//        $this->baseUrl = Yii::$app->params['skinUrl'];
//        return parent::init();
//    }
}