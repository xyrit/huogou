<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/9
 * Time: 下午3:11
 */
namespace app\modules\admin\controllers;

use app\helpers\Message;
use app\models\NoticeTemplate;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

class NoticeController extends BaseController
{

    public function actionIndex()
    {
        $query = NoticeTemplate::find();
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount'=>$totalCount, 'defaultPageSize'=>20]);
        $list = $query->orderBy('id asc')->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        foreach ($list as &$one) {
            $ways = explode(',', $one['notice_way']);
            $one['ways'] = '';
            if (in_array(NoticeTemplate::WAY_SMS, $ways)) {
                $one['ways'] .= '短信&';
            }
            if (in_array(NoticeTemplate::WAY_EMAIL, $ways)) {
                $one['ways'] .= '邮箱&';
            }
            if (in_array(NoticeTemplate::WAY_SYSMSG, $ways)) {
                $one['ways'] .= '站内信&';
            }
            if (in_array(NoticeTemplate::WAY_WECHAT, $ways)) {
                $one['ways'] .= '微信&';
            }
            if (in_array(NoticeTemplate::WAY_APP, $ways)) {
                $one['ways'] .= 'APP&';
            }
            $one['ways'] = rtrim($one['ways'], '&');
        }
        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $model = NoticeTemplate::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('页面找不到');
        }
        if ($request->isPost) {
            if ($model->load($request->post())) {
                $model->notice_way = implode(',', $model->notice_way);
                $model->op_user = \Yii::$app->admin->id;
                $model->updated_at = time();
                if ($model->validate()) {
                    $model->save(false);
                    \Yii::$app->session->setFlash('success', '保存成功');
                    return $this->refresh();
                }
            }
        }
        $model->notice_way = explode(',', $model->notice_way);
        return $this->render('edit', [
            'model' => $model
        ]);
    }

    public function actionParams()
    {
        $params = Message::$replace;
        return $this->render('params', [
            'params' => $params,
        ]);
    }


}