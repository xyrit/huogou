<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_jd_log".
 *
 * @property integer $id
 * @property integer $red_id
 * @property integer $user_id
 * @property integer $remain
 * @property integer $add_time
 * @property integer $old_money
 */
class ActivityJdLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_jd_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['red_id', 'user_id', 'remain', 'add_time', 'old_money'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'red_id' => 'Red ID',
            'user_id' => 'User ID',
            'remain' => 'Remain',
            'add_time' => 'Add Time',
            'old_money' => 'Old Money',
        ];
    }
}
