<!DOCTYPE html>
<html data-use-rem="" lang="en">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<title>注册</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=no, maximum-scale=1.0" />
 	<meta content="yes" name="apple-mobile-web-app-capable" />
  	<meta content="black" name="apple-mobile-web-app-status-bar-style" />
  	<meta content="telephone=no" name="format-detection" />
	<link rel="stylesheet" type="text/css" href="css/register.css">
	<script type="text/javascript" src="js/jquery-2.js"></script>
</head>
<body>
	 <header class="g-header">
	 	<div class="head-l">
	 		<a class="z-HReturn" onclick="history.go(-1)" href="javascript:;"><s></s><b>返回</b></a>
	 	</div>
	 	<h2>注册</h2>
	 </header>
 	<div class="step">
 		<span class="orange" id="step1_title">1.输入手机号</span> > <span id="step2_title">2.输入验证码</span> > <span id="step3_title">3.设置密码</span>
 	</div>
	<section id="step1">
		<div class="registerCon">
	      <ul>
	        <li class="accAndPwd">
	          <dl>
	            <input id="userMobile" maxlength="11" type="tel" placeholder="请输入您的手机号码" />
	          </dl> </li>
	        <li><a id="btnNext" href="javascript:;" class="orangeBtn loginBtn">下一步</a></li>
	        <li><span id="isCheck"><em></em>我已阅读并同意</span><a href="./terms.html" class="blue">伙购网用户服务协议</a></li>
	      </ul>
	    </div>
	    <div class="buylist_title">
	    	<span>购买时间</span><span>会员账号</span><span>伙购人次</span>
	    </div>
	    <ul class="buylist">
	    	<li id="box-item"><span>刚刚</span><span>150*****057</span><span>1</span></li>
	    	<li id="box-item"><span>刚刚</span><span>我想静静</span><span>2</span></li>
	    	<li id="box-item"><span>刚刚</span><span>138*****848</span><span>1</span></li>
	    	<li id="box-item"><span>3秒前</span><span>135****547</span><span>125</span></li>
	    	<li id="box-item"><span>3秒前</span><span>伙购网是真的</span><span>3</span></li>
	    	<li id="box-item"><span>4秒前</span><span>我叫叶良辰</span><span>1</span></li>
	    	<li id="box-item"><span>5秒前</span><span>138****111</span><span>1</span></li>
	    	<li id="box-item"><span>8秒前</span><span>139****545</span><span>5</span></li>
	    	<li id="box-item"><span>8秒前</span><span>俊哥</span><span>1</span></li>
	    	<li id="box-item"><span>8秒前</span><span>中台iphone6</span><span>1</span></li>
	    	<li id="box-item"><span>8秒前</span><span>180****006</span><span>300</span></li>
	    	<li id="box-item"><span>15秒前</span><span>189****447</span><span>1</span></li>
	    	<li id="box-item"><span>16秒前</span><span>伙购保佑我中</span><span>1</span></li>
	    	<li id="box-item"><span>17秒前</span><span>大家都来啊</span><span>1</span></li>
	    	<li id="box-item"><span>20秒前</span><span>135****236</span><span>2</span></li>
	    	<li id="box-item"><span>20秒前</span><span>155****289</span><span>2</span></li>
	    	<li id="box-item"><span>22秒前</span><span>天天玩伙购</span><span>1</span></li>
	    	<li id="box-item"><span>25秒前</span><span>一直不中是什么鬼</span><span>7</span></li>
	    	<li id="box-item"><span>30秒前</span><span>170****558</span><span>34</span></li>
	    	<li id="box-item"><span>30秒前</span><span>189****352</span><span>13</span></li>
	    	<li id="box-item"><span>30秒前</span><span>137****559</span><span>1</span></li>
	    	<li id="box-item"><span>33秒前</span><span>187****045</span><span>1</span></li>
	    	<li id="box-item"><span>35秒前</span><span>159****495</span><span>1</span></li>
	    	<li id="box-item"><span>35秒前</span><span>156****448</span><span>1</span></li>
	    </ul>
	</section>
	<section id="step2">
		<div class="registerCon">
	      <ul>
	        <li>
	          <input id="mobileCode" type="text" value="" placeholder="请输入系统发送给您的6位验证码"></li>
	        <li><a id="btnPostCode" href="javascript:void(0);" class="orangeBtn">确认，下一步</a></li>
	        <li>长时间未收到验证码，请联系客服QQ:4000067060</li>
	        <li><a id="btnGetCode" href="javascript:;" class="resendBtn">重新发送</a></li>
	      </ul>
	    </div>
	</section>
	<section id="step3">
		<div class="registerCon">
	      <ul>
	        <li class="accAndPwd">
	          <dl>
	            <input id="userPassword" maxlength="20" type="password" placeholder="请输入登录密码" />
	          </dl>
	        </li>
	        <li class="accAndPwd">
	          <dl>
	            <input id="userRepassword" maxlength="20" type="password" placeholder="请输入重复登录密码" />
	          </dl>
	        </li>
	        <li><a id="regSubmit" href="javascript:;" class="orangeBtn ">提交</a></li>
	      </ul>
	    </div>
	</section>
	<div class="popUp"></div>
	<div id="dialog" class="mgp-dialog-alert" style="">
		<div class="mgp-dialog-alert-title"></div>
		<div class="mgp-dialog-alert-cont">请重新输入</div>
		<div class="mgp-dialog-alert-close j_alert_button" d-tap="0" onclick="closePop()">确定</div>
	</div>
</body>
<script type="text/javascript">
	$(function(){
		var phone,smscode;
		var resend = 0;
    	var baseUrl = "http://api.huogou.com/";
		$("#btnNext").click(function(){
			var ths = $(this);
			if (ths.hasClass('grayBtn')) {
				return;
			}
			ths.addClass('grayBtn');
			var patrn=/^(11[0-9]|13[0-9]|15[0-9]|14[0-9]|17[0-9]|18[0-9])\d{8}$/;
	        phone = $('#userMobile').val();
	        if(!patrn.exec(phone)){
	            showNotice('手机号码格式错误');
				ths.removeClass('grayBtn');
	        }else{
	            $.getJSON(baseUrl+"user/check-phone?phone="+phone+'&callback=?',function(data){
	                if (data.state == 1) {
	                    showNotice('手机号码已存在');
						ths.removeClass('grayBtn');
	                }else if(data.state == 0){
	                    $.getJSON(baseUrl+"user/send-code?account="+phone+'&type=1&callback=?',function(data){
	                        $('#step1').hide();
	                        $('#step2').show();
	                        $('#step1_title').removeClass('orange');
	                        $('#step2_title').addClass('orange');
	                        countDown($("#btnGetCode"),'grayBtn');
	                        $("#mobileCode").focus();
	                    })
	                }
	            })
	            return false;
        }
		})
		$("#btnPostCode").click(function(){
			smscode = $("#mobileCode").val();
		    if (smscode.length != 6) {
		        showNotice('验证码输入错误');
		    }else{
		        $.getJSON(baseUrl+"user/check-code?account="+phone+"&code="+smscode+"&type=1&callback=?",function(data){
		            if (data.state == 1) {
		                $('#step2').hide();
                        $('#step3').show();
                        $('#step2_title').removeClass('orange');
                        $('#step3_title').addClass('orange');
                        $("#userPassword").focus();
		            }else{
		                showNotice("验证码错误");
		            }
		        })
		    }
		})
		$("#btnGetCode").click(function(){
			if (resend == 1) {
				$.getJSON(baseUrl+"user/send-code?account="+phone+'&type=1&callback=?',function(data){
					countDown($("#btnGetCode"),'grayBtn');
					$("#mobileCode").focus();
				})
			};
		})
		$("#regSubmit").click(function(){
			var password = $("#userPassword").val();
			var repassword = $("#userRepassword").val();
		    if (password.length<8) {
		        showNotice('密码长度为8-20位字符');
		    }else if (password != repassword) {
		    	showNotice('两次输入密码不相同');
		    }else{
		        $.getJSON(baseUrl+"user/register?account="+phone+"&password="+password+"&smscode="+smscode+"&source=99&spreadSource=wy_"+getUrlParam('did')+"&callback=?",function(data){
		            if (data.code == 100) {
		                if (getCookie('ti') == 'yes' && getCookie('name') == '3') {
		                	$.getJSON(baseUrl + "cart/add?productId=33&num=1&token="+getCookie('t')+"&callback=?",function(data){});
		                	$.getJSON(baseUrl + "cart/add?productId=4&num=1&token="+getCookie('t')+"&callback=?",function(data){});
	                		window.location.href = baseUrl + "/cart.html?t="+data.token;
		                }else{
		                	setCookie('t',encodeURIComponent(data.token));
		                	window.location.href = "lottery.php?did="+getUrlParam('did');
		                }
		            }else{
		                
						var errMsg = '注册失败';
						if (typeof data.errorMsg!='undefined') {
							for(var p in data.errorMsg) {
								errMsg = data.errorMsg[p];
								break;
							}
						}
						showNotice(errMsg);
		            }
		            
		        })
		    }
		})
		setInterval(function(){
            $('#box-item').animate({
                marginTop : "-2em"
            },500,function(){
                $('#box-item').animate({marginTop : "0"});
                $('.buylist').find("li:first").appendTo('.buylist');
            })
        },4000)
	})
	function showNotice(msg){
		$("#dialog .mgp-dialog-alert-title").text(msg);
		$(".popUp").show();
		$("#dialog").show();
	}
	function countDown(obj,grayCls) {     
	    var timeLeft = 150; 
	    obj.attr("t", timeLeft).text(timeLeft); 
	    obj.addClass(grayCls); 
	    var j = setInterval(function () {         
	        var t = parseInt(obj.attr("t") - 1); 
	        if (t <= 0) {             
	            clearInterval(j); 
	            obj.attr("t", t).text('重新发送'); 
	            obj.removeClass(grayCls); 
	            resend = 1;
	        } else {             
	            obj.attr("t", t).text(t); 
	        }     
	    }, 1000); 

	}
	function closePop(){
		$(".popUp").hide();
		$("#dialog").hide();
	}
	function getUrlParam(name) {
	    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
	    var r = window.location.search.substr(1).match(reg);
	    if (r != null) return decodeURI(r[2]);
	    return null;
	}
	function getCookie(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    }
    function setCookie(name, value) {
        var Days = 30;
        var exp = new Date();
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
        var host=getDomain(document.domain);
        document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";domain=" + host;
    }
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
</html>