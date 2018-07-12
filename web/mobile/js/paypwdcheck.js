/**
 * Created by jun on 15/12/14.
 */
$(function(){
    var isOpen = $('#hidIsOpen').val();
    var isUpdate = $('#hidIsUpate').val();

    $('#a_switch').click(function(){
        $(this).toggleClass('open-pay');
        if (isOpen==1 && $(this).hasClass('open-pay')) {
            $('#div_auth').hide();
            $('#div_update').show();
        } else if(isOpen==0 && !$(this).hasClass('open-pay')) {
            $('#div_auth').hide();
            $('#div_update').hide();
        } else {
            $('#div_auth').show();
            $('#div_update').hide();
        }
    });
    if (isOpen==1) {
        $('#div_update').show();
        $('#a_switch').addClass('open-pay');
    } else {
        $('#div_update').hide();
        $('#a_switch').removeClass('open-pay');
    }
    if (isUpdate==1) {
        $('#div_auth').show();
        $('.login-protection p:eq(0)').remove();
        $('#div_update').html('');
    }

    $("#btnSend").click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var account = $('#hidIdentity').val();
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
        var account = $('#hidIdentity').val();
        var code = $('#txtCode').val();
        var sendType = getSendType();
        $.getJsonp(apiBaseUrl+'/user/check-code',{code:code,account:account,type:sendType},function(json) {
            if (json.state==1) {
                if (sendType==8) {
                    window.location.href = mobileBaseUrl+'/member/paypwdupdate.html?flag=2';
                } else if (sendType==9) {
                    window.location.href = mobileBaseUrl+'/member/paypwdupdate.html?flag=1';
                } else if(sendType==34) {
                    $.getJsonp(apiBaseUrl+'/info/close-pay-password',{},function(json) {
                        if (json.code==100) {
                            $.PageDialog.ok('关闭支付密码成功',function() {
                                window.location.href = mobileBaseUrl+'/member/safesetting.html';
                            });
                        } else {
                            $.PageDialog.fail(json.msg);
                        }
                    });
                }
            } else {
                $.PageDialog.fail('验证码错误');
            }
        });
    });
});


function getSendType() {
    var isUpdate = $('#hidIsUpate').val();
    if (isUpdate==1) {
        return 9;
    }
    var isOpen = $('#a_switch').hasClass('open-pay');
    return isOpen ? 8 : 34;
}
