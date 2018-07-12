$(function () {
    if (getCookie('t')) {
        location.href = 'result.html';
        return;
    }
    function bindEvent() {
        $(".info input").on("blur",
            function () {
                checkInfo($(this))
            }),
            $('.info select[infotype="routeEnd"]').on("change",
                function () {
                    checkRoute()
                }),
            $(".rules").on("click",
                function () {
                    return clickLog("from=sqticket_rule"),
                        spClickLog("source=$!{" + from + "}_sqticket_rule"),
                        $(".rules-layer").show(),
                        !1
                }),
            $(".layer-close").on("click",
                function () {
                    return $(".layer").hide(),
                        !1
                }),
            $(".btn-code").on("click",
                function () {
                    return clickLog("from=sqticket_certify"),
                        spClickLog("source=$!{" + from + "}_sqticket_certify"),
                        getCode(),
                        !1
                }),
            $(".btn-submit").on("click",
                function () {
                    return checkForm() && (clickLog("from=sqticket_submit"), spClickLog("source=$!{" + from + "}_sqticket_submit"), submitForm()),
                        !1
                })
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

    function submitForm() {
        var password = $('.info input[infotype="password"]').val(),
            mobile = $('.info input[infotype="mobile"]').val(),
            authCode = $('.info input[infotype="authCode"]').val();
        $.getJSON(apiBaseUrl+"user/register?account="+mobile+"&password="+password+"&smscode="+authCode+"&source=99&spreadSource=wy_"+getUrlParam('did')+"&callback=?",function(data){
            if (data.code == 100) {
                setCookie('t',encodeURIComponent(data.token));
                location.href = 'result.html';
            }else{
                var errMsg = '注册失败';
                if (typeof data.errorMsg!='undefined') {
                    for(var p in data.errorMsg) {
                        errMsg = data.errorMsg[p];
                        break;
                    }
                }
                showWranMes("body", errMsg, !0)
            }

        })
    }

    function getCode() {
        var $ele = $('.info input[infotype="mobile"]');
        var phone = $ele.val();
        checkInfo($ele) && ($(".btn-code").off("click"), myTimer($(".btn-code")), $.getJSON(apiBaseUrl+"user/check-phone?phone="+phone+'&callback=?',function(data){
            if (data.state == 1) {
                showWranMes("body", '手机号码已存在', !0)
            }else if(data.state == 0){
                $.getJSON(apiBaseUrl+"user/send-code?account="+phone+'&type=1&callback=?',function(json){
                    if (json.errcode==100) {

                    } else {
                        showWranMes("body", '验证码发送频率过高', !0)
                    }
                })
            }
        }))
    }

    function timer() {
        var wait = 60,
            countDown = function ($ele) {
                0 === wait ? ($ele.on("click", getCode), $ele.html(""), $ele.removeClass("disable"), wait = 60) : ($ele.addClass("disable"), $ele.html(wait + "s"), wait--, setTimeout(function () {
                        countDown($ele)
                    },
                    1e3))
            };
        return countDown
    }

    function checkForm() {
        for (var $forms = $(".form .info"), result = 0, i = 0; i < $forms.length - 1; i++) checkInfo($($forms[i]).find("input")) && (result += 1);
        return  3 == result ? !0 : !1
    }

    function checkInfo($ele) {
        var result, value = $ele.val(),
            type = $ele.attr("infotype"),
            reg = "";
        switch (type) {
            case "name":
                reg = /^[\u4E00-\u9fa5]{2,4}$/,
                    result = reg.test(value);
                break;
            case "mobile":
                reg = /^(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/,
                    result = reg.test(value);
                break;
            case "authCode":
                reg = /^([0-9]){6}$/,
                    result = reg.test(value);
                break;
            case "password":
                reg = /^([0-9a-zA-Z]){8,20}$/,
                    result = reg.test(value);
                break;
        }
        return result ? $ele.parents(".info").find("p").css("visibility", "hidden") : $ele.parents(".info").find("p").css("visibility", "visible"),
            result
    }

    function showWranMes(obj, mes, isClear) {
        var $this = $(obj);
        $this.find(".wran_mes").remove();
        var htmlStr = "";
        if (htmlStr = "<div class='wran_mes'>" + mes + "</div>", $this.append(htmlStr), $(".wran_mes").css({
                "font-size": "15px",
                "line-height": "34px",
                position: isClear ? "absolute" : "fixed",
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
            setTimeout(function () {
                    node.remove()
                },
                2e3)
        }
    }

    var url = "http://operation.58supin.com",
        myTimer = timer(),
        Request = GetRequest(),
        openid = Request.openid;
    bindEvent(),
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
                        link: "http://api.wx.58supin.com/wx/info?redirecturl=http%3a%2f%2foperation.58supin.com%2fmagictrip%2findex%3ffrom%3d" + from,
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
