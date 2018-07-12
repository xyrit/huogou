<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/18
 * Time: 下午12:55
 */

namespace app\controllers;

use app\helpers\Brower;
use Yii;
use yii\web\Controller;
use app\models\User;
use app\models\Fund;

class BaseController extends Controller
{
	public $userId=0;
    public $token = '';

    public function init()
    {
        $user = Yii::$app->user;
        $this->token = $user->accessToken;
        $from = Brower::whereFrom(); // 来源判断  是否滴滴


    }

    public function render($view,$data=[]){
        $fund = Fund::find()->asArray()->one();
        $data['token'] = '';//$this->token;
        $data['fund'] = $fund['count'];
        $data['description'] = "伙购网是一种全新的购物方式，是时尚、潮流的风向标，能满足个性、年轻消费者的购物需求，由深圳市橙果网络科技有限公司注入巨资打造的新型购物网。";
        $data['keywords'] = "伙购网,伙购,夺宝,伙购官网,伙购网";
        return parent::render($view, $data);
    }

    public function redirectDeviceUrl($weixinUrl,$mobileUrl)
    {
        $request = \Yii::$app->request;
        if (!$request->cookies->getValue('pcview')) {
            if (Brower::isMcroMessager()) {
                $this->redirect($weixinUrl);
                Yii::$app->end();
            } elseif(Brower::isMobile()) {
                $this->redirect($mobileUrl);
                Yii::$app->end();
            }
        }
    }

}