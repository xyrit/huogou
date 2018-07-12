<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/18
 * Time: 下午12:51
 */

namespace app\controllers;

use app\helpers\Brower;
use Yii;

class ListController extends BaseController
{

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        if (!$request->cookies->getValue('pcview')) {
            if (Brower::isMcroMessager()) {
                return $this->redirect(['/weixin/list']);
            } elseif(Brower::isMobile()) {
                return $this->redirect(['/mobile/list']);
            }
        }
        return $this->render('index');
    }
}