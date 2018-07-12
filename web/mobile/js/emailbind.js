/**
 * Created by jun on 15/12/14.
 */
$(function() {


    $("#btnSend").click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var account = $('#txtEmail').val();
        if (account.length==0) {
            $.PageDialog.fail('请填写邮箱');
            return;
        }

        $(this).addClass('grayBtn');
        var t = function () {
            $.getJsonp(apiBaseUrl+'/user/send-code',{account:account,type:6},function(json) {
                if (json.errcode==100) {
                    $('#div_registercon').hide();
                    $('#div_securitycode').show();
                    $('#span_email').text(account);

                    countDown($('#btnAgain'),'againBtn');
                } else {
                    $.PageDialog.fail('验证码发送过多');
                    ths.addClass('orgBtn');
                    ths.removeClass('grayBtn');
                }
            });
        }

        $.getJsonp(apiBaseUrl+'/user/check-email',{email:account},function(json) {
            if (json.state==1) {
                $.PageDialog.fail('邮箱已被占用');
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

        var account = $('#txtEmail').val();
        if (account.length==0) {
            $.PageDialog.fail('请填写邮箱');
            return;
        }
        $.getJsonp(apiBaseUrl+'/user/send-code',{account:account,type:6},function(json) {
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
        var account = $('#txtEmail').val();
        var code = $('#txtCode').val();
        var sendType = getSendType();
        if (account.length==0) {
            $.PageDialog.fail('请填写邮箱');
            return;
        }
        if (code.length==0) {
            $.PageDialog.fail('请填写验证码');
            return;
        }
        var url = '';
        console.log(sendType)
        if (sendType==5) {
            url = apiBaseUrl+'/info/update-email'
        } else if (sendType==6) {
            url = apiBaseUrl+'/info/bind-email'
        }
        console.log(url)
        $.getJsonp(url,{code:code,email:account},function(json) {
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
    var oldMobile = $('#hidOldEmail').val();
    return oldMobile.length>0 ? 5:6;
}
