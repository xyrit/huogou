/**
 * Created by jun on 15/12/14.
 */
$(function(){


    $("#btnSend").click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var account = $('#hidEmail').val();
        var sendType = getSendType();

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
        var account = $('#hidEmail').val();
        var code = $('#txtCode').val();
        if (account.length==0) {
            $.PageDialog.fail('请填写邮箱');
            return;
        }
        if (code.length==0) {
            $.PageDialog.fail('请填写验证码');
            return;
        }
        var sendType = getSendType();
        $.getJsonp(apiBaseUrl+'/user/check-code',{code:code,account:account,type:sendType},function(json) {
            if (json.state==1) {
                window.location.href = weixinBaseUrl+'/member/emailbind.html';
            } else {
                $.PageDialog.fail('验证码错误');
            }
        });
    });
});


function getSendType() {
    var oldEmail = $('#hidOldEmail').val();
    return oldEmail.length>0 ? 5:6;
}
