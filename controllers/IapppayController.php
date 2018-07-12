<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/18
 * Time: 下午2:29
 */
namespace app\controllers;

use app\models\User;
use app\services\Pay;
use app\services\Thirdpay;
use yii\helpers\Url;

class IapppayController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $order = $request->get('o');
        $pay = new Thirdpay();

        return $pay->pay($order,'iapp');
    }

    public function actionNotify()
    {
        try {

            $respData = \Yii::$app->request->post() ? : \Yii::$app->request->get() ;
            $respData = http_build_query($respData);

            $iapppay = \Yii::$app->iapppay;
            $transdata = $iapppay->parseResponse($respData);
            if ($transdata && isset($transdata['result']) && $transdata['result']==0) {
                $no = $transdata['cporderid'];
                $appuserid = $transdata['appuserid'];
                $cpprivate = $transdata['cpprivate'];

                $third = new Thirdpay();
                $orderInfo = $third->getOrderByNo($no);
                if ($orderInfo && $transdata['money'] == $orderInfo['post_money']) {
                    echo 'SUCCESS';
                    $third->result('notice',$no,$cpprivate,$transdata);
                }
            }
            echo 'FAILURE';
        } catch(\Exception $e ) {
            echo 'FAILURE';
        }
    }

    public function actionRedirect()
    {
        $respData = \Yii::$app->request->getQueryString();

        $iapppay = \Yii::$app->iapppay;
        $transdata = $iapppay->parseResponse($respData);
        if ($transdata && isset($transdata['result']) && $transdata['result']==0) {
            $no = $transdata['cporderid'];

            $appuserid = $transdata['appuserid'];
            $cpprivate = $transdata['cpprivate'];

            $third = new Thirdpay();
            $orderInfo = $third->getOrderByNo($no);

            if ($orderInfo && $transdata['money'] == $orderInfo['post_money']) {
                $data = $third->result('redirect',$no,$cpprivate,$transdata);
                if (isset($data['url'])) {
                    return $this->redirect($data['url']);
                }
            }
        }
        return $this->redirect(Url::to(['/member/recharge/money-log']));
    }

    public function actionBackmoney()
    {

    }

}