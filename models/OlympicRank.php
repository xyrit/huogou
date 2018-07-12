<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "olympic_rank".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $gold
 * @property integer $silver
 * @property integer $bronze
 * @property integer $score
 * @property string $created_at
 */
class OlympicRank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'olympic_rank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'gold', 'silver', 'bronze', 'score', 'created_at'], 'required'],
            [['user_id', 'gold', 'silver', 'bronze', 'score'], 'integer'],
            [['created_at'], 'string', 'max' => 16],
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
            'gold' => 'Gold',
            'silver' => 'Silver',
            'bronze' => 'Bronze',
            'score' => 'Score',
            'created_at' => 'Created At',
        ];
    }
}
