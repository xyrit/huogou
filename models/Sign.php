<?php

namespace app\models;

use app\helpers\MyRedis;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "signs".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $signed_at
 * @property integer $continue
 * @property integer $total
 */
class Sign extends \yii\db\ActiveRecord
{
    const SIGN_LIST = 'SIGN_LIST';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'signs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['days', 'type', 'num'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'days' => 'Days',
            'type' => 'Type',
            'num' => 'Num',
        ];
    }

    public static function getList()
    {
        $key = self::SIGN_LIST;
        $redis = new MyRedis();

        $sign = [];
        if ($redis->isexist($key)) {
            $sign = $redis->hget($key, 'all');
            $sign['list'] = Json::decode($sign['list'], true);
        }

        return $sign;
    }
}
