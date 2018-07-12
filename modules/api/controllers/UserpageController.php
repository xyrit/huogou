<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/30
 * Time: 上午11:40
 */
namespace app\modules\api\controllers;

use app\services\User;

class UserpageController extends BaseController
{

    public function actionGoodsbuylist()
    {
        $request = \Yii::$app->request;
        $home_id = $request->get('home_id');
        $page = $request->get('page');
        $perpage = $request->get('perpage');
        return User::buyList($this->userId, $home_id, $page, $perpage);
    }

    public function actionProductlist()
    {
        $request = \Yii::$app->request;
        $home_id = $request->get('home_id');
        $page = $request->get('page');
        $perpage = $request->get('perpage');
        return User::productList($this->userId, $home_id, $page, $perpage);
    }

    public function actionSharelist()
    {
        $request = \Yii::$app->request;
        $home_id = $request->get('home_id');
        $page = $request->get('page');
        $perpage = $request->get('perpage');

        return User::shareList($this->userId, $home_id, $page, $perpage);
    }

    public function actionPkList()
    {
        $request = \Yii::$app->request;
        $home_id = $request->get('home_id');
        $page = $request->get('page');
        $perpage = $request->get('perpage');

        return User::pkList($this->userId, $home_id, $page, $perpage);
    }


}