<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/25
 * Time: 下午8:15
 */
namespace app\modules\api\controllers;

use app\modules\help\models\Suggestion;

class SuggestionController extends BaseController
{
    public function actionSuggestion()
    {
        $request = \Yii::$app->request;
        if($request->isGet){
            $post = $request->get();
            $phonePattern = "/1[3458]{1}\d{9}$/";
            $emailPattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            $QQPattern = "/^\d{5,11}$/";
            if(!preg_match($phonePattern,$post['contact']) && !preg_match( $emailPattern, $post['contact'] ) && !preg_match( $QQPattern, $post['contact'] )){
                return ['code'=>102, 'msg'=>'联系方式格式错误'];
            }

            if($post['content'] == '') return ['code'=>103, 'msg'=>'内容不能为空'];
            $model = new Suggestion();
            $model->type = $post['type'];
            $model->content = $post['content'];
            $model->created_at = time();
            $model->email = $post['contact'];
            if($model->save()){
                return ['code'=>100, 'msg'=>'提交成功'];
            }else{
                return ['code'=>101, 'msg'=>'提交失败，请重试',"err"=>$model->getErrors()];
            }
        }
    }
}