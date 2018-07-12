/**
 * Created by jun on 15/11/18.
 */
var validPassword = validRepassword = false;

$(function () {
    $('#btnNext').on('click', function () {
        var password = $('#userPassword').val();
        var repassword = $('#userRepassword').val();
        var checkpass = checkPassword(password);
        var checkrepass = checkRepassword(repassword, password);
        if (checkpass && checkrepass) {
            var mobile = $('#hidAccount').val();
            var key = $('#hidKey').val();
            var url = mobileBaseUrl+'/passport/registerbind.html';
            var data = {mobile:mobile, key:key, password:password, repassword:repassword};
            $.post(url, data, function(json) {
                if (json.error==0) {
                    window.location.href = json.url;
                } else if(json.error==1) {
                    $.PageDialog.fail(json.message);
                    return;
                }
            });
        } else if (!checkpass) {
            $.PageDialog.fail('使用8-20位字母、数字或符号两种或以上组合');
        } else if(!checkrepass) {
            $.PageDialog.fail('两次密码不一致');
        }
    });
});

function checkPassword(password) {
    var re = new RegExp("[a-zA-Z]");
    var letter = re.test(password);
    re = new RegExp("[0-9]");
    var number = re.test(password);
    re = new RegExp("((?=[\x21-\x7e]+)[^A-Za-z0-9])");
    var word = re.test(password);
    if (((letter && number) || (letter && word) || (number && word)) && password.length >= 8 && password.length <= 16) {
        return true;
    } else {
        return false;
    }
}

function checkRepassword(repassword, password) {
    if (repassword && password && repassword != password) {
        return false;
    } else {
        return true;
    }
}
