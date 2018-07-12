/**
 * Created by jun on 15/11/18.
 */
$(function() {
    $('#btnNext').on('click', function() {
        var ths = $(this);
        if ($(this).hasClass('grayBtn')) {
            return false;
        }
        $(this).addClass('grayBtn');
        var mobile = $('#userMobile').val();
        if (mobile.length==0) {
            $.PageDialog.fail('请输入手机号码');
            ths.removeClass('grayBtn');
            return;
        }
        if (!checkPhone(mobile)) {
            $.PageDialog.fail('请输入正确的手机号码');
            ths.removeClass('grayBtn');
            return;
        }
        var url = apiBaseUrl + '/user/check-phone';
        var data = {'phone':mobile};
        $.getJsonp(url,data,function(json) {
            if (json.state==1) {
                $.PageDialog.fail('已被注册，请更换手机号码');
                ths.removeClass('grayBtn');
                return;
            } else if (json.state==0) {
                var url = weixinBaseUrl + '/passport/register.html';
                var data = {'phone':mobile};
                $.post(url, data, function(json) {
                    if (json.error==0) {
                        window.location.href = weixinBaseUrl + '/passport/registercheck.html?mobile='+mobile;
                        return;
                    }
                });
            } else {
                $.PageDialog.fail('未知错误');
                ths.removeClass('grayBtn');
            }
            return;
        });
    });
});
