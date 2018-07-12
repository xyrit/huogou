<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "honour".
 *
 * @property string $id
 * @property string $period
 * @property string $rich_userid
 * @property integer $buynum
 * @property integer $first_userid
 * @property integer $end_userid
 * @property string $created_at
 */
class Honour extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'honour';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['period'], 'required'],
            [['period', 'rich_userid', 'buynum', 'first_userid', 'end_userid', 'created_at', 'last_modify'], 'integer'],
            [['period'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'period' => 'Period',
            'rich_userid' => 'Rich Userid',
            'buynum' => 'Buynum',
            'first_userid' => 'First Userid',
            'end_userid' => 'End Userid',
            'created_at' => 'Created At',
            'last_modify' => 'Last Modify',
        ];
    }

}
