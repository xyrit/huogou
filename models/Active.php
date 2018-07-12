<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actives".
 *
 * @property string $id
 * @property string $title
 * @property string $sub_title
 * @property integer $flag
 * @property string $icon
 * @property string $url
 * @property string $picture
 * @property integer $status
 * @property integer $type
 * @property string $created_at
 * @property integer $from
 */
class Active extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actives';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flag', 'status', 'type', 'created_at', 'from'], 'integer'],
            [['title', 'sub_title', 'icon', 'url'], 'string', 'max' => 64],
            [['picture'], 'string', 'max' => 255]
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
            'sub_title' => 'Sub Title',
            'flag' => 'Flag',
            'icon' => 'Icon',
            'url' => 'Url',
            'picture' => 'Picture',
            'status' => 'Status',
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }
}
