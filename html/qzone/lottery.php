<?php 
  
  include 'server/getdomain.php';

  if (is_mobile()) {
    if ($_COOKIE['ti'] == 'yes' && $_COOKIE['name'] == '3') {
        if ($_COOKIE['t']) {
            //header("location:http://m.huogou.com/cart.html?t=".$_COOKIE['t']);
			//if($_GET['did'] == '24'){
			//	echo "http://m.huogou.com/redirect.html?t=".$_COOKIE['t'].'&target='.urlencode('http://m.huogou.com/cart.html');
			//	exit;
			//}
			//header("location:http://m.huogou.com/redirect.html?t=".$_COOKIE['t'].'&target='.urlencode('http://m.huogou.com/cart.html'));
			header("location:success.php");
        }else{
            header("location:reg.php?did=".$_GET['did']);
        }
    }
  }else{
    header( "HTTP/1.1 404 PAGE NOT FOUND");
    exit;
  }
?>
<!DOCTYPE html>
<html data-use-rem="" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta itemprop="name" content="伙购网年末大放水，免费抽大奖">
    <meta itemprop="image" content="./image/373138113.jpg">
    <meta name="description" itemprop="description" content="我刚刚抽中了一个奖品，大家快来帮我点赞，满5个赞我就可以拿走了">
    <title>伙购网用户注册，请认真填写您的资料</title>
    <!--css-->
    <link rel="stylesheet" href="./css/a20150813New.css">
    <link rel="stylesheet" href="./css/styleNew.css">
    <!--js-->
    <script src="./js/pp.js" tppabs="./js/pp.js"></script>
    <script src="./js/jquery-2.js"></script>
    <script type="text/javascript" src="js/lottery.js"></script>
<body>
<div align="center" class="con">
    <div class="wrapper" >
        <div class="pages">
            <section class="page page1">
                <div class="inner">
                    <div class="w_patr1" ></div>

                    <div class="w_part3">
                        <div class="flash">
                            <div class="w_title">
                                新用户有<span style="color: #fee504">3次</span>抽奖机会
                                <a href="reg.php" style="padding: 4px 20px; background-color: #fefb04; border: 3px solid #fcbb00; font-weight: 600; border-radius: 4px; color: #29122f;">立即注册</a>
                            </div>
                            <div id="swfcontent">
                                <!--choujiangzhuanpan-->

                                <div id="lottery">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                        <tr class="lottery-group">
                                            <td lottery-unit-index="0" class="lottery-unit td_1 active"><img src="img/t1.jpg" alt="" height="100%"><i></i></td>
                                            <td lottery-unit-index="1" class="lottery-unit td_2"><img src="img/t2.jpg" alt="" height="100%"><i></i></td>
                                            <td lottery-unit-index="2" class="lottery-unit td_3" style=" margin-right: 0"><img src="img/t3.jpg" alt="" height="100%"><i></i></td>
                                        </tr>
                                        <tr class="lottery-group">
                                            <td lottery-unit-index="7" class="lottery-unit td_4"><img src="img/t4.jpg" alt="" height="100%"><i></i></td>
                                            <td class="td_5" style="border: 0" id="nowLottery"><a href="javascript:;"></a></td>
                                            <td lottery-unit-index="3" class="lottery-unit td_6" style=" margin-right: 0;"><img src="img/t5.jpg" alt="" height="100%"><i></i></td>
                                        </tr>
                                        <tr class="lottery-group">
                                            <td lottery-unit-index="6" class="lottery-unit td_7"><img src="img/t6.jpg" alt="" height="100%"><i></i></td>
                                            <td lottery-unit-index="5" class="lottery-unit td_8"><img src="img/t7.jpg" alt="" height="100%"><i></i></td>
                                            <td lottery-unit-index="4" class="lottery-unit td_9" style=" margin-right: 0"><img src="img/t8.jpg" alt="" height="100%"><i></i></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <script type="text/javascript">
                                    window.onload = function () {
                                        lottery.lottery({
                                            selector: '#lottery',
                                            width: 3,
                                            height: 3,
                                            index: 0,
                                        });
                                    }
                                </script>
                                <!--！choujiangzhuanpan-->
                            </div>
                        </div>
                    </div>
                    <div class="w_part4">
                        <div class="notice">
                            <div class="m" style=" height: 165px; overflow: hidden;">
                                <ul>
                                </ul>
                            </div>
                        </div>
                        <h4>温馨提示：</h4>
                        <p>
                            伙购网，1元伙购iphone6S,只要你运气好，1元就可以买到你任何想要的商品。
                            从此购物无需攒够钱。<script language="javascript" type="text/javascript" src="http://js.users.51.la/1955047.js"></script>
<noscript><a href="http://www.51.la/?1955047" target="_blank"><img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/1955047.asp" style="border:none" /></a></noscript>
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>


    <!--弹窗背景层-->
    <div class="plog" style="display: none;"></div>

    <!--中奖-->
    <div class="share" id="win" align="center" style="display: none;">
        <div class="share_con zj">
            <p>
                恭喜您，人品爆发<br />
                获得1元伙购<span style="color: #e32c3f;">iphone6S</span>的机会
            </p>
            <a href="javascript:;" class="share_btn" id="share">点击分享到QQ空间后领取</a>
        </div>
    </div>
    <!--中奖end-->

    <!--没中奖-->
    <div class="share" id="more" align="center" style="display: none">
        <div class="share_con mzj">
            <a href="javascript:;" class="close" onclick="close()"></a>
            <p>伙购网20福分，福分可当钱花</p>
            <a href="javascript:;" class="share_btn" id="next" >再来一次</a>
        </div>
    </div>
    <div class="share" id="card" align="center" style="display: none">
        <div class="share_con mzj">
            <a href="javascript:;" class="close" onclick="close()"></a>
            <p>
                很遗憾，就差一点<br />
                什么也没抽到，<span style="color: #e32c3f;">还有一次</span>机会
            </p>
            <a href="javascript:;" class="share_btn" id="next2" >再来一次</a>
        </div>
    </div>
    <!--没中奖end-->
</div>
<div class="popUp"></div>
<div id="dialog" class="mgp-dialog-alert" style="">
    <div class="mgp-dialog-alert-title">请先注册</div>
    <div class="mgp-dialog-alert-cont">再点击抽奖</div>
    <div class="mgp-dialog-alert-close j_alert_button" d-tap="0" onclick="closePop()">确定</div>
</div>
<script type="text/javascript">
    var baseUrl = "http://api.huogou.com/";
    $(function(){
        var timestamp = bh = "";
		$(".w_title a").attr('href','reg.php?did='+getUrlParam('did'));
        for(var i=0;i<100;i++){
            var html = "";
            html += '<li id="box-item">';
            html += '<span class="nickname" style="text-align: left;">'+xianshiname()+'&nbsp;&nbsp;</span>抽中了'+xianshichouzhong() +'</li>';
            $('.m ul').append(html);
        }
        $('.m li').show();
        setInterval(function(){
            $('#box-item').animate({
                marginTop : "-33px"
            },500,function(){
                $('#box-item').animate({marginTop : "0"});
                $('.m ul').find("li:first").appendTo('.m ul');
            })
        },4000)
        $("#share").click(function(){
            window.location.href = 'server/fx.php?did='+getUrlParam('did');
        })
        $("#did").val(getUrlParam('did'));

        timestamp = (Date.parse(new Date())/1000).toString();
        var buy = parseInt(timestamp.substring(5)-30000);
            if (buy > 99988) {
                buy -= 30000;
            }else if (buy <= 10000) {
                buy += 20000;
            };
            buy = buy.toString();
        bh = "<ul>";
        for (var i =0; i < buy.length; i++) {
            bh += "<li>"+buy[i]+"</li>";
        };
        bh += '<li class="b">人</li>';
        bh += '<li class="b">正</li>';
        bh += '<li class="b">在</li>';
        bh += '<li class="b">抽</li>';
        bh += '<li class="b">奖</li>';
        bh += "</ul>";
        $(".w_patr1").html(bh);
    })

    function getCookie(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    }
    function share() {
        $(".plog").show();
        if (getCookie('ti') == 'yes') {
            var l = getCookie('name');
            if (l == '1') {
                $("#more").show();
            }else if (l == '2') {
                $("#card").show();
                if (getCookie('t')) {
				    $.getJSON(baseUrl + "cart/add?productId=153&num=1&token="+getCookie('t')+"&callback=?",function(data){});
				    $.getJSON(baseUrl + "cart/add?productId=148&num=3&token="+getCookie('t')+"&callback=?",function(data){});
	     		    $.getJSON(baseUrl + "cart/add?productId=4&num=1&token="+getCookie('t')+"&callback=?",function(data){});
                }
            }else if (l == '3') {
                $("#win").show();
                if (getCookie('t')) {
                    $.getJSON(baseUrl + "cart/add?productId=5&num=1&token="+getCookie('t')+"&callback=?",function(data){});
                }
            };
        };
    }
    function di2(){
        setCookie('ti', 'yes');
    }
    function setCookie(name, value) {
        var Days = 30;
        var exp = new Date();
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
        var host=getDomain(document.domain);
        document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";domain=" + host;
    }
    $("#next").click(function(){
        close();
    })
    $("#next2").click(function(){
        close();
    })
    $(".close").click(function(){
        close();  
    })
    function close(){
        $('.share').hide();
        $('.plog').hide();
        if (getCookie('name') == '1') {
            $(".w_title").html('您还有<span style="color: #fee504">2次</span>抽奖机会<a href="reg.php" style="padding: 4px 20px; background-color: #fefb04; border: 3px solid #fcbb00; font-weight: 600; border-radius: 4px; color: #29122f;">立即注册</a>');
        }else if (getCookie('name') == '2'){
            $(".w_title").html('您还有<span style="color: #fee504">1次</span>抽奖机会<a href="reg.php" style="padding: 4px 20px; background-color: #fefb04; border: 3px solid #fcbb00; font-weight: 600; border-radius: 4px; color: #29122f;">立即注册</a>');
        }
    }
    function xianshiname() {
        var realname = gundong_name[GetRandomNum(0, gundong_name.length - 1)];
        return realname;
    }
    var gundong_name = new Array(
            '137****1546',
            '153****5213',
            '☞公mthongbao',
            '阿迪姐姐.',
            '素雅',
            '赚客帮小雄',
            '专属§味道',
            '烟花、绽放',
            '阿静',
            '156****1342',
            '巨花魔芋',
            '^_^尾巴',
            '天使^_^',
            '叫我王翔',
            '梦雪儿',
            '屁屁屁',
            '十五',
            '真性情',
            '✎﹏ℳ๓﹏恋ღ',
            '红包总裁',
            '兔子样?',
            '小姑凉',
            '【古风】',
            '青山',
            '东方',
            '滨州孙杰',
            '火爆捡钱',
            'A今天',
            '小彩',
            'Vroo',
            '李23',
            '日照孙磊',
            'aerla',
            '巧巧',
            'xuan',
            '嗯',
            '十一年',
            '彭先森',
            '莫小琳',
            '朋友',
            '谢明',
            '范二小超人',
            '☞ongbao',
            '艾特',
            '丽丽',
            '创造机遇?',
            '少司命我的',
            '林佳鹏',
            '彭先亚',
            '【古风】',
            '我的女神',
            '满天红包☞',
            '东方',
            '朋友',
            'aerla',
            '滨州孙杰',
            '十一年',
            '阿迪姐姐.',
            '范二小超人?',
            'xuan',
            'waldenpond',
            '艾特',
            '思思'
    );
    var chouzhongjiangping = new Array(
        '200QB已发放!',
        '20福分已经发放!',
        '20元话费已成功充值!',
        '20福分已经发放!',
        '20福分已经发放!',
        '200QB已发放!',
        '20元话费已成功充值!',
        '1元伙购iPhone6s!'
    );

    function xianshichouzhong() {
        var chouzhong = chouzhongjiangping[GetRandomNum(0, chouzhongjiangping.length - 1)];
        return chouzhong;
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

    function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return decodeURI(r[2]);
        return null;
    }
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
    function getDomain (str) {
        if (!str) return '';
        if (str.indexOf('://') != -1) str = str.substr(str.indexOf('://') + 3);
        var topLevel = ['com', 'net', 'org', 'gov', 'edu', 'mil', 'biz', 'name', 'info', 'mobi', 'pro', 'travel', 'museum', 'int', 'areo', 'post', 'rec'];
        var domains = str.split('.');
        if (domains.length <= 1) return str;
        if (!isNaN(domains[domains.length - 1])) return str;
        var i = 0;
        while (i < topLevel.length && topLevel[i] != domains[domains.length - 1]) i++;
        if (i != topLevel.length) return domains[domains.length - 2] + '.' + domains[domains.length - 1];
        else {
            i = 0;
            while (i < topLevel.length && topLevel[i] != domains[domains.length - 2]) i++;
            if (i == topLevel.length) return domains[domains.length - 2] + '.' + domains[domains.length - 1];
            else return domains[domains.length - 3] + '.' + domains[domains.length - 2] + '.' + domains[domains.length - 1];
        }
    };
</script>
</body>
</html>