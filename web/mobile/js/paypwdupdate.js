/**
 * Created by jun on 15/12/16.
 */
$(function() {

    $('#btnSubmit').click(function() {

        var paypwd = $('#txtPayPwd').val();
        var conPaypwd = $('#txtConPayPwd').val();
        if (paypwd.length==0) {
            $.PageDialog.fail('请填写支付密码');
            return;
        }
        if (conPaypwd.length==0) {
            $.PageDialog.fail('请填写确认支付密码');
            return;
        }
        if (paypwd!==conPaypwd) {
            $.PageDialog.fail('两次密码不一致');
            return;
        }
        var flag = $('#hidFlag').val();
        var url = '';
        if (flag==1) {
            url = apiBaseUrl+'/info/update-pay-password';
        } else if (flag==2) {
            url = apiBaseUrl+'/info/open-pay-password';
        }
        $.getJsonp(url,{paypwd:paypwd},function(json) {
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
