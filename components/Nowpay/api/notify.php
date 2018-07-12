<?php 
    require_once define(__FILE__).'../utils/Log.php';
    require_once '../services/Services.php';
    /**
     * @author Jupiter
     * 
     * 通知接口
     * 
     * 用于被动接收中小开发者支付系统发过来的通知信息，并对通知进行验证签名，
     * 签名验证通过后，商户可对数据进行处理。
     * 
     * 通知频率:2min、10min、30min、1h、2h、6h、10h、15h
     */
    $request=file_get_contents('php://input');
    Log::outLog("通知接口", $request);
    parse_str($request,$request_form);
    if (Services::verifySignature($request_form)){
        $tradeStatus=$_REQUEST['tradeStatus'];
        if($tradeStatus!=""&&$tradeStatus=="A001"){
            echo "success=Y";
            /**
             * 在这里对数据进行处理
             */
        }
    }
?>