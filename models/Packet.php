<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "packet".
 *
 * @property string $id
 * @property string $name
 * @property integer $num
 * @property string $desc
 * @property string $content
 * @property integer $send_num
 * @property integer $left_num
 * @property integer $receive_limit
 * @property integer $create_time
 * @property integer $update_time
 */
class Packet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'packet';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['num','create_time', 'update_time'], 'required'],
            [['num', 'send_num', 'left_num', 'receive_limit', 'create_time', 'update_time'], 'integer'],
            [['desc'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['content'], 'string', 'max' => 400]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'num' => 'Num',
            'desc' => 'Desc',
            'content' => 'Content',
            'send_num' => 'Send Num',
            'left_num' => 'Left Num',
            'receive_limit' => 'Receive Limit',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
	public static function getInfo($id)
	{
		return Packet::find()->where(['id'=>$id])->asArray()->one();
	}
}
