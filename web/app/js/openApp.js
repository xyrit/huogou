/**
 * Created by jun on 15/12/21.
 */

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

function openApp(obj) {
    var t = function () {
        var iosUrl = obj.attr('ios-url');
        var androidUrl = obj.attr('android-url');
        if (browser.versions.iPhone || browser.versions.iPad || browser.versions.ios) {
            openMobileApp(iosUrl);
        } else if(browser.versions.android) {
            openMobileApp(androidUrl);
        } else {
            openMobileApp(iosUrl);
        }
    }
    obj.click(t);

}

function openMobileApp(t) {
    r = navigator.userAgent;
    if (/android/i.test(r)) {
        var a = $.detectBrowser(),
            s = a[0],
            p = a[1] || "location";
        if (s) {
            var c = s.toLowerCase(),
                u = {
                    ucbrowser: "ucweb"
                };
            u[c] && (t += "&schemacallback=" + encodeURIComponent(u[c] + "://"))
        }
        switch (p) {
            case "iframe":
                openMobileApp.iframe ? openMobileApp.iframe.src = t: (openMobileApp.iframe = document.createElement("iframe"), openMobileApp.iframe.src = t, openMobileApp.iframe.style.display = "none", document.body.appendChild(openMobileApp.iframe)),
                    openMobileApp.flag = "iframe";
                break;
            case "open":
                var l = window.open(t, "_blank");
                setTimeout(function() {
                        l.close()
                    },
                    0),
                    openMobileApp.flag = "open";
                break;
            case "location":
                location.href = t,
                    openMobileApp.flag = "location"
        }
    } else {
        var a = $.detectBrowser(),
            s = a[0];
        s && "ucbrowser" == s.toLowerCase() && (t += "&schemacallback=" + encodeURIComponent("ucbrowser://")),
            location.href = t,
            openMobileApp.flag = "location"
    }
}

$.detectBrowser = function() {
    var t,
        e = navigator.userAgent;
    if (/android/i.test(e)) {
        if (t = e.match(/MQQBrowser|UCBrowser|360Browser|Firefox/i)) t[1] = "location";
        else if (t = e.match(/baidubrowse|SogouMobileBrowser|LieBaoFast|XiaoMi\/MiuiBrowser|opr/i)) t[1] = "iframe";
        else if (t = e.match(/Chrome/i)) {
            var n = e.match(/chrome\/([\d]+)/i);
            n && (n = n[1]),
            40 != n && (t[1] = "open")
        }
    } else / iphone | ipod /gi.test(e) && ((t = e.match(/MQQBrowser|UCBrowser|baidubrowse|Opera|360Browser|LieBao/i)) || (t = e.match(/CriOS|Chrome/i)) && "crios" == t[0].toLowerCase() && (t[0] = "Chrome"));
    return t || ["others", ""]
}


