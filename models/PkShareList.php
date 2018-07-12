<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pk_share_list".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $headimg
 * @property string $nickname
 * @property integer $size
 * @property integer $product_id
 * @property string $product_img
 * @property string $product_name
 * @property integer $product_price
 * @property integer $status
 */
class PkShareList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pk_share_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'size', 'product_id', 'product_price', 'status'], 'integer'],
            [['headimg', 'nickname', 'product_img', 'product_name'], 'string', 'max' => 50]
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
            'headimg' => 'Headimg',
            'nickname' => 'Nickname',
            'size' => 'Size',
            'product_id' => 'Product ID',
            'product_img' => 'Product Img',
            'product_name' => 'Product Name',
            'product_price' => 'Product Price',
            'status' => 'Status',
        ];
    }
}
