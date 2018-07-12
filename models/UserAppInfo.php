<?php

namespace app\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "user_app_info".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $client_id
 * @property integer $status
 * @property integer $source
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserAppInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_app_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'created_at', 'updated_at'], 'required'],
            [['uid', 'status', 'source', 'created_at', 'updated_at'], 'integer'],
            [['client_id'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'client_id' => 'Client ID',
            'status' => 'Status',
            'source' => 'Source',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /** 更新用户app信息
     * @param $uid
     * @param $clientid
     * @param $source
     * @return bool
     */
    public static function updateAppInfo($uid,$clientid,$source,$status)
    {
        $model = static::find()->where(['uid'=>$uid])->one();
        $time = time();
        if ($model) {
            $oldClientId = $model->client_id;
            $oldStatus = $model->status;
            $model->client_id = $clientid;
            $model->source = $source;
            $model->status = $status;
            $model->updated_at = $time;
        } else {
            $model = new self();
            $model->uid = $uid;
            $model->client_id = $clientid;
            $model->source = $source;
            $model->status = $status;
            $model->created_at = $time;
            $model->updated_at = $time;
        }
        $save = $model->save();
        if (!empty($oldClientId) && $oldClientId!=$clientid && isset($oldStatus) && $oldStatus==1) {
            static::pushLogout($oldClientId,'您的账号已在另外一台手持设备登录！');
        }
    }

    /** 更改用户app信息状态
     * @param $uid
     * @param $status
     * @return int
     */
    public static function changeStatus($uid,$status)
    {
        return static::updateAll(['status'=>$status, 'updated_at'=>time()],['uid'=>$uid]);
    }

    public static function pushLogout($clientid,$msg)
    {
        if (!$clientid) {
            return;
        }
        $getui = \Yii::$app->getui;
        $customInfo = Json::encode(['type'=>'logout','msg'=>$msg]);
        $req = $getui->setTemplate('Transmission',[
            'transmissionType' => '2',//透传消息类型
            'transmissionContent' => $customInfo,//透传内容
        ])->pushOne($clientid);
    }
}
