<?php
/**
 * User: chenyi
 * Date: 15/9/24
 * Time: 16:54
 */

namespace app\modules\share\controllers;

use app\controllers\BaseController;
use app\helpers\Brower;
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

class DefaultController extends BaseController
{
    /**
     * 晒单首页
     * @return \yii\base\string
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        if ($request->isGet) {
            if (!$request->cookies->getValue('pcview')) {
                if (Brower::isMcroMessager()) {
                    return $this->redirect(['/weixin/post']);
                } elseif(Brower::isMobile()) {
                    return $this->redirect(['/mobile/post']);
                }
            }
        }
        //1最新 2精华  3推荐  4人气
        $typeId = $request->get('t');
        $catId = $request->get('s', 0);

        if (!isset($typeId) || !in_array($typeId, array(10, 20, 30, 40))) {
            $typeId = 10;
        }
        $totalCount = ShareTopic::find()->where(['is_pass' => 1, 'is_show' => 1])->count();

        // 商品类型
        $productCategory = ProductCategory::firstLevel();
        $selectCateName = ProductCategory::findOne(['id' => $catId]);

        return $this->render('index', [
            't' => $typeId,
            's' => $catId,
            'productCategory' => $productCategory,
            'totalCount' => $totalCount,
            'selectCateName' => isset($selectCateName->name) ? $selectCateName->name : '全部商品'
        ]);
    }

    /**
     * 晒单详情页
     */
    public function actionDetail()
    {

        $request = Yii::$app->request;
        $id = $request->get('id');

        //微信手机端跳转
        if ($request->isGet) {
            if (!$request->cookies->getValue('pcview')) {
                if (Brower::isMcroMessager()) {
                    return $this->redirect(['/weixin/post/detail','pid'=>$id]);
                } elseif(Brower::isMobile()) {
                    return $this->redirect(['/mobile/post/detail','pid'=>$id]);
                }
            }
        }


        if (!isset($id)) {
            throw new NotFoundHttpException("页面未找到");
        }

        $userId = Yii::$app->user->id;

        $shareTopic = Share::info($id);
        
        if ($shareTopic['is_pass'] != 1) {
            throw new NotFoundHttpException("页面未找到");
        }
        //该商品正在进行的期数
        $curPeriodInfo = Product::curPeriodInfo($shareTopic['product_id']);
        $shareTopic['curPeriodNumber'] = empty($curPeriodInfo) ? '' : $curPeriodInfo['period_number'];

        // TA的其他晒单
        /*$tShareTopic = ShareTopic::getListByType(10, 0, 0, 4, 1, $userId);

        foreach ($tShareTopic['list'] as $key => $topic) {
            if ($topic['id'] == $id) {
                unset($tShareTopic['list'][$key]);
                break;
            }
        }*/

        // 浏览次数+1
        if (!ShareTopic::addColumnByNum($id, 'view_num', 1)) {
            throw new NotFoundHttpException("页面未找到");
        }

        // 获取图片
        $shareTopicImages = ShareTopicImage::getImagesByShareTopicId($id);

        foreach ($shareTopicImages as $key => $image) {
            $shareTopicImages[$key] = Image::getShareInfoUrl($image, 'big');
        }

        $loginUser = User::baseInfo($userId);
        $loginUser['avatar'] = Image::getUserFaceUrl($loginUser['avatar'], 160);

        // 左侧期数信息
        $periodInfo = Period::info($shareTopic['period_id']);
        if (!empty($periodInfo)) {
            $userInfo = User::baseInfo($periodInfo['uid']);
            $periodInfo['goods_picture_url'] = Image::getProductUrl($periodInfo['goods_picture'], 200, 200);
            $periodInfo['user_avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 160);
            $periodInfo['raff_time'] = DateFormat::formatTime(strtotime($periodInfo['raff_time']));
            $periodInfo['price'] = intval($periodInfo['price']);
        }

        // 获取评论
        /*$shareComment = ShareComment::getList($id, 10);

        foreach ($shareComment['list'] as &$comment) {
            $comment['created_at'] = DateFormat::formatTime($comment['created_at']);
        }

        $userIds = ArrayHelper::getColumn($shareComment['list'], 'user_id');
        $userInfos = User::baseInfo($userIds);

        foreach ($shareComment['list'] as &$comment) {
            $comment['user_name'] = $userInfos[$comment['user_id']]['username'];
            $comment['user_avatar'] = $userInfos[$comment['user_id']]['avatar'];
        }*/

        //该商品的其他获得者

        //最新晒单
        $shareTopicNew = ShareTopic::getListByType(10, 0, 0, 4, 1);
        foreach ($shareTopicNew['list'] as &$new) {
            //取三张图片
            $new['picture'] = ShareTopicImage::getImagesByShareTopicId($new['id'], 3);
            foreach ($new['picture'] as $key => $image) {
                $new['picture'][$key] = Image::getShareInfoUrl($image, 'small');
            }
        }
        return $this->render('detail', [
            'detail' => $shareTopic,
            //'list' => $shareComment['list'],
            'pictures' => $shareTopicImages,
            'shareNew' => $shareTopicNew['list'],
            'periodInfo' => $periodInfo,
            'is_up' => ShareTopic::is_up($id),
            'loginUser' => $loginUser
        ]);
    }

    /**
     * 羡慕
     */
    public function actionUp()
    {
        $id = Yii::$app->request->post('id');

        if (!isset($id)) {
            throw new NotFoundHttpException("页面未找到");
        }

        if (ShareTopic::is_up($id, 1)) {
            return 0;
        } else {
            ShareTopic::addColumnByNum($id, 'up_num', 1);
            return 1;
        }
    }

    public function actionTopicList()
    {
        // $type 10最新 20精华  30推荐  40人气  50评论
        $page = Yii::$app->request->get('page', 1);
        $catId = Yii::$app->request->get('catId', 0);
        $type = Yii::$app->request->get('orderFlag', 10);
        $perpage = Yii::$app->request->get('perpage', 16);
        $shareList = Share::getListByType($page, $catId, $type, $perpage);
        foreach ($shareList['list'] as &$share) {
            $periodInfo = Period::info($share['period_id']);
            $share['product_name'] = $periodInfo['goods_name'];
            $share['period_number'] = $periodInfo['period_number'];
            $share['is_up'] = ShareTopic::is_up($share['id']);
            $share['content'] = mb_substr($share['content'],0,50,'utf-8');
        }
        return json_encode($shareList);
    }
}
