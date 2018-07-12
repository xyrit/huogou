<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "exchange_order".
 *
 * @property string $id
 * @property string $order_no
 * @property integer $admin_id
 * @property string $created_time
 */
class ExchangeOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'exchange_orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no', 'admin_id', 'created_time'], 'required'],
            [['admin_id', 'created_time', 'user_id', 'order_no'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'admin_id' => 'Admin ID',
            'created_time' => 'Created Time',
        ];
    }
}
