/**
 * Created by jun on 15/11/18.
 */
$(function () {
    $('#mobileCode').on('focus', function () {
        if ($(this).val() == '请输入系统发送给您的6位验证码') {
            $(this).val('');
        }
    }).on('blur', function () {
        if ($(this).val() == '') {
            $(this).val('请输入系统发送给您的6位验证码');
        }
    });

    countDown();

    $('#btnGetCode').on('click', function () {
        var ths = $('#btnGetCode');
        var t = ths.attr('t');
        if (t>0) {
            return;
        }
        countDown();
    });

    $('#btnPostCode').on('click', function() {
        var code = $('#mobileCode').val();
        var mobile = $('#inpMobile').val();
        var url = weixinBaseUrl+'/passport/registercheck.html';
        var data = {code:code, mobile:mobile};
        $.post(url, data, function(json) {
            if (json.error==0) {
                window.location.href = json.url;
            } else if(json.error==1) {
                $.PageDialog.fail(json.message);
                return;
            }
        });
    });

});

function countDown() {
    var countDownNum = 150;
    var obj = $('#btnGetCode');
    obj.attr('t', countDownNum).text('重新发送(' + countDownNum + ')');
    obj.addClass('grayBtn');
    var s = setInterval(function () {
        var t = obj.attr('t') - 1;
        if (t <= 0) {
            obj.removeClass('grayBtn');
            obj.attr('t', t).text('重新发送');
            clearInterval(s);
        } else {
            obj.attr('t', t).text('重新发送(' + t + ')');
        }
    }, 1000);
}
