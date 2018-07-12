<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "roles".
 *
 * @property string $id
 * @property string $name
 * @property string $privilege
 * @property string $created_at
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'roles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'required'],
            [['created_at'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['privilege'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'privilege' => 'Privilege',
            'created_at' => 'Created At',
        ];
    }
}
