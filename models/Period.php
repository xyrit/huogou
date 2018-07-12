<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "periods".
 *
 * @property string $id
 * @property integer $table_id
 * @property integer $product_id
 * @property integer $limit_num
 * @property integer $buy_unit
 * @property integer $cat_id
 * @property integer $period_number
 * @property integer $lucky_code
 * @property integer $user_id
 * @property integer $price
 * @property string $start_time
 * @property string $end_time
 * @property string $exciting_time
 * @property integer $result_time
 * @property string $period_no
 */
class Period extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'periods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_id', 'product_id', 'limit_num', 'cat_id', 'period_number', 'price', 'start_time'], 'required'],
            [['table_id', 'product_id', 'limit_num','buy_unit',  'cat_id', 'period_number', 'lucky_code', 'user_id', 'price'], 'integer'],
            [['start_time', 'end_time', 'exciting_time', 'result_time'], 'string', 'max' => 16],
            [['period_no'], 'string', 'max' => 20],
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
            'limit_num' => 'Limit Num',
            'cat_id' => 'Cat ID',
            'period_number' => 'Period Number',
            'lucky_code' => 'Lucky Code',
            'user_id' => 'User ID',
            'price' => 'Price',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'exciting_time' => 'Exciting Time',
        ];
    }
}
