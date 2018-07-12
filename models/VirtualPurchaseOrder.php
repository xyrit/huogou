<?php

namespace app\models;

use Yii;

/**
 * 虚拟物品仓库
 */
class VirtualPurchaseOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'virtual_purchase_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderid', 'vid', 'par_value','nums','create_time','update_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orderId' => '购买订单id',
            'vid' => '虚拟商品Id',
            'card' => '卡号',
            'pwd' => '密码',
            'par_value' => '面值',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ];
    }

    public static function createOrder($purchaseId,$vid,$par_value,$nums)
    {
        list($sec, $usec) = explode('.', microtime(true));
        $orderId = date('YmdHis') . $usec . mt_rand(10, 99) . '1';

        $model = new VirtualPurchaseOrder();
        $model->orderid = $orderId;
        $model->vid = $vid;
        $model->par_value = $par_value;
        $model->nums = $nums;
        $model->create_time = time();
        $model->update_time = time();
        $model->purchaseid = $purchaseId;

        if ($model->save()) {
            return $orderId;
        }else{
            return false;
        }
 
    }

}
