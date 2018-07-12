<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tasks".
 *
 * @property string $id
 * @property string $name
 * @property string $point
 * @property integer $type
 * @property integer $level
 * @property string $num
 */
class Task extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tasks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['point', 'type', 'level', 'num'], 'integer'],
            [['name'], 'string', 'max' => 32]
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
            'point' => 'Point',
            'type' => 'Type',
            'level' => 'Level',
            'num' => 'Num',
        ];
    }
}
