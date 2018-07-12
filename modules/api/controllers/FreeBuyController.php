<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/3/8
 * Time: 上午9:32
 */
namespace app\modules\api\controllers;

use app\helpers\MyRedis;
use app\models\FreePeriodBuylistDistribution;
use app\models\UserAddress;
use app\services\FreeBuy;

class FreeBuyController extends BaseController
{

    /**
     *  零元购商品列表
     */
    public function actionProductList()
    {
        $request = \Yii::$app->request;
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 20);

        $result = FreeBuy::productList($page, $perpage);

        if ($this->userId) {
            $result['phone'] = $this->userInfo['phone'] ? 1 : 0;
            $address = UserAddress::find()->select('id')->where(['uid'=>$this->userId])->one();
            $result['address'] = $address ? 1 : 0;
        } else {
            $result['phone'] = 0;
            $result['address'] = 0;
        }
        return $result;
    }

    /**
     *  零元购商品详情
     */
    public function actionProductInfo()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $info = FreeBuy::productInfo($id);
        $startTime = date('Y年m月d日H点', $info['start_time']);
        $endTime = date('Y年m月d日H点', $info['end_time']);
        $info['desc'] = $this->appDirUrl. 'free_desc.html?s='.urlencode($startTime).'&e='.urlencode($endTime);
        if ($this->userId) {
            $userBuyNum = FreePeriodBuylistDistribution::findByTableId($info['table_id'])
                ->where(['period_id' => $info['period_id'], 'user_id' => $this->userId, 'pay_type' => FreeBuy::PAY_TYPE_GIVE])
                ->count();
        } else {
            $userBuyNum = -1;
        }
        $info['is_buy'] = $userBuyNum > 0 ? 1 : 0;
        return $info;
    }

    /**
     *  零元购往期揭晓列表
     */
    public function actionPeriodList()
    {
        $request = \Yii::$app->request;
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 20);
        $result = FreeBuy::periodList($page, $perpage);
        return $result;
    }

    /**
     *  零元购往期揭晓详情
     */
    public function actionPeriodInfo()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $info = FreeBuy::periodInfo($id);
        if (!$info) {
            return [];
        }
        $startTime = date('Y年m月d日H点', $info['start_time']);
        $endTime = date('Y年m月d日H点', $info['end_time']);
        $info['desc'] = $this->appDirUrl. 'free_desc.html?s='.urlencode($startTime).'&e='.urlencode($endTime);
        if ($this->userId) {
            $userBuyNum = FreePeriodBuylistDistribution::findByTableId($info['table_id'])->where(['period_id' => $id, 'user_id' => $this->userId, 'pay_type' => FreeBuy::PAY_TYPE_GIVE])->count();
        } else {
            $userBuyNum = -1;
        }
        $info['is_buy'] = $userBuyNum > 0 ? 1 : 0;
        return $info;
    }

    /** 商品相册
     * @return array
     */
    public function actionProductImages()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $images = FreeBuy::productImages($id);
        $return = [];
        $return['list'] = $images;
        return $return;
    }

    /** 商品详情
     * @return array
     */
    public function actionProductIntro()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $intro = FreeBuy::productIntro($id);
        $return = [];
        $return['intro'] = $intro;
        return $return;
    }

    /**
     *  零元购用户某期参与记录
     */
    public function actionUserBuylistByPeriod()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $result = FreeBuy::userBuyListByPeriodId($this->userId, $id);
        $result['end_time'] = date('Y年m月d日H点',$result['end_time']);
        $result['invite_num'] = FreeBuy::inviteNum($this->userId, $id);
        return $result;
    }

    /**
     *  零元购用户购码
     */
    public function actionBuy()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $userId = $this->userId;
        $request = \Yii::$app->request;
        $periodId = $request->get('id');
        $source = $request->get('source');
        $payType = $request->get('payType',1);
        $payBank = $request->get('payBank',0);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        if ($payType == FreeBuy::PAY_TYPE_SHARE_REG) {
            $return =  ['code' => 202, 'msg' => '购买失败'];
            return $return;
        }
        $redis = new MyRedis();
        $userBuyingKey = FreeBuy::USER_PRODUCT_BUYING_KEY . $periodId . '_' . $userId;
        $userBuying = $redis->incr($userBuyingKey);
        $redis->expire($userBuyingKey, 60);
        if ($userBuying>1) {
            $return = ['code' => 205, 'msg' => '正在购买'];
            return $return;
        }
        $buy = FreeBuy::buy($userId, $periodId, $payType, $payBank, (int)$source);
        $redis->del($userBuyingKey);
        return $buy;
    }


    /** 零元购购买结果
     * @return array|mixed|string
     */
    public function actionBuyResult()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $periodId = $request->get('id');
        $result = FreeBuy::buyResult($this->userId, $periodId);
        return $result;
    }


    /** 零元购用户某期邀请列表
     * @return array
     */
    public function actionInviteList()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $periodId = $request->get('pid',0);
        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', 20);

        $result = FreeBuy::inviteList($this->userId, $periodId, $page, $perpage);
        return $result;
    }


}