/**
 * Created by jun on 16/1/28.
 */


var page = 1;
var perpage = 20;
var buyUnit = 10;
$(function() {
    sidebarCart(false);
    $('.quota_list').html('<img src="'+divLoadingImg+'">');
    var data = {'page':page, 'perpage': perpage, 'buyUnit':buyUnit};
    $.getJsonp(apiBaseUrl+'/product/list',data,productList);

    // 限购专区揭晓公告
    var data = {num:20};
    $.getJsonp(apiBaseUrl + '/buylist/new-buy-list', data, newBuyList);

});


//十倍专区最新购买
$(document).ready(function(){
    var timer = null;
    timer = setInterval(autoScroll,3000);
    $('.sbei-newest').mouseenter(function(){
        clearInterval(timer);

    }).mouseleave(function(){
        clearInterval(timer);
        timer = setInterval(autoScroll,3000);
    })

});
function autoScroll(){
    $('.sbei-newest').find('ul').animate({
        marginTop : "-60px"
    },800,function(){
        $(this).css({marginTop : "0px"}).find("li:lt(4)").appendTo(this);
    })
}
//十倍专区最新购买


function newBuyList(json) {
    var strHtml = '';
    $.each(json.list,function(i, v) {
        var userUrl = createUserCenterUrl(v.user_id);
        var userFaceUrl = createUserFaceImgUrl(v.avatar,avatarSize[1]);
        var goodsUrl = createGoodsUrl(v.product_id);
        var goodsName = v.product;
        var buyTime = v.buy_time2;
        var nickname = v.user_name;

        strHtml += '<li>';
        strHtml += '<a href="' + userUrl + '">';
        strHtml += '<picture><img src="'+userFaceUrl+'"></picture>';
        strHtml += '<article>';
        strHtml += '<h3>'+nickname+'</h3>';
        strHtml += '<p>'+goodsName+'</p>';
        strHtml += '<aside>'+buyTime+'</aside>';
        strHtml += '</article>';
        strHtml += '</a>';
        strHtml += '</li>';

    });
    $('#sbei-newest-list').html(strHtml);
}

function productList(json) {
    var html = '';
    if (json.list.length==0) {
        html += '<div class="notHaveB" style="display: block;">';
        html += '<span class="notHaveB_icon"></span>';
        html += '<p class="notHaveB_txt">暂无记录！</p>';
        html += '</div>';
        $(".quota_list").html(html);
        return;
    }
    $.each(json.list,function(i,v) {
        var goodsName = v.name;
        var goodsPic = v.picture;
        var goodsId = v.product_id;
        var price = v.price;
        var periodId = v.period_id;
        var periodNumber = v.period_number;
        var limitBuyNum = v.limit_num;
        var salesNum = v.sales_num;
        var quantity = v.price;
        var leftNum = v.left_num;

        var goodsUrl = createGoodsUrl(goodsId);
        var goodsImgUrl = createGoodsImgUrl(goodsPic,photoSize[1],photoSize[1]);

        var progressWidth = Math.ceil(100 * (salesNum/quantity));

        html += '<li  periodid="'+periodId+'" limitbuy="'+limitBuyNum+'" productid="'+goodsId+'">';
        html += '<div class="limitDiv">';
        html += '<div class="f-callout"><span class="sbei">十元</span></div>';
        html += '<picture><a href="'+goodsUrl+'" target="_blank"><img src="'+goodsImgUrl+'" alt="'+goodsName+'"></a></picture>';
        html += '<a href="'+goodsUrl+'" target="_blank" title=" '+goodsName+'"><h3> '+goodsName+'</h3></a>';
        html += '<aside>总需：'+quantity+'人次</aside>';
        html += '<p><i style="width: '+progressWidth+'%"></i></p>';
        html += '<summary>';
        html += '<span class="fl"><i>'+salesNum+'</i>已参与</span>';
        html += '<span class="rl">剩余<i>'+leftNum+'</i></span>';
        html += '</summary>';
        html += '<article>';
        html += '<a class="buy" href="javascript:void(0);">立即伙购</a><a class="car" href="javascript:;"></a>';
        html += '</article>';
        html += '</div>';
        html += '</li>';

    });
    $('.quota_list').html(html);
    $('.buy').on('click', function () {
        var productId = $(this).parents('li').attr('productid');
        window.location.href = createGoodsUrl(productId);
    });

    $('.car').on('click', function(){
        productImg = $(this).parents('li').find('img').attr('src');
        addProduct(productImg, $(this));
        var periodId = $(this).parents('li').attr('periodid');
        var cartdata = {'periodid':periodId,'num':10};
        $.getContent(apiBaseUrl+'/cart/add',cartdata,'cartadd');
    });

    $('.quota_list li').on('mouseenter',function(){
        $(this).addClass('p_hover');
    }).on('mouseleave',function(){
        $(this).removeClass('p_hover');
    })

}

