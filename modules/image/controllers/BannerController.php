<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/24
 * Time: 18:50
 */

namespace app\modules\image\controllers;

use app\models\Image;
use Yii;

class BannerController extends BaseController
{
    public function actionInfo()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $size = Yii::$app->request->get('size');
        $fullPath = Image::getBannerInfoFullPath($basename, $size);
        return $this->showImage($fullPath);
    }
}