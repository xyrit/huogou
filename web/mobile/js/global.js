/**
 * Created by jun on 15/11/19.
 */

$(function() {
    var s = '/cart\.html';
    var reg = new RegExp(s);
    var r = location.href.match(reg);
    if (!r) {
        cartListInfo();
    }
    var d = $(".footer").find("a");
    d.on("touchstart", function () {
        if (!$(this).hasClass("hover")) {
            d.removeClass("active").eq(d.index(this)).addClass("active");
            setTimeout(function () {
                d.removeClass("active")
            }, 1000)
        }
    })

    var c = "_downApp";
    var p = $.cookie(c);
    if (p == null || p == "") {
        $("#divDownApp").show();
        $("#divDownApp .close-icon").click(function(e) {
            stopBubble(e);
            $("#divDownApp").hide();
            $.cookie(c, "1", {expires: 1, path: "/"})
        })
    }

});

function cartListInfo() {
    var url = apiBaseUrl+'/cart/list';
    var data = {};
    $.getJsonp(url, data, function(json) {
        renderCartBtnInfo(json);
    });
}

function renderCartBtnInfo(json) {
    var len = json.list.length;
    if (len>0) {
        $('#btnCart').html('<em>'+len+'</em>');
        $('#btnCartFooter i').html('<b>'+len+'</b>');
    } else {
        $('#btnCart').html('');
        $('#btnCartFooter i').html('');
    }
}

function getCartListInfo(json) {
    var len = json.list.length;
    if (len>0) {
        $('#btnCart').html('<em>'+len+'</em>');
        $('#btnCartFooter i').html('<b>'+len+'</b>');
    } else {
        $('#btnCart').html('');
        $('#btnCartFooter i').html('');
    }
}

function changeCartNum(pid,num,func) {
    var url = apiBaseUrl+'/cart/changenum';
    var data = {pid:pid,num:num};
    $.getJsonp(url, data, function(json) {
        func(json)
    });
}

function leftTime(time, obj, func) {
    var curTime = new Date().getTime();
    var curTimeLeft = parseFloat((curTime - time) / 1000 - parseFloat(1 / 100));
    var seconds = obj.attr("left-time");
    seconds -= curTimeLeft;
    var minutes = seconds / 60;
    var CMinute = Math.floor(minutes % 60);
    var CSecond = Math.floor(seconds % 60);
    var CMSecond = Math.floor(seconds * 100 % 100);

    CMinute = CMinute < 10 ? "0" + CMinute : CMinute;
    CSecond = CSecond < 10 ? "0" + CSecond : CSecond;
    CMSecond = CMSecond < 10 ? "0" + CMSecond : CMSecond;
    if (seconds <= 0) {
        func(obj);
        return;
    } else {
        obj.html('<s></s> <span>揭晓倒计时</span> <i></i> <em>'+ CMinute +'</em> <i>:</i> <em>'+ CSecond +'</em> <i>:</i> <em>'+ CMSecond +'</em>');
    }
    obj.attr("left-time", parseFloat(seconds - parseFloat(1 / 100)));

    setTimeout(function () {
        leftTime(curTime, obj, func);
    }, 10);
}

var Gobal = new Object();

function loadImgFun(c) {
    var b = $("#loadingPicBlock");
    if (b.length > 0) {
        var i = "src2";
        Gobal.LoadImg = b.find("img[" + i + "]");
        var a = function () {
            return $(window).scrollTop()
        };
        var e = function () {
            return $(window).height() + a() + 50
        };
        var h = function () {
            Gobal.LoadImg.each(function (j) {
                if ($(this).offset().top <= e()) {
                    var k = $(this).attr(i);
                    if (k) {
                        $(this).attr("src", k).removeAttr(i).show()
                    }
                }
            })
        };
        var d = 0;
        var f = -100;
        var g = function () {
            d = a();
            if (d - f > 50) {
                f = d;
                h()
            }
        };
        if (c == 0) {
            $(window).bind("scroll", g)
        }
        g()
    }
}
var IsMasked = false;
var _IsLoading = false;
function scrollForLoadData(a) {
    $(window).scroll(function () {
        var c = $(document).height();
        var b = $(window).height();
        var d = $(document).scrollTop() + b;
        if (c - d <= b * 4) {
            if (!_IsLoading && a) {
                _IsLoading = true;
                a()
            }
        }
    })
}
(function () {
    Gobal.Skin = skinBaseUrl;
    Gobal.LoadImg = null;
    Gobal.LoadHtml = '<div class="loadImg">正在加载</div>';
    Gobal.LoadPic = skinBaseUrl+ "/images/loading.gif";
    Gobal.NoneHtml = '<div class="noRecords colorbbb clearfix"><s></s>暂无记录</div>';
    Gobal.NoneHtmlEx = function (b) {
        return '<div class="noRecords colorbbb clearfix"><s></s>' + b + '<div class="z-use">请使用电脑访问www.huogou.com查看更多</div></div>'
    };
    Gobal.LookForPC = '<div class="g-suggest clearfix">请使用电脑访问www.huogou.com查看更多</div>';
    Gobal.ErrorHtml = function (b) {
        return '<div class="g-suggest clearfix">抱歉，加载失败，请重试[' + b + "]</div>"
    };
    Gobal.unlink = "javascript:void(0);";
    loadImgFun(0);
})();
