/**
 * Created by jun on 2015/11/20.
 */
var page = 1;
var perpage = 40;
var cid = GetQueryString('cid') ? GetQueryString('cid') : 0;
var orderFlag = GetQueryString('order') ? GetQueryString('order') : 10;
var limitFlag = GetQueryString('limitFlag') ? GetQueryString('limitFlag') : 'all';
var buyUnit = GetQueryString('buyUnit') ? GetQueryString('buyUnit') : 'all';
$(function () {

    $('#hidCateId').val(cid);
    $('#hidOrderFlag').val(orderFlag);
    var listdata = {'cid': cid, "page": page, "perpage": perpage, 'orderFlag': orderFlag, 'limit':limitFlag, 'buyUnit':buyUnit};

    $.getJsonp(apiBaseUrl + '/product/list', listdata, function (json) {
        $('#divGoodsLoading').css({'display': 'none'});
        createGoodsListHtml(json);
    });


    $.getJsonp(apiBaseUrl + '/product/catlist', {"catid": cid}, function (json) {
        createCateListHtml(json);

        sortListSelect();

    });

    $('.goodList').on('click','ul li',function(e) {
        var goodsId = $(this).attr('goodsid');
        var goodsUrl = createGoodsUrl(goodsId);
        window.location.href = goodsUrl;
        return;
    });

    $('.goodList').on('click', '.gRate a', function (e) {
        stopBubble(e);
        var periodId = $(this).attr('periodId');
        var buyUnit = $(this).attr('buyUnit');
        addCart(periodId, 1*buyUnit);
    });

    var stopLoadPage = false;
    var isLoading = false;
    $.onSrollBottom( function() {
        if (stopLoadPage || isLoading) {
            return;
        }

        isLoading = true;
        $('#divGoodsLoading').show();

        var pageVal = $('#hidPage').val();
        pageVal = parseInt(pageVal) + 1;
        var orderFlag = $('#hidOrderFlag').val();
        var cid = $('#hidCateId').val();
        var listdata = {'cid': cid, "page": pageVal, "perpage": perpage, 'orderFlag': orderFlag, 'limit':limitFlag, 'buyUnit':buyUnit};
        $.getJsonp(apiBaseUrl + '/product/list', listdata, function (json) {
            var t = function() {
                $('#divGoodsLoading').hide();
                if (json.list.length==0) {
                    stopLoadPage = true;
                } else {
                    createGoodsListHtml(json,true);
                    $('#hidPage').val(pageVal);
                    isLoading = false;
                }
            }
            setTimeout(t,1000);
        });
    });

    //nav固定
    var navTop = $('.column').offset().top;
    var navOff = 0;
    $(window).on('scroll',function(){
        if ($(window).scrollTop() > navTop && !navOff) {
            $('.column').addClass('pFixed');
            navOff = 1;
        }else if($(window).scrollTop() <= navTop && navOff){
            $('.column').removeClass('pFixed');
            navOff = 0;
        }
    })



});


function createGoodsListHtml(json,append) {
    var html = '';
    if(json.totalCount == "0") html = '<div class="noRecords colorbbb clearfix" id="nohave"><s></s>最近三个月无记录 <div class="z-use">请下载“伙购网”APP查看更多</div> </div>';
    $.each(json.list, function (i, v) {

        var periodId = v.period_id;
        var goodsId = v.product_id;
        var goodsName = v.name;
        var goodsUrl = createGoodsUrl(v.product_id);
        var goodsPicUrl = createGoodsImgUrl(v.picture, photoSize[1], photoSize[1]);
        var price = v.price;
        var periodNumber = v.period_number;
        var salesNum = v.sales_num;
        var leftNum = v.left_num;
        var limitNum = v.limit_num;
        var buyUnit = v.buy_unit;
        var peopleNum = parseInt(price);
        var progress = parseFloat(salesNum / peopleNum) * 100;


        html += '<ul>';
        html += '<li goodsid="'+goodsId+'">';
        if (limitNum>0) {
            html += '<span class="gList_l fl"><img src="' + goodsPicUrl + '" /><div class="pTitle pPurchase">限购</div></span>';
        } else if (buyUnit==10) {
            html += '<span class="gList_l fl"><img src="' + goodsPicUrl + '" /><div class="pTitle sbei">十元</div></span>';
        } else {
            html += '<span class="gList_l fl"><img src="' + goodsPicUrl + '" /></span>';
        }
        html += '<div class="gList_r">';
        html += '<h3 class="gray6"><a href="' + goodsUrl + '">' + goodsName + '</a></h3>';
        html += '<em class="gray9">价值：￥' + price + '.00</em>';
        html += '<div class="gRate">';
        html += '<div class="Progress-bar">';
        html += '<p class="u-progress"><span style="width: ' + progress + '%;" class="pgbar"> <span class="pging"></span> </span> </p>';
        html += '<ul class="Pro-bar-li">';
        html += '<li class="P-bar01"><em>' + salesNum + '</em>已参与</li>';
        html += '<li class="P-bar02"><em>' + peopleNum + '</em>总需人次</li>';
        html += '<li class="P-bar03"><em>' + leftNum + '</em>剩余</li>';
        html += '</ul>';
        html += '</div>';
        html += '<a href="javascript:" periodId="' + periodId + '" buyUnit="'+buyUnit+'">';
        html += '<s></s>';
        html += '</a>';
        html += '</div>';
        html += '</div>';
        html += '</li>';
        html += '</ul>';


    });
    if (append) {
        $('.goodList').append(html);
    } else {
        $('.goodList').html(html);
    }
}

function sortListSelect() {
    $('.entry-list').on('click', function () {
        $(this).append('<cite><em></em></cite>');
        $('#sort_list_cate').show();
        $('#sort_list_order').hide();
        $('.goodList').hide();
    });

    $('.ann-publicly').on('click', function () {

        $(this).append('<cite><em></em></cite>');
        $('#sort_list_order').show();
        $('#sort_list_cate').hide();
        $('.goodList').hide();
    });

    $('#sort_list_cate').on('click', 'a', function() {
        var ths = $(this);
        var cid = ths.attr('cid');
        limitFlag = ths.attr('limitFlag');
        buyUnit = ths.attr('buyUnit');
        ths.closest('.sort_list').find('a').removeClass('hover');
        ths.addClass('hover');
        $('.entry-list').html(ths.text()+'<span></span><b class="fr"></b>');
        $('#hidCateId').val(cid);
        var orderFlag = $('#hidOrderFlag').val();
        if (limitFlag=='1' || buyUnit=='10') {
            orderFlag = '40';
            $('.ann-publicly').html('<font color="#bbbbbb">最新</font><b class="fl"></b></a>');
        } else {
            limitFlag = 'all';
            buyUnit = 'all';
            var orderFlagText = $('#sort_list_order ul li[order='+orderFlag+']').text();
            $('.ann-publicly').html(orderFlagText+'<span></span><b class="fr"></b>');
        }
        var listdata = {'cid': cid, "page": 1, "perpage": perpage, 'orderFlag': orderFlag, 'limit':limitFlag, 'buyUnit':buyUnit};

        ths.closest('.sort_list').hide();
        $('.goodList').show();
        $.getJsonp(apiBaseUrl + '/product/list', listdata, function (json) {
            $('#divGoodsLoading').css({'display': 'none'});
            $('.goodList').html('');
            createGoodsListHtml(json);
        });

    });

    $('#sort_list_order').on('click', 'li a', function() {
        var ths = $(this);
        var orderFlag = ths.closest('li').attr('order');
        ths.closest('.sort_list').find('li a').removeClass('hover');
        ths.addClass('hover');
        $('.ann-publicly').html(ths.text()+'<span></span><b class="fr"></b>');
        $('#hidOrderFlag').val(orderFlag);
        var cid = $('#hidCateId').val();
        var listdata = {'cid': cid, "page": 1, "perpage": perpage, 'orderFlag': orderFlag};

        $.getJsonp(apiBaseUrl + '/product/list', listdata, function (json) {
            $('#divGoodsLoading').css({'display': 'none'});
            $('.goodList').html('');
            createGoodsListHtml(json);
            ths.closest('.sort_list').hide();
            $('.goodList').show();
        });

    });


    //选中
    if (limitFlag=='1') {
        var cateText = '限购专区';
        $('.entry-list').html(cateText+'<span></span><b class="fr"></b>');
        $('#sort_list_cate a[limitFlag=1]').addClass('hover');
    } else if (buyUnit=='10') {
        var cateText = '十元专区';
        $('.entry-list').html(cateText+'<span></span><b class="fr"></b>');
        $('#sort_list_cate a[buyUnit=10]').addClass('hover');
    } else {
        var cateText = $('#sort_list_cate a[cid='+cid+']:eq(0)').text();
        $('.entry-list').html(cateText+'<span></span><b class="fr"></b>');
        $('#sort_list_cate a[cid='+cid+']').not('[limitFlag=1]').not('[buyUnit=10]').addClass('hover');
    }
    if (limitFlag=='1' || buyUnit=='10') {
        $('.ann-publicly').html('<font color="#bbbbbb">最新</font><b class="fl"></b></a>');
    } else {
        var orderFlagText = $('#sort_list_order ul li[order='+orderFlag+']').text();
        $('.ann-publicly').html(orderFlagText+'<span></span><b class="fr"></b>');
    }

    $('#sort_list_order ul li').removeClass('hover');
}

function createCateListHtml(json) {
    var curCateId = $('#hidCateId').val();
    if (curCateId==0 && limitFlag!='1' && buyUnit!='10') {
        var html = '<a cid="0" href="javascript:;" class="hover">全部分类<i></i></a>';
    } else {
        var html = '<a cid="0" href="javascript:;" >全部分类<i></i></a>';
    }

    $.each(json.list, function(i,v) {
        var cateId = v.id;
        var cateName = v.name;
        if (curCateId==cateId && limitFlag!='1' && buyUnit!='10') {
            html += '<a cid="'+cateId+'" href="javascript:;" class="hover">'+cateName+'<i></i></a>';
        } else {
            html += '<a cid="'+cateId+'" href="javascript:;" >'+cateName+'<i></i></a>';
        }
    });
    if (limitFlag=='1') {
        html += '<a cid="0" limitFlag="1" href="javascript:;" class="hover">限购专区<i></i></a>';
    } else {
        html += '<a cid="0" limitFlag="1" href="javascript:;" >限购专区<i></i></a>';
    }
    if (buyUnit=='10') {
        html += '<a cid="0" buyUnit="10" href="javascript:;" class="hover">十元专区<i></i></a>';
    } else {
        html += '<a cid="0" buyUnit="10" href="javascript:;" >十元专区<i></i></a>';
    }
    $('#sort_list_cate').html(html);
}




