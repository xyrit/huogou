<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ip_blacklist".
 *
 * @property integer $id
 * @property string $ip
 * @property integer $created_at
 * @property integer $updated_at
 */
class IpBlacklist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_blacklist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['ip'], 'string', 'max' => 15]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    public static function add($ip)
    {
        $black = IpBlacklist::findOne(['ip'=>$ip]);
        if (!$black) {
            $black->updated_at = time();
            $black->save(false);
        } else {
            $black = new static();
            $black->ip = $ip;
            $black->created_at = time();
            $black->updated_at = time();
            $black->save(false);
        }
    }
}
