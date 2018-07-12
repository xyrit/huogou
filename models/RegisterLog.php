<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "register_log".
 *
 * @property string $id
 * @property string $user_id
 * @property string $url
 * @property string $name
 * @property string $ip
 * @property integer $type
 * @property string $created_at
 */
class RegisterLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'register_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'ip', 'created_at'], 'integer'],
            [['url'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 100],
            [['source'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'url' => 'Url',
            'name' => 'Name',
            'ip' => 'Ip',
            'source' => 'source',
            'created_at' => 'Created At',
        ];
    }

    //日志记录
    public function actionAddLog($uid, $source = '', $name = '', $url = '')
    {
        $model = new RegisterLog();
        $model->user_id = $uid;
        $model->source = $source;
        $model->ip = ip2long(Yii::$app->request->userIP);
        $model->name = $name;
        $model->url = $url;
        $model->created_at = time();
        $model->save();
    }
}
