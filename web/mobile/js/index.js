/**
 * Created by jun on 15/11/20.
 */
var cid = 0;
var page = 1;
var perpage = 40;
var orderFlag = 10;
$(function () {
    $('.select-icon').click(function(){
        $('.select-total').toggle();
    });

    //首页轮播图
    $.getJsonp(apiBaseUrl + '/banner/banner-list', {type: 0, source: 4, num: 5}, function (json) {
        createBannerList(json);
    });

    //商品列表
    var listdata = {'cid': cid, "page": page, "perpage": perpage, 'orderFlag': orderFlag};
    $.getJsonp(apiBaseUrl + '/product/list', listdata, function (json) {
        createGoodsListHtml(json);
    });

    ulOrder();

    $('#ulGoodsList').on('click', '.gRate', function (e) {
        stopBubble(e);
        var periodId = $(this).attr('periodId');
        var buyUnit = $(this).attr('buyUnit');
        addCart(periodId, 1*buyUnit);
    });

    $('#ulGoodsList').on('click', '.buy-btn', function (e) {
        stopBubble(e);
        var periodId = $(this).attr('periodId');
        var buyUnit = $(this).attr('buyUnit');
        goCart(periodId, 1*buyUnit);
    });

    //分类
    $.getJsonp(apiBaseUrl + '/product/catlist', {"catid": cid}, function (json) {
        createCateListHtml(json);
    });

    $('#btnLoadMore').click(function() {
        $('.loading').show();
        $('#btnLoadMore').hide();
        var page = $('#hidPage').val();
        page = parseInt(page)+1;
        var orderFlag = $('#hidOrderFlag').val();
        var listdata = {'cid': cid, "page": page, "perpage": perpage, 'orderFlag': orderFlag};
        $.getJsonp(apiBaseUrl + '/product/list', listdata, function (json) {
            createGoodsListHtml(json, true);
            if (json.list.length>0) {
                $('#hidPage').val(page);
                $('#btnLoadMore').show();
            }
        });
    });

    $('#ulGoodsList').on('click', 'li', function () {
        var productId = $(this).attr('id');
        var goodsUrl = createGoodsUrl(productId);
        return window.location.href = goodsUrl;
    });

    //nav固定
    var navTop = $('#goodsNav').offset().top + $('#goodsNav').height();
    var navOff = 0;
    $(window).on('scroll',function(){
        if ($(window).scrollTop() > navTop && !navOff) {
            $('#goodsNav').addClass('pFixed');
            navOff = 1;
        }else if($(window).scrollTop() < navTop && navOff){
            $('#goodsNav').removeClass('pFixed');
            navOff = 0;
        }
    })

});

function createBannerList(json) {

    $('#sliderBox').prev('.loading').hide();
    $('#sliderBox').show();

    var html = '<ul>';
    $.each(json, function (i, v) {
        var bannerImgUrl = v.picture;
        var bannerUrl = v.link.replace(/http:..[^\/]+/,'');
        var w = $(window).width();

        if (i == 0) {
            html += '<li style="display:block; width: '+w+'px"">';
        } else {
            html += '<li style="display: none;">';
        }
        html += '<a href="' + bannerUrl + '">';
        html += '<img src="' + bannerImgUrl + '" alt="" width="100%" height="100%" />';
        html += '</a>';
        html += '</li>';
    });
    html += '</ul>';

    var x = $(html);
    x.addClass("slides");
    $("#sliderBox").empty().append(x).flexslider({slideshow: true});

}


function createGoodsListHtml(json, append) {

    var html = '';
    $.each(json.list, function (i, v) {

        var periodId = v.period_id;
        var goodsName = v.name;
        var productId = v.product_id;
        var goodsUrl = createGoodsUrl(productId);
        var goodsPicUrl = createGoodsImgUrl(v.picture, photoSize[1], photoSize[1]);
        var price = v.price;
        var periodNumber = v.period_number;
        var salesNum = v.sales_num;
        var leftNum = v.left_num;
        var limitNum = v.limit_num;
        var buyUnit = v.buy_unit;
        var peopleNum = parseInt(price);
        var progress = parseFloat(salesNum / peopleNum) * 100;

        html += '<li id="' + productId + '">';
        if (limitNum > 0) {
            html += '<a href="' + goodsUrl + '" class="g-pic"> <img src="' + goodsPicUrl + '" width="136" height="136" /> <div class="pTitle pPurchase">限购</div></a>';
        } else if (buyUnit==10) {
            html += '<a href="' + goodsUrl + '" class="g-pic"> <img src="' + goodsPicUrl + '" width="136" height="136" /> <div class="pTitle sbei">十元</div></a>';
        } else {
            html += '<a href="' + goodsUrl + '" class="g-pic"> <img src="' + goodsPicUrl + '" width="136" height="136" /> </a>';
        }
        html += '<p class="g-name">' + goodsName + '</p>';
        html += '<ins class="gray9">价值:￥' + price + '.00 </ins>';
        html += '<div class="Progress-bar">';
        html += '<p class="u-progress"><span class="pgbar" style="width: ' + progress + '%;" > <span class="pging"></span> </span> </p>';
        html += '</div>';
        html += '<div class="btn-wrap">';
        html += '<a href="javascript:;" class="buy-btn" periodId="' + periodId + '" buyUnit="'+buyUnit+'">立即伙购</a>';
        html += '<div class="gRate" periodId="' + periodId + '" buyUnit="'+buyUnit+'">';
        html += '<a href="javascript:;">';
        html += '<s></s></a>';
        html += '</div>';
        html += '</div>';
        html += '</li>';

    });
    if (append) {
        $('#ulGoodsList').append(html);
    } else {
        $('#ulGoodsList').html(html);
    }
    if (html) {
        $('#btnLoadMore').show();
        $('.loading').hide();
    }
}

function ulOrder() {
    $('#ulOrder li a').on('click', function () {
        $('#ulOrder li ').removeClass('current');
        var objLi = $(this).closest('li');
        objLi.addClass('current');
        var orderFlag = objLi.attr('order');
        $('#hidOrderFlag').val(orderFlag);
        if (orderFlag==51) {
            objLi.attr('order',50);
        } else if(orderFlag==50) {
            objLi.attr('order',51);
        }
        var listdata = {'cid': cid, "page": page, "perpage": perpage, 'orderFlag': orderFlag};

        $('#ulGoodsList').html('');
        $('.loading').show();
        $('#btnLoadMore').hide();
        $.getJsonp(apiBaseUrl + '/product/list', listdata, function (json) {
            createGoodsListHtml(json);
        });
    });
}

function createCateListHtml(json) {

    var html = '';
    html += '<li cid="0" ><a href="javascript:;">全部分类</a></li>';
    $.each(json.list, function (i, v) {
        var cateId = v.id;
        var cateName = v.name;
        html += '<li cid="' + cateId + '" ><a href="javascript:;">' + cateName + '</a></li>';
    });
    html += '<li cid="0" limitFlag="1"><a href="javascript:;">限购专区</a></li>';
    html += '<li cid="0" buyUnit="10"><a href="javascript:;">十元专区</a></li>';
    $('ul.sort_list').html(html);

    $('#divSort').on('click', function () {
        if ($(this).hasClass('current')) {
            $(this).removeClass('current');
            $('.select-total').hide();
        } else {
            $(this).addClass('current');
            $('.select-total').show();
        }
    });

    $('.select-total ul li').on('click', function() {
        var orderFlag = $('#hidOrderFlag').val();
        var cid = $(this).attr('cid');
        var limitFlag = $(this).attr('limitFlag');
        var buyUnit = $(this).attr('buyUnit');
        var url = mobileBaseUrl+'/list.html?order='+orderFlag+'&cid='+cid;
        if (limitFlag) {
            url += '&limitFlag='+limitFlag;
        } else if (buyUnit==10) {
            url += '&buyUnit='+buyUnit;
        }
        window.location.href = url;
    });
}
