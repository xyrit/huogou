<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_notices".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $receive_sysinfo
 * @property integer $receive_wchat
 * @property string $created_at
 * @property string $updated_at
 */
class UserNotice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_notices';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'receive_sysinfo', 'receive_wchat', 'created_at', 'updated_at'], 'integer'],
            [['created_at', 'updated_at'], 'required'],
            [['user_id'], 'unique']
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
            'receive_sysinfo' => 'Receive Sysinfo',
            'receive_wchat' => 'Receive Wchat',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function editNoitce($model, $params)
    {
        $model->receive_sysinfo = isset($params['receive_sysinfo']) ? $params['receive_sysinfo'] : 0;
        $model->receive_wchat = isset($params['receive_wchat']) ? $params['receive_wchat'] : 0;

        if (!isset($model->id)) {
            $model->user_id = Yii::$app->user->id;
            $model->created_at = time();
            $model->updated_at = 0;
        } else {
            $model->updated_at = time();
        }

        if (!$model->save()) {
            return false;
        }

        return true;
    }
}
