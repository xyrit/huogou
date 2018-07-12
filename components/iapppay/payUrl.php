<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/22
 * Time: 下午7:43
 */
require_once ("config.php");
require_once ("base.php");


//此为H5 调收银台时需要的参数组装函数
function payUrl($transid,$redirectUrl,$cpUrl,$source) {
    global $pcurl, $h5url,$cpvkey, $platpkey;//组装数据并签名。
    echo "开始组装号调用支付接口的参数";
    //下单接口
    $orderReq["transid"] = "$transid";
    $orderReq["redirecturl"] = "$redirectUrl";
    $orderReq["cpurl"] = "$cpUrl";
    //组装请求报文
    $reqData = composeReq($orderReq, $cpvkey);
    if ($source==1) {
        return $pcurl . '?' . $reqData;
    }
    return $h5url . '?' . $reqData;

}