<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/19
 * Time: 下午5:55
 */
namespace app\modules\ddweixin\controllers;

use app\models\Image;
use app\services\User;
use yii\web\NotFoundHttpException;

class UserController extends BaseController
{
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $homeId = $request->get('id');
        $user = \app\models\User::find()->where(['home_id'=>$homeId])->one();
        if (!$user) {
            throw new NotFoundHttpException('用户存在');
        }
        $userBaseInfo = User::baseInfo($user->id);
        $userBaseInfo['avatarUrl'] = Image::getUserFaceUrl($userBaseInfo['avatar'], 80);
        return $this->render('index',[
            'userBaseInfo'=>$userBaseInfo,
        ]);
    }


}