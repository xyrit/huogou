<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/22
 * Time: 下午7:39
 */
header("Content-type: text/html; charset=utf-8");
/**
 *类名：trade.php
 *功能  服务器端创建交易Demo
 *版本：1.0
 *日期：2014-06-26
'说明：
'以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己的需要，按照技术文档编写,并非一定要使用该代码。
'该代码仅供学习和研究爱贝云计费接口使用，只是提供一个参考。
 */
require_once ("config.php");
require_once ("base.php");


//此为下单函数。使用时请把下列参数按要求更换成你们自己的数据。另外也需要更换config.php 中的公钥和私钥
function createOrder($userId,$goodsId,$orderId,$price,$cpprivateinfo,$notifyUrl,$returnTransData = false) {
    global $orderUrl, $cpvkey, $platpkey,$appid;
    //下单接口
    $orderReq["appid"] = "{$appid}";
    $orderReq["waresid"] = $goodsId;
    $orderReq["cporderid"] = "$orderId";
    $orderReq["price"] = floatval(sprintf('%.2f',$price));  //单位：元
    $orderReq["currency"] = "RMB";
    $orderReq["appuserid"] = "$userId";
    $orderReq["cpprivateinfo"] = "$cpprivateinfo";
    $orderReq["notifyurl"] = "$notifyUrl";
    //组装请求报文
    $reqData = composeReq($orderReq, $cpvkey);
    //如果是app返回transdata拼装的字符串
    if ($returnTransData) {
        return $reqData;
    }
    //发送到爱贝服务后台请求下单
    $respData = request_by_curl($orderUrl, $reqData, "order test");

    //验签数据并且解析返回报文
    if(!parseResp($respData, $platpkey, $respJson)) {
        return false;
    }
//     下单成功之后获取 transid
    return $transid = isset($respJson->transid) ? $respJson->transid : false;
}