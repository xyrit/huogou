<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午2:19
 */
namespace app\services;

use app\helpers\Brower;
use app\models\ActivityProducts;

use app\models\PkPeriod as PeriodModel;
use app\models\PkCurrentPeriod;
use app\models\PkPeriodBuylistDistribution;
use app\models\PkProductImages;
use app\models\ProductCategory;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;


class PkProduct
{
    /**
     *  获取商品列表
     * @param $catId
     * @param $brandId
     * @param $page
     * @param $orderFlag  排序方式 10=即将揭晓，20=人气，30=剩余人次，40=最新，50=价格正序，51=价格倒序,60=热门推荐
     * @param $isLimit  是否限购 all=全部,0=不限购，1=限购
     * @param int $perpage
     *
     */
    public static function getList($catId, $brandId, $page, $orderFlag = 0, $perpage = 20,$searchWord = '')
    {
        $where = [];
        if ($catId) {
            $cats = ProductCategory::allOrderList($catId);
            if (!$cats) {
                $where['activity_products.cat_id'] = $catId;
            } else {
                $catIds[] = $catId;
                $catIds = ArrayHelper::getColumn($cats, 'id');
                $where['activity_products.cat_id'] = $catIds;
            }
        }
        if ($brandId) {
            $where['activity_products.brand_id'] = $brandId;
        }
        $query = \app\models\ActivityProducts::find()
            ->select('activity_products.*,c.end_time end_time,c.id period_id,c.period_no period_no,c.table_id table_id,c.price period_price')
            ->where($where);
        $query->andWhere(['<>','activity_products.marketable',2]);//排除新增
        if ($searchWord) {
            $query->leftJoin('pk_current_periods c', 'c.product_id = activity_products.id');
            $query->where(['or',"name like '%".$searchWord."%'",['or',"tag like '%".$searchWord."%'"]]);
        }else {
            $query->rightJoin('pk_current_periods c', 'c.product_id = activity_products.id');
        }
        $from=Brower::whereFrom();

        $query->andWhere(['in','activity_products.display',[0,$from]]);

        switch ($orderFlag) {
            case 20:
                $orderBy = 'activity_products.list_order desc,c.period_number desc';//人气
                break;
            case 40:
                $orderBy = 'activity_products.created_at desc';//最新
                break;
            case 50:
                $orderBy = 'c.price asc';//价格正序
                break;
            case 51:
                $orderBy = 'c.price desc';//价格倒序
                break;
            case 60:
                $query->andWhere(['is_recommend'=>1]);//热门推荐
                $orderBy = 'activity_products.list_order desc';
                break;
            default:
                $orderBy = 'activity_products.list_order desc';
                break;
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage]);
        $query->orderBy($orderBy);
       $products = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $return['list'] = $products;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    /** 商品信息
     * @param $id 商品ID
     * @return array|null
     */
    public static function info($id)
    {
        if (is_array($id)) {
            $product = ActivityProducts::find()->where(['id'=>$id])->indexBy('id')->asArray()->all();
        } else {
            $product = ActivityProducts::find()->where(['id'=>$id])->asArray()->one();
        }

        return $product;
    }

    /** 当期商品相关信息
     * @param $id
     */
    public static function curPeriodInfo($id)
    {
        if (is_array($id)) {
            $currentPeriod = PkCurrentPeriod::find()->where(['product_id'=>$id])->indexBy('id')->asArray()->all();
        } else {
            $currentPeriod = PkCurrentPeriod::find()->where(['product_id'=>$id])->asArray()->one();
        }
        return $currentPeriod;
    }

    public static function curPeriodBuyCount($pid, $tableId)
    {
        $query = PkPeriodBuylistDistribution::findByTableId($tableId);
        $query->where(['period_id' => $pid]);
        $count = $query->count();
        return $count;
    }

    /** 往期揭晓列表
     * @param $productId     商品
     * @param int $page     页数
     * @param int $perpage    数量
     * @param int $perpage
     * @param int $user_id   用户id
     */
    public static function oldPeriodList($id=0, $page, $perpage = 20)
    {
        $query = PeriodModel::find()->select('id')->where(['product_id' => $id]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
        $perioadList = $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        $oldPeriodList = [];

        foreach ($perioadList as $one) {
            $periodInfo = PkPeriod::info($one['id']);
            unset($periodInfo['goods_info']);
            $oldPeriodList[] = $periodInfo;
        }

        $return['list'] = $oldPeriodList;
        $return['totalCount'] = $totalCount;
        $return['page'] = $page;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }
    /**
     * 商品所有图片
     * @param $id 商品ID
     */
    public static function images($id)
    {
        $productImage = PkProductImages::find()->select('basename')->where(['product_id'=>$id])->asArray()->all();
        $images = ArrayHelper::getColumn($productImage, 'basename');
        return $images;
    }

    public static function intro($productId)
    {
        $intro = ActivityProducts::find()->select('intro')->where(['id'=>$productId])->one();
        return $intro ? $intro['intro'] : '';
    }







}