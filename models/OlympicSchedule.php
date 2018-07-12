<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "olympic_schedules".
 *
 * @property integer $id
 * @property integer $date
 * @property string $name
 * @property integer $created_at
 */
class OlympicSchedule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'olympic_schedules';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'name', 'created_at'], 'required'],
            [['date', 'created_at'], 'integer'],
            [['name'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'name' => 'Name',
            'created_at' => 'Created At',
        ];
    }
}
