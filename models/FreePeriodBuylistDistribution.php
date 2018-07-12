<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/3/8
 * Time: 下午5:15
 */
namespace app\models;

use Yii;

/**
 * This is the model class for table "free_period_buylist_100".
 *
 * @property string $id
 * @property integer $product_id
 * @property string $period_id
 * @property integer $user_id
 * @property string $code
 * @property integer $ip
 * @property integer $source
 * @property integer $pay_type
 * @property integer $pay_bank
 * @property string $buy_time
 */
class FreePeriodBuylistDistribution extends \yii\db\ActiveRecord
{

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
        return 'free_period_buylist_' . $tableId;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'period_id', 'user_id',  'ip', 'buy_time'], 'required'],
            [['product_id', 'period_id', 'user_id', 'ip', 'source', 'pay_type', 'pay_bank'], 'integer'],
            [['code'], 'string'],
            [['buy_time'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'period_id' => 'Period ID',
            'user_id' => 'User ID',
            'code' => 'Code',
            'ip' => 'Ip',
            'source' => 'Source',
            'buy_time' => 'Buy Time',
        ];
    }

    /**
     * @param $tableId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByTableId($tableId)
    {
        $model = new static($tableId);
        return $model::find();
    }

    /**
     * @param $tableId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByTableId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findOne($condition);
    }

    /**
     * @param $tableId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByTableId($tableId, $condition)
    {
        $model = new static($tableId);
        return $model::findAll($condition);
    }
}
