<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午4:19
 */
namespace app\modules\api\controllers;
use app\components\nowpay;
use app\models\JdcardList;
use app\models\VirtualDepotJdcard;
use yii;
class TestqyController extends BaseController
{
    /**
     * @return 测试发送金额
     */
    public function actionIndex()
    {

        $amount='1';  //金额
        $desc='给辉哥打款'; // 中奖商品标题
        $openid='oukEswITVpHqjrwN651EWIqaT58w'; // 对应appid 用户的openid
        $partner_trade_no='DD'.time().mt_rand(10000, 99999); // 站内订单号，因为测试所以随便填的
        $re_user_name=''; //用户微信的实名认证
         $result=\Yii::$app->wxpay->pay($partner_trade_no,$amount,$re_user_name,$desc,$openid);
        var_dump($result);


    }

    /**
     * @return 测试现在支付
     */

    public function actionNowpay(){
        $result=\Yii::$app->nowpay->notify([$this, 'notifyRechargeSuccess'],[$this, 'notifyRechargeFail']);
        echo $result;
    }
    public function notifyRechargeSuccess(){

    }
    public function notifyRechargeFail(){

    }

    /*
     * @return 测试 京东卡密
     */
    public function actionJdcard(){

     //   var_dump(\Yii::$app);exit;
        $nominal=50;
        $balance=VirtualDepotJdcard::find()->andwhere(['status'=>1])->count('id');

        if($balance<2)
        {
        $rs= \Yii::$app->jdcard->pullcart($nominal);      //面额
        }
        $cardinfo=VirtualDepotJdcard::find()->andwhere(['status'=>0,'denomination'=>$nominal])->asArray()->one();
        $jdcard=VirtualDepotJdcard::findone($cardinfo['id']);
        $jdcard->status=1;
        $jdcard->service_time=time();
        $rs=$jdcard->save();
        if($rs)
        {
            //发送卡密到手机

        }

    }
}