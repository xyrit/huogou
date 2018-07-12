<?php
/**
 * 充值奖励记录
 * @authors hechen 
 * @date    2016-03-30 19:09:24
 * @version $Id$
 */
namespace app\models;

use Yii;

class RechargeRewardLog extends \yii\db\ActiveRecord {

    public static function tableName()
	{
		return 'recharge_reward_log';
	}

	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number', 'user_id', 'level', 'amount','create_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => '期数',
            'user_id' => '用户ID',
            'level' => '等级',
            'amount' => '充值金额',
            'time' => '充值时间'
        ];
    }
}