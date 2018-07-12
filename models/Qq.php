<?php

namespace app\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "qq".
 *
 * @property string $id
 * @property string $num
 * @property integer $default
 * @property string $android_key
 * @property string $ios_key
 * @property string $uin
 * @property string $created_at
 * @property integer $admin_id
 */
class Qq extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qq';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['default', 'created_at', 'admin_id'], 'integer'],
            [['android_key', 'ios_key', 'uin'], 'required'],
            [['num'], 'string', 'max' => 20],
            [['android_key', 'ios_key', 'uin'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'num' => 'Num',
            'default' => 'Default',
            'android_key' => 'Android Key',
            'ios_key' => 'Ios Key',
            'uin' => 'Uin',
            'created_at' => 'Created At',
            'admin_id' => 'Admin ID',
        ];
    }

    public static function getList($pageSize, $page)
    {
        $query = Qq::find()->where('default = 1');
        $pages = new Pagination(['defaultPageSize' => $pageSize, 'totalCount' => $query->count(), 'page' => $page - 1]);
        $roles = $query->orderBy('id desc')->offset($pages->offset)->limit($pages->limit)->asArray()->one();
        return $roles;
    }
}
