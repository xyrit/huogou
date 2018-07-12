$(function () {
    var s = getUrlParam('s');
    if (s) {
        $(".btn-share").css('background','url(./images/btn-help.png) center center no-repeat');
        $(".btn-share").css('background-size','3rem .92rem');
    }

    function shareFun() {
        if (s) {
            $('.success-layer').show();
        } else {
            //分享
            location.href = 'fx.php';
            return;
        }
    }
    $('.btn-share').on('click',shareFun);
    $('.btn-findjob').click(function() {
        var t = getCookie('t');
        if (t) {
            var result = 0;
            $.getJSON(apiBaseUrl + "cart/add?productId=153&num=1&token="+t+"&callback=?",function(data){result+=1;});
            $.getJSON(apiBaseUrl + "cart/add?productId=148&num=5&token="+t+"&callback=?",function(data){result+=1;});
            $.getJSON(apiBaseUrl + "cart/add?productId=5&num=1&token="+t+"&callback=?",function(data){result+=1;});
            var si = setInterval(function() {
                if (result>=3) {
                    clearInterval(si);
                    var url = mobileBaseUrl+'cart.html';
                    location.href = mobileBaseUrl+'redirect.html?t='+t+'&target='+encodeURIComponent(url);
                    return;
                }
            },100);
            return;
        } else {
            location.href = 'regist.html?did='+getUrlParam('did');
            return;
        }
    });



    $('.layer-btn').click(function() {
        $_that = $(this);
        showWranMes("body", '领取积分成功', !0)
        $_that.addClass('received');
    });

    $('.layer-receive').click(function() {

        location.href = 'regist.html?did='+getUrlParam('did');
        return;
    });



    function bindEvent() {
        $('.gift-2').click(function() {
            $('#callFriendImg img').attr('src','images/card.png');
            $('#callFriendImg img').css('width','100%');
            $('.callfriend-ann').text('恭喜您获得充值卡');
        });
        $('.gift-3').click(function() {
            $('#callFriendImg img').attr('src','images/iphone.png');
            $('#callFriendImg img').css('width','50%');
            $('.callfriend-ann').text('恭喜您获得iPhone');
        });
        $('.layer-callfriend').click(function() {
            $('#callFriendImg').show();
            $('.callfriend-ann').show();
            $('.layer-subheader').hide();
            $(this).removeClass('layer-callfriend').addClass('btn-share').css({'margin':'auto','float':'none'});
            $('.btn-share').on('click',shareFun);
        });

        $(".btn-share, .layer-callfriend").on("click",
            function () {
                return clickLog("from=sqticket_share_my"),
                    spClickLog("source=$!{" + from + "}_sqticket_share_my"),
                    //shareLayerShow(),
                    !1
            }),
            $(".btn-findjob").on("click",
                function () {
                    clickLog("from=sqticket_2home"),
                        spClickLog("source=$!{" + from + "}_sqticket_2home")
                }),
            $(".share-layer").on("click",
                function () {
                    return shareLayerHide(),
                        !1
                }),
            $(".btn-get").on("click",
                function () {
                    return clickLog("from=sqticket_duihuan"),
                        spClickLog("source=$!{" + from + "}_sqticket_duihuan"),
                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: url + "/magictrip/ajax/popUp",
                            data: {
                                openid: openid,
                                from: from
                            },
                            error: function (res) {
                            },
                            success: function (res) {
                                res.success ? (res.data && ($(".success-layer .layer-receive").addClass("received"), $(".success-layer .layer-receive").off("click")), $(".success-layer").show()) : showWranMes("body", "用户信息错误", !0)
                            }
                        }),
                        !1
                }),
            $(".layer-close").on("click",
                function () {
                    return $(".layer").hide(),
                        !1
                }),
            $(".layer-btn").on("click",
                function () {
                    $_that = $(this);
                    var splitBag = $_that.attr("giftType");
                    "splitBag1" == splitBag ? (clickLog("from=sqticket_get1"), spClickLog("source=$!{" + from + "}_sqticket_get1")) : "splitBag2" == splitBag ? (clickLog("from=sqticket_get2"), spClickLog("source=$!{" + from + "}_sqticket_get2")) : "splitBag3" == splitBag && (clickLog("from=sqticket_get3"), spClickLog("source=$!{" + from + "}_sqticket_get3"))
                })
    }

    function initRender() {
        var html = '<div class="callfriend-layer layer" style="display: none;"><div class="layer-content"><sapn class="layer-close"></sapn><p id="callFriendImg" style="display: none;"><img style="width: 100%;" src="images/card.png"></p><p class="layer-subheader">亲~别心急~</p><p class="layer-subheader">马上就能回家咯~</p><p class="callfriend-ann" style="display: none;">恭喜您获得充值卡</p><a class="layer-callfriend" style="display: block;" href="javascript:void(0)"></a>';
        $(".success-layer").after(html)
    }

    function initial() {
        var percent = $("#splitPercent").val();
        percent > .3 && $(".main-info p:eq(0)").hide(),
            .5 > percent ? ($(".picture").addClass("picture-position1"), $(".gift-1").on("click",
                function () {
                    return clickLog("from=sqticket_invitation"),
                        spClickLog("source=$!{" + from + "}_sqticket_invitation"),
                        $(".gzyq-layer").show(),
                        !1
                }), $(".gift-2, .gift-3").on("click",
                function () {
                    return $(".callfriend-layer").show(),
                        !1
                })) : percent >= .5 && .7 > percent ? ($(".picture").addClass("picture-position2"), $(".gift-1").on("click",
                function () {
                    return clickLog("from=sqticket_invitation"),
                        spClickLog("source=$!{" + from + "}_sqticket_invitation"),
                        $(".gzyq-layer").show(),
                        !1
                }), $(".gift-2").on("click",
                function () {
                    return clickLog("from=sqticket_guanpei"),
                        spClickLog("source=$!{" + from + "}_sqticket_guanpei"),
                        $(".gpjj-layer").show(),
                        !1
                }), $(".gift-3").on("click",
                function () {
                    return $(".callfriend-layer").show(),
                        !1
                })) : percent >= .7 && 1 > percent ? ($(".picture").addClass("picture-position3"), $(".gift-1").on("click",
                function () {
                    return clickLog("from=sqticket_invitation"),
                        spClickLog("source=$!{" + from + "}_sqticket_invitation"),
                        $(".gzyq-layer").show(),
                        !1
                }), $(".gift-2").on("click",
                function () {
                    return clickLog("from=sqticket_guanpei"),
                        spClickLog("source=$!{" + from + "}_sqticket_guanpei"),
                        $(".gpjj-layer").show(),
                        !1
                }), $(".gift-3").on("click",
                function () {
                    return clickLog("from=sqticket_ruzhi"),
                        spClickLog("source=$!{" + from + "}_sqticket_ruzhi"),
                        $(".rzlb-layer").show(),
                        !1
                })) : ($(".picture").addClass("picture-position4"), $(".gift-1").on("click",
                function () {
                    return clickLog("from=sqticket_invitation"),
                        spClickLog("source=$!{" + from + "}_sqticket_invitation"),
                        $(".gzyq-layer").show(),
                        !1
                }), $(".gift-2").on("click",
                function () {
                    return clickLog("from=sqticket_guanpei"),
                        spClickLog("source=$!{" + from + "}_sqticket_guanpei"),
                        $(".gpjj-layer").show(),
                        !1
                }), $(".gift-3").on("click",
                function () {
                    return clickLog("from=sqticket_ruzhi"),
                        spClickLog("source=$!{" + from + "}_sqticket_ruzhi"),
                        $(".rzlb-layer").show(),
                        !1
                }), $(".btn-share").hide(), $(".btn-get").show()),
        1 == splitBag1 && ($(".gzyq-layer .layer-btn").addClass("received"), $(".gzyq-layer .layer-btn").off("click")),
        1 == splitBag2 && ($(".gpjj-layer .layer-btn").addClass("received"), $(".gpjj-layer .layer-btn").off("click")),
        1 == splitBag3 && ($(".rzlb-layer .layer-btn").addClass("received"), $(".rzlb-layer .layer-btn").off("click")),
            $(".route-info .progress-bar").css("background-size", 100 * percent + "% 0.23rem")
    }

    function GetRequest() {
        var url = location.search,
            theRequest = {};
        if (-1 != url.indexOf("?")) {
            var str = url.substr(1);
            strs = str.split("&");
            for (var i = 0; i < strs.length; i++) theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1])
        }
        return theRequest
    }

    function isWeiXin() {
        var ua = window.navigator.userAgent.toLowerCase();
        return "micromessenger" == ua.match(/MicroMessenger/i) ? !0 : !1
    }

    function shareLayerShow() {
        isWeiXin() ? $(".share-wx").show() : $(".share-m").show(),
            $(".share-layer").show()
    }

    function shareLayerHide() {
        $(".share-wx").hide(),
            $(".share-m").hide(),
            $(".share-layer").hide()
    }

    function showWranMes(obj, mes, isClear) {
        var $this = $(obj);
        $this.find(".wran_mes").remove();
        var htmlStr = "";
        if (htmlStr = "<div class='wran_mes'>" + mes + "</div>", $this.append(htmlStr), $(".wran_mes").css({
                "font-size": "15px",
                "line-height": "34px",
                position: isClear ? "absolute" : "fixed",
                top: "35%",
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
            setTimeout(function () {
                    node.remove()
                },
                2e3)
        }
    }

    var url = "http://operation.58supin.com",
        Request = GetRequest(),
        openid = Request.openid;
    initRender(),
    bindEvent(),
    initial(),
    function () {
        function setRem() {
            var clientWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
                nowRem = clientWidth / 640 * 100;
            $("html").css("font-size", nowRem + "px")
        }

        onresize = setRem,
            setRem()
    }(),
    function () {
        wx.config({
            debug: !1,
            appId: wxappid,
            timestamp: wxtimestamp,
            nonceStr: wxnonceStr,
            signature: wxsignature,
            jsApiList: ["onMenuShareTimeline", "onMenuShareAppMessage", "onMenuShareQQ", "onMenuShareWeibo", "onMenuShareQZone"]
        }),
            wx.ready(function () {
                var wxconfig = {
                    title: "回家不再寂寞！美女给我了一个神奇的礼物••••••",
                    desc: "回家不再寂寞，帅哥美女一起玩，领神奇礼包，抢神奇路费，手快有，手慢无！",
                    link: "http://api.wx.58supin.com/wx/info?redirecturl=http%3a%2f%2foperation.58supin.com%2fmagictrip%2ffriend%3fshareid%3d" + openid,
                    imgUrl: "http://c.58cdn.com.cn/crop/zt/supin/sqhj-m/static/images/share-icon.png",
                    success: function () {
                    },
                    cancel: function () {
                    }
                };
                wx.onMenuShareTimeline(wxconfig),
                    wx.onMenuShareAppMessage(wxconfig),
                    wx.onMenuShareQQ(wxconfig),
                    wx.onMenuShareWeibo(wxconfig),
                    wx.onMenuShareQZone(wxconfig)
            })
    }()
});