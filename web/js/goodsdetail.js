$(function () {

    sidebarCart(false);
    productId = getProductId();
    var data = {'id': productId, "token": token};
    $.getContent(apiBaseUrl + '/product/info', data, 'productInfo');
    data = {"pid": productId};
    $.getContent(apiBaseUrl + '/share/share-list', data, 'topicList');

    $("#div_menu ul li").click(function () {
        $("#div_menu ul li").removeClass("current");
        $(this).addClass("current");
        var i = $(this).index();
        if (i == 0) {
            $("#div_desc").show();
            $("#div_allrecord").show().find('.rocord-header').show();
            $("#div_postlist").show().find('.ng-share-header').show();
        } else if (i == 1) {
            $("#div_desc").hide();
            $("#div_allrecord").show().find('.rocord-header').hide();
            $("#div_postlist").hide();
        } else if (i == 2) {
            $("#div_desc").hide();
            $("#div_allrecord").hide();
            $("#div_postlist").show().find('.ng-share-header').hide();
        }
        ;
    })

    $(".guanzhu").click(function () {
        $.getContent($(this).attr('href'), {"token": token, "pid": productId}, 'follow');
        return false;
    })


    var navTop = $('.con_title').offset().top + $('.con_title').height();
    var navOff = 0;
    $(window).on('scroll', function () {
        if ($(window).scrollTop() > navTop && !navOff) {
            $('.con_title').addClass('fixed');
            navOff = 1;
        } else if ($(window).scrollTop() < navTop && navOff) {
            $('.con_title').removeClass('fixed');
            navOff = 0;
        }
    })


})
//商品进行中--商品信息
function success_productInfo(json) {
    var title = '';
    if (json.periodInfo && json.periodInfo.period_number.length > 0) {
        $(".renci").show();
        // $(".renci_gw").show();
        //$(".phase").append('<a class="first act" href="/product/' + json.id + '.html">第' + json.periodInfo.period_number + '期<i></i><b></b></a>');
        $(".phase").append('<a class="first act" href="/product/' + json.id + '.html"><i></i><b></b></a>');
        // $("title").html(json.name);
        document.title = json.name;
        $(".phase_centre_title").append('<h2>' + json.name + '<span class="text-red">' + json.brief + '</span>' + '</h2>');
        $(".phase_centre_title").append('<aside>期号&nbsp;:&nbsp;' + json.periodInfo.period_no + '&nbsp;&nbsp;&nbsp;价值&nbsp;：&nbsp;￥' + json.periodInfo.price + '.00&nbsp;&nbsp;&nbsp;&nbsp;每满' + json.periodInfo.price + '人次，即抽取1人获得该商品</aside>');
        $(".phase_centre").attr("pid", json.periodInfo.id);

        var canBuyNum = json.limit_num > 0 ? parseInt(json.limit_num - json.userInfo.hasBuy) : json.periodInfo.left_num;

        createScheduleHtml(json.periodInfo, json.limit_num, canBuyNum, json.userInfo.hasBuy);
        getmycode();
        $("#renci_value").val(1 * json.periodInfo.buy_unit);
        $(".renci_gw a").click(function () {
            if (canBuyNum == 0) {
                return false
            }
            ;
            var cartdata = {'periodid': json.periodInfo.id, 'num': $("#renci_value").val()};
            if ($(this).attr('class') == 'renci_gw_a') {
                $.getContent(apiBaseUrl + '/cart/add', cartdata, 'goCart');
            } else if ($(this).attr('class') == 'car') {
                $.getContent(apiBaseUrl + '/cart/add', cartdata, 'cartadd');
                addProduct($(".phase_fl picture img").attr("src"), $(".car"));
            }
            return false;
        });

        $('.gouw').click(function () {
            var cartdata = {'periodid': json.periodInfo.id, 'num': 1 * json.periodInfo.buy_unit};
            $.getContent(apiBaseUrl + '/cart/add', cartdata, 'cartadd');
            addProduct($(".phase_fl picture img").attr("src"), $(this));
        });

    } else {
        $(".phase_centre_title").append('<h2>' + json.name + '</h2>');
        $(".phase_centre_title").append('<h3></h3>');
        $(".renci").remove();
        $(".renci_gw").remove();
        $(".renshu").after('<div class="jieshu_01"></div>');
    }
    // $("#curcat").text(json.catName).attr('href','/list-'+json.cat_id+'-0.html');
    $(".present").append(json.catNav + '<i></i>商品详情');
    $('#contentInfo').html(json.intro);
    if (!json.periodInfo) {
        var buyUnit = json.buy_unit;
    } else {
        var buyUnit = json.periodInfo.buy_unit;
    }
    if (json.periodInfo) {
        $.getContent(apiBaseUrl + '/period/buylist', {'id': json.periodInfo.id}, 'buyList');
    } else {
        var item = '<li class="tishi"><p>还没有人参与？</p><p>梦想与您只有1元的距离！</p></li>';
        $("#newbuy").append(item);
        $(".canyu").remove();
        $("#canyu").html('<ul class="phase_rl_list" style="height:120px;">' + item + '</ul>').find('li').css("cssText", "margin-top:20px!important");
    }
    createPhotoList(json.photoList, json.limit_num, null, null, buyUnit);
    $.getContent(apiBaseUrl + '/product/periodlist', {"id": productId}, 'periodList');

    $.getJsonp(apiBaseUrl + '/product/oldperiodlist', {
        'id': productId,
        'showinfo': 1,
        'page': 1,
        'perpage': 10
    }, function (json) {
        allperiodlist(json);
    });
    isFollowed(json.userInfo.followed);

    var picture = createGoodsImgUrl(json.picture, photoSize[2], photoSize[2]);
    bShare.addEntry({
        title: json.name,
        //url: "分享的链接，默认为当前页面URL",
        summary: "伙购网1元就可以买到你想要的商品哦，小伙伴们购起来！",
        pic: picture
    });
    sliderPic();

}

//商品进行中——商品购买信息替换
function createScheduleHtml(periodinfo, limit, canBuyNum, hasBuy) {
    var scheduleHtml = '';
    var schedule = parseInt(periodinfo.sales_num / periodinfo.price);
    var surplus = parseInt(periodinfo.price - periodinfo.sales_num);
    scheduleHtml += '<p class="progress_2"><i style="width:' + changeTwoDecimal_f(periodinfo.sales_num * 100 / periodinfo.price) + '%"></i></p>';
    scheduleHtml += '<p class="already">已完成<span class="text-red">' + changeTwoDecimal_f(periodinfo.sales_num * 100 / periodinfo.price) + '%</span></p>';
    scheduleHtml += '<summary class="progress-num">';
    scheduleHtml += '<span class="fl"><i>' + periodinfo.price + '</i><br>总需人次</span>';
    scheduleHtml += '<span class="fl" style="margin-left: 150px;"><i>' + periodinfo.sales_num + '</i><br>已参与人次</span>';
    scheduleHtml += '<span class="rl"><i class="text-red">' + surplus + '</i><br>剩余人次</span>';
    scheduleHtml += '</summary>';
    $(".phase_centre .renshu").html(scheduleHtml).show();
    var quickAmount = '';
    if (limit > 0) {
        var tip = '<div class="quota_txt"><i></i>限购<span>' + limit + '</span>人次</div>';
        $(".quota_tip").append(tip).show();
    } else {
        if (surplus < 30) {
            quickAmount += '<b class="renci_sz">' + surplus + '</b>';
        }
        ;
        if (surplus > 30) {
            quickAmount += '<b class="renci_sz">30</b>';
        }
        ;
        if (surplus > 50) {
            quickAmount += '<b class="renci_sz">50</b>';
        }
        ;
        if (surplus > 100) {
            quickAmount += '<b class="renci_sz">100</b>';
        }
        ;
        quickAmount += '<i>人次</i>';
        $("#buynum").after(quickAmount);
    }

    if (canBuyNum > 0) {
        $.changeByNum('buynum', periodinfo.price, surplus, canBuyNum, 'win_prob', periodinfo.buy_unit);
    }
    ;

    if (limit > 0 && hasBuy > 0) {
        if (canBuyNum > 0) {
            $(".quota_txt").append("，您已购买" + hasBuy + "人次");
        } else {
            $(".renci_gw a").css({"background": "#dedede", "border": "none", "color": "#ffffff"});
            $(".quota_txt").append("，您参与人数已达上限");
            $("#renci_value").attr('disabled', true).css("background", "#eeeeee");
            $("#buynum span").css('background', '#eeeeee');
        }
    }

    $("#buynum").show();
    $(".renci_gw").show();

    $(".renci .renci_sz").click(function () {
        $("#renci_value").val($(this).text());
        $(".win_prob").html('<span class="win_txt">获得几率' + changeTwoDecimal_f($(this).text() / periodinfo.price * 100) + '%<i></i></span>').show();
        setTimeout(function () {
            $(".win_prob").fadeOut();
        }, 3000)
    });

}