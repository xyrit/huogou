<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/20
 * Time: 下午2:04
 */
namespace app\modules\admin\controllers;

use app\models\Product;
use app\modules\admin\models\Admin;
use app\modules\admin\models\OrderManageGroup;
use app\modules\admin\models\OrderManageGroupForm;
use app\modules\admin\models\OrderManageGroupUser;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class OrderManageGroupController extends BaseController
{

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $name = $request->get('name');
        $query = OrderManageGroup::find();
        if ($name) {
            $where = ['like','name', $name . '%'];
            $query->andWhere($where);
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount'=>$totalCount, 'defaultPageSize'=>10]);
        $groups = $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        foreach ($groups as &$group) {
            $adminUser = Admin::findOne($group['by_uid']);
            $productNum = Product::find()->where(['order_manage_gid'=>$group['id']])->count();
            $userNum = OrderManageGroupUser::find()->where(['group_id'=>$group['id']])->count();
            $group['username'] = $adminUser->username;
            $group['product_nums'] = $productNum;
            $group['user_nums'] = $userNum;
        }
        $tv = [];
        $tv['groups'] = $groups;
        $tv['pagination'] = $pagination;
        return $this->render('index', $tv);
    }

    public function actionAdd()
    {
        $model = new OrderManageGroupForm();
        $request = \Yii::$app->request;
        $admin = \Yii::$app->admin;
        if ($request->isPost) {
            if ($model->load($request->post()) && $model->validate()) {
                $manageGroup = new OrderManageGroup();
                $manageGroup->by_uid = $admin->id;
                $manageGroup->name = $model->name;
                $time = time();
                $manageGroup->updated_at = $time;
                $manageGroup->created_at = $time;
                if ($manageGroup->save()) {
                    $userIds = $model->userIds;
                    foreach ((array)$userIds as $uid) {
                        $groupUser = new OrderManageGroupUser();
                        $groupUser->user_id = $uid;
                        $groupUser->group_id = $manageGroup->id;
                        $groupUser->save();
                    }
                    return $this->redirect(['/admin/order-manage-group']);

                }
            }
        }

        $users = Admin::find()->all();
        $tv = [];
        $tv['users'] = $users;
        $tv['model'] = $model;
        return $this->render('add', $tv);
    }

    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $manageGroup = OrderManageGroup::findOne($id);
        if (!$manageGroup) {
            throw new NotFoundHttpException('未知的小组');
        }
        $model = new OrderManageGroupForm();

        if ($request->isPost) {
            if ($model->load($request->post()) && $model->validate()) {
                $manageGroup->name = $model->name;
                $time = time();
                $manageGroup->updated_at = $time;
                if ($manageGroup->save()) {
                    $userIds = $model->userIds;
                    OrderManageGroupUser::deleteAll(['group_id'=>$manageGroup->id]);
                    foreach ((array)$userIds as $uid) {
                        $groupUser = new OrderManageGroupUser();
                        $groupUser->user_id = $uid;
                        $groupUser->group_id = $manageGroup->id;
                        $groupUser->save();
                    }
                    return $this->redirect(['/admin/order-manage-group']);

                }
            }
        }
        $users = Admin::find()->indexBy('id')->all();
        $groupUserModel = OrderManageGroupUser::findAll(['group_id'=>$manageGroup->id]);
        $groupUserIds = ArrayHelper::getColumn($groupUserModel, 'user_id');
        $userIds = ArrayHelper::getColumn($users, 'id');
        $unSelectedUserIds = array_diff($userIds, $groupUserIds);

        $model->name = $manageGroup->name;
        $tv = [];
        $tv['users'] = $users;
        $tv['groupUserIds'] = $groupUserIds;
        $tv['unSelectedUserIds'] = $unSelectedUserIds;
        $tv['model'] = $model;
        return $this->render('edit', $tv);

    }

    public function actionDel()
    {
        $request = \Yii::$app->request;
        $id = $request->post('id');
        if (!$request->isAjax) {
            return;
        }
        $manageGroup = OrderManageGroup::findOne($id);
        if (!$manageGroup) {
            return Json::encode(['error'=>1, 'message'=>'未知的小组']);
        }
        $groupId = $manageGroup->id;
        $manageGroup->delete();
        OrderManageGroupUser::deleteAll(['group_id'=>$groupId]);
        return Json::encode(['error'=>0]);
    }

}