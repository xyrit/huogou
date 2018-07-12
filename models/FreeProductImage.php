<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "free_product_images".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $basename
 */
class FreeProductImage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'free_product_images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'basename'], 'required'],
            [['product_id'], 'integer'],
            [['basename'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'basename' => 'Basename',
        ];
    }
}
