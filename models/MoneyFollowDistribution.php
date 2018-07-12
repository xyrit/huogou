<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "money_follow_100".
 *
 * @property string $id
 * @property string $user_id
 * @property string $current_money
 * @property integer $money
 * @property integer $type
 * @property string $desc
 * @property integer $created_at
 */
class MoneyFollowDistribution extends \yii\db\ActiveRecord
{
    /**
     * 余额变动类型
     */
    const MONEY_SIGN = 1; //签到奖励
    const MONEY_COUPON = 2; //优惠券添加
    const MONEY_LOTTERY = 3; //抽奖
    const MONEY_TASK = 4; //任务

    const MONEY_CHANGE = 5;//后台管理员变动

    private static $_tableId;

    public static function instantiate($row)
    {
        return new static(static::$_tableId);
    }

    public function __construct($tableId, $config = [])
    {
        parent::__construct($config);
        static::$_tableId = $tableId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $tableId = substr(static::$_tableId, 0, 3);
        return 'money_follow_' . $tableId;
    }

    public static function getTableIdByOrderId($orderId)
    {
        return substr($orderId, 0, 3);
    }

    public static function getTableIdByUserHomeId($userHomeId)
    {
        return substr($userHomeId, 0, 3);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'current_money', 'money', 'type', 'desc', 'created_at'], 'required'],
            [['user_id', 'current_money', 'money', 'type', 'created_at'], 'integer'],
            [['desc'], 'string', 'max' => 255]
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
            'current_money' => 'Current Money',
            'money' => 'Money',
            'type' => 'Type',
            'desc' => 'Desc',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param $userHomeId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByUserHomeId($userHomeId) {
        $model = new static($userHomeId);
        return $model::find();
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByUserHomeId($userHomeId, $condition)
    {
        $model = new static($userHomeId);
        return $model::findOne($condition);
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByUserHomeId($userHomeId, $condition)
    {
        $model = new static($userHomeId);
        return $model::findAll($condition);
    }
}
