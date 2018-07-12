<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/19
 * Time: 上午10:41
 */
namespace app\modules\admin\controllers;

use app\models\Brand;
use app\models\CategoryBrand;
use app\models\ProductCategory;
use app\modules\admin\models\ProductCategoryForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use Yii;
use app\modules\admin\models\BackstageLog;

class ProductCategoryController extends BaseController
{

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id', 0);
        $firstLevelCats = ProductCategory::firstLevel();
        array_unshift($firstLevelCats, ['id' => 0, 'name' => '全部分类']);
        $allOrderList = ProductCategory::allOrderList($id);
        if ($allOrderList) {
            if ($id) {
                $currentCat = ProductCategory::find()->where(['id' => $id])->asArray()->one();
                if (!$currentCat) {
                    throw new NotFoundHttpException("页面未找到");
                }
                array_unshift($allOrderList, $currentCat);
            }
            foreach ($allOrderList as &$cat) {
                $cat['line'] = ProductCategory::getTrim($cat['level']);
            }
        }
        return $this->render('index', [
            'cats' => $allOrderList,
            'firstLevelCats' => $firstLevelCats,
            'id' => $id,
        ]);
    }

    public function actionAdd()
    {

        $allCat = ProductCategory::allOrderList();
        $categoryItems = [];
        $categoryItems[0] = '无上级分类';
        if ($allCat) {
            foreach ($allCat as $cat) {
                $categoryItems[$cat['id']] = ProductCategory::getLine($cat['level']) . $cat['name'];
            }
        }

        $request = \Yii::$app->request;
        $pid = $request->get('pid', 0);

        $formModel = new ProductCategoryForm();
        $formModel->parent_id = $pid;
        if ($request->isPost) {
            if ($formModel->load($request->post()) && $formModel->validate()) {
                $model = new ProductCategory();
                $model->name = $formModel->name;
                $model->parent_id = $formModel->parent_id;
                $model->updated_at = time();
                $model->level = ProductCategory::getLevelByPid($formModel->parent_id);
                $model->top_id = ProductCategory::getTopIdByPid($formModel->parent_id);
                $orderBy =$request->post('ProductCategoryForm');
                $model->list_order = $orderBy['list_order'];
                if ($model->save()) {
                    BackstageLog::addLog(\Yii::$app->admin->id, 3, '新增分类'.$formModel->name);
                    return $this->redirect(['/admin/product-category']);
                }
            }
        }
        return $this->render('add', [
            'formModel' => $formModel,
            'categoryItems' => $categoryItems,
        ]);
    }

    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $model = ProductCategory::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException("页面未找到");
        }

        $request = \Yii::$app->request;
        $formModel = new ProductCategoryForm();
        $formModel->name = $model->name;
        $formModel->parent_id = $model->parent_id;
        $formModel->list_order = $model['list_order'];

        if ($request->isPost) {
            if ($formModel->load($request->post()) && $formModel->validate())

            $model->name = $formModel->name;
            $orderBy =$request->post('ProductCategoryForm');
            $model->list_order = $orderBy['list_order'];
            $model->updated_at = time();
            if ($model->save()) {
                CategoryBrand::deleteAll(['cat_id' => $model->id]);
                /*foreach ($formModel->brands as $brandId) {
                    $categoryBrand = new CategoryBrand();
                    $categoryBrand->cat_id = $model->id;
                    $categoryBrand->brand_id = $brandId;
                    $categoryBrand->brand_order = 0;
                    $categoryBrand->save();
                }*/
                return $this->redirect(['/admin/product-category']);
            }
        }

        return $this->render('edit', [
            'formModel' => $formModel,
            'parentName' => ProductCategory::cateName($model->parent_id),
        ]);

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

    public function actionDel()
    {
        $request = \Yii::$app->request;
        $response = \Yii::$app->response;
        if ($request->isAjax) {
            $id = $request->post('id');
            $children = ProductCategory::children($id);
            $response->format = \yii\web\Response::FORMAT_JSON;
            if ($children) {
                return [
                    'error' => 1,
                    'message' => '该分类有子类，不能删除'
                ];
            } else {
                $delete = ProductCategory::deleteAll(['id' => $id]);
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
    }

    public function actionChangeOrder()
    {
        $request = Yii::$app->request;
        $get = $request->get();
        if($get['id']){
            $model = ProductCategory::findOne($get['id']);
            $model->list_order = $get['order'];
            if($model->save()){
                return 0;
            }
        }
    }

}