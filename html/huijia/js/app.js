function app58(b) {
    window.__58error = this.sendAppError;
    this.init();
    if (!b || b.autoPageReady) {
        this.pageReady()
    }
    var a = this;
    if (/complete|loaded|interactive/.test(document.readyState) && document.body) {
        a.doPreActionQueue()
    } else {
        document.addEventListener("DOMContentLoaded",
            function() {
                a.doPreActionQueue();
                document.removeEventListener("DOMContentLoaded", arguments.callee, false)
            },
            false)
    }
}
app58.prototype = {
    version: "0.0.1",
    appVersion: "0.0",
    constructor: app58,
    os: null,
    class2type: {},
    jsonpCallback: {},
    uniqueId: 0,
    topRightReg: /^sou|map|hide$/,
    pagetypeReg: /^childcate|list|detail|link|publish|backreload|mypublish|usercenterbusiness$/,
    loadType: /^index|center$/,
    pluginType: /^login|register|bind$/,
    infoType: /^addSubscribe|delSubscribe|notice$/,
    toggleReg: /^show|hide$/,
    iosFrame: null,
    domReady: false,
    publishRootDomain: "webapp.58.com",
    appDomain: "app.58.com",
    actionPreQueue: [],
    getUniqueId: function() {
        return this.uniqueId++
    },
    init: function() {
        this.os = this._getOS();
        this._getVersion()
    },
    _getOS: function() {
        return navigator.userAgent.indexOf("Android") > -1 ? "android": "ios"
    },
    _nativeBriadge: function(a) {
        this._nativeBridge(a)
    },
    _nativeBridge: function(a) {
        a["action_handler"] = window.location.href;
        a = JSON.stringify(a);
        if (this.os == "android") {
            this._andr4Native(a)
        } else {
            if (this.os == "ios") {
                this._ios4Native(a)
            }
        }
    },
    _andr4Native: function(a) {
        if (typeof window.stub == "undefined") {
            return
        }
        window.stub.jsCallMethod(a)
    },
    _createIframe: function() {
        var a;
        a = document.createElement("iframe");
        a.setAttribute("style", "display:none;");
        a.setAttribute("height", "0px");
        a.setAttribute("width", "0px");
        a.setAttribute("frameborder", "0");
        return a
    },
    _ios4Native: function(b) {
        if (this.appVersion >= "6.2.5") {
            if (!this.domReady) {
                this.actionPreQueue.push(b);
                return
            }
            this.iosFrame = this._createIframe();
            this.iosFrame.src = "nativechannel://?paras=" + encodeURIComponent(b);
            document.body.appendChild(this.iosFrame);
            this.iosFrame = null
        } else {
            var a = {
                url: "http://127.0.0.1/nativechannel/",
                type: "POST",
                data: {
                    "paras": b
                },
                success: function(c) {},
                error: function(d, c) {}
            };
            this.ajax(a)
        }
    },
    doPreActionQueue: function() {
        this.domReady = true;
        while (this.actionPreQueue.length > 0) {
            this._ios4Native(this.actionPreQueue.shift())
        }
    },
    type: function(c) {
        if (c == null) {
            return c + ""
        }
        var a = this;
        var b = "Boolean Number String Function Array Date RegExp Object Error".split(" ");
        b.forEach(function(e, d, f) {
            a.class2type["[object " + e + "]"] = e.toLowerCase()
        });
        return typeof c === "object" || typeof c === "function" ? this.class2type[this.class2type.toString.call(c)] || "object": typeof c
    },
    pageReady: function() {
        if (this.domReady) {
            return
        }
        this._nativeBridge({
            "action": "page_finish"
        })
    },
    getParamsFromUrl: function(e) {
        var d = location.search;
        if (d && d != "") {
            d = d.substr(1);
            var f = {},
                a = d.split("&");
            for (var c = a.length - 1; c >= 0; c--) {
                var b = a[c].split("=");
                f[b[0]] = b[1]
            }
            if (e) {
                return f[e]
            } else {
                return f
            }
        } else {
            return null
        }
    },
    ajax: function(b) {
        var d = this.obj2formatStr(b["data"], "=", "&");
        if (b["type"] == "JSONP") {
            var e = "app58_jsonpcallback" + this.getUniqueId();
            window[e] = b["success"];
            var a = document.createElement("script");
            a.chareset = "utf-8";
            a.src = b["url"] + "?" + d + "&callback=" + e;
            a.id = e;
            document.body.appendChild(a)
        } else {
            var c = new XMLHttpRequest();
            c.open(b["type"], b["url"], true);
            c.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            c.onreadystatechange = function() {
                if (c.readyState == 4 && c.status == 200) {
                    b["success"] && b["success"].apply(null, [c.responseText])
                } else {
                    b["error"] && b["error"].apply(null, [c, c.responseText])
                }
            };
            c.send(d)
        }
    },
    obj2formatStr: function(e, b, a) {
        var d = [];
        for (var c in e) {
            d.push(c + b + e[c])
        }
        return d.join(a)
    },
    _valiParam: function(b, e, h) {
        var a = {
            "a": "Array",
            "b": "Boolean",
            "d": "Date",
            "e": "Error",
            "f": "Function",
            "n": "Number",
            "r": "RegExp",
            "s": "String",
            "o": "Object"
        };
        for (var d = 0; d < e.length; d++) {
            var g = e[d].split("|"),
                c = b[d];
            if (g[1] == "r" || g[1] == "o" && c) {
                if (g[0] == "n" && c != "") {
                    c = parseInt(c)
                }
                if (g[1] == "o" && c == "") {
                    continue
                }
                if (this.type(c).substr(0, 1) != g[0]) {
                    var f = "WBAPP." + h + " 第" + (d + 1) + "个参数的类别应该为" + a[g[0]];
                    console.log(f);
                    return
                }
            }
        }
    },
    _packingParam: function(a, c) {
        var d = {};
        for (var b = 0; b < a.length; b++) {
            d[c[b]] = a[b]
        }
        return d
    },
    _cookie: function(c) {
        var b = arguments;
        var a = this;
        switch (c) {
            case "get":
                return (function(g, l) {
                    var e = g + "=";
                    var k = e.length;
                    var d = document.cookie.length;
                    var h = 0;
                    var f = 0;
                    while (h < d) {
                        f = h + k;
                        if (document.cookie.substring(h, f) == e) {
                            return a._cookie("getCookieVal", f, l)
                        }
                        h = document.cookie.indexOf(" ", h) + 1;
                        if (h == 0) {
                            break
                        }
                    }
                    return null
                })(b[1], b[2]);
                break;
            case "set":
                return (function(e, k, g, l, i, d) {
                    var j = arguments;
                    var h = arguments.length;
                    var f = new Date();
                    var g = (h > 2) ? new Date().getTime() + parseInt(g) * 24 * 60 * 60 * 1000 : new Date(f.getFullYear(), f.getMonth() + 1, f.getUTCDate());
                    var l = (h > 3) ? j[3] : "/";
                    var i = (h > 4) ? j[4] : ".58.com";
                    var d = (h > 5) ? j[5] : false;
                    document.cookie = e + "=" + encodeURIComponent(k) + ((g == null) ? "": ("; expires=" + new Date(g).toGMTString())) + ((l == null) ? "": ("; path=" + l)) + ((i == null) ? "": ("; domain=" + i)) + ((d == true) ? "; secure": "")
                })(b[1], b[2], b[3], b[4], b[5], b[6]);
                break;
            case "set_noencode":
                return (function(e, k, g, l, i, d) {
                    var j = arguments;
                    var h = arguments.length;
                    var f = new Date();
                    var g = (h > 2) ? j[2] : new Date(f.getFullYear(), f.getMonth() + 1, f.getUTCDate());
                    var l = (h > 3) ? j[3] : "/";
                    var i = (h > 4) ? j[4] : ".58.com";
                    var d = (h > 5) ? j[5] : false;
                    document.cookie = e + "=" + k + ((g == null) ? "": ("; expires=" + g.toGMTString())) + ((l == null) ? "": ("; path=" + l)) + ((i == null) ? "": ("; domain=" + i)) + ((d == true) ? "; secure": "")
                })(b[1], b[2], b[3], b[4], b[5], b[6]);
                break;
            case "remove":
                return (function(d) {
                    if (a._cookie("get", d)) {
                        a._cookie("set", d, "", new Date(1970, 1, 1))
                    }
                })(b[1]);
                break;
            case "getCookieVal":
                return (function(f, e) {
                    var d = document.cookie.indexOf(";", f);
                    if (d == -1) {
                        d = document.cookie.length
                    }
                    if (e == false) {
                        return document.cookie.substring(f, d)
                    } else {
                        return decodeURIComponent(document.cookie.substring(f, d))
                    }
                })(b[1], b[2]);
                break
        }
    },
    toastMsg: function(b) {
        try {
            this._nativeBridge({
                "action": "toast",
                "msg": b
            })
        } catch(a) {
            alert(b)
        }
    },
    isLogin: function(a) {
        this._valiParam(arguments, ["s|r"], "isLogin");
        this._nativeBridge({
            "action": "islogin",
            "callback": a
        })
    },
    login: function(a, e, c, d, b) {
        this._valiParam(arguments, ["s|r", "s|r", "s|r", "b|o", "b|o"], "login");
        this._nativeBridge({
            "action": "login",
            "isReload": d,
            "url": a,
            "title": e,
            "pagetype": c,
            "isFinish": b
        })
    },
    loadPage: function(a, b, k, i, l, h, c, e, j, d, g) {
        if (c) {
            if (!this.topRightReg.test(c)) {
                c = "hide"
            }
        }
        if (!this.pagetypeReg.test(a)) {
            a = "link"
        }
        this._valiParam(arguments, ["s|r", "s|r", "s|r", "b|o", "b|o", "b|o", "s|o", "b|o", "b|o", "b|o", "s|o"], "loadPage");
        var f = this._packingParam(arguments, ["pagetype", "url", "title", "showarea", "showpub", "isfinish", "top_right", "is_recent", "showsift", "backtoroot", "anim"]);
        f["action"] = "loadpage";
        this._nativeBridge(f)
    },
    loadNativeList: function(c, i, b, j, f, g, a, d) {
        this._valiParam(arguments, ["s|r", "s|r", "s|r", "s|r", "s|o", "s|o", "b|o", "s|o"], "loadNativeList");
        var h = location.host.indexOf("test") != -1 ? "http://apptest.58.com/api/list": ("http://" + (this.appDomain) + "/api/list");
        var e = {
            "action": "pagetrans",
            "tradeline": c || "",
            "content": {
                "title": i,
                "pagetype": "list",
                "is_backtomain": a || false,
                "list_name": b || "",
                "local_name": d,
                "cateid": j || "",
                "params": f ? JSON.parse(f) : {},
                "filterParams": g ? JSON.parse(g) : {},
                "meta_url": h
            }
        };
        this._nativeBridge(e)
    },
    loadNativeDetail: function(a, d, b, e, c, g) {
        var f = {
            "action": "pagetrans",
            "tradeline": a,
            "content": {
                "title": "详情",
                "action": "pagetrans",
                "pagetype": "detail",
                "list_name": d,
                "local_name": b,
                "is_backtomain": g || false,
                "infoID": e,
                "full_path": c || "",
                "recomInfo": false,
                "use_cache": true,
                "pre_info": "",
                "charge_url": "",
                "filterParames": ""
            }
        };
        this._nativeBridge(f)
    },
    setWebLog: function(d, b, c, f) {
        c = c && (c + "");
        if (f && f.length) {
            for (var a = 0; a < f.length; a++) {
                f[a] = f[a] + ""
            }
        }
        this._valiParam(arguments, ["s|r", "s|r", "s|o", "a|o"], "setWebLog");
        var e = this._packingParam(arguments, ["actiontype", "pagetype", "cate", "params"]);
        this._nativeBridge({
            "action": "weblog",
            "trackinfo": e,
            "forcesend": "false"
        })
    },
    setTitle: function(b, a) {
        this._valiParam(arguments, ["s|r", "s|o"], "setTitle");
        this._nativeBridge({
            "action": "changetitle",
            "title": b,
            "rightbtn": a || "show"
        })
    },
    reload: function() {
        this._nativeBridge({
            "action": "reload",
            "code": ""
        })
    },
    goBack: function() {
        this._nativeBridge({
            "action": "goback"
        })
    },
    getPosition: function(a) {
        this._valiParam(arguments, ["s|r"], "getPosition");
        this._posCB = a;
        this._nativeBridge({
            "action": "getposition",
            "callback": "WBAPP.getPositionCallback"
        })
    },
    getPositionCallback: function(lon, lat, source) {
        if (this.os == "ios") {
            eval("(" + this._posCB + '("' + lat + '", "' + lon + '", "' + source + '")' + ")")
        } else {
            eval("(" + this._posCB + '("' + lon + '", "' + lat + '", "' + source + '")' + ")")
        }
    },
    _getVersion: function() {
        if (this.appVersion == "0.0") {
            var b = this.getParamsFromUrl("cversion");
            if (b) {
                this.appVersion = b;
                return b
            }
            var a = this._cookie("get", "cversion");
            if (a !== null) {
                if (typeof a == "string") {
                    a = a.replace(/(^"+)|("+$)/g, "")
                }
                this.appVersion = a;
                return a
            }
            this.appVersion = "999.99"
        }
        return this.appVersion
    },
    bind: function(a, c, b) {
        this._valiParam(arguments, ["s|r", "s|r", "b|o"], "bind");
        this._nativeBridge({
            "action": "third_bind",
            "type": a,
            "callback": c,
            "autoBack": b
        })
    },
    setCateId: function(a) {
        this._valiParam(arguments, ["n|r"], "setCateId");
        this._nativeBridge({
            "action": "setcateid",
            "cateid": a
        })
    },
    showDialog: function(d, f, e, g, c, a, b) {
        this._valiParam(arguments, ["s|r", "s|r", "s|r", "s|r", "s|r", "s|o", "s|o"], "showDialog");
        this._nativeBridge({
            "action": "dialog",
            "type": d,
            "title": f,
            "content": e,
            "btn1_txt": c,
            "btn2_txt": a,
            "callback": g,
            "url": b
        })
    },
    downloadApp: function(e, d, b, a, f, c) {
        this._valiParam(arguments, ["s|r", "s|r", "s|r", "s|r", "s|r", "s|o"], "downloadApp");
        if (this.os == "ios") {
            location.href = b;
            return
        }
        this._nativeBridge({
            "action": "downapp",
            "cmd": e,
            "appid": d,
            "package": a,
            "maincls": f,
            "url": b,
            "type": c,
            "tid": d
        })
    },
    extendRightBtn: function(a, b, c) {
        this._valiParam(arguments, ["s|r", "s|r", "s|r"], "extendRightBtn");
        this._nativeBridge({
            "action": "extend_btn",
            "type": a,
            "text": b,
            "enable": "true",
            "callback": c
        })
    },
    editRightBtn: function(b, a, c) {
        this._valiParam(arguments, ["b|r", "s|o", "s|o"], "editRightBtn");
        this._nativeBridge({
            "action": "editbtn",
            "enable": b,
            "txt": a,
            "type": c
        })
    },
    shareInfo: function(g, a, d, i, e, h, f, b, c) {
        f = !!f ? "imageShare": "";
        c = !!c ? c: d;
        this._valiParam(arguments, ["s|r", "s|r", "s|r", "s|r", "s|r", "s|r", "s|o", "s|o", "s|o"], "shareInfo");
        this._nativeBridge({
            "action": "info_share",
            "type": f,
            "data": {
                "title": g,
                "url": a,
                "picurl": d,
                "placeholder": i,
                "content": e,
                "dataURL": b,
                "thumburl": c
            },
            "shareto": h,
            "extshareto": h
        })
    },
    getUserInfo: function(a, b) {
        this._valiParam(arguments, ["s|o", "s|r"], "getUserInfo");
        this._nativeBridge({
            "action": "get_user_info",
            "key": a,
            "callback": b
        })
    },
    sendAppError: function(a, b, d, c) {
        this.toastMsg("URL:" + a + "\n\r ACTIONTYPE:" + b + "\n\r ERRORTYPE:" + d + "\n\r ERRORMSG:" + errorMessage)
    },
    reloadApp: function(b, a) {
        if (!this.loadType.test(b)) {
            b = "index"
        }
        this._valiParam(arguments, ["s|r", "s|o"], "reloadApp");
        this._nativeBridge({
            "action": "reload_app",
            "type": b,
            "url": a
        })
    },
    getNativePlugin: function(b, e, d, a, c) {
        if (!this.pluginType.test(b)) {
            b = "login"
        }
        this._valiParam(arguments, ["s|r", "s|r", "s|o", "s|o", "s|o"], "getNativePlugin");
        this._nativeBridge({
            "action": "get_app_plugin",
            "type": b,
            "callback": e,
            "defVal": d,
            "goBackCb": a,
            "ani": c
        })
    },
    setFlag: function(b, a) {
        this._valiParam(arguments, ["s|r", "b|o"], "setFlag");
        this._nativeBridge({
            "action": "set_flag",
            "type": b,
            "flag": a
        })
    },
    sendInfoToApp: function(a, b) {
        if (!this.infoType.test(a)) {
            a = "notice"
        }
        this._valiParam(arguments, ["s|r", "o|r"], "sendInfoToApp");
        this._nativeBridge({
            "action": "send_info",
            "type": a,
            "info": b
        })
    },
    toggleLoadingBar: function(a) {
        if (!this.toggleReg.test(a)) {
            a = "show"
        }
        this._valiParam(arguments, ["s|r"], "toggleLoadingBar");
        this._nativeBridge({
            "action": "loadingbar",
            "cmd": a
        })
    },
    getPay: function(j, e, h, c, d, f, g, k, b, i, a) {
        this._valiParam(arguments, ["s|r", "s|r", "s|r", "s|r", "s|r", "s|r", "s|r", "s|o", "s|o", "s|o", "s|o"], "getPay");
        this._nativeBridge({
            "action": "get_pay",
            "productName": j,
            "productDesc": e,
            "orderMoney": h,
            "merId": c,
            "orderId": d,
            "notifyUrl": f,
            "returnUrl": g,
            "payType": k,
            "validPayTime": b,
            "starttime": i,
            "endtime": a
        })
    }
};
var WBAPP = new app58(window.app_config || null);
window.onerror = function(d, b, a, c, e) {};
var $ = window.$ || {};
$.index = function() {};
$.index.dopost = function(g, f, j, d, i, c, a) {
    try {
        if (____json4fe.catentry instanceof Array && ____json4fe.catentry.length > 0) {
            var k = ____json4fe.catentry[0].listname
        } else {
            if (typeof ____json4fe.catentry != "undefined" && typeof ____json4fe.catentry.listname != "undefined") {
                var k = ____json4fe.catentry.listname
            } else {
                var k = "shangjie"
            }
        }
        var e = f + "," + j + "," + d;
        var a = a || "";
        var b = "";
        b = "http://p." + WBAPP.publishRootDomain + "/" + g + "/5/s5?s5&localid=" + g + "&location=" + e + "&geotype=" + i + "&geoia=" + c + "&formatsource=" + a;
        WBAPP.loadPage("publish", b, ____json4fe.catentry.name, false, false, false)
    } catch(h) {
        WBAPP.toastMsg(h.name + ": " + h.message)
    }
};