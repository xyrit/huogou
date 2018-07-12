<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/18
 * Time: 14:54
 */

namespace app\modules\admin\controllers;


use app\models\Brand;
use app\models\TypeBrand;
use Yii;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\admin\models\BackstageLog;

class BrandController extends BaseController
{
    //品牌列表
    public function actionIndex()
    {
        $query = Brand::find();
        $request = Yii::$app->request;
        $keywords = $request->post('keywords');

        if ($keywords) {
            $query->where('name like :keywords', [':keywords' => '%' . $keywords . '%']);
            $query->orWhere('alias like :keywords', [':keywords' => '%' . $keywords . '%']);
        }

        $keyword = isset($keywords) ? $keywords : '';

        $pages = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $brands = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('index', [
            'brands' => $brands,
            'pages' => $pages,
            'keywords' => $keyword
        ]);
    }

    // 新增品牌
    public function actionAdd()
    {
        $model = new Brand();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->create_name = Yii::$app->admin->identity->username;
            $model->created_at = time();
            $model->save();
            $name = Yii::$app->request->post('Brand');
            BackstageLog::addLog(\Yii::$app->admin->id, 4, '新增品牌'.$name['name']);
            return $this->redirect(['/admin/brand']);
        }

        return $this->render('add', ['model' => $model]);
    }

    // 编辑品牌
    public function actionEdit($id)
    {
        $brand = Brand::findOne($id);

        if (!$brand) {
            throw new NotFoundHttpException("Not Found");
        }

        if ($brand->load(Yii::$app->request->post()) && $brand->validate()) {
            $brand->save();
            BackstageLog::addLog(\Yii::$app->admin->id, 4, '修改品牌'.$brand['name']);
            return $this->redirect(['/admin/brand']);
        }

        return $this->render('edit', ['brand' => $brand]);
    }

    // 删除品牌
    public function actionDel()
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        if ($request->isAjax) {
            if ($id = $request->post('id')) {
                $model = Brand::findOne($id);
                BackstageLog::addLog(\Yii::$app->admin->id, 4, '删除品牌'.$model['name']);
                if (Brand::deleteAll(['id' => $id])) {
                    return ['errno' => 0, 'errmsg' => ''];
                }
            }
        }

        return ['errno' => 1, 'errmsg' => '异常'];
    }
}