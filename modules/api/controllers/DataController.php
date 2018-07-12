<?php

/**
 * User: hechen
 * Date: 15/9/28
 * Time: 下午3:12
 */

namespace app\modules\api\controllers;

use yii;
use yii\web\Controller;
use app\models\PeriodBuylistDistribution;

/**
*  web页面返回jsonp数据
*/
class DataController extends BaseController
{
	
	public function actionDate(){
		return array("time"=>Date('Y/m/d H:i:s',time()));
	}

	public function actionTotalBuyCount(){
		$total = PeriodBuylistDistribution::findTotalBuyCount();
		$i = rand(1,1000);

		return array('count'=>$total);
	}

	public function actionHotSearch(){
		return array('keywords'=>array('1'=>'iPhone','2'=>'小米','3'=>'单反'));
	}

	public function actionVirtualType($source=0,$os="android"){
        if($source)
            $os = [3=>"ios",4=>"android"][$source];
        $rt = [];
        $list = \app\models\AppConfig::find()->where(["type"=>"virtual","status"=>1,"system"=>$os])->orderBy("sort desc")->all();
        foreach($list as $model)
        {
            $rt[]= array(
				       "name"=>$model->virtual_name,
				       "type"=>$model->virtual_type,
				       "icon"=>"http://skin.".DOMAIN.$model->virtual_icon,
						);
		}
        
        return array("list"=>$rt);
	}
}