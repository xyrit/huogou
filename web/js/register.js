var validVerify = validPassword = validRepassword = validAccount = false;
$(function () {
    var account = code = password = repassword = verify = '';

    $(".txtreg").on('blur',function () {
        var value = $(this).val();
        var type = $(this).attr('id');
        if (type == 'account') {
            if(value.length > 0){
                checkAccont(value);
            }
        } else if (type == 'password') {
            if(value.length > 0){
                checkPassword(value);
            }
        } else if (type == 'repassword') {
            if(value.length > 0){
                checkRepassword(value, $("#password").val());
            }
        } else if (type == 'verify') {
            if(value.length > 0){
                checkVerify(value);
            }
        }
    });

    $("#btnAgreeBtn").click(function () {
        if ($(this).hasClass('grayBtn')) {
            return false;
        }
        account = $("#account").val();
        checkAccont(account);
        if (!validAccount) {
            $("#account").focus();
            return false;
        }
        password = $("#password").val();
        checkPassword(password);
        if (!validPassword) {
            $("#password").focus();
            return false;
        }
        repassword = $("#repassword").val();
        checkRepassword(repassword, password);
        if (!validRepassword) {
            $("#repassword").focus();
            return false;
        }
        verify = $("#verify").val();
        checkVerify(verify);
        if (!validVerify) {
            $("#verify").focus();
            return false;
        }
        $('#btnAgreeBtn').val('正在提交...').addClass('grayBtn');
        $("#register-form").submit();

        return false;
    });

});

function checkAccont(account) {
    $("#usererror").text('');
    validAccount = false;
    if (!checkPhone(account) && !checkEmail(account)) {
        $("#usererror").html('<i class="r_error"></i>请输入正确的手机号码或邮箱');
        $('#account').next().removeClass('pas_icon');
    } else {
        $.getContent(passportBaseUrl + '/api/check-account', {'account': account}, "isExist", false);
    }
}

function checkPassword(password) {
    var re = new RegExp("[a-zA-Z]");
    var letter = re.test(password);
    re = new RegExp("[0-9]");
    var number = re.test(password);
    re = new RegExp("((?=[\x21-\x7e]+)[^A-Za-z0-9])");
    var word = re.test(password);
    if (password.length >= 8 && password.length <=20) {
        if ((letter && number) || (letter && word) || (number && word)) {
            validPassword = true;
            $('#password').next().addClass('pas_icon');
            $('#pwderror').text('').show();
            $('.p_strength').hide();
        } else {
            validPassword = false;
            $('#pwderror').show();
            $("#pwderror").html('<i class="r_error"></i>使用8-20位字母、数字或符号两种或以上组合');
            $('#password').next().removeClass('pas_icon');
            $('.p_strength').hide();
        }
    } else if (password.length==0) {
        $('.p_strength').hide();
        $('#pwderror').show();
        validPassword = false;
        $("#pwderror").html('<i class="r_error"></i>请设置登录密码');
        $('#password').next().removeClass('pas_icon');
    } else {
        $('.p_strength').hide();
        $('#pwderror').show();
        validPassword = false;
        $("#pwderror").html('<i class="r_error"></i>使用8-20位字母、数字或符号两种或以上组合');
        $('#password').next().removeClass('pas_icon');
    }

    //如果重复密码验证错误再验证
    if ($("#repwderror").text()!='') {
        var repassword = $('#repassword').val();
        $('.p_strength').hide();
        $('#pwderror').show();
        checkRepassword(repassword, password);
    }
}

function checkPasswordStrong(password) {
    var re = new RegExp("[a-zA-Z]");
    var letter = re.test(password);
    re = new RegExp("[0-9]");
    var number = re.test(password);
    re = new RegExp("((?=[\x21-\x7e]+)[^A-Za-z0-9])");
    var word = re.test(password);
    if (password.length >= 8) {
        if (letter && number && word) {
            $('.p_strength').find('b').css('width','100%');
            $('.p_strength').find('i').html('强');
            $('.p_strength').css('display','block');
        } else if (letter && word) {
            $('.p_strength').find('b').css('width','66.66%');
            $('.p_strength').find('i').html('中');
            $('.p_strength').css('display','block');
        } else if (number && word) {
            $('.p_strength').find('b').css('width','66.66%');
            $('.p_strength').find('i').html('中');
            $('.p_strength').css('display','block');
        } else if (letter && number) {
            $('.p_strength').find('b').css('width','66.66%');
            $('.p_strength').find('i').html('中');
            $('.p_strength').css('display','block');
        } else {
            $('.p_strength').find('b').css('width','33.33%');
            $('.p_strength').find('i').html('弱');
            $('.p_strength').css('display','block');
        }
    } else {
        $('.p_strength').hide();
        $('#pwderror').show();
        $('#pwderror').text("");
    }
}

$(function () {
    $('#password').keyup(function(){
        var password = $(this).val();
        $('.p_strength').css('display','block');
        $('#pwderror').hide();
        checkPasswordStrong(password);
    });



})

function checkRepassword(repassword, password) {
    $("#repwderror").text('');
    if (repassword && password && repassword != password) {
        validRepassword = false;
        $("#repwderror").html('<i class="r_error"></i>两次密码不同');
        $('#repassword').next().removeClass('pas_icon');
    }else if(repassword.length == 0 && repassword == ""){
        $("#repwderror").html('<i class="r_error"></i>请输入确认密码');
        $('#repassword').next().removeClass('pas_icon');
    } else {
        $('#repassword').next().addClass('pas_icon');
        validRepassword = true;
    }
}

function checkVerify(verify) {
    $("#verifyerror").text('');
    if (verify.length == 0) {
        validVerify = false;
        $("#verifyerror").html('<i class="r_error"></i>请输入验证码');
    }else {
        validVerify = false;
        $.getContent(passportBaseUrl + '/api/verify-code', {"code": verify}, "checkVerify", false);
    }
}

function success_isExist(json) {
    if (json.state == 1) {
        validAccount = false;
        var loginUrl = passportBaseUrl + '/login.html';
        $("#usererror").html('<i class="r_error"></i>账号已注册，请更换或 <a style="color: #2af;" target="_blank" href="' + loginUrl + '">登录</a>');
        $('#account').next().removeClass('pas_icon');
    } else if (json.state == 2) {
        validAccount = false;
        $("#usererror").html('<i class="r_error"></i>账号非法');
        $('#account').next().removeClass('pas_icon');
    } else if (json.state == 0) {
        $("#usererror").html('');
        validAccount = true;
        $('#account').next().addClass('pas_icon');
    } else if (json.status == 3) {
        validAccount = false;
        $("#usererror").html('<i class="r_error"></i>暂不支持此类邮箱，建议您使用QQ邮箱或手机号');
        $('#account').next().removeClass('pas_icon');
    }
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


function success_checkVerify(json) {
    if (json.state == 1) {
        $("#verifyerror").text('');
        validVerify = true;
    } else if (json.state == 0) {
        validVerify = false;
        $("#verifyerror").html('<i class="r_error"></i>验证码错误');
    }
}


//注册验证页

var validCode = false;

function checkCode(code) {
    validCode = false;
    if (code == '') {
        $("#codeerror").html('<i class="r_error"></i>请输入验证码');
    } else {
        $.getContent(passportBaseUrl + '/api/check-code', {
            "code": code,
            "type": 1,
            "account": $("#account").val()
        }, "checkCode", false);
    }
}

function success_checkCode(json) {
    if (json.state == 1) {
        $("#codeerror").text('');
        validCode = true;
    } else if (json.state == 0) {
        validCode = false;
        $("#codeerror").html('<i class="r_error"></i>验证码错误');
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

function success_sendCodeAndVerify(json) {
    if (json.errcode == 101) {
        $('#vcodeerror').html('<i class="r_error"></i>验证码错误');
    }else if(json.errcode == 100) {
        $('#resend-wrap').stop().fadeOut();
        $('#vcodeerror').text('');
        $('#smsVcode').val('');
        countDown();
    }
}

$(function () {
    $('.refresh-imgCode').on('click', function () {
        $('#imgCode').attr('src', passportBaseUrl + '/api/captcha?v=' + Math.random())
    });
    $('#resend-close').on('click', function () {
        $('#resend-wrap').stop().fadeOut();
    });
    $('#vcodeSubmit').on('click', function () {
        var account = $("#account").val();
        var smsVcode = $('#smsVcode').val();
        if (smsVcode.length == 0) {
            $('#vcodeerror').html('<i class="r_error"></i>请输入验证码');
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
            $('#vcodeerror').html('<i class="r_error"></i>请输入验证码');
            return false;
        }
    });

    $('#registerSubmit').on('click', function () {
        code = $("#code").val();
        checkCode(code);
        if (validCode) {
            $('#registerSubmit').val('正在提交...');
            $("#register-form").submit();
        }
        return false;
    });

    $('#code').on('blur', function () {
        code = $("#code").val();
        checkCode(code);
    });

    countDown();


    $(".verification_act").click(function () {
        var obj = $(".verification_act");
        if ((obj.attr("t") && obj.attr("t")) > 0) {
            return;
        }
        account = $("#account").val();
        $.getContent(passportBaseUrl + '/api/send-code', {"account": account, "type": 1}, 'sendCode', false);

        return false;
    });


});