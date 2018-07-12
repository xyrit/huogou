<?php

namespace app\models;

use app\helpers\ImagickHelper;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "images".
 *
 * @property integer $id
 * @property string $basename
 */
class Image extends \yii\db\ActiveRecord
{
    const TYPE_PRODUCT = 1; // 商品
    const TYPE_PRODUCT_INFO = 2; // 商品信息
    const TYPE_SHARE_INFO = 3;   //晒单话题
    const TYPE_GROUP_INFO = 4;   //圈子话题
    const TYPE_GROUP_ICON = 5;   //圈子ICON
    const TYPE_USERFACE = 6;   //头像
    const TYPE_BANNER = 7;
    const TYPE_TEMP = 8;
    const TYPE_ACTIVE = 9; //活动

    const THUMB_QUALITY = 80;

    const USER_FACE_DEFAULT = '000000000000.jpg';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basename'], 'required'],
            [['basename'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'basename' => 'Basename',
        ];
    }

    /** 根据类型获取前目录路径
     * @param $type
     * @return bool|string
     */
    private static function getPreDirPath($type)
    {
        $sftpBaseDir = '/s1';
        switch ($type) {
            case static::TYPE_PRODUCT :
                $dirPath = Yii::getAlias($sftpBaseDir . '/goodspic/');
                break;
            case static::TYPE_PRODUCT_INFO :
                $dirPath = Yii::getAlias($sftpBaseDir . '/goodsinfo/');
                break;
            case static::TYPE_SHARE_INFO :
                $dirPath = Yii::getAlias($sftpBaseDir . '/sharepic/');
                break;
            case static::TYPE_GROUP_INFO :
                $dirPath = Yii::getAlias($sftpBaseDir . '/grouppic/');
                break;
            case static::TYPE_GROUP_ICON :
                $dirPath = Yii::getAlias($sftpBaseDir . '/groupicon/');
                break;
            case static::TYPE_USERFACE :
                $dirPath = Yii::getAlias($sftpBaseDir . '/userface/');
                break;
            case static::TYPE_BANNER :
                $dirPath = Yii::getAlias($sftpBaseDir . '/banner/');
                break;
            case static::TYPE_ACTIVE :
                $dirPath = Yii::getAlias($sftpBaseDir . '/active/');
                break;
            case static::TYPE_TEMP :
                $dirPath = Yii::getAlias($sftpBaseDir . '/temp/');
                break;
            default :
                $dirPath = '';
                break;
        }
        return $dirPath;
    }

    public static function generateName()
    {
        return date('Ymd') . mt_rand(100000000, 999999999);
    }

    public static function getProductFullPath($basename, $width, $height)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_PRODUCT);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $subDirPath = $year . '/' . $month;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/' . $width . '/' . $height . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    public static $productSize = [
        ['width' => 400, 'height' => 400],
        ['width' => 200, 'height' => 200],
        ['width' => 58, 'height' => 58],
    ];

    public static function createProductImage($sourceFilePath, $basename)
    {
        $imageSize = static::$productSize;
        foreach ($imageSize as $info) {
            $width = $info['width'];
            $height = $info['height'];
            $fullPath = static::getProductFullPath($basename, $width, $height);
            $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
            $dir = dirname($fullPath);
            Yii::$app->sftp->createDir($dir, 0777);

            $image = new ImagickHelper();
            $image->open($sourceFilePath);
            $image->resize_to($width, $height, 'force');
            $image->save_to($fullPath);
        }
    }

    public static function deleteProductImage($basename)
    {
        $imageSize = static::$productSize;
        foreach ($imageSize as $info) {
            $width = $info['width'];
            $height = $info['height'];
            $fullPath = static::getProductFullPath($basename, $width, $height);
            $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
            @unlink($fullPath);
        }
        $fullPath = static::getProductFullPath($basename, 'org', 'org');
        $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
        @unlink($fullPath);
    }

    public static function getProductInfoFullPath($basename)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_PRODUCT_INFO);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $day = substr($basename, 6, 2);
        $subDirPath = $year . '/' . $month . '/' . $day;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    public static function createProductInfoImage($sourceFilePath, $basename)
    {
//        $fullPath = static::getProductInfoFullPath($basename);
//        $dir = dirname($fullPath);
//        FileHelper::createDirectory($dir);
//
//        $image = new GD($sourceFilePath);
//        $image->save($fullPath);
    }

    public static function getUserFaceFullPath($basename, $width)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_USERFACE);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $day = substr($basename, 6, 2);
        $subDirPath = $year . '/' . $month . '/' . $day;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/' . $width . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;

    }

    public static $userFaceSize = [
        '160' => ['width' => 160, 'height' => 160],
        '80' => ['width' => 80, 'height' => 80],
        '30' => ['width' => 30, 'height' => 30],
    ];

    public static $userFaceDefault = '000000000000.jpg';

    public static function createUserFaceImage($sourceFilePath, $basename, $cutX, $cutY, $width, $height)
    {
        $imageSize = static::$userFaceSize;
        $sftp = Yii::$app->sftp;
        foreach ($imageSize as $info) {
            $w = $info['width'];
            $h = $info['height'];
            $fullPath = static::getUserFaceFullPath($basename, $w);
            $fullPath = $sftp->getSFtpPath($fullPath);
            $dir = dirname($fullPath);
            $sftp->createDir($dir, 0777);

            $image = new ImagickHelper();
            $image->open($sourceFilePath);
            $image->crop($cutX, $cutY, $width, $height);
            $image->resize_to($w, $h, 'force');
            $image->save_to($fullPath);
        }
    }

    public static function getGroupIconFullPath($basename)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_GROUP_ICON);
        $dirPath = rtrim($preDirPath, '/') . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    public static function createGroupIconImage($sourceFilePath, $basename)
    {
        $width = 120;
        $height = 120;
        $fullPath = static::getGroupIconFullPath($basename);
        $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
        $dir = dirname($fullPath);
        Yii::$app->sftp->createDir($dir, 0777);

        $image = new ImagickHelper();
        $image->open($sourceFilePath);
        $image->resize_to($width, $height, 'force');
        $image->save_to($fullPath);
    }

    public static function getGroupInfoFullPath($basename, $size)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_GROUP_INFO);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $day = substr($basename, 6, 2);
        $subDirPath = $year . '/' . $month . '/' . $day;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/' . $size . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    //banner
    public static function getBannerInfoFullPath($basename, $size)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_BANNER);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $day = substr($basename, 6, 2);
        $subDirPath = $year . '/' . $month . '/' . $day;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/' . $size . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    //创建banner路径
    public static function createBannerInfoImage($sourceFilePath, $basename, $picsize)
    {
        $imageSize = [
            'big' => ['width' => $picsize['width'], 'height' => $picsize['height']],
            'small' => ['width' => 200, 'height' => 120],
        ];
        foreach ($imageSize as $size => $info) {
            $width = $info['width'];
            $height = $info['height'];
            $fullPath = static::getBannerInfoFullPath($basename, $size);
            $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
            $dir = dirname($fullPath);
            Yii::$app->sftp->createDir($dir, 0777);

            if (!class_exists('Imagick')) {
                \yii\imagine\Image::thumbnail($sourceFilePath, $width, $height)->save($fullPath);
            } else {
                $image = new ImagickHelper();
                $image->open($sourceFilePath);
                $image->resize_to($width, $height, 'force');
                $image->save_to($fullPath);
            }
        }
    }

    public static $groupInfoSize = [
        'big' => ['width' => 400, 'height' => 400],
        'small' => ['width' => 200, 'height' => 200],
    ];

    public static function createGroupInfoImage($sourceFilePath, $basename)
    {

        $imageSize = static::$groupInfoSize;
        foreach ($imageSize as $size => $info) {
            if($info['width'] != '200'){
                $orgSize = getimagesize($sourceFilePath);
                if($orgSize[1] <= 860){
                    $width = $orgSize[1];
                }else{
                    $width = 860;
                }
                $height = $orgSize[0];
            }else{
                $width = $info['width'];
                $height = $info['height'];
            }

            $fullPath = static::getGroupInfoFullPath($basename, $size);
            $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
            $dir = dirname($fullPath);
            Yii::$app->sftp->createDir($dir, 0777);

            if (!class_exists('Imagick')) {
                \yii\imagine\Image::thumbnail($sourceFilePath, $width, $height)->save($fullPath);
            } else {
                $image = new ImagickHelper();
                $image->open($sourceFilePath);
                $image->resize_to($width, $height, 'scale');
                $image->save_to($fullPath);
            }
        }
    }

    public static function getActiveInfoFullPath($basename, $size)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_ACTIVE);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $day = substr($basename, 6, 2);
        $subDirPath = $year . '/' . $month . '/' . $day;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/' . $size . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    public static function getShareInfoFullPath($basename, $size)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_SHARE_INFO);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $day = substr($basename, 6, 2);
        $subDirPath = $year . '/' . $month . '/' . $day;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/' . $size . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    public static $shareInfoSize = [
        'big' => ['width' => 884],
        'small' => ['width' => 200],
        'main' => ['width' => 270],
        'recommend' => ['width' => 555, 'height' => 369],
        'roll' => ['width' => 230, 'height' => 146],
        'mobile' => ['width' => 100, 'height' => 100],
    ];

    public static function createShareInfoImage($sourceFilePath, $basename, $size = 'share', $params = [])
    {
        $fullPath = static::getShareInfoFullPath($basename, $size);
        $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
        if (file_exists($fullPath)) return;
        $imageSize = static::$shareInfoSize;
        $sourceSize = getimagesize($sourceFilePath);
        if ($size == 'share') {
            $width = $sourceSize['0'];
            $height = $sourceSize['1'];
        } else {
            $width = $imageSize[$size]['width'];
            $height = isset($imageSize[$size]['height']) ? $imageSize[$size]['height'] : 0;
        }

        $dir = dirname($fullPath);
        Yii::$app->sftp->createDir($dir, 0777);

        $image = new ImagickHelper();
        $image->open($sourceFilePath);
        if ($size == 'recommend' || $size == 'roll' || $size == 'mobile') {
            $image->rotate($params['angle']);
            $image->crop($params['x'], $params['y'], $params['w'], $params['h']);
            $image->resize_to($width, $height, 'force');
        } elseif ($size == 'share') {
            $image->resize_to($width, $height, 'scale');
        } else {
            $height = ceil($sourceSize[1] * $width / $sourceSize[0]);
            $image->resize_to($width, $height, 'scale');
            if ($size != 'main') {
                $image->add_text($params['text'], 0, $height/2, $params['angle'], ['font_size' => $params['font_size'], 'fill_color' => $params['fill_color'], 'fill_opacity' => $params['fill_opacity']]);
                $image->add_text($params['text'], 0, 5*$height/6, $params['angle'], ['font_size' => $params['font_size'], 'fill_color' => $params['fill_color'], 'fill_opacity' => $params['fill_opacity']]);
                $waterPath = Yii::$app->params['skinUrl'] . '/img/water.png';
                //$waterSize = getimagesize($waterPath);
                $waterWidth = 160;
                $waterHeight = 100;
                if ($size == 'big') {
                    $image->add_watermark($waterPath, $width - $waterWidth - 5, $height - $waterHeight - 5);
                }
            }
        }
        $image->save_to($fullPath);
    }

    public static function deleteShareInfoImage($basename)
    {
        $imageSize = static::$shareInfoSize;
        foreach ($imageSize as $size => $info) {
            $fullPath = static::getShareInfoFullPath($basename, $size);
            $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
            @unlink($fullPath);
        }
    }

    public static function getTempImageFullPath($basename, $width, $height)
    {
        $preDirPath = static::getPreDirPath(static::TYPE_TEMP);
        $year = substr($basename, 0, 4);
        $month = substr($basename, 4, 2);
        $day = substr($basename, 6, 2);
        $subDirPath = $year . '/' . $month . '/' . $day;
        $dirPath = rtrim($preDirPath, '/') . '/' . $subDirPath . '/' . $width . '/' . $height . '/';
        $fullPath = $dirPath . $basename;
        return $fullPath;
    }

    public static function createTempImage($sourceFilePath, $basename, $width, $height)
    {
        $fullPath = static::getTempImageFullPath($basename, $width, $height);
        $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
        $dir = dirname($fullPath);
        Yii::$app->sftp->createDir($dir, 0777);
        $image = new ImagickHelper();
        $image->open($sourceFilePath);
        $image->resize_to($width, $height, 'scale_fill');
        $image->save_to($fullPath);
    }

    public static function createTempImageByContent($content, $basename, $width, $height)
    {
        $fullPath = static::getTempImageFullPath($basename, $width, $height);
        $fullPath = Yii::$app->sftp->getSFtpPath($fullPath);
        $dir = dirname($fullPath);
        Yii::$app->sftp->createDir($dir, 0777);
        $image = new ImagickHelper();
        $image->load($content);
        $image->resize_to($width, $height, 'scale_fill');
        $image->save_to($fullPath);
    }


    public static function getProductUrl($basename, $width, $height)
    {
        return Url::to(['/image/product/view', 'basename' => $basename, 'width' => $width, 'height' => $height]);
    }

    public static function getProductInfoUrl($basename)
    {
        return Url::to(['/image/product/info', 'basename' => $basename]);
    }

    public static function getGoupIconUrl($basename)
    {
        return Url::to(['/image/group/icon', 'basename' => $basename]);
    }

    public static function getGroupInfoUrl($basename, $size)
    {
        return Url::to(['/image/group/info', 'basename' => $basename, 'size' => $size]);
    }

    public static function getShareInfoUrl($basename, $size)
    {
        return Url::to(['/image/share/info', 'basename' => $basename, 'size' => $size]);
    }

    public static function getUserFaceUrl($basename, $width)
    {
        if (!$basename) {
            return Url::to(['/image/user/face', 'width' => $width, 'basename' => static::$userFaceDefault]);
        }
        return Url::to(['/image/user/face', 'width' => $width, 'basename' => $basename]);
    }

    public static function getBannerInfoUrl($basename, $size)
    {
        return Url::to(['/image/banner/info', 'basename' => $basename, 'size' => $size]);
    }

    public static function getActiveInfoUrl($basename, $size)
    {
        return Url::to(['/image/active/info', 'basename' => $basename, 'size' => $size]);
    }
}
