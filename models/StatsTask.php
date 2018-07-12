<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "stats_task".
 *
 * @property string $id
 * @property string $date
 * @property integer $type
 * @property integer $level
 * @property integer $cate
 * @property integer $num
 * @property integer $count
 */
class StatsTask extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stats_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'type', 'level', 'cate', 'num', 'count'], 'integer']
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
            'type' => 'Type',
            'level' => 'Level',
            'cate' => 'Cate',
            'num' => 'Num',
            'count' => 'Count',
        ];
    }
}
