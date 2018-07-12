var targetUrl = 'http://www.huogou.com/spread/tg/down.php';
$(function(){
	$(".pro-fixed a").click(function(){
		$(this).hide();
		$(this).siblings('.awards-tip-box').show();
		if(!$('.step span').hasClass('orange')){
			$('.step').html('恭喜你获得20福分，下载伙购APP领福分');
			$('.login-con').hide();
			$('#register-submit').hide();
			$('.downApp').show();
		}
	})
	var phone,smscode;
	var resend = 0;
	var baseUrl = "http://api.huogoudev.com/";
	$(".input-phone input").blur(function(){
		var patrn=/^(13[0-9]|15[0-9]|14[0-9]|18[0-9])\d{8}$/;
        phone = $(this).val();
        if(!patrn.exec(phone)){
            showNotice('手机号码格式错误');
        }else{
            $.getJSON(baseUrl+"user/check-phone?phone="+phone+'&callback=?',function(data){
                if (data.state == 1) {
                    showNotice('手机号码已存在');
                }else if(data.state == 0){
                    $.getJSON(baseUrl+"user/send-code?account="+phone+'&type=1&callback=?',function(data){
                        countDown($(".input-verify a"),'grayBtn');
                    })
                }
            })
            return false;
   		}
	})
	$(".input-verify input").blur(function(){
		smscode = $(this).val();
	    if (smscode.length != 6) {
	        showNotice('验证码输入错误');
	    }else{
	        $.getJSON(baseUrl+"user/check-code?account="+phone+"&code="+smscode+"&type=1&callback=?",function(data){
	            if (data.state == 1) {
	                
	            }else{
	                showNotice("验证码错误");
	            }
	        })
	    }
	})
	$(".input-verify a").click(function(){
		if (resend == 1) {
			if ($(".input-phone input").val()) {
				$.getJSON(baseUrl+"user/send-code?account="+phone+'&type=1&callback=?',function(data){
					countDown($(".input-verify a"),'grayBtn');
					$(".input-verify input").focus();
				})
			};
		};
	})

	$(".input-password input").blur(function(){
		var password = $(".input-password input").val();
	    if (password.length<8) {
	        showNotice('密码长度为8-20位字符');
	    }
	});
	$("#register-submit").click(function(){
		var password = $(".input-password input").val();
		if (!phone) {
			showNotice('手机号码不能为空');
		}else if (!smscode) {
			showNotice('验证码不能为空');
		}else if (password.length<8) {
	        showNotice('密码长度为8-20位字符');
	    }else{
	        $.getJSON(baseUrl+"user/register?account="+phone+"&password="+password+"&smscode="+smscode+"&source=99&spreadSource=wy_"+getUrlParam('did')+"&callback=?",function(data){
	            if (data.code == 100) {
	            	$(".mgp-dialog-alert-cont").text('');
	            	showNotice('注册成功');
	            	setTimeout(function(){
		                window.location.href=targetUrl;
	            	},1000)
	            }else{
	                showNotice("注册失败");
	            }
	            
	        })
	    }
	})
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