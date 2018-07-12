<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "follow_products".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $product_id
 * @property string $follow_time
 */
class FollowProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'product_id', 'follow_time'], 'required'],
            [['user_id', 'product_id'], 'integer'],
            [['follow_time'], 'string', 'max' => 16],
            [['user_id', 'product_id'], 'unique', 'targetAttribute' => ['user_id', 'product_id'], 'message' => 'The combination of User ID and Product ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'product_id' => 'Product ID',
            'follow_time' => 'Follow Time',
        ];
    }
}
