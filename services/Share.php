<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午2:50
 */
namespace app\services;

use app\helpers\Brower;
use app\helpers\MyRedis;
use app\models\GroupTopicComment;
use app\models\Order;
use app\models\ProductCategory;
use app\models\ShareComment;
use app\models\ShareReply;
use app\models\ShareTopic;
use app\models\Image;
use app\models\ShareTopicImage;
use app\services\User;
use yii\data\Pagination;
use app\helpers\DateFormat;
use yii\helpers\ArrayHelper;
use app\models\Period;

class Share
{

    /**
     * 晒单信息
     * @param $id
     * @return array|null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function info($id)
    {
        if (is_array($id)) {
            $shareTopic = ShareTopic::find()->where(['id' => $id])->asArray()->all();

            foreach ($shareTopic as &$topic) {
                $shareTopic['created_at'] = $shareTopic['checked_at'] ? DateFormat::formatTime($shareTopic['checked_at']) : DateFormat::formatTime($shareTopic['created_at']);
            }

            $userIds = ArrayHelper::getColumn($shareTopic, 'user_id');
            $userInfos = User::baseInfo($userIds);

            foreach ($shareTopic as &$topic) {
                $topic['user_home_id'] = $userInfos[$topic['user_id']]['home_id'];
                $topic['user_name'] = $userInfos[$topic['user_id']]['username'];
                $topic['user_avatar'] = $userInfos[$topic['user_id']]['avatar'];
            }
        } else {
            $shareTopic = ShareTopic::find()->where(['id' => $id])->asArray()->one();

            if (!empty($shareTopic)) {
                $shareTopic['created_at'] = $shareTopic['checked_at'] ? DateFormat::formatTime($shareTopic['checked_at']) : DateFormat::formatTime($shareTopic['created_at']);

                $userInfo = User::baseInfo($shareTopic['user_id']);
                $shareTopic['user_home_id'] = $userInfo['home_id'];
                $shareTopic['user_name'] = $userInfo['username'];
                $shareTopic['user_avatar'] = $userInfo['avatar'];
            }
        }

        return $shareTopic;
    }

    /**
     * 某商品的晒单列表
     * @param $productId
     * @param $page
     * @param int $perpage
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($productId, $page, $perpage = 20)
    {
        $query = ShareTopic::find()->where(['product_id' => $productId,'is_pass'=>1]);
        $query->andWhere(['from'=>Brower::whereFrom()]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $shareTopicList = $query->orderBy('created_at DESC')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $periodId = $shareId = array();
        foreach ($shareTopicList as &$topicList) {
            $topicList['created_at'] = DateFormat::formatTime($topicList['created_at']);
            $periodId[] = $topicList['period_id'];
            $shareId[] = $topicList['id'];
        }
        //获取商品期数
        $_periodNums = Period::find()->select('id,period_number')->where(['in','id',$periodId])->asArray()->all();
        $periodNums = array();
        foreach ($_periodNums as $key => $value) {
            $periodNums[$value['id']] = $value['period_number'];
        }

        //获取晒单图片
        $_shareImgs = ShareTopicImage::find()->where(['in','share_topic_id',$shareId])->asArray()->all();
        $shareImgs = array();
        foreach ($_shareImgs as $key => $value) {
            $shareImgs[$value['share_topic_id']][] = $value['basename'];
        }

        $userIds = ArrayHelper::getColumn($shareTopicList, 'user_id');
        $userInfos = User::baseInfo($userIds);

        foreach ($shareTopicList as &$topicList) {
            $period = \app\services\Period::info($topicList['period_id']);
            $topicList['user_buy_num'] = $period['user_buy_num'];
            $topicList['user_home_id'] = $userInfos[$topicList['user_id']]['home_id'];
            $topicList['user_name'] = $userInfos[$topicList['user_id']]['username'];
            $topicList['user_avatar'] = $userInfos[$topicList['user_id']]['avatar'];
            $topicList['period_number'] = $periodNums[$topicList['period_id']];
            $topicList['pictures'] = isset($shareImgs[$topicList['id']])?$shareImgs[$topicList['id']]:'';
            $topicList['is_up'] = ShareTopic::is_up($topicList['id']);
        }
        $return['list'] = $shareTopicList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     * 晒单列表
     * @param $page
     * @param int $catId
     * @param int $t
     * @param int $perpage
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getListByType($page, $catId = 0, $t = 10, $perpage = 20, $userId = 0, $is_pass = 1)
    {
        $query = ShareTopic::find();

        $where = [];
        $is_pass != 0 && $query->andWhere(['is_pass' => $is_pass]);
        $userId == 0 && $query->andWhere(['is_show' => 1]);
        $order = 'checked_at DESC';

        if ($userId != 0) {
            $query->andWhere(['user_id' => $userId]);
        }

        if (!isset($t) || !in_array($t, array(10, 20, 30, 40, 50))) {
            $t = 10;
        }

        switch ($t) {
            case 10: //最新
                break;
            case 20: //精华
                $query->andWhere(['is_digest' => 1]);
                break;
            case 30: //推荐
                $query->andWhere(['is_recommend' => 1]);
                break;
            case 40: //人气
                $order = 'view_num DESC, checked_at DESC';
                break;
            case 50: //评论最多
                $order = 'comment_num DESC, checked_at DESC';
        }

        if ($catId != 0) {
            $productCategory = ProductCategory::find()->where(['top_id' => $catId])->orWhere(['id' => $catId])->all();
            $catIds = ArrayHelper::getColumn($productCategory, 'id');
            $query->andWhere(['cat_id' => $catIds]);
        }

        $query->andWhere(['from'=>Brower::whereFrom()]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();

        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $shareTopicList = $query->orderBy($order)
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        foreach ($shareTopicList as &$topic) {
            $topic['created_at'] = $topic['checked_at'] ? date("Y-m-d", $topic['checked_at']) : date("Y-m-d", $topic['created_at']);//DateFormat::formatTime($topic['created_at']);
            $topic['pictures'] = ShareTopicImage::getImagesByShareTopicId($topic['id']);
        }

        $userIds = ArrayHelper::getColumn($shareTopicList, 'user_id');
        $userInfos = User::baseInfo($userIds);

        foreach ($shareTopicList as &$topic) {
            $topic['user_home_id'] = $userInfos[$topic['user_id']]['home_id'];
            $topic['user_name'] = $userInfos[$topic['user_id']]['username'];
            $topic['user_avatar'] = $userInfos[$topic['user_id']]['avatar'];
            //if (!$topic['header_image']) {
//                $picture = ShareTopicImage::findOne(['share_topic_id' => $topic['id']]);
//                $picture = $picture['basename'];
//                $imagePath = Image::getShareInfoFullPath($picture, 'share');
//                $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
//                $topic['header_image'] = $picture;
            //} else {
                $imagePath = Image::getShareInfoFullPath($topic['header_image'], 'main');
                $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
            //}
            $sourceSize = @getimagesize($imagePath);
            $topic['width'] = $sourceSize['0'];
            $topic['height'] = $sourceSize['1'];
            $period = \app\services\Period::info($topic['period_id']);
            $topic['user_buy_num'] = $period['user_buy_num'];
        }

        $return['list'] = $shareTopicList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     * 晒单评论
     * @param $id
     * @param $page
     * @param int $perpage
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function commentList($id, $page, $perpage = 20, $userId = 0)
    {
        $query = ShareComment::find()->where(['share_topic_id' => $id]);

        $redis = new MyRedis();
        $key = ShareComment::SWITCH_SHARE;
        $status = $redis->get($key);
        if ($status) {
            if ($userId) {
                $query->andWhere(['or', 'user_id='.$userId, 'status=1']);
            } else {
                $query->andWhere(['status' => 1]);
            }
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $shareCommentList = $query->orderBy('created_at DESC')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        foreach ($shareCommentList as &$comment) {
            $comment['created_at'] = DateFormat::formatTime($comment['created_at']);
            $comment['reply_num'] = ShareReply::find()->where(['share_comment_id' => $comment['id']])->count();
            $comment['content'] = GroupTopicComment::commentDeal($comment['content']);
        }

        $userIds = ArrayHelper::getColumn($shareCommentList, 'user_id');
        $userInfos = User::baseInfo($userIds);

        foreach ($shareCommentList as &$comment) {
            $comment['user_home_id'] = $userInfos[$comment['user_id']]['home_id'];
            $comment['user_name'] = $userInfos[$comment['user_id']]['username'];
            $comment['user_avatar'] = $userInfos[$comment['user_id']]['avatar'];
        }

        $return['list'] = $shareCommentList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     * 评论的回复列表
     * @param $id
     * @param $page
     * @param int $perpage
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function replyList($id, $page, $perpage = 20, $userId = 0)
    {
        $query = ShareReply::find()->where(['share_comment_id' => $id]);

        $redis = new MyRedis();
        $key = ShareComment::SWITCH_SHARE;
        $status = $redis->get($key);
        if ($status) {
            $query->andWhere(['status' => 1]);
            if ($userId) {
                $query->orWhere(['user_id' => $userId]);
            }
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $shareReplyList = $query->orderBy('floor DESC')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        foreach ($shareReplyList as &$reply) {
            $reply['created_at'] = DateFormat::formatTime($reply['created_at']);
            $reply['content'] = GroupTopicComment::commentDeal($reply['content']);
        }

        $userIds = ArrayHelper::getColumn($shareReplyList, 'user_id');
        $userInfos = User::baseInfo($userIds);

        foreach ($shareReplyList as &$reply) {
            $reply['user_home_id'] = $userInfos[$reply['user_id']]['home_id'];
            $reply['user_name'] = $userInfos[$reply['user_id']]['username'];
            $reply['user_avatar'] = $userInfos[$reply['user_id']]['avatar'];
        }

        $return['list'] = $shareReplyList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /**
     * 根据商品id获取成功的订单
     * @param int $product_id
     * @param int $page
     * @param int $perpage
     * @return mixed
     */
    public static function getOrderByProductId($product_id = 0, $page = 1, $perpage = 20, $exceptUserId = 0)
    {
        $query = Order::find()->leftJoin('share_topics s', 'orders.period_id=s.period_id')->select('orders.*, s.id as share_topic_id, s.is_pass')->where(['orders.product_id' => $product_id]);
        $query->andWhere(['!=', 'orders.user_id', $exceptUserId]);
        $query->andWhere(['s.from' => Brower::whereFrom()]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $orders = $query->orderBy('s.is_pass DESC, orders.create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $orderList = array();
        foreach ($orders as $order) {
            $periodInfo = \app\services\Period::info($order['period_id']);
            $userInfo = User::baseInfo($order['user_id']);
            $orderInfo['status'] = $order['status'];
            $orderInfo['share_topic_id'] = intval($order['share_topic_id']);
            $orderInfo['is_pass'] = intval($order['is_pass']);
            $orderInfo['user_id'] = $order['user_id'];
            $orderInfo['user_home_id'] = $userInfo['home_id'];
            $orderInfo['user_name'] = $userInfo['username'];
            $orderInfo['user_avatar'] = $userInfo['avatar'];
            $orderInfo['period_number'] = $periodInfo['period_number'];

            $orderList[] = $orderInfo;
        }

        $return['list'] = $orderList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }
}