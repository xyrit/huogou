var targetUrl = 'http://www.huogou.com/spread/tg/down.php';
var baseUrl = "http://api.huogoudev.com/";
var send = 1;
var phone;
$(function(){
	$(".pro-fixed a").click(function(){
		$('#reg').hide();
		$(this).siblings('.awards-tip-box').show();
	})
	$("#register-submit").click(function() {
		var step = $(this).attr('step');
		var checkMobile = 0,checkCode = 0;
		var patrn=/^(13[0-9]|15[0-9]|14[0-9]|18[0-9])\d{8}$/;
        	phone = $("#mobile").val();
        var code = $("#code").val();
        var password = $("#password").val();
        if (step == 'mobile') {
        	if(!patrn.exec(phone)){
	            showNotice('手机号码格式错误');
	            return false;
	        }else{
	        	$.getJSON(baseUrl+"user/check-phone?phone="+phone+'&callback=?',function(data){
	                if (data.state == 1) {
	                    showNotice('手机号码已存在');
	                }else{
	                	$("#step1_title").removeClass('orange');
	                	$("#step2_title").addClass('orange');
	                	$(".input-phone").hide();
	                	$(".input-verify").show();
	                	$("#register-submit").attr('step','code');
	                }
	            })
	            return false;
	        }
        }else if (step == 'code') {
        	if (!code) {
        		showNotice('验证码不能为空');
        		return false;
        	}else{
        		$.getJSON(baseUrl+"user/check-code?account="+phone+"&code="+code+"&type=1&callback=?",function(data){
		            if (data.state == 1) {
		                $("#step2_title").removeClass('orange');
	                	$("#step3_title").addClass('orange');
	                	$(".input-verify").hide();
	                	$(".input-password").show();
	                	$("#register-submit").attr('step','submit');
	                	$(".act").remove();
		            }else{
		                showNotice("验证码错误");
		            }
		        })
        	}
        	return false;
        }else if (step == 'submit') {
        	if (password.length<8) {
		        showNotice('密码长度为8-20位字符');
		    }else{
		    	$.getJSON(baseUrl+"user/register?account="+phone+"&password="+password+"&smscode="+code+"&source=99&spreadSource=wy_"+getUrlParam('did')+"&callback=?",function(data){
		            if (data.code == 100) {
		            	$('.step').html('恭喜你获得20福分，下载伙购APP领福分');
		            	$(".login-input").hide();
						$("#register-submit").val('立即下载');
						$("#register-submit").attr('step','down');
		            }else{
		                showNotice("注册失败");
		            }
		            
		        })
		    }
        	return false;
        }else if (step == 'down') {
        	window.location.href = targetUrl;
        };
	});
	$(".act").click(function(){
		if (send == 1) {
			if (phone) {
				$.getJSON(baseUrl+"user/send-code?account="+phone+'&type=1&callback=?',function(data){
					countDown($(".input-verify a"),'grayBtn');
					$(".input-verify input").focus();
				})
			};
		}
		send = 0;	
	})

	$(document).on("touchstart",function(e) {
		if($(e.target).parents('.pro-fixed').length == 0){
			$('#reg').show();
			$('.awards-tip-box').hide();
		}else if($(e.target).parents('.pro-fixed').length == 1){
			$('#reg').hide();
			$('.awards-tip-box').show();
		}
	});


})

function showNotice(msg){
	if ($("#dialog").css('display') == 'none') {
		$("#dialog .mgp-dialog-alert-title").text(msg);
		$(".popUp").show();
		$("#dialog").show();
	};
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
            send = 1;
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