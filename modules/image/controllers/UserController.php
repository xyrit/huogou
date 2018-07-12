<?php
/**
 * User: chenyi
 * Date: 15/9/30
 * Time: 09:56
 */
namespace app\modules\image\controllers;

use app\helpers\Brower;
use app\services\User as ServiceUser;
use Yii;
use app\models\Image;


class UserController extends BaseController
{

    public function actionFace()
    {

        $request = Yii::$app->request;
        $basename = $request->get('basename');
        $width = $request->get('width');

        $from = Brower::whereFrom();
        if ($from == 2) {
            if ($basename == '000000000000.jpg') {
                $basename = '111111111111.jpg';
            }
        }

        $fullPath = Image::getUserFaceFullPath($basename, $width);
        $sftp = \Yii::$app->sftp;
        $imagePath = $sftp->getSFtpPath($fullPath);
        if ($width=='org' && !file_exists($imagePath)) {
            $fullPath =  Image::getUserFaceFullPath($basename, '160');
        }
        if (!file_exists($sftp->getSFtpPath($fullPath))) {
            if ($from == 2) {
                $basename = '111111111111.jpg';
            } else {
                $basename = '000000000000.jpg';
            }
            $fullPath =  Image::getUserFaceFullPath($basename, '160');
        }
        return $this->showImage($fullPath);



    }


}