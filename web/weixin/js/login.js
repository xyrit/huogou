/**
 * Created by jun on 15/11/18.
 */
$(function() {

    var account = $('#txtAccount').val();
    if (account.length>0) {
        $('#txtPassword').focus();
    }
    document.onkeydown = function(e){
        var ev = document.all ? window.event : e;
        if(ev.keyCode==13) {
            postLoginForm();
        }
    }

    $('#btnLogin').on('click',function() {
        postLoginForm();
    });
    
    $(".i-icon-remove").click(function(){
        $(this).parent().find("input").val("").focus();
    });
});


function checkLoginForm() {
    var account = $('#txtAccount').val();
    var password = $('#txtPassword').val();
    if(account.length==0) {
        $.PageDialog.fail('请输入您的手机号码/邮箱');
        $('#txtAccount').focus();
        return false;
    }else if (!checkPhone(account) && !checkEmail(account)) {
        $.PageDialog.fail('请输入正确的手机号码/邮箱');
        $('#txtAccount').focus();
        return false;
    }else if (password.length==0) {
        $.PageDialog.fail('请输入密码');
        $('#txtPassword').focus();
        return false;
    }
    return true;
}

function postLoginForm() {
    if (!checkLoginForm() || $('#btnLogin').hasClass('grayBtn')) {
        return false;
    }
    var forword = GetQueryString('forward');
    var account = $('#txtAccount').val();
    var password = $('#txtPassword').val();
    var url = weixinBaseUrl+'/passport/login.html';
    var data = {account:account, password:password};
    $('#btnLogin').text('正在登录...').removeClass('orangeBtn').addClass('grayBtn');
    $.post(url, data, function(json) {
        if (json.error==0) {
            $.PageDialog.ok('登录成功',function() {
                if (!forword) {
                    window.location.href = weixinBaseUrl+'/member/index.html';
                } else {
                    window.location.href = decodeURIComponent(forword);
                }
            });
        } else if (json.error==1) {
            $('#btnLogin').text('登录').removeClass('grayBtn').addClass('orangeBtn');
            var errMsg;
            if (typeof json.message.username!='undefined') {
                errMsg = json.message.username;
            } else if (typeof json.message.password!='undefined') {
                errMsg = json.message.password;
            }
            $.PageDialog.fail(errMsg);
        }
    });
}
