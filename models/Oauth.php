<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oauth".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $source
 * @property string $source_id
 * @property string $unionid
 */
class Oauth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oauth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'source', 'source_id'], 'required'],
            [['user_id', 'source'], 'integer'],
            [['source_id'], 'string', 'max' => 255],
            [['source_id', 'source'], 'unique', 'targetAttribute' => ['source_id', 'source'], 'message' => 'The combination of Source and Source ID has already been taken.']
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
            'source' => 'Source',
            'source_id' => 'Source ID',
        ];
    }
}
