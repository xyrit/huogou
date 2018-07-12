<?php

namespace app\controllers;

use app\helpers\Brower;
use Yii;
use yii\web\Cookie;

class SiteController extends BaseController
{

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $from = Brower::whereFrom();
        if ($from==2) {
            return $this->render('dd_index', []);
        }
        if ($dev = $request->get('dev')) {
            $response = Yii::$app->response;
            $response->cookies->add(new Cookie([
                'name'=>'pcview',
                'value'=>'1',
                'domain'=>'.'.DOMAIN,
                'expire'=>time()+3600,
            ]));
            return $this->render('index', []);
        }

        $this->redirectDeviceUrl(['/weixin'], ['/mobile']);
        return $this->render('index', []);
    }

}
