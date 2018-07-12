<?php
/**
 * Created by PhpStorm.
 * User: hui
 * Date: 16/06/22
 * Time: 下午3:37
 * 微信企业支付
 */

namespace app\components;

use yii\base\Component;
use app\models\WxOrderDistribution;

class Wxpay extends Component
{

//======================= 商户开通的商户号
    public $mchid;

//=======================商户签名密钥
    public $signKey;
//====================== 商户关联的appid
    public $mch_appid;
//=======================企业付款给个人付款地址
    public $serverPayUrl = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';


    public static $err_code=array(
        'NOAUTH'=>'没有权限',
        'AMOUNT_LIMIT'=>'付款金额不能小于最低限额',
        'PARAM_ERROR'=>'参数错误',
        'OPENID_ERROR'=>'Openid错误',
        'NOTENOUGH'=>'余额不足',
        'SYSTEMERROR'=>'系统繁忙，请稍后再试。',
        'NAME_MISMATCH'=>'姓名校验出错',
        'SIGN_ERROR'=>'签名错误',
        'XML_ERROR'=>'Post内容出错',
        'FATAL_ERROR'=>'两次请求参数不一致',
        'CA_ERROR'=>'证书出错',
        'FREQ_LIMIT'=>'受频率限制',
    );

    /**
     * @param $partner_trade_no  商户订单号
     * @param $amount           金额，单位为分
     * @param $re_user_name     收款用户真实姓名。如果check_name设置为FORCE_CHECK或OPTION_CHECK，则必填用户真实姓名
     * @param $nonce_str      随机字符串
     * @param $desc      企业付款描述信息
     * @param $spbill_create_ip   调用接口的机器 ip地址
     * $param $openid    该公众号 对应的openid
     * @param string $check_name    校验用户姓名选项 NO_CHECK：不校验真实姓名
                                                FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）
                                                OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功
     */
    public function pay($partner_trade_no,$amount,$re_user_name,$desc,$openid,$homeid,$userid){




        $dataArr=[];
        if ($re_user_name) {
            $dataArr['re_user_name']=$re_user_name;
            $check_name='OPTION_CHECK';
        } else {
            $check_name='NO_CHECK';
        }
        $id=WxOrderDistribution::generateOrderId($homeid);

        $dataArr['amount']=$amount=$amount*100;
        $dataArr['check_name']=$check_name;
        $dataArr['desc']=$desc;
        $dataArr['mch_appid']=$this->mch_appid;
        $dataArr['mchid']=$this->mchid;
        $dataArr['nonce_str']=$nonce_str=$this->rand_str(); //随机串
        $dataArr['openid']=$openid;
        $dataArr['partner_trade_no']=$id;       //微信记录表id
        $dataArr['spbill_create_ip']=$spbill_create_ip=\Yii::$app->request->userIp;
        $sign=$this->getSign($dataArr);


        $model = new WxOrderDistribution($homeid);
        //$model->id = $partner_trade_no;   //订单号

        $model->id = $id;
        $model->uid = $userid;
        $model->mchid = $this->mchid;   //商户号
        $model->device_info = $dataArr['nonce_str'];      //设备号
        $model->openid = $openid;
        $model->check_name = $check_name;
        $model->re_user_name = $re_user_name;
        $model->amount =$dataArr['amount'];
        $model->desc = $desc;
        $model->spbill_create_ip = $dataArr['spbill_create_ip'];
        $model->sign = $sign;
        $model->add_time = time();
        $model->status = 0;
        $model->partner_trade_no=$partner_trade_no;
        $rs=$model->save();
        if(!$rs)
        {
            return  array(
                'code'=>'-1',
                'msg'=>'系统繁忙',
                'err_msg'=>'数据库插入失败'
            );
        }

        //插入数据库
        if(!$homeid)
        {
            return  array(
                'code'=>'-1',
                'msg'=>'系统繁忙',
                'err_msg'=>'用户home不存在'
            );
        }

        $data="<xml>
                <mch_appid>".$this->mch_appid."</mch_appid>
                <mchid>".$this->mchid."</mchid>
                <nonce_str>".$nonce_str."</nonce_str>
                <partner_trade_no>".$partner_trade_no."</partner_trade_no>
                <openid>".$openid."</openid>
                <check_name>".$check_name."</check_name>
                <re_user_name>".$re_user_name."</re_user_name>
                <amount>".$amount."</amount>
                <desc>".$desc."</desc>
                <spbill_create_ip>".$spbill_create_ip."</spbill_create_ip>
                <sign>".$sign."</sign>
                </xml>";

        $return=$this->curl_post_ssl($this->serverPayUrl,$data);
        $res = @simplexml_load_string($return,NULL,LIBXML_NOCDATA);
        $res = json_decode(json_encode($res),true);
        $savemodel= WxOrderDistribution::findByTableId($homeid)->where(['id' =>$id])->one();
       if($res['return_code']=='SUCCESS' && $res['result_code']=='SUCCESS'){

           //更新表Customer
         // $model = WxOrderDistribution::findOne($partner_trade_no);
           $savemodel->return_code =  $res['return_code'];
           $savemodel->return_msg = $res['return_msg'];
           $savemodel->payment_no =  $res['payment_no'];
           $savemodel->payment_time = $res['payment_time'];
           $savemodel->openid = $openid;
           $savemodel->check_name = $check_name;
           $savemodel->re_user_name = $re_user_name;

           $savemodel->status = 1;
          $rs1= $savemodel->save();
           if(!$rs1)
           {
               return  array(
                   'code'=>'-1',
                   'msg'=>'系统繁忙',
                   'err_msg'=>'数据库修改失败'
               );
           }
            return array(
                'code'=>1,
                'partner_trade_no'=>$res['partner_trade_no'],
                'payment_no'=>$res['payment_no'],
                'payment_time'=>$res['payment_time'],
            );
       }elseif($res['result_code']=='FAIL'){
           //更新表
          // $model = WxOrderDistribution::findOne($partner_trade_no);
           $savemodel->return_code =  $res['err_code'];
           $savemodel->return_msg = $res['err_code_des'];
           $savemodel->openid = $openid;
           $savemodel->check_name = $check_name;
           $savemodel->re_user_name = $re_user_name;
           $rs1= $savemodel->save();
           if(!$rs1)
           {
               return  array(
                   'code'=>'-1',
                   'msg'=>'系统繁忙',
                   'err_msg'=>'数据库修改失败'
               );
           }
            return  array(
                'code'=>'-1',
                'msg'=>isset(self::$err_code[$res['err_code']])?self::$err_code[$res['err_code']]:'系统繁忙',
                'err_msg'=>$res['err_code_des']
            );
       }

    }

   public  function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSLCERT,__DIR__ . '/wxpay/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEY,__DIR__ . '/wxpay/apiclient_key.pem');

        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            curl_close($ch);
            return false;
        }
    }


    /**
     * 	作用：格式化参数，签名过程需要使用
     */
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {

        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }

        return $reqPar;
    }

    /**
     * 	作用：生成签名
     */
    public function getSign($Obj)
    {

        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);

        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->signKey;

        //签名步骤三：MD5加密
        $String = md5($String);

        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);

        return $result_;
    }

    /**
     * @param int $length
     * @return string  随机字符串
     */
   public function rand_str( $length = 16 ) {
        // 字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $str = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            $str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }

        return $str;
    }


}