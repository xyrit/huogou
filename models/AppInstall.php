<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "banners".
 *
 * @property string $id
 * @property string $name
 * @property string $starttime
 * @property string $endtime
 * @property string $picture
 * @property string $link
 * @property integer $type
 * @property string $width
 * @property string $height
 * @property integer $list_order
 * @property string $created_at
 * @property string $updated_at
 */
class AppInstall extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_install';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'source','create_time'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => '客户端唯一标示',
            'source' => '来源(包名)',
            'account' => 'App登陆账号',
            'create_time' => '创建时间(第一次打开时间)',
            'update_time' => '更新时间(第一次登陆时间)',
            'install_times' => '安装次数',
            'login_times' => '登陆次数'
        ];
    }


    public static function appInstallLog($code,$source,$account=''){
        $log = AppInstall::find()->where(['code'=>$code])->one();
        if ($log) {
            if ($account) {
                $log->account = $account;
                $log->login_times = $log->login_times+1;
            }else{
                $log->install_times = $log->install_times+1;
            }
        }else{
            $log = new AppInstall();
            $log->code = $code;
            $log->source = $source;
            $log->create_time = time();
            $log->install_times = 1;
        }
        $log->update_time = time();
        return $log->save();
    }
}
