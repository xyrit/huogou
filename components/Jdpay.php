<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/26
 * Time: 下午3:37
 */

namespace app\components;

use yii\base\Component;

class Jdpay extends Component
{

//======================= 商户开通的商户号
    public $pcMerchantNum;
    public $wapMerchantNum;

//======================= 商户DES密钥
    public $desKey;

//=======================商户MD5密钥
    public $md5Key;

//=======================网银支付服务地址
    public $serverPayUrl = 'https://plus.jdpay.com/nPay.htm';

    public $wapServerPayUrl = 'https://m.jdpay.com/wepay/web/pay';

//=======================网银查询服务地址
    public $serverQueryUrl = 'https://m.jdpay.com/wepay/query';

//=======================网银退款服务地址
    public $serverRefundUrl = 'https://m.jdpay.com/wepay/refund';


    public function pay($no, $price, $notifyUrl, $successCallbackUrl, $remark, $noDesc, $noName, $noTime, $token = '')
    {
        $version = '1.1.5';
        $ip = \Yii::$app->request->userIP;
        $param = [];
        $param["currency"] = 'CNY';//
        $param["ip"] = $ip;
        $param["merchantNum"] = $this->pcMerchantNum;//商户号
        $param["merchantRemark"] = $remark;//商户备注
        $param["notifyUrl"] = $notifyUrl;
        $param["successCallbackUrl"] = $successCallbackUrl;
        $param["tradeAmount"] = $price;//交易金额
        $param["tradeDescription"] = $noDesc;//交易描述
        $param["tradeName"] = $noName;//交易名称
        $param["tradeNum"] = $no;//交易号
        $param["tradeTime"] = $noTime;//交易时间
        $param["version"] = $version;
        $param["token"] = $token;//令牌

        $sign = SignUtil::signWithoutToHex($param,SignUtil::$pcUnSignKeyList);
        $param["merchantSign"] = $sign;

        $html = $this->createPaySubmitHtml($param);
        return $html;
    }

    public function wapPay($no, $price, $notifyUrl, $successCallbackUrl,$failCallbackUrl, $remark, $noDesc, $noName, $noTime, $token = '')
    {
        $version = '2.0';
        $param = [];
        $param["currency"] = 'CNY';//
        $param["failCallbackUrl"] = $failCallbackUrl;
        $param["merchantNum"] = $this->wapMerchantNum;//商户号
        $param["merchantRemark"] = $remark;//商户备注
        $param["notifyUrl"] = $notifyUrl;
        $param["successCallbackUrl"] = $successCallbackUrl;
        $param["tradeAmount"] = $price;//交易金额
        $param["tradeDescription"] = $noDesc;//交易描述
        $param["tradeName"] = $noName;//交易名称
        $param["tradeNum"] = $no;//交易号
        $param["tradeTime"] = $noTime;//交易时间
        $param["version"] = $version;
        $param["token"] = $token;//令牌

        $sign = SignUtil::sign($param,SignUtil::$wapUnSignKeyList);
        $param["merchantSign"] = $sign;

        $desUtils  = new DesUtils();
        $key = $this->desKey;
        $param["merchantRemark"] = $desUtils->encrypt($param["merchantRemark"],$key);
        $param["tradeNum"] =$desUtils->encrypt($param["tradeNum"],$key);
        $param["tradeName"] = $desUtils->encrypt($param["tradeName"],$key);
        $param["tradeDescription"] = $desUtils->encrypt($param["tradeDescription"],$key);
        $param["tradeTime"] =$desUtils->encrypt($param["tradeTime"],$key);
        $param["tradeAmount"] = $desUtils->encrypt($param["tradeAmount"],$key);
        $param["currency"] = $desUtils->encrypt($param["currency"],$key);
        $param["notifyUrl"] = $desUtils->encrypt($param["notifyUrl"],$key);
        $param["successCallbackUrl"] = $desUtils->encrypt($param["successCallbackUrl"],$key);
        $param["failCallbackUrl"] =$desUtils->encrypt($param["failCallbackUrl"],$key);

        $html = $this->createWapPaySubmitHtml($param);
        return $html;

    }

    public function notify($successCallback, $failCallback)
    {
        $request = \Yii::$app->request;
        $resp = $request->post('resp');
        $w = new WebAsynNotificationCtrl ();
        return $w->execute($this->md5Key, $this->desKey, $resp, $successCallback, $failCallback);
    }

    public function createPaySubmitHtml($tradeInfo)
    {

        $serverPayUrl = $this->serverPayUrl;
        $html = <<<EOF
        <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>支付跳转中...</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0, user-scalable=no"/>
</head>
<body  onload="javascript:document.E_FORM.submit()">

    <div>支付跳转中...</div>
    <form method="post" name="E_FORM" action="$serverPayUrl" >
        <!--交易信息 start-->
        <input type="hidden" name="version" value="{$tradeInfo['version']}"/>
        <input type="hidden" name="token" value="{$tradeInfo['token']}"/>
        <input type="hidden" name="merchantSign" value="{$tradeInfo['merchantSign']}"/>
        <input type="hidden" name="merchantNum" value="{$tradeInfo['merchantNum']}"/>
        <input type="hidden" name="merchantRemark" value="{$tradeInfo['merchantRemark']}"/>
        <input type="hidden" name="tradeNum" value="{$tradeInfo['tradeNum']}"/>
        <input type="hidden" name="tradeName" value="{$tradeInfo['tradeName']}"/>
        <input type="hidden" name="tradeDescription" value="{$tradeInfo['tradeDescription']}"/>
        <input type="hidden" name="tradeTime" value="{$tradeInfo['tradeTime']}"/>
        <input type="hidden" name="tradeAmount" value="{$tradeInfo['tradeAmount']}"/>
        <input type="hidden" name="currency" value="{$tradeInfo['currency']}"/>
        <input type="hidden" name="notifyUrl" value="{$tradeInfo['notifyUrl']}"/>
        <input type="hidden" name="successCallbackUrl" value="{$tradeInfo['successCallbackUrl']}"/>
        <input type="hidden" name="ip" value="{$tradeInfo['ip']}"/>
    </form>

</body>
</html>
EOF;
        return $html;
    }

    public function createWapPaySubmitHtml($tradeInfo)
    {
        $serverPayUrl = $this->wapServerPayUrl;
        $html = <<<EOF
        <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>支付跳转中...</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0, user-scalable=no"/>
</head>
<body  onload="javascript:document.E_FORM.submit()">

    <div>支付跳转中...</div>
    <form method="post" name="E_FORM" action="$serverPayUrl" >
        <!--交易信息 start-->
        <input type="hidden" name="version" value="{$tradeInfo['version']}"/>
        <input type="hidden" name="token" value="{$tradeInfo['token']}"/>
        <input type="hidden" name="merchantSign" value="{$tradeInfo['merchantSign']}"/>
        <input type="hidden" name="merchantNum" value="{$tradeInfo['merchantNum']}"/>
        <input type="hidden" name="merchantRemark" value="{$tradeInfo['merchantRemark']}"/>
        <input type="hidden" name="tradeNum" value="{$tradeInfo['tradeNum']}"/>
        <input type="hidden" name="tradeName" value="{$tradeInfo['tradeName']}"/>
        <input type="hidden" name="tradeDescription" value="{$tradeInfo['tradeDescription']}"/>
        <input type="hidden" name="tradeTime" value="{$tradeInfo['tradeTime']}"/>
        <input type="hidden" name="tradeAmount" value="{$tradeInfo['tradeAmount']}"/>
        <input type="hidden" name="currency" value="{$tradeInfo['currency']}"/>
        <input type="hidden" name="notifyUrl" value="{$tradeInfo['notifyUrl']}"/>
        <input type="hidden" name="successCallbackUrl" value="{$tradeInfo['successCallbackUrl']}"/>
        <input type="hidden" name="failCallbackUrl" value="{$tradeInfo['failCallbackUrl']}"/>
    </form>

</body>
</html>
EOF;
        return $html;
    }


}

class WebAsynNotificationCtrl
{

    public function xml_to_array($xml)
    {
        $array = ( array )(simplexml_load_string($xml));
        foreach ($array as $key => $item) {
            $array [$key] = $this->struct_to_array(( array )$item);
        }
        return $array;
    }

    public function struct_to_array($item)
    {
        if (!is_string($item)) {
            $item = ( array )$item;
            foreach ($item as $key => $val) {
                $item [$key] = $this->struct_to_array($val);
            }
        }
        return $item;
    }

    /**
     * 签名
     */
    public function generateSign($data, $md5Key)
    {
        $sb = $data ['VERSION'] [0] . $data ['MERCHANT'] [0] . $data ['TERMINAL'] [0] . $data ['DATA'] [0] . $md5Key;

        return md5($sb);
    }

    public function execute($md5Key, $desKey, $resp, $successCallback, $failCallback)
    {
        // 获取通知原始信息
        if (null == $resp) {
            return call_user_func($failCallback, $resp);
        }

        // 解析XML
        $params = $this->xml_to_array(base64_decode($resp));

        $ownSign = $this->generateSign($params, $md5Key);
        $params_json = json_encode($params);

        if ($params ['SIGN'] [0] != $ownSign) {
            return call_user_func($failCallback, $resp);
        }
        // 验签成功，业务处理
        // 对Data数据进行解密
        $des = new DesUtils (); // （秘钥向量，混淆向量）
        $decryptArr = $des->decrypt($params ['DATA'] [0], $desKey); // 加密字符串
        $params ['xmlData'] = $decryptArr;
        $params['data'] = $this->xml_to_array($params['xmlData']);
        return call_user_func($successCallback, $params);
    }
}

/**
 * byte数组与字符串转化类
 */
class ByteUtils
{

    /**
     *
     *
     *
     *
     * 转换一个String字符串为byte数组
     *
     * @param $str 需要转换的字符串
     *
     * @param $bytes 目标byte数组
     *
     *
     *
     */
    public static function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes [] = ord($string [$i]);
        }
        return $bytes;
    }

    /**
     *
     *
     *
     *
     * 转换一个16进制hexString字符串为十进制byte数组
     *
     * @param $hexString 需要转换的十六进制字符串
     * @return 一个byte数组
     *
     */
    public static function hexStrToBytes($hexString)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($hexString) - 1; $i += 2) {
            $bytes [$i / 2] = hexdec($hexString [$i] . $hexString [$i + 1]) & 0xff;
        }

        return $bytes;
    }

    public static function ascToHex($asc, $AscLen)
    {
        $i = 0;
        $Hex = array();
        for ($i = 0; 2 * $i < $AscLen; $i++) {

            /* A:0x41(0100 0001),a:0x61(0110 0001),右移4位后都是0001,加0x90等0xa */
            $Hex [$i] = (chr($asc [2 * $i]) << 4);
            if (!(chr($asc [2 * $i]) >= '0' && chr($asc [2 * $i]) <= '9')) {
                $Hex [$i] += 0x90;
            }

            if (2 * $i + 1 >= $AscLen) {
                break;
            }

            $Hex [$i] |= (chr($asc [2 * $i + 1]) & 0x0f);
            if (!(chr($asc [2 * $i + 1]) >= '0' && chr($asc [2 * $i + 1]) <= '9')) {
                $Hex [$i] += 0x09;
            }
        }
        return $Hex;
    }

    /**
     *
     *
     *
     *
     * 将十进制字符串转换为十六进制字符串
     *
     * @param $string 需要转换字符串
     * @return 一个十六进制字符串
     *
     */
    public static function strToHex($string)
    {
        $hex = "";
        for ($i = 0; $i < strlen($string); $i++) {
            $tmp = dechex(ord($string [$i]));
            if (strlen($tmp) == 1) {
                $hex .= "0";
            }
            $hex .= $tmp;
        }
        $hex = strtolower($hex);
        return $hex;
    }

    public static function strToBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes [] = ord($string [$i]);
        }
        return $bytes;
    }

    /**
     *
     *
     *
     *
     * 将字节数组转化为String类型的数据
     *
     * @param $bytes 字节数组
     *
     * @param $str 目标字符串
     *
     * @return 一个String类型的数据
     *
     */
    public static function toStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }

        return $str;
    }

    // 字符串转16进制
    public static function bytesToHex($bytes)
    {
        $str = ByteUtils::toStr($bytes);
        return ByteUtils::strToHex($str);
    }

    /**
     *
     *
     *
     *
     * 转换一个int为byte数组
     *
     * @param $byt 目标byte数组
     *
     * @param $val 需要转换的字符串
     *
     *
     *
     *
     */
    public static function integerToBytes($val)
    {
        $byt = array();
        $byt [0] = ($val >> 24 & 0xff);
        $byt [1] = ($val >> 16 & 0xff);
        $byt [2] = ($val >> 8 & 0xff);
        $byt [3] = ($val & 0xff);
        return $byt;
    }

    /**
     *
     *
     *
     *
     * 从字节数组中指定的位置读取一个Integer类型的数据
     *
     * @param $bytes 字节数组
     *
     * @param $position 指定的开始位置
     *
     * @return 一个Integer类型的数据
     *
     *
     */
    public static function bytesToInteger($bytes, $position)
    {
        $val = 0;
        $val = $bytes [$position + 3] & 0xff;
        $val <<= 8;
        $val |= $bytes [$position + 2] & 0xff;
        $val <<= 8;
        $val |= $bytes [$position + 1] & 0xff;
        $val <<= 8;
        $val |= $bytes [$position] & 0xff;
        return $val;
    }

    /**
     * 将byte数组 转换为int
     *
     * @param
     *            b
     * @param
     *            offset 位游方式
     * @return
     *
     *
     */
    public static function byteArrayToInt($b, $offset)
    {
        $value = 0;
        for ($i = 0; $i < 4; $i++) {
            $shift = (4 - 1 - $i) * 8;
            $value = $value + ($b [$i + $offset] & 0x000000FF) << $shift; // 往高位游
        }
        return $value;
    }

    /**
     *
     *
     *
     *
     * 转换一个shor字符串为byte数组
     *
     * @param $byt 目标byte数组
     *
     * @param $val 需要转换的字符串
     *
     *
     *
     *
     */
    public static function shortToBytes($val)
    {
        $byt = array();
        $byt [0] = ($val & 0xff);
        $byt [1] = ($val >> 8 & 0xff);
        return $byt;
    }

    /**
     *
     *
     *
     *
     * 从字节数组中指定的位置读取一个Short类型的数据。
     *
     * @param $bytes 字节数组
     *
     * @param $position 指定的开始位置
     *
     * @return 一个Short类型的数据
     *
     *
     */
    public static function bytesToShort($bytes, $position)
    {
        $val = 0;
        $val = $bytes [$position + 1] & 0xFF;
        $val = $val << 8;
        $val |= $bytes [$position] & 0xFF;
        return $val;
    }

    /**
     *
     * @param unknown $hexstr
     * @return Ambigous <string, unknown>
     */
    public static function hexTobin($hexstr)
    {
        $n = strlen($hexstr);
        $sbin = "";
        $i = 0;
        while ($i < $n) {
            $a = substr($hexstr, $i, 2);
            $c = pack("H*", $a);
            if ($i == 0) {
                $sbin = $c;
            } else {
                $sbin .= $c;
            }
            $i += 2;
        }
        return $sbin;
    }
}

class DesUtils
{

    public function encrypt($input, $key)
    {
        $key = base64_decode($key);

        $key = $this->pad2Length($key, 8);

        $size = mcrypt_get_block_size('des', 'ecb');
        $input = $this->pkcs5_pad($input, $size);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    public function decrypt($encrypted, $key)
    {
        $encrypted = base64_decode($encrypted);
        $key = base64_decode($key);
        $key = $this->pad2Length($key, 8);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        // 使用MCRYPT_DES算法,cbc模式
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        // 初始处理
        $decrypted = mdecrypt_generic($td, $encrypted);
        // 解密
        mcrypt_generic_deinit($td);
        // 结束
        mcrypt_module_close($td);
        $y = $this->pkcs5_unpad($decrypted);
        return $y;
    }

    function pad2Length($text, $padlen)
    {
        $len = strlen($text) % $padlen;
        $res = $text;
        $span = $padlen - $len;
        for ($i = 0; $i < $span; $i++) {
            $res .= chr($span);
        }
        return $res;
    }

    function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, -1 * $pad);
    }
}


class SignUtil
{

    public static $pcUnSignKeyList = array(
        "merchantSign",
        "version",
        "successCallbackUrl",
        "forPayLayerUrl"
    );

    public static $wapUnSignKeyList = array(
        "merchantSign",
        "token",
        "version"
    );

    public static function signWithoutToHex($params,$unSignKeyList)
    {
        ksort($params);
        $sourceSignString = SignUtil::signString($params, $unSignKeyList);
        $sha256SourceSignString = hash("sha256", $sourceSignString, true);
        return RSAUtils::encryptByPrivateKey($sha256SourceSignString);
    }

    public static function sign($params,$unSignKeyList)
    {
        ksort($params);
        $sourceSignString = SignUtil::signString($params, $unSignKeyList);
        $sha256SourceSignString = hash("sha256", $sourceSignString);
        return RSAUtils::encryptByPrivateKey($sha256SourceSignString);
    }

    public static function signString($params, $unSignKeyList)
    {

        // 拼原String
        $sb = "";
        // 删除不需要参与签名的属性
        foreach ($params as $k => $arc) {
            for ($i = 0; $i < count($unSignKeyList); $i++) {

                if ($k == $unSignKeyList [$i]) {
                    unset ($params [$k]);
                }
            }
        }

        foreach ($params as $k => $arc) {

            $sb = $sb . $k . "=" . ($arc == null ? "" : $arc) . "&";
        }
        // 去掉最后一个&
        $sb = substr($sb, 0, -1);

        return $sb;
    }
}

class RSAUtils
{

    public static function encryptByPrivateKey($data)
    {
        $priKeyFile = \Yii::getAlias('@app/components/jdpay/my_rsa_private_pkcs8_key.pem');
        $pi_key = openssl_pkey_get_private(file_get_contents($priKeyFile));//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $encrypted = "";
        openssl_private_encrypt($data, $encrypted, $pi_key, OPENSSL_PKCS1_PADDING);//私钥加密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    public static function decryptByPublicKey($data)
    {
        $puKeyFile = \Yii::getAlias('@app/components/jdpay/wy_rsa_public_key.pem');
        $pu_key = openssl_pkey_get_public(file_get_contents($puKeyFile));//这个函数可用来判断公钥是否是可用的，可用返回资源id Resource id
        $decrypted = "";
        $data = base64_decode($data);
        openssl_public_decrypt($data, $decrypted, $pu_key);//公钥解密
        return $decrypted;
    }


}