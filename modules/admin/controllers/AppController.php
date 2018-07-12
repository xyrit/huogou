<?php
/**
 * @category  huogou.com
 * @name  AppController
 * @version 1.0
 * @date 2015-12-29
 * @author  keli <liwanglai@gmail.com>
 *
 */
namespace app\modules\admin\controllers;

use app\models\AppConfigLog;
use Yii;
use app\models\AppConfig;
use app\models\AppPush;
use yii\helpers\Json;
use app\models\UserAppInfo;
use app\modules\image\models\UploadForm;
use yii\web\UploadedFile;

class AppController extends AdminController
{

    private function commonAction($action)
    {
        $id = ['type' => $action];
        if ($os = Yii::$app->request->get("os")) $id['system'] = $os;
        $model = AppConfig::G($id);
        $model->scenario = $action;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash("success", "保存成功");
            return $this->refresh();
        }

        $tpl = Yii::$app->request->get("tpl", $action);
        return $this->render($tpl, [
            "model" => $model,
        ]);
    }

    function actionLogin()
    {
        return $this->commonAction("login");
    }

    function actionSdk()
    {
        return $this->commonAction("sdk");
    }

    function actionShare()
    {
        return $this->commonAction("share");
    }

    function actionVirtual()
    {
        return $this->commonAction("virtual");
    }

    function actionUpgrade()
    {
        return $this->commonAction("upgrade");
    }

    function actionH5pay()
    {
        return $this->commonAction("h5pay");
    }

    function actionImage()
    {
        if (Yii::$app->request->isPost and $_FILES) {
            $rt = [];
            $name = array_shift(array_keys($_FILES));

            //// 图片上传生成图片链接 赋值给
            $model = new UploadForm();
            $model->imageFile = UploadedFile::getInstanceByName($name);
            if ($uploadData = $model->uploadShareInfo()) {
                // file is uploaded successfully
                $rt[] = $uploadData;
            } elseif (!$model->imageFile and isset($_FILES[$name])) {
                foreach ($_FILES[$name]["name"] as $i => $file) {
                    $model = new UploadForm();
                    $model->imageFile = UploadedFile::getInstanceByName($name);
                    if ($uploadData = $model->uploadShareInfo()) {      ///这里选择了分享图片方式,没有缩略图,可以选择其他方式参考model类
                        // file is uploaded successfully
                        $rt[] = $uploadData;
                    }
                }
            }
            //图片路径补全
            if ($rt) {
                foreach ($rt as $i => &$row) {
                    if (isset($row["basename"]))
                        $row['src'] = "http://img." . DOMAIN . "/userpost/share/" . $row["basename"];
                }
                if (count($_FILES) == 1) $rt = $rt[0];
            }
            if (isset($_GET["callback"]))
                return "<script >parent.{$_GET["callback"]}('" . Json::encode($rt) . "')</script>";
            return json_decode($rt, JSON_UNESCAPED_UNICODE);
        }
        return $this->commonAction("image");
    }

    //推送更新消息到ios
    function actionPushUpmsg($id)
    {
        $model = AppConfig::findOne($id);
        $this->push(3, $model->up_code, $model->up_des, "{type:'app',id:1}", []);
        Yii::$app->session->setFlash("success", "保存成功");
        $this->goBack();
    }

    // 开机图片列表
    function actionImageList($system = '', $status = '')
    {
        $where = ["type" => "image"];
        if ($status !== '') $where["status"] = $status;
        if ($system !== '') $where["system"] = $system;
        $query = AppConfig::find()->where($where)->orderBy("sort asc");
        $dataProvider = new \yii\data\ActiveDataProvider(['query' => $query,]);

        return $this->render("image_list", [
            "dataProvider" => $dataProvider,
        ]);
    }

    // 支付方式列表
    function actionSdkList()
    {
        $query = AppConfig::find()->where(["type" => "sdk"])->orderBy("system asc, sort desc");
        $dataProvider = new \yii\data\ActiveDataProvider(['query' => $query,]);
        return $this->render("sdk_list", [
            "dataProvider" => $dataProvider,
        ]);
    }

    //分享设置列表
    function actionShareList()
    {
        $query = AppConfig::find()->where(["type" => "share"])->orderBy("system asc, sort desc");
        $dataProvider = new \yii\data\ActiveDataProvider(['query' => $query,]);
        return $this->render("share_list", [
            "dataProvider" => $dataProvider,
        ]);
    }

    //虚拟运营商列表
    function actionVirtualList($os = '')
    {
        $query = AppConfig::find()->where(["type" => "virtual"])->orderBy("system asc, sort desc");
        if ($os) $query->andFilterWhere(["like", "content", "$os"]);

        $dataProvider = new \yii\data\ActiveDataProvider(['query' => $query,]);
        return $this->render("virtual_list", [
            "dataProvider" => $dataProvider,
        ]);
    }

    //信息推送设置
    function actionPush($tpl = "msg")
    {
        $model = AppPush::G();
        if (Yii::$app->request->isPost and $model->load(Yii::$app->request->post())) {
            $model->msg_do = Json::encode(array("type" => $model->msg_do, "id" => $model->msg_id));
        } else {
            $model->msg_do = "";
            $model->msg_log = 1;
        }
        if (Yii::$app->request->isPost and $model->save()) {
            //立即推送信息
            if ($model->status) {
                foreach ($model->os as $source) {
                    $msg_type = (Array)($model->msg_type);
                    $this->push($source, $model->msg_name, $model->msg_des, $model->msg_do, $msg_type);
                }
            }
            //写入log
            if ($model->msg_log) {
                $model_log = new AppConfigLog();
                $model_log->setAttributes($model->attributes, false);
                $model_log->id = '';
                $model_log->type = "push_log";
                $model_log->save();

            }
            Yii::$app->session->setFlash("success", "保存成功");
            //return $this->refresh();
        }
        return $this->render($tpl, [
            "model" => $model,
        ]);
    }

    //信息推送测试 用于推送历史信息
    function actionPushTest($id = "push")
    {
        $model = AppConfigLog::G($id);
        foreach ($model->os as $source) {
            $req = $this->push($source, $model->msg_name, $model->msg_des, $model->msg_do, $model->msg_type);
            echo '<pre>';
            print_r($req);
            echo '<br>';
        }
    }

    //配置修改历史
    function actionLog($type)
    {
        $query = \app\models\AppConfigLog::find()->where(["type" => $type])->orderBy("id desc");
        $dataProvider = new \yii\data\ActiveDataProvider(['query' => $query,]);
        return $this->render("log", [
            "dataProvider" => $dataProvider,
        ]);
    }

    public function actionCreate($tpl)
    {
        $model = new AppConfig;
        $model->type = $model->scenario = $tpl;
        $model->status = 1;
        if (Yii::$app->request->isPost and $model->load(Yii::$app->request->post()) && $model->save()) {
            $model->sort = $model->id;
            Yii::$app->session->setFlash("success", "新建成功");
            return $this->goBack();
        }
        return $this->render($tpl, [
            "model" => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = $model->type;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash("success", "保存成功");
            return $this->goBack();
        }

        return $this->render($model->type, [
            "model" => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->goBack();
    }

    public function actionSort($id, $order = "desc")
    {
        $model = $this->findModel($id);
        $list = AppConfig::find()->where(["type" => $model->type])->orderBy("sort $order")->all();
        foreach ($list as $i => $m) {
            if ($id == $m->id) break;
        }
        if (isset($list[$i - 1])) {
            $m = $list[$i - 1];
            $sort = $model->sort ? $model->sort : $model->id;
            $model->sort = $m->sort ? $m->sort : $m->id;
            $model->save(false);
            $m->sort = $sort;
            $m->save(false);
        }
        return $this->goBack();
    }

    // 删除配置修改历史记录
    public function actionDeleteLog($id)
    {
        $model = \app\models\AppConfigLog::findOne($id);
        if ($model) $model->delete();
        return $this->goBack();
    }


    protected function findModel($id)
    {
        if (($model = AppConfig::findOne($id)) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function push($source, $title, $content, $customInfo, $msg_type)
    {
        $getui = Yii::$app->getui;
        if ($source == 3) {
            //ios发送
            $req = $getui->setTemplate('Transmission', [
                'transmissionType' => '2',//透传消息类型
                'transmissionContent' => $customInfo,//透传内容
            ])->setAPNPayload([
                'body' => $content,
                'title' => $title,
                'badge' => 0,
                'customMsg' => [
                    'url' => $customInfo,
                ],
            ])->pushApp(['IOS']);
        } elseif ($source == 4) {
            //Android发送
            $req = $getui->setTemplate('Notification', [
                'transmissionType' => '2',//透传消息类型
                'transmissionContent' => $customInfo,//透传内容
                'title' => $title,
                'text' => $content,
                'badge' => 0,
                'logo' => 'logo.png',
                'isRing' => in_array('isRing', $msg_type),
                'isVibrate' => in_array('isVibrate', $msg_type),
                'isClearable' => in_array('isClearable', $msg_type),
            ])->pushApp(['ANDROID']);
        }
        return $req;
    }
}
