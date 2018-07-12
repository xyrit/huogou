<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/2/17
 * Time: 下午1:44
 */
namespace app\commands;

class PaymentController extends \yii\console\Controller
{

    public function actionAddsource()
    {
        set_time_limit(0);
        $tableNum = 10;
        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;
            $paymentOrder = \app\models\PaymentOrderDistribution::findByTableId($tableId);
            $query = $paymentOrder->createCommand()->query();
            while($readData = $query->read()) {
                $paymentOrderId = $readData['id'];
                $source = $readData['source'];

                $paymentItemOrders = \app\models\PaymentOrderItemDistribution::findByTableId($paymentOrderId)
                    ->where(['payment_order_id'=>$paymentOrderId])
                    ->all();
                foreach($paymentItemOrders as $paymentItemOrder) {
                    if (isset($paymentItemOrder->source) && $paymentItemOrder->source==0) {
                        $paymentItemOrder->source = $source;
                        $save = $paymentItemOrder->save();
                        if ($save) {
                            echo "paymentOrder:{$paymentOrderId} paymentItemOrder:{$paymentItemOrder->id} source:{$source}\n";
                        }
                    }
                }


            }

        }
    }


}