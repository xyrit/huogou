/**
 * Created by jun on 15/12/14.
 */
$(function() {

    $('#btnSubmit').click(function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return;
        }
        var oldPwd = $('#txtOldPwd').val();
        var newPwd = $('#txtNewPwd').val();
        var newPwdAgain = $('#txtConNewPwd').val();

        if (oldPwd.length==0) {
            $.PageDialog.fail('请填写当前密码');
            return;
        }
        if (newPwd===oldPwd) {
            $.PageDialog.fail('新密码不能与当前密码相同');
            return;
        }
        var checkPwd = checkPassword(newPwd);
        if (!checkPwd[0]) {
            $.PageDialog.fail(checkPwd[1]);
            return;
        }
        var checkrepwd = checkRepassword(newPwdAgain,newPwd);
        if (!checkrepwd[0]) {
            $.PageDialog.fail(checkrepwd[1]);
            return;
        }
        $(this).removeClass('orgBtn').addClass('grayBtn');
        $(this).text('正在提交...');
        $.getJsonp(apiBaseUrl+'/info/edit-pwd',{oldPwd:oldPwd,newPwd:newPwd,newPwdAgain:newPwdAgain},function(json) {

            if (json.code==100) {
                $.PageDialog.ok('密码修改成功',function() {
                    window.location.href = 'safesetting.html';
                });
            } else {
                $.PageDialog.fail(json.msg);
            }
            ths.removeClass('grayBtn').addClass('orgBtn');
            ths.text('保 存');
        });
    });
});

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
            msg = '';
        } else {
            validPassword = false;
            msg = '使用8-20位字母、数字或符号两种或以上组合';
        }
    } else if (password.length==0) {
        validPassword = false;
        msg = '请填写新密码';
    } else {
        validPassword = false;
        msg = '使用8-20位字母、数字或符号两种或以上组合';
    }
    return [validPassword, msg];
}

function checkRepassword(repassword, password) {
    if (repassword && password && repassword != password) {
        validRepassword = false;
        msg = '两次密码不同';
    }else if(repassword.length == 0 && repassword == ""){
        msg = '请输入重复密码';
        validRepassword = false;
    } else {
        msg = '';
        validRepassword = true;
    }
    return [validRepassword, msg];
}
