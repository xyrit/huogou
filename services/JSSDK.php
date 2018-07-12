<?php
namespace app\services;
use app\helpers\Brower;
use Yii;
class JSSDK {
    private $appId;
    private $appSecret;
    private $host;
    private $time;


  public function __construct($appId, $appSecret,$host) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
    $this->host = $host;
    $this->time=time();

  }

  public function getSign(){
    $params=$this->getWxJsConf();

    $signature_str=http_build_query($params)."&url=".$this->host;

    $params['signature'] = sha1($signature_str);
    $params['appId']=$this->appId;
    $params['url']=$this->host;
    //  unset($params['jsapi_ticket']);
    $data['status']='1';
    $data['info']=$params;
    return $data;
  }
  /**
   * 获取微信JS配置
   */
  public function getWxJsConf(){
    //获取tiket
    $jsapi_ticket = $this->wxjsapitiket();
    //获取随即串
    $noncestr = $this->createNonceStr();
    //拼合返回值
    return array(
        'jsapi_ticket'=>$jsapi_ticket,
        'noncestr'=>$noncestr,
        'timestamp'=>$this->time,
    );

  }
  /**
   * 获取微信JS中需要的Ticket
   */
  private function wxjsapitiket(){

      if(Brower::whereFrom()==1)
      {
          $key = 'wx_jsapi_tiket1';
      }else{
          $key = 'wx_jsapi_tiket2';
      }
    $tiket =  Yii::$app->cache->get($key);
    if(!$tiket || $this->time > $tiket['my_expire_time']){
      $token = $this->wxjstoken();
      $url ='https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$token.'&type=jsapi';
      $tiket = json_decode($this->httpGet($url),true);
      if($tiket['errcode'] == '0'){
        $tiket['my_expire_time'] = $this->time + $tiket['expires_in'] - 100;
        Yii::$app->cache->set($key,$tiket,$tiket['expires_in'] - 100);
      }
    }
    return $tiket['ticket'];
  }

  /**
   * 获取微信JS中需要的Access Token
   */
  private function wxjstoken(){
      if(Brower::whereFrom()==1)
      {
          $key = 'wx_jsapi_token1';
      }else{
          $key = 'wx_jsapi_token2';
      }


    $token =  Yii::$app->cache->get($key);
    if(!$token || $this->time > $token['my_expire_time']){
      $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appId.'&secret='.$this->appSecret;
      $token = json_decode( $this->httpGet($url),true);

      if($token['access_token']){
        $token['my_expire_time'] = $this->time + $token['expires_in'] - 100;
        Yii::$app->cache->set($key,$token,$token['expires_in'] -100);
      }
    }
    return $token['access_token'];
  }


  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
    // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}

