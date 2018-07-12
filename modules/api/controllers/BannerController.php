<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/10/14
 * Time: 11:13
 */
namespace app\modules\api\controllers;

use app\models\Banner;
use yii;

class BannerController extends BaseController
{
    public function actionBannerList()
    {
        $request = Yii::$app->request;
        if($request->isGet){
            $type = $request->get('type', 0);
            $source = $request->get('source', 0);
            $num = $request->get('num');
            $arr = Banner::bannerList($source, $type, $num);
            foreach($arr as $key => $val){
                $arr[$key]['name'] = $val['name'];
                $arr[$key]['endtime'] = date('Y-m-d H:i:s', $val['endtime']);
                $arr[$key]['height'] = $val['height'];
                $arr[$key]['picture'] = $val['picture'];
                $arr[$key]['type'] = $val['type'];
                $arr[$key]['src'] = $val['link'];
                $arr[$key]['width'] = $val['width'];
                unset($arr[$key]['status']);
            }

            return $arr;
        }
    }
}