<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recharge_card_out".
 *
 * @property integer $out_id
 * @property integer $card_id
 * @property integer $user_apply
 * @property string $time_out
 */
class RechargeCardOut extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'recharge_card_out';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_id', 'user_apply'], 'required'],
            [['card_id', 'user_apply'], 'integer'],
            [['time_out'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'out_id' => 'ID',
            'card_id' => '批次id',
            'user_apply' => '申请人',
            'time_out' => '导出时间',
        ];
    }
    
        
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['card_id' => 'card_id']);
    }
    
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplyUser()
    {
        return $this->hasOne(AdminModel::className(), ['user_apply' => 'id']);
    }
}
