(function (d) {
    d.PageDialog = function (h, t) {
        var m = {
            W: 255, H: 45, obj: null, oL: 0, oT: 0, autoClose: true, autoTime: 1000, ready: function () {
            }, submit: function () {
            }
        };
        var j = {
            obj: null, oL: 0, oT: 0, autoClose: true, autoTime: 2000, ready: function () {
            }, submit: function () {
            }
        };
        t = t || j;
        d.extend(m, t);
        var g = m.autoClose;
        var p = function (x) {
            var u = x.get(0);
            u.addEventListener("touchstart", w, false);
            function w(z) {
                if (z.touches.length === 1) {
                    u.addEventListener("touchmove", v, false);
                    u.addEventListener("touchend", y, false)
                }
            }

            function v(z) {
                z.preventDefault()
            }

            function y(z) {
                u.removeEventListener("touchmove", v, false);
                u.removeEventListener("touchend", y, false)
            }
        };
        var r = d("#pageDialogBG");
        if (!g) {
            if (r.length == 0) {
                r = d('<div id="pageDialogBG" class="pageDialogBG"></div>');
                r.appendTo("body")
            } else {
                r = d("#pageDialogBG")
            }
            r.css("height", d(document).height() > d(window).height() ? d(document).height() : d(window).height());
            p(r)
        }
        var o = d("#pageDialog");
        if (o.length == 0) {
            o = d('<div id="pageDialog" class="pageDialog" />');
            o.appendTo("body");
            if (!g) {
                p(o)
            }
        }
        var f = d(window);
        if (m.obj != null) {
            if (m.obj.length < 1) {
                m.obj = null
            }
        }
        var e = function (u) {
            u = u.replace(/<\/?[^>]*>/g, "");
            u = u.replace(/[ | ]*\n/g, "\n");
            u = u.replace(/ /ig, "");
            return u
        };
        var l = m.W;
        if (l == 255 && m.H == 45) {
            h = h.replace(/！/ig, "");
            h = h.replace(/!/ig, "");
            var s = 10;
            if (h.indexOf("<s></s>") > -1) {
                s = 50
            }
            var i = e(h);
            if (i.length > 0) {
                l = 20 * i.length + s;
                if (l >= (d(window).width() - 20)) {
                    l = d(window).width() - 20
                }
            }
            m.W = l
        }
        o.css({width: m.W + "px", height: m.H + "px"});
        o.html(h);
        var q = function () {
            var u, w, x;
            if (m.obj != null) {
                var v = m.obj.offset();
                u = v.left + m.oL;
                w = v.top + m.obj.height() + m.oT;
                x = "absolute"
            } else {
                u = (f.width() - m.W) / 2;
                w = (f.height() - m.H) / 2;
                w = w - w / 2;
                x = "fixed"
            }
            o.css({position: x, left: u, top: w})
        };
        q();
        f.resize(q);
        var n = function () {
            if (g) {
                o.fadeOut('fast');
            } else {
                o.hide();
                r.hide()
            }
        };
        var k = function () {
            m.submit();
            n()
        };
        if (g) {
            o.fadeIn('fast')
        } else {
            o.show();
            r.show()
        }
        o.ready = m.ready();
        if (g) {
            window.setTimeout(k, m.autoTime)
        }
        this.close = function () {
            k()
        };
        this.cancel = function () {
            n()
        }
    };
    d.PageDialog.ok = function (f, e,t) {
        d.PageDialog('<div class="Prompt"><s></s>' + f + "</div>", {
            autoTime: t ? t : 500,
            submit: (e === undefined ? function () {
            } : e)
        })
    };
    d.PageDialog.fail = function (h, g, i, e, f) {
        d.PageDialog('<div class="Prompt">' + h + "</div>", {
            obj: g,
            oT: i,
            oL: e,
            autoTime: 1000,
            submit: (f === undefined ? function () {
            } : f)
        })
    };
    var b = 0;
    d.PageDialog.confirm = function (j, f, e) {
        var h = null;
        var g = '<div class="clearfix m-round u-tipsEject"><div class="u-tips-txt">' + j + '</div><div class="u-Btn"><div class="u-Btn-li"><a href="javascript:;" id="btnMsgCancel" class="z-CloseBtn">取消</a></div><div class="u-Btn-li"><a id="btnMsgOK" href="javascript:;" class="z-DefineBtn">确定</a></div></div></div>';
        var i = function () {
            d("#btnMsgCancel").click(function () {
                h.cancel()
            });
            d("#btnMsgOK").click(function () {
                h.close()
            })
        };
        b++;
        h = new d.PageDialog(g, {H: (e === undefined ? 126 : e), autoClose: false, ready: i, submit: f})
    };
    d.PageDialog.custom = function (j, k, l, f, e) {
        var h = null;
        var g = '<div class="clearfix m-round u-tipsEject"><div class="u-tips-txt">' + j + '</div><div class="u-Btn"><div class="u-Btn-li"><a href="javascript:;" id="btnMsgCancel" class="z-CloseBtn">取消</a></div><div class="u-Btn-li"><a id="btnMsgOK" href="javascript:;" class="z-DefineBtn">确定</a></div></div></div>';
        var i = function () {
            d("#btnMsgCancel").click(function () {
                h.cancel()
            });
            d("#btnMsgOK").click(function () {
                k()
            })
            l();
        };
        b++;
        h = new d.PageDialog(g, {H: (e === undefined ? 126 : e), autoClose: false, ready: i, submit: f})
    };
    d.PageDialog.fail1 = function (h, g, f) {
        var i = c(g);
        var e = a(g);
        d.PageDialog('<div class="Prompt">' + h + "</div>", {
            obj: g,
            oT: i,
            oL: e,
            autoTime: 1000,
            submit: (f === undefined ? function () {
            } : f)
        })
    };
    d.PageDialog.hidden = function() {
        d("#pageDialog").hide();
        d("#pageDialogBG").hide();
    }
    var a = function (h) {
        var e = d(h).width() - 255;
        var g = e > 0 ? e : e * -1;
        var f = g / 2;
        return f
    };
    var c = function (e) {
        return (d(e).height() * 2 + 20) * -1
    }
})(jQuery);