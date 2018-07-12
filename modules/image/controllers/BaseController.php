<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/23
 * Time: 下午5:24
 */
namespace app\modules\image\controllers;

use yii\helpers\FileHelper;
use yii\imagine\Image as Imagine;
use yii\web\Controller;
use yii\web\Response;

class BaseController extends Controller
{

    public function showImage($fullPath, $defaultImagePath = '')
    {
        return $this->redirectImage($fullPath);
    }

    private function renderImage($fullPath) {
        $imagine = Imagine::getImagine();
        $mimeType = FileHelper::getMimeType($fullPath);
        $extensions = FileHelper::getExtensionsByMimeType($mimeType);
        $formats = ['gif', 'jpeg', 'png', 'wbmp', 'xbm'];
        $format = array_intersect($extensions, $formats);
        $format = array_pop($format);
        $response = \Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        return $imagine->open($fullPath)->show($format);
    }

    private function redirectImage($fullPath)
    {
        $pos = strpos($fullPath, '/s1');
        $path = substr($fullPath, $pos+3);
        $url = 'http://s1.'.DOMAIN.'/' .trim($path, '/');
        return \Yii::$app->getResponse()->redirect($url, 301);
    }


}