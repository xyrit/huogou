<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/3
 * Time: 下午1:59
 */
namespace app\modules\mobile\controllers;

use  app\modules\mobile\controllers;
use app\models\ProductCategory;
use app\models\ShareComment;
use app\models\ShareReply;
use app\services\Period;
use app\services\Product;
use app\services\Share;
use app\services\UserInfo;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use app\models\ShareTopic;
use app\helpers\DateFormat;
use app\models\ShareTopicImage;
use app\models\Image;
use app\services\User;

class PostController extends BaseController
{

    public function actionIndex()
    {
        return $this->render('index', []);
    }

    public function actionDetail()
    {
        $id = \Yii::$app->request->get('pid');

        if (!isset($id)) {
            throw new NotFoundHttpException("页面未找到");
        }

        $shareTopic = Share::info($id);
        //该商品正在进行的期数
        $curPeriodInfo = Product::curPeriodInfo($shareTopic['product_id']);
        $shareTopic['curPeriodNumber'] = empty($curPeriodInfo) ? '' : $curPeriodInfo['period_number'];

   
        // 浏览次数+1
        if (!ShareTopic::addColumnByNum($id, 'view_num', 1)) {
            throw new NotFoundHttpException("页面未找到");
        }

        // 获取图片
        $shareTopicImages = ShareTopicImage::getImagesByShareTopicId($id);
        foreach ($shareTopicImages as $key => $image) {
            $shareTopicImages[$key] = Image::getShareInfoUrl($image, 'big');
        }
    
        // 左侧期数信息
        $periodInfo = Period::info($shareTopic['period_id']);
        if (!empty($periodInfo)) {
            $userInfo = User::baseInfo($periodInfo['uid']);
            $periodInfo['goods_picture_url'] = Image::getProductUrl($periodInfo['goods_picture'], 200, 200);
            $periodInfo['user_avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 160);
            $periodInfo['raff_time'] = DateFormat::formatTime(strtotime($periodInfo['raff_time']));
            $periodInfo['price'] = intval($periodInfo['price']);
        }

        return $this->render('detail', [
            'detail' => $shareTopic,
            //'list' => $shareComment['list'],
            'pictures' => $shareTopicImages,
            'periodInfo' => $periodInfo,
            'is_up' => ShareTopic::is_up($id),
            ]);
        return $this->render('detail', []);
    }


    public function actionList()
    {
        $productId = \Yii::$app->request->get('pid');
        return $this->render('list',[
            'productId'=>$productId,
        ]);
    }

}