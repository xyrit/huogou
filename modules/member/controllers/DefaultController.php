<?php

namespace app\modules\member\controllers;
use app\helpers\TimeHelper;
use app\models\CurrentPeriod;
use app\models\ShareTopic;
use app\models\UserVirtualHand;
use app\models\VirtualProductInfo;
use app\modules\admin\models\ConfirmFailReason;
use app\modules\admin\models\Deliver;
use app\modules\admin\models\DeliverCompany;
use app\modules\admin\models\ExchangeOrder;
use app\modules\member\controllers\BaseController;

use app\helpers\DateFormat;
use app\models\Image;
use app\models\Order;
use app\services\Member;
use app\services\Period;
use app\services\Product;
use app\services\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\services\Member_m;
use app\models\InviteLink;
use app\models\UserVirtual;
use yii\web\NotFoundHttpException;


class DefaultController extends BaseController
{
    /**
     * 用户中心
     */
    public function actionIndex()
    {
        $uid = Yii::$app->user->id;
        $data['createCode'] = InviteLink::getInviteLink($uid);//邀请专属链接
        $data['groups'] = Member_m::getGroup();// 所属专区
        $data['topics'] = Member_m::getNotic();//公告栏
        $data['users'] = Member_m::getUsers();//可能感兴趣的人
        $data['orderList'] = Member_m::userOrderList($uid);//用户中奖订单
        $data['friendBuyList'] = Member_m::friendBuyList(8);
        foreach ($data['friendBuyList'] as &$buy) {
            $buy['user_avatar'] = Image::getUserFaceUrl($buy['user_avatar'], 160);
        }

        $member = new Member(['id' => $uid]);
        $data['buyList'] = $member->getBuyList('', '', 1, 10);
        foreach ($data['buyList']['list'] as &$buy) {
            $buy['goods_picture_url'] = Image::getProductUrl($buy['goods_picture'], 200, 200);
        }
        $data['collectList'] = $member->getFollowProductList(1, 4);

        foreach ($data['collectList']['list'] as &$collect) {
            $collect['goods_picture_url'] = Image::getProductUrl($collect['goods_picture'], 200, 200);
        }
        $data['shareList'] = ShareTopic::getListByType(10, 0, 0, 8, 1);
        foreach ($data['shareList']['list'] as &$share) {
            $share['created_at'] = DateFormat::formatTime($share['created_at']);
            $share['header_image_url'] = Image::getShareInfoUrl($share['roll_image'], 'roll');
            $user = User::baseInfo($share['user_id']);
            $share['user_home_id'] = $user['home_id'];
            $share['user_name'] = $user['username'];
            $share['user_avatar'] = Image::getUserFaceUrl($user['avatar'], 160);
        }

        $data['rightTopic'] = $member->getTopic();//圈子热门话题

        return $this->render('index', $data);
    }

    /**
     * 我的伙购记录
     */
    public function actionBuyList()
    {
        return $this->render('buylist');
    }

    /**
     * 伙购详情
     */
    public function actionBuyDetail()
    {
        $userId = Yii::$app->user->id;
        $periodId = Yii::$app->request->get('id');
        $member = new Member(['id' => $userId]);

        // 购买信息
        $buyDetail = $member->getBuyDetail($periodId);
        $buyNumber = 0;
        foreach ($buyDetail as &$detail) {
            $detail['buy_time'] = DateFormat::microDate($detail['buy_time']);
            $detail['codes'] = explode(',', $detail['codes']);
            $buyNumber += $detail['buy_num'];
        }

        $periodInfo = Period::info($periodId);
        if (empty($periodInfo)) {
            $periodInfo = CurrentPeriod::find()->where(['id' => $periodId])->one();
            $periodInfo = ArrayHelper::toArray($periodInfo);
            $productInfo = Product::info($periodInfo['product_id']);
            $periodInfo['goods_picture'] = $productInfo['picture'];
            $periodInfo['goods_id'] = $productInfo['id'];
            $periodInfo['goods_name'] = $productInfo['name'];
            $periodInfo['period_id'] = $periodInfo['id'];
        }
        $periodInfo['user_buy_num'] = $buyNumber;
        $periodInfo['product_picture_url'] = Image::getProductUrl($periodInfo['goods_picture'], '200', '200');

        $orderInfo = Order::findOne(['product_id' => $periodInfo['goods_id'], 'period_id' => $periodInfo['period_id'], 'user_id' => $userId]);
        if (!empty($orderInfo) && $orderInfo['status'] == 0) {
            $data['orderInfo'] = $orderInfo;
        }

        $data['periodInfo'] = $periodInfo;
        $data['buyDetail'] = $buyDetail;

        return $this->render('buydetail', $data);
    }

    /**
     * 获得的商品
     */
    public function actionOrderList()
    {
        $userId = Yii::$app->user->id;
        $totalProduct = Order::find()->select('orders.*')->leftJoin('periods as p', 'orders.period_id=p.id')->where(['orders.user_id' => $userId])->andWhere(['<=','p.result_time', time()])->all();
        $tips = [0, 0, 0, 0];
        foreach ($totalProduct as $product) {
            if ($product['status'] == Order::STATUS_INIT || $product['status'] == Order::STATUS_REJECT) {
                $tips[0]++;
            } elseif ($product['status'] >=Order::STATUS_COMMIT_ADDRESS && $product['status'] <= Order::STATUS_PREPARE_GOODS) {
                $tips[1]++;
            } elseif ($product['status'] == Order::STATUS_SHIPPING) {
                $tips[2]++;
            } elseif ($product['status'] == Order::STATUS_COMFIRM_RECEIVE) {
                $productInfo = \app\models\Product::findOne(['id' => $product['product_id']]);
                if ($productInfo && $productInfo['allow_share'] == 1) {
                    $tips[3]++;
                }
            }
        }
        $totalExchangeProduct = ExchangeOrder::find()->where(['user_id' => $userId])->count();
        return $this->render('orderlist', ['totalProduct' => Count($totalProduct), 'totalExchangeProduct' => $totalExchangeProduct, 'tips' => $tips]);
    }

    /**
     * 换货的商品
     */
    public function actionExchangeOrderList()
    {
        $userId = Yii::$app->user->id;
        $totalProduct = Order::find()->where(['user_id' => $userId])->count();

        $totalExchangeProduct = Order::find()->select('orders.*')->leftJoin('periods as p', 'orders.period_id=p.id')->where(['orders.user_id' => $userId, 'orders.is_exchange' => 1])->andWhere(['<=','p.result_time', time()])->all();
        $tips = [0, 0, 0, 0];
        foreach ($totalExchangeProduct as $product) {
            if ($product['status'] == Order::STATUS_INIT || $product['status'] == Order::STATUS_REJECT) {
                $tips[0]++;
            } elseif ($product['status'] >=Order::STATUS_COMMIT_ADDRESS && $product['status'] <= Order::STATUS_PREPARE_GOODS) {
                $tips[1]++;
            } elseif ($product['status'] == Order::STATUS_SHIPPING) {
                $tips[2]++;
            } elseif ($product['status'] == Order::STATUS_COMFIRM_RECEIVE) {
                $productInfo = \app\models\Product::findOne(['id' => $product['product_id']]);
                if ($productInfo && $productInfo['allow_share'] == 1) {
                    $tips[3]++;
                }
            }
        }

        return $this->render('exchangeorderlist', ['totalProduct' => $totalProduct, 'totalExchangeProduct' => COUNT($totalExchangeProduct), 'tips' => $tips]);
    }


    /**
     * 订单详情
     */
    public function actionOrderDetail()
    {
        $orderId = Yii::$app->request->get('id');
        $userId = Yii::$app->user->id;
        $orderInfo = Order::findOne(['id' => $orderId, 'user_id' => $userId]);
        if (empty($orderInfo)) {
            throw new NotFoundHttpException("页面未找到");
        }
        $orderInfo = ArrayHelper::toArray($orderInfo);

        $periodInfo = Period::info($orderInfo['period_id']);
        $periodInfo['goods_picture_url'] = Image::getProductUrl($periodInfo['goods_picture'], '200', '200');

        $productInfo = Product::info($orderInfo['product_id']);
        $orderInfo['allow_share'] = $productInfo['allow_share'];

        if ($orderInfo['status'] == 0 || $orderInfo['status'] == 6) {
            if ($orderInfo['status'] == 6) { // 驳回原因
                if (!empty($orderInfo['fail_id'])) {
                    $orderInfo['fail_info'] = $orderInfo['fail_id'];
                }
            }
            $data['periodInfo'] = $periodInfo;
            $data['orderId'] = $orderId;
            $data['orderInfo'] = $orderInfo;
            $userId = Yii::$app->user->id;
            $member = new Member(['id' => $userId]);

            if (in_array($productInfo['delivery_id'], [5, 6, 7,9,10])) { //虚拟物品
                //$data['addressList'] = $member->getVirtualAddressList(1, 10);
                $data['delivery_id'] = $productInfo['delivery_id'];
//                return $this->render('orderdetailvirtual', $data);
                return $this->render('orderdetail_go_app', $data);
            }

            if ($productInfo['delivery_id'] == 3) { //虚拟物品
                return $this->render('neworderdetailvirtual', $data);
            }

            if ($productInfo['delivery_id'] == 8) { //京东充值卡
                return $this->render('orderdetailvirtualjd', $data);
            }

            $addressList = $member->getAddressList(1, 10);
            $data['addressList'] = $addressList;
            return $this->render('orderdetail', $data);
        } else {

            if ($productInfo['delivery_id'] == 2) { //虚拟物品
                $data['addressInfo'] = VirtualProductInfo::findOne(['order_id' => $orderId]);
            }

            if ($productInfo['delivery_id'] == 3) { //虚拟物品 自动发货
                $data['orderInfo'] = $orderInfo;
                $data['periodInfo'] = $periodInfo;
                $data['orderId'] = $orderId;
                $data['list'] = UserVirtual::find()->where(['orderid'=>$orderId,'uid'=>Yii::$app->user->id])->asArray()->all();
                return $this->render('neworderdetailvirtual', $data);
            }

            if (in_array($productInfo['delivery_id'], [5, 6, 7,9,10])) { //虚拟物品
                //$data['addressList'] = $member->getVirtualAddressList(1, 10);
                $data['orderInfo'] = $orderInfo;
                $data['periodInfo'] = $periodInfo;
                $data['orderId'] = $orderId;
                $data['delivery_id'] = $productInfo['delivery_id'];
                $userVirtualHand = UserVirtualHand::findOne(['order_id' => $orderId]);
                $data['virtual_hand'] = $userVirtualHand;
//                return $this->render('orderdetailvirtual', $data);
                return $this->render('orderdetail_go_app', $data);
            }

            if ($productInfo['delivery_id'] == 8) { //京东充值卡
                $data['orderInfo'] = $orderInfo;
                $data['periodInfo'] = $periodInfo;
                $data['orderId'] = $orderId;
                $data['list'] = UserVirtual::find()->where(['orderid'=>$orderId,'uid'=>Yii::$app->user->id])->asArray()->all();
//                return $this->render('orderdetailvirtualjd', $data);
                return $this->render('orderdetail_go_app', $data);
            }

            $orderInfo['detail'] = array();
            // 订单详细流程
            $on = Yii::$app->request->get('on');
            $person = Deliver::getEmployeeName();
            if($on != 'exchange'){
                if ($orderInfo['status'] >= 0) {
                    $detail['time'] = DateFormat::microDate($orderInfo['create_time']);
                    $detail['content'] = "恭喜您伙购成功，请尽快填写收货地址，以便我们为您配送！";
                    $detail['name'] = "伙购系统";
                    $orderInfo['detail'][] = $detail;
                }

                if ($orderInfo['status'] >= 1) {
                    $detail['time'] = DateFormat::microDate($orderInfo['confirm_addr_time']);
                    $detail['content'] = "会员已填写配送地址信息，等待商城发货。";
                    $detail['name'] = "会员本人";
                    $orderInfo['detail'][] = $detail;
                }

                if ($orderInfo['status'] >= 2) {
                    $deliverInfo = Deliver::findOne($orderId);
                    $detail['time'] = DateFormat::microDate($deliverInfo['confirm_time']);
                    $detail['content'] = "您的订单信息已确认。";
                    $detail['name'] = mb_substr($person[$deliverInfo['confirm_userid']], 0, 1).'**';
                    $orderInfo['detail'][] = $detail;
                }
            }

            if($orderInfo['is_exchange'] == 0){
                if ($orderInfo['status'] >= 3 && $productInfo['delivery_id'] != 2) {
                    $detail['time'] = DateFormat::microDate($deliverInfo['prepare_time']);
                    $detail['content'] = "您的订单已打包完毕。";
                    $detail['name'] = mb_substr($person[$deliverInfo['prepare_userid']], 0, 1).'**';
                    $orderInfo['detail'][] = $detail;
                }

                if ($orderInfo['status'] >= 4 && $productInfo['delivery_id'] != 2) {
                    $detail['time'] = DateFormat::microDate($deliverInfo['deliver_time']);
                    $detail['content'] = "您的订单已出库，将由 【{$deliverInfo['deliver_company']}】配送，快递单号【{$deliverInfo['deliver_order']}】。";
                    $detail['name'] = mb_substr($person[$deliverInfo['deliver_userid']], 0 ,1).'**';
                    $detail['deliver_company'] = $deliverInfo['deliver_company'];
                    $detail['deliver_order'] = $deliverInfo['deliver_order'];
                    $orderInfo['detail'][] = $detail;
                }

                if ($orderInfo['status'] >= 5) {
                    $detail['time'] = DateFormat::microDate($orderInfo['confirm_goods_time']);
                    $detail['content'] = "买家提交确认收货成功。";
                    $detail['name'] = "会员本人";
                    $orderInfo['detail'][] = $detail;
                }

                if (isset($deliverInfo) && $deliverInfo['deliver_company'] && $deliverInfo['deliver_order']) {
                    $orderInfo['deliver_company'] = $deliverInfo['deliver_company'];
                    $orderInfo['deliver_order'] = $deliverInfo['deliver_order'];
                }
            }else{
                $exchange = ExchangeOrder::findOne(['order_no'=>$orderInfo['id']]);
                if($on == 'exchange'){
                    $detail['time'] = DateFormat::microDate($exchange['created_time']);
                    $detail['content'] = "换货申请成功，新订单号[".$exchange['id']."]。";
                    $detail['name'] = mb_substr($person[$exchange['admin_id']], 0, 1).'**';
                    $orderInfo['detail'][] = $detail;

                    if ($orderInfo['status'] >= 4 && $productInfo['delivery_id'] != 2) {
                        $detail['time'] = DateFormat::microDate($exchange['deliver_time']);
                        $detail['content'] = "您的订单已出库，将由 【{$exchange['deliver_company']}】配送，快递单号【{$exchange['deliver_order']}】。";
                        $detail['name'] = mb_substr($person[$exchange['deliver_userid']], 0, 1).'**';
                        $orderInfo['detail'][] = $detail;
                    }

                    if ($orderInfo['status'] >= 5) {
                        $detail['time'] = DateFormat::microDate($exchange['confirm_goods_time']);
                        $detail['content'] = "买家提交确认收货成功。";
                        $detail['name'] = "会员本人";
                        $orderInfo['detail'][] = $detail;
                    }
                    if (isset($exchange) && $exchange['deliver_company'] && $exchange['deliver_order']) {
                        $orderInfo['deliver_company'] = $exchange['deliver_company'];
                        $orderInfo['deliver_order'] = $exchange['deliver_order'];
                    }
                }else{
                    $detail['time'] = DateFormat::microDate($deliverInfo['prepare_time']);
                    $detail['content'] = "您的订单已打包完毕。";
                    $detail['name'] = mb_substr($person[$deliverInfo['prepare_userid']], 0 ,1).'**';
                    $orderInfo['detail'][] = $detail;

                    $detail['time'] = DateFormat::microDate($deliverInfo['deliver_time']);
                    $detail['content'] = "您的订单已出库，将由 【{$deliverInfo['deliver_company']}】配送，快递单号【{$deliverInfo['deliver_order']}】。";
                    $detail['name'] = mb_substr($person[$deliverInfo['deliver_userid']], 0, 1).'**';
                    $orderInfo['detail'][] = $detail;

                    $detail['time'] = DateFormat::microDate($exchange['created_time']);
                    $detail['content'] = "您的换货申请已通过，原订单号[".$exchange['order_no']."]，新订单号[".$exchange['id']."]，等待商城发货。";
                    $detail['name'] = mb_substr($person[$exchange['admin_id']],0 ,1).'**';
                    $orderInfo['detail'][] = $detail;
                    $orderInfo['exchange'] = $exchange;
                    if (isset($deliverInfo) && $deliverInfo['deliver_company'] && $deliverInfo['deliver_order']) {
                        $orderInfo['deliver_company'] = $deliverInfo['deliver_company'];
                        $orderInfo['deliver_order'] = $deliverInfo['deliver_order'];
                    }
                }
            }

            /*$data['express'] = [];
            if (isset($orderInfo['deliver_company']) && isset($orderInfo['deliver_order'])) {
                $data['express'] = Express::getOrder($deliverInfo['deliver_company'], $orderInfo['deliver_order']);
            }*/

            $data['periodInfo'] = $periodInfo;
            $data['orderId'] = $orderId;
            $data['orderInfo'] = $orderInfo;
            $data['exchange'] = isset($exchange) ? $exchange : '';
            $data['ex_view'] = isset($on) ? $on : '';

            if(isset($exchange) && $on != 'exchange'){
                return $this->render('orderdetailshare', $data);
            }else{
                if ($orderInfo['status'] >= 1 && $orderInfo['status'] <=3) {
                    return $this->render('orderdetailship', $data);
                } elseif ($orderInfo['status'] == 4) {
                    return $this->render('orderdetailsubmit', $data);
                } else {
                    if ($orderInfo['status'] == 8) {
                        $data['shareInfo'] = ShareTopic::findOne(['period_id' => $orderInfo['period_id']]);
                    }
                    return $this->render('orderdetailshare', $data);
                }
            }
        }
    }

    /**
     * 我的晒单
     */
    public function actionTopicList()
    {
        $t = Yii::$app->request->get('t');
        $userId = Yii::$app->user->id;
        $shareTopic['totalTopic'] = ShareTopic::find()->where(['user_id' => $userId])->count();

        $notShareOrderCount = \app\models\Order::find()
            ->leftJoin('products p', 'p.id = orders.product_id')
            ->where(['orders.user_id' => $userId, 'orders.status' => 5, 'p.allow_share' => 1])->count();

        $shareTopic['notShareOrderCount'] = $notShareOrderCount;
        if ($t == 1) {
            return $this->render('nottopiclist', $shareTopic);
        }
        return $this->render('topiclist', $shareTopic);
    }

    /**
     * 活动订单
     */
    public function actionActiveList()
    {
        return $this->render('activelist');
    }

    /**
     * 我的关注
     */
    public function actionCollectList()
    {
        return $this->render('collectlist');
    }

}