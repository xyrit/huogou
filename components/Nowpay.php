<?php
/**
 * Created by PhpStorm.
 * User: hui
 * Date: 16/06/22
 * Time: 下午3:37
 * 微信企业支付
 */

namespace app\components;

use yii;
use yii\base\Component;

//require_once __DIR__.'/nowpay/utils/Log.php';
//require_once __DIR__.'/nowpay/services/Services.php';
require_once dirname(__FILE__) . '/Nowpay/utils/Log.php';
require_once dirname(__FILE__) . '/Nowpay/services/Services.php';

//require_once dirname(__FILE__).'/Nowpay/services/Core.php';
class Nowpay extends Component
{

//=======================商户签名密钥
    public $secure_key;
//====================== 商户关联的appid
    public $app_id;
//=======================企业付款给个人付款地址
    public $query_url;


    public function notify($successCallback, $failCallback)
    {
          $request=file_get_contents('php://input');
        $arr = [];
      // $request='appId=1430218141631672&channelOrderNo=4009192001201607058398399184&deviceType=01&funcode=N001&mhtCharset=UTF-8&mhtCurrencyType=156&mhtOrderAmt=100&mhtOrderName=1%E5%85%83%E7%A4%BC%E5%8C%85&mhtOrderNo=16070510351270000023&mhtOrderStartTime=20160705103522&mhtOrderType=01&nowPayOrderNo=2301106305700358&payChannelType=13&payConsumerId=oAL-PuH-rBfW3rOyuyvguEH9gqJU&signType=MD5&signature=d1d0388bd77c6b568eeeecb44cded4c0&tradeStatus=A001';
      //  file_put_contents(\Yii::getAlias('@app/web/nowpay1.txt'), $request, FILE_APPEND);
        if($request){
        parse_str($request, $request_form);
           if (\Services::verifySignature($request_form)) {
           // $tradeStatus = $_REQUEST['tradeStatus'];
            $arr = \Core::paraFilter($request_form);
                $tradeStatus = $arr['tradeStatus'];
            if ($tradeStatus) {
                if ($tradeStatus == "A001")        //A001 成功 A002 失败 A003 未知
                {
                    echo "success=Y";
                    return call_user_func($successCallback, $arr);
                } else {
                    echo "success=N";
                    return call_user_func($failCallback, $arr);
                }
            }
        }
        }
        echo "success=N";
        return call_user_func($failCallback, $arr);
    }

}