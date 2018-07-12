/**
 * Created by jun on 15/12/14.
 */
$(function() {

    var flag = $('#hidFlag').val();
    if (flag==1) {
        $('#a_switch').addClass('open-pay');
    }

    $('#a_switch').click(function(){
        $(this).toggleClass('open-pay');
        var open = 0;
        var openText = '关闭';
        if (flag==0 && $(this).hasClass('open-pay')) {
            var open = 1;
            openText = '开启';
        } else if(flag==1 && !$(this).hasClass('open-pay')) {
            var open = 0;
            openText = '关闭';
        }
        $.getJsonp(apiBaseUrl+'/info/login-remind',{open:open},function(json) {
            if (json.code==100) {
                $.PageDialog.ok('登录保护'+openText+'成功');
            } else {
                $.PageDialog.fail('登录保护'+openText+'失败');
            }
        });
    });
});