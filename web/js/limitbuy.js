/**
 * Created by fu on 2015/10/17.
 */

//限购专区揭晓公告
$(document).ready(function(){
    var timer = null;
    timer = setInterval(autoScroll,3000);
    $('.quota_gg').mouseenter(function(){
        clearInterval(timer);

    }).mouseleave(function(){
        clearInterval(timer);
        timer = setInterval(autoScroll,3000);
    })

});
function autoScroll(){
    $('.quota_gg').find('#scrollDiv').animate({
        marginTop : "-50px"
    },800,function(){
        $(this).css({marginTop : "0px"}).find("li:first").appendTo(this);
    })
}
//限购专区揭晓公告


var page = 1;
var perpage = 20;
var limit = 1;
var isRevealed = 1;
$(function() {
    sidebarCart(false);
    $('.quota_list').html('<img src="'+divLoadingImg+'">');
    var data = {'page':page, 'perpage': perpage, 'limit':limit};
    $.getContent(apiBaseUrl+'/product/list',data,'productList');

    // 限购专区揭晓公告
    var data = {'page':page, 'perpage': perpage, 'isRevealed': isRevealed, 'isLimit': limit};
    $.getContent(apiBaseUrl + '/period/publish-goods', data, 'limitBuyAdvertise');
});

function success_productList(json) {
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
        html += '<picture><a href="'+goodsUrl+'" target="_blank"><img src="'+goodsImgUrl+'" alt=""></a></picture>';
        html += '<div>';
        html += '<a href="'+goodsUrl+'" target="_blank" title=" '+goodsName+'"><h3> '+goodsName+'</h3></a>';
        html += '<aside>总需：'+quantity+'人次<b><i></i>限购'+limitBuyNum+'人次</b></aside>';
        html += '<p><i style="width: '+progressWidth+'%"></i></p>';
        html += '<summary><span class="fl"><i>'+salesNum+'</i>已参与</span> <span class="rl">剩余<i>'+leftNum+'</i></span> </summary>';
        html += '</div>';
        html += '<article><a class="buy" href="javascript:void(0);">立即伙购</a><a class="car" href="javascript:;"></a> </article>';
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
        var cartdata = {'periodid':periodId,'num':1,'token':token};
        $.getContent(apiBaseUrl+'/cart/add',cartdata,'cartadd');
    });

    $('.quota_list li').on('mouseenter',function(){
        $(this).addClass('p_hover');
    }).on('mouseleave',function(){
        $(this).removeClass('p_hover');
    })

}

function success_limitBuyAdvertise(json) {
    $.each(json.list,function(i, v) {
        var userUrl = createUserCenterUrl(v.user_home_id);
        var goodsUrl = createGoodsUrl(v.goods_id);
        var strHtml = '';

        strHtml += '<li>';
        strHtml += '恭喜<a class="huiyuan" href="' + userUrl + '">' + v.user_name + '</a>';
        strHtml += '参与<i>' + v.user_buy_num + '</i>人次获得<a href="' + goodsUrl + '">' + v.goods_name + '</a>';
        strHtml += '</li>';

        $('#scrollDiv').append(strHtml);
    });
}
