<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/17
 * Time: 下午5:43
 */
namespace app\components;

use yii\base\Component;

class Iapppay extends Component
{

    /** 获取app下单参数
     * @param $userId
     * @param $goodsName
     * @param $goodsId
     * @param $orderId
     * @param $price
     * @param $cpprivateinfo
     * @param $notifyUrl
     * @return bool|string
     */
    public function getAppOrderParams($userId, $goodsName, $goodsId, $orderId, $price, $cpprivateinfo, $notifyUrl)
    {
        require_once(__DIR__ . '/iapppay/createOrder.php');
        $transdata =  createOrder($userId, $goodsId, $orderId, $price, $cpprivateinfo, $notifyUrl, true);
        return ['transdata'=>$transdata, 'orderid'=>$orderId];
    }

    /** 爱贝支付下单
     * @param $goodsName
     * @param $goodsId
     * @param $orderId
     * @param $price
     * @param $cpprivateinfo
     * @param $notifyUrl
     * @return bool
     */
    function order($userId, $goodsName, $goodsId, $orderId, $price, $cpprivateinfo, $notifyUrl)
    {
        require_once(__DIR__ . '/iapppay/createOrder.php');
        return createOrder($userId, $goodsId, $orderId, $price, $cpprivateinfo, $notifyUrl);
    }

    /** 跳转爱贝支付收银台Url
     * @param $transid
     * @param $redirectUrl
     * @param $cpUrl
     * @param $source
     * @return string
     */
    function url($transid, $redirectUrl, $cpUrl, $source)
    {
        require_once(__DIR__ . '/iapppay/payUrl.php');
        return payUrl($transid, $redirectUrl, $cpUrl, $source);
    }

    /** 支付结果查询
     * @param $cporderid
     * @return array|bool
     */
    function queryResult($cporderid)
    {
        require_once(__DIR__ . '/iapppay/config.php');
        global $queryResultUrl,$appid;
        $data = [
            'appid'=>"$appid",
            "cporderid"=>"$cporderid",
        ];
        return $this->httpPost($queryResultUrl, $data);
    }

    function parseResponse($respData)
    {
        require_once(__DIR__ . '/iapppay/config.php');
        require_once(__DIR__ . '/iapppay/base.php');
        global $platpkey;
        //验签数据并且解析返回报文
        if(!parseResp($respData, $platpkey, $respJson)) {
            return false;
        }
        return (Array)$respJson;
    }

    function httpPost($url,$data)
    {
        require_once(__DIR__ . '/iapppay/config.php');
        require_once(__DIR__ . '/iapppay/base.php');
        global $cpvkey,$platpkey;
        //组装请求报文
        $reqData = composeReq($data, $cpvkey);
        //发送到爱贝服务后台请求下单
        $respData = request_by_curl($url, $reqData, "qeury result");
        //验签数据并且解析返回报文
        if(!parseResp($respData, $platpkey, $respJson)) {
            return false;
        }
        $respJson = (Array)$respJson;
        return $respJson;
    }


}