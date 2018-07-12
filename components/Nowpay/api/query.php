<?php 
    require_once '../conf/Config.php';
    require_once '../services/Services.php';
    /**
     * @author Jupiter
     * 查询接口
     * 
     * 用于根据商户订单号历史交易信息。
     */
    class QueryOrder{
        public function main(){
            $req=array();
            $req["funcode"]=Config::QUERY_FUNCODE_KEY;
            $req["mhtOrderNo"]="";//这里填写商户已发生过交易的订单号
            $req["appId"]=Config::$app_id;
            $req["mhtCharset"]=Config::CHARSET;
            $req["mhtSignType"]=Config::SIGN_TYPE;
            $req["mhtSignature"]=Core::buildSignature($req);
            
            $resp=array();
            Services::queryOrder($req, $resp);
            echo print_r($resp);//若签名验证成功，则在这里获取响应信息
        }
    }
    $p=new QueryOrder();
    $p->main();
?>