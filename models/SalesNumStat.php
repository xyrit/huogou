<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sales_num_stat".
 *
 * @property integer $id
 * @property string $day
 * @property string $hour
 * @property integer $result
 * @property integer $created_at
 */
class SalesNumStat extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sales_num_stat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['day', 'hour', 'result', 'created_at'], 'required'],
            [['result', 'created_at'], 'integer'],
            [['day', 'hour'], 'string', 'max' => 32],
            [['day', 'hour'], 'unique', 'targetAttribute' => ['day', 'hour'], 'message' => 'The combination of Day and Hour has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'day' => 'Day',
            'hour' => 'Hour',
            'result' => 'Result',
            'created_at' => 'Created At',
        ];
    }
}
