<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午2:19
 */
namespace app\services;

use app\helpers\Brower;
use app\models\CurrentPeriod;
use app\models\Period as PeriodModel;
use app\models\ProductCategory;
use app\models\ProductImage;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\Product as ProductModel;

class Product
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
    public static function getList($catId, $brandId, $page, $orderFlag = 0, $isLimit = 'all', $perpage = 20,$searchWord = '',$buyUnit = 'all')
    {
        $where = [];
        if ($catId) {
            $cats = ProductCategory::allOrderList($catId);
            if (!$cats) {
                $where['products.cat_id'] = $catId;
            } else {
                $catIds[] = $catId;
                $catIds = ArrayHelper::getColumn($cats, 'id');
                $where['products.cat_id'] = $catIds;
            }
        }
        if ($brandId) {
            $where['products.brand_id'] = $brandId;
        }
        $query = \app\models\Product::find()
            ->select('products.*,c.limit_num limit_num,c.buy_unit buy_unit,c.id period_id,c.period_number period_number,c.sales_num sales_num, c.left_num left_num,c.price period_price')
            ->where($where);
        $query->andWhere(['<>','products.marketable',2]);//排除新增
        if ($searchWord) {
            $query->leftJoin('current_periods c', 'c.product_id = products.id');
            $query->where(['or',"name like '%".$searchWord."%'",['or',"tag like '%".$searchWord."%'"]]);
        }else {
            $query->rightJoin('current_periods c', 'c.product_id = products.id');
        }
        if ($isLimit !== 'all') {
            if ($isLimit) {
                $query->andWhere(['<>','c.limit_num',0]);
            } else {
                $query->andWhere(['=','c.limit_num',0]);
            }
        }
        if ($buyUnit !== 'all') {
            if ($buyUnit) {
                $query->andWhere(['=','c.buy_unit',$buyUnit]);
            }
        }
        $from=Brower::whereFrom();

        $query->andWhere(['in','products.display',[0,$from]]);

        switch ($orderFlag) {
            case 10:
                $orderBy = 'c.progress desc';//即将揭晓
                break;
            case 20:
                $orderBy = 'products.list_order desc,c.period_number desc';//人气
                break;
            case 30:
                $orderBy = 'c.left_num asc';//剩余人次
                break;
            case 40:
                $orderBy = 'products.created_at desc';//最新
                break;
            case 50:
                $orderBy = 'c.price asc';//价格正序
                break;
            case 51:
                $orderBy = 'c.price desc';//价格倒序
                break;
            case 60:
                $query->andWhere(['is_recommend'=>1]);//热门推荐
                $orderBy = 'products.list_order desc';
                break;
            default:
                $orderBy = 'products.list_order desc';
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

    /**
     * 获取商品名
     */
    public static function getProductName()
    {
        $all = \app\models\Product::find()->all();
        return ArrayHelper::map($all, 'id', 'name');
    }

    /**
     * 获取商品发货方式
     */
    public static function getProductDeliver()
    {
        $all = \app\models\Product::find()->all();
        return ArrayHelper::map($all, 'id', 'delivery_id');
    }

    /**
     * 获取商品分类方式
     */
    public static function getProductCate()
    {
        $all = \app\models\Product::find()->all();
        return ArrayHelper::map($all, 'id', 'cat_id');
    }

    /** 商品信息
     * @param $id 商品ID
     * @return array|null
     */
    public static function info($id)
    {
        if (is_array($id)) {
            $product = ProductModel::find()->where(['id'=>$id])->indexBy('id')->asArray()->all();
        } else {
            $product = ProductModel::find()->where(['id'=>$id])->asArray()->one();
        }

        return $product;
    }

    /** 商品当前期数信息
     * @param $id 商品ID
     * @return array|null
     */
    public static function curPeriodInfo($id)
    {
        if (is_array($id)) {
            $currentPeriod = CurrentPeriod::find()->where(['product_id'=>$id])->indexBy('id')->asArray()->all();
        } else {
            $currentPeriod = CurrentPeriod::find()->where(['product_id'=>$id])->asArray()->one();
        }
        return $currentPeriod;
    }

    /** 当前期数信息
     * @param $id 期数ID
     * @return array|null
     */
    public static function curPeriod($id)
    {
        if (is_array($id)) {
            $currentPeriod = CurrentPeriod::find()->where(['id'=>$id])->indexBy('id')->asArray()->all();
        } else {
            $currentPeriod = CurrentPeriod::find()->where(['id'=>$id])->asArray()->one();
        }
        return $currentPeriod;
    }

    /**
     * 商品所有图片
     * @param $id 商品ID
     */
    public static function images($id)
    {
        $productImage = ProductImage::find()->select('basename')->where(['product_id'=>$id])->asArray()->all();
        $images = ArrayHelper::getColumn($productImage, 'basename');
        return $images;
    }

    /**
     *  商品已满员期数列表
     * @param $id   商品ID
     * @param $page
     * @param int $perpage
     */
    public static function perioadList($id, $page, $perpage = 20,$offset = 0)
    {
        $query = PeriodModel::find()->where(['product_id'=>$id]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage]);
        if ($offset >= $perpage) {
            $query->where(['and','product_id='.$id,['<=','period_number',$offset]]);
        }else if ($offset > 0) {
            $query->where(['and','product_id='.$id,['<=','period_number',$perpage-1]]);
        }
        $perioadList = $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->defaultPageSize)->asArray()->all();
        $return['list'] = $perioadList;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        $return['offset'] = $offset;
        return $return;
    }


    /** 获取所有期数列表
     * @param $productId
     * @param $page
     * @param int $perpage
     * @param bool|false $showUserInfo
     * @return mixed
     */
    public static function allPeriodList($productId, $page, $perpage = 20,$showInfo = false)
    {
        $curPeriod = CurrentPeriod::find()->where(['product_id'=>$productId])->asArray()->one();
        $query = PeriodModel::find()->select(['id'])->where(['product_id'=>$productId]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        if ($curPeriod) {
            $totalCount += 1;
        }
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage]);
        if ($page==1) {
            $offset = 0;
            $limit = $perpage - 1;
        } else {
            $offset = $pagination->offset - 1;
            $limit = $pagination->limit;
        }
        $perioadList = $query->orderBy('id desc')->offset($offset)->limit($limit)->asArray()->all();
        $allPeriodList = [];
        if ($showInfo) {
            foreach($perioadList as $one) {
                $allPeriodListInfo = Period::info($one['id']);
                unset($allPeriodListInfo['goods_info']);
                $allPeriodList[] = $allPeriodListInfo;
            }
        }
        if ($page==1) {
            if ($curPeriod) {
                $curPeriod['status'] = 0;//未揭晓
                $productInfo = static::info($productId);
                unset($productInfo['intro']);
                $curPeriod['goods_picture'] = $productInfo['picture'];
                $curPeriod['period_id'] = $curPeriod['id'];
                unset($curPeriod['table_id']);
                array_unshift($allPeriodList,$curPeriod);
            }
        }
        $return['list'] = $allPeriodList;
        $return['page'] = $page;
        $return['totalCount'] = $totalCount;
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }

    public static function oldPeriodlist($id, $page, $perpage = 20,$showInfo = false)
    {
        $query = PeriodModel::find()->select('id')->where(['product_id'=>$id]);
        $query->andWhere(['<=', 'result_time', time()]);
        $query->andWhere(['>', 'user_id', 0]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1,'defaultPageSize'=>$perpage]);
        $perioadList = $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $oldPeriodList = [];
        if ($showInfo) {
            foreach($perioadList as $one) {
                $periodInfo = Period::info($one['id']);
                unset($periodInfo['goods_info']);
                $oldPeriodList[] = $periodInfo;
            }
        }
        $return['list'] = $oldPeriodList;
        $return['totalCount'] = $totalCount;
        $return['page']=$page; 
        $return['totalPage'] = $pagination->getPageCount();
        return $return;
    }




}