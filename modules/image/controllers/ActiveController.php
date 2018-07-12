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

class ActiveController extends BaseController
{
    public function actionInfo()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $size = Yii::$app->request->get('size', 'org');
        $fullPath = Image::getActiveInfoFullPath($basename, $size);
        return $this->showImage($fullPath);
    }
}