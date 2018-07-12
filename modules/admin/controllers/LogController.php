<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/9/18
 * Time: 14:54
 */

namespace app\modules\admin\controllers;

use app\helpers\Ip;
use app\models\LoginLog;
use app\models\NoticeMessage;
use app\models\User;
use app\modules\admin\models\BackstageLog;
use yii\data\Pagination;
use app\modules\admin\models\Admin;
use yii\helpers\StringHelper;

class LogController extends BaseController
{
    public function actionLoginLog()
    {
        $request = \Yii::$app->request;
        $where['startTime'] = $request->get('startTime', '');
        $where['endTime'] = $request->get('endTime', '');
        $where['content'] = $request->get('content', '');
        $where['type'] = $request->get('type', 'all');
        $page = $request->get('page', 1);
        $list = LoginLog::fetchAllRecords($where, $page);
        $return = [];
        foreach($list['list'] as $key => $val){
            $return[$key]['user_id'] = User::findOne($val['user_id']);
            $return[$key]['ip'] = long2ip($val['ip']);
            $return[$key]['type'] = $val['type'];
            $return[$key]['id'] = $val['id'];
            $return[$key]['created_at'] = $val['created_at'];
            $return[$key]['action'] = $val['action'];
            $return[$key]['city'] = Ip::getAddressByIp(long2ip($val['ip']));
        }

        return $this->render('login-log',[
            'list' => $return,
            'condition' => $where,
            'pagination' => $list['pagination'],
        ]);
    }

    public function actionBackstageLog()
    {
        $request = \Yii::$app->request;
        $where['startTime'] = $request->get('startTime', '');
        $where['endTime'] = $request->get('endTime', '');
        $where['content'] = $request->get('content', '');
        $where['type'] = $request->get('type', 'all');
        $page = $request->get('page', 1);
        $list = BackstageLog::fetchAllRecords($where, $page);
        foreach($list['list'] as $key => $val){
            $list['list'][$key]['admin_id'] = Admin::findOne($val['admin_id']);
        }

        return $this->render('backstage-log',[
            'list' => $list['list'],
            'condition' => $where,
            'pagination' => $list['pagination'],
        ]);
    }

    //后台操作日志
    public function actionMessageLog()
    {
        $query = NoticeMessage::find();
        $condition = [];
        $request = \Yii::$app->request;
        if($request->isGet){
            $get = $request->get();
            if(!empty($get)){
                if((isset($get['startTime']) && isset($get['endTime'])) && ($get['startTime'] && $get['endTime'])){
                    $condition['start'] = $get['startTime'];
                    $condition['end'] = $get['endTime'];
                    $query->andWhere(['>=', 'created_at', strtotime($get['startTime'])]) ;
                    $query->andWhere(['<', 'created_at', strtotime($get['endTime'])]);
                }
                if(isset($get['content']) && $get['content']){
                    $condition['content'] = $get['content'];
                    $query->andWhere(['user_id'=>$get['content']]);
                }
                if(isset($get['type']) && $get['type'] != 'all'){
                    $condition['type'] = $get['type'];
                    $query->andWhere(['mode'=>$get['type']]);
                }
            }
        }
        $countQuery = clone $query;

        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => 25]);
        $list = $query->offset($pagination->offset)->orderBy('id desc')->limit($pagination->limit)->asArray()->all();

        foreach($list as $key => $val){
            $userinfo = User::find()->where(['or', 'email="'.$val['user_id'].'"', 'phone="'.$val['user_id'].'"', 'id="'.$val['user_id'].'"'])->one();
            $list[$key]['user_info'] = $userinfo;
            $list[$key]['ip'] = long2ip($val['ip']);
        }

        return $this->render('message-log', [
            'list' => $list,
            'pagination' => $pagination,
            'condition' => $condition,
        ]);
    }

}