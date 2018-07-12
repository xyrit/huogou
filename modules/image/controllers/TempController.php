<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/28
 * Time: 下午3:14
 */

namespace app\modules\image\controllers;

use app\models\Image;
use Yii;

class TempController extends BaseController
{

    public function actionView()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $size = $request->get('size');
        if (empty($size)) {
            $fullPath = Image::getTempImageFullPath($basename, 250, 250);
        }
        $fullPath = Image::getTempImageFullPath($basename, $size, $size);
        return $this->showImage($fullPath);
    }

}