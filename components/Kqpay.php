<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/29
 * Time: 下午5:44
 */
namespace app\components;

use yii\base\Component;

class Kqpay extends Component
{

    public $merchantAcctId;
    public $payUrl = 'https://www.99bill.com/gateway/recvMerchantInfoAction.htm';

    public function pay($productName,$orderId,$orderAmount,$orderTime,$pageUrl,$bgUrl,$ext1,$ext2)
    {
        //人民币网关账号，该账号为11位人民币网关商户编号+01,该参数必填。
        $merchantAcctId = $this->merchantAcctId;
        //编码方式，1代表 UTF-8; 2 代表 GBK; 3代表 GB2312 默认为1,该参数必填。
        $inputCharset = "1";
        //接收支付结果的页面地址，该参数一般置为空即可。
//        $pageUrl = "";
        //服务器接收支付结果的后台地址，该参数务必填写，不能为空。
//        $bgUrl = "http://219.233.173.50:8802/futao/rmb_demo/recieve.php";
        //网关版本，固定值：v2.0,该参数必填。
        $version =  "v2.0";
        //语言种类，1代表中文显示，2代表英文显示。默认为1,该参数必填。
        $language =  "1";
        //签名类型,该值为4，代表PKI加密方式,该参数必填。
        $signType =  "4";
        //支付人姓名,可以为空。
        $payerName= "";
        //支付人联系类型，1 代表电子邮件方式；2 代表手机联系方式。可以为空。
        $payerContactType =  "";
        //支付人联系方式，与payerContactType设置对应，payerContactType为1，则填写邮箱地址；payerContactType为2，则填写手机号码。可以为空。
        $payerContact =  "";
        //商户订单号，以下采用时间来定义订单号，商户可以根据自己订单号的定义规则来定义该值，不能为空。
//        $orderId = date("YmdHis");
        //订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试。该参数必填。
//        $orderAmount = "1";
        //订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101，不能为空。
        $orderTime = date("YmdHis",$orderTime);
        //商品名称，可以为空。
//        $productName= "苹果";
        //商品数量，可以为空。
        $productNum = "";
        //商品代码，可以为空。
        $productId = "";
        //商品描述，可以为空。
        $productDesc = "";
        //扩展字段1，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
//        $ext1 = "";
        //扩展自段2，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
//        $ext2 = "";
        //支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10，必填。
        $payType = "00";
        //银行代码，如果payType为00，该值可以为空；如果payType为10，该值必须填写，具体请参考银行列表。
        $bankId = "";
        //同一订单禁止重复提交标志，实物购物车填1，虚拟产品用0。1代表只能提交一次，0代表在支付不成功情况下可以再提交。可为空。
        $redoFlag = "";
        //快钱合作伙伴的帐户号，即商户编号，可为空。
        $pid = "";
        // signMsg 签名字符串 不可空，生成加密签名串

        $params = [];
        $params['inputCharset'] = $inputCharset;
        $params['pageUrl'] = $pageUrl;
        $params['bgUrl'] = $bgUrl;
        $params['version'] = $version;
        $params['language'] = $language;
        $params['signType'] = $signType;
        $params['merchantAcctId'] = $merchantAcctId;
        $params['payerName'] = $payerName;
        $params['payerContactType'] = $payerContactType;
        $params['payerContact'] = $payerContact;
        $params['orderId'] = $orderId;
        $params['orderAmount'] = $orderAmount;
        $params['orderTime'] = $orderTime;
        $params['productName'] = $productName;
        $params['productNum'] = $productNum;
        $params['productId'] = $productId;
        $params['productDesc'] = $productDesc;
        $params['ext1'] = $ext1;
        $params['ext2'] = $ext2;
        $params['payType'] = $payType;
        $params['bankId'] = $bankId;
        $params['redoFlag'] = $redoFlag;
        $params['pid'] = $pid;
        $kq_all_para = '';
        foreach($params as $key=>$val) {
            if ($val) {
                $kq_all_para .= $key.'='.$val.'&';
            }
        }

        $kq_all_para=substr($kq_all_para,0,strlen($kq_all_para)-1);

        /////////////  RSA 签名计算 ///////// 开始 //
        $fp = fopen(\Yii::getAlias('@app/components/kqpay/99bill-rsa.pem'), "r");
        $priv_key = fread($fp, 123456);
        fclose($fp);
        $pkeyid = openssl_get_privatekey($priv_key);

        // compute signature
        openssl_sign($kq_all_para, $signMsg, $pkeyid,OPENSSL_ALGO_SHA1);

        // free the key from memory
        openssl_free_key($pkeyid);

        $signMsg = base64_encode($signMsg);
        /////////////  RSA 签名计算 ///////// 结束 //

        $params['signMsg'] = $signMsg;

        return $this->createPaySubmitHtml($params);

    }

    public function notify($successCallback, $failCallback,$redirectUrl)
    {
        function kq_ck_null($kq_va,$kq_na){if($kq_va == ""){return $kq_va="";}else{return $kq_va=$kq_na.'='.$kq_va.'&';}}
        //人民币网关账号，该账号为11位人民币网关商户编号+01,该值与提交时相同。
        $kq_check_all_para=kq_ck_null($_REQUEST["merchantAcctId"],'merchantAcctId');
        //网关版本，固定值：v2.0,该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["version"],'version');
        //语言种类，1代表中文显示，2代表英文显示。默认为1,该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["language"],'language');
        //签名类型,该值为4，代表PKI加密方式,该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["signType"],'signType');
        //支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10,该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["payType"],'payType');
        //银行代码，如果payType为00，该值为空；如果payType为10,该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["bankId"],'bankId');
        //商户订单号，,该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["orderId"],'orderId');
        //订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101,该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["orderTime"],'orderTime');
        //订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试,该值与支付时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["orderAmount"],'orderAmount');
        // 快钱交易号，商户每一笔交易都会在快钱生成一个交易号。
        $kq_check_all_para.=kq_ck_null($_REQUEST["dealId"],'dealId');
        //银行交易号 ，快钱交易在银行支付时对应的交易号，如果不是通过银行卡支付，则为空
        $kq_check_all_para.=kq_ck_null($_REQUEST["bankDealId"],'bankDealId');
        //快钱交易时间，快钱对交易进行处理的时间,格式：yyyyMMddHHmmss，如：20071117020101
        $kq_check_all_para.=kq_ck_null($_REQUEST["dealTime"],'dealTime');
        //商户实际支付金额 以分为单位。比方10元，提交时金额应为1000。该金额代表商户快钱账户最终收到的金额。
        $kq_check_all_para.=kq_ck_null($_REQUEST["payAmount"],'payAmount');
        //费用，快钱收取商户的手续费，单位为分。
        $kq_check_all_para.=kq_ck_null($_REQUEST["fee"],'fee');
        //扩展字段1，该值与提交时相同
        $kq_check_all_para.=kq_ck_null($_REQUEST["ext1"],'ext1');
        //扩展字段2，该值与提交时相同。
        $kq_check_all_para.=kq_ck_null($_REQUEST["ext2"],'ext2');
        //处理结果， 10支付成功，11 支付失败，00订单申请成功，01 订单申请失败
        $kq_check_all_para.=kq_ck_null($_REQUEST["payResult"],'payResult');
        //错误代码 ，请参照《人民币网关接口文档》最后部分的详细解释。
        $kq_check_all_para.=kq_ck_null($_REQUEST["errCode"],'errCode');



        $trans_body=substr($kq_check_all_para,0,strlen($kq_check_all_para)-1);
        $MAC=base64_decode($_REQUEST["signMsg"]);

        $fp = fopen(\Yii::getAlias("@app/components/kqpay/99bill.cert.rsa.20340630.cer"), "r");
        $cert = fread($fp, 8192);
        fclose($fp);
        $pubkeyid = openssl_get_publickey($cert);
        $ok = openssl_verify($trans_body, $MAC, $pubkeyid);

        if ($ok == 1) {
            switch($_REQUEST['payResult']){
                case '10':
                    //此处做商户逻辑处理
                    $rtnOK=1;
                    echo "<result>$rtnOK</result><redirecturl>$redirectUrl</redirecturl>";
                    call_user_func($successCallback, $_REQUEST);
                    break;
                default:
                    $rtnOK=0;
                    echo "<result>$rtnOK</result><redirecturl>$redirectUrl</redirecturl>";
                    call_user_func($failCallback, $_REQUEST);
                    break;

            }

        }else{
            $rtnOK=0;
            echo "<result>$rtnOK</result><redirecturl>$redirectUrl</redirecturl>";
            call_user_func($failCallback, $_REQUEST);
        }
        return;
    }

    public function createPaySubmitHtml($tradeInfo)
    {

        $serverPayUrl = $this->payUrl;
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
        <input type="hidden" name="inputCharset" value="{$tradeInfo['inputCharset']}" />
        <input type="hidden" name="pageUrl" value="{$tradeInfo['pageUrl']}" />
        <input type="hidden" name="bgUrl" value="{$tradeInfo['bgUrl']}" />
        <input type="hidden" name="version" value="{$tradeInfo['version']}" />
        <input type="hidden" name="language" value="{$tradeInfo['language']}" />
        <input type="hidden" name="signType" value="{$tradeInfo['signType']}" />
        <input type="hidden" name="signMsg" value="{$tradeInfo['signMsg']}" />
        <input type="hidden" name="merchantAcctId" value="{$tradeInfo['merchantAcctId']}" />
        <input type="hidden" name="payerName" value="{$tradeInfo['payerName']}" />
        <input type="hidden" name="payerContactType" value="{$tradeInfo['payerContactType']}" />
        <input type="hidden" name="payerContact" value="{$tradeInfo['payerContact']}" />
        <input type="hidden" name="orderId" value="{$tradeInfo['orderId']}" />
        <input type="hidden" name="orderAmount" value="{$tradeInfo['orderAmount']}" />
        <input type="hidden" name="orderTime" value="{$tradeInfo['orderTime']}" />
        <input type="hidden" name="productName" value="{$tradeInfo['productName']}" />
        <input type="hidden" name="productNum" value="{$tradeInfo['productNum']}" />
        <input type="hidden" name="productId" value="{$tradeInfo['productId']}" />
        <input type="hidden" name="productDesc" value="{$tradeInfo['productDesc']}" />
        <input type="hidden" name="ext1" value="{$tradeInfo['ext1']}" />
        <input type="hidden" name="ext2" value="{$tradeInfo['ext2']}" />
        <input type="hidden" name="payType" value="{$tradeInfo['payType']}" />
        <input type="hidden" name="bankId" value="{$tradeInfo['bankId']}" />
        <input type="hidden" name="redoFlag" value="{$tradeInfo['redoFlag']}" />
        <input type="hidden" name="pid" value="{$tradeInfo['pid']}" />
    </form>

</body>
</html>
EOF;
        return $html;
    }


}