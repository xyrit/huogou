<?php
/**
 * User: chenyi
 * Date: 15/9/30
 * Time: 09:56
 */
namespace app\modules\image\controllers;

use app\models\Image;
use Yii;

class ShareController extends BaseController
{

    public function actionView()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $width = $request->get('width');
        $height = $request->get('height');
        $fullPath = Image::getShareInfoFullPath($basename, $width, $height);
        return $this->showImage($fullPath);
    }

    public function actionInfo()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $size = $request->get('size', 'small');
        $fullPath = Image::getShareInfoFullPath($basename, $size);
        return $this->showImage($fullPath);
    }
}