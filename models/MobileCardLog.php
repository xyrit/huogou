<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
/**
 * 采购
 */
class MobileCardLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mobile_card_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile','orderid','message','result','create_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => '手机号',
            'orderid' => '中奖订单号',
            'province' => '省份',
            'type' => '类型',
            'product_id' => '商品ID',
            'face_value' => '面值',
            'message' => '返回结果',
            'result' => '返回内容',
        ];
    }
}
