$(function() {
    function bindEvent() {
        $(".btn-sqlf").on("click",
            function() {
                return location.href = 'regist.html?did='+getUrlParam('did');
            }),
            $(".tips-layer").on("click",
                function() {
                    return $(".tips-layer").hide(),
                        !1
                })
    }
    function isWeiXin() {
        var ua = window.navigator.userAgent.toLowerCase();
        return "micromessenger" == ua.match(/MicroMessenger/i) ? !0 : !1
    }
    function showWranMes(obj, mes, isClear) {
        var $this = $(obj);
        $this.find(".wran_mes").remove();
        var htmlStr = "";
        if (htmlStr = "<div class='wran_mes'>" + mes + "</div>", $this.append(htmlStr), $(".wran_mes").css({
                "font-size": "15px",
                "line-height": "34px",
                position: isClear ? "absolute": "fixed",
                top: "50%",
                left: "50%",
                width: "200px",
                height: "34px",
                margin: "-15px 0 0 -100px",
                "text-align": "center",
                color: "#fff",
                "font-family": "黑体",
                "border-radius": "3px",
                background: "rgba(0,0,0,.6)",
                "z-index": "9999"
            }), isClear) {
            var node = $this.find(".wran_mes");
            setTimeout(function() {
                    node.remove()
                },
                2e3)
        }
    }
    var WBAPP = new app58;
    bindEvent(),
        function() {
            function setRem() {
                var clientWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
                    nowRem = clientWidth / 640 * 100;
                $("html").css("font-size", nowRem + "px")
            }
            onresize = setRem,
                setRem()
        } (),
        function() {
            var prefix = "http://c.58cdn.com.cn/crop/zt/supin/sqhj-m/static/images/",
                imageNames = ["close.png", "game/arrow.png", "game/btn-findjob.png", "game/btn-get.png", "game/btn-help.png", "game/btn-known.png", "game/btn-receive.png", "game/btn-received.png", "game/btn-share.png", "game/game-bg.png", "game/gift-bg.png", "game/gift-big.png", "game/gift-sml.png", "game/gift.png", "game/layer-code.png", "game/line1.png", "game/line2.png", "game/picture.png", "game/progress-bar.png", "game/progress-bg.png", "game/sqLb-layer.png", "game/success-layer.png", "game/user-header.png", "index/app2wx.png", "index/btn-sqlf-active.png", "index/btn-sqlf.png", "index/m2wx.png", "index/sqlf-bg.png", "regist/btn-code-active.png", "regist/btn-code-disable.png", "regist/btn-code.png", "regist/btn-submit-active.png", "regist/btn-submit.png", "regist/regist-bg.png", "regist/route-icon.png", "regist/rules-layer.png", "regist/warning-icon.png", "share-icon.png", "share-m.png", "share-wx.png"],
                loadCount = 0,
                scale = 0,
                $loadText = $("#loadText"),
                $daQuanChildren = $("#daQuan").children(),
                daQuanIndex = 0,
                lastDaQuanIndex = 0;
            $loadText.text("0%"),
                imageNames.forEach(function(img) {
                    var image = new Image;
                    image.onload = function() {
                        loadCount++,
                            scale = 100 * loadCount / imageNames.length,
                            daQuanIndex = Math.floor(scale / 12),
                        daQuanIndex !== lastDaQuanIndex && $daQuanChildren.eq(lastDaQuanIndex).attr("stroke", "#cf4d15"),
                            $loadText.text(parseInt(scale, 10) + "%"),
                            lastDaQuanIndex = daQuanIndex,
                        100 === scale && setTimeout(function() {
                                $(".loading").hide(),
                                    $(".sqlf").show()
                            },
                            500)
                    },
                        image.src = prefix + img
                })
        } (),
        function() {
            wx.config({
                debug: !1,
                appId: wxappid,
                timestamp: wxtimestamp,
                nonceStr: wxnonceStr,
                signature: wxsignature,
                jsApiList: ["onMenuShareTimeline", "onMenuShareAppMessage", "onMenuShareQQ", "onMenuShareWeibo", "onMenuShareQZone"]
            }),
                wx.ready(function() {
                    var wxconfig = {
                        title: "回家不再寂寞！美女给我了一个神奇的礼物••••••",
                        desc: "回家不再寂寞，帅哥美女一起玩，领神奇礼包，抢神奇路费，手快有，手慢无！",
                        link: "http://api.wx.58supin.com/wx/info?redirecturl=http%3a%2f%2foperation.58supin.com%2fmagictrip%2findex%3ffrom%3d" + from,
                        imgUrl: "http://c.58cdn.com.cn/crop/zt/supin/sqhj-m/static/images/share-icon.png",
                        success: function() {},
                        cancel: function() {}
                    };
                    wx.onMenuShareTimeline(wxconfig),
                        wx.onMenuShareAppMessage(wxconfig),
                        wx.onMenuShareQQ(wxconfig),
                        wx.onMenuShareWeibo(wxconfig),
                        wx.onMenuShareQZone(wxconfig)
                })
        } ()
});