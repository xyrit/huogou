<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "yesterday".
 *
 * @property string $id
 * @property integer $member
 * @property integer $income
 * @property integer $recharge
 * @property integer $lottery
 * @property integer $delivery
 * @property string $created_at
 */
class Yesterday extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yesterday';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member', 'income', 'recharge', 'lottery', 'delivery', 'created_at'], 'integer'],
            [['created_at'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member' => 'Member',
            'income' => 'Income',
            'recharge' => 'Recharge',
            'lottery' => 'Lottery',
            'delivery' => 'Delivery',
            'created_at' => 'Created At',
        ];
    }
}
