<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/22
 * Time: 下午12:03
 */
namespace app\modules\image\models;

use app\models\Image as Image;
use yii\base\Model;
use yii\web\UploadedFile;
use Yii;

class UploadForm extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $imageFile;

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'extensions' => 'png, jpg, jpeg, gif', 'maxFiles' => 1, 'maxSize'=>1024*1024*5,
                'tooBig'=>'只能上传jpg,png,bmp格式,小于4M的图片',
                'wrongExtension'=>'只能上传jpg,png,gif格式,小于5M的图片',
                'checkExtensionByMimeType'=>false,
            ],
        ];
    }

    public function uploadProduct()
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getProductFullPath($newName, 'org', 'org');//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            Image::createProductImage($filePath, $newName);
            return ['basename' => $newName, 'error'=>0];
        } else {
            return ['error'=>1, 'message'=>$this->getFirstError('imageFile')];
        }
    }

    public function uploadProductInfo()
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getProductInfoFullPath($newName);//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            Image::createProductInfoImage($filePath, $newName);
            return ['url' => Image::getProductInfoUrl($newName), 'error' => 0];
        } else {
            return ['error'=>1, 'message'=>$this->getFirstError('imageFile')];
        }
    }

    public function uploadGroupInfo()
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getGroupInfoFullPath($newName, 'org');//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            Image::createGroupInfoImage($filePath, $newName);
            return ['url' => Image::getGroupInfoUrl($newName, 'org'), 'error'=>0];
        } else {
            return ['error'=>1, 'message'=>$this->getFirstError('imageFile')];
        }
    }

    public function uploadGroupIcon()
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getGroupIconFullPath($newName);//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            Image::createGroupIconImage($filePath, $newName);
            return ['basename' => $newName, 'error'=>0];
        } else {
            return ['error'=>1, 'message'=>$this->getFirstError('imageFile')];
        }
    }

    //banner
    public function uploadBannerInfo($pic)
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getBannerInfoFullPath($newName, 'org');//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            Image::createBannerInfoImage($filePath, $newName, $pic);
            return ['basename' => $newName, 'error'=>0];
        } else {
            return ['error'=>1, 'message'=>$this->getFirstError('imageFile')];
        }
    }

    public function uploadShareInfo()
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getShareInfoFullPath($newName, 'share');//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            Image::createShareInfoImage($filePath, $newName);
            return ['basename' => $newName, 'error'=>0];
        } else {
            return ['error'=>1, 'message'=>'只能上传jpg,png,bmp格式,小于5M的图片'];
        }
    }

    public function uploadTempImg($width, $height)
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getTempImageFullPath($newName, 'org', 'org');//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            Image::createTempImage($filePath, $newName, $width, $height);
            @unlink($filePath);
            return ['basename' => $newName, 'error'=>0];
        } else {
            return ['error'=>1, 'message'=>$this->getFirstError('imageFile')];
        }
    }

    public function uploadAvatar()
    {
        if ($this->validate()) {
            $newName = Image::generateName() . '.jpg';
            $filePath = Image::getUserFaceFullPath($newName, 'org');//原图路径
            $filePath = Yii::$app->sftp->getSFtpPath($filePath);
            $dir = dirname($filePath);
            Yii::$app->sftp->createDir($dir, 0777);
            $this->imageFile->saveAs($filePath);
            $width = $this->imageFile->size[0];
            $height = $this->imageFile->size[1];
            Image::createUserFaceImage($filePath,$newName,0,0,$width,$height);
            return ['basename' => $newName, 'error'=>0];
        } else {
            return ['error'=>1, 'message'=>$this->getFirstError('imageFile')];
        }
    }


}