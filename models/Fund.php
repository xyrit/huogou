<?php

namespace app\models;

use Yii;

class Fund extends \yii\db\ActiveRecord
{
	public static function tableName()
    {
        return 'fund';
    }

    /**
     * 添加
     * @param float $money [description]
     */
    public static function addFund($money){
    	$count = static::find()->asArray()->one();
    	$new = $count['count']+$money;
    	static::updateAll(['count'=>$new]);
    }
}