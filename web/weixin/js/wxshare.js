var _IsLoadWxShareJs = false;
var _wxInitConfig = false;
var _wx_m_popUp = $("div.m_popUp");
var wxRemoveMask = function () {
    _wx_m_popUp.hide();
    $("body").attr("style", "");
    IsMasked = false
};
var wxShowMaskFun = function (b) {
    if (IsMasked) {
        return
    }
    IsMasked = true;
    var a = "";
    if (b) {
        a += '<div class="m_popUp" style="display:none;">';
        a += "<span></span>";
        a += '<div class="m_how">';
        a += "<h4>怎么赚钱？</h4>";
        a += "<p>1: 点击本页面右上角的三个点的图标</p>";
        a += "<p>2: 选择[发送给朋友]或[分享到朋友圈]</p>";
        a += "<p>3: 经您邀请的好友，成功参与伙购后，您可获得好友消费额6%的佣金奖励</p>";
        a += "</div>";
        a += '<div class="m_guide">';
        a += "</div>";
        a += "</div>"
    } else {
        a += '<div class="m_popUp" style="display:none;">';
        a += '<div class="m_guide">';
        a += "</div>";
        a += "<cite></cite>";
        a += "</div>"
    }
    if (_wx_m_popUp.length == 0) {
        _wx_m_popUp = $(a);
        _wx_m_popUp.appendTo("body")
    }
    _wx_m_popUp.show(0, function () {
        $("body").attr("style", "overflow:hidden;")
    });
    _wx_m_popUp.bind("click", wxRemoveMask)
};
var wxInitShareFun = function (b) {
    var a = function () {
        var e = $("#hidAppID").val();
        var d = $("#hidTimeSpan").val();
        var f = $("#hidNonceStr").val();
        var c = $("#hidSignature").val();
        if (e == "" || typeof(e) == "undefined" || d == "" || typeof(d) == "undefined" || f == "" || typeof(f) == "undefined" || c == "" || typeof(c) == "undefined") {
            console.log("初始参数错误！");
            return
        }
        wx.config({
            debug: false,
            appId: e,
            timestamp: d,
            nonceStr: f,
            signature: c,
            jsApiList: ["checkJsApi", "onMenuShareAppMessage", "onMenuShareTimeline", "onMenuShareWeibo", "onMenuShareQQ", "onMenuShareQZone"]
        });
        wx.error(function (g) {
            console.log(JSON.stringify(g));
            _wxInitConfig = false
        });
        wx.ready(function () {
            _wxInitConfig = true;
            b()
        })
    };
    if (_IsLoadWxShareJs) {
        a()
    } else {
        Base.getScript("http://res.wx.qq.com/open/js/jweixin-1.0.0.js", function () {
            a();
            _IsLoadWxShareJs = true
        })
    }
};
var wxShareFun = function (b) {
    var e = {
        shareTitle: "欢乐伙购 - 惊喜无限",
        shareImg: "http://img.huogou.com/pic-58-58/20151119425868128.jpg",
        shareLink: "",
        shareDesc: "1元就能购买iphone6S哦，快去看看吧！",
        shareMoney: false,
        showMask: true
    };
    b = b || {};
    $.extend(e, b);
    var d = e.shareTitle;
    var f = e.shareImg;
    var c = e.shareLink;
    var g = e.shareDesc;
    if (e.showMask) {
        wxShowMaskFun(e.shareMoney)
    }
    var a = function () {
        wx.ready(function () {
            wx.onMenuShareAppMessage({
                title: d, desc: g, link: c, imgUrl: f, success: function () {
                    wxRemoveMask()
                }, cancel: function () {
                    wxRemoveMask()
                }
            });
            wx.onMenuShareTimeline({
                title: d + g, link: c, imgUrl: f, success: function () {
                    wxRemoveMask()
                }, cancel: function () {
                    wxRemoveMask()
                }
            });
            wx.onMenuShareWeibo({
                title: d, desc: g, link: c, imgUrl: f, success: function () {
                    wxRemoveMask()
                }, cancel: function () {
                    wxRemoveMask()
                }
            });
            wx.onMenuShareQQ({
                title: d, desc: g, link: c, imgUrl: f, success: function () {
                    wxRemoveMask()
                }, cancel: function () {
                    wxRemoveMask()
                }
            });
            wx.onMenuShareQZone({
                title: d, desc: g, link: c, imgUrl: f, success: function () {
                    wxRemoveMask()
                }, cancel: function () {
                    wxRemoveMask()
                }
            })
        })
    };
    if (!_wxInitConfig) {
        wxInitShareFun(a)
    } else {
        a()
    }
};