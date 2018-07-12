/**
 * Created by jun on 15/12/14.
 */
$(function() {


    $("#btnSend").click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var account = $('#txtMobile').val();
        if (account.length==0) {
            $.PageDialog.fail('请填写手机号');
            return;
        }

        $(this).addClass('grayBtn');
        var t = function () {
            $.getJsonp(apiBaseUrl+'/user/send-code',{account:account,type:4},function(json) {
                if (json.errcode==100) {
                    $('#div_registercon').hide();
                    $('#div_securitycode').show();
                    $('#span_mobile').text(account);

                    countDown($('#btnAgain'),'againBtn');
                } else {
                    $.PageDialog.fail('验证码发送过多');
                    ths.addClass('orgBtn');
                    ths.removeClass('grayBtn');
                }
            });
        }

        $.getJsonp(apiBaseUrl+'/user/check-phone',{phone:account},function(json) {
            if (json.state==1) {
                $.PageDialog.fail('手机已被占用');
                ths.removeClass('grayBtn');
                ths.addClass('orgBtn');
                return;
            } else {
                t();
            }
        });

    });

    $('#btnAgain').click(function() {
        var ths = $(this);
        if ($(this).hasClass('againBtn')) {
            return;
        }

        var account = $('#txtMobile').val();
        if (account.length==0) {
            $.PageDialog.fail('请填写手机号');
            return;
        }
        $.getJsonp(apiBaseUrl+'/user/send-code',{account:account,type:4},function(json) {
            if (json.errcode==100) {

                countDown($('#btnAgain'),'againBtn');
            } else {
                $.PageDialog.fail('验证码发送过多');
                ths.addClass('orgBtn');
                ths.removeClass('againBtn');
            }
        });
    });

    $('#btnSubmit').click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var account = $('#txtMobile').val();
        var code = $('#txtCode').val();
        var sendType = getSendType();
        if (account.length==0) {
            $.PageDialog.fail('请填写手机');
            return;
        }
        if (code.length==0) {
            $.PageDialog.fail('请填写验证码');
            return;
        }
        var url = '';
        if (sendType==3) {
            url = apiBaseUrl+'/info/update-mobile'
        } else if (sendType==4) {
            url = apiBaseUrl+'/info/bind-mobile'
        }
        $.getJsonp(url,{code:code,mobile:account},function(json) {
            if (json.code==100) {
                $.PageDialog.ok(json.msg,function() {
                    window.location.href = mobileBaseUrl+'/member/safesetting.html';
                });
            } else {
                $.PageDialog.fail(json.msg);
            }
        });
    });


});

function getSendType() {
    var oldMobile = $('#hidOldMobile').val();
    return oldMobile.length>0 ? 3:4;
}
