<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "free_periods".
 *
 * @property string $id
 * @property integer $table_id
 * @property integer $product_id
 * @property integer $period_number
 * @property integer $lucky_code
 * @property integer $user_id
 * @property integer $sales_num
 * @property integer $ip
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $exciting_time
 */
class FreePeriod extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'free_periods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'table_id', 'product_id', 'period_number', 'start_time', 'end_time'], 'required'],
            [['id', 'table_id', 'product_id', 'period_number', 'lucky_code', 'user_id', 'ip', 'start_time', 'end_time', 'exciting_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'table_id' => 'Table ID',
            'product_id' => 'Product ID',
            'period_number' => 'Period Number',
            'lucky_code' => 'Lucky Code',
            'user_id' => 'User ID',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'exciting_time' => 'Exciting Time',
        ];
    }
}
