/**
 * Created by jun on 15/12/14.
 */
$(function(){


    $("#btnSend").click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var account = $('#hidMobile').val();
        var sendType = getSendType();
        if (account.length==0) {
            $.PageDialog.fail('请填写手机');
            return;
        }
        $.getJsonp(apiBaseUrl+'/user/send-code',{account:account,type:sendType},function(json) {
            if (json.errcode==100) {
                countDown(ths,'grayBtn');
                $('#btnSubmit').removeClass('grayBtn');
                $('#btnSubmit').addClass('orgBtn');
            } else {
                $.PageDialog.fail('验证码发送过多');
            }
        });
    });

    $('#btnSubmit').click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var account = $('#hidMobile').val();
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
        $.getJsonp(apiBaseUrl+'/user/check-code',{code:code,account:account,type:sendType},function(json) {
            if (json.state==1) {
                window.location.href = weixinBaseUrl+'/member/mobilebind.html';
            } else {
                $.PageDialog.fail('验证码错误');
            }
        });
    });
});


function getSendType() {
    var oldMobile = $('#hidOldMobile').val();
    return oldMobile.length>0 ? 3:4;
}
