/**
 * Created by jun on 15/12/14.
 */
$(function() {

    $.getJsonp(apiBaseUrl+'/info/safe-setting',{},function(json) {
        createSafeSettingHtml(json)
    });


});


function createSafeSettingHtml(json) {

    var payPwdClass = json.payPwd ? 'member-icon zfmm':'member-icon zfmmRe';
    var payPwdText = json.payPwd ? '已开启':"<font style='color: red'>未开启</font>";
    var smallPayClass = json.smallPayMoney > 0 ?  'member-icon xezf':'member-icon xezfRe';
    var smallPayText = json.smallPayMoney > 0 ?  '≤'+json.smallPayMoney+'元':"<font style='color: red'>未设置</font>";
    var mobileClass = json.phone.flag ?  'member-icon sjbd':'member-icon sjbdRe';
    var mobileText = json.phone.flag ?  json.phone.val : "<font style='color: red'>未绑定</font>";
    var emailClass = json.email.flag ?  'member-icon yxbd':'member-icon yxbdRe';
    var emailText = json.email.flag ?   json.email.val : "<font style='color: red'>未绑定</font>";
    var loginRemindClass = json.loginRemind ?  'member-icon dlbh':'member-icon dlbhRe';
    var loginRemainText = json.loginRemind ? '已开启':"<font style='color: red'>未开启</font>";
    var html = '';
    html += '<ul>';
    html += '<li> <a href="loginpwdupdate.html"><span><b class="member-icon"></b>登录密码</span><em><font>修改</font><i class="z-arrow"></i></em></a> </li>';

    html += '<li> <a href="paypwdcheck.html"><span><b class="'+payPwdClass+'"></b>支付密码</span><em>'+payPwdText+'<i class="z-arrow"></i></em></a> </li>';
    html += '<li> <a href="smallpaymoneycheck.html"><span><b class="'+smallPayClass+'"></b>小额免密码设置</span><em>'+smallPayText+'<i class="z-arrow"></i></em></a> </li>';
    html += '<li> <a href="mobilecheck.html"><span><b class="'+mobileClass+'"></b>手机号绑定</span><em>'+mobileText+'<i class="z-arrow"></i></em></a> </li>';
    html += '<li> <a href="emailcheck.html"><span><b class="'+emailClass+'"></b>邮箱绑定</span><em>'+emailText+'<i class="z-arrow"></i></em></a> </li>';
    html += '<li> <a href="loginremindset.html"><span><b class="'+loginRemindClass+'"></b>登录保护</span><em>'+loginRemainText+'<i class="z-arrow"></i></em></a> </li>';

    html += '</ul>';

    $('.security-con').html(html);
    $('.security-con ul li:eq(1)').click(function() {
        if (!json.phone.flag) {
            $.PageDialog.fail('请先绑定手机');
            return false;
        }
    });
    $('.security-con ul li:eq(2)').click(function() {
        if (!json.payPwd) {
            $.PageDialog.fail('未设置支付密码');
            return false;
        }
    });
    $('.security-con ul li:eq(3)').click(function() {
        if (!json.phone.flag) {
            window.location.href = 'mobilebind.html';
            return false;
        }
    });
}
