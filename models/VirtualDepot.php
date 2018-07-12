<?php

namespace app\models;

use Yii;

/**
 * 虚拟物品仓库
 */
class VirtualDepot extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'virtual_depot';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderid', 'card','pwd','par_value'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orderId' => '购买订单id',
            'card' => '卡号',
            'pwd' => '密码',
            'par_value' => '面值',
        ];
    }

}
