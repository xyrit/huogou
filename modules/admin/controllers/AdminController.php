<?php
/**
 * @category  huogou.com
 * @name  AppController
 * @version 1.0
 * @date 2015-12-29
 * @author  keli <liwanglai@gmail.com>
 * 
 */
namespace app\modules\admin\controllers;

use Yii;
use \yii\filters\VerbFilter;

class AdminController extends BaseController
{
    public $layout = "main";

    function render($view, $params = array())
    {
        if(1 or isset($_GET["view"]) and $_GET["view"]== "html")
        {
            $this->layout = null;

            return parent::render($view, $params);
        }
        $this->module->setLayoutPath(dirname($this->module->layoutPath)."/app");
        $v = Yii::$app->getView();
        $v->defaultExtension = "php";
        $v->renderers = [];
        return parent::render($view, $params);
    }

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    
    function goBack($defaultUrl = null)
    {
        $refer = Yii::$app->request->get("refer");
        return Yii::$app->getResponse()->redirect($refer ? $refer : Yii::$app->request->referrer);
    }
    
    function console($data)
    {
        $msg = json_decode($data);
        $this->getView()->registerJs("console.log($msg);");
    }
}