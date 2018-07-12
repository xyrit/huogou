<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/21
 * Time: 上午11:01
 */

namespace app\modules\admin\controllers;

use app\models\Brand;
use app\models\ProductCategory;
use app\models\ProductType;
use app\modules\admin\models\ProductTypeForm;
use app\models\TypeBrand;
use yii\data\Pagination;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class ProductTypeController extends BaseController
{

    public function actionIndex()
    {
        $query = ProductType::find();

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $types = $query->offset($pagination->offset)->limit($pagination->limit)->all();

        return $this->render('index', [
            'types' => $types,
            'pagination' => $pagination,
        ]);
    }

    public function actionAdd()
    {
        $formModel = new ProductTypeForm();
        $brandModel = Brand::find()->all();
        $brands = ArrayHelper::map($brandModel, 'id', 'name');
        $request = Yii::$app->request;
        if ($request->isPost) {
            if ($formModel->load($request->post()) && $formModel->validate()) {
                $productType = new ProductType();
                $productType->name = $formModel->name;
                $productType->alias = $formModel->alias;
                $productType->save();
                foreach ($formModel->brands as $brand) {
                    $typeBrand = new TypeBrand();
                    $typeBrand->type_id = $productType->id;
                    $typeBrand->brand_id = $brand;
                    $typeBrand->brand_order = 0;
                    $typeBrand->save();
                }
                return $this->redirect(['/admin/product-type']);
            }
        }
        return $this->render('add', [
            'formModel' => $formModel,
            'brands' => $brands,
        ]);
    }

    public function actionEdit()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $productType = ProductType::findOne($id);
        if (!$productType) {
            throw new NotFoundHttpException('不存在的商品类型ID');
        }
        $typeBrand = TypeBrand::findAll(['type_id' => $id]);
        $brandIds = ArrayHelper::getColumn($typeBrand, 'brand_id');
        $formModel = new ProductTypeForm();
        $allBrandModel = Brand::find()->all();
        $brands = ArrayHelper::map($allBrandModel, 'id', 'name');
        $checkedBrands = [];
        foreach ($brands as $brandId => $brandName) {
            if (in_array($brandId, $brandIds)) {
                $checkedBrands[] = $brandId;
            }
        }
        $formModel->name = $productType->name;
        $formModel->alias = $productType->alias;
        $formModel->brands = $checkedBrands;
        if ($request->isPost) {
            if ($formModel->load($request->post()) && $formModel->validate()) {
                $productType->name = $formModel->name;
                $productType->alias = $formModel->alias;
                $save = $productType->save();
                if ($save) {
                    TypeBrand::deleteAll(['type_id' => $productType->id]);
                }
                foreach ($formModel->brands as $brand) {
                    $typeBrand = new TypeBrand();
                    $typeBrand->type_id = $productType->id;
                    $typeBrand->brand_id = $brand;
                    $typeBrand->brand_order = 0;
                    $typeBrand->save();
                }
                return $this->redirect(['/admin/product-type']);
            }
        }
        return $this->render('edit', [
            'formModel' => $formModel,
            'brands' => $brands,
        ]);
    }

    public function actionDel()
    {

        $request = \Yii::$app->request;
        $response = \Yii::$app->response;
        if ($request->isAjax) {
            $id = $request->post('id', 0);
            $productCategory = ProductCategory::findOne(['type_id' => $id]);
            $response->format = \yii\web\Response::FORMAT_JSON;
            if ($productCategory) {
                return [
                    'error' => 1,
                    'message' => '该类型已被商品分类关联，不能删除'
                ];
            } else {
                $delete = ProductType::deleteAll(['id' => $id]);
                TypeBrand::deleteAll(['type_id'=>$id]);
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


}