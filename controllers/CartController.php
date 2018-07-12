<?php
/**
 * User: hechen
 * Date: 15/9/30
 * Time: 下午3:33
 */

namespace app\controllers;

use Yii;

class CartController extends BaseController
{

    public function actionIndex()
    {

        $this->redirectDeviceUrl(['/weixin/cart'], ['/mobile/cart']);
        return $this->render('index');
    }

}