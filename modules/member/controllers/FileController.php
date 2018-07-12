<?php

namespace app\modules\member\controllers;

use app\modules\api\controllers\BaseController;


use Yii;
use app\services\File;

use yii\helpers\Json;
use yii\helpers\Reresponsese;
use yii\web\Request;


use yii\web\UploadedFile;
use app\modules\image\models\UploadForm;

class FileController extends BaseController
{
    
    public $enableCsrfValidation = FALSE;
    /**
     * 上传头像
     */
    public function actionFace()
    { 
       $request =  Yii::$app->request;
       if($request->isPost){
           echo File::face();
       }
        /*
        $model = new UploadForm();
        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('Filedata');
   
            $uploadData = $model->uploadFaceInfo();

            if ($uploadData) {
                print_r($uploadData);

            }
        }
       */
     
    }
    
    /**
     * 保存头像
     */
    public function actionCrop(){
        
        $request =  Yii::$app->request;
        $post = $request->post();
        if($request->isPost){
            $img = File::crop($post); 
            File::updateFace($img);
            echo json_encode($img);
        }
    
    }
}


