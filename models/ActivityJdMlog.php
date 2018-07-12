<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_jd_mlog".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $y_money
 * @property integer $n_money
 * @property integer $start_period
 * @property integer $end_period
 * @property integer $uptime
 */
class ActivityJdMlog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_jd_mlog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'y_money', 'n_money', 'start_period', 'end_period', 'uptime'], 'integer']
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
            'y_money' => 'Y Money',
            'n_money' => 'N Money',
            'start_period' => 'Start Period',
            'end_period' => 'End Period',
            'uptime' => 'Uptime',
        ];
    }
}
