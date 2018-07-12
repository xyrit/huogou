<?php
    require_once 'Core.php';
    require_once 'Net.php';
    require_once dirname(__FILE__).'/../conf/Config.php';
require_once dirname(__FILE__).'/../utils/Log.php';
use app\helpers\Brower;
    if(function_exists("date_default_timezone_set")){
        date_default_timezone_set(Config::$timezone);
    }
    /**
     * 
     * @author Jupiter
     * 接口服务类
     * 
     * 用于处理通知以及查询等请求
     */
    class Services{
        /**
         * 查询处理方法
         * @param array $req
         * @param array $para
         */


        public static function queryOrder(Array $req,Array &$para){
            //组合报文
            $req_str=self::buildReq($req);
            Log::outLog("订单查询(商户->中怡同创)", $req_str);
            //推送给中小开发者支付系统

            $from = Brower::whereFrom();
            if ($from == 2) {
                $config = require (Yii::getAlias('@app/config/didi_nowpay.php'));
                $nowpay = Yii::createObject($config);
            } else {
                $nowpay = Yii::$app->nowpay;
            }
            $query_url=$nowpay->query_url;



            $resp_str=Net::sendMessage($req_str, $query_url);
            Log::outLog("订单查询(商户->中怡同创)", $resp_str);
            //验证签名
            return self::verifyResponse($resp_str, $para);
        }
        
        private static function buildReq(Array $req){
            return Core::createLinkString($req, false, true);
        }
        
        public static function verifySignature($para){

            $respSignature=$para[Config::SIGNATURE_KEY];

            Log::outLog("原签名", $respSignature);
            $filteredReq=Core::paraFilter($para);
         //   var_dump($filteredReq);exit;
            $signature=Core::buildSignature($filteredReq);
              //  var_dump($signature);exit;
            Log::outLog("核对签名", $signature);
            if ($respSignature!=""&&$respSignature==$signature) {
                return TRUE;
            }else {
                return FALSE;
            }
        }
        
        public static function buildSignature(Array $para){
            $filteredReq=Core::paraFilter($para);
            return Core::buildSignature($filteredReq);
        }
        
        public static function verifyResponse($resp_str,&$resp){
            if ($resp_str!="") {
                parse_str($resp_str,$para);
                
                $signIsValid=self::verifySignature($para);
                $resp=$para;
                if ($signIsValid) {
                    return TRUE;
                }else{
                    return FALSE;
                }
            }
        }
    }