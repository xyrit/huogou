<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/22
 * Time: 下午4:23
 */
namespace app\modules\image\controllers;

use app\models\Image;
use Yii;

class ProductController extends BaseController
{

    public function actionView()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $width = $request->get('width');
        $height = $request->get('height');
        $fullPath = Image::getProductFullPath($basename, $width, $height);
        return $this->showImage($fullPath);
    }

    public function actionInfo()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $fullPath = Image::getProductInfoFullPath($basename);
        return $this->showImage($fullPath);
    }
}