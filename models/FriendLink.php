<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "friend_link".
 *
 * @property string $id
 * @property string $name
 * @property string $picture
 * @property string $link
 * @property integer $list_order
 * @property string $created_at
 * @property string $updated_at
 */
class FriendLink extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'friend_link';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'link'], 'required'],
            [['list_order', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['picture'], 'string', 'max' => 255],
            [['link'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'picture' => '图片',
            'link' => '链接地址',
            'list_order' => '排序',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
