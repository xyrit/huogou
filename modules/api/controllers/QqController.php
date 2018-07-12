<?php
/**
 * Created by PhpStorm.
 * User: chenyi
 * Date: 2015/12/29
 * Time: 10:48
 */
namespace app\modules\api\controllers;

use Yii;
use app\models\Qq;

class QqController extends BaseController
{
    //qq列表
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $type = $request->get('type');
        $list = Qq::find()->where(['default'=>1])->orderBy('id desc')->one();

        $arr = [];
        if($type == 1){
            $arr['qq'] = isset($list) ? $list['num'] : '0';
            $arr['key'] = isset($list) ? $list['android_key'] : '0';
            return $arr;
        }elseif($type == 2){
            $arr['qq'] = isset($list) ? $list['num'] : '0';
            $arr['key'] = isset($list) ? $list['ios_key'] : '0';
            $arr['uin'] = isset($list) ? $list['uin'] : '0';
            return $arr;
        }
    }
}