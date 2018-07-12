<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/18
 * Time: 下午5:51
 */

namespace app\modules\admin\controllers;

use app\modules\admin\models\LoginForm;
use yii\web\Controller;
use Yii;

class LoginController extends Controller
{

    public function actionIndex()
    {
        $admin = Yii::$app->admin;
        if (!$admin->isGuest) {
            return $this->redirect(['default/index']);
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect($admin->returnUrl);
        } else {
            return $this->render('index', [
                'model' => $model,
            ]);
        }
    }

}