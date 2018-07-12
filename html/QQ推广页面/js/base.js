!
    function(dd) {
        var didi = window.didi = window.dd = dd; !
            function(a) {
                "use strict";
                a._cfg = {},
                    a.init = function(b) {
                        b = b || {},
                        b.id && a.trace({
                            id: b.id,
                            data: b.data || {}
                        });
                        var c = a._cfg;
                        "auto" === b.debug && (b.debug = a.is("dev")),
                            c.debug = !!b.debug,
                        b.debug === !0,
                            b.wxSignFn ? c.wxSignFn = b.wxSignFn: b.wxSignApi && (c.wxSignApi = b.wxSignApi),
                            "undefined" != typeof b.share ? a.setShare(b.share) : a.app.setShare(!1)
                    },
                    a._ = {},
                    a._.require = function(a, b, c) {
                        if ($.isFunction(window.define) && (window.define.amd || window.define.cmd) && window.require) window.require.config({
                            paths: {
                                wx: a
                            }
                        }),
                            window.require([b],
                                function(a) {
                                    window[b] = a,
                                        c(a)
                                });
                        else {
                            var d = document.createElement("script");
                            d.type = "text/javascript",
                                d.src = a + ".js",
                                document.getElementsByTagName("head")[0].appendChild(d),
                                d.onload = function() {
                                    c(window[b])
                                }
                        }
                    },
                    $.getScript = function(a, b) {
                        var c = document.createElement("script");
                        c.type = "text/javascript",
                            c.src = a,
                            document.getElementsByTagName("head")[0].appendChild(c),
                            c.onload = c.onreadystatechange = function() {
                                this.readyState && "loaded" !== this.readyState && "complete" !== this.readyState || "function" == typeof b && b()
                            }
                    },
                    $.fn.touch = function(b, c, d) {
                        var g, h, i, j, k, l, e = $(this),
                            f = !1;
                        if (b === !1) return e.off(".touch"),
                            this;
                        if ("string" == typeof b && c === !1) return e.off(".touch", b),
                            this;
                        if ("string" == typeof b && $.isFunction(c)) f = !0;
                        else {
                            if (!$.isFunction(b)) return this;
                            f = !1,
                                c = b,
                                d = c
                        }
                        return g = 0,
                            h = 0,
                            i = a.is("mobile"),
                            j = function(a, b) {
                                d !== !1 && (b ? a.addClass("active") : a.removeClass("active"))
                            },
                            a._cfg.lastTouch = +new Date,
                            k = function(c, d) {
                                c = "touch" + c + ".touch";
                                var g = function() {
                                    d.apply(this, arguments),
                                        a._cfg.lastTouch = +new Date
                                };
                                f ? e.on(c, b, g) : e.on(c, g)
                            },
                            l = function(c, d) {
                                c = "mouse" + c + ".touch";
                                var g = function() {
                                    var b = +new Date; (!i || a.is("chrome") && b - a._cfg.lastTouch > 1e3) && d.apply(this, arguments)
                                };
                                f ? e.on(c, b, g) : e.on(c, g)
                            },
                            k("start",
                                function(a) {
                                    j($(this), !0);
                                    var b = a.touches || a.originalEvent && a.originalEvent.touches || [{}];
                                    b = b[0],
                                        g = b.pageX,
                                        h = b.pageY
                                }),
                            k("move",
                                function(a) {
                                    var b = a.touches || a.originalEvent && a.originalEvent.touches || [{}];
                                    b = b[0],
                                        Math.abs(b.pageX - g) + Math.abs(b.pageY - h) > 15 ? ($(this).attr("moved", "true"), j($(this), !1)) : ($(this).attr("moved", "false"), j($(this), !0))
                                }),
                            k("end",
                                function(a) {
                                    j($(this), !1),
                                        "true" !== $(this).attr("moved") ? c.call(this, a) : $(this).attr("moved", "false")
                                }),
                            k("cancel",
                                function() {
                                    j($(this), !1),
                                        $(this).attr("moved", "false")
                                }),
                            l("down",
                                function() {
                                    j($(this), !0)
                                }),
                            l("leave",
                                function() {
                                    j($(this), !1)
                                }),
                            l("up",
                                function(a) {
                                    j($(this), !1),
                                        c.call(this, a)
                                }),
                            this
                    },
                    $.fn.loading = function(a) {
                        "string" == typeof a && (a = {
                            text: a
                        })
                    }
            } (didi),
            function(a) {
                "use strict";
                var b = {
                    decode: function(a) {
                        a = a || window.location.href;
                        var b = document.createElement("a");
                        return b.href = a,
                        {
                            source: a,
                            protocol: b.protocol.replace(":", ""),
                            host: b.hostname,
                            port: b.port,
                            query: b.search,
                            params: function() {
                                var f, a = {},
                                    c = b.search.replace(/^\?/, "").split("&"),
                                    d = c.length,
                                    e = 0;
                                for (; d > e; e++) c[e] && (f = c[e].split("="), a[decodeURIComponent(f[0])] = decodeURIComponent(c[e].substr(f[0].length + 1)));
                                return a
                            } (),
                            file: (b.pathname.match(/\/([^\/?#]+)$/i) || [, ""])[1],
                            hash: b.hash.replace("#", ""),
                            path: b.pathname.replace(/^([^\/])/, "/$1"),
                            relative: (b.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ""])[1],
                            segments: b.pathname.replace(/^\//, "").split("/")
                        }
                    },
                    encode: function(a) {
                        var b = [],
                            c = [];
                        return b.push(a.protocol, "://"),
                            b.push(a.host),
                        a.port && b.push(":", a.port),
                            b.push(a.path),
                            $.each(a.params,
                                function(a, b) {
                                    c.push(encodeURIComponent(a) + "=" + encodeURIComponent(b))
                                }),
                        c.length > 0 && b.push("?", c.join("&")),
                        a.hash && b.push("#", a.hash),
                            b.join("")
                    },
                    get: function(a, c) {
                        if (0 === arguments.length) return b.decode();
                        var d = "",
                            e = b.decode(c || window.location.href);
                        return $.each(a.split(","),
                            function(a, b) {
                                return e.params[b] ? (d = e.params[b], !1) : void 0
                            }),
                            d
                    },
                    build: function(a, c) {
                        var d = b.decode(a);
                        return $.each(c || {},
                            function(a, b) {
                                d.params[a] = b
                            }),
                            b.encode(d)
                    },
                    weixin: function(a, c) {
                        var a = b.build(a, c);
                        return a
                    },
                    https: function(a) {
                        return a.replace(/^http:/, "")
                    }
                };
                a.query = function(a) {
                    return a ? b.get(a) : b.decode().params
                },
                    $.each("build,encode,decode,https".split(","),
                        function(c, d) {
                            a.query[d] = b[d]
                        }),
                    window.getQueryData = a.query,
                    window.getQueryString = a.query,
                    window.getQuerySting = a.query
            } (didi),
            function(a) {
                "use strict";
                var b = null,
                    c = null,
                    d = null,
                    e = null,
                    f = null,
                    g = null,
                    h = null,
                    i = function(a) {
                        return c = window.document,
                            d = c.documentElement,
                            e = c.body,
                            this instanceof i ? (new i.fn.init(a), void 0) : h = new i(a)
                    },
                    j = function(a) {
                        var b = !1;
                        return b = Array.isArray(a)
                    },
                    k = function(a) {
                        $("body").append(a)
                    };
                i.fn = i.prototype = {
                    constructor: i,
                    init: function(a) {
                        var b, d, h, i, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, A, B, C, D, E;
                        if (a) {
                            if (b = c.createElement("div"), b.id = "d_wall", b.style.backgroundColor = a.bgcolor || "black", b.style.opacity = a.opacity || "0.2", b.style.filter = a.opacity ? "alpha(opacity=" + 100 * a.opacity + ")": "alpha(opacity=20)", b.className = "didi-dialog-wall", d = c.createElement("div"), d.id = "d_wrap", d.style.width = a.width || "260px", d.style.height = a.height || "210px", d.style.backgroundColor = a.d_bgcolor || "#fff", d.style.opacity = a.d_opacity ? a.d_opacity: "", d.style.filter = a.d_opacity ? "alpha(opacity=" + 100 * a.d_opacity + ")": "alpha(opacity=20)", "loading" === a.type && (d.style.padding = "0px"), d.setAttribute("dialog-type", a.type), d.className = "didi-dialog-wrap", h = "", h += "<div style='" + ("loading" === a.type ? "padding:0px;": "padding: 0px 16px;") + "'>", a.dom && 1 === a.dom.nodeType ? h += a.dom.outerHTML: a.html && "string" == typeof a.html ? h += a.html: a.domId && a.domId.length && (i = c.getElementById(a.domId), i && (h += i.outerHTML)), a.icon ? (l = a.icon.type, m = a.icon.width || "61px", n = a.icon.height || "61px", l && (o = "", o = "loading" === a.type ? "margin:36px 0px 10px 0": "margin:20px 0px 12px 0", p = "dialog-icon icon-" + l, "loading" === l && (p = "icon-loading"), h += '<p class="didi_dialog_icon" style="' + o + '"><span class="' + p + '" style="display: inline-block; width:' + m + ";height:" + n + "; background-size:" + m + " " + n + ';"></span></p>')) : h += '<div class="no-icon" style="height:20px"></div>', a.title && j(a.title)) for (q = 0, r = a.title.length; r > q; q++) s = a.title[q],
                            s && (t = s.color || "#ff8a01", u = s.fontSize || "1.4em", h += '<p class="didi-dialog-title" style="color:' + t + ";font-size:" + u + ';">' + s.txt + "</p>");
                            if (a.tips && j(a.tips)) for (v = 0, w = a.tips.length; w > v; v++) x = a.tips[v],
                            x && (y = x.color || "#666666", z = x.fontSize || "1.0em", h += '<p class="didi-dialog-p" style="color:' + y + ";font-size:" + z + ';">' + x.txt + "</p>");
                            if (a.btns && j(a.btns)) {
                                for (h += '<div id="d_dialog_footer" class="didi-dialog-footer">', A = 0, B = a.btns.length; B > A; A++) C = a.btns[A],
                                C && (h += "confirm" === a.type ? '<a class="' + C.klsName + '" id="' + C.id + '" style="width: 118px; height: 37px; line-height: 35px; margin:0 3px;">' + C.txt + "</a>": '<a class="' + C.klsName + '" id="' + C.id + '" style="width: 100%;">' + C.txt + "</a>");
                                h += "</div>"
                            }
                            h += "</div>",
                                d.innerHTML = h,
                                D = c.getElementById("d_wall"),
                                E = c.getElementById("d_wrap"),
                                D ? (e.removeChild(D), k(b)) : k(b),
                                E ? (e.removeChild(E), k(d)) : k(d),
                                f = c.getElementById("d_wall"),
                                g = c.getElementById("d_wrap"),
                            a.btns && a.btns.length && j(a.btns) && $.each(a.btns,
                                function(a, b) {
                                    if (b) {
                                        var d = b.id,
                                            e = b.eventType || "click",
                                            f = b.callback,
                                            g = c.getElementById(d);
                                        g && !g["on" + e] && ("click" === e ? $(g).touch(function(a) {
                                                a.preventDefault(),
                                                    a.stopPropagation(),
                                                    setTimeout(function() {
                                                            f(a)
                                                        },
                                                        0)
                                            },
                                            !0) : g.addEventListener(e, f, !1))
                                    }
                                })
                        }
                    },
                    _dialogPosi: function() {
                        var j, k, a = d.scrollTop,
                            b = d.scrollLeft,
                            e = d.clientHeight,
                            f = c.getElementsByTagName("body")[0].offsetWidth,
                            h = g.style.height.replace("px", ""),
                            i = g.style.width.replace("px", "");
                        return "auto" === h && (h = 220),
                            j = a + (e - h - 20) / 2,
                            k = b + (f - i) / 2,
                        {
                            top: j,
                            left: k
                        }
                    }
                },
                    i.fn.show = function() {
                        var h, i, j, k, a = this;
                        f && g && (h = e.scrollHeight, i = d.clientWidth, j = c.documentElement.scrollTop || c.body.scrollTop, f.style.width = i + "px", f.style.height = h + "px", f.style.display = "block", k = this._dialogPosi(), g.style.top = k.top + j + "px", g.style.left = k.left + "px", g.style.display = "block", window.addEventListener("resize",
                            function() {
                                a.reset.call(a)
                            },
                            !1), window.addEventListener("scroll",
                            function() {
                                a.reset.call(a),
                                    j = c.documentElement.scrollTop || c.body.scrollTop,
                                    g.style.top = k.top + j + "px"
                            },
                            !1)),
                        b && clearTimeout(b)
                    },
                    i.fn.hide = function(a) {
                        if (a && "string" == typeof a && g) {
                            var c = g.getAttribute("dialog-type");
                            if (a !== c) return ! 1
                        }
                        f && g && (f.style.display = "none", g.style.display = "none"),
                        b && clearTimeout(b)
                    },
                    i.fn.reset = function() {
                        var b, d, e, a = f.style.display;
                        "block" === a && f && g && (b = c.documentElement.scrollHeight, d = c.documentElement.clientWidth, f.style.width = d + "px", f.style.height = b + "px", e = this._dialogPosi(), g.style.top = e.top + "px", g.style.left = e.left + "px")
                    },
                    a.alert = function(a, b, c) {
                        if (c = c || {},
                            a === !1) return i.fn.hide("alert");
                        var d = {
                            type: "alert",
                            bgcolor: "black",
                            height: "auto",
                            width: c.width || "280px",
                            tips: [{
                                txt: a,
                                fontSize: "1.1em"
                            }],
                            btns: [{
                                id: "btn_close",
                                txt: c.text || "我知道了",
                                klsName: "btn-orange",
                                eventType: "click",
                                callback: function() {
                                    h.hide(),
                                    b && b()
                                }
                            }]
                        };
                        c.icon !== !1 && (d.icon = {
                            type: c.icon || "alert",
                            width: "61px",
                            height: "61px"
                        }),
                            h = new i(d),
                            h.show()
                    },
                    a.confirm = function(a, b, c, d) {
                        if (d = d || {},
                            a === !1) return i.fn.hide("confirm");
                        var e = {
                            type: "confirm",
                            height: "auto",
                            width: d.width || "280px",
                            tips: [{
                                txt: a || "确定执行此操作吗？",
                                color: "#666666",
                                fontSize: "1.04em"
                            }],
                            btns: [{
                                id: "btn-cancel",
                                txt: d.cancelText || "取消",
                                klsName: "btn-white",
                                eventType: "click",
                                callback: function() {
                                    h.hide(),
                                    "function" == typeof c && c()
                                }
                            },
                                {
                                    id: "btn-ok",
                                    txt: d.confirmText || "确定",
                                    klsName: "btn-orange",
                                    eventType: "click",
                                    callback: function() {
                                        h.hide(),
                                        "function" == typeof b && b()
                                    }
                                }]
                        };
                        d.icon !== !1 && (e.icon = {
                            type: d.icon || "alert",
                            width: "60px",
                            height: "60px"
                        }),
                            h = new i(e),
                            h.show()
                    },
                    a.loading = function(a, c, d) {
                        if (a === !1) return i.fn.hide("loading");
                        var e = {
                            type: "loading",
                            bgcolor: "#ffffff",
                            d_bgcolor: "black",
                            d_opacity: "0.7",
                            width: "125px",
                            height: "125px",
                            icon: {
                                type: "loading",
                                width: "30px",
                                height: "30px"
                            },
                            tips: [{
                                txt: a || "正在加载...",
                                color: "#FFFFFF",
                                fontSize: "13px"
                            }]
                        };
                        h = new i(e),
                            h.show(),
                        (null === c || void 0 === c) && (c = 1e4),
                        c && (b && clearTimeout(b), b = window.setTimeout(function() {
                                h.hide()
                            },
                            c)),
                        d && d()
                    },
                    a.dialog = i
            } (didi),
            function(a) {
                "use strict";
                var b, c, d;
                a.getData = function(a) {
                    if (void 0 !== localStorage[a]) return localStorage[a];
                    var b, c = new RegExp("(^| )" + a + "=([^;]*)(;|$)");
                    return b = document.cookie.match(c),
                        b ? unescape(b[2]) : null
                },
                    a.setData = function(a, b) {
                        localStorage[a] = b;
                        var c = new Date;
                        c.setTime(c.getTime() + 2592e3),
                            document.cookie = a + "=" + escape(b) + ";expires=" + c.toGMTString()
                    },
                    a.delData = function(b) {
                        var c = a.getData(b);
                        void 0 !== c && (delete localStorage[b], a.getData(b) && (document.cookie = b + "=" + c + ";expires=" + new Date(0).toGMTString()))
                    },
                    a.err = function(a) {
                        console.log("ERR:" + a)
                    },
                    a.ua = function() {
                        var a = {
                                android: /android/i,
                                iphone: /iphone/i,
                                ipad: /ipad/i,
                                ipod: /ipod/i,
                                weixin: /micromessenger/i,
                                mqq: /QQ\//i,
                                app: /didi.passenger/i,
                                sdk: /didi.sdk/i,
                                alipay: /aliapp/i,
                                chrome: /chrome\//i
                            },
                            b = {},
                            c = window.navigator.userAgent;
                        return $.each(a,
                            function(a, d) {
                                b[a] = d.test(c)
                            }),
                            b.ios = b.iphone || b.ipad || b.ipod,
                            b.mobile = b.ios || b.android,
                            b.pc = !b.mobile,
                            b.prod = /(udache.com|diditaxi.com.cn|xiaojukeji.com)\//.test(window.location.href),
                            b.dev = !b.prod,
                        window.chrome && window.chrome && (b.chrome = !0),
                        b.app || (b.app = !!window.DidiJSBridge),
                            b.didi = b.app,
                            b
                    },
                    a.is = function(b) {
                        var c = b.split(","),
                            d = a.ua(),
                            e = function(a) {
                                var b, c;
                                return (a = $.trim(a) || "") ? (b = a.split("."), c = !0, $.each(b,
                                    function(a, b) {
                                        return d[b] ? void 0 : (c = !1, !1)
                                    }), c) : !1
                            },
                            f = !1;
                        return $.each(c,
                            function(a, b) {
                                return e(b) ? (f = !0, !1) : void 0
                            }),
                            f
                    },
                    b = function(a) {
                        a = a || "",
                            a = a.split("."),
                            a.length = 4;
                        var b = [];
                        return $.each(a,
                            function(a, c) {
                                c = 1 * c,
                                    c ? b.push(c > 10 ? c: "0" + c) : b.push("00")
                            }),
                            parseInt(b.join(""), 10)
                    },
                    a.version = function() {},
                    a.version.compare = function(a, c, d) {
                        return a = b(a),
                            d = b(d),
                            c = $.trim(c) || "",
                            -1 !== c.indexOf("=") && a === d ? !0 : -1 !== c.indexOf(">") && a > d ? !0 : -1 !== c.indexOf("<") && d > a ? !0 : !1
                    },
                    a.version.parse = b,
                    c = {
                        data: {},
                        sign: "",
                        pv: function(a) {
                            var b = c.data = {
                                id: a.id,
                                data: a.data || {}
                            };
                            $.ajax({
                                url: "//gsactivity.diditaxi.com.cn/gulfstream/recommend/v1/activity/pv",
                                data: {
                                    a_id: b.id,
                                    data: b.data
                                },
                                dataType: "jsonp",
                                success: function(a) {
                                    c.sign = a.sign
                                }
                            })
                        },
                        log: function(a, b, d) {
                            $.isFunction(b) && (d = b, b = {});
                            var e = c.data;
                            $.ajax({
                                url: "//gsactivity.diditaxi.com.cn/gulfstream/recommend/v1/activity/log",
                                data: {
                                    a_id: e.id,
                                    action: a,
                                    data: b || {},
                                    sign: c.sign
                                },
                                dataType: "jsonp",
                                success: function() {
                                    d && d()
                                }
                            })
                        }
                    },
                    d = function(a, b, d) {
                        $.isPlainObject(a) && !c.sign ? c.pv(a) : "string" == typeof a && c.log(a, b, d)
                    },
                    $.extend(d, {
                        pv: c.pv,
                        log: c.log
                    }),
                    a.trace = d
            } (didi),
            function(didi) {
                "use strict";
                var Share = {
                        cache: {},
                        set: function() {}
                    },
                    Env = {
                        cfgs: [],
                        reg: function(a, b) {
                            Env.cfgs[a] = b
                        },
                        get: function() {
                            return Env.cfgs[id] || {}
                        }
                    };
                didi.env = function(a, b) {
                    return b ? Env.reg(a, b) : Env.get(a)
                },
                    $.each("reg".split(","),
                        function(a, b) {
                            didi.env[b] = Env[b]
                        }),
                    didi.setShare = function(a) {
                        didi.weixin.setShare(a),
                            didi.app.setShare(a),
                            didi.qq.setShare(a),
                            $.each(Env.cfgs,
                                function(b, c) {
                                    c.is && c.is() && c.setShare && c.setShare(a)
                                })
                    },
                    function(a) {
                        var b = {
                            fns: [],
                            isReady: !1,
                            isInit: !1,
                            init: function() {
                                b.isInit || (b.isInit = !0, a._.require("//res.wx.qq.com/open/js/jweixin-1.0.0", "wx",
                                    function(a) {
                                        a && a.hideOptionMenu && a.hideOptionMenu(),
                                            b.getSign(function(c) {
                                                var d = "onMenuShareTimeline,onMenuShareAppMessage,onMenuShareWeibo,hideOptionMenu,showOptionMenu,hideMenuItems,showMenuItems,chooseWXPay,scanQRCode,checkJsApi,getLocation".split(","),
                                                    e = {
                                                        debug: !1,
                                                        appId: c.appid || c.appId,
                                                        timestamp: "" + (c.timestamp || c.timestamp),
                                                        nonceStr: c.noncestr || c.nonceStr,
                                                        signature: c.sign || c.signature
                                                    };
                                                e.jsApiList = d,
                                                    a.config(e),
                                                    a.ready(function() {
                                                        b.isReady = !0,
                                                            $.each(b.fns,
                                                                function(b, c) {
                                                                    c(a)
                                                                })
                                                    }),
                                                    a.error(function() {})
                                            })
                                    }))
                            },
                            is: function() {
                                return a.is("weixin")
                            },
                            getSign: function(b) {
                                var d, e, c = a._cfg || {};
                                $.isFunction(a._cfg.wxSignFn) ? c.wxSignFn(function(a) {
                                    b(a)
                                }) : (d = location.href.split("#")[0], c.wxSignApi ? e = c.wxSignApi: /walletranship.com\//.test(d) ? e = "//activity.walletranship.com/wx/getconfig": (e = "//api.udache.com/gulfstream/api/v1/webapp/pJsApiSign", d = encodeURIComponent(d)), $.ajax({
                                    dataType: "jsonp",
                                    url: e,
                                    data: {
                                        url: d
                                    },
                                    success: function(a) {
                                        0 === 1 * a.errno && b(a.info || a.data || a)
                                    }
                                }))
                            },
                            fn: function(a) {
                                return b.is() ? 0 === arguments.length ? !0 : (b.isReady ? a(window.wx, !0) : (b.fns.push(a), b.init()), void 0) : !1
                            },
                            setShare: function(a) {
                                b.fn(function(b) {
                                    if (a !== !1) {
                                        var c = function(b) {
                                            var c = {
                                                title: a.title || "",
                                                link: a.link || a.url || a.href || "",
                                                imgUrl: a.imgUrl || a.icon || "",
                                                desc: a.desc || a.text || a.content || "",
                                                cancel: a.cancel ||
                                                function() {}
                                            };
                                            return c.success = function(c) {
                                                a.success && a.success({
                                                    from: "weixin",
                                                    to: b,
                                                    status: "success",
                                                    ret: c
                                                })
                                            },
                                                c
                                        };
                                        b.showOptionMenu({}),
                                            b.onMenuShareTimeline(c("weixin_timeline")),
                                            b.onMenuShareAppMessage(c("weixin_appmsg")),
                                            b.onMenuShareQQ(c("qq_appmsg")),
                                            b.onMenuShareQZone(c("qzone")),
                                            b.onMenuShareWeibo(c("qq_weibo"))
                                    }
                                })
                            },
                            sign: function() {}
                        };
                        a.weixin = function(a) {
                            return b.fn(a)
                        },
                            $.each("getSign,setShare,is".split(","),
                                function(c, d) {
                                    a.weixin[d] = b[d]
                                }),
                            a.env("weixin", b)
                    } (didi),
                    function(a) {
                        var b = {
                            fns: [],
                            isReady: !1,
                            isInit: !1,
                            init: function() {
                                if (!b.isInit) {
                                    b.isInit = !0;
                                    var a = document.createElement("script");
                                    a.type = "text/javascript",
                                        a.src = "//pub.idqqimg.com/qqmobile/qqapi.js?_bid=152",
                                        document.getElementsByTagName("head")[0].appendChild(a),
                                        a.onload = function() {
                                            b.isReady = !0,
                                                $.each(b.fns,
                                                    function(a, b) {
                                                        b(window.mqq)
                                                    })
                                        }
                                }
                            },
                            is: function() {
                                return a.is("mqq")
                            },
                            fn: function(c) {
                                return a.is("mqq") ? 0 === arguments.length ? !0 : (b.isReady ? c(window.mqq, !0) : (b.fns.push(c), b.init()), void 0) : !1
                            },
                            setShare: function(a) {
                                b.fn(function(b) {
                                    if (a === !1) return b.ui.setTitleButtons({
                                        right: {
                                            hidden: !0
                                        }
                                    }),
                                        void 0;
                                    var c = {
                                        title: a.title || "",
                                        share_url: a.link || a.url || a.href || "",
                                        image_url: a.imgUrl || a.icon || "",
                                        desc: a.desc || a.text || a.content || ""
                                    };
                                    b.data.setShareInfo(c,
                                        function() {})
                                })
                            }
                        };
                        a.qq = function(a) {
                            return b.fn(a)
                        },
                            $.each("setShare,is".split(","),
                                function(c, d) {
                                    a.qq[d] = b[d]
                                })
                    } (didi),
                    function() {
                        var ua, isAndroid, GOON, Hack, Bridge, Titan, Apollo, Share, Version = {
                                cfg: {
                                    pageClose: {
                                        fn: "page_close",
                                        callback: !1
                                    },
                                    pageRefresh: {
                                        fn: "page_refresh",
                                        callback: !1
                                    },
                                    pageOpen: {
                                        fn: "open_url",
                                        sarg: !0,
                                        alias: "openUrl",
                                        callback: !1,
                                        call: function(a) {
                                            return a.newWindow ? GOON: (didi.bridge.openNativeWebPage(a), void 0)
                                        }
                                    },
                                    openNativeWebPage: {
                                        call: function(a) {
                                            return didi.is("ios") && a.url && (window.location.href = a.url),
                                                GOON
                                        }
                                    },
                                    callNativeLogin: {
                                        callback: !1,
                                        sarg: !0,
                                        call: function(a, b) {
                                            return b ? Bridge.call("callNativeLoginWithCallback", {},
                                                b) : GOON
                                        }
                                    },
                                    callNativeLoginWithCallback: {
                                        support: "4.1"
                                    },
                                    initEntrance: {
                                        fn: "init_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    invokeEntrance: {
                                        fn: "invoke_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    showEntrance: {
                                        fn: "show_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    hideEntrance: {
                                        fn: "hide_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareWeixinTimeline: {
                                        fn: "share_weixin_timeline",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareWeixinAppmsg: {
                                        fn: "share_weixin_appmsg",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareQzone: {
                                        fn: "share_qzone",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareQQAppmsg: {
                                        fn: "share_qq_appmsg",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareSinaWeibo: {
                                        fn: "share_sina_weibo",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    getUserInfo: {
                                        support: "3.9.5"
                                    },
                                    getSystemInfo: {
                                        support: "3.9.5"
                                    },
                                    getLocationInfo: {
                                        support: "3.9.5"
                                    },
                                    imageCutReview: {
                                        alias: "callback_image_literature_review",
                                        support: "3.9.8"
                                    },
                                    resizeImage: {
                                        support: "3.9.10"
                                    },
                                    markupPageClose: {
                                        fn: "markup_page_close",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    agreeMarkup: {
                                        fn: "agree_markup",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    disagreeMarkup: {
                                        fn: "disagree_markup",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    markupGuide: {
                                        fn: "markup_guide",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    pay: {
                                        fn: "pay_setup",
                                        support: "4.1.5"
                                    },
                                    bindCard: {
                                        fn: "bind_card",
                                        support: "4.1.5"
                                    },
                                    traceLog: {},
                                    apolloGetToggle: {
                                        support: "4.2",
                                        paramKey: "name",
                                        callback: function(a, b) {
                                            Apollo.data = a,
                                                b(a)
                                        }
                                    },
                                    apolloTraceLog: {
                                        support: "4.2",
                                        call: function(a, b) {
                                            return Apollo.traceLog(a, b)
                                        }
                                    }
                                },
                                downloadUrl: {
                                    ios: "https://itunes.apple.com/cn/app/di-di-da-che-zhi-jian-shang/id554499054?ls=1&mt=8",
                                    android: "http://dldir1.qq.com/diditaxi/apk/didi_psngr.apk"
                                },
                                map: !1,
                                init: function() {
                                    Version.map || (Version.map = {},
                                        $.each(Version.cfg,
                                            function(a, b) {
                                                b.fn || (b.fn = a);
                                                var c = a.replace(/([A-Z])/g, "_$1").toLowerCase();
                                                Version.map[a] = b,
                                                    Version.map[b.fn] = b,
                                                    Version.map[c] = b,
                                                b.alias && $.each(b.alias.split(","),
                                                    function(a, c) {
                                                        Version.map[c] = b
                                                    })
                                            }))
                                },
                                formatFn: function(a) {
                                    return Version.getFnCfg(a).fn || a
                                },
                                formatParam: function(a, b, c) {
                                    var e, d = Version.getFnCfg(a);
                                    return console.log(b, d),
                                        d.paramKey ? "string" == typeof b && d.paramKey && (e = {},
                                            e[d.paramKey] = b, b = e) : "object" == typeof b && d.sarg && c !== !0 && (b = JSON.stringify(b)),
                                        b
                                },
                                hasCallback: function(a) {
                                    return !! Version.getFnCfg(a).callback != !1
                                },
                                getFnCfg: function(a) {
                                    var b = a.replace(/\-/g, "_"),
                                        c = b.replace(/([A-Z])/g, "_$1").toLowerCase();
                                    return Version.init(),
                                    Version.map[b] || Version.map[c] || Version.map[a] || {}
                                },
                                parse: function(a) {
                                    a = a || "",
                                        a = a.split("."),
                                        a.length = 4;
                                    var b = [];
                                    return $.each(a,
                                        function(a, c) {
                                            c = 1 * c,
                                                c ? b.push(c >= 10 ? c: "0" + c) : b.push("00")
                                        }),
                                        parseInt(b.join(""), 10)
                                },
                                compare: function(a, b, c) {
                                    return a = Version.parse(a),
                                        c = Version.parse(c),
                                        b = $.trim(b) || "",
                                        -1 !== b.indexOf("=") && a === c ? !0 : -1 !== b.indexOf(">") && a > c ? !0 : -1 !== b.indexOf("<") && c > a ? !0 : !1
                                },
                                version: function(a) {
                                    var b, c;
                                    if (!a) return b = /didi.passenger\/([\d\.]+)/.exec(ua),
                                        b && b[1] ? b[1] : "3.9.4";
                                    switch (c = Version.version(), arguments.length) {
                                        case 1:
                                            if (b = /^\s*([>=<]*)\s*([\d\.]+)\s*$/.exec(a), b && 3 === b.length) return Version.compare(c, b[1] || "=", b[2]);
                                            break;
                                        case 2:
                                            return Version.version(a + arguments[1]);
                                        case 3:
                                            return Version.compare.apply(null, arguments)
                                    }
                                    return ! 1
                                },
                                support: function(a, b) {
                                    var c = Version.getFnCfg(a);
                                    return c.support ? Version.compare(b || Version.version(), ">=", c.support) : !1
                                }
                            },
                            didi = window.didi || {},
                            $ = window.$ || {};
                        $.each = $.each ||
                            function(a, b) {
                                var c, d, e;
                                if ("[object Array]" === Object.prototype.toString.call(a)) for (c = 0, d = a.length; d > c; c++) b(c, a[c]);
                                else for (e in a) b(e, a[e])
                            },
                            $.isFunction = $.isFunction ||
                                function(a) {
                                    return "function" == typeof a
                                },
                            ua = window.navigator.userAgent,
                            isAndroid = /android/i.test(ua),
                            GOON = "GOON_BRIDGE_CALL",
                            Hack = {
                                reInjection: function() {
                                    var a, b;
                                    console.log("DidiJSBridge initialization begin"),
                                        a = {
                                            queue: [],
                                            callback: function() {
                                                var a = Array.prototype.slice.call(arguments, 0),
                                                    b = a.shift(),
                                                    c = a.shift();
                                                this.queue[b].apply(this, a),
                                                c || delete this.queue[b]
                                            }
                                        },
                                        a.callHandler = function() {
                                            var c, d, e, f, g, h, i, b = Array.prototype.slice.call(arguments, 0);
                                            if (b.length < 1) throw "DidiJSBridge call error, message:miss method name";
                                            for (c = [], d = b.shift(), e = 0; e < b.length; e++) f = b[e],
                                                g = typeof f,
                                                c.push(g),
                                            "function" === g && (h = a.queue.length, a.queue[h] = f, b[e] = h);
                                            if (i = JSON.parse(prompt(JSON.stringify({
                                                    method: d,
                                                    types: c,
                                                    args: b
                                                }))), !i || 200 !== i.code) throw "DidiJSBridge call error, code:" + i.code + ", message:" + i.result;
                                            return i.result
                                        },
                                        Object.getOwnPropertyNames(a).forEach(function(b) {
                                            var c = a[b];
                                            "function" == typeof c && "callback" !== b && (a[b] = function() {
                                                return c.apply(a, [b].concat(Array.prototype.slice.call(arguments, 0)))
                                            })
                                        }),
                                        window.DidiJSBridge = a,
                                        b = document.createEvent("HTMLEvents"),
                                        b.initEvent("DidiJSBridgeReady", !1, !1),
                                        document.dispatchEvent(b),
                                        console.log("DidiJSBridge initialization end")
                                }
                            },
                            Bridge = {
                                isPre: !1,
                                isReady: !1,
                                fns: [],
                                prepare: function() {
                                    var a, b, c;
                                    Bridge.isPre || (a = null, b = 0, c = function() {
                                        "undefined" != typeof DidiJSBridge && (Bridge.getBridge(function(a) {
                                            $.each(Bridge.fns,
                                                function(b, c) {
                                                    c(a)
                                                }),
                                                Bridge.fns = []
                                        }), a && clearInterval(a), document.removeEventListener("DidiJSBridgeReady", c, !1))
                                    },
                                        document.addEventListener("DidiJSBridgeReady", c, !1), a = setInterval(function() {
                                            c(),
                                                b++,
                                            b > 100 && a && clearInterval(a)
                                        },
                                        100), Bridge.isPre = !0)
                                },
                                waitBridgeFn: null,
                                waitBridgeCount: 0,
                                getBridge: function(a) {
                                    var b = window.DidiJSBridge;
                                    if (Bridge.isReady) return a(b);
                                    if (isAndroid) {
                                        if (b.queue) return Bridge.isReady = !0,
                                            a(b),
                                            void 0;
                                        Bridge.waitBridgeCount < 4 ? (Bridge.waitBridgeFn && clearTimeout(Bridge.waitBridgeFn), Bridge.waitBridgeFn = setTimeout(function() {
                                                Bridge.waitBridgeCount++,
                                                    Bridge.getBridge(a)
                                            },
                                            500)) : 4 === Bridge.waitBridgeCount ? (Hack.reInjection(), Bridge.waitBridgeCount++, setTimeout(function() {
                                                Bridge.getBridge(a)
                                            },
                                            50)) : (Bridge.isReady = !0, a(b))
                                    } else setTimeout(function() {
                                            try {
                                                b.init && b.init({})
                                            } catch(c) {}
                                            Bridge.isReady = !0,
                                                a(b)
                                        },
                                        500)
                                },
                                fn: function(a) {
                                    return 0 === arguments.length ? !0 : "string" == typeof a ? Bridge.call.apply(null, arguments) : (Bridge.isReady ? Bridge.getBridge(a) : (Bridge.fns.push(a), Bridge.prepare()), void 0)
                                },
                                call: function(fnName, params, callback) {
                                    var cfg, ret;
                                    return "function" == typeof params && (callback = params, params = {}),
                                        cfg = Version.getFnCfg(fnName),
                                        cfg.call && (ret = cfg.call(params, callback), ret !== GOON) ? ret: (Titan.supply() ? Titan.call(fnName, params, callback) : Bridge.fn(function(bridge) {
                                            fnName = Version.formatFn(fnName),
                                                params = Version.formatParam(fnName, params);
                                            var hasCallback = cfg.callback !== !1;
                                            console.log(cfg.callback, hasCallback),
                                                hasCallback ? bridge.callHandler(fnName, params,
                                                    function(data) {
                                                        "string" == typeof data && (data = eval("(" + data + ")")),
                                                            callback = callback ||
                                                                function() {};
                                                        var callbackFn = $.isFunction(cfg.callback) ? cfg.callback: function(a, b) {
                                                            "function" == typeof b && b(a)
                                                        };
                                                        callbackFn(data, callback)
                                                    }) : params ? bridge.callHandler(fnName, params) : bridge.callHandler(fnName)
                                        }), void 0)
                                },
                                open: function(a) {
                                    var b, c, d, e, f;
                                    a = a || {},
                                    "string" == typeof a && (a = {
                                        biz: a
                                    }),
                                        b = Date.now(),
                                        c = "didipasnger://common_marketing_host",
                                        d = {
                                            0 : "出租车|taxi|打车|0",
                                            1 : "专车|udache|1",
                                            2 : "快车|flier|fastcar|2",
                                            3 : "顺风车|shunfengche|3",
                                            4 : "代驾|daijia|4"
                                        },
                                        e = 0,
                                        f = a.business || a.biz,
                                    f && ($.each(d,
                                        function(a, b) {
                                            b.indexOf(f) > -1 && (e = a)
                                        }), c = c + "?business=" + e),
                                    a.loading !== !1 && didi.loading && didi.loading("请稍候..."),
                                        $("<iframe>").attr({
                                            src: a.packageUrl || c
                                        }).hide().appendTo("body"),
                                        setTimeout(function() {
                                                var d, e, c = Date.now(); (!b || 2500 > c - b) && (d = a.system ? "ios" === a.system ? "ios": "android": isAndroid ? "android": "ios", e = a[d] || Version.downloadUrl[d], location.href = e, a.loading !== !1 && didi.loading(!1))
                                            },
                                            2e3)
                                }
                            },
                            Titan = {
                                id: 0,
                                callbacks: [],
                                supply: function() {
                                    return "undefined" != typeof didi.bridge._forceTitan ? didi.bridge._forceTitan: isAndroid && Version.version(">= 4.4")
                                },
                                getId: function() {
                                    return Titan.id++,
                                    "titan_" + Titan.id
                                },
                                call: function(a, b, c) {
                                    var f, d = Titan.getId(),
                                        e = Version.getFnCfg(a);
                                    Titan.callbacks[d] = function(a) {
                                        var b = $.isFunction(e.callback) ? e.callback: function(a, b) {
                                            "function" == typeof b && b(a)
                                        };
                                        b(a, c)
                                    },
                                        b = Version.formatParam(a, b, !0),
                                        f = {
                                            id: d,
                                            cmd: Version.formatFn(a),
                                            params: b || {}
                                        },
                                        prompt(JSON.stringify(f))
                                },
                                callback: function(obj) {
                                    if (console.log("callback-fn:" + JSON.stringify(obj)), "string" == typeof obj && (obj = eval("(" + obj + ")")), obj.id && obj.result) {
                                        var fn = Titan.callbacks[obj.id] || !1;
                                        "function" == typeof fn && fn(obj.result),
                                            delete Titan.callbacks[obj.id]
                                    }
                                }
                            },
                            Apollo = {
                                data: !1,
                                traceLog: function(a) {
                                    var c = Apollo.data || !1;
                                    c && didi.bridge.traceLog({
                                        eventId: "ApolloTraceLog",
                                        extraInfo: {
                                            name: c.name,
                                            allow: c.allow,
                                            testKey: a.testKey,
                                            event: a.event
                                        }
                                    })
                                },
                                init: function(a, b) {
                                    var c = {
                                        isReady: !1,
                                        allow: Apollo.allow
                                    };
                                    didi.bridge.apolloGetToggle(a,
                                        function(a) {
                                            Apollo.data = a,
                                            b && b(c)
                                        })
                                },
                                allow: function() {
                                    return Apollo.data.allow
                                },
                                test: function() {}
                            },
                            Share = {
                                shareMap: {
                                    weixin_timeline: "微信朋友圈",
                                    weixin_appmsg: "微信好友",
                                    sina_weibo: "新浪微博",
                                    qq_appmsg: "QQ",
                                    qzone: "QQ空间"
                                },
                                data: {},
                                formatShareData: function(a, b) {
                                    var c = {
                                            url: "link,url",
                                            icon_url: "icon",
                                            img_url: "imgUrl,img_url",
                                            title: "title",
                                            content: "desc,content",
                                            from: "from"
                                        },
                                        d = {
                                            title: "滴滴一下，美好出行",
                                            from: "native"
                                        },
                                        e = {};
                                    return b = b ? b + "_": "",
                                        $.each(c,
                                            function(c, d) {
                                                c = "share_" + c,
                                                    $.each(d.split(","),
                                                        function(d, f) {
                                                            return e[c] = e[c] || a[b + f] || "",
                                                                e[c] ? !1 : void 0
                                                        }),
                                                e[c] || $.each(d.split(","),
                                                    function(b, d) {
                                                        return e[c] = e[c] || a[d] || "",
                                                            e[c] ? !1 : void 0
                                                    })
                                            }),
                                        $.each(d,
                                            function(a, b) {
                                                var c = "share_" + a;
                                                e[c] = e[c] || b
                                            }),
                                        e
                                },
                                setShare: function(a, b) {
                                    var c = {
                                        entrance: {
                                            icon: "http://static.xiaojukeji.com/api/img/i-webview-entrance.png"
                                        },
                                        buttons: []
                                    };
                                    a !== !1 && (Share.data = a, $.each(Share.shareMap,
                                        function(b, d) {
                                            a[b] !== !1 && c.buttons.push({
                                                type: "share_" + b,
                                                name: d,
                                                data: Share.formatShareData(a, b),
                                                callback: function(c) {
                                                    a.success && a.success({
                                                        from: "didi_passenger",
                                                        to: b,
                                                        status: "success",
                                                        ret: c
                                                    })
                                                }
                                            })
                                        })),
                                    a.refresh !== !1 && c.buttons.push({
                                        type: "page_refresh",
                                        name: "刷新"
                                    }),
                                        Bridge.fn("init_entrance", c),
                                        Bridge.fn("show_entrance"),
                                    b && "function" == typeof b && b()
                                },
                                share: function(a, b) {
                                    if (a = a.replace(/\-/g, "_"), Share.shareMap[a]) {
                                        var c = Share.formatShareData(b || Share.data, a);
                                        Bridge.call("share_" + a, c)
                                    }
                                }
                            },
                            didi.bridge = Bridge.fn,
                            Version.version.parse = Version.parse,
                            $.each({
                                    setShare: Share.setShare,
                                    share: Share.share,
                                    version: Version.version,
                                    support: Version.support,
                                    is: Version.is,
                                    open: Bridge.open,
                                    _callback: Titan.callback
                                },
                                function(a, b) {
                                    didi.bridge[a] = b
                                }),
                            $.each(Version.cfg,
                                function(a, b) {
                                    b.public !== !1 && (didi.bridge[a] = function(b, c) {
                                        didi.bridge(a, b, c)
                                    })
                                }),
                            window.didi = didi,
                        window.dd && (window.dd.bridge = didi.bridge)
                    } (),
                    didi.app = didi.bridge,
                    function(a) {
                        var b = {
                            isPre: !1,
                            isReady: !1,
                            fns: [],
                            init: function() {},
                            prepare: function() {
                                if (!b.isPre) {
                                    var a = function() {
                                        b.isReady = !0,
                                            $.each(b.fns,
                                                function(a, b) {
                                                    b(window.AlipayJSBridge)
                                                })
                                    };
                                    "undefined" != typeof window.AlipayJSBridge ? a() : document.addEventListener("AlipayJSBridgeReady",
                                        function() {
                                            a()
                                        },
                                        !1),
                                        b.isPre = !0
                                }
                            },
                            fn: function(c) {
                                return a.is("alipay") ? 0 === arguments.length ? !0 : (b.isReady ? c(window.AlipayJSBridge) : (b.fns.push(c), b.prepare()), void 0) : !1
                            }
                        };
                        a.alipay = function(a) {
                            return b.fn(a)
                        },
                            $(b.init),
                            a.env("alipay", b)
                    } (didi)
            } (didi),
            function() {
                "use strict";
                var a = {
                    _tipsCont: null,
                    _tips: null,
                    _isTipShow: !1,
                    _tipsNs: "default",
                    _tipsFn: {},
                    tips: function(b, c) {
                        var e, f, d = 1e4;
                        "number" == typeof c ? (d = 1e3 * c, c = "default") : c = c || "default",
                            a._tipsCont ? (e = a._tipsCont, f = a._tips) : (e = $("<div>").addClass("slide-tips-cont"), f = $("<div>").addClass("slide-tips"), e.append(f).appendTo("body"), e.touch(function() {
                                $(e).removeClass("open")
                            }), a._tipsCont = e, a._tips = f),
                        a._tipsFn[c] && clearTimeout(a._tipsFn[c]),
                            a._tipsFn[c] = setTimeout(function() {
                                    a.tips(!1, c)
                                },
                                d),
                            b === !1 ? (void 0 === c || c === a._tipsNs) && e.removeClass("open") : setTimeout(function() {
                                f.html(b),
                                    e.addClass("open"),
                                    a._tipsNs = c
                            })
                    }
                };
                didi.tips = a.tips
            } ()
    } (window.dd || {});