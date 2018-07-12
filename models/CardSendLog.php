<?php

namespace app\models;

use Yii;

/**
 * 虚拟物品仓库
 */
class CardSendLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_send_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','orderid','user_id','mobile','create_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orderid' => '订单ID',
            'user_id' => '用户ID',
            'mobile' => '手机号',
            'create_time' => '发送时间',
        ];
    }

}
