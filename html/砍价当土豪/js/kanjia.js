var registerUrl = 'http://www.pk718.com/liebiao/register.html';
var downUrl = 'http://www.pk718.com/liebiao/down.php';
var shareUrl = 'http://www.pk18.com/kanjia/index.html';
$(function() {

    var t = getCookie('t');
    if (t) {
        $('#helpKnock').text('邀请砍价');
        $('#buyNow').text('立即购买');
        $('#buyNow').attr('href',downUrl);
        $('#goodsInfoA .leftK').hide();
        $('#kanjiayaoqinBtn .p1').text('砍价');
    } else {

        $('#helpKnock').text('帮助砍价');
        $('#buyNow').text('立即参与');
        $('#buyNow').attr('href',registerUrl);
        $('#goodsInfoA .leftK').show();
        $('#kanjiayaoqinBtn .p1').text('帮助');
    }

    $('#registerBth').click(function() {
        $(this).attr('href',registerUrl);
    });

    $('#kanjiayaoqinBtn').click(function() {
        $('#kouchuZhegai').css({display: 'block', opacity: 0.8});
        $('#daobiBuZu').css({display: 'table',opacity: 1,transform: 'scale(1)'});
    });


    $('.inviteFriend').click(function() {
        $(this).attr('href',shareUrl);
    });

});



function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURI(r[2]);
    return null;
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
    var host=getDomain(document.domain);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";domain=" + host;
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