<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/19
 * Time: 上午10:38
 */
namespace app\modules\admin\controllers;

use app\helpers\DateFormat;
use app\helpers\Excel;
use app\helpers\MyRedis;
use app\models\Brand;
use app\models\CategoryBrand;
use app\models\CodeDistribution;
use app\models\CurrentPeriod;
use app\models\Product;
use app\models\ProductCategory;
use app\models\Image;
use app\models\ProductImage;
use app\modules\admin\models\OrderManageGroup;
use app\modules\image\models\UploadForm;
use app\services\Category;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use app\modules\admin\models\BackstageLog;
use app\helpers\Ex;

class ProductController extends BaseController
{

    const PERIOD_ALL_CODE_KEY = 'PERIOD_ALL_CODE_';  // set 码表  productid__periodid

    public function actionIndex()
    {
        $cats = ProductCategory::find()->all();
        $cats = ArrayHelper::map($cats, 'id', 'name');

        $allOrderList = ProductCategory::allOrderList();
        foreach ($allOrderList as &$cat) {
            $cat['line'] = ProductCategory::getTrim($cat['level']);
        }

        $request = Yii::$app->request;

        $query = Product::find()->select('products.*')->leftJoin('product_category a', 'products.cat_id = a.id');

        $where = [];
        $condition = [];
        if($request->isGet){
            $post = $request->get();

            if(isset($post['recommand']) && $post['recommand'] != '0'){
                $condition['recommand'] = $post['recommand'];
                if($post['recommand'] == 2) $post['recommand'] = 0;
                $where['products.is_recommend'] = $post['recommand'];
            }

            if(isset($post['market']) && $post['market'] != 'all'){
                $condition['market'] = $post['market'];
                $where['products.marketable'] = $post['market'];
            }else{
                $condition['market'] = 'all';
            }

            if(!empty($where)) $query = $query->where($where);

            if(isset($post['limit']) && $post['limit'] != '0'){
                $condition['limit'] = $post['limit'];
                if($post['limit'] == 1) {
                    $query->andWhere(['>=','products.limit_num',0]) ;
                } elseif($post['limit']==2) {
                    $query->andWhere(['=','products.limit_num',0]) ;
                }
            }

            if(isset($post['ten']) && $post['ten'] != '0'){
                $condition['ten'] = $post['ten'];
                if($post['ten'] == 1) {
                    $query->andWhere(['=','products.buy_unit',10]) ;
                } elseif($post['ten']==2) {
                    $query->andWhere(['=','products.buy_unit',0]) ;
                }
            }

            if(isset($post['startTime']) && $post['startTime'] != ''){
                $gt = ['>=', 'products.created_at', strtotime($post['startTime'])];
                $condition['start'] = $post['startTime'];
                $query->andWhere($gt) ;
            }
            if(isset($post['endTime']) && $post['endTime'] != ''){
                $lt = ['<=', 'products.created_at', strtotime($post['endTime'])];
                $condition['end'] = $post['endTime'];
                $query->andWhere($lt) ;
            }
            if(isset($post['content']) && $post['content'] != ''){
                $like = ['or', 'products.name like "%'.$post['content'].'%"', 'bn like "%'.$post['content'].'%"'];
                $condition['content'] = $post['content'];
                $query->andWhere($like);
            }
            if(isset($post['cart']) && $post['cart'] != 0){
                $condition['cart'] = $post['cart'];
                $query->andWhere(['or', 'a.top_id = '.$post['cart'].'', 'a.parent_id = '.$post['cart'].'', 'a.id = '.$post['cart'].'']);
            }
        }

        if(isset($post['excel']) && $post['excel'] == 'product'){
            $data = [];
            $pagination = new Pagination(['totalCount' => $query->count(), 'defaultPageSize' =>PHP_INT_MAX, 'pageSizeLimit'=>[0, PHP_INT_MAX] ]);

            $list = $query->offset($pagination->offset)->limit($pagination->limit)->orderBy('marketable desc,id desc')->asArray()->all();

            $productIds = ArrayHelper::getColumn($list, 'id');
            $currentPeriods = CurrentPeriod::find()->where(['product_id'=>$productIds])->indexBy('product_id')->all();

            $data[0] = ['name'=>'商品名称', 'live_time'=>"使用期限",'bn'=>'编号', 'allow_share'=>'晒单','catone'=>'一级分类', 'cattwo'=>'二级分类', 'catthree'=>'三级分类','price'=>'价格', 'limit'=>'限购','number'=>'伙购期数','period'=>'总期数', 'status'=>'状态', 'recommand'=>'推荐', 'time'=>'时间','live_time'=>'使用期限'];
            foreach($list as $key => $val){
                $key = $key +1;
                $data[$key]['name'] = $val['name'];
                $data[$key]['live_time'] = $val['live_time']."年";
                $data[$key]['bn'] = $val['bn'];
                if($val['allow_share'] == 1) $allow = '是';else $allow = '否';
                $data[$key]['allow_share'] = $allow;
                $catArr = Category::getCatName($val['cat_id']);
                $data[$key]['catone'] = $catArr[0];
                $data[$key]['cattwo'] = isset($catArr[1]) ? $catArr[1] : '';
                $data[$key]['catthree'] = isset($catArr[2]) ? $catArr[2] : '';
                $data[$key]['price'] = $val['price'];
                $data[$key]['limit'] = $val['limit_num'];
                $data[$key]['number'] = isset($currentPeriods[$val['id']]) ? $currentPeriods[$val['id']]['period_number'] : '';
                $data[$key]['period'] = $val['store'];
                if($val['marketable'] == 1){
                    $s = '销售中';
                } elseif($val['marketable'] == 0){
                    $s = '结束';
                }elseif($val['marketable'] == 2){
                    $s = '新增';
                }
                $data[$key]['status'] = $s;
                if($val['is_recommend'] == 1) $re = '是';else $re = '否';
                $data[$key]['recommand'] = $re;
                $data[$key]['time'] = DateFormat::microDate($val['created_at']);
                if($val['live_time']) $time = '10年';else $time = '长期';
                $data[$key]['live_time'] = $time;
            }
            $excel = new Ex();
            $excel->download( $data, '商品列表-'.date('Y-m-d H:i:s').'.xls');
        }

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $products = $query->offset($pagination->offset)->limit($pagination->limit)->orderBy('marketable desc,id desc')->asArray()->all();
        foreach ($products as &$product) {
            $product['imgUrl'] = Image::getProductUrl($product['picture'], 58, 58);
        }
        $productIds = ArrayHelper::getColumn($products, 'id');
        $currentPeriods = CurrentPeriod::find()->where(['product_id'=>$productIds])->indexBy('product_id')->all();

        if(empty($post)){
            $url = Yii::$app->request->getUrl().'?excel=product';
        }else{
            $url = Yii::$app->request->getUrl().'&excel=product';
        }

        return $this->render('index', [
            'products' => $products,
            'pagination' => $pagination,
            'cats' => $cats,
            //'firstLevelCats' => $firstLevelCats,
            'catsList' => $allOrderList,
            'currentPeriods' => $currentPeriods,
            'where' => $where,
            'condition' => $condition,
            'page' => $request->get('page', 1),
            'url' => $url
        ]);
    }

    private function initCurrentPeriodInfo($productModel, $period_numer = 1)
    {
        $currentPeriod = CurrentPeriod::findOne(['product_id'=>$productModel->id]);
        if (!$currentPeriod) {
            $currentPeriod = new CurrentPeriod();
            $currentPeriod->table_id = mt_rand(100, 109);
            $currentPeriod->product_id = $productModel->id;
            $currentPeriod->price = $productModel->price;
            $currentPeriod->limit_num = $productModel->limit_num;
            $currentPeriod->buy_unit = $productModel->buy_unit;
            $currentPeriod->period_number = $period_numer;
            $currentPeriod->sales_num = 0;
            $currentPeriod->progress = 0;
            $currentPeriod->left_num = $productModel->price;
            $currentPeriod->start_time = microtime(true);
            $currentPeriod->save(false);

            $periodId = $currentPeriod->id;

            static::initCodes(ArrayHelper::toArray($productModel), $periodId);
        }
    }

    private static function initCodes($product,$periodId)
    {

        $redis = new MyRedis();
        $codeKey = self::PERIOD_ALL_CODE_KEY.$periodId;

        $start = 10000001;
        $end = $start + $product['price'];
        $pipe = $redis->pipeline();
        for ($i=$start;$i<$end;$i++) {
            $pipe->sadd($codeKey,$i);
            $num = $i - $start + 1;
            if (($num > 0 && $num % 10000 == 0) || $i == ($end-1)) {
                $pipe->exec();
                if($i!=($end-1)) {
                    $pipe = $redis->pipeline();
                }
            }
        }

        if ($redis->slen($codeKey) != $product['price']) {
            $redis->del($codeKey);
            static::initCodes($product,$periodId);
        }
    }


    public function actionAdd()
    {
        $allCat = ProductCategory::allOrderList();
        $categoryItems = [];
        if ($allCat) {
            foreach ($allCat as $cat) {
                $categoryItems[$cat['id']] = ProductCategory::getLine($cat['level']) . $cat['name'];
            }
        }
        $model = new Product();
        $model->limit_num = 0;
        $model->buy_unit = 1;
        $model->marketable = 2;
        $model->allow_share = 1;
        $model->is_recommend = 0;
        $model->list_order = 0;
        $request = Yii::$app->request;
        $tran = Yii::$app->db->beginTransaction();
        if ($request->isPost) {
            if ($model->load($request->post())) {
                $album = $request->post('album');
                $time = time();
                $model->updated_at = $time;
                $model->created_at = $time;
                if ($model->validate()) {
                    $model->save();
                    if ($album) {
                        $this->addProductImages($model->id, $album);
                    }
                    //添加到商品分类关联表
                    $post = $request->post('Product');
                    $brandModel = CategoryBrand::find()->where(['and', 'cat_id='.$post['cat_id'], 'brand_id='.$post['brand_id']])->one();
                    if($brandModel){
                        $brandModel->product_num = $brandModel['product_num'] + 1;
                        if(!$brandModel->save()){
                            $tran->rollBack();
                        }
                    }else{
                        $categoryBrand = new CategoryBrand();
                        $categoryBrand->cat_id = $post['cat_id'];
                        $categoryBrand->brand_id = $post['brand_id'];
                        $categoryBrand->product_num = 1;
                        if(!$categoryBrand->save()){
                            $tran->rollBack();
                        }
                    }
                    $tran->commit();
                    BackstageLog::addLog(\Yii::$app->admin->id, 2, '新增产品'.$post['name']);

                    return $this->redirect(['/admin/product']);
                }
            }
        }

        $brand = Brand::find()->all();
        $brandItems = ArrayHelper::map($brand, 'id', 'name');
        $orderManageGroup = OrderManageGroup::find()->all();
        $orderManageGidItems = ArrayHelper::map($orderManageGroup, 'id', 'name');
        array_unshift($orderManageGidItems, '====请选择小组');
        return $this->render('add', [
            'model' => $model,
            'categoryItems' => $categoryItems,
            'brandItems' => $brandItems,
            'deliveryItems' => Product::$deliveries,
            'orderManageGidItems' => $orderManageGidItems,
        ]);
    }

    private function addProductImages($productId, $pictures)
    {
        foreach ($pictures as $pic) {
            $productImage = new ProductImage();
            $productImage->basename = $pic;
            $productImage->product_id = $productId;
            $productImage->save();
        }
    }

    public function actionEdit()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Product::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('页面未找到');
        }
        $allCat = ProductCategory::allOrderList();
        $categoryItems = [];
        if ($allCat) {
            foreach ($allCat as $cat) {
                $categoryItems[$cat['id']] = ProductCategory::getLine($cat['level']) . $cat['name'];
            }
        }

        $productImage = ProductImage::findAll(['product_id'=>$model->id]);
        if ($productImage) {
            $pictures = ArrayHelper::getColumn($productImage, 'basename');
            $info = [];
            foreach ($pictures as $basename) {
                $info[] = [
                    'basename' => $basename,
                    'url' => Image::getProductUrl($basename, 200, 200),
                ];
            }
            $pictures = $info;
        } else {
            $pictures = [];
        }
        //$oldMarketable = $model->marketable;
        $request = Yii::$app->request;
        $tran = Yii::$app->db->beginTransaction();
        //try{}catch (){$tran->rollBack();}
        if ($request->isPost) {
            $post = $request->post('Product');
            //更新商品分类关联表
            if( ($post['cat_id'] != $model['cat_id'] || $post['brand_id'] != $model['brand_id']) ){
                $productModel = CategoryBrand::find()->where(['and', 'cat_id='.$model['cat_id'], 'brand_id='.$model['brand_id']])->one();
                if($productModel){
                    $num = $productModel['product_num'] - 1;
                    if($num < 0) $num = 0;
                    $productModel->product_num = $num;
                    if (!$productModel->save()) {
                        $tran->rollBack();
                    }
                }
                $brandModel = CategoryBrand::find()->where(['and', 'cat_id='.$post['cat_id'], 'brand_id='.$post['brand_id']])->one();
                if($brandModel){
                    $brandModel->product_num = $brandModel['product_num'] + 1;
                    if (!$brandModel->save()) {
                        $tran->rollBack();
                    }
                }else{
                    $categoryBrand = new CategoryBrand();
                    $categoryBrand->cat_id = $post['cat_id'];
                    $categoryBrand->brand_id = $post['brand_id'];
                    $categoryBrand->product_num = 1;
                    if(!$categoryBrand->save()){
                        $tran->rollBack();
                    }
                }
            }else{
                $productModel = CategoryBrand::find()->where(['and', 'cat_id='.$model['cat_id'], 'brand_id='.$model['brand_id']])->one();
                if(!$productModel){
                    $categoryBrand = new CategoryBrand();
                    $categoryBrand->cat_id = $model['cat_id'];
                    $categoryBrand->brand_id = $model['brand_id'];
                    $categoryBrand->product_num = 1;
                    if(!$categoryBrand->save()){
                        $tran->rollBack();
                    }
                }
            }

            if ($model->load($request->post())) {

                $model->updated_at = time();
                if ($model->validate()) {
                    if (!$model->save()) {
                        $tran->rollBack();
                    }
                    if ($album = $request->post('album')) {
                        $this->addProductImages($model->id, $album);
                    }
                    $tran->commit();
                    BackstageLog::addLog(\Yii::$app->admin->id, 2, '修改产品'.$model['name']);
                    return $this->redirect(['/admin/product', 'page'=>$request->post('page')]);
                }
            }
        }

        $brand = Brand::find()->all();
        $brandItems = ArrayHelper::map($brand, 'id', 'name');

        $orderManageGroup = OrderManageGroup::find()->all();
        $orderManageGidItems = ArrayHelper::map($orderManageGroup, 'id', 'name');
        array_unshift($orderManageGidItems, '====请选择小组');

        return $this->render('edit', [
            'model' => $model,
            'categoryItems' => $categoryItems,
            'brandItems' => $brandItems,
            'pictures' => $pictures,
            'deliveryItems' => Product::$deliveries,
            'orderManageGidItems' => $orderManageGidItems,
            'page' => $request->get('page'),
        ]);
    }

    public function actionDel()
    {
        $request = \Yii::$app->request;
        $response = \Yii::$app->response;
        if ($request->isAjax) {
            $id = $request->post('id');
            $model = Product::findOne($id);
            $brandModel = CategoryBrand::find()->where(['and', 'cat_id='.$model['cat_id'], 'brand_id='.$model['brand_id']])->one();
            $brandModel->product_num = $brandModel['product_num'] - 1;
            $brandModel->save();
            BackstageLog::addLog(\Yii::$app->admin->id, 2, '删除产品'.$model['name']);
            $delete = Product::deleteAll(['id' => $id]);
            $response->format = \yii\web\Response::FORMAT_JSON;
            if ($delete) {
                return [
                    'error' => 0,
                    'message' => '删除成功'
                ];
            }
            return [
                'error' => 1,
                'message' => '删除失败'
            ];

        }
    }

    public function actionMarket()
    {
        set_time_limit(0);
        $request = \Yii::$app->request;
        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        if ($request->isAjax) {
            $ids = $request->post('pid');
            $market = $request->post('market');
            $market = $market ? 1:0;
            $marketMsg = $market ? '上架' : '下架';
            $add = $request->post('dataId') ? $request->post('dataId') : '';
            $model = Product::findOne($ids);
            try {
                $ret = Product::updateAll(['marketable'=>$market], ['id'=>$ids]);

                if($market == 1){
                    //新增的产品生成码和期数
                    if($add){
                        if($ret){
                            $this->initCurrentPeriodInfo($model);
                        }
                    }else{
                        $currenPeriod = CurrentPeriod::find()->where(['product_id'=>$model['id']])->one();
                        if(!$currenPeriod){
                            $conn = Yii::$app->db;

                            //增加期数
                            $command = $conn->createCommand('select period_number from periods where product_id = '.$model['id'].' order by id desc');
                            $period = $command->queryOne();
                            $this->initCurrentPeriodInfo($model, $period['period_number'] + 1);
                        }
                    }
                }elseif($market == 0){
                    $currenPeriod = CurrentPeriod::find()->where(['product_id'=>$model['id']])->one();
                    if($currenPeriod && $currenPeriod['sales_num'] == 0){
                        $currenPeriod->delete();
                    }
                }

                return [
                    'error' => 0,
                    'message' => $marketMsg . '成功'
                ];
            } catch(\Exception $e) {var_dump($e->getMessage());
                return [
                    'error' => 1,
                    'message' => $marketMsg . '失败',
                    'pid' => $ids
                ];
            }

        }
    }

    //验证推荐
    public function actionCheckRecommand()
    {
        $request = \Yii::$app->request;
        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        if ($request->isAjax) {
            $count = Product::find()->where(['is_recommend'=>1])->count();
            if($count >9){
                return [
                    'error' => 1,
                    'message' => '推荐数已超过9个',
                ];
            }
        }
    }

    public function actionBrand()
    {
        $request = Yii::$app->request;
        $catId = $request->post('cat_id', 0);
        if ($request->isAjax) {
            if ($catId) {
                $categoryBrand = CategoryBrand::findAll(['cat_id' => $catId]);
                $brandIds = ArrayHelper::getColumn($categoryBrand, 'brand_id');
                $brands = Brand::findAll(['id' => $brandIds]);
            } else {
                $brands = Brand::find()->all();
            }
            $result = ArrayHelper::map($brands, 'id', 'name');
            echo Json::encode($result);
        }
    }

    public function actionUploadImage()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('imageFile');
            if ($uploadData = $model->uploadProduct()) {
                // file is uploaded successfully
                $response = Yii::$app->response;
                $response->format = Response::FORMAT_JSON;
                return Json::encode($uploadData);
            }
        }
    }

    public $enableCsrfValidation = false;
    public function actionUploadInfoImage()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('imgFile');
            if ($uploadData = $model->uploadProductInfo()) {
                // file is uploaded successfully
                echo Json::encode($uploadData);
            }
        }
    }

    public function actionDeleteProductImage()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $productId = $request->post('product_id', 0);
            $picture = $request->post('picture', 0);
            $productImage = ProductImage::findOne(['product_id'=>$productId, 'basename'=>$picture]);
            if ($productImage) {
                Image::deleteProductImage($picture);
                $product = Product::findOne(['id'=>$productId, 'picture'=>$picture]);
                if ($product) {
                    $product->picture = '';
                    $product->save(false);
                }
                $productImage->delete();
            }
        }
        return true;
    }

    public function actionDeleteImage()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $picture = $request->post('picture', 0);
            if ($picture) {
                Image::deleteProductImage($picture);
            }

        }
        return true;
    }

    //分类二级菜单
    public function actionChildrenCart()
    {
        $request = Yii::$app->request;
        $parent = $request->get('parent_id');
        if($parent){
            $children = ProductCategory::find()->where(['top_id'=>$parent])->asArray()->all();
            return json_encode($children);
        }
    }
}