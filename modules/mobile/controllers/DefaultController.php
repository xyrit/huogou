<?php

namespace app\modules\mobile\controllers;
use app\helpers\Cs;

class DefaultController extends BaseController
{
    public function actionIndex()
    {
        $cnzz=$this->_cnzzTrackPageView('1259542566');
        return $this->render('index', [
          "cnzz"=>$cnzz,
        ]);
    }


    function _cnzzTrackPageView($siteId) {
        $cs = new Cs($siteId);
        return $cs->trackPageView();
    }



}
