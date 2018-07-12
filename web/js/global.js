var arrCount = [];
var oldCountLen = null;
var isLogined = 0;
var t={};//时间，对象
$(document).ready(function () {

    quickLinksBar();//加上短侧边栏

    $('#mobile').on('mouseenter', function () {
        $(this).find('p').stop().fadeIn();
    }).on('mouseleave', function () {
        $(this).find('p').stop().fadeOut();
    })

    $('.sort').find('dl').each(function (index) {
        if ((index + 1) % 2 == 0) {
            $(this).css('backgroundColor', '#f3f3f3');
        }
    });

    $('.log_arr').each(function () {
        $(this).on('mouseenter', function () {
            $(this).find('div').stop().fadeIn();
        }).on('mouseleave', function () {
            $(this).find('div').stop().fadeOut();
        })
    });
    $("body").on('click', '#get_top', function () {
        $('body,html').animate({scrollTop: 0}, 1000);
        return false;
    });

    var searchInput = $('#search_input').val();
    $('#search_input').on('focus', function () {
        $('#search_span').hide();
        if ($(this).val() == searchInput) {
            $(this).val("");
        }
    }).on('blur', function () {
        $('#search_span').show();
        if ($(this).val() == "") {
            $(this).val(searchInput);
            $('#search_input').css('color', '#bbb');
        }
    }).on('keydown', function () {
        $('#search_input').css('color', '#000');
    });

    //判断不是首页不触发事件
    var url = window.location.pathname.substring(1).split('.')[0];
    if (url != '') {
        $('.sort').on('mouseenter', function () {
            $(this).find('div').stop().show();
        }).on('mouseleave', function () {
            $(this).find('div').stop().hide();
        })
    }


    // ---------------服务器时间----------------

    $.getContent(apiBaseUrl + '/data/date', {}, 'serverTime');

    // ---------------购物车商品数量----------------
    if (window.location.pathname.split('/')[1] != 'cart.html') {
        $.getContent(apiBaseUrl + "/cart/list", {"token": token}, 'sideBarCart');
    }
    ;

    // ---------------总参与人数----------------

    //$.getContent(apiBaseUrl + '/data/total-buy-count', {}, 'totalBuyCount');
    //
    //setInterval(function () {
    //    $.getContent(apiBaseUrl + '/data/total-buy-count', {}, 'totalBuyCount');
    //}, 4000);

    $(".search a").click(function () {
        window.location.href = wwwBaseUrl + "/search.html?q=" + encodeURIComponent($(this).text());
    })
    $(".search input[type=submit]").click(function () {
        window.location.href = wwwBaseUrl + "/search.html?q=" + encodeURIComponent($("#search_input").val());
    })

    document.onkeydown = function (event) {
        var e = event || window.event || arguments.callee.caller.arguments[0];
        if (e && e.keyCode == 13 && e.srcElement.id == 'search_input') { // enter 键
            window.location.href = wwwBaseUrl + "/search.html?q=" + encodeURIComponent($("#search_input").val());
        }
    };

    //判断邀请页面 body加上背景色
    var url = window.location.pathname.substring(1).split('.')[0];
    if (url == 'invite') {
        $('body').css('backgroundColor', '#fff');
    }
    if (url == 'wechat') {
        $('body').css('height', 'auto');
    }

    //App下载
    //$('.moblieApp').on('click',function(){
    //    var appDownObj = $('<section id="appDownObj" class="safety-b-con"><div class="safety-b-box"><div class="safety-b-box"></div><i class="safety-b-close"></i><h4>即将上线，敬请期待！</h4></div></section>');
    //    $('.footer_b_01').append(appDownObj);
    //    $('#appDownObj').fadeIn();
    //})

    //wechat
    //$('.wechat').on('click',function(){
    //    var appDownObj = $('<section id="appDownObj" class="safety-b-con"><div class="safety-b-box"><div class="safety-b-box"></div><i class="safety-b-close"></i><h4>即将上线，敬请期待！</h4></div></section>');
    //    $('.footer_b_01').append(appDownObj);
    //    $('#appDownObj').fadeIn();
    //})


    $('.footer_b_01').on('click', '.safety-b-close', function () {
        $('#appDownObj').fadeOut();
    })

});

function sidebarCart(scroll) {
    $('#quick-links').remove();
    var headerOn = 0;

    var headerMin = function () {
        if (!headerOn || $(window).scrollTop() > 20) {
            $(".sidebar").fadeIn();
            headerOn = 1;
        } else if (headerOn || $(window).scrollTop() < 20) {
            $(".sidebar").fadeOut();
            headerOn = 0;
        }
    }
    if (scroll) {
        $(window).on('scroll', headerMin);
    } else {
        $(".sidebar").show();
    }
    var innerTimer = null;
    $('.sidebar_shop, #car-inner').on('mouseenter', function () {
        $('#car-inner').stop().fadeIn();
        clearTimeout(innerTimer);
    }).on('mouseleave', function () {
        clearTimeout(innerTimer);
        innerTimer = setTimeout(function () {
            $('#car-inner').stop().fadeOut();
        }, 1e3);
    });
}

function quickLinksBar() {
    var html = '';
    html += '<section id="quick-links">';
    html += '<ul>';
    html += '<li class="quick-a">';
    html += '<a href="javascript:;" id="service_qq_d"><b>在线<br>客服</b></a>';
    html += '</li>';
    html += '<li class="quick-b">';
    html += '<a target="_blank" href=http://help.' + baseHost + '/app.html>';
    html += '<b>手机<br>APP</b>';
    html += '<p><img src="' + skinBaseUrl + '/img/interact/interact_icon02.png" alt="" width="71" height="71"><br>下载手机端</p>';
    html += '</a>';
    html += '</li>';
    html += '<li class="quick-c">';
    html += '<a target="_blank" href=http://help.' + baseHost + '/wechat.html>';
    html += '<b>微信</b>';
    html += '<p><img src="' + skinBaseUrl + '/img/wechat.jpg" alt="" width="71" height="71"><br>关注官方微信</p>';
    html += '</a>';
    html += '</li>';
    html += '<li class="quick-d">';
    html += '<a target="_blank" href=http://help.' + baseHost + '/suggestion.html><b>反馈</b></a>';
    html += '</li>';
    html += '<li id="get_top" class="quick-e"></li>';
    html += '</ul>';
    html += '</section>';
    $('body').append(html);
}

$(window).scroll(function () {
    if ($(window).scrollTop() >= 10) {
        $('#quick-links').fadeIn();
    } else {
        $('#quick-links').fadeOut()
    }
})

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

    t.time = new Date().getTime();
	t.i = parseInt(CMinute);
	t.s = parseInt(CSecond);
	t.ms = parseInt(CMSecond);

	10 > t.i && (t.i = "0" + t.i);
	10 > t.s && (t.s = "0" + t.s);
	0 > t.ms && (t.ms = "00");

	t.oi = String(t.i).slice(0, 1);
	t.ti = String(t.i).slice(1);
	t.os = String(t.s).slice(0, 1);
	t.ts = String(t.s).slice(1);
	t.oms = String(t.ms).slice(0, 1);
	t.tms = String(t.ms).slice(1)?String(t.ms).slice(1):1;
    if (seconds <= 0) {
        func(obj);
        return;
    } else {
        //obj.html("<i>" + CMinute + "</i><em>:</em><i>" + CSecond + "</i><em>:</em><i>" + CMSecond + "</i>");
        obj.html("<i>" + t.oi + "</i><i>" + t.ti + "</i><em>:</em><i>" + t.os + "</i><i>" + t.ts + "</i><em>:</em><i>" + t.oms + "</i><i>" + t.tms + "</i>");
    }
    obj.attr("left-time", parseFloat(seconds - parseFloat(1 / 100)));
    setTimeout(function () {
        leftTime(curTime, obj, func);
    }, 10);
}

//分割时分秒
function division(s) {
    var arr = new Array();
    arr[0] = s.toString().substr(0, 1);
    arr[1] = s.toString().substr(1, 1);
    return arr;
}
function shoucang(sTitle, sURL) {
    try {
        window.external.addFavorite(sURL, sTitle);
    }
    catch (e) {
        try {
            window.sidebar.addPanel(sTitle, sURL, "");
        }
        catch (e) {
            alert("加入收藏失败，请使用Ctrl+D进行添加");
        }
    }
}

function addProduct(imgSrc, buttonObj) {
    var sideShow = true;
    if ($(".sidebar").css("display") == 'none') {
        sideShow = false;
        $(".sidebar").fadeIn();

    }
    ;
    var cartOffset = $('.sidebar_shop').offset();
    var buttonOffset = buttonObj.offset();
    var bodyScrollTop = $(window).scrollTop();
    var flyer = $('<div class="u-flyer"><img src="' + imgSrc + '"/></div>');//动画图
    flyer.fly({
        start: {
            left: buttonOffset.left,
            top: buttonOffset.top - bodyScrollTop
        },
        end: {
            left: cartOffset.left + 13,
            top: cartOffset.top - bodyScrollTop + 15,
            width: 20, height: 20
        },
        onEnd: function () {
            if (!sideShow) {
                $(".sidebar").fadeOut();
            }
            ;
            $.getJsonp(apiBaseUrl + "/cart/list", {"token": token}, success_sideBarCart);
            flyer.html('<span>+1</span>').animate({'opacity': 0, marginTop: -80}, 1500, function () {
                $(this).remove();
            });

            $('.sidebar article .sidebar_shop i').css({'left': 10});
            for (var i = 1; 5 >= i; i++) {
                $('.sidebar article .sidebar_shop i').animate({left: 10 - (5 - 1 * i)}, 30);
                $('.sidebar article .sidebar_shop i').animate({left: 10 + 2 * (5 - 1 * i)}, 30);
            }

        }
    });
}

function success_sideBarCart(json) {
    var count = total = 0;
    if (json) {
        count = json.list.length;
        $(".sidebar_shop b").text("(" + count + ")");
        $("#car-list").html('');
        var html = '';
        if (json.list.length == 0) {
            html += '<div id="divEmpty" class="No_shopping" style="padding-top: 330px;">';
            html += '<b></b>';
            html += '<p>您的购物车为空！</p>';
            html += '</div>';
            $("#car-list").html(html);
            return;
        }
        $.each(json.list, function (i, v) {
            html += '<dl productId="' + v.product_id + '" cid="' + v.id + '">';
            html += '<dt><img src="' + createGoodsImgUrl(v.picture, photoSize[1], photoSize[1]) + '"></dt>';
            html += '<dd class="car-list-info">';
            html += '<p>伙购人次：<span class="car-info-num">' + v.nums + '</span></p>';
            html += '<p>小结：<span class="car-info-money">￥' + v.nums + '.00</span></p>';
            html += '</dd>';
            html += '<dd class="car-list-ctrl">';
            html += '<p>剩余 ' + v.left_num + ' 人次</p>';
            html += '<span class="car-list-modify" limit="' + v.limit_num + '">';
            html += '<a href="javascript:;" class="car-modify-less">-</a>';
            html += '<input type="text" value="' + v.nums + '" class="car-list-num">';
            html += '<a href="javascript:;" class="car-modify-add">+</a>';
            html += '</span>';
            if (v.limit_num > 0) {
                html += '<p>限购' + v.limit_num + '人次</p>';
            }
            
            html += '<a href="javascript:;" class="car-list-delete">X</a>';
            html += '</dd>';
            html += '</dl>';
            total += parseInt(v.nums);
        });
        $("#car-list").html(html);
        $('.car-inner-bottom p').html('共<span id="car-count-num">' + count + '</span>个商品合计：<em id="car-count-money">' + total + '.00</em>元');
        function getTotal() {
            var total = 0;
            $("#car-list dl").each(function () {
                total += parseInt($(this).find('.car-list-num').val());
            })
            $("#car-count-money").text(total + '.00');
        }
    }
    $('.car-modify-less').on('click', function () {
        var numIpt = $(this).siblings('.car-list-num');
        var iNum = parseInt(numIpt.val());
        if (iNum > 1) {
            numIpt.val(iNum - 1);
        }
        getTotal();
    })

    $('.car-modify-add').on('click', function () {
        var numIpt = $(this).siblings('.car-list-num');
        var limit = $(this).parent().attr('limit');

        if (limit == 0 || (parseInt(numIpt.val()) + 1) <= limit) {
            numIpt.val(parseInt(numIpt.val()) + 1);
            getTotal();
        }

    })

    $('.car-list-num').on('keyup', function () {
        var iNum = parseInt($(this).val());
        if (iNum < 1 || !iNum) {
            $(this).val(1);
        } else {
            var limit = $(this).parent().attr('limit');
            if (limit == 0 || iNum <= limit) {
                $(this).val(iNum);
            } else {
                $(this).val(limit);
            }
        }
        getTotal();
    }).on('mouseenter', function () {
        $(this).select();
    }).on('mouseleave', function () {
        $(this).focus().val($(this).val());
    })

    function carInfoUpdate(This) {
        var val = This.find('.car-list-num').val();
        This.find('.car-info-num').html(val);
        This.find('.car-info-money').html('￥' + val + '.00');
        $.getContent(apiBaseUrl + '/cart/changenum', {pid: This.attr('productId'), num: val}, 'cartadd');
    }


    $('#car-list').find('dl').on('mouseenter', function () {
        $(this).addClass('hover');
    }).on('mouseleave', function () {
        $(this).removeClass();
        carInfoUpdate($(this));
    })

    $('.car-list-delete').on('click', function () {
        $.getContent(apiBaseUrl + '/cart/del', {cid: $(this).parents('dl').attr('cid')}, 'cartdel');
        $(this).parents('dl').remove();
        if ($("#car-list dl").length == 0) {
            var html = '';
            html += '<div id="divEmpty" class="No_shopping" style="padding-top: 330px;">';
            html += '<b></b>';
            html += '<p>您的购物车为空！</p>';
            html += '</div>';
            $("#car-list").html(html);
        }
    })
    $(".shopCart_total").html(count);
}

function timeFormat(data) {
    return data > 9 ? data : "0" + data;
}
var serverTime;
function success_serverTime(json) {
    serverTime = Date.parse(json.time);
    setInterval(function () {
        var sH = new Date(serverTime).getHours();
        var sM = new Date(serverTime).getMinutes();
        var sS = new Date(serverTime).getSeconds();
        $('#server-time').html('<span>' + timeFormat(sH) + '</span> : <span>' + timeFormat(sM) + '</span> : <span>' + timeFormat(sS) + '</span>');
        serverTime += 1000;
    }, 1000);
}

function success_totalBuyCount(json) {
    if (json.count == null) {
        return false;
    }
    arrCount = json.count.replace(/[^0-9]/ig, "").split("");
    if (arrCount.length > 0 && arrCount.length == oldCountLen) {
        countUpdate();
    } else if (arrCount.length > 0) {
        var html = "";
        for (var i = arrCount.length - 1; i >= 0; i--) {
            html += '<li><aside><em>0</em></aside></li>';
        }
        ;
        $("#counts").html(html);
        countInit();
    }
    oldCountLen = arrCount.length;
}

function countInit() {
    var strCounts = "";
    var arrComma = [];
    for (var i = 1, l = arrCount.length - 1; i < Math.floor(l / 3) + 1; i++) {
        arrComma.unshift(l - 3 * i);
    }
    ;

    for (var i = 0; i < arrCount.length; i++) {
        strCounts += "<li class='num'><aside>";
        for (var j = arrCount[i]; j >= 0; j--) {
            strCounts += "<em>" + j + "</em>";
        }
        strCounts += "</aside></li>";
        if (i == arrComma[0]) {
            strCounts += "<li class='nobor'>,</li>";
            arrComma.shift();
        }
    }
    $('#counts').html(strCounts).find('aside').each(function () {
        $(this).stop().animate({'bottom': ($(this).find('em').size() - 1) * -32}, 2e3);
    });
}

function countUpdate() {
    $('#counts').find('aside').each(function (index) {
        var old = $(this).find('em').eq(0).html();
        var newNum = arrCount[index] - 1;
        var strCounts = "";

        do {
            newNum++;
            if (newNum > 9) newNum = 0;
            strCounts += "<em>" + newNum + "</em>";
        } while (old != newNum)

        $(this).html(strCounts).css('bottom', 0).stop().animate({'bottom': ($(this).find('em').size() - 1) * -32}, 2e3);
    })
}

