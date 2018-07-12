<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cards".
 *
 * @property integer $id
 * @property integer $card_id
 * @property integer $home_id
 * @property string $number
 * @property string $password
 * @property integer $money
 * @property integer $out_type
 * @property integer $out_user
 * @property integer $out_id
 * @property string $time_out
 * @property string $time_start
 * @property string $time_end
 * @property string $time_used
 * @property integer $status
 */
class Card extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cards';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_id', 'number', 'password'], 'required'],
            [['card_id', 'home_id', 'money', 'out_type', 'out_user', 'out_id', 'status'], 'integer'],
            [['time_out', 'time_start', 'time_end', 'time_used'], 'safe'],
            [['number'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card_id' => '批次id',
            'home_id' => '使用人ID',
            'number' => '充值卡号',
            'password' => '充值密码',
            'money' => '金额',
            'out_type' => '使用方式 [未使用/销售/导出]',
            'out_user' => '导出人admin_user_id',
            'out_id' => '导出id',
            'time_out' => '导出时间',
            'time_used' => '使用时间',
            'status' => '0未使用，1已使用，2过期，3已导出',
        ];
    }
    
    public function getOutType()
    {
            return ["","销售","导出"][$this->out_type];
    }
    
    public function getState()
    {
            return ["未使用","已使用","过期","已导出"][$this->status];
    }
        
      /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['home_id' => 'home_id']);
    }
    
      
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOutUser()
    {
        return $this->hasOne(AdminModel::className(), ['out_id' => 'id']);
    }
    
    
      /**
     * @return \yii\db\ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(RechargeCard::className(), ['card_id' => 'card_id']);
    }
        
      /**
     * @return \yii\db\ActiveQuery
     */
    public function getOut()
    {
        return $this->hasOne(RechargeCardOut::className(), ['out_id' => 'out_id']);
    }
}
