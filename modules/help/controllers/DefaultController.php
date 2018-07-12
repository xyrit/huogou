<?php

namespace app\modules\help\controllers;

use app\controllers\BaseController;
use yii\web\Controller;
use app\modules\help\models\SuggestionForm;
use app\models\Banner;

class DefaultController extends BaseController
{
    public function actionSuggestion()
    {
        $formModel = new SuggestionForm();

        $request = \Yii::$app->request;
        if($request->isPost){
            if ($formModel->load( $request->post()) && $formModel->validate()) {
                $conn = \Yii::$app->db;
                $post = $request->post('SuggestionForm');

                $result = $conn->createCommand()->insert('suggestions', [
                    'type' => $post['type'],
                    'phone' => $post['phone'],
                    'email' => $post['email'],
                    'content' => $post['content'],
                    'created_at' => time(),
                ])->execute();

                if($result){
                    return $this->redirect('suggestion.html?status=1');
                }

            }
        }

        return $this->render('suggestion', [
            'model' => $formModel,
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionQuestionDetail()
    {
        return $this->render('questionDetail');
    }

    public function actionAgreement()
    {
        return $this->render('agreement');
    }

    public function actionGenuinetwo()
    {
        return $this->render('genuinetwo');
    }

    public function actionPrivacy()
    {
        return $this->render('privacy');
    }

    public function actionGenuine()
    {
        return $this->render('genuine');
    }

    public function actionSecurepayment()
    {
        return $this->render('securepayment');
    }

    public function actionShip()
    {
        return $this->render('ship');
    }

    public function actionDeliveryFees()
    {
        return $this->render('deliveryFees');
    }

    public function actionProdCheck()
    {
        return $this->render('prodCheck');
    }

    public function actionShiptwo()
    {
        return $this->render('shiptwo');
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionContactus()
    {
        return $this->render('contactus');
    }

    public function actionUnderstand()
    {
        return $this->render('understand');
    }


    public function actionJobs()
    {
        return $this->render('jobs');
    }

    public function actionUserExperience()
    {
        return $this->render('userExperience');
    }

    public function actionSincerity()
    {
        return $this->render('sincerity');
    }

    public function actionQqgroup()
    {
        return $this->render('qqgroup');
    }


    public function actionLink()
    {
        $conn = \Yii::$app->db;
        $sql = "select name,link from friend_link order by list_order desc";
        $command = $conn->createCommand($sql);
        $find = $command->queryAll();

        return $this->render('link',[
            'list' => $find,
        ]);
    }

    public function actionWechat()
    {
        return $this->render('wechat');
    }

    public function actionApp()
    {
        return $this->render('APP');
    }
}
