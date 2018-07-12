/**
 * Created by jun on 15/11/25.
 */
var page = 1;
var perpage = 20;
$(function() {
    var data = {page:page,perpage:perpage};
    $.getJsonp(apiBaseUrl+'/record/order-list',data,function(json) {
        $('#divLoading').hide();
        createOrderlistHtml(json);
    });

    var stopLoadPage = false;
    var isLoading = false;
    //下一页
    $.onSrollBottom(function() {
        if (stopLoadPage || isLoading) {
            return;
        }

        isLoading = true;
        $('#divLoading').show();
        page = parseInt(page);
        $.getJsonp(apiBaseUrl + '/record/order-list', {page:page+1,perpage:perpage}, function (json) {
            var t = function() {
                createOrderlistHtml(json, true);
                $('#divLoading').hide();
                if (json.list.length==0) {
                    stopLoadPage = true;
                } else {
                    page += 1;
                    isLoading = false;
                }
            }
            setTimeout(t,1000);
        });
    });

    $('#ul_list').on('click','li',function() {
        var orderid = $(this).attr('orderno');
        location.href = 'orderdetail-'+orderid+'.html';
    });


});


function createOrderlistHtml(json,append) {
    var html = '';

    $.each(json.list,function(i,v) {
        var goodsName = v.goods_name;
        var goodsImgUrl = createGoodsImgUrl(v.goods_picture,photoSize[1],photoSize[1]);
        var periodId = v.period_id;
        var periodNumber = v.period_number;
        var periodUrl = createPeriodUrl(periodId);
        var price = v.price;
        var buyNum = v.buy_num;
        var raffTime = v.raff_time;
        var orderId = v.order_id;
        var orderState = v.order_state;
        var deliveryId = v.delivery_id;
        var statusName = v.status_name;
        var cardDeleveryId = 3;
        var luckyCode = v.lucky_code;
        var allowShare = v.allow_share;

        html += '<li orderstate="'+orderState+'" orderno="'+orderId+'">';
        html += '<cite><a href="'+periodUrl+'"><img src="'+goodsImgUrl+'" /></a></cite>';
        html += '<dl>';
        html += '<dt>';
        html += '<a href="'+periodUrl+'" class="gray6">'+goodsName+'</a>';
        html += '</dt>';
        html += '<dd>';
        html += '幸运伙购码：';
        html += '<em>'+luckyCode+'</em>';
        html += '</dd>';
        html += '<dd>';
        html += '揭晓时间：';
        html += '<em>'+raffTime+'</em>';
        html += '</dd>';
        html += '<dd>';
        html += '订单编号：';
        html += '<em>'+orderId+'</em>';
        html += '</dd>';
        html += '<dd>';
        if (orderState==0 || orderState==6) {
            if (deliveryId==cardDeleveryId) {
                statusName = '选择运营商';
            } else if(deliveryId=='5' || deliveryId=='6' || deliveryId=='7' || deliveryId=='8' || deliveryId=='9'||deliveryId=='10') {
                statusName = '下载APP兑换';
            } else {
                statusName = '完善收货地址';
            }
            html += '<a href="orderdetail-'+orderId+'.html" class="orangeBtn">'+statusName+'</a>';
        } else if (orderState>=1 && orderState<=3) {
            html += '<a href="orderdetail-'+orderId+'.html" class="grayBtn">等待商家发货</a>';
        } else if (orderState==4) {
            if (deliveryId==cardDeleveryId) {
                html += '<a href="orderdetail-'+orderId+'.html" class="orangeBtn">'+statusName+'</a>';
            } else {
                html += '<a href="orderdetail-'+orderId+'.html" class="grayBtn">已发货</a>';
                html += '<a href="orderdetail-'+orderId+'.html" class="orangeBtn">确认收货</a>';
            }
        } else if (orderState==5) {
            if (deliveryId==cardDeleveryId) {
                html += '<a href="orderdetail-'+orderId+'.html" class="grayBtn">'+statusName+'</a>';
            } else {
                if (allowShare==1) {
                    html += '<a href="orderdetail-'+orderId+'.html" class="grayBtn">订单详情</a>';
                    html += '<a href="postone-'+orderId+'.html" class="orangeBtn">晒单赢福分</a>';
                }else{
                    html += '<a href="orderdetail-'+orderId+'.html" class="grayBtn">已完成</a>';
                }
            }
        } else if (orderState==8) {
            if (deliveryId==cardDeleveryId) {
                html += '<a href="orderdetail-'+orderId+'.html" class="grayBtn">'+statusName+'</a>';
            } else {
                html += '<a href="orderdetail-'+orderId+'.html" class="grayBtn">已完成</a>';
            }
        }
        html += '</dd>';
        html += '</dl>';
        html += '<a goodsname="'+goodsName+'" goodspic="'+goodsImgUrl+'" buyNum="'+buyNum+'" periodid="'+periodId+'" href="javascript:;" class="z-set-wrap">';
        html += '<div class="z-set"></div>分享';
        html += '</a>';
        html += '</li>';
    });
    if (append) {
        $('#ul_list').append(html);
        $('.g-suggest').show();
    } else {
        if (json.list.length==0) {
            $('#ul_list').html('<div class="noRecords colorbbb clearfix"><s></s>最近三个月无记录 <div class="z-use">请下载“伙购网”APP查看更多</div> </div>')
            $('.g-suggest').hide();
        } else {
            $('#ul_list').html(html);
            $('.g-suggest').show();

            var shareBtn = $('#ul_list li:eq(0) .z-set-wrap');
            var shareTitle = "看看这是真的嘛，好嗨森好激动！"+shareBtn.attr('buyNum')+"块钱居然真的可以买到！";
            var shareDesc = shareBtn.attr('goodsname');
            var shareLink = createPeriodUrl(shareBtn.attr('periodid'));
            var shareImg = createGoodsImgUrl(shareBtn.attr('goodspic'),photoSize[0],photoSize[0]);
            shareOrder(shareTitle,shareDesc,shareLink,shareImg);
        }
    }
}

function shareOrder(shareTitle,shareDesc,shareLink,shareImg) {

    //分享功能
    var t = function () {
        wxShareFun({
            shareTitle: shareTitle,
            shareImg: shareImg,
            shareLink: shareLink,
            shareDesc: shareDesc,
            shareMoney: false,
            showMask: false,
        });
    }
    Base.getScript(skinBaseUrl + '/weixin/js/wxshare.js', t);

    $('#ul_list').on('click','.z-set-wrap',function(){

        var periodid = $(this).attr('periodid');
        var periodUrl = createPeriodUrl(periodid);
        var goodsImg = $(this).attr('goodspic');
        var goodsName = $(this).attr('goodsname');
        var buyNum = $(this).attr('buyNum');
        var shareTitle = "看看这是真的嘛，好嗨森好激动！"+buyNum+"块钱居然真的可以买到！";
        wxShareFun({
            shareTitle: shareTitle,
            shareImg: goodsImg,
            shareLink: periodUrl,
            shareDesc: goodsName,
            shareMoney: false,
            showMask: true
        });
        return false
    });




}