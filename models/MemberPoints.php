<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member_points".
 *
 * @property integer $id
 * @property integer $score
 * @property string $description
 * @property integer $type
 * @property integer $date
 * @property integer $uid
 */
class MemberPoints extends \yii\db\ActiveRecord 
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member_points';  //我的福分表
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['score', 'description', 'date', 'uid'], 'required'],
            [['score', 'type', 'date', 'uid'], 'integer'],
            [['description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'score' => '福分数',
            'description' => '详情',
            'type' => '类型0[获得]1[支出]',
            'date' => '获得福分时间',
            'uid' => '用户ID',
        ];
    }
}
