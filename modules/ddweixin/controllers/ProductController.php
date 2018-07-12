<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/19
 * Time: 上午9:51
 */
namespace app\modules\ddweixin\controllers;

use app\models\CurrentPeriod;
use app\models\Product;
use yii\web\NotFoundHttpException;

class ProductController extends BaseController
{
    public function actionIndex()
    {

        return $this->render('index', [

        ]);
    }

    public function actionLottery()
    {
        $periodId = \Yii::$app->request->get('pid');
        $curPeriod = CurrentPeriod::findOne($periodId);
        if ($curPeriod) {
            return $this->redirect(['/ddweixin/product', 'pid'=>$curPeriod->product_id], 302);
        }
        return $this->render('lottery', [

        ]);
    }

    public function actionMoreperiod()
    {
        $productId = \Yii::$app->request->get('pid');

        return $this->render('moreperiod', [
            'productId'=>$productId,
        ]);
    }

    public function actionBuyrecords()
    {
        return $this->render('buyrecords', []);
    }

    public function actionCalresult()
    {
        return $this->render('calresult', []);
    }

    public function actionGoodsimgdesc()
    {
        $productId = \Yii::$app->request->get('pid');
        $product = Product::find()->select('intro')->where(['id'=>$productId])->one();
        if (!$product) {
            throw new NotFoundHttpException('页面未找到');
        }
        $imgDesc = str_replace('src', 'src2',$product->intro);
        return $this->render('goodsimgdesc', [
            'imgdesc'=>$imgDesc,
        ]);
    }




}