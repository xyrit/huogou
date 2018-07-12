<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "brands".
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 * @property string $url
 * @property string $intro
 */
class LotteryCompute extends \yii\db\ActiveRecord
{   
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lottery_compute';
    }
}