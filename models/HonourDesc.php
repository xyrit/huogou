<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "honour_desc".
 *
 * @property string $id
 * @property string $title
 * @property string $icon
 * @property string $desc
 * @property string $created_at
 * @property integer $from
 */
class HonourDesc extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'honour_desc';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['desc'], 'string'],
            [['created_at','from'], 'integer'],
            [['title', 'icon'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'icon' => 'Icon',
            'desc' => 'Desc',
            'created_at' => 'Created At',
        ];
    }
}
