<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jdcard_buyback_mobile".
 *
 * @property integer $id
 * @property string $mobile
 * @property integer $admin_id
 * @property integer $add_time
 */
class JdcardBuybackMobile extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jdcard_buyback_mobile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id', 'add_time'], 'integer'],
            [['mobile'], 'string', 'max' => 13]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'admin_id' => 'Admin ID',
            'add_time' => 'Add Time',
        ];
    }
}
