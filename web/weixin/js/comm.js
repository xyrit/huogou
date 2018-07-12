/**
 * Created by jun on 15/11/18.
 */
var baseHost = getHost();
var apiBaseUrl = 'http://api.' + baseHost;
var weixinBaseUrl = 'http://weixin.' + baseHost;
var wwwBaseUrl = 'http://www.' + baseHost;
var skinBaseUrl = 'http://skin.' + baseHost;

var photoSize = ['58', '200', '400', 'org'];
var avatarSize = ['30', '80', '160'];
jQuery.extend({
    getJsonp: function(url, data, succfunc) {
        $.jsonp({
            url: url,
            data: data,
            callbackParameter: "callback",
            success: succfunc,
            error: function (xOptions, textStatus) {
                console.log(xOptions)
            }
        });
    },
    onSrollBottom: function(bottomFuc, notBottomFuc) {
        $(window).on('scroll', function (e) {

            var scrollTop = $(this).scrollTop();
            var scrollHeight = $(document).height();
            var windowHeight = $(this).height();
            if (scrollTop + windowHeight == scrollHeight){
                if (typeof bottomFuc =='function') {
                    bottomFuc();
                }
            } else {
                if (typeof notBottomFuc =='function') {
                    notBottomFuc();
                }
            }

        });
    }
});

function createGoodsImgUrl(name, width, height) {
    return 'http://img.' + baseHost + '/pic-' + width + '-' + height + '/' + name;
}

function createUserFaceImgUrl(name, width) {
    if (!name) {
        name = '000000000000.jpg';
        return 'http://img.' + baseHost + '/userface/' + width + '/' + name;
    }
    return 'http://img.' + baseHost + '/userface/' + width + '/' + name;
}

function createShareImgUrl(name, size) {
    return 'http://img.' + baseHost + '/userpost/' + size + '/' + name;
}

function createGoodsUrl(productId) {
    return weixinBaseUrl+'/product/'+productId+'.html';
}

function createPeriodUrl(periodId) {
    return weixinBaseUrl+'/lottery/'+periodId+'.html';
}
function createUserCenterUrl(homeId) {
    return weixinBaseUrl+'/userpage/'+homeId;
}

function createPostDetailUrl(postId) {
    return weixinBaseUrl+'/post/detail-'+postId+'.html';
}

function checkPhone(str) {
    var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;
    if (reg.test(str)) {
        return true;
    } else {
        return false;
    }
}

function checkEmail(str) {
    var reg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    if (reg.test(str)) {
        return true;
    } else {
        return false;
    }
}

function addCart(periodId, num, productId) {
    var url = apiBaseUrl+'/cart/add';
    var data = {'periodid':periodId,'num':num};
    if (typeof productId!='undefined') {
        data = {'productId':productId,'num':num};
    }
    var msgs = {
        "100":"添加购物车成功",
        "101":"此期已经满员",
        "102":"获取商品信息失败",
        "103":"添加购物车失败",
        "104":"不能再购买限购数量的商品",
        "105":"购买数量超过限购数量",
        "10099": "账户已冻结，如有疑问请联系伙购网客服"
    };
    $.getJsonp(url, data, function(json) {
        var code = json.code;
        if (code == 100) {
            $.PageDialog.ok('添加购物车成功');
            cartListInfo();
        } else if (code == 101) {//已满员被抢光
            $.PageDialog.confirm('本期已满员，是否伙购下一期？',function() {
                addCart('',num,json.productId);
            });
        } else {
            var msg = msgs[json.code] ? msgs[json.code] : "添加购物车失败";
            $.PageDialog.fail(msg);
        }
    });
}

function goCart(periodId, num, productId) {
    var url = apiBaseUrl+'/cart/add';
    var data = {'periodid':periodId,'num':num};
    if (typeof productId!='undefined') {
        data = {'productId':productId,'num':num};
    }
    var msgs = {
        "100":"添加购物车成功",
        "101":"此期已经满员",
        "102":"获取商品信息失败",
        "103":"添加购物车失败",
        "104":"不能再购买限购数量的商品",
        "105":"购买数量超过限购数量",
        "10099": "账户已冻结，如有疑问请联系伙购网客服"
    };
    $.getJsonp(url, data, function(json) {
        var code = json.code;
        if (code == 100) {
            location.href = weixinBaseUrl+'/cart.html';
        } else if (code == 101) {//已满员被抢光
            $.PageDialog.confirm('本期已满员，是否伙购下一期？',function() {
                goCart('',num,json.productId);
            });
        } else {
            var msg = msgs[json.code] ? msgs[json.code] : "添加购物车失败";
            $.PageDialog.fail(msg);
        }
    });
}

function countDown(obj,grayCls) {
    var timeLeft = 120;
    obj.attr("t", timeLeft).text('重新发送('+timeLeft+')');
    obj.addClass(grayCls);
    var j = setInterval(function () {
        var t = parseInt(obj.attr("t") - 1);
        if (t <= 0) {
            clearInterval(j);
            obj.attr("t", t).text('重新发送');
            obj.removeClass(grayCls);
        } else {
            obj.attr("t", t).text('重新发送(' + t + ')');
        }
    }, 1000);
}


function getHost(url) {
    var host = "null";
    if (typeof url == "undefined"
        || null == url)
        url = window.location.href;
    var regex = /.*\:\/\/([^\/|:]*).*/;
    var match = url.match(regex);
    if (typeof match != "undefined"
        && null != match) {
        host = match[1];
    }
    if (typeof host != "undefined"
        && null != host) {
        var strAry = host.split(".");
        if (strAry.length > 1) {
            host = strAry[strAry.length - 2] + "." + strAry[strAry.length - 1];
        }
    }
    return host;
}

function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)","i");
    var r = window.location.search.substr(1).match(reg);
    if (r!=null) return (r[2]); return null;
}

var stopBubble = function (a) {
    if (a && a.stopPropagation) {
        a.stopPropagation()
    } else {
        window.event.cancelBubble = true
    }
}


function fastNav(e) {
    if (e == "1" || e == "2" || e == "3") {
        var k = true;
        var g = '<div id="div_fastnav"  class="fast-nav-wrapper">';
        g += '<ul class="fast-nav">';
        if (e != "3") {
            g += '<li id="li_menu"><a href="javascript:;"><i class="nav-menu"></i></a></li>'
        }
        if (e != "2") {
            g += '<li id="li_top" style="display:none;"><a href="javascript:;"><i class="nav-top"></i></a></li>'
        }
        g += "</ul>";
        if (e != "3") {
            g += '<div class="sub-nav" style="display:none;">';
            g += '<a href="/"><i class="home"></i>伙购</a>';
            g += '<a href="/lottery/m1.html"><i class="announced"></i>最新揭晓</a>';
            g += '<a href="/post/index.html"><i class="single"></i>晒单</a>';
            g += '<a href="/member/index.html"><i class="personal"></i>我</a>';
            g += "</div>"
        }
        g += "</div>";
        var l = $("#div_fastnav");
        if (l.length == 0) {
            l = $(g)
        }
        if (e != "3") {
            var c = $(".sub-nav", l);
            var b = $("#li_menu", l);
            var d = null;
            b.bind("click", function () {
                if (k == false) {
                    return
                }
                if (d != null) {
                    clearTimeout(d)
                }
                if ($(this).attr("isshow") == "1") {
                    c.fadeOut("fast");
                    $(this).attr("isshow", "0")
                } else {
                    c.fadeIn("fast", function () {
                        d = setTimeout(function () {
                            c.fadeOut("fast");
                            b.attr("isshow", "0")
                        }, 5000)
                    });
                    $(this).attr("isshow", "1")
                }
            });
            l.bind("click", function (m) {
                stopBubble(m)
            });
            $("html").bind("click", function () {
                c.fadeOut("fast");
                b.attr("isshow", "0")
            })
        }
        if (e != "2") {
            var h = $("#li_top", l);
            h.bind("click", function () {
                $(this).hide();
                $("body,html").animate({scrollTop: 0}, 500)
            });
            $(window).scroll(function () {
                if ($(window).scrollTop() > 100) {
                    h.show()
                } else {
                    h.hide()
                }
            })
        }
        l.appendTo("body")
    }
}

$(function(){
    //阻止密码输入复制粘贴
   $("input:password").bind("copy cut paste",function(e){
        return false;
    });

    var hash = location.hash;
    if (hash!='#nofastnav') {
        if ($('.footer').length==0) {
            fastNav(1);
        } else {
            fastNav(3);
        }
    }

});

var Base = {
    head: document.getElementsByTagName("head")[0] || document.documentElement,
    Myload: function (B, A) {
        this.done = false;
        B.onload = B.onreadystatechange = function () {
            if (!this.done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                this.done = true;
                A();
                B.onload = B.onreadystatechange = null;
                if (this.head && B.parentNode) {
                    this.head.removeChild(B)
                }
            }
        }
    },
    getScript: function (A, C) {
        var B = function () { };
        if (C != undefined) {
            B = C;
        }
        var D = document.createElement("script");
        D.setAttribute("language", "javascript");
        D.setAttribute("type", "text/javascript");
        D.setAttribute("src", A);
        this.head.appendChild(D);
        this.Myload(D, B);
    },
    getStyle: function (A, B) {
        var B = function () { };
        if (callBack != undefined) {
            B = callBack;
        }
        var C = document.createElement("link");
        C.setAttribute("type", "text/css");
        C.setAttribute("rel", "stylesheet");
        C.setAttribute("href", A);
        this.head.appendChild(C);
        this.Myload(C, B);
    }
}


function GetVerNum() {
    var D = new Date();
    return D.getFullYear().toString().substring(2, 4) + '.' + (D.getMonth() + 1) + '.' + D.getDate() + '.' + D.getHours() + '.' + (D.getMinutes() < 10 ? '0' : D.getMinutes().toString().substring(0, 1));
}

$.cookie = function (b, j, m) {
    if (typeof j != "undefined") {
        m = m || {};
        if (j === null) {
            j = "";
            m.expires = -1
        }
        var e = "";
        if (m.expires && (typeof m.expires == "number" || m.expires.toUTCString)) {
            var f;
            if (typeof m.expires == "number") {
                f = new Date();
                f.setTime(f.getTime() + (m.expires * 24 * 60 * 60 * 1000))
            } else {
                f = m.expires
            }
            e = "; expires=" + f.toUTCString()
        }
        var l = m.path ? "; path=" + (m.path) : "";
        var g = m.domain ? "; domain=" + (m.domain) : "";
        var a = m.secure ? "; secure" : "";
        document.cookie = [b, "=", encodeURIComponent(j), e, l, g, a].join("")
    } else {
        var d = null;
        if (document.cookie && document.cookie != "") {
            var k = document.cookie.split(";");
            for (var h = 0; h < k.length; h++) {
                var c = jQuery.trim(k[h]);
                if (c.substring(0, b.length + 1) == (b + "=")) {
                    d = decodeURIComponent(c.substring(b.length + 1));
                    break
                }
            }
        }
        return d
    }
};

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

