/**
 * Created by jun on 15/10/14.
 */
var page = 1;
var perpage = 20;
var limit = 1;
$(function() {
    var data = {'page':page, 'perpage': perpage, 'limit':limit};
    $.getContent(apiBaseUrl+'/product/list',data,'productList');
});

function success_productList(json) {
    console.log(json);
    $.each(json.list,function(i,v) {
        var goodsName = v.name;
        var goodsPic = v.picture;
        var goodsId = v.id;
        var price = v.price;
        var periodId = v.period_id;
        var periodNumber = v.period_number;
        var limitBuyNum = v.limit_num;
        var salesNum = v.sales_num;
        var quantity = v.price;
        var leftNum = quantity - salesNum;

        var goodsUrl = createGoodsUrl(goodsId);
        var goodsImgUrl = createGoodsImgUrl(goodsPic,photoSize[1],photoSize[1]);

        var progressWidth = Math.ceil(216 * (salesNum/quantity));

        var html = '';
        html += '<div class="productsCon" codeid="'+periodId+'" limitbuy="'+limitBuyNum+'" productid="'+goodsId+'">';
        html += '<div class="proList ">';
        html += '<ul>';
        html += '<li class="list-pic"><a href="'+goodsUrl+'" target="_blank" title="(第'+periodNumber+'云)&nbsp;'+goodsName+'"> <img name="goodsImg" src="'+goodsImgUrl+'" /></a> </li>';
        html += '<li class="list-name"><a href="'+goodsUrl+'" target="_blank" title="(第'+periodNumber+'云)&nbsp;'+goodsName+'">(第'+periodNumber+'云)&nbsp;'+goodsName+'</a> </li>';
        html += '<li class="list-value gray6">价值：￥'+price+'.00<span><i></i>限购<b>'+limitBuyNum+'</b>人次</span></li>';
        html += '<li class="g-progress">';
        html += '<dl class="m-progress"><dt><b style="width:'+progressWidth+'px;"></b> </dt> <dd> <span class="orange fl"><em>'+salesNum+'</em>已参与</span> <span class="fl gray6"><em>'+quantity+'</em>总需人次</span> <span class="blue fr"><em>'+leftNum+'</em>剩余</span> </dd></dl>';
        html += '</li>';
        html += '<li name="buyBox" class="list-btn" limitbuy="'+limitBuyNum+'"><a href="javascript:;" title="立即伙购" class="u-imm">立即伙购</a> <a href="javascript:;" class="u-carts" title="加入到购物车"></a> </li>';
        html += '</ul>';
        html += '</div>';
        html += '</div>';
        $('#ulLimitGoodsList').append(html);
    });
    $('.u-carts').on('click', function () {
        var productId = $(this).parents('.productsCon').attr('productid');
        var cartdata = {'productid':productId,'num':1};
        $.getContent(apiBaseUrl+'/cart/add',cartdata,'cartadd');
    });
    $('.u-imm').on('click', function () {
        var productId = $(this).parents('.productsCon').attr('productid');
        window.location.href = createGoodsUrl(productId);
    });

}

function success_cartadd(json){
    alert('已添加到购物车');
}