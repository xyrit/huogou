<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "virtual_depot_jd".
 *
 * @property string $id
 * @property string $card
 * @property string $pwd
 * @property integer $par_value
 * @property integer $status
 * @property integer $created_at
 * @property integer $admin_id
 */
class VirtualDepotJd extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'virtual_depot_jd';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['par_value'], 'required'],
            [['par_value', 'status', 'created_at', 'admin_id'], 'integer'],
            [['card', 'pwd'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card' => 'Card',
            'pwd' => 'Pwd',
            'par_value' => 'Par Value',
            'status' => 'Status',
            'created_at' => 'Created At',
            'admin_id' => 'Admin ID',
        ];
    }
}
