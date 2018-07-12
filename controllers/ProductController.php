<?php
/**
 * User: hechen
 * Date: 15/9/29
 * Time: 下午3:51
 */

namespace app\controllers;

use Yii;
use app\models\Product;

class ProductController extends BaseController
{

    public function actionIndex($pid)
    {
        $this->redirectDeviceUrl(['/weixin/product','pid'=>$pid], ['/mobile/product','pid'=>$pid]);
        $detail = Product::findOne($pid);
        return $this->render('index', [
            'detail' => $detail
        ]);
    }

    public function actionLottery($pid)
    {

        //如果本期是正在进行的商品则跳转到 当前期
        $curPeriod = \app\models\CurrentPeriod::findOne($pid);
        if ($curPeriod) {
            $this->redirectDeviceUrl(['/weixin/product/lottery','pid'=>$pid], ['/mobile/product/lottery','pid'=>$pid]);
            return $this->redirect(['/product', 'pid'=>$curPeriod->product_id]);
        } else {
            $this->redirectDeviceUrl(['/weixin/product','pid'=>$pid], ['/mobile/product','pid'=>$pid]);
        }
        
        return $this->render('lottery');
    }

}