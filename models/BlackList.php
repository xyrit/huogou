<?php

namespace app\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "black_lists".
 *
 * @property string $id
 * @property integer $type
 * @property integer $user_id
 * @property integer $created_at
 */
class BlackList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'black_lists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'user_id', 'created_at'], 'integer'],
            [['user_id', 'type'], 'unique', 'targetAttribute' => ['user_id', 'type'], 'message' => 'The combination of Type and User ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }
}
