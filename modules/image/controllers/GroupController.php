<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/24
 * Time: 18:50
 */

/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/22
 * Time: 下午4:23
 */
namespace app\modules\image\controllers;

use app\models\Image;
use Yii;

class GroupController extends BaseController
{

    public function actionIcon()
    {
        $request = Yii::$app->request;
        $path = $request->get('basename');
        $fullPath = Image::getGroupIconFullPath($path);
        $this->showImage($fullPath);
    }

    public function actionInfo()
    {
        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $size = Yii::$app->request->get('size');
        $fullPath = Image::getGroupInfoFullPath($basename, $size);
        return $this->showImage($fullPath);
    }
}