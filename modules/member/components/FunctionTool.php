<?php
/**
 *功能函数
 */
namespace app\modules\member\components;
//use yii\base\Object;

class FunctionTool
{
    private $charset='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private $codelen=7;
    private $code;
       
    public function getCode(){
        $this->createCode();
        return $this->code;
    }

    private function createCode(){
        $_len = strlen($this->charset)-1;
        for ($i=0;$i<$this->codelen;$i++){
            $this->code .= $this->charset[mt_rand(0, $_len)];
        }
      // $this->code = 'http://t.'.DOMAIN.'/'.$this->code;
        $this->code = $this->code;
    }
    
    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @return mixed
     */
    public static function  get_client_ip($type = 0) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    } 
    
    
    // 不区分大小写的in_array实现
    function in_array_case($value,$array){
        return in_array(strtolower($value),array_map('strtolower',$array));
    }

}

   



?>