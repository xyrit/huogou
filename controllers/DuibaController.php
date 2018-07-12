<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/4/22
 * Time: 16:20
 */
namespace app\controllers;

use app\helpers\MyRedis;
use app\models\DuibaOrderDistribution;
use app\models\Order;
use app\models\Product;
use app\models\User;
use app\models\VirtualProductInfo;
use Yii;
use yii\web\Response;

class DuibaController extends BaseController
{


    public function actionRedirect()
    {
        $request = Yii::$app->request;
        $orderId = $request->get('orderId');
        $token = $request->get('token');
        $tokenSource = $request->get('tokenSource');
        $tokenType = in_array($tokenSource,['__ios__','__android__']) ? 1 : null;
        $user = User::findIdentityByAccessToken($token,$tokenType);
        if (!$user || $user['status']==1) {
            echo '未登录!';
            Yii::$app->end();
        }

        $userId = $user['id'];
        $order = Order::findOne(['id'=>$orderId, 'user_id'=>$userId, 'status'=>[0,6]]);
        if (!$order) {
            echo '订单信息有误!';
            Yii::$app->end();
        }

        $productId = $order['product_id'];
        $product = Product::find()->select('face_value,delivery_id')->where(['id'=>$productId])->one();
        $money = $product['face_value'];
        $deliveryId = $product['delivery_id'];

        $sumCredits = DuibaOrderDistribution::findByTableId($user['home_id'])->select('sum(credits) as sum_credits')->where(['order_no'=>$orderId,'status'=>[0,1]])->asArray()->one();
        $sumCredits = !empty($sumCredits['sum_credits']) ? $sumCredits['sum_credits'] : 0;
        if ($sumCredits) {
            $money = $money - $sumCredits/100;
            if ($money<=0) {
                echo '充值信息错误!';
                Yii::$app->end();
            }
        }

        $duiba = Yii::$app->duiba;

        $address = VirtualProductInfo::find()->where(['order_id'=>$orderId, 'user_id'=>$userId])->asArray()->one();
        if (!$address) {
            echo '无确认地址!';
            Yii::$app->end();
        }
        $account = $address['account'];
        $name = $address['name'];
        if ($deliveryId==5) {
            $url = $duiba->redirectAlipay($userId, $orderId, $money, $account, $name);
        } elseif($deliveryId==6) {
            $url = $duiba->redirectQB($userId, $orderId, $money, $account);
        } elseif($deliveryId==7) {
            $url = $duiba->redirectPhonebill($userId, $orderId, $money, $account);
        }
        return $this->redirect($url);

    }

    /** 扣分通知
     * @return array
     */
    public function actionConsume()
    {
        $request = \Yii::$app->request;
        $uid = $request->get('uid');

        $uidArr = explode('_', $uid);
        $userId = $uidArr[0];
        $no = $uidArr[1];

        $redis = new MyRedis();
        $key = 'DUIBA_CONSUME_REQUEST_'.$uid;

        if ($redis->ttl($key)<=0) {
            $redis->expire($key, 10);
        }

        if ($redis->incr($key) > 1) {
            return [
                'status' => 'fail',
                'errorMessage' => '请求频繁',
                'credits' => 0,
            ];
        }
        $duiba = Yii::$app->duiba;
        $result = $duiba->consumeNotify();
        $redis->del($key);
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        return $result;
    }

    /** 结果通知
     * @return array|string
     */
    public function actionNotify()
    {
        $request = \Yii::$app->request;
        $duiba = Yii::$app->duiba;
        $result = $duiba->resultNotify();
        return $result;
    }



}