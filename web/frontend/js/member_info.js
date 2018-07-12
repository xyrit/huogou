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
            var str = "<option value=\"" + FirstValue + "\">"+FirstText+"</option>";
            $YearSelector.html(str);
            $MonthSelector.html(str);
            $DaySelector.html(str);

            // 年份列表
            var yearNow = new Date().getFullYear();
            var yearSel = $YearSelector.attr("rel");
            for (var i = yearNow; i >= 1900; i--) {
                var sed = yearSel==i?"selected":"";
                var yearStr = "<option value=\"" + i + "\" " + sed+">"+i+"</option>";
                $YearSelector.append(yearStr);
            }

            // 月份列表
            var monthSel = $MonthSelector.attr("rel");
            for (var i = 1; i <= 12; i++) {
                var sed = monthSel==i?"selected":"";
                var monthStr = "<option value=\"" + i + "\" "+sed+">"+i+"</option>";
                $MonthSelector.append(monthStr);
            }

            // 日列表(仅当选择了年月)
            function BuildDay() {
                if ($YearSelector.val() == 0 || $MonthSelector.val() == 0) {
                    // 未选择年份或者月份
                    $DaySelector.html(str);
                } else {
                    $DaySelector.html(str);
                    var year = parseInt($YearSelector.val());
                    var month = parseInt($MonthSelector.val());
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
                        var sed = daySel==i?"selected":"";
                        var dayStr = "<option value=\"" + i + "\" "+sed+">" + i + "</option>";
                        $DaySelector.append(dayStr);
                    }
                }
            }
            $MonthSelector.change(function () {
                BuildDay();
            });
            $YearSelector.change(function () {
                BuildDay();
            });
            if($DaySelector.attr("rel")!=""){
                BuildDay();
            }
        } // End ms_DatePicker
    });
})(jQuery);

$('#btnEditPwd').click(function() {
    var params = {
        'oldPwd': $('#oldPwd').val(),
        'newPwd': $('#newPwd').val(),
        'newPwdAgain': $('#newPwdAgain').val(),
    };

    if (params.newPwd != params.newPwdAgain) {
        $('#tips').html("两次输入密码不一致，请重新输入");
        return;
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
                //重新登录
            } else {
                $('#tips').html(data.msg);
            }
        }
    });
})

$('#btnPayPwd').click(function() {
    var params = {
        'payPwd': $('#payPwd').val(),
        'payPwdAgain': $('#payPwdAgain').val()
    };

    if (params.payPwd != params.payPwdAgain) {
        $('#tips').html("两次输入密码不一致，请重新输入");
        return;
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
                //重新登录
            } else {
                $('#tips').html(data.msg);
            }
        }
    });
})

$('#getCode').click(function() {
    var params = {
        type: 'pay'
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
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                })
            }
        }
    });
});

//验证小额免支付
$('#btnMicroValid').click(function() {
    var params = {
        'type': 'pay',
        'code': $('#code').val(),
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
function closeMicroPay(data){
    var apply_url = memberBaseUrl + '/info/close-micro-pay?t='+data
    $.get(apply_url, {}, function(data){
        if(data == 1){
            $('.safety-b-box h3').html('尚未设置小额免密');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }else if(data == 2){
            $('.safety-b-box h3').html('访问不正确');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
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
    }).eq(0).trigger('click');

    var passwordInput = $('#password-jine').val();
    $('#password-jine').on('focus',function(){
        if ($(this).val() == passwordInput) {
            $(this).val("");
        }
    }).on('blur',function(){
        if($(this).val() == ""){
            $(this).val(passwordInput);
        }
    })
})

$('#btnPayValid').click(function() {
    var params = {
        'type': 'pay',
        'code': $('#code').val(),
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
                    closePayPwd();
                } else {
                    window.location.href="/info/pay-pwd";
                }
            } else {
                $('#tips').html(data.msg);
            }
        }
    });
});

function closePayPwd()
{
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/close-pay-pwd',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            window.location.href="/info/safety";
        }
    });
}

//判断是否开启小额免密码
$('#micro').click(function(){
    var apply_url = memberBaseUrl + '/info/micro-check'
    $.get(apply_url, {}, function(data){
        if(data == 1){
            $('.safety-b-box h3').html('请先验证手机');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }else if(data == 2){
            $('.safety-b-box h3').html('请先设置支付密码');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }else if(data == 0){
            window.location.href="/info/micro-send-sms?flag=1";
        }
    })
})

//修改手机--旧手机
$('#changePhone').click(function() {
    var params = {
        'type': 'pay',
        'code': $('#code').val(),
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
        $('#safety-b-close').on('click',function(){
            $('#safety-b-con').fadeOut();
        });return false;
    }
    var params = {
        type: 'pay',
        phone: 'phone'
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
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                })
            }
        }
    });
})

//修改手机--验证新手机
$('#newPhone').click(function(){
    var phone = $('#phone').val();
    var params = {
        'type': 'pay',
        'phone' :phone,
        'code': $('#code').val(),
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/valid-new-phone-msg',
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
                $('.safety-b-box h3').html(data.msg);
                $('#safety-b-con').fadeIn();
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                });return false;
            }
        }
    });
})

//发送邮件
$('#sendMail').click(function(){
    var email = $('#newEmail').val();
    if(email){
        var pattern = /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
        if (!pattern.test(email)) {
            $('.safety-b-box h3').html('请输入正确的邮箱地址');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }
        var apply_url = memberBaseUrl + '/info/send-mail?email='+email;
    }else{
        var apply_url = memberBaseUrl + '/info/send-mail';
    }

    $.get(apply_url, {}, function(data){
        if(data == 1){
            $('.safety-b-box h3').html('该邮箱已使用');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }else{
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>发送成功</h4>');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            })
            setTimeout(function(){
                self.location= 'http://member.'+baseHost+'/info/new-email-complete';
            },1000)
        }
    })
})
