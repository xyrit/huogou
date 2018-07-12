<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "free_current_periods".
 *
 * @property string $id
 * @property integer $table_id
 * @property integer $product_id
 * @property string $period_number
 * @property integer $sales_num
 * @property integer $start_time
 * @property integer $end_time
 */
class FreeCurrentPeriod extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'free_current_periods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_id', 'start_time', 'end_time'], 'required'],
            [['table_id', 'product_id', 'period_number', 'sales_num', 'start_time', 'end_time'], 'integer'],
            [['product_id'], 'unique']
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
            'sales_num' => 'Sales Num',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
        ];
    }

    public static function generateTableId()
    {
        return mt_rand(100,109);
    }
}
