<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category_brand".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $brand_id
 * @property integer $brand_order
 */
class CategoryBrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category_brand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cat_id', 'brand_id'], 'required'],
            [['cat_id', 'brand_id', 'brand_order'], 'integer'],
            [['cat_id', 'brand_id'], 'unique', 'targetAttribute' => ['cat_id', 'brand_id'], 'message' => 'The combination of Cat ID and Brand ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cat_id' => 'Cat ID',
            'brand_id' => 'Brand ID',
            'brand_order' => 'Brand Order',
        ];
    }
}
