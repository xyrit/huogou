<?php
/**
 * 用户中心晒单模块
 * User: chenyi
 * Date: 2015/9/28
 * Time: 9:15
 */
namespace app\modules\member\controllers;

use app\models\Order;
use app\services\Period;
use Yii;
use yii\base\Controller;
use app\models\ShareTopic;
use yii\web\NotFoundHttpException;
use app\models\ShareTopicImage;
use app\modules\image\models\UploadForm;
use yii\web\UploadedFile;
use app\models\Image;

class ShareController extends BaseController
{
    /**
     * 晒单列表
     */
    public function actionIndex()
    {
        $t = Yii::$app->request->get('t', 0);
        $userId = Yii::$app->user->id;

        if ($t == 0) {
            $shareTopic = ShareTopic::getListByType(10, 0, 0, 10, 0, $userId);
        } else {

        }

        foreach ($shareTopic['list'] as &$val) {
            $val['url'] = Image::getShareInfoUrl($val['header_image'], 'small');
        }

        $notShareOrderCount = Order::find()->where(['user_id' => $userId, 'status' => 3])->count();
        $shareTopic['notShareOrderCount'] = $notShareOrderCount;

        return $this->render('index', $shareTopic);
    }
    
    /**
     * 添加晒单
     */
    public function actionAdd()
    {
        $request = Yii::$app->request;
        $orderId = $request->get('id', 0);
        if($request->isPost){
            $model = new ShareTopic();
            $post = $request->post();
            if (ShareTopic::updateTopic($model, $post)) {
                return Order::updateAll(['status' => 8], ['id' => $orderId]);
            }
        }

        $orderInfo = Order::findOne(['id' => $orderId]);

        $periodInfo = Period::info($orderInfo['period_id']);
        $periodInfo['goods_picture_url'] = Image::getProductUrl($periodInfo['goods_picture'], '200', '200');
        if (empty($periodInfo)) {
            throw new NotFoundHttpException("页面未找到");
        }

        $data['periodInfo'] = $periodInfo;
        $data['orderInfo'] = $orderInfo;

        return $this->render("addshare", $data);
    }
    
    /**
     * 晒单详情
     */
    public function actionDetail()
    {
        $shareId = Yii::$app->request->get('id');
        
        $shareInfo = ShareTopic::findOne($shareId);
        
        if (empty($shareInfo)) {
            throw new NotFoundHttpException("页面未找到");
        }
        
        //获取图片
        $shareTopicImages = ShareTopicImage::getImagesByShareTopicId($shareId);
        $pictures = array();
        foreach ($shareTopicImages as $image) {
            $imagePath = Image::getShareInfoFullPath($image, 'share');
            $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
            $sourceSize = getimagesize($imagePath);
            $picture['width'] = $sourceSize['0'];
            $picture['height'] = $sourceSize['1'];
            $picture['basename'] = $image;
            $picture['url'] = Image::getShareInfoUrl($image, 'share');
            $pictures[] = $picture;
        }
        
        return $this->render("detail", ['info' => $shareInfo, 'pictures' => $pictures]);
    }
    
    /**
     * 编辑晒单
     */
    public function actionEdit()
    {
        $request = Yii::$app->request;
        $shareId = $request->get('id');
        $shareInfo = ShareTopic::findOne($shareId);
        if (!$shareInfo) {
            throw new NotFoundHttpException("页面未找到");
        }
        //获取图片
        $pictures = array();
        $shareTopicImage = ShareTopicImage::getImagesByShareTopicId($shareId);
        foreach ($shareTopicImage as $image) {
            $imagePath = Image::getShareInfoFullPath($image, 'share');
            $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
            $sourceSize = getimagesize($imagePath);
            $picture['width'] = $sourceSize['0'];
            $picture['height'] = $sourceSize['1'];
            $picture['basename'] = $image;
            $picture['url'] = Image::getShareInfoUrl($image, 'share');
            $pictures[] = $picture;
        }

        $periodInfo = Period::info($shareInfo['period_id']);
        $periodInfo['goods_picture_url'] = Image::getProductUrl($periodInfo['goods_picture'], '200', '200');
        if (empty($periodInfo)) {
            throw new NotFoundHttpException("页面未找到");
        }

        $data['periodInfo'] = $periodInfo;
        $data['info'] = $shareInfo;
        $data['pictures'] = $pictures;
        
        return $this->render('edit', $data);
    }
    
    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('imageFile');
            $uploadData = $model->uploadShareInfo();
            if ($uploadData) {
                if ($uploadData['error'] == 1) {
                    echo '<script type="text/javascript">window.parent.ShowError("'.$uploadData['message'].'")</script>';
                } else {
                    $imagePath = Image::getShareInfoFullPath($uploadData['basename'], 'share');
                    $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
                    $sourceSize = getimagesize($imagePath);
                    $width = $sourceSize['0'];
                    $height = $sourceSize['1'];
                    // file is uploaded successfully
                    echo '<script type="text/javascript">window.parent.UploadFileScriptAPI("'.$uploadData['basename'].'",'.$width.','.$height.')</script>';
                    //echo Json::encode($uploadData);
                }
            }
        }
    }
    
    public function actionDelImage()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $picture = $request->post('picture', 0);
            $shareTopicId = $request->post('share_topic_id', 0);
            $ShareTopicImage = ShareTopicImage::findOne(['share_topic_id' => $shareTopicId, 'basename' => $picture]);

            if ($ShareTopicImage) {
                Image::deleteShareInfoImage($picture);
                $ShareTopicImage->delete();
            }
        }
        return true;
    }
}