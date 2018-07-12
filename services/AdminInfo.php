<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午3:16
 */

namespace app\services;

use app\models\Product;
use app\models\ProductCategory;
use app\models\Period;
use app\models\Order;
use yii\base\Object;

class AdminInfo extends Object
{
    /** 商品信息
     * @param $id 商品ID
     */
    public static function productList($product_id)
    {
        if (is_array($product_id)) {
            $product = Product::find()->where(['id'=>$product_id])->indexBy('id')->asArray()->all();
        } else {
            $product = Product::find()->where(['id'=>$product_id])->asArray()->one();
            $product['category'] = ProductCategory::findOne($product['cat_id'])['name'];
        }
        return $product;
    }
    
    /**
     * 商品已满员期数信息
     */
   public static function periodsList($period_id)
    {
        if (is_array($period_id)) {
            $Period = Period::find()->where(['id'=>$period_id])->indexBy('id')->asArray()->all();
        } else {
            $Period = Period::find()->where(['id'=>$period_id])->asArray()->one();
        }
        return $Period;
    }
    
    /**
     * 订单信息
     */
    
    
    

}
