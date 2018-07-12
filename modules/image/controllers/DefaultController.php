<?php

namespace app\modules\image\controllers;

use app\models\Image;
use yii\web\Controller;
use Yii;

class DefaultController extends Controller
{


    public function actionDelete()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $bigPicId = $request->post('big_pic', 0);
            $thumbPicId = $request->post('thumb_pic', 0);
            $smallPicId = $request->post('small_pic', 0);
            $imageIds[] = $smallPicId;
            $imageIds[] = $bigPicId;
            $imageIds[] = $thumbPicId;
            foreach ($imageIds as $imgId) {
                $image = Image::findOne($imgId);
                @unlink($image->getFullpath());
                $image->delete();
            }
        }
        return true;
    }
}
