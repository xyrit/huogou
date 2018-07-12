<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/28
 * Time: 14:24
 */
namespace app\modules\admin\controllers;


use app\modules\help\models\Suggestion;
use yii\data\Pagination;
use Yii;

class SuggestionController extends BaseController
{

    public function actionIndex()
    {
        $query = Suggestion::find();
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>10 ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();


        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    public function actionDel()
    {
        $request = Yii::$app->request;

        if($request->isGet){
            $id = $request->get('id');
            $model = Suggestion::findOne($id);
            $delete = $model->delete();
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($delete) {
                return [
                    'error' => 0,
                    'message' => '删除成功'
                ];
            }
            return [
                'error' => 1,
                'message' => '删除失败'
            ];
        }
    }
}