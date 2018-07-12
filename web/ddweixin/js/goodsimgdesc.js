/**
 * Created by jun on 15/12/8.
 */
/*
 * 智能机浏览器版本信息:
 *
 */
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
            webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
        };
    }(),
    language: (navigator.browserLanguage || navigator.language).toLowerCase()
}

var _swidth = 0;
var _dwidth = 0;
var _gheight = 0;
var setZoomFun = function (isResize) {
    var _goodsdesc = $(".goodsimgDesc").show();
    var _hwidth = _goodsdesc.width();//$("header.g-header").width();
    if (_hwidth == _swidth) { return; }
    _swidth = _hwidth;//window.screen.width;
    if (_dwidth == 0) {
        _dwidth = $(document).width();
    }
    if (_gheight == 0) {
        _gheight = _goodsdesc.height();
    }
    if (!isResize) {
        _goodsdesc.find("img").each(function () {
            var E = "src2";
            var H = $(this).attr(E);
            $(this).attr("src", H).removeAttr(E).show();

        });
    }
    var _zoom = parseFloat(_swidth / _dwidth);
    if (_zoom >= 1 || _zoom <= 0) {
        return;
    }
    // document.title = _zoom;
    if (browser.versions.ios || browser.versions.iPhone || browser.versions.iPad) {
        _goodsdesc.css("-webkit-transform-origin", "left top");
        _goodsdesc.css("-moz-transform-origin", "left top");
        _goodsdesc.css("-o-transform-origin", "left top");

        _goodsdesc.css("-webkit-transform", "scale(" + _zoom + ")");
        _goodsdesc.css("-moz-transform", "scale(" + _zoom + ")");
        _goodsdesc.css("-o-transform", "scale(" + _zoom + ")");

        _goodsdesc.css("height", _gheight * _zoom + "px");

    } else {
        _goodsdesc.css("zoom", _zoom);
    }
    swiperPic(_zoom);
}

$(document).ready(function () {
    setZoomFun(false);
});

$(window).resize(function () {
    setZoomFun(true);
});


//图文详情页滚动效果初始化
function swiperPic(_zoom) {
    var mySwiper = new Swiper('.swiper-container',{
        loop:true,
        autoplay : 2000,
        autoplayDisableOnInteraction : false,
        pagination: '.swiper-pagination',
        paginationClickable: true
    })
    $('.swiper-container').css({'height':$(window).height()/_zoom});
    $('.swiper-slide img').css({'height':$(window).height()/_zoom});
}
