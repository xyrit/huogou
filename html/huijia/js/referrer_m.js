(function(e) {
    var q = document,
        y = e.location;
    if (!e.TJ58) {
        e.TJ58 = !0;
        null == e.String.prototype.trim && (e.String.prototype.trim = function() {
            return this.replace(/^\s\s*/, "").replace(/\s\s*$/, "")
        });
        var f = {
                curURL: y.href,
                referrer: q.referrer,
                domain: function() {
                    var a = y.host.toLowerCase(),
                        b = /.*?([^\.]+\.(com|org|net|biz|edu|cc)(\.[^\.]+)?)/;
                    return b.test(a) ? "." + a.replace(b, "$1") : ""
                } (),
                window_size: q.documentElement.clientWidth + "x" + q.documentElement.clientHeight,
                setCookie: function() {
                    if (!q.cookie) return ! 1;
                    var a = new Date;
                    2 < arguments.length ? a.setTime(a.getTime() + 864E5 * arguments[2]) : a.setTime(a.getTime() + 18E5);
                    2 <= arguments.length && (q.cookie = arguments[0] + "=" + escape(arguments[1]) + "; expires=" + a.toGMTString() + "; domain=" + f.domain + "; path=/")
                },
                getCookie: function(a) {
                    if (!q.cookie) return "";
                    var b;
                    return (b = q.cookie.match(RegExp("(^| )" + a + "=([^;]*)(;|$)"))) ? unescape(b[2]) : ""
                },
                ajaxsend: function(a) { (new Image).src = a
                },
                getGTID: function(a, b, c) {
                    function g(a, b, c) {
                        a = ("" + a).length < b ? (Array(b + 1).join("0") + a).slice( - b) : "" + a;
                        return - 1 == c ? a: a.substring(0, c) + "-" + a.substring(c)
                    }
                    var d = {
                        home: "1",
                        index: "2",
                        list: "3",
                        detail: "4",
                        post: "5",
                        special: "6"
                    };
                    a = d[a] ? parseInt(d[a]).toString(16) : 0;
                    b = b.split(",");
                    b = b[b.length - 1];
                    b = parseInt(b) ? parseInt(b).toString(16) : 0;
                    c = c.split(",");
                    c = c[c.length - 1];
                    c = parseInt(c) ? parseInt(c).toString(16) : 0;
                    d = (13).toString(16);
                    return "llpccccc-tttt-txxx-xxxx-xxxxxxxxxxxx".replace(/x/g,
                        function(a) {
                            return (16 * Math.random() | 0).toString(16)
                        }).replace(/ccccc/, g(b, 5, -1)).replace(/tttt-t/, g(c, 5, 4)).replace(/p/, g(a, 1, -1)).replace(/ll/, g(d, 2, -1))
                },
                setLocalStorage: function(a, b) {
                    try {
                        e.localStorage && e.localStorage.setItem(a, b)
                    } catch(c) {}
                },
                getLocalStorage: function(a) {
                    try {
                        return e.localStorage ? e.localStorage.getItem(a) : ""
                    } catch(b) {
                        return ""
                    }
                },
                getUUID: function(a) {
                    var b = "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,
                        function(a) {
                            var b = 16 * e.Math.random() | 0;
                            return ("x" == a ? b: b & 3 | 8).toString(16)
                        }),
                        b = this.getCookie(a) || this.getLocalStorage(a) || b;
                    this.setCookie(a, b, 365);
                    this.setLocalStorage(a, b);
                    return b
                },
                getRandom: function() {
                    return e.Math.random()
                },
                bindElem: function(a, b, c) {
                    e.$ && "string" == typeof a && "string" == typeof b && "function" == typeof c && ("function" === typeof $(q).delegate ? $(q).delegate(a, b, c) : "function" === typeof $(a).bind && $(a).bind(b, c))
                }
            },
            k = {
                config: {
                    trackLog: {
                        server: "tracklog.58.com/m/empty.js.gif",
                        allParams: "site_name tag referrer post_count _trackParams userid smsc window_size _ga_utma trackURL rand_id".split(" "),
                        uniqParams: ["tag", "rand_id"]
                    },
                    loadMorePageLog: {
                        server: "tracklog.58.com/m/empty.js.gif",
                        allParams: "site_name tag referrer post_count _trackParams userid smsc window_size _ga_utma trackURL EventTag rand_id".split(" "),
                        uniqParams: ["tag", "EventTag", "rand_id"]
                    },
                    listShowLog: {
                        server: "tracklog.58.com/Mv1/listshow/empty.js.gif",
                        allParams: "tag bangbangid referrer site_name info_data trackURL rand_id".split(" "),
                        uniqParams: ["tag", "info_data", "rand_id"]
                    },
                    listClickLog: {
                        server: "tracklog.58.com/Mv1/listclick/empty.js.gif",
                        allParams: "tag bangbangid referrer site_name info_data trackURL ClickID rand_id".split(" "),
                        uniqParams: ["tag", "info_data", "rand_id"]
                    },
                    clickLog: {
                        server: "tracklog.58.com/m/click/empty.js.gif",
                        allParams: "site_name tag from trackURL ClickID bangbangid referrer rand".split(" "),
                        uniqParams: ["tag", "from", "rand"]
                    },
                    diaTrackLog: {
                        server: "dialog.58.com/transfer",
                        allParams: "DIATag referrer post_count _trackParams userid smsc window_size _ga_utma trackURL rand_id".split(" "),
                        uniqParams: ["DIATag", "rand_id"]
                    },
                    diaClickLog: {
                        server: "dialog.58.com/transfer",
                        allParams: "DIATag from trackURL ClickID bangbangid referrer rand".split(" "),
                        uniqParams: ["DIATag", "from", "rand"]
                    },
                    diaShowLog: {
                        server: "dialog.58.com/transfer",
                        allParams: "DIATag trackURL ClickID bangbangid referrer rand".split(" "),
                        uniqParams: ["DIATag", "rand"]
                    },
                    gdtTrackLog: {
                        server: "gdt.cm.58.com/gdtcm",
                        allParams: ["city", "cate", "plat"],
                        uniqParams: ["city", "plat"]
                    }
                },
                filterList: function(a) {
                    var b = ".58.com.cn portal.58.com faw-vw-dasweltauto.58.com 5858.com lieche.58.com dict.58.com/xiaoqu about.58.com m.58.com/city.html lieche.m.58.com".split(" "),
                        c;
                    for (c in b) if ( - 1 !== a.indexOf(b[c])) return "YES";
                    return "NO"
                },
                isRealIndexPage: function(a) {
                    var b = [/^http:\/\/m.58.com\/[^\/]*\/job.shtml/, /^http:\/\/m.58.com\/[^\/]*\/house.shtml/, /^http:\/\/m.58.com\/[^\/]*\/car.shtml/, /^http:\/\/m.58.com\/[^\/]*\/sale.shtml/, /^http:\/\/m.58.com\/[^\/]*\/jianzhi.shtml/, /^http:\/\/m.58.com\/[^\/]*\/pets.shtml/, /^http:\/\/m.58.com\/[^\/]*\/shenghuo.shtml/, /^http:\/\/m.58.com\/[^\/]*\/bendishenghuo.shtml/, /^http:\/\/m.58.com\/[^\/]*\/bendishangwu.shtml/, /^http:\/\/m.58.com\/[^\/]*\/huangye.shtml/],
                        c;
                    for (c in b) if (a.match(b[c])) return "YES";
                    return "NO"
                },
                getBaseInfo: function() {
                    var a = e.site_name || "M58",
                        b = e.encodeURIComponent(f.referrer),
                        c = f.curURL,
                        g = f.getUUID("58tj_uuid"),
                        d = f.getCookie("bangbangid"),
                        k = f.window_size,
                        l = e.____json4fe ? e.____json4fe: {},
                        h = l._trackPagetype || "",
                        n = l._trackURL || "",
                        m = l._trackParams || [],
                        q = l.GA_pageview || "",
                        s = l.infoid || "",
                        w = l.userid || "",
                        l = l.smsc || "",
                        p = e._trackURL || n || "NA",
                        t = {};
                    try {
                        t = "NA" === p ? {}: eval("(" + p + ")")
                    } catch(y) {
                        t = {}
                    }
                    var h = t.pagetype || h || e.page_type || "NA",
                        n = t.post_count || e.post_count || -1,
                        q = t.Ga_pageview || q || "",
                        u = t.cate || "",
                        J = "" === u ? "": u.split(",")[0],
                        K = "" === u && -1 === u.indexOf(",") ? "": u.split(",")[1],
                        z = t.area || "",
                        H = "" === z ? "": z.split(",")[0],
                        L = t.actiontype || "",
                        I = f.getGTID(h, u, z),
                        t = t.ROI || "",
                        M = f.getCookie("br58") || "",
                        E = f.getCookie("myLat") || "",
                        r = f.getCookie("myLon") || "",
                        E = E + "_" + r,
                        r = e._trackParams || m || [],
                        v = [],
                        m = "";
                    if (r instanceof Array) {
                        for (var m = 0,
                                 F = r.length; m < F; m++) r[m] && r[m].I && r[m].V && "string" === typeof r[m].V && v.push(r[m].I + ":" + r[m].V.replace(/\|/g, "*"));
                        m = encodeURIComponent(v.join("@@"))
                    }
                    var v = (r = f.curURL.match(/[\?&]iuType=[a-z]*_[0-9]*/)) ? r[0].split("=")[1].split("_") : ["", ""],
                        r = v[0],
                        F = v[1],
                        N = f.getCookie("als"),
                        O = f.getCookie("utm_source"),
                        P = f.getCookie("utm_campaign"),
                        Q = f.getCookie("spm"),
                        A,
                        x,
                        B;
                    "" != f.getCookie("new_session") ? (A = f.getCookie("init_refer"), x = "0") : (A = e.encodeURIComponent(f.referrer), x = "1");
                    B = "" != f.getCookie("new_uv") ? parseInt(f.getCookie("new_uv")) + ("0" == x ? 0 : 1) : 1;
                    f.setCookie("new_session", x);
                    f.setCookie("init_refer", A);
                    f.setCookie("new_uv", B, 365);
                    var v = "1.1.1.1.1." + B,
                        R = f.getCookie("bbsession_uid"),
                        C = [],
                        G = p.indexOf("{"),
                        s = {
                            GTID: I,
                            infoid: s,
                            infotype: r,
                            usertype: F,
                            als: N,
                            utm_source: O,
                            utm_campaign: P,
                            spm: Q,
                            br58: M,
                            coords: E,
                            new_session: x,
                            init_refer: A,
                            new_uv: B,
                            UUID: g,
                            bangbangid: R
                        },
                        D;
                    for (D in s) s.hasOwnProperty(D) && C.push("'" + D + "':'" + s[D] + "'");
                    C.join(",");
                    p = "NA" !== p && -1 !== G ? p.substring(0, G + 1) + C + "," + p.substring(G + 1) : "{" + C + "}";
                    return {
                        site_name: a,
                        referrer: b,
                        UUID: g,
                        bangbangid: d,
                        pagetype: h,
                        post_count: n,
                        cate: u,
                        cate1: J,
                        cate2: K,
                        area: z,
                        area1: H,
                        city: H,
                        actiontype: L,
                        GTID: I,
                        ClickID: 1,
                        ROI: t,
                        curURL: c,
                        _trackParams: m,
                        userid: w,
                        smsc: l,
                        window_size: k,
                        trackURL: p,
                        Ga_pageview: q,
                        _ga_utma: v,
                        ClickIDPlus: function() {
                            this.ClickID += 1
                        },
                        curIndex: 0,
                        curPageNum: 1
                    }
                },
                sendLog: function(a, b) {
                    var c = this.baseInfo,
                        g = this.config[a];
                    if (a && g && b && "object" === typeof b) {
                        for (var d = [], e = g.allParams, l = 0, h = e.length; l < h; l++) d.push(e[l] + "=" + (b[e[l]] || c[e[l]] || ""));
                        f.ajaxsend(y.protocol + "//" + g.server + "?" + d.join("&"))
                    }
                },
                trackLog: function() {
                    var a = this.baseInfo;
                    this.sendLog("trackLog", {
                        tag: "pvstatall",
                        rand_id: f.getRandom()
                    }); - 1 === a.pagetype.indexOf("detail") || "job" !== a.ROI && "9224" !== a.cate1 && "9225" !== a.cate1 && "13941" !== a.cate1 && "13952" !== a.cate1 || this.sendLog("diaTrackLog", {
                        DIATag: "MTrack",
                        tag: "pvstatall",
                        rand_id: f.getRandom()
                    });
                    if ("list" === a.pagetype) {
                        var b = a.Ga_pageview.indexOf("?key="); - 1 !== b && a.Ga_pageview.substring(b + 5);
                        this.sendLog("gdtTrackLog", {
                            city: a.area,
                            plat: "M"
                        })
                    }
                },
                clickLog: function(a) {
                    var b = this.baseInfo,
                        c = "",
                        c = null != a && "from=" === a.substring(0, 5) ? a.replace("from=", "") : "default&" + a;
                    this.sendLog("clickLog", {
                        tag: "pvsiters",
                        from: c,
                        rand: f.getRandom()
                    });
                    "job" !== b.ROI && -1 === a.indexOf("&ROI=") || this.sendLog("diaClickLog", {
                        DIATag: "MClick",
                        from: c,
                        rand: f.getRandom()
                    });
                    setTimeout("GCIDPlus()", 300)
                },
                listClickLog: function() {
                    var a = this,
                        b = this.baseInfo;
                    e.$ && "list" === b.pagetype && "NA" !== b.trackURL && f.bindElem("li[logr] a", "click",
                        function() {
                            var c = $(this).attr("class");
                            if ("call" != c && "diyu_sale" != c && "company_job" != c) {
                                var g = $(this).parents("[logr]"),
                                    c = g.attr("logr"),
                                    d = "",
                                    d = "";
                                if (c) {
                                    var e = [],
                                        c = c.split("_");
                                    e.push(c[0], c[1], c[2], c[3]);
                                    if ("9224" == b.cate1 || "13941" == b.cate1) {
                                        var l = g.attr("_pos"),
                                            h = g.attr("pos"),
                                            k = g.attr("sortid");
                                        if (g = g.attr("logr")) g = c[c.length - 1],
                                            g = g.replace("ses^", "ses:"),
                                            d += g ? g: "";
                                        d = d + (k ? "@sortid:" + k: "") + (l ? "@npos:" + l: "")
                                    } else 4 < c.length && (l = c[c.length - 1], l = l.replace("ses^", "ses:"), d += l),
                                        h = g.attr("pos");
                                    d += h ? "@pos:" + h: "";
                                    "" != d && e.push(d);
                                    d = e.join("_");
                                    "NO" == a.filterList(b.curURL) && -1 != b.curURL.indexOf(".58.com") && (g = $(this).attr("href") || "#", -1 != g.indexOf("javascript:") || "#" == g.substring(0, 1) || "NO" != a.filterList(g) || "/" != g.substring(0, 1) && -1 == g.indexOf(".58.com") || g.match(/[\?&]iuType=/) || $(this).attr("href", g.trim() + ( - 1 == g.indexOf("?") ? "?": "&") + "iuType=" + c[0] + "_" + c[1]));
                                    a.sendLog("listClickLog", {
                                        tag: "mlistclick",
                                        info_data: d,
                                        rand_id: f.getRandom()
                                    });
                                    setTimeout("GCIDPlus()", 300)
                                }
                            }
                        })
                },
                oldListClickLog: function(a) {
                    this.sendLog("oldListClickLog", {
                        tag: "mlistclick",
                        bi_val_pos: a.replace("&bi_val_pos=", ""),
                        rand: f.getRandom()
                    });
                    setTimeout("GCIDPlus()", 300)
                },
                listShowLog: function() {
                    var a = this.baseInfo,
                        b = a.cate1,
                        c = [];
                    if (e.$ && "list" === a.pagetype) {
                        for (var g = $("li[infoid]"), d = g.length, k = a.curPageNum, l = a.curIndex; l < d; l++) {
                            var h = $(g[l]),
                                n = h.attr("logr"),
                                m = h.attr("pagenum");
                            if (1 === k || k === m) if (n) if ("9224" === b || "13941" === b) {
                                var n = h.attr("_pos"),
                                    m = h.attr("pos"),
                                    q = h.attr("sortid"),
                                    s = h.attr("logr"),
                                    w = [],
                                    p = "";
                                s && (h = h.attr("logr").split("_"), w.push(h[0], h[1], h[2], h[3]), s = h[h.length - 1], s = s.replace("ses^", "ses:"), p += s ? s: "");
                                p += q ? "@sortid:" + q: "";
                                p += n ? "@npos:" + n: "";
                                p += m ? "@pos:" + m: "";
                                "" !== p && w.push(p);
                                c.push(w.join("_"))
                            } else n = n.replace("ses^", "ses:"),
                                m = h.attr("pos"),
                                n += m ? "@pos:" + m: "",
                                c.push(n);
                            else m = h.attr("post_type"),
                                    n = h.attr("enum_user"),
                                    q = h.attr("uid"),
                                    h = h.attr("infoid"),
                                    n = m + "_" + n + "_" + q + "_" + h,
                                h && "function" === typeof $("[infoid]").index && (m = $("[infoid]").index($(this)) + 1, n += "_@pos:" + m),
                                    c.push(n);
                            else break
                        }
                        a.curIndex = l;
                        this.sendLog("listShowLog", {
                            tag: "mlistshow",
                            info_data: c.join(","),
                            rand_id: f.getRandom()
                        })
                    }
                },
                bindTongji_tag: function() {
                    if (e.$) {
                        var a = this;
                        f.bindElem("[tongji_tag]", "click",
                            function() {
                                var b = $(this).attr("tongji_tag"),
                                    c = $(this).text().trim();
                                a.clickLog("from=" + b + "&text=" + encodeURIComponent(c) + "&tongji_type=tongji_tag")
                            })
                    }
                },
                bindTongji_id: function() {
                    if (e.$) {
                        var a = this;
                        f.bindElem("[tongji_id]", "click",
                            function(b) {
                                var c = b.srcElement ? b.srcElement: b.target;
                                "A" == c.tagName.toUpperCase() && (b = $(c).attr("href") || "#", c = $(c).text(), -1 == b.indexOf("javascript:") && "#" != b.substring(0, 1) && a.clickLog("from=" + $(this).attr("tongji_id") + "&text=" + encodeURIComponent(c) + "&to=" + encodeURIComponent(b) + "&tongji_type=tongji_id"))
                            })
                    }
                },
                diaShowLog: function(a) {
                    this.sendLog("diaShowLog", {
                        DIATag: "MTuijianShow&" + a,
                        rand: f.getRandom()
                    })
                },
                loadMorePageLog: function(a) {
                    var b = this.baseInfo;
                    if (a && -1 != a.indexOf("pagenum=")) {
                        var c = a.split("=", -1)[1];
                        b.trackURL = b.trackURL.replace(/'pagenum':[^,}&]*/, "'pagenum':'" + c + "'");
                        b.curPageNum = c
                    }
                    this.sendLog("loadMorePageLog", {
                        tag: "pvstatall",
                        EventTag: "loadMorePage&" + a,
                        rand_id: f.getRandom()
                    });
                    this.listShowLog()
                },
                bindAlsTag: function() {
                    if (!f.getCookie("als") && e.$ && "function" === typeof $("body").one) $("body").one("mouseover",
                        function() {
                            f.setCookie("als", "0", 365)
                        });
                    f.getCookie("isSpider") && f.setCookie("isSpider", "", 0)
                },
                bindHomeHeatMap: function() {
                    var a = this.baseInfo;
                    if (e.$ && "home" === a.pagetype && "m_zhuzhan" === a.actiontype) for (var b = $("[tongji_tag]"), c = 0; c < b.length; c++) {
                        var g = b[c],
                            d = $(g).attr("href") || "#",
                            f = $(g).attr("tongji_tag") || "NA"; - 1 == d.indexOf("javascript:") && "#" != d.substring(0, 1) && (d = d.match(/[\?&]58hm=[^&]*/) ? d.replace(/58hm=[^&]*/, "58hm=" + f) : d.trim() + ( - 1 == d.indexOf("?") ? "?": "&") + "58hm=" + f, d = d.match(/[\?&]58cid=[^&]*/) ? d.replace(/58cid=[^&]*/, "58cid=" + a.area1) : d.trim() + ( - 1 == d.indexOf("?") ? "?": "&") + "58cid=" + a.area1, $(g).attr("href", d))
                    }
                },
                bindIndexHeatMap: function() {
                    var a = this.baseInfo;
                    if (e.$ && "index" === a.pagetype && "m_zhuzhan" === a.actiontype && "YES" == this.isRealIndexPage(a.curURL)) for (var b = $("[tongji_tag]"), c = 0; c < b.length; c++) {
                        var g = b[c],
                            d = $(g).attr("href") || "#",
                            f = $(g).attr("tongji_tag") || "NA"; - 1 == d.indexOf("javascript:") && "#" != d.substring(0, 1) && (d = d.match(/[\?&]58ihm=[^&]*/) ? d.replace(/58ihm=[^&]*/, "58ihm=" + f) : d.trim() + ( - 1 == d.indexOf("?") ? "?": "&") + "58ihm=" + f, d = d.match(/[\?&]58cid=[^&]*/) ? d.replace(/58cid=[^&]*/, "58cid=" + a.area1) : d.trim() + ( - 1 == d.indexOf("?") ? "?": "&") + "58cid=" + a.area1, $(g).attr("href", d))
                    }
                },
                bindAddGTIDtoURL: function() {
                    var a = this,
                        b = this.baseInfo;
                    e.$ && f.bindElem("a", "click",
                        function() {
                            if ("NO" == a.filterList(b.curURL) && -1 != b.curURL.indexOf(".58.com")) {
                                var c = $(this).attr("href") || "#"; - 1 != c.indexOf("javascript:") || "#" == c.substring(0, 1) || "NO" != a.filterList(c) || "/" != c.substring(0, 1) && -1 == c.indexOf(".58.com") || (c.match(/[\?&]ClickID=\d*/) ? $(this).attr("href", c.replace(/ClickID=\d*/, "ClickID=" + b.ClickID)) : $(this).attr("href", c.trim() + ( - 1 == c.indexOf("?") ? "?": "&") + "PGTID=" + b.GTID + "&ClickID=" + b.ClickID))
                            }
                        })
                },
                insertMiGuan: function() {
                    try {
                        var a = "default";
                        switch (this.baseInfo.cate1) {
                            case "9224":
                            case "9225":
                            case "13941":
                            case "13952":
                                a = "yewu";
                                break;
                            case "1":
                                a = "ershoufang";
                                break;
                            case "5":
                                a = "shouji";
                                break;
                            case "832":
                                a = "dog";
                                break;
                            case "4":
                                a = "ershouche";
                                break;
                            default:
                                a = "shenghuo"
                        }
                        var b = Math.ceil(1E14 * Math.random()),
                            c = document.getElementsByTagName("body")[0],
                            e = document.createElement("div");
                        e.id = "addInfo";
                        e.style.display = "none";
                        var d = document.createElement("a");
                        d.href = "http://tracklog.58.com/detail/m/" + a + "/" + b + "x.shtml";
                        d.text = "\u63a8\u8350\u4fe1\u606f";
                        e.appendChild(d);
                        c.appendChild(e)
                    } catch(f) {}
                },
                bindUndefinedClickLog: function() {
                    if (e.limited_show) {
                        var a = limited_show.replace(/\[/g, "").replace(/\]/g, "").trim().split(","),
                            b;
                        for (b in a) a[b].trim() && this.clickLog(a[b].trim())
                    }
                    if (e._statisArr) for (a = e._statisArr; a instanceof Array && 0 < a.length;) b = a.shift(),
                        b instanceof Array ? this.clickLog("from=" + b[0] + "&sumval=" + b[1]) : this.clickLog(b)
                }
            };
        k.baseInfo = k.getBaseInfo();
        k.trackLog();
        k.listShowLog();
        k.listClickLog();
        k.bindAlsTag();
        k.bindTongji_tag();
        k.bindTongji_id();
        k.bindHomeHeatMap();
        k.bindIndexHeatMap();
        k.bindAddGTIDtoURL();
        k.bindUndefinedClickLog();
        k.insertMiGuan();
        e.clickLog = function(a) {
            k.clickLog(a)
        };
        e.showLog = function(a) {};
        e.loadMorePage = function(a) {
            k.loadMorePageLog(a)
        };
        e.ajaxlog_mlistshow = function() {
            k.listShowLog()
        };
        e.GCIDPlus = function() {
            k.baseInfo.ClickIDPlus()
        };
        e.listClickLog = function(a) {};
        e.reTrackLog = function() {
            k.baseInfo = k.getBaseInfo();
            k.trackLog();
            k.listShowLog()
        };
        e.getGTID = function() {
            return k.baseInfo.GTID
        };
        e._gaq = e._gaq || []
    }
})(window);