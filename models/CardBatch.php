<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "card_batch".
 *
 * @property string $id
 * @property string $name
 * @property string $card_price_detail
 * @property string $num_limit
 * @property string $recharge_money_limit
 * @property string $recharge_num_limit
 * @property integer $valid_type
 * @property string $start_at
 * @property string $end_at
 * @property string $valid
 * @property string $user_apply
 * @property string $user_check
 * @property string $check_note
 * @property integer $export_num
 * @property string $created_at
 * @property string $checked_at
 * @property integer $status
 */
class CardBatch extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_batch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_price_detail', 'check_note'], 'string'],
            [['num_limit', 'recharge_money_limit', 'recharge_num_limit', 'valid_type', 'start_at', 'end_at', 'valid', 'user_apply', 'user_check', 'export_num', 'created_at', 'checked_at', 'status'], 'integer'],
            [['user_apply'], 'required'],
            [['name'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'card_price_detail' => 'Card Price Detail',
            'num_limit' => 'Num Limit',
            'recharge_money_limit' => 'Recharge Money Limit',
            'recharge_num_limit' => 'Recharge Num Limit',
            'valid_type' => 'Valid Type',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'valid' => 'Valid',
            'user_apply' => 'User Apply',
            'user_check' => 'User Check',
            'check_note' => 'Check Note',
            'export_num' => 'Export Num',
            'created_at' => 'Created At',
            'checked_at' => 'Checked At',
            'status' => 'Status',
        ];
    }
}
