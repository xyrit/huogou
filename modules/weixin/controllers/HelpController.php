<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/4
 * Time: 下午5:17
 */
namespace app\modules\weixin\controllers;

class HelpController extends BaseController
{
    public function actionAbout()
    {
        return $this->render('about', []);
    }

    public function actionProblem()
    {
        return $this->render('problem', []);
    }
    
     public function actionSuggestion($tpl="suggestion")
    {
        return $this->render($tpl, []);
    }
}