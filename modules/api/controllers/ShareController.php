<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午4:19
 */
namespace app\modules\api\controllers;

use app\helpers\DateFormat;
use app\helpers\Message;
use app\models\BlackList;
use app\models\ExperienceFollowDistribution;
use app\models\Order;
use app\models\PointFollowDistribution;
use app\models\ShareComment;
use app\models\ShareReply;
use app\models\ShareTopic;
use app\models\ShareTopicImage;
use app\modules\admin\models\Keyword;
use app\services\Member;
use app\services\Period;
use app\services\Product;
use app\services\User;
use Yii;
use app\services\Share;
use app\modules\image\models\UploadForm;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;
use app\models\Image;

class ShareController extends BaseController
{
    /**
     * 晒单列表
     */
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
            $share['product_name'] = isset($periodInfo['goods_name'])?$periodInfo['goods_name']:'';
            $share['period_number'] = isset($periodInfo['period_number'])?$periodInfo['period_number']:'';
            $share['period_no'] = isset($periodInfo['period_no'])?$periodInfo['period_no']:'';//期号
            $share['is_up'] = ShareTopic::is_up(['id'=>$share['id'],'userid'=>$this->userId]);
            $share['content'] = mb_substr($share['content'],0,50,'utf-8');
        }
        return $shareList;
    }

    /**
     * 晒单详情
     */
    public function actionDetail()
    {
        $shareId = Yii::$app->request->get('id');
        $shareInfo = ShareTopic::findOne($shareId)->toArray();

        $shareInfo['is_up'] = ShareTopic::is_up($shareId);

        //获取图片
        $shareTopicImages = ShareTopicImage::getImagesByShareTopicId($shareId);
        $pictures = array();
        if ($shareInfo['is_pass'] == 1) {
            foreach ($shareTopicImages as $image) {
                $picture['basename'] = $image;
                $picture['url'] = Image::getShareInfoUrl($image, 'big');
                $imagePath = Image::getShareInfoFullPath($image, 'share');
                $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
                $sourceSize = getimagesize($imagePath);
                $picture['width'] = $sourceSize['0'];
                $picture['height'] = $sourceSize['1'];
                $pictures[] = $picture;
            }
        } else {
            foreach ($shareTopicImages as $image) {
                $picture['basename'] = $image;
                $picture['url'] = Image::getShareInfoUrl($image, 'big');
                $imagePath = Image::getShareInfoFullPath($image, 'share');
                $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
                $sourceSize = getimagesize($imagePath);
                $picture['width'] = $sourceSize['0'];
                $picture['height'] = $sourceSize['1'];
                $pictures[] = $picture;
            }
        }

        $periodInfo = [];
        $period = Period::info($shareInfo['period_id']);
        if (!empty($period)) {
            $userInfo = User::baseInfo($period['uid']);
            $periodInfo['goods_picture'] = $period['goods_picture'];
            $periodInfo['user_avatar'] = $userInfo['avatar'];
            $periodInfo['raff_time'] = DateFormat::formatTime(strtotime($period['raff_time']));
            $periodInfo['price'] = intval($period['price']);
            $periodInfo['user_name'] = $period['user_name'];
            $periodInfo['user_home_id'] = $period['user_home_id'];
            $periodInfo['user_id'] = $period['uid'];
            $periodInfo['goods_id'] = $period['goods_id'];
            $periodInfo['goods_name'] = $period['goods_name'];
            $periodInfo['period_id'] = $period['period_id'];
            $periodInfo['period_number'] = $period['period_number'];
            $periodInfo['period_no'] = $period['period_no']; //期号

            $periodInfo['lucky_code'] = $period['lucky_code'];
            $periodInfo['user_buy_num'] = $period['user_buy_num'];
        }
        
        return ['info' => $shareInfo, 'pictures' => $pictures, 'periodInfo' => $periodInfo];
    }

    /**
     * 我的晒单
     */
    public function actionUserShare()
    {

    }

    /**
     * 晒单评论
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionCommentList()
    {
        $id = Yii::$app->request->get('id');
        $page = Yii::$app->request->get('page', 1);
        $perpage = Yii::$app->request->get('perpage', 10);

        $shareCommentList = Share::commentList($id, $page, $perpage, $this->userId);
        foreach ($shareCommentList['list'] as &$comment) {
            $comment['is_up'] = ShareComment::is_up($comment['id']);
        }
        return $shareCommentList;
    }

    /**
     * 评论
     * @return mixed
     */
    public function actionComment()
    {
        if (empty($this->userId)) {
            return ['code' => 201];
        }

        $blackList = BlackList::findOne(['user_id' => $this->userId, 'type' => 1]);
        if ($blackList) {
            return ['code' => 101, 'msg' => '您无权操作'];
        }

        $key = $this->userId . 'share_comment' . date('Ymd');
        $commentNum = Yii::$app->cache->get($key);
        $commentNum = $commentNum ? $commentNum : 0;
        if ($commentNum >= 20) {
            return ['code' => 101, 'msg' => '评论次数已达上限'];
        }

        $get = Yii::$app->request->get();

        $keywords = Keyword::keywords($get['content']);
        if($keywords == 1){
            return ['code' => 120, 'msg' => '内容有敏感词汇'];
        }

        $get['user_id'] = $this->userId;
        $result = ShareComment::addCommit($get);

        if ($result) {
            $shareInfo = Share::info($get['share_topic_id']);
            $shareUserInfo = User::baseInfo($shareInfo['user_id']);
            $commentUserInfo = User::baseInfo($this->userId);
            $productInfo = Product::info($shareInfo['product_id']);
            Message::send(31, $shareUserInfo['id'], ['nickname' => $shareUserInfo['username'], 'oppositeNickname' => $commentUserInfo['username'], 'goodsName' => $productInfo['name']]);
            $member = new Member(['id' => $this->userId]);
            // 加福分
            //$member->editPoint(PointFollowDistribution::NUMBER_SHARE_COMMENT, PointFollowDistribution::POINT_SHARE_COMMENT, "晒单评论获得福分");
            // 加经验
            $member->editExperience(ExperienceFollowDistribution::NUMBER_SHARE_COMMENT, ExperienceFollowDistribution::EXPR_SHARE_COMMENT, "晒单评论获得经验");
        }

        Yii::$app->cache->set($key, $commentNum+1, strtotime((date('y-m-d') . ' 23:59:59')) - time());

        return ['code' => 100];
    }

    /**
     * 晒单评论回复
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionReplyList()
    {
        $id = Yii::$app->request->get('id');
        $page = Yii::$app->request->get('page', 1);
        $perpage = Yii::$app->request->get('perpage', 10);

        $shareReplyList = Share::replyList($id, $page, $perpage, $this->userId);
        return $shareReplyList;
    }

    /**
     * 晒单回复
     */
    public function actionCommentReply()
    {
        if (empty($this->userId)) {
            return ['code' => 201];
        }

        $blackList = BlackList::findOne(['user_id' => $this->userId, 'type' => 1]);
        if ($blackList) {
            return ['code' => 101, 'msg' => '您无权操作'];
        }

        $key = $this->userId . 'share_comment' . date('Ymd');
        $commentNum = Yii::$app->cache->get($key);
        $commentNum = $commentNum ? $commentNum : 0;
        if ($commentNum >= 20) {
            return ['code' => 101, 'msg' => '评论次数已达上限'];
        }

        $get = Yii::$app->request->get();
        $get['user_id'] = $this->userId;
        $result = ShareReply::addReply($get);
        if ($result) {
            $member = new Member(['id' => $this->userId]);
            // 加福分
            //$member->editPoint(PointFollowDistribution::NUMBER_SHARE_COMMENT, PointFollowDistribution::POINT_SHARE_COMMENT, "晒单评论获得福分");
            // 加经验
            $member->editExperience(ExperienceFollowDistribution::NUMBER_SHARE_COMMENT, ExperienceFollowDistribution::EXPR_SHARE_COMMENT, "晒单评论获得经验");
        }

        Yii::$app->cache->set($key, $commentNum+1, strtotime((date('y-m-d') . ' 23:59:59')) - time());

        return ['code' => 100];
    }

    /**
     * 删除晒单回复
     */
    public function actionReplyDel()
    {
        if (empty($this->userId)) {
            return ['code' => 201];
        }
        $id = Yii::$app->request->get('id');

        $result = ShareReply::deleteAll(['id' => $id, 'user_id' => $this->userId]);
        return ['code' => 100];
    }

    /**
     * 用户未晒单列表
     */
    public function actionNotShareOrder()
    {
        $member = new Member();
        $member->id = Yii::$app->user->id;
        $notShareOrder = $member->getShareList(1, 1, 10);
        return $notShareOrder;
    }

    /**
     * 其他获得者
     */
    public function actionOtherShareOrder()
    {
        $page = Yii::$app->request->get('page', 1);
        $productId = Yii::$app->request->get('productId', 0);
        $perpage = Yii::$app->request->get('perpage', 20);
        $exceptUserId = Yii::$app->request->get('exceptUserId');
        $orderList = Share::getOrderByProductId($productId, $page, $perpage, $exceptUserId);

        return $orderList;
    }

    /**
     * 羡慕
     */
    public function actionUp()
    {
        $id = Yii::$app->request->get('id');

        if (!isset($id)) {
            return ['code' => 101];
        }

        if (ShareTopic::is_up($id, 1)) {
            return ['code' => 101];
        } else {
            ShareTopic::addColumnByNum($id, 'up_num', 1);
            return ['code' => 100];
        }
    }

    public function actionShareList(){
        $productId = Yii::$app->request->get("pid",'0');
        $page = Yii::$app->request->get("page",'1');

        return Share::getList($productId,$page);
    }

    public function actionAddShare()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = Yii::$app->request;
        $orderId = $request->get('order_id');

        $orderInfo = Order::findOne(['id' => $orderId, 'user_id' => $this->userId]);
        if (empty($orderInfo)) {
            return ['code' => 102, 'msg' => '订单不存在'];
        }

        $get = $request->get();
        $get['user_id'] = $this->userId;
        $get['period_id'] = $orderInfo['period_id'];
        $get['from'] = $this->userInfo['from'];
        $result = ShareTopic::add($get);

        if (is_array($result)) {
            return $result;
        } elseif ($result) {
            Order::updateAll(['status' => 8], ['id' => $orderId]);
            return ['code' => 100];
        }

        return ['code' => 101];
    }

    public function actionEditShare()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = Yii::$app->request;
        $shareId = $request->get('id');
        $shareTopic = ShareTopic::findOne(['id' => $shareId, 'user_id' => $this->userId]);
        if (empty($shareTopic)) {
            return ['code' => 102, 'msg' => '晒单不存在'];
        }
        $get = $request->get();
        $get['is_pass'] = 0;
        $result = ShareTopic::edit($shareId, $get);

        if (is_array($result)) {
            return $result;
        } elseif ($result) {
            return ['code' => 100];
        }

        return ['code' => 101];
    }

    public function actionUpload()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('imageFile');
            $uploadData = $model->uploadShareInfo();
            if ($uploadData) {
                if ($uploadData['error'] == 1) {
                    return ['code' => 101, 'msg' => $uploadData['message']];
                } else {
                    //$imagePath = Image::getShareInfoFullPath($uploadData['basename'], 'share');
                    //$imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
                   // $sourceSize = getimagesize($imagePath);
                   // $width = $sourceSize['0'];
                    //$height = $sourceSize['1'];
                    // file is uploaded successfully
                    //echo '<script type="text/javascript">window.parent.UploadFileScriptAPI("'.$uploadData['basename'].'",'.$width.','.$height.')</script>';
                    //echo Json::encode($uploadData);
                    return ['code' => 100, 'msg' => $uploadData['basename']];
                }
            }
        }
        return ['code' => 101, 'msg' => '参数错误'];
    }

    /**
     * 点赞
     * @return int
     * @throws NotFoundHttpException
     */
    public function actionHit()
    {
        $id = Yii::$app->request->get('id');

        if (!isset($id)) {
            return ['code' => 101, 'msg' => '失败'];
        }
        $data=['id'=>$id,'userid'=>$this->userId];
        if (ShareTopic::is_up($data, 1)) {
            return ['code' => 101, 'msg' => '失败'];
        } else {
            ShareTopic::addColumnByNum($id, 'up_num', 1);
            return ['code' => 100, 'msg' => '成功'];
        }
    }

    /**
     * 回复点赞
     */
    public function actionCommentHit()
    {
        $id = Yii::$app->request->get('id');

        if (!isset($id)) {
            return ['code' => 101, 'msg' => '失败'];
        }

        if (ShareComment::is_up($id, 1)) {
            return ['code' => 101, 'msg' => '失败'];
        } else {
            $shareComment = ShareComment::findOne($id);
            $shareComment->comment_up_num += 1;
            $shareComment->save();
            return ['code' => 100, 'msg' => '成功'];
        }
    }
}