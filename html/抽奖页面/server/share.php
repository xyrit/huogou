<?php

	?>
<!DOCTYPE HTML>
<html lang="en" data-use-rem>
<head>
<meta charset="UTF-8">
<meta content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
<meta itemprop="name" content="年末大放水，免费抽大奖">
<meta itemprop="image" content="http://diermoshi.b0.upaiyun.com/choujiang3/image/373138113.jpg">
<meta name="description" itemprop="description" content="我刚刚抽中了一个奖品，大家快来帮我点赞，满10个赞我就可以拿走了">
<title>年末送好礼，集赞抽大奖</title>
<script src="js/jquery-2.0.3.min.js" tppabs="http://diermoshi.b0.upaiyun.com/choujiang3/js/jquery-2.0.3.min.js"></script>
<script type="text/javascript" src="js/lottery.js" tppabs="http://diermoshi.b0.upaiyun.com/choujiang3/js/lottery.js"></script>
<link rel="stylesheet" href="css/a20150813.css" tppabs="http://diermoshi.b0.upaiyun.com/choujiang3/aa_files/a20150813.css"/>
</head>
<style>
<style>  * {
 margin: 0;
 padding: 0;
 -webkit-box-sizing: border-box;
 -moz-box-sizing: border-box;
 box-sizing: border-box;
}
ul {
	list-style-type: none;
}
.clearfix:after {
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden
}
.clearfix {
 *zoom: 1
}
p {
	margin: 0;
}
body {
	background-color: #7f7f7f;
	font-size: 24px;
	font-family: "Microsoft yahei";
	margin: 0;
}
.wrapper {
	width: 640px;
	background-color: #7f7f7f;
}
.page {
	width: 100%
}
.w_patr1 {
	background: url("images/ok718111469.jpg") no-repeat;
	height: 600px;
	position: relative;
}
.w_part2 .w_czhi {
	width: 378px;
	height: 63px;
	display: inline-block;
	position: absolute;
	top: 10px;
	left: 130px;
}
.w_part3 {
	height: 560px;
	padding-top: 20px;
}
.flash {
	position: relative;
	width: 510px;
	height: 465px;
	margin-left: 62px;
}
#swfcontent {
	width: 510px;
	height: 465px;
	position: relative;
	z-index: 1;
}
#swfcontent-btn {
	display: block;
	position: absolute;
	left: 171px;
	top: 151px;
	z-index: 2;
	width: 175px;
	height: 152px;
}
.w_part3 p.w_fuwu {
	font-size: 20px;
	line-height: 30px;
	height: 30px;
	width: 100%;
	text-align: center;
	padding-top: 10px;
}
.w_part3 p.w_fuwu a {
	color: #fde9c7;
}
.w_part4 {
	height: 500px;
	position: relative;
}
.w_part4 .w_rule {
	width: 510px;
	height: 535px;
	background-color: #c12138;
	margin-left: 62px;
	border-radius: 16px;
	position: relative;
}
.w_part4 p {
	width: 485px;
	color: #fde9c7;
	padding-left: 18px;
	font-size: 22px;
	line-height: 34px;
}
.w_part4 p.w_rulep1 {
	font-size: 32px;
	height: 40px;
	padding: 20px 0 20px 12px;
}
.w_part4 p em {
	width: 36px;
	display: inline-block;
}
.w_star {
	background: url("images/w_star.png") no-repeat;
	width: 63px;
	height: 77px;
	position: absolute;
	z-index: 3;
	right: 33px;
	top: 20px;
}
 @-webkit-keyframes twinkling {
 0% {
 opacity: 0;
}
 50% {
 opacity: 1;
}
 100% {
 opacity: 0;
}
}
.element {
	-webkit-animation: twinkling 3s infinite ease-in-out;
}
.w_tcone {
	width: 389px;
	height: 496px;
	position: relative;
}
.w_tcone .w_guanbi, .w_tctwo .w_guanbi {
	width: 35px;
	height: 35px;
	border-radius: 35px;
	display: inline-block;
	position: absolute;
	right: 0;
	top: 0;
	z-index: 6;
}
.w_num {
	width: 100%;
	height: 56px;
	line-height: 56px;
	color: #fff;
	font-size: 22px;
	padding-top: 73px;
	position: relative;
}
.w_num .w_haoma, .w_yunys .w_haoma {
	width: 160px;
	padding-left: 25px;
}
.w_haoma input {
	width: 160px;
	height: 30px;
	border: none;
	font-size: 22px;
	background: none;
	color: #fff;
}
.w_num span.w_lianxi {
	width: 40px;
	height: 40px;
	display: inline-block;
	position: absolute;
	top: 84px;
	right: 33px;
}
.w_yunys {
	width: 100%;
	height: 56px;
	line-height: 56px;
	color: #dbdcdc;
	font-size: 22px;
	padding-top: 2px;
}
.w_daxiao {
	height: 230px;
	margin-top: 10px;
}
.w_daxiao .w_haoma {
	width: 320px;
	padding-left: 25px;
	color: #fff;
	font-size: 20px;
	height: 30px;
	line-height: 30px;
	padding-top: 16px;
}
.w_daxiao .w_haoma .w_hmspo {
	float: left;
}
.w_daxiao .w_haoma .w_hmspt {
	float: right;
}
.w_daxiao p.w_jiage {
	color: #dbdbdb;
	font-size: 17px;
}
.w_daxiao p.w_jiage span {
	color: #d8c601;
}
.w_daxiao a.w_goumai {
	width: 318px;
	height: 44px;
	border: 1px #eb631d solid;
	color: #9c4306;
	font-size: 28px;
	font-weight: bold;
	border-radius: 12px;
	display: inline-block;
	background-color: #ffea00;
	text-align: center;
	line-height: 42px;
	margin-left: 31px;
	margin-top: 38px;
}
.w_other {
	color: #d9c701;
	font-size: 18px;
	text-align: center;
	padding-top: 30px;
}
.w_other a {
	color: #d9c701;
	font-size: 18px;
}
.w_tctwo {
	width: 380px;
	height: 190px;
	background-color: #dd0931;
	border-radius: 12px;
	position: relative;
}
.w_tctwo .w_num .w_haoma {
	width: 340px;
}
.w_tctwo .w_num .w_haoma .w_xingm {
	width: 136px;
	display: inline-block;
	float: left;
}
.w_tctwo .w_num .w_haoma input {
	background-color: #fff;
	width: 200px;
}
.w_tctnum {
	padding-top: 0;
}
.w_tctwo .w_daxiao {
	height: 150px;
}
.w_tctdiz {
	padding-left: 22px;
	color: #fcece1;
	font-size: 18px;
	line-height: 28px;
}
.w_guanzhu {
	color: #fff;
	font-size: 18px;
	padding-left: 22px;
	padding-top: 12px;
}
.w_guanzhu em {
	width: 18px;
	height: 18px;
	display: inline-block;
}
.w_tctwo a.w_goumai {
	margin-top: 20px;
}
.w_tctwo a.w_guanbi {
	text-decoration: none;
	background-color: #7e060b;
	width: 31px;
	height: 31px;
	border: 2px #fff solid;
	position: absolute;
	right: -14px;
	color: #fff;
	font-size: 34px;
	text-align: center;
	line-height: 30px;
	top: -14px;
	border-radius: 20px;
}
.w_tcthree {
	width: 358px;
	height: 358px;
	text-align: center;
	position: relative;
}
.w_tcthree p.w_chouz {
	color: #ffe48e;
	font-size: 50px;
	font-weight: bold;
	-webkit-text-stroke: 4px #47010e;
	height: 60px;
	line-height: 60px;
	padding-top: 95px;
}
.w_tcthree .w_guanbi {
	position: absolute;
	z-index: 3;
	width: 170px;
	height: 42px;
	border-radius: 12px;
	line-height: 40px;
	display: inline-block;
	color: #fff;
	left: 92px;
	top: 270px;
	background-color: #6a0317;
	text-decoration: none;
}
.w_tcfour {
	width: 379px;
	height: 381px;
	position: relative;
	text-align: center;
}
.w_tcfour p.w_chouz {
	color: #ffe48e;
	font-size: 45px;
	font-weight: bold;
	-webkit-text-stroke: 2px #47010e;
	height: 60px;
	line-height: 60px;
	padding-top: 95px;
}
.w_tcfour .w_guanbi {
	position: absolute;
	z-index: 3;
	width: 170px;
	height: 42px;
	border-radius: 12px;
	line-height: 40px;
	display: inline-block;
	color: #6a0317;
	left: 92px;
	top: 305px;
	background-color: #ffea00;
	text-decoration: none;
}
.w_tcfive {
	width: 365px;
	height: 334px;
	text-align: center;
	position: relative;
}
.w_tcfive p {
	color: #682b03;
	font-size: 34px;
	font-weight: bold;
	padding-top: 45px;
	line-height: 42px;
}
.w_tcfive a.w_guanbi {
	position: absolute;
	width: 160px;
	height: 50px;
	color: #fff;
	background-color: #7a4646;
	line-height: 50px;
	border-radius: 12px;
	top: 164px;
	left: 105px;
	text-decoration: none;
}
.w_tcsix {
	width: 356px;
	height: 313px;
	background-color: #9c182c;
	position: relative;
	text-align: center;
	color: #fff;
}
.w_tcsix p.w_sixpone {
	height: 40px;
	font-size: 34px;
	font-weight: bold;
	line-height: 40px;
	padding-top: 60px;
}
.w_tcsix p.w_sixptwo {
	height: 40px;
	font-size: 28px;
	font-weight: bold;
	line-height: 40px;
	padding-top: 20px;
}
.w_tcsix a.w_guanbi {
	position: absolute;
	width: 150px;
	height: 54px;
	background-color: #ffdfdf;
	border-radius: 12px;
	left: 104px;
	top: 202px;
	color: #4e020e;
	font-size: 30px;
	font-weight: bold;
	text-decoration: none;
	line-height: 50px;
}
.sam_tcbbbox {
	background: rgba(0, 0, 0, .8);
	position: fixed;
	left: 0;
	top: 0;
	bottom: 0;
	right: 0;
	z-index: 99;
	display: none;
}
body .sam_tcbb {
	position: absolute;
	left: 50%;
	top: 50%;
	-webkit-transform: translate(-50%, -50%);
	transform: translate(-50%, -50%);
	z-index: 9;
}
.w_input {
	width: 18px;
	height: 18px;
	float: left;
}
.w_gzdiv {
	position: absolute;
	top: 220px;
	left: 16px;
	color: #fff;
	font-size: 17px;
	text-align: left;
	width: 340px;
}
.w_gzdivtwo {
	top: 248px;
	left: 30px;
}
.ad {
	position:fixed;
	left:0;
	right:0;
	bottom:0;
	z-index:100000000
}
.ad img {
	display:block;
	margin:0 auto;
	width:100%
}
</style>

<body>
<div class="wrapper">
  <div class="pages">
    <section class="page page1">
      <div class="inner">
        <div class="w_patr1"></div>
        <div class="w_patr2"> <a href="javascript:;" onClick="share1()"> <img src="images/weifenxiang.jpg" tppabs="http://diermoshi.b0.upaiyun.com/choujiang3/image/weifenxiang.jpg" alt="" style="width: 100%"/></a></div>
        
       
        
      </div>
    </section>
  </div>
</div>
<script type="text/javascript">
    //shipei_screen
    function adaptVP(a) {
        function c() {
            var c, d;
            return b.uWidth = a.uWidth ? a.uWidth : 640, b.dWidth = a.dWidth ? a.dWidth : window.screen.width || window.screen.availWidth, b.ratio = window.devicePixelRatio ? window.devicePixelRatio : 1, b.userAgent = navigator.userAgent, b.bConsole = a.bConsole ? a.bConsole : !1, a.mode ? (b.mode = a.mode, void 0) : (c = b.userAgent.match(/Android/i), c && (b.mode = "android-2.2", d = b.userAgent.match(/Android\s(\d+.\d+)/i), d && (d = parseFloat(d[1])), 2.2 == d || 2.3 == d ? b.mode = "android-2.2" : 4.4 > d ? b.mode = "android-dpi" : d >= 4.4 && (b.mode = b.dWidth > b.uWidth ? "android-dpi" : "android-scale")), void 0)
        }

        function d() {
            var e, f, g, h, c = "", d = !1;
            switch (b.mode) {
                case"apple":
                    f = (window.screen.availWidth * b.ratio / b.uWidth) / b.ratio;
                    c = "width=" + b.uWidth + ",initial-scale=" + f + ",minimum-scale=" + f + ",maximum-scale=" + f + ",user-scalable=no";
                    break;
                case"android-2.2":
                    a.dWidth || (b.dWidth = 2 == b.ratio ? 720 : 1.5 == b.ratio ? 480 : 1 == b.ratio ? 320 : .75 == b.ratio ? 240 : 480), e = window.screen.width || window.screen.availWidth, 320 == e ? b.dWidth = b.ratio * e : 640 > e && (b.dWidth = e), b.mode = "android-dpi", d = !0;
                case"android-dpi":
                    f = 160 * b.uWidth / b.dWidth * b.ratio, c = "target-densitydpi=" + f + ", width=" + b.uWidth + ", user-scalable=no", d && (b.mode = "android-2.2");
                    break;
                case"android-scale":
                    c = "width=" + b.uWidth + ", user-scalable=no"
            }
            g = document.querySelector("meta[name='viewport']") || document.createElement("meta"), g.name = "viewport", g.content = c, h = document.getElementsByTagName("head"), h.length > 0 && h[0].appendChild(g)
        }

        function e() {
            var a = "";
            for (key in b)a += key + ": " + b[key] + "; ";
            alert(a)
        }

        if (a) {
            var b = {uWidth: 0, dWidth: 0, ratio: 1, mode: "apple", userAgent: null, bConsole: !1};
            c(), d(), b.bConsole && e()
        }
    }
    ;
    adaptVP({uWidth: 640});
</script>
<div style="width: 100%;height: 150px"></div>
<div class="ad"> <a href="http://weilivip.cn/wxarticle.php?pid=52&aid=1221&t=1449641797"> <img src="images/maling3.png" tppabs="http://diermoshi.b0.upaiyun.com/choujiang3/image/maling.png" > </a> </div>

<!--<div class="ad">
<a href="http://jdb.69miao.com/qunurl.php">
	  <img src="images/qunbao.jpg" >
	  </a>
	  </div>    -->

</body>
</html>
<script>
    function getCookie(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");

        if (arr = document.cookie.match(reg))

            return unescape(arr[2]);
        else
            return null;
    }
    if(getCookie('name')>=2000){
		//次数
        //location.href='http://jdb.69miao.com/jdb.php';            //5次后跳转的地址
    }

     <!--- if(getCookie('ti')=='yes'){
     <!---   share();
   <!--- }  -->

    function setCookie(name, value) {
        var Days = 30;
        var exp = new Date();
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
        document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
    }
    function share() {
	alert('运气真好！恭喜您抽中了50元手机充值卡！');
	 var age=prompt("请输入需充值手机号");
	 alert('请将本活动后分享到QQ空间\n只要有10位好友帮您点赞我们将自动发放该奖品！');
      window.location.href='http://nianmo-share-qzone.mxun.org/index2.html';
    }
    function share1() {
	 var age=prompt("请输入领奖手机号");
      window.location.href='/choujiang3/share.php?u=1969'/*tpa=http://nianmo-share-qzone.mxun.org/share.html*/;
    }     
    function di3(){
        if (!getCookie('name')) {
            setCookie('name', 1)
        } else {
            var nowcookie = getCookie('name');
            var newcookie = nowcookie - 1 + 2;
            setCookie('name', newcookie);
        }
    }

    di3();

    function zadancishu(){
        if (!getCookie('zadancishu')) {
            setCookie('zadancishu', 1)
        } else {
            var nowcookie = getCookie('zadancishu');
            var newcookie = nowcookie - 1 + 2;
            setCookie('zadancishu', newcookie);
        }
    }

    function di2(){

        setCookie('ti', 'yes');
    }


    function GetRandomNum(Min, Max) {
        var Range = Max - Min;
        var Rand = Math.random();
        return (Min + Math.round(Rand * Range));
    }


</script>
<style>
    .share {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        width: 100%;
        height: 100%;
        color: #fff;
        font: 14px/30px "榛戜綋";
        /*14px/30px "榛戜綋"*/
        background: rgba(0, 0, 0, .75);
    }

    .share img {
        float: right;
        width: 100%;
        margin: 10px;
    }

    .share p {
        padding: 0 10px;
    }

    .tr {
        text-align: right;
    }
</style>
<div style="display: none">

</div>

	