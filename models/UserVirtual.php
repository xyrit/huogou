<?php

namespace app\models;

use Yii;

/**
 * 虚拟物品仓库
 */
class UserVirtual extends \yii\db\ActiveRecord
{
    const GET_CARD_KEY = "GET_CARD_"; //orderid
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_virtual';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'type','orderid','card','pwd','par_value'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户id',
            'type' => '类型',
            'orderid' => '中奖订单号',
            'card' => '卡号',
            'pwd' => '密码',
            'par_value' => '面值'
        ];
    }

}
