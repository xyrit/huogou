/**
 * Created by jun on 15/11/9.
 */
var validUsername = validVerify = validPassword = validRepassword = newUser = false;

function checkAccount() {
    var obj = $('#account');
    var username = obj.val();
    var checkemail = checkEmail(username);
    var checkMob = checkPhone(username);
    if (!checkemail && !checkMob) {
        validUsername = false;
        $('#nameerror').text('错误格式的手机或邮箱').show();
        obj.siblings('i').hide();
    } else {
        $('#nameerror').text('').hide();
        obj.siblings('i').hide();
        validUsername = false;
        $.getContent(passportBaseUrl + '/api/check-account', {'account':username}, 'checkUserName', false);
    }
}

function checkPassword() {
    var password = $('#passowrd').val();
    $("#pwderror").text('');
    var re = new RegExp("[a-zA-Z]");
    var letter = re.test(password);
    re = new RegExp("[0-9]");
    var number = re.test(password);
    re = new RegExp("((?=[\x21-\x7e]+)[^A-Za-z0-9])");
    var word = re.test(password);
    if (((letter&&number) || (letter&&word) || (number&&word)) && password.length >= 8 && password.length <=16) {
        $("#pwderror").text('').hide();
        $('#pwderror').next().show();
        validPassword = true;
    } else {
        validPassword = false;
        $("#pwderror").text('使用8-20位字母、数字或符号两种或以上组合').show();
        $('#pwderror').next().hide();
    }
}

function checkRepassword() {
    var repassword = $('#repassword').val();
    var password = $('#passowrd').val();
    $("#repwderror").text('');
    if (repassword.length<=0) {
        validRepassword = false;
        $("#repwderror").text('重复密码不能为空').show();
        $('#repwderror').next().hide();
    }else if (repassword != password) {
        validRepassword = false;
        $("#repwderror").text('两次密码不同').show();
        $('#repwderror').next().hide();
    } else {
        $("#repwderror").text('').hide();
        $('#repwderror').next().show();
        validRepassword = true;
    }
}

function checkVerify() {
    var verify = $('#verify').val();
    if (verify.length==0) {
        validVerify = false;
        $("#verifyCodeError").text('验证码不能为空').show();
        return;
    }
    validVerify = false;
    $.getContent(passportBaseUrl + '/api/verify-code', {"code": verify}, 'checkVerify', false);
}

function success_checkVerify(json) {
    if (json.state == 1) {
        $("#verifyCodeError").text('').hide();
        validVerify = true;
    }else if (json.state == 0) {
        validVerify = false;
        $("#verifyCodeError").text('验证码错误').show();
    }
}

function success_checkUserName(json) {
    var obj = $('#account');
    if (json.state == 0) {
        validUsername = true;
        newUser = true;
        $('#nameerror').text('').hide();
        obj.siblings('i').show();
        $('#repasswordLi').show();
    } else if (json.state == 1) {
        validUsername = true;
        $('#nameerror').text('').hide();
        obj.siblings('i').show();
        $('#repasswordLi').hide();
    } else if (json.state==2) {
        validUsername = false;
        $('#nameerror').text('错误格式的手机或邮箱').show();
        obj.siblings('i').hide();
    }
}

var validCode = false;
function checkSmsCode() {
    var oathCode = $('#oathCode').val();
    validCode = false;
    $.getContent(passportBaseUrl + '/api/check-code', {"code": oathCode, "account": $("#account").val(), "type": 1}, 'checkCode', false);
}

function success_checkCode(json) {
    if (json.state == 1) {
        $("#oathCodeError").text('').hide();
        validCode = true;
    }else if (json.state == 0) {
        validCode = false;
        $("#oathCodeError").text('验证码错误').show();
    }
}

function countDown() {
    var obj = $(".verification_act");
    var timeLeft = 120;
    obj.attr("t", timeLeft).text('重新发送('+timeLeft+')');
    obj.addClass('verification_gray');
    var j = setInterval(function () {
        var t = parseInt(obj.attr("t") - 1);
        if (t <= 0) {
            clearInterval(j);
            obj.attr("t", t).text('重新发送');
            obj.removeClass('verification_gray');
        } else {
            obj.attr("t", t).text('重新发送(' + t + ')');
        }
    }, 1000);
}

function success_sendCode(json) {
    if (json.errcode == 101) {
        $('#resend-wrap').fadeIn();
        $('#vcodeerror').text('');
        $('#smsVcode').val('');
    } else {
        countDown();
    }
}

function success_sendCodeAndVerify(json) {
    if (json.errcode == 101) {
        $('#vcodeerror').text('验证码错误');
    }else if(json.errcode == 100) {
        $('#resend-wrap').stop().fadeOut();
        $('#vcodeerror').text('');
        $('#smsVcode').val('');
        countDown();
    }
}

$(function() {
    $('#formSubmit').on('click', function() {
        checkAccount();
        if (!validUsername) {
            $(".gl_id").focus();
            return false;
        }
        checkPassword();
        if(!validPassword){
            $(".gl_code").focus();
            return false;
        }
        checkVerify();
        if(!validVerify){
            $(".gl_verify").focus();
            return false;
        }
        checkRepassword();

        if (validUsername && validVerify && validPassword ) {
            if (newUser) {
                if (validRepassword) {
                    $('#formSubmit').val('正在提交...');
                    $.post(window.location.href,$('#register-form').serialize(),function(e) {
                        if (e.error==0) {
                            window.location.href = e.url;
                        } else {
                            if(e.message.username) {
                                $("#nameerror").text(e.message.username).show();
                                $('#nameerror').next().hide();
                            }
                            if (e.message.password) {
                                $("#pwderror").text(e.message.password).show();
                                $('#pwderror').next().hide();
                            }
                            if (e.message.verifyCode) {
                                $("#verifyCodeError").text(e.message.verifyCode).show();
                            }
                            $('#formSubmit').val('立即绑定');
                        }
                    });
                }
            } else {
                $('#formSubmit').val('正在提交...');
                $.post(window.location.href,$('#register-form').serialize(),function(e) {
                    if (e.error==0) {
                        window.location.href = e.url;
                    } else {
                        if(e.message.username) {
                            $("#nameerror").text(e.message.username).show();
                            $('#nameerror').next().hide();
                        }
                        if (e.message.password) {
                            $("#pwderror").text(e.message.password).show();
                            $('#pwderror').next().hide();
                        }
                        if (e.message.verifyCode) {
                            $("#verifyCodeError").text(e.message.verifyCode).show();
                        }
                        $('#formSubmit').val('立即绑定');
                    }
                });
            }
        }
        return false;
    });
    //$('#verify').on('blur',checkVerify);
    $('#imgCode').on('click', function() {
        $('#imgCode').attr('src', apiBaseUrl + '/user/captcha?v='+Math.random());
        return false;
    });

    $('#account').on('blur',function(){
        var value = $(this).val();
        if(value.length > 0){
            checkAccount();
        }
    });
    $('#passowrd').on('blur',function(){
        var value = $(this).val();
        if(value.length > 0){
            checkPassword();
        }
    });
    $('#repassword').on('blur',function(){
        var value = $(this).val();
        if(value.length > 0){
            checkRepassword();
        }
    });


    //verify
    $('#oathCode').on('blur',function() {
        var val = $(this).val();
        if(val.length>0) {
            checkSmsCode();
        }
    });
    $('#oauthBindBtn').on('click', function() {
        var oathCode = $('#oathCode').val();
        checkSmsCode();
        if (validCode) {
            $('#oauthBindBtn').val('正在提交...');
            $('#oatuh-verify-form').submit();
        }
        return false;
    });

    countDown();
    $(".verification_act").click(function () {
        var obj = $(".verification_act");

        if (obj.attr("t") > 0) {
            return;
        }

        $.getContent(passportBaseUrl + '/api/send-code', {"account": $("#account").val(), "type": 1}, 'sendCode', false);

        return false;
    });

    $('.refresh-imgCode').on('click', function () {
        $('#imgCode').attr('src', passportBaseUrl + '/api/captcha?v=' + Math.random())
    });

    $('#vcodeSubmit').on('click', function () {
        var account = $("#account").val();
        var smsVcode = $('#smsVcode').val();
        if (smsVcode.length == 0) {
            $('#vcodeerror').text('请输入验证码');
            return false;
        }

        $.getContent(passportBaseUrl + '/api/send-code', {
            "account": account,
            "type": 1,
            "vcode": smsVcode
        }, 'sendCodeAndVerify', false);
    });
    $('#smsVcode').on('blur', function() {
        var smsVcode = $('#smsVcode').val();
        if (smsVcode.length == 0) {
            $('#vcodeerror').text('请输入验证码');
            return false;
        }
    });
    $('#resend-close').on('click', function () {
        $('#resend-wrap').stop().fadeOut();
    });
});
