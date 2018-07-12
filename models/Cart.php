<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "carts".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $product_id
 * @property string $period_id
 * @property string $nums
 */
class Cart extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'carts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'product_id', 'period_id', 'nums'], 'required'],
            [['user_id', 'product_id', 'period_id', 'nums'], 'integer'],
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
            'period_id' => 'Period ID',
            'nums' => 'Nums',
        ];
    }
}
