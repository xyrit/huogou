<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pk_periods".
 *
 * @property string $id
 * @property integer $table_id
 * @property integer $product_id
 * @property integer $period_number
 * @property string $period_no
 * @property integer $price
 * @property string $start_time
 * @property string $end_time
 * @property integer $size
 * @property integer $match_num
 * @property string $exciting_time
 */
class PkPeriod extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pk_periods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'table_id', 'product_id', 'period_no', 'price', 'start_time', 'end_time', 'exciting_time'], 'required'],
            [['id', 'table_id', 'product_id', 'lucky_code', 'price','size','match_num'], 'integer'],
            [['start_time', 'end_time', 'exciting_time'], 'string', 'max' => 16],
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
            'period_no' => 'Period No',
            'lucky_code' => 'Lucky Code',
            'price' => 'Price',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'exciting_time' => 'Exciting Time',
        ];
    }


}
