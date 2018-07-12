<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/18
 * Time: 14:54
 */

namespace app\modules\admin\controllers;

use app\modules\admin\models\Role;
use app\modules\admin\models\Admin;
use yii;
use yii\web\NotFoundHttpException;

class EmployeeController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $conditon = $request->post('content');
        $list = Admin::getList($conditon);
        foreach($list['list'] as $key => $val){
            $role = Role::findOne($val['role']);
            $list['list'][$key]['role'] = $role['name'];
        }

        return $this->render('index', [
            'list' => $list
        ]);
    }

    public function actionAdd()
    {
        $request = \Yii::$app->request;

        $roles = Role::find()->asArray()->all();
        $model = new Admin();
        if($request->isPost){
            $post = $request->post();
            $model->created_at = time();
            $model->updated_at = time();
            if ($model->load( $post) && $model->validate()) {
                $model->password = Yii::$app->security->generatePasswordHash($post['Admin']['password']);
                $result = $model->save();
                if($result){
                    return $this->redirect('index');
                }
            }
        }

        return $this->render('add', [
            'roles' => $roles,
            'model' => $model,
        ]);
    }

    public function actionEdit()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Admin::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException("页面未找到");
        }

        if($request->isPost){
            $post = $request->post();
            $model = Admin::findOne($post['id']);
            if($post['Admin']['password'] != $model['password']){
                $post['Admin']['password'] = Yii::$app->security->generatePasswordHash($post['Admin']['password']);
            }
            if ($model->validate() && $model->load($post)) {
                $result = $model->save();
                if($result){
                    return $this->redirect('index');
                }
            }
        }

        $roles = Role::find()->asArray()->all();
        return $this->render('edit', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    public function actionDel()
    {
        $request = Yii::$app->request;
        if($request->isPost){
            $id = $request->post('id');
            $model = Admin::findOne($id);
            if($model){
                $del = $model->delete();
                if($del){
                    return 0;
                }else{
                    return 1;
                }
            }else{
                return 1;
            }
        }
    }

    public function actionChangeStatus()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $model = Admin::findOne($id);
        if(!$model){
            throw new NotFoundHttpException("页面未找到");
        }

        if($model['status'] == 0){
            $model->status = 1;
            $model->save();
        }elseif($model['status'] == 1){
            $model->status = 0;
            $model->save();
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}