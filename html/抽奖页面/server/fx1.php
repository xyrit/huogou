<?php
/**
 * Created by PhpStorm.
 * User: Coder-Jun
 * Date: 2015/12/2
 * Time: 10:18
 */

 function randCode($length = 5, $type = 0) {
    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } elseif ($type == "-1") {
        $string = implode("", $arr);	
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $string[rand(0, $count)];
    }
    return $code;
 }
 $lm=randCode(12,-1);

$fxUrl = "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?";
//$desc = "这个果然是真的，昨天抽到了20元现金，现在真的到账了";

$desc = "又中50话费,活动就要结束了,速度抢,别忘了给我点个赞！";


$_wv = "1027";
$height = "70";
$imageUrl = "http://i5.tietuku.com/eb09e644efda25b5.jpg";
$site = "年末送好礼";
$style = "102";
$url = "http://www.xxxx.com/";
$width = "145";
$num=rand(10,100);
$summary = "点击进入活动页面,中奖率高达90%";
$title = "迎新年，庆元旦，豪礼大放送！";
//$t="1449022626";
//http://i5.tietuku.com/eb09e644efda25b5.jpg 元旦送礼
//http://diermoshi.b0.upaiyun.com/choujiang3/image/373138113.jpg 手机图片


$params = "desc={$desc}&url={$url}&summary={$summary}&title={$title}&pics={$imageUrl}&style={$style}";
$demourl = "http://qzs.qzone.qq.com/open/connect/widget/mobile/qzshare/index.html?_wv=1027&desc=%E8%BF%99%E4%B8%AA%E6%9E%9C%E7%84%B6%E6%98%AF%E7%9C%9F%E7%9A%84%EF%BC%8C%E6%98%A8%E5%A4%A9%E6%8A%BD%E5%88%B0%E4%BA%8656.8%E5%85%83%E7%BA%A2%E5%8C%85%EF%BC%8C%E7%8E%B0%E5%9C%A8%E7%9C%9F%E7%9A%84%E5%88%B0%E8%B4%A6%E4%BA%86&height=30&imageUrl=http://ugc.qpic.cn/adapt/0/e5f1bfa7-a90d-2aec-90d3-05b79cb39810/200&site=%E5%A5%96%E5%93%81%E5%A4%A7%E6%94%BE%E9%80%81&style=102&summary=%E7%82%B9%E5%87%BB%E9%A2%86%E5%8F%96%E7%BA%A2%E5%8C%85&title=%E5%BF%AB%E6%9D%A5%E5%91%80%EF%BC%8C%E5%8F%AA%E6%9C%89%E4%B8%89%E6%AC%A1%E6%9C%BA%E4%BC%9A%E5%93%A6%EF%BC%8C%E6%B2%A1%E6%8A%BD%E7%9A%84%E9%83%BD%E6%9D%A5%E5%90%A7&url=http%3A%2F%2Fsc.qq.com%2Ffx%2Ft%3Fr%3Ds299MqA%26t%3D1449022626&width=145&t=1449022626";
header("Location:" . $fxUrl . $params);
//header("Location:" . $demourl);

