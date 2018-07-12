<?php

namespace app\modules\admin\models;

use Yii;
use app\modules\admin\models\DeliverFailReason;
use app\modules\admin\models\SendFailReason;
use app\modules\admin\models\ExchangeGoodReason;
/**
 * This is the model class for table "confirm_fail_reasons".
 *
 * @property string $id
 * @property string $name
 */
class ConfirmFailReason extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'confirm_fail_reasons';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * 订单异常原因
     */
    public static function orderFailReason($type, $id)
    {
        if($type == 1){
            $name = ConfirmFailReason::findOne($id);
        }elseif($type == 2){
            $name = DeliverFailReason::findOne($id);
        }elseif($type == 3){
            $name = SendFailReason::findOne($id);
        }elseif($type == 4){
            $name = ExchangeGoodReason::findOne($id);
        }
        return $name['name'];
    }
}
