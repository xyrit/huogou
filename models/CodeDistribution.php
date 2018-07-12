<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/29
 * Time: 上午9:53
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "codes_x".
 *
 * @property integer $id
 * @property integer $code
 * @property integer $product_id
 * @property integer $status
 */
class CodeDistribution extends ActiveRecord
{
    const STATUS_SOLD = 1;
    const STATUS_UNSOLD = 0;

    private static $_productId;

    public static function instantiate($row)
    {
        return new static(static::$_productId);
    }

    public function __construct($productId, $config = [])
    {
        parent::__construct($config);
        static::$_productId = $productId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $tableId = static::$_productId % 10;
        return 'codes_' . $tableId;
    }

    /**
     * @param $productId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByProductId($productId) {
        $model = new static($productId);
        return $model::find();
    }

    /**
     * @param $productId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByProductId($productId, $condition)
    {
        $model = new static($productId);
        return $model::findOne($condition);
    }

    /**
     * @param $productId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByProductId($productId, $condition)
    {
        $model = new static($productId);
        return $model::findAll($condition);
    }

    public static function createCodes($num)
    {
        $start = 10000001;
        $end = $start + $num;
        $codes = [];
        for ($i=10000001;$i<$end;$i++) {
            $codes[] = $i;
        }
        return $codes;
    }



}