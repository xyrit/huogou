var memberBaseUrl = 'http://member.'+ baseHost;
/**
 * 会员中心个人信息
 */
(function($){
    $.extend({
        ms_DatePicker: function (options) {
            var defaults = {
                YearSelector: "#sel_year",
                MonthSelector: "#sel_month",
                DaySelector: "#sel_day",
                FirstText: "--",
                FirstValue: 0
            };
            var opts = $.extend({}, defaults, options);
            var $YearSelector = $(opts.YearSelector);
            var $MonthSelector = $(opts.MonthSelector);
            var $DaySelector = $(opts.DaySelector);
            var FirstText = opts.FirstText;
            var FirstValue = opts.FirstValue;

            // 初始化
            var str = "<a value=\"" + FirstValue + "\">"+FirstText+"</a>";
            str = '';
            $YearSelector.html(str);
            $MonthSelector.html(str);
            $DaySelector.html(str);

            // 年份列表
            var yearNow = new Date().getFullYear();
            var yearSel = $YearSelector.attr("rel");
            for (var i = yearNow; i >= 1900; i--) {
                var sed = yearSel==i?" class='selected'":"";
                var yearStr = "<a value=\"" + i + "\" " + sed+">"+i+"</a>";
                $YearSelector.append(yearStr);
            }

            // 月份列表
            var monthSel = $MonthSelector.attr("rel");
            for (var i = 1; i <= 12; i++) {
                var sed = monthSel==i?"class='selected'":"";
                var monthStr = "<a value=\"" + i + "\" "+sed+">"+i+"</a>";
                $MonthSelector.append(monthStr);
            }

            // 日列表(仅当选择了年月)
            function BuildDay() {
                if ($YearSelector.siblings().children('a').attr('value') == 0) {
                    //$MonthSelector.html('');
                    $DaySelector.html('');
                } else if ($MonthSelector.siblings().children('a').attr('value') == 0) {
                    // 未选择年份或者月份
                    $DaySelector.html('');
                } else {
                    $DaySelector.html('');
                    var year = parseInt($YearSelector.siblings().children('a').attr('value'));
                    var month = parseInt($MonthSelector.siblings().children('a').attr('value'));
                    var dayCount = 0;
                    switch (month) {
                        case 1:
                        case 3:
                        case 5:
                        case 7:
                        case 8:
                        case 10:
                        case 12:
                            dayCount = 31;
                            break;
                        case 4:
                        case 6:
                        case 9:
                        case 11:
                            dayCount = 30;
                            break;
                        case 2:
                            dayCount = 28;
                            if ((year % 4 == 0) && (year % 100 != 0) || (year % 400 == 0)) {
                                dayCount = 29;
                            }
                            break;
                        default:
                            break;
                    }

                    var daySel = $DaySelector.attr("rel");
                    for (var i = 1; i <= dayCount; i++) {
                        var sed = daySel==i?"class='selected'":"";
                        var dayStr = "<a value=\"" + i + "\" "+sed+">" + i + "</a>";
                        $DaySelector.append(dayStr);
                    }
                }
            }
            //$MonthSelector.change(function () {
            //    BuildDay();
            //});
            //$YearSelector.change(function () {
            //    BuildDay();
            //});
            //if($DaySelector.attr("rel")!=""){
            //    BuildDay();
            //}
            BuildDay();
            $('.select_ck').click(function(e){
                $('.select_o').hide();
                $(this).siblings('.select_o').show();
            })
            $(document).click(function(e) {
                if(!$(e.target).parent().siblings().is('.select_o') && !$(e.target).parent().is('.select_ck')){
                    $('.select_o').hide();
                }
            });
            $('.select_o').on('click','a',function(){
                $(this).parent('.select_o').siblings('.select_ck').find('a').text($(this).text());
                $(this).parent('.select_o').siblings('.select_ck').find('a').attr('value',$(this).attr('value'));
                $(this).parent('.select_o').siblings('.select_ck').find('a').addClass($(this).attr('class'));
                $(this).parent('.select_o').hide();
                $('.select_o').find('a').removeClass('selected');
                $(this).addClass('selected');
                $(this).parent().val($(this).text());
                if ($(this).parent().hasClass('sel_year') || $(this).parent().hasClass('sel_month') || $(this).parent().hasClass('sel_day')) {
                    BuildDay();
                }
            })


        } // End ms_DatePicker
    });
})(jQuery);

function checkPassword(password) {
    $("#newPwd").text('');
    var re = new RegExp("[a-zA-Z]");
    var letter = re.test(password);
    re = new RegExp("[0-9]");
    var number = re.test(password);
    re = new RegExp("((?=[\x21-\x7e]+)[^A-Za-z0-9])");
    var word = re.test(password);
    if (password.length >= 8 && password.length <=20) {
        if (letter && number && word) {
            $('.p_strength').show();
            $('.safety-a-con summary').find('b').css('width','100%');
            $('.safety-a-con summary').find('i').html('强');
            $('#password').next().addClass('pas_icon');
            validPassword = true;
        }else if (letter && word) {
            $('.p_strength').show();
            $('.safety-a-con summary').find('b').css('width','66.66%');
            $('.safety-a-con summary').find('i').html('中');
            $('#password').next().addClass('pas_icon');
            validPassword = true;
        } else if (number && word) {
            $('.p_strength').show();
            $('.safety-a-con summary').find('b').css('width','66.66%');
            $('.safety-a-con summary').find('i').html('中');
            $('#password').next().addClass('pas_icon');
            validPassword = true;
        } else if (letter && number) {
            $('.p_strength').show();
            $('.safety-a-con summary').find('b').css('width','66.66%');
            $('.safety-a-con summary').find('i').html('中');
            $('#password').next().addClass('pas_icon');
            validPassword = true;
        } else {
            $('.p_strength').show();
            $('.safety-a-con summary').find('b').css('width','33.33%');
            $('.safety-a-con summary').find('i').html('');
            validPassword = false;
            $('#password').next().removeClass('pas_icon');
        }
    } else {
        validPassword = false;
        $('.p_strength').hide();
        $('#password').next().removeClass('pas_icon');
    }
}

$(function(){
    $('#newPwd').keyup(function(){
        var password = $(this).val();
        checkPassword(password);
    })
})



$('#btnEditPwd').click(function() {
    var params = {
        'oldPwd': $('#oldPwd').val(),
        'newPwd': $('#newPwd').val(),
        'newPwdAgain': $('#newPwdAgain').val(),
        token: token
    };

    if (params.oldPwd == '') {
        $('.safety-b-box').html('<h3>请输入原密码</h3>');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000);
        return false;
    }

    var re = new RegExp("[a-zA-Z]");
    var letter = re.test(params.newPwd);
    re = new RegExp("[0-9]");
    var number = re.test(params.newPwd);
    re = new RegExp("((?=[\x21-\x7e]+)[^A-Za-z0-9])");
    var word = re.test(params.newPwd);
    if (!(((letter&&number) || (letter&&word) || (number&&word)) && params.newPwd.length >= 8 && params.newPwd.length <=20)) {
        $('.safety-b-box').html('<h3>新密码格式错误</h3>');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000);
        return false;
    }

    if (params.newPwd != params.newPwdAgain) {
        $('.safety-b-box').html('<h3>两次密码不一致</h3>');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000);
        return false;
    }

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/edit-pwd',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                    window.location.href="/info/safety";
                },1000);
                return false;
            } else {
                $('.safety-b-box').html('<h3>'+data.msg+'</h3>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }
        }
    });
})

$('#btnPayPwd').click(function() {
    var params = {
        payPwd: $('#payPwd').val(),
        payPwdAgain: $('#payPwdAgain').val(),
        t: $('#type').val(),
        token: token,
    };

    var reg = /^\d{6}$/;
    if (params.payPwd == '') {
        showErrorMsg('请输入支付密码');
        return false;
    }
    if (!reg.test(params.payPwd)) {
        showErrorMsg('密码格式错误');
        return false;
    }

    if (params.payPwdAgain == '') {
        showErrorMsg('请确认支付密码');
        return false;
    }

    if (params.payPwd != params.payPwdAgain) {
        showErrorMsg('两次密码不一致');
        return false;
    }

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/pay-pwd',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>保存成功</h4>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                    window.location.href="/info/safety";
                },1000);
                return false;
            } else {
                showErrorMsg(data.msg);
                return false;
            }
        }
    });
})

/*$('#getCode').click(function() {
 var params = {
 type: 'pay',
 token: token
 };
 $.ajax({
 async: false,
 url: apiBaseUrl + '/info/send-msg',
 type: "POST",
 dataType: 'jsonp',
 jsonp: 'callback',
 data: params,
 success: function (data) {
 if(data.code == 100){
 $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
 $('#safety-b-con').fadeIn();
 setTimeout(function(){
 $('#safety-b-con').fadeOut();
 },1000);
 return false;
 }
 }
 });
 });*/

//安全中心获取手机验证码
$('#getCode').click(function() {
    if (($(this).attr("t") && $(this).attr("t")) > 0) {
        return false;
    }

    var params = {
        type: 8,
        token: token,
        phone: ''
    };

    var flag = $('#flag').val();
    var type = $('#type').val();

    if (type == 'payvalid') { //支付密码
        if (flag == 0) { //关闭支付密码
            params.type = 34;
        } else if (flag == 1) { //开启支付密码
            params.type = 8;
        } else if (flag == 2) { //修改支付密码
            params.type = 9;
        }
    } else if (type == 'microsms') { //小额支付
        if (flag == 0) { //关闭小额支付
            params.type = 36;
        } else if (flag == 1) { //开启小额支付
            params.type = 35;
        } else if (flag == 2) { //修改小额支付
            params.type = 37;
        }
    } else if (type == 'editphone') { //修改手机号
        if (flag == 2) { //修改手机号
            params.type = 3;
        }
    } else if (type == 'newphone') { //绑定手机
        if (flag == 1) {
            params.type = 4;
        }
    } else if (type == 'checkphone') { //绑定邮箱前先验证手机
        if (flag == 1) {
            params.type = 6;
        }
    }

    if (type == 'newphone') {
        params.phone = $('#phone').val();
        if (params.phone == '') {
            showErrorMsg('请输入手机号');
            return false;
        }
        if (!checkPhone(params.phone)) {
            showErrorMsg('手机格式错误');
            return false;
        }
    }

    $('#getCode').addClass('verification_gray').attr("t",60).text('重新发送(60)');
    var j =setInterval(function(){
        var t = parseInt($('#getCode').attr("t")-1);
        if (t <= 0) {
            clearInterval(j);
            $('#getCode').attr("t",t).text('获取验证码').removeClass('verification_gray');
        }else{
            $('#getCode').attr("t",t).text('重新发送('+t+')');
        }
    },1000);

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/send-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $.cookie('SEND_CODE_' + params.type, 1, {expire: 60});
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>发送成功</h4>');
                $('#safety-b-con').fadeIn();
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                })
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
                /*$('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                 $('#safety-b-con').fadeIn();
                 setTimeout(function() {
                 $('#safety-b-con').fadeOut();
                 $('.safety-b-box').html('<i class="safety-b-close"></i><h3></h3>');
                 }, 1000);
                 return false;*/
            } else {
                clearInterval(j);
                $('#getCode').attr("t",0).text('获取验证码').removeClass('verification_gray');
                if (data.code == 201) {
                    showLoginForm();
                    return false;
                } else {
                    showErrorMsg(data.msg);
                    return false;
                }
            }
        }
    });
});

//安全中心验证手机号码
$('#btnValid').click(function() {
    var params = {
        type: 8,
        code: $('#code').val(),
        token: token,
        phone: ''
    };

    if (params.code == '') {
        showErrorMsg("请输入验证码");
        return false;
    }

    var flag = $('#flag').val();
    var type = $('#type').val();

    if (type == 'payvalid') { //支付密码
        if (flag == 0) { //关闭支付密码
            params.type = 34;
        } else if (flag == 1) { //开启支付密码
            params.type = 8;
        } else if (flag == 2) { //修改支付密码
            params.type = 9;
        }
    } else if (type == 'microsms') { //小额支付
        if (flag == 0) { //关闭小额支付
            params.type = 36;
        } else if (flag == 1) { //开启小额支付
            params.type = 35;
        } else if (flag == 2) { //修改小额支付
            params.type = 37;
        }
    } else if (type == 'editphone') { //修改手机号
        if (flag == 2) { //修改手机号
            params.type = 3;
        }
    } else if (type == 'newphone') { //绑定手机
        if (flag == 1) {
            params.type = 4;
        }
    } else if (type == 'checkphone') { //绑定邮箱前先验证手机
        if (flag == 1) {
            params.type = 6;
        }
    }

    if (type == 'newphone') {
        params.phone = $('#phone').val();
        if (params.phone == '') {
            showErrorMsg('请输入手机号');
            return false;
        }
        if (!checkPhone(params.phone)) {
            showErrorMsg('手机格式错误');
            return false;
        }
    }

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/valid-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $.cookie('SEND_CODE_' + params.type, 0, {expire: 1});
                if (flag == 0) {
                    if (type == 'payvalid') { //支付密码
                        $.ajax({
                            async: false,
                            url: apiBaseUrl + '/info/close-pay-pwd',
                            type: "POST",
                            dataType: 'jsonp',
                            jsonp: 'callback',
                            data: {t: params.type},
                            success: function (data) {
                                if (data.code == 100) {
                                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                                    $('#safety-b-con').fadeIn();
                                    setTimeout(function(){
                                        $('#safety-b-con').fadeOut();
                                        self.location= 'http://member.'+baseHost+'/info/safety';
                                    }, 1000)
                                } else {
                                    showErrorMsg(data.msg);
                                }
                            }
                        });
                    } else if (type == 'microsms') { //小额支付
                        $.ajax({
                            async: false,
                            url: apiBaseUrl + '/info/close-pay-not-pwd',
                            type: "POST",
                            dataType: 'jsonp',
                            jsonp: 'callback',
                            data: {t: params.type},
                            success: function (data) {
                                if (data.code == 100) {
                                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                                    $('#safety-b-con').fadeIn();
                                    setTimeout(function(){
                                        $('#safety-b-con').fadeOut();
                                        self.location= 'http://member.'+baseHost+'/info/safety';
                                    }, 1000)
                                } else {
                                    showErrorMsg(data.msg);
                                }
                            }
                        });
                    }
                } else {
                    if (type == 'payvalid') { //支付密码
                        window.location.href = "/info/pay-pwd?t=" + params.type;
                    } else if (type == 'microsms') { //小额支付
                        window.location.href = "/info/pay-not-pwd?t=" + params.type;
                    } else if (type == 'editphone') {
                        window.location.href="/info/new-phone?t=" + params.type;
                    } else if (type == 'newphone') {
                        $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                        $('#safety-b-con').fadeIn();
                        setTimeout(function(){
                            $('#safety-b-con').fadeOut();
                            self.location= 'http://member.'+baseHost+'/info/modify';
                        }, 1000)
                    } else if (type == 'checkphone') { //绑定邮箱前先验证手机
                        window.location.href="/info/email?flag=1&t=" + params.type;
                    }
                }
            } else {
                if (data.code == 201) {
                    showLoginForm();
                    return false;
                } else {
                    showErrorMsg(data.msg);
                    return false;
                }
            }
        }
    });
});

function getCode(to, type) {
    var params = {
        phone: to,
        type: type,
        token: token
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/send-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if(data.code == 100){
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }else{
                $('.safety-b-box h3').html(data.msg);
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }
        }
    });
}

//验证小额免支付
$('#btnMicroValid').click(function() {
    var params = {
        'type': 'pay',
        'code': $('#code').val(),
        token: token
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/valid-micro-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                var flag = $('#flag').val();
                if (flag == 0) {
                    closeMicroPay(data.t);
                } else {
                    window.location.href="/info/pay-not-pwd?t="+data.t;
                }
            } else {
                $('#tips').html(data.msg);
            }
        }
    });
});

//关闭小额支付
function closeMicroPay(){
    var apply_url = memberBaseUrl + '/info/close-micro-pay';
    $.get(apply_url, {}, function(data){
        if(data == 1){
            showErrorMsg('尚未设置小额免密');
            return false;
        }else if(data == 2){
            showErrorMsg('访问不正确');
            return false;
        }else if(data == 0){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>关闭成功</h4>');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            })
            setTimeout(function(){
                self.location= 'http://member.'+baseHost+'/info/safety';
            },1000)
        }
    })
}

$(function(){
    $('.password-list').find('li').on('click', function(){
        $(this).addClass('act').siblings().removeClass('act');
        $(this).find('input').attr("checked","checked");
        $(this).siblings().children('input').removeAttr('checked');
    });

    var passwordInput = $('#password-jine').val();
    $('#password-jine').on('focus',function(){
        if ($(this).val() == passwordInput) {
            $(this).val("");
        }
        $(this).siblings().children('input').removeAttr('checked');
    }).on('blur',function(){
        if($(this).val() == ""){
            $(this).val(passwordInput);
        }
    })
})

//判断是否开启小额免密码
$('#micro').click(function(){
    var apply_url = memberBaseUrl + '/info/micro-check'
    $.get(apply_url, {}, function(data){
        if (data == 0) {
            window.location.href="/info/micro-send-sms?flag=1";
        } else {
            $('.safety-b-box h3').html('请先设置支付密码');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },1000);
            return false;
        }
        //if(data == 1){
        //    $('.safety-b-box h3').html('请先验证手机');
        //    $('#safety-b-con').fadeIn();
        //    setTimeout(function(){
        //        $('#safety-b-con').fadeOut();
        //    },1000);
        //    return false;
        //}else if(data == 2){
        //    $('.safety-b-box h3').html('请先设置支付密码');
        //    $('#safety-b-con').fadeIn();
        //    setTimeout(function(){
        //        $('#safety-b-con').fadeOut();
        //    },1000);
        //    return false;
        //}else if(data == 0){
        //    window.location.href="/info/micro-send-sms?flag=1";
        //}
    })
})

//修改手机--旧手机
$('#changePhone').click(function() {
    var params = {
        'type': 3,
        'code': $('#code').val(),
        token: token
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/valid-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                var flag = $('#flag').val();
                if (flag == 0) {
                    closeMicroPay(data.t);
                } else {
                    window.location.href="/info/new-phone?t="+data.t;
                }
            } else {
                $('#tips').html(data.msg);
            }
        }
    });
});

//验证新手机
$('#getNewCode').click(function(){
    var patrn=/^(13[0-9]|15[0-9]|14[0-9]|18[0-9])\d{8}$/;
    var phone = $('#phone').val();
    if(!patrn.exec(phone)){
        $('.safety-b-box h3').html('手机格式错误');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000);
        return false;
    }
    var params = {
        type: 4,
        phone: phone,
        token: token
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/send-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if(data.code == 100){
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }else{
                $('.safety-b-box').html('<h3>'+data.msg+'</h3>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }
        }
    });
})

//修改手机--验证新手机
$('#newPhone').click(function(){
    var phone = $('#phone').val();
    var code = $('#code').val();

    if(phone == ''){
        $('.safety-b-box h3').html('手机号码不能为空');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000);
        return false;
    }

    if(code == ''){
        $('.safety-b-box h3').html('验证码不能为空');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000);
        return false;
    }

    var params = {
        'type': 4,
        'phone' :phone,
        'code': $('#code').val(),
        token: token
    };

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/valid-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                $('#safety-b-con').fadeIn();
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                })
                setTimeout(function(){
                    self.location= 'http://member.'+baseHost+'/info/modify';
                },1000)
            } else {
                $('.safety-b-box').html('');
                $('.safety-b-box').html('<h3>'+data.msg+'</h3>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }
        }
    });
})

//发送邮件
$('#sendMail').click(function(){
    if (($(this).attr("t") && $(this).attr("t")) > 0) {
        return false;
    }
    var email = $('#newEmail').val();
    if(email){
        var pattern = /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
        if (!pattern.test(email)) {
            $('.safety-b-box h3').html('请输入正确的邮箱地址');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },1000);
            return false;
        }
        var apply_url = memberBaseUrl + '/info/send-mail?type=6&email='+email;
    }else{
        var apply_url = memberBaseUrl + '/info/send-mail?type=5';
    }

    $('#sendMail').addClass('verification_gray').attr("t",60).text('重新发送(60)');
    var j =setInterval(function(){
        var t = parseInt($('#sendMail').attr("t")-1);
        if (t <= 0) {
            clearInterval(j);
            $('#sendMail').attr("t",t).text('发送邮件').removeClass('verification_gray');
        }else{
            $('#sendMail').attr("t",t).text('重新发送('+t+')');
        }
    },1000);

    $.get(apply_url, {token: token}, function(data){
        if(data.error == 1){
            $('.safety-b-box h3').html('该邮箱已使用');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },1000);
            clearInterval(j);
            $('#sendMail').attr("t",0).text('发送邮件').css('background', '#ff500b');
            return false;
        }else{
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>发送成功</h4>');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            })

            setTimeout(function(){
                self.location= data.url;
            },1000)
        }
    })
})

$('#getEmailCode').click(function(){
    if (($(this).attr("t") && $(this).attr("t")) > 0) {
        return false;
    }

    type = $('#type').val();

    var params = {
        f: 1,
        type: type
    };

    if (params.type == 6) {
        params['email'] = $('#email').val();
        if (params.email == '') {
            showErrorMsg('请输入邮箱');
            return false;
        }
    }

    $('#getEmailCode').addClass('verification_gray').attr("t",60).text('重新发送(60)');
    var j =setInterval(function(){
        var t = parseInt($('#getEmailCode').attr("t")-1);
        if (t <= 0) {
            clearInterval(j);
            $('#getEmailCode').attr("t",t).text('获取验证码').removeClass('verification_gray');
        }else{
            $('#getEmailCode').attr("t",t).text('重新发送('+t+')');
        }
    },1000);

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/send-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $.cookie('SEND_CODE_' + params.type, 1, {expire: 60});
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>发送成功</h4>');
                $('#safety-b-con').fadeIn();
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                })
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
            } else {
                if (data.code == 201) {
                    showLoginForm();
                    return false;
                } else {
                    clearInterval(j);
                    $('#getEmailCode').attr("t",0).text('获取验证码').removeClass('verification_gray');
                    showErrorMsg(data.msg);
                    return false;
                }
            }
        }
    });
})

$('#checkEmailCode').click(function() {
    var params = {
        type: 4,
        code: $('#code').val(),
        f: 1
    };
    params.type = $('#type').val();
    if (params.type == 6) {
        params['email'] = $('#email').val();
        if (params.email == '') {
            showErrorMsg('请输入邮箱');
            return false;
        }
    }

    if (params.code == '') {
        showErrorMsg("请输入验证码");
        return false;
    }

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/valid-msg',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $.cookie('SEND_CODE_' + params.type, 0, {expire: 1});
                if (params.type == 4) {
                    window.location.href="/info/phone?flag=1&t=" + params.type;
                } else if (params.type == 6) {
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>绑定成功</h4>');
                    $('#safety-b-con').fadeIn();
                    $('#safety-b-close').on('click',function(){
                        $('#safety-b-con').fadeOut();
                    })
                    setTimeout(function(){
                        self.location= 'http://member.'+baseHost+'/info/safety';
                    },1000)
                } else if (params.type == 5) {
                    window.location.href="/info/new-email?t=" + params.type;
                }
            } else {
                if (data.code == 201) {
                    showLoginForm();
                    return false;
                } else {
                    showErrorMsg(data.msg);
                    return false;
                }
            }
        }
    });
});

$('#Login-protection').on('click', function() {
    params = {
        token: token
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/login-protected',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                $('#safety-b-con').fadeIn();
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                })
                setTimeout(function(){
                    self.location= 'http://member.'+baseHost+'/info/safety';
                },1000)
            }
        }
    });
})

function showErrorMsg(msg) {
    $('.safety-b-box').html('<i class="safety-b-close"></i><h3></h3>');
    $('.safety-b-box h3').html(msg);
    $('#safety-b-con').fadeIn();
    setTimeout(function(){
        $('#safety-b-con').fadeOut(function() {
            $('.safety-b-box h3').html("");
        });
    },1000)
}

$.cookie = function(B, I, L) {
    if (typeof I != "undefined") {
        /*L = L || {};
         if (I === null) {
         I = "";
         L.expires = -1
         }
         var E = "";
         if (L.expires && (typeof L.expires == "number" || L.expires.toUTCString)) {
         var F;
         if (typeof L.expires == "number") {
         F = new Date();
         F.setTime(F.getTime() + (L.expires * 24 * 60 * 60 * 1000))
         } else {
         F = L.expires
         }
         E = "; expires=" + F.toUTCString()
         }
         var K = L.path ? "; path=" + (L.path) : "";
         var G = L.domain ? "; domain=" + (L.domain) : "";
         var A = L.secure ? "; secure": "";
         document.cookie = [B, "=", encodeURIComponent(I), E, K, G, A].join("")*/
        //F = new Date();
        //document.cookie = B+"="+I+";expires="+(F.getTime() + L.expire);//[B, "=", encodeURIComponent(I), 'expires=' + (F.getTime() + L.expire)].join("")
        var exp = new Date();
        exp.setTime(exp.getTime() + L.expire * 1000);//过期时间 2分钟
        document.cookie = B + "=" + I + ";expires=" + exp.toGMTString();
    } else {
        var D = null;
        if (document.cookie && document.cookie != "") {
            var J = document.cookie.split(";");
            for (var H = 0; H < J.length; H++) {
                var C = jQuery.trim(J[H]);console.log(C);
                if (C.substring(0, B.length + 1) == (B + "=")) {
                    D = decodeURIComponent(C.substring(B.length + 1));
                    break
                }
            }
        }
        return D
    }
};
