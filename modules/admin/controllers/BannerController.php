<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/10/13
 * Time: 17:26
 */

namespace app\modules\admin\controllers;

use app\models\Banner;
use app\modules\image\models\UploadForm;
use yii\web\UploadedFile;
use yii\data\Pagination;
use app\models\Image;
use yii\web\NotFoundHttpException;
use app\models\FriendLink;

class BannerController extends BaseController
{
    public function actionIndex()
    {
        $query = Banner::find();
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>10 ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();

        foreach($list as $key => $val){
            $list[$key]['picture'] = Image::getBannerInfoUrl($val['picture'], 'small');
        }

        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }


    public function actionAdd()
    {
        $model = new Banner();
        $request = \Yii::$app->request;
        if($request->isPost){
            $post = $request->post();
            if(!empty($_FILES['picture']['name'])){
                $pic['width'] = $post['Banner']['width'];
                $pic['height'] = $post['Banner']['height'];
                $imagModel = new UploadForm();
                $imagModel->imageFile = UploadedFile::getInstanceByName('picture');
                $uploadData = $imagModel->uploadBannerInfo($pic);
            }else{
                $uploadData['basename'] = '';
            }

            if ($model->load( $post) && $model->validate()) {
                $model->picture = $uploadData['basename'];
                if($post['Banner']['starttime'] == '') $start = 0;
                else $start = strtotime($post['Banner']['starttime']);
                if($post['Banner']['endtime'] == '') $end = 0;
                else $end = strtotime($post['Banner']['endtime']);
                $model->starttime = $start;
                $model->endtime = $end;
                $model->source = $post['Banner']['source'];
                $model->created_at = time();

                if($model->save()){
                    $this->redirect('index');
                }
            }
        }

        return $this->render('add', [
            'model' => $model,
        ]);
    }

    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $model = Banner::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException("页面未找到");
        }

        if($request->isPost){
            $post = $request->post();
            if(!empty($_FILES['picture']['name'])){
                $pic['width'] = $post['Banner']['width'];
                $pic['height'] = $post['Banner']['height'];
                $imagModel = new UploadForm();
                $imagModel->imageFile = UploadedFile::getInstanceByName('picture');
                $uploadData = $imagModel->uploadBannerInfo($pic);
            }else{
                $uploadData['basename'] = $model->picture;
            }

            if($model->load( $post) && $model->validate()){
                $model->picture = $uploadData['basename'];
                $model->starttime = strtotime($post['Banner']['starttime']);
                $model->endtime = strtotime($post['Banner']['endtime']);
                $model->source = $post['Banner']['source'];
                $model->updated_at = time();
                $model->save();

                return $this->redirect('index');
            }
        }

        $image = Image::getBannerInfoUrl($model['picture'], 'small');

        return $this->render('edit', [
            'model' => $model,
            'image' => $image,
        ]);
    }

    public function actionDel()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $id = $request->get('id');
            $model = Banner::findOne($id);
            if(!$model){
                throw new NotFoundHttpException('页面未找到');
            }

            if($model->delete()){
                return 0;
            }else{
                return 1;
            }
        }
    }

    //友情链接
    public function actionFriendLink()
    {
        $query = FriendLink::find();
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>10 ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();

        foreach($list as $key => $val){
            $list[$key]['picture'] = Image::getBannerInfoUrl($val['picture'], 'small');
        }

        return $this->render('friend-link', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    //新增友情链接
    public function actionAddLink()
    {
        $model = new FriendLink();
        $request = \Yii::$app->request;
        if($request->isPost){
            $post = $request->post();
            if(!empty($_FILES['picture']['name'])){
                $pic['width'] = 400;
                $pic['height'] = 200;
                $imagModel = new UploadForm();
                $imagModel->imageFile = UploadedFile::getInstanceByName('picture');
                $uploadData = $imagModel->uploadBannerInfo($pic);
            }else{
                $uploadData['basename'] = '';
            }

            if ($model->load( $post) && $model->validate()) {
                $model->picture = $uploadData['basename'];
                $model->username = \Yii::$app->admin->identity->username;
                $model->created_at = time();
                $model->updated_at = time();

                if($model->save()){
                    $this->redirect('friend-link');
                }
            }
        }

        return $this->render('add-link', [
            'model' => $model,
        ]);
    }

    //修改友情链接
    public function actionEditLink()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $model = FriendLink::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException("页面未找到");
        }

        if ($request->isPost) {
            $post = $request->post();
            if (!empty($_FILES['picture']['name'])) {
                $pic['width'] = 400;
                $pic['height'] = 200;
                $imagModel = new UploadForm();
                $imagModel->imageFile = UploadedFile::getInstanceByName('picture');
                $uploadData = $imagModel->uploadBannerInfo($pic);
            } else {
                $uploadData['basename'] = $model->picture;
            }

            if ($model->load($post) && $model->validate()) {
                $model->picture = $uploadData['basename'];
                $model->username = \Yii::$app->admin->identity->username;
                $model->updated_at = time();
                $model->save();

                return $this->redirect('friend-link');
            }
        }

        $image = Image::getBannerInfoUrl($model['picture'], 'small');

        return $this->render('edit-link', [
            'model' => $model,
            'image' => $image,
        ]);
    }

    //删除
    public function actionDelLink()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $id = $request->get('id');
            $model = FriendLink::findOne($id);
            if(!$model){
                throw new NotFoundHttpException('页面未找到');
            }

            if($model->delete()){
                return 0;
            }else{
                return 1;
            }
        }
    }
}