/**
 * Created by jun on 15/12/21.
 */
function openApp(obj,noAddSub) {
    var t = function () {
        var iosUrl = obj.attr('ios-url');
        var androidUrl = obj.attr('android-url');
        if (browser.versions.iPhone || browser.versions.iPad || browser.versions.ios) {
            openMobileApp(iosUrl,noAddSub);
        } else if(browser.versions.android) {
            openMobileApp(androidUrl,noAddSub);
        } else {
            openMobileApp(iosUrl,noAddSub);
        }
    }
    obj.click(t);

}

function openMobileApp(t,noAddSub) {
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
            u[c] && !noAddSub && (t += "&schemacallback=" + encodeURIComponent(u[c] + "://"))
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
        s && "ucbrowser" == s.toLowerCase() && !noAddSub && (t += "&schemacallback=" + encodeURIComponent("ucbrowser://")),
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
            40 == n && (t[1] = "open")
        }
    } else / iphone | ipod /gi.test(e) && ((t = e.match(/MQQBrowser|UCBrowser|baidubrowse|Opera|360Browser|LieBao/i)) || (t = e.match(/CriOS|Chrome/i)) && "crios" == t[0].toLowerCase() && (t[0] = "Chrome"));
    return t || ["others", ""]
}


