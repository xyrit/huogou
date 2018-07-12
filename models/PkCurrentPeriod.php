<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pk_current_periods".
 *
 * @property string $id
 * @property integer $product_id
 * @property integer $table_id
 * @property integer $period_number
 * @property string $period_no
 * @property integer $price
 * @property string $start_time
 * @property string $end_time
 */
class PkCurrentPeriod extends \yii\db\ActiveRecord
{
    const BUY_SIZE_BIG = 1;
    const BUY_SIZE_SMALL = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pk_current_periods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'table_id', 'period_number', 'price'], 'integer'],
            [['table_id', 'period_number', 'period_no', 'start_time', 'end_time'], 'required'],
            [['start_time', 'end_time'], 'string', 'max' => 16],
            [['product_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'table_id' => 'Table ID',
            'period_number' => 'Period Number',
            'period_no' => 'Period No',
            'price' => 'Price',
            'sales_num' => 'Sales Num',
            'progress' => 'Progress',
            'left_num' => 'Left Num',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
        ];
    }
}
