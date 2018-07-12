<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "euro_userbuylist".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $game_date
 * @property string $team
 * @property integer $buy_num
 * @property string $buy_time
 */
class EuroUserbuylist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'euro_userbuylist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'game_date', 'team', 'buy_num', 'buy_time'], 'required'],
            [['user_id', 'buy_num'], 'integer'],
            [['game_date'], 'string', 'max' => 8],
            [['team'], 'string', 'max' => 25],
            [['buy_time'], 'string', 'max' => 16],
            [['user_id', 'game_date', 'team'], 'unique', 'targetAttribute' => ['user_id', 'game_date', 'team'], 'message' => 'The combination of User ID, Game Date and Team has already been taken.'],
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
            'game_date' => 'Game Date',
            'team' => 'Team',
            'buy_num' => 'Buy Num',
            'buy_time' => 'Buy Time',
        ];
    }
}
