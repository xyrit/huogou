<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "adjust_balance".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $money
 * @property string $reason
 * @property string $order
 * @property string $admin_id
 * @property string $created_at
 */
class AdjustBalance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'adjust_balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'money', 'final_money',  'reason', 'order', 'admin_id', 'created_at'], 'required'],
            [['user_id', 'type', 'money', 'before_money', 'admin_id', 'created_at'], 'integer'],
            [['reason', 'order'], 'string', 'max' => 255],
            [['admin_id', 'created_at'], 'unique', 'targetAttribute' => ['admin_id', 'created_at'], 'message' => 'The combination of Admin ID and Created At has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户名',
            'type' => '操作',
            'money' => '金额',
            'reason' => '调整原因',
            'order' => '原始订单号',
            'admin_id' => '操作人',
            'created_at' => 'Created At',
        ];
    }
}
