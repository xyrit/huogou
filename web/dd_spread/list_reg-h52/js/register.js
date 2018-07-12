var host = 'dddb.com';
var baseUrl = "http://api."+host+"/";
var grayClass = 'grayBtn';
var url = 'http://down.cgyyg.com/down.php';//下载 跳转地址
var regiUrl = 'register.html';//注册页面
var successUrl = 'reg_success.html';
//判断uir 地址 有没有参数
regiUrl += '?did='+getUrlParam('did');
successUrl += '?did='+getUrlParam('did');
url += '?c='+getUrlParam('did');

$(function() {
    $(".guod_g").bind("click", function (e) {
        var target = $(e.target);
        if (target.closest(".wrap_r").length == 0) {
            $(".footer").removeClass("on");
        }
    });

    $('#userMobile').focus();

    $(".sendCode").click(function() {
        var ths = $(this);
        if (ths.hasClass(grayClass)) {
            return;
        }
        ths.addClass(grayClass);
        var patrn = /^(11[0-9]|13[0-9]|15[0-9]|14[0-9]|17[0-9]|18[0-9])\d{8}$/;
        var phone = $('#userMobile').val();
        if (!patrn.exec(phone)) {
            showNotice('手机号码格式错误');
            ths.removeClass(grayClass);
        } else {
            $.getJSON(baseUrl + "user/check-phone?phone=" + phone + '&callback=?', function (data) {
                if (data.state == 1) {
                    showRegbefore();
                    ths.removeClass(grayClass);
                } else if (data.state == 0) {
                    $.getJSON(baseUrl + "user/send-code?account=" + phone + '&type=1&callback=?', function (data) {
                        if (data.errcode==100) {
                            showNotice('验证码发送成功');
                        } else {
                            showNotice('验证码发送频繁，请稍候再试');
                        }
                        countDown($(".sendCode"), grayClass);
                        $("#mobileCode").focus();
                    })
                }
            })
            return false;
        }
    });

    $("#regSubmit").click(function(){
        var ths = $(this);
        if (ths.hasClass(grayClass)) {
            return;
        }
        var phone = $('#userMobile').val();
        var smscode = $('#mobileCode').val();
        $(this).addClass(grayClass);
        var patrn = /^(11[0-9]|13[0-9]|15[0-9]|14[0-9]|17[0-9]|18[0-9])\d{8}$/;
        var phone = $('#userMobile').val();
        if (!patrn.exec(phone)) {
            showNotice('手机号码格式错误');
            ths.removeClass(grayClass);
        }else if (smscode.length != 6) {
            showNotice('验证码输入错误');
            ths.removeClass(grayClass);
        }else{
            $.getJSON(baseUrl+"user/check-code?account="+phone+"&code="+smscode+"&type=1&callback=?",function(data){
                if (data.state == 1) {
                    reg();
                }else{
                    showNotice("验证码错误");
                    ths.removeClass(grayClass);
                }
            })
        }
    });
});

function reg() {

    var phone = $('#userMobile').val();
    var password = $("#userPassword").val();
    var smscode = $('#mobileCode').val();
    if (password.length<8) {
        showNotice('密码长度为8-20位字符');
        $("#regSubmit").removeClass(grayClass);
    }else{
        $("#regSubmit").text('正在提交注册...');
        $.getJSON(baseUrl+"user/register?account="+phone+"&password="+password+"&smscode="+smscode+"&source=99&spreadSource="+getUrlParam('did')+"&callback=?",function(data){
            //$.getJSON(baseUrl+"user/register?account="+phone+"&password="+password+"&smscode="+smscode+"&callback=?",function(data){
            if (data.code == 100) {
                setCookie('t',encodeURIComponent(data.token));
                showRegSucc();
                $("#regSubmit").removeClass(grayClass);
                $("#regSubmit").text('提交');
            }else{
                var errMsg = '注册失败';
                if (typeof data.errorMsg!='undefined') {
                    for(var p in data.errorMsg) {
                        errMsg = data.errorMsg[p];
                        break;
                    }
                }
                $("#regSubmit").removeClass(grayClass);
                $("#regSubmit").text('提交');
                showNotice(errMsg);
            }

        })
    }
}

function showNotice(msg){
    $('.footer').removeClass('hide').addClass('on');
    $('.reg_notice').show();
    $('.reg_before').hide();
    $('.reg_notice .two').text(msg);
    setTimeout(function() {
        $('.footer').removeClass('on');
        $('.reg_notice').hide();
        $('.reg_notice .two').text('');
    },1500);
}

function showRegbefore() {
    $('.footer').removeClass('hide').addClass('on');
    $('.reg_before').show();
    $('.reg_notice').hide();
}

function showRegSucc() {
    $('.m_popUpTip').show();
   // window.location.href="./reg_success.html?did="+getUrlParam('did');
    //弹出框居中
    var m_con=$('.m_popUpTip .m_con');
    var winHight=$(window).height();
    var conHeight=m_con.height();
    if(winHight>conHeight){
        var curHeight=(winHight-conHeight)/2;
        m_con.css('margin-top',curHeight);
    }
    else {
        var curHeight2=(conHeight-winHight)/2;
        m_con.css('margin-top',-curHeight2);
    }
}


function countDown(obj,grayCls) {
    var timeLeft = 150;
    obj.attr("t", timeLeft).text(timeLeft);
    obj.addClass(grayCls);
    var j = setInterval(function () {
        var t = parseInt(obj.attr("t") - 1);
        if (t <= 0) {
            clearInterval(j);
            obj.attr("t", t).text('重新发送');
            obj.removeClass(grayCls);
            resend = 1;
        } else {
            obj.attr("t", t).text(t);
        }
    }, 1000);
}



function getCookie(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}
function setCookie(name, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    //var host=getDomain(document.domain);
    var host= 'dddb.com';
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";domain=" + host;
}
//接受地址栏中的 参数
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURI(r[2]);
    return '';
}

function getDomain (str) {
    if (!str) return '';
    if (str.indexOf('://') != -1) str = str.substr(str.indexOf('://') + 3);
    var topLevel = ['com', 'net', 'org', 'gov', 'edu', 'mil', 'biz', 'name', 'info', 'mobi', 'pro', 'travel', 'museum', 'int', 'areo', 'post', 'rec'];
    var domains = str.split('.');
    if (domains.length <= 1) return str;
    if (!isNaN(domains[domains.length - 1])) return str;
    var i = 0;
    while (i < topLevel.length && topLevel[i] != domains[domains.length - 1]) i++;
    if (i != topLevel.length) return domains[domains.length - 2] + '.' + domains[domains.length - 1];
    else {
        i = 0;
        while (i < topLevel.length && topLevel[i] != domains[domains.length - 2]) i++;
        if (i == topLevel.length) return domains[domains.length - 2] + '.' + domains[domains.length - 1];
        else return domains[domains.length - 3] + '.' + domains[domains.length - 2] + '.' + domains[domains.length - 1];
    }
};

function isIos(){
    var ua = navigator.userAgent.toLowerCase();
    if (/iphone|ipad|ipod/.test(ua)) {
        return true;
    } else if (/android/.test(ua)) {
        return false;
    }
}
function is_weixn(){
    var ua = navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        return true;
    } else {
        return false;
    }
}