<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/8
 * Time: 10:12
 * 聚合礼品卡接口
 */
namespace app\components;
use yii;
use yii\base\Component;
use app\models\JdcardList;
class Jdcard extends Component
{

    /*
     * 礼品卡列表
     */
    public $key;
    public $listurl;
    public $pulllisturl;
    public $orderinfourl;
    public $oldlisturl;
    public $dtype;
    public $denomination=['1000'=>100022,'10'=>'200023','20'=>200024,'50'=>200025,'100'=>200026,'200'=>200027,'500'=>200029,'800'=>200030,'5'=>200022];               // 面额=>id
    public $num;
    public $username;
    public function cardlist($type='jd')
    {
            $typename='京东E卡';


        $url=$this->listurl;
        $data=[
            'dtype'=>$this->dtype,
            'key'=>$this->key
        ];
        $arr_jd=[];
        $result=$this->http($url,$data);

      //  $encode = mb_detect_encoding($result, array("ASCII","UTF-8","GB2312","GBK"));

        $arr=json_decode($result,1);

        if($arr['reason']=='查询成功')
        {
            foreach($arr['result'] as $row)
            {
                if(strpos($row['name'],$typename))
                {
                    $arr_jd[]=$row;
                }

            }
     
            return $arr_jd;
        }
    }

    /*
     * 获取礼品卡
     */
    public function pullcart($money)
    {
        $denomination=$this->denomination;
        if($denomination[$money])
        {
        $url=$this->pulllisturl;
        $data=[
            'dtype'=>$this->dtype,
            'key'=>$this->key,
            'num'=>$this->num,
            'productId'=>$denomination[$money]
        ];

        $result=$this->http($url,$data,'POST');
            $arr=json_decode($result,1);

        if($arr['reason']=='成功的返回')
        {
            $time=time();
            $card_name='京东卡';
            $uncode=substr(str_pad($this->username, 8, '0'), 0, 8);
            foreach($arr['cards'] as $row)
            {
            $Jdcard=new JdcardList();
            $Jdcard->add_time=$time;
            $Jdcard->cardno=$this->decode($row['cardno'], $uncode);
            $Jdcard->cardpws=$this->decode($row['cardpws'], $uncode);
            $Jdcard->expirationtime=$row['expireDate'];
            $Jdcard->card_id=$denomination[$money];
            $Jdcard->denomination=$money;
            $Jdcard->card_name=$card_name;
            $Jdcard->save();
            }
            $rs['code']=100;
        }
        else
        {
            $rs['result']=$arr['reason'];
            $rs['error_code']=$arr['error_code'];
            return $rs;
        }
    }
    }

    public function cspullcart($money){
        $denomination=$this->denomination;
        if($denomination[$money])
        {
            $time=time();
            $card_name='京东卡';
            $Jdcard=new JdcardList();
            $Jdcard->add_time=$time;
            $Jdcard->cardno='1111111111';
            $Jdcard->cardpws='1111111111';
            $Jdcard->expirationtime=$row['expireDate'];
            $Jdcard->card_id=$denomination[$money];
            $Jdcard->denomination=$money;
            $Jdcard->card_name=$card_name;
            $Jdcard->save();

        }
    }



    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string  $url    请求URL
     * @param  array   $params 请求参数
     * @param  string  $method 请求方法GET/POST
     * @param  boolean $ssl    是否进行SSL双向认证
     * @return array   $data   响应数据

     */
    private function http($url, $params = array(), $method = 'GET'){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        );
        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $getQuerys = !empty($params) ? '?'. http_build_query($params) : '';
                $opts[CURLOPT_URL] = $url . $getQuerys;
                break;
            case 'POST':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
        }
        /* nodejs 控制台输出日志 */
     //   $CSdata = ($method == 'POST' ? json_decode($params, true) : '');
        //  K($opts[CURLOPT_URL], $CSdata);      7.10修改
        /* 初始化并执行curl请求 */
        $ch     = curl_init();
        curl_setopt_array($ch, $opts);
        $data   = curl_exec($ch);
        $err    = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        if ($err > 0) {
            $this->error = $errmsg;
            return false;
        }else {
            return $data;
        }
    }

    public function encode($str, $key) {
        $key = substr($key, 0, 8);
        $iv = $key;
        $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_ECB );
        $str = $this->pkcs5Pad ( $str, $size );
        $s = mcrypt_encrypt(MCRYPT_DES,$key,$str,MCRYPT_MODE_ECB,$iv);
        return base64_encode($s);
    }

    /**
     * 解密
     * @param  string $str 待解密的字符串
     * @param  string $key 密码
     * @return string
     */
    public function decode($str, $key) {
        $iv = $key;
        $str = base64_decode($str);
        $str = mcrypt_decrypt( MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB, $iv );
        $str = $this->pkcs5Unpad( $str );
        return $str;
    }

    public function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

    public function pkcs5Unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }
}

