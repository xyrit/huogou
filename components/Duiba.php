<?php
namespace app\components;

use app\helpers\MyRedis;
use app\models\DuibaOrderDistribution;
use app\models\Order;
use app\models\Product;
use app\models\User;
use app\modules\admin\models\Deliver;

/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/4/20
 * Time: 18:22
 */
class Duiba extends \yii\base\Component
{

    public $appKey;
    public $appSecret;

    const TYPE_TEST = 0;
    const TYPE_ALIPAY = 1; //支付宝充值
    const TYPE_QB = 2; //Q币充值
    const TYPE_PHONE = 3; //话费充值


    public static $redirectUrls = [
        0 => 'http://www.duiba.com.cn/mobile/detail?itemId=191',
        1 => 'http://www.duiba.com.cn/mobile/detail?itemId=53',
        2 => 'http://www.duiba.com.cn/mobile/detail?itemId=2',
        3 => 'http://www.duiba.com.cn/mobile/detail?itemId=1',
    ];

    public static $typesName = [
        0 => 'coupon',
        1 => 'alipay',
        2 => 'qb',
        3 => 'phonebill',
    ];

    /** 跳转到支付宝充值
     * @param $alipay
     * @param $realName
     */
    public function redirectAlipay($userId, $no, $money, $alipay = '', $realName = '')
    {
        $uid = $userId . '_' . $no;
        $credits = $money * 100;
        if ($alipay && $realName) {
            $otherParams = [
                'alipay' => $alipay,
                'realname' => $realName,
            ];
        } else {
            $otherParams = [];
        }
        $url = $this->autologinAndRedirect(static::TYPE_ALIPAY, $uid, $credits, $otherParams);
        return $url;
    }

    /** 跳转到Q币充值
     * @param $qq
     */
    public function redirectQB($userId, $no, $money, $qq = '')
    {
        $uid = $userId . '_' . $no;
        $credits = $money * 100;
        if ($qq) {
            $otherParams = [
                'qq' => $qq,
            ];
        } else {
            $otherParams = [];
        }
        $url = $this->autologinAndRedirect(static::TYPE_QB, $uid, $credits, $otherParams);
        return $url;
    }

    /** 跳转到话费充值
     * @param $userId
     * @param $no
     * @param $money
     */
    public function redirectPhonebill($userId, $no, $money, $phone = '')
    {
        $uid = $userId . '_' . $no;
        $credits = $money * 100;
        if ($phone) {
            $otherParams = [
                'phone' => $phone,
            ];
        } else {
            $otherParams = [];
        }
        $url = $this->autologinAndRedirect(static::TYPE_PHONE, $uid, $credits, $otherParams);
        return $url;
    }

    /** 跳转到测试优惠券
     * @param $userId
     * @param $no
     * @param $money
     * @param $qq
     * @return string
     */
    public function redirectTest($userId, $no, $money)
    {
        $uid = $userId . '_' . $no;
        $credits = $money * 100;
        $otherParams = [];
        $url = $this->autologinAndRedirect(static::TYPE_TEST, $uid, $credits, $otherParams);
        return $url;
    }


    /**
     * 抵扣通知
     */
    public function consumeNotify()
    {
        $request = \Yii::$app->request;

        $uid = $request->get('uid');
        $credits = $request->get('credits');
        $appKey = $request->get('appKey');
        $timestamp = $request->get('timestamp');
        $description = $request->get('description');
        $orderNum = $request->get('orderNum');
        $type = $request->get('type');
        $facePrice = $request->get('facePrice');
        $actualPrice = $request->get('actualPrice');
        $ip = $request->get('ip');
        $waitAudit = $request->get('waitAudit');
        $params = $request->get('params');
        $sign = $request->get('sign');
        if (!$sign || !$this->signVerify($this->appSecret, $request->getQueryParams())) {
            return [
                'status' => 'fail',
                'errorMessage' => '签名验证错误',
                'credits' => 0,
            ];
        }

        if (!in_array($type, static::$typesName)) {
            return [
                'status' => 'fail',
                'errorMessage' => '错误的充值类型',
                'credits' => 0,
            ];
        }

        $uidArr = explode('_', $uid);
        $userId = $uidArr[0];
        $no = $uidArr[1];

        $userInfo = User::find()->select('home_id')->where(['id'=>$userId])->one();
        if (!$userInfo) {
            return [
                'status' => 'fail',
                'errorMessage' => '中奖订单信息有误',
                'credits' => 0,
            ];
        }
        $homeId = $userInfo['home_id'];

        $order = Order::findOne(['id' => $no, 'user_id' => $userId, 'status' => [0,6]]);
        if (!$order) {
            return [
                'status' => 'fail',
                'errorMessage' => '中奖订单信息有误',
                'credits' => 0,
            ];
        }
        $product = Product::find()->select('face_value,delivery_id')->where(['id' => $order['product_id']])->asArray()->one();

        if (in_array($order['status'], [0,6])) {
            if (!$credits || $credits / 100 != $product['face_value']) {
                return [
                    'status' => 'fail',
                    'errorMessage' => '兑换面额不正确',
                    'credits' => 0,
                ];
            }
        } else {
            $sumCredits = DuibaOrderDistribution::findByTableId($homeId)->select('sum(credits) as sum_credits')->where(['order_no'=>$no,'status'=>1])->asArray()->one();
            $sumCredits = isset($sumCredits['sum_credits']) ? $sumCredits['sum_credits'] : 0;

            //多张兑换卡总和不能大于商品的面值
            if (!$credits || ($sumCredits + $credits) / 100 > $product['face_value']) {
                return [
                    'status' => 'fail',
                    'errorMessage' => '兑换面额不正确',
                    'credits' => 0,
                ];
            }
        }

        $model = new DuibaOrderDistribution($homeId);
        $model->id = DuibaOrderDistribution::generateOrderId($homeId);
        $model->user_id = $userId;
        $model->order_no = $no;
        $model->credits = $credits;
        $model->appKey = $appKey;
        $model->description = $description;
        $model->order_num = $orderNum;
        $model->timestamp = $timestamp;
        $model->type = $type;
        $model->face_price = $facePrice;
        $model->actual_price = $actualPrice;
        $model->ip = $ip;
        $model->wait_audit = $waitAudit == 'true' ? 1 : 0;
        $model->audit_status = 0;
        $model->sign = $sign;
        $model->params = $params;
        $model->error_msg = '';
        $model->status = 0;
        $model->created_at = time();
        $model->updated_at = time();
        $save = $model->save();
        if ($save) {

            //多张都兑换提交后,改变中奖订单状态为待发货
            $sumCredits = DuibaOrderDistribution::findByTableId($homeId)->select('sum(credits) as sum_credits')->where(['order_no'=>$no,'status'=>[0,1]])->asArray()->one();
            $sumCredits = $sumCredits['sum_credits'];
            if ($sumCredits / 100 == $product['face_value']) {
                $order->status = 3;
                $order->last_modified = time();
                $order->save(false);
            }

            return [
                'status' => 'ok',
                'errorMessage' => '',
                'bizId' => $model->id,
                'credits' => '0',
            ];
        } else {
            return [
                'status' => 'fail',
                'errorMessage' => '订单兑换出错'.print_r($model->getFirstErrors(),true),
                'bizId' => '',
                'credits' => '0',
            ];
        }

    }


    /**
     *  结果通知
     */
    public function resultNotify()
    {
        $request = \Yii::$app->request;
        $appKey = $request->get('appKey');
        $timestamp = $request->get('timestamp');
        $success = $request->get('success');
        $errorMessage = $request->get('errorMessage');
        $orderNum = $request->get('orderNum');
        $bizId = $request->get('bizId');
        $sign = $request->get('sign');

        if (!$sign || !$this->signVerify($this->appSecret, $request->getQueryParams())) {
            return [];
        }

        $tableId = substr($bizId, 0, 3);
        $success = $success=='true' ? 1 : 0;
        $duibaOrder = DuibaOrderDistribution::findByTableId($tableId)->where(['id' => $bizId, 'order_num' => $orderNum])->one();
        if ($duibaOrder) {
            $duibaOrder->status = $success ? 1 : 2;
            $duibaOrder->error_msg = $errorMessage;
            $duibaOrder->updated_at = time();
            $duibaOrder->save(false);

            $orderId = $duibaOrder->order_no;

            if ($success) {
                $sumCredits = DuibaOrderDistribution::findByTableId($tableId)->select('sum(credits) as sum_credits')->where(['order_no'=>$orderId,'status'=>1])->asArray()->one();
                $sumCredits = $sumCredits['sum_credits'];
                $order = Order::find()->select('product_id')->where(['id'=>$orderId])->asArray()->one();
                $product = Product::find()->select('face_value')->where(['id'=>$order['product_id']])->asArray()->one();
                if ($sumCredits/100 == $product['face_value']) {
                    $orderUpdate = Order::updateAll(['status' => 8, 'last_modified'=>time()], ['id' => $duibaOrder['order_no']]);
                }
            } else {
                $order = Order::find()->select('product_id')->where(['id'=>$orderId])->asArray()->one();
                if ($order) {
                    $deliver = Deliver::find()->where( ['id' => $duibaOrder['order_no']])->one();
                    if (!$deliver) {
                        $orderUpdate = Order::updateAll(['status' => 6, 'last_modified'=>time()], ['id' => $duibaOrder['order_no'], 'status'=>3]);
                    }
                }
            }
        }
        echo 'ok';
    }


    /**
     *  自动登录跳转参数
     */
    public function autologinAndRedirect($type, $uid, $credits, $otherParams)
    {
        if (!isset(static::$redirectUrls[$type])) {
            return '';
        }
        $redirect = static::$redirectUrls[$type];
        $url = "http://www.duiba.com.cn/autoLogin/autologin?";
        $timestamp = time() * 1000 . "";
        $array = array("uid" => $uid, "credits" => $credits, "appSecret" => $this->appSecret, "appKey" => $this->appKey, "timestamp" => $timestamp, 'redirect' => $redirect);
        $array = array_merge($array, $otherParams);
        $sign = $this->sign($array);
        $redirect = urlencode($redirect);
        $url = $url . "uid=" . $uid . "&credits=" . $credits . "&appKey=" . $this->appKey . "&timestamp=" . $timestamp . "&redirect=" . $redirect;
        foreach ($otherParams as $key => $value) {
            $url .= '&' . $key . '=' . urlencode($value);
        }
        $url .= "&sign=" . $sign;
        return $url;
    }


    /**
     *  生成订单查询请求地址
     *  orderNum 和 bizId 二选一，不填的项目请使用空字符串
     * @param $orderNum
     * @param $bizId
     * @return string
     */
    public function buildCreditOrderStatusRequest($orderNum, $bizId)
    {
        $appKey = $this->appKey;
        $appSecret = $this->appSecret;
        $url = "http://www.duiba.com.cn/status/orderStatus?";
        $timestamp = time() * 1000 . "";
        $array = array("orderNum" => $orderNum, "bizId" => $bizId, "appKey" => $appKey, "appSecret" => $appSecret, "timestamp" => $timestamp);
        $sign = $this->sign($array);
        $url = $url . "orderNum=" . $orderNum . "&bizId=" . $bizId . "&appKey=" . $appKey . "&timestamp=" . $timestamp . "&sign=" . $sign;
        return $url;
    }

    /**
     *  兑换订单审核请求
     *  有些兑换请求可能需要进行审核，开发者可以通过此API接口来进行批量审核，也可以通过兑吧后台界面来进行审核处理
     * @param $passOrderNums
     * @param $rejectOrderNums
     * @return string
     */
    public function buildCreditAuditRequest($passOrderNums, $rejectOrderNums = null)
    {
        $appKey = $this->appKey;
        $appSecret = $this->appSecret;
        $url = "http://www.duiba.com.cn/audit/apiAudit?";
        $timestamp = time() * 1000 . "";
        $array = array("appKey" => $appKey, "appSecret" => $appSecret, "timestamp" => $timestamp);
        if ($passOrderNums != null && !empty($passOrderNums)) {
            $string = null;
            while (list($key, $val) = each($passOrderNums)) {
                if ($string == null) {
                    $string = $val;
                } else {
                    $string = $string . "," . $val;
                }
            }
            $array["passOrderNums"] = $string;
        }
        if ($rejectOrderNums != null && !empty($rejectOrderNums)) {
            $string = null;
            while (list($key, $val) = each($rejectOrderNums)) {
                if ($string == null) {
                    $string = $val;
                } else {
                    $string = $string . "," . $val;
                }
            }
            $array["rejectOrderNums"] = $string;
        } else {
            $array["rejectOrderNums"] = '';
        }
        $sign = $this->sign($array);
        $url = $url . "appKey=" . $appKey . "&passOrderNums=" . $array["passOrderNums"] . "&rejectOrderNums=" . $array["rejectOrderNums"] . "&sign=" . $sign . "&timestamp=" . $timestamp;
        return $url;
    }


    /**
     *  md5签名，$array中务必包含 appSecret
     * @param $array
     * @return string
     */
    public function sign($array)
    {
        ksort($array);
        $string = "";
        while (list($key, $val) = each($array)) {
            $string = $string . $val;
        }
        return md5($string);
    }

    /**
     *  签名验证,通过签名验证的才能认为是合法的请求
     * @param $appSecret
     * @param $array
     * @return bool
     */
    public function signVerify($appSecret, $array)
    {
        $newarray = array();
        $newarray["appSecret"] = $appSecret;
        reset($array);
        while (list($key, $val) = each($array)) {
            if ($key != "sign") {
                $newarray[$key] = $val;
            }

        }
        $sign = $this->sign($newarray);
        if ($sign == $array["sign"]) {
            return true;
        }
        return false;
    }

}