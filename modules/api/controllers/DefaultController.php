<?php

namespace app\modules\api\controllers;


class DefaultController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
