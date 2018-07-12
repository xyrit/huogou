<?php
/**
 * 充值活动配置
 * @authors hechen
 * @date    2016-04-01 17:30:13
 * @version $Id$
 */

namespace app\models;

use Yii;

class RechargeReward extends \yii\db\ActiveRecord {
    
    public static function tableName()
    {
        return 'recharge_reward';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'intr', 'start_time', 'end_time', 'status', 'prizes','create_time','update_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '活动名称',
            'intr' => '活动简介',
            'start_time' => '活动开始时间',
            'end_time' => '活动结束时间',
            'status' => '状态',
            'prizes' => '奖品',
            'create_time' => '创建时间',
            'update_time' => '更新时间'
        ];
    }
}