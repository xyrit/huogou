<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_config".
 *
 * @property integer $id
 * @property string $type
 * @property string $content
 * @property string $auth
 * @property string $time
 * @property integer $status
 * @property string $system
 * @property integer $sort
 * @property integer $from
 */
class AppConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['content'], 'string'],
            [['time'], 'safe'],
            [['status', 'sort', 'from'], 'integer'],
            [['type'], 'string', 'max' => 20],
            [['auth'], 'string', 'max' => 10],
            [['system'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'content' => 'Content',
            'auth' => 'Auth',
            'time' => 'Time',
            'status' => 'Status',
            'system' => 'System',
            'sort' => 'Sort',
            'from' => 'From',
        ];
    }
}
