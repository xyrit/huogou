/**
 * Created by jun on 16/8/1.
 */

var baseHost = getHost();
var apiBaseUrl = 'http://api.' + baseHost;
var wwwBaseUrl = 'http://www.' + baseHost;
jQuery.extend({
    getJsonp: function (url, data, sucfunc) {
        var token = getUrlParam('token');
        var tokenSource = getUrlParam('tokenSource');
        if (!data) {
            data = {};
        }
        if (token) {
            data.token = token;
            data.tokenSource = tokenSource;
        }
        $.ajax({
            async: false,
            url: url,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: data,
            cache: false,
            success: function (json) {
                if (typeof sucfunc == "function") {
                    sucfunc(json);
                }
            }
        });
    },
    onSrollBottom: function(bottomFuc, notBottomFuc) {
        $(window).on('scroll', function (e) {

            var scrollTop = $(this).scrollTop();
            var scrollHeight = $(document).height();
            var windowHeight = $(this).height();
            if (scrollTop + windowHeight == scrollHeight){
                if (typeof bottomFuc =='function') {
                    bottomFuc();
                }
            } else {
                if (typeof notBottomFuc =='function') {
                    notBottomFuc();
                }
            }

        });
    }
});

$(function() {
    rebuildUrl();
});

function rebuildUrl() {
    var token = getUrlParam('token');
    var tokenSource = getUrlParam('tokenSource');

    $('body').on('click','a',function() {
        var href = $(this).attr('href');
        if (token) {
            var url = setUrlParam(href,'token',token);
            url = setUrlParam(url,'tokenSource',tokenSource);
            $(this).attr('href',url);
        }
    });

}

function getRandomNum(Min,Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return(Min + Math.round(Rand * Range));
}

function getHost(url) {
    var host = "null";
    if (typeof url == "undefined"
        || null == url)
        url = window.location.href;
    var regex = /.*\:\/\/([^\/|:]*).*/;
    var match = url.match(regex);
    if (typeof match != "undefined"
        && null != match) {
        host = match[1];
    }
    if (typeof host != "undefined"
        && null != host) {
        var strAry = host.split(".");
        if (strAry.length > 1) {
            host = strAry[strAry.length - 2] + "." + strAry[strAry.length - 1];
        }
    }
    return host;
}

function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURIComponent(r[2]);
    return null;
}

function setUrlParam(url,para_name, para_value) {
    var strNewUrl = '';
    var strUrl = url;
    if (strUrl.indexOf("?") != -1) {
        strUrl = strUrl.substr(strUrl.indexOf("?") + 1);
        if (strUrl.toLowerCase().indexOf(para_name.toLowerCase()) == -1) {
            strNewUrl = url + "&" + para_name + "=" + para_value;
            return strNewUrl;
        } else {
            var aParam = strUrl.split("&");
            for (var i = 0; i < aParam.length; i++) {
                if (aParam[i].substr(0, aParam[i].indexOf("=")).toLowerCase() == para_name.toLowerCase()) {
                    aParam[i] = aParam[i].substr(0, aParam[i].indexOf("=")) + "=" + para_value;
                }
            }
            strNewUrl = url.substr(0, url.indexOf("?") + 1) + aParam.join("&");
            return strNewUrl;
        }
    } else {
        strUrl += "?" + para_name + "=" + para_value;
        return strUrl;
    }
}

var browser = {
    versions: function () {
        var u = navigator.userAgent, app = navigator.appVersion;
        return {//移动终端浏览器版本信息
            trident: u.indexOf('Trident') > -1, //IE内核
            presto: u.indexOf('Presto') > -1, //opera内核
            webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
            gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
            mobile: !!u.match(/AppleWebKit.*Mobile.*/) || !!u.match(/AppleWebKit/), //是否为移动终端
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
            android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
            iPhone: u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, //是否为iPhone或者QQHD浏览器
            iPad: u.indexOf('iPad') > -1, //是否iPad
            webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
            weChat:u.match(/MicroMessenger/i) == 'micromessenger',
        };
    }(),
    language: (navigator.browserLanguage || navigator.language).toLowerCase()
}




function createUserFaceImgUrl(name, width) {
    if (!name) {
        name = '000000000000.jpg';
        return 'http://img.' + baseHost + '/userface/' + width + '/' + name;
    }
    return 'http://img.' + baseHost + '/userface/' + width + '/' + name;
}

$(function() {
    $('.share').each(function() {
        var url = $(this).attr('href');
        url = url.replace(/huogou\.com/g, baseHost);
        $(this).attr('href', url);
    });


});
