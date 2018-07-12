<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_virtual_address".
 *
 * @property integer $id
 * @property integer $virtual_addr_id
 * @property string $nickname
 * @property string $headimg
 * @property integer $create_time
 */
class WxVirtualAddr extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wx_virtual_addr';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['virtual_addr_id','create_time'], 'integer'],
            [['headimg', 'nickname'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'virtual_addr_id' => '关联地址ID',
            'nickname' => '微信昵称',
            'headimg' => '微信头像',
            'create_time' => '创建时间'
        ];
    }
}
