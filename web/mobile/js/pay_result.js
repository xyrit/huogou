/**
 * Created by jun on 15/11/28.
 */
var t;
$(function() {
    var r = $('#hidROrderId').val();
    if (r.length>0) {
        t = setInterval(function() {
            $.getJsonp(apiBaseUrl+'/recharge/result',{'o':r},function(json) {
                if (typeof json.url !='undefined') {
                    clearInterval(t);
                    if (json.type==1) {
                        location.href = json.url;
                        return;
                    } else if(json.type==2) {
                        t = setInterval(function() {
                            $.getJsonp(apiBaseUrl+'/pay/result',{'o':json.order},function(json) {
                                showPayResult(json)
                            });
                        },1000);
                        $.getJsonp(apiBaseUrl+'/pay/pay-order',{'o':json.order},function(json) {

                        });
                    }
                }

            });
        },1000);
    } else {
        var orderId = $('#hidOrderId').val();
        t = setInterval(function() {
            $.getJsonp(apiBaseUrl+'/pay/result',{'o':orderId},function(json) {
                showPayResult(json)
            });
        },1000);
        $.getJsonp(apiBaseUrl+'/pay/pay-order',{'o':orderId},function(json) {

        });
    }


});

function showPayResult(json) {
    if (json.code==100) {
        $('#paySuccess').show();
        $('#failPayFT').show();
        $('#payShare').show();
        $('#payFailed').hide();
        $('#payLoading').hide();
        $('#divDownApp').show();
        clearInterval(t);
        showGoodsListHtml(json);
    } else if (json.code==0) {


    } else {

        clearInterval(t);
        $('#paySuccess').hide();
        $('#failPayFT').hide();
        $('#payShare').hide();
        $('#payFailed').show();
        $('#payLoading').hide();
        $('#divDownApp').show();
    }
}

function showGoodsListHtml(json) {
    var html = '';
    var sucNum = json.success.length;
    $.each(json.success,function(i,v) {
        var goodsName = v.name;
        var goodsImgUrl = createGoodsImgUrl(v.picture,photoSize[1],photoSize[1]);
        var periodNumber = v.period_number;
        var periodId = v.period_id;
        var periodUrl = createPeriodUrl(periodId);
        var buyNum = v.nums;
        var postNum = v.post_nums;
        html += '<li onclick="location.href=\''+periodUrl+'\'">';
        html += '<span class="g-pic">';
        html += '<img src="'+goodsImgUrl+'" alt="" width="35" height="35" />';
        html += '</span>';
        html += '<p>'+goodsName+'</p>';
        html += '<p>';
        html += '<span>';
        html += '<em class="orange">'+buyNum+'</em>人次';
        html += '</span>支付成功';
        html += '</p>';
        html += '</li>';
    });
    if (sucNum>0) {
        $('#successPay .goods-list').html(html);
        $('#successPay h6').html('以下<span>'+sucNum+'</span>件商品支付成功');
        $('#successPay').show();
    }

    html = '';
    var someNum = json.some.length;
    $.each(json.some,function(i,v) {
        var goodsName = v.name;
        var goodsImgUrl = createGoodsImgUrl(v.picture,photoSize[1],photoSize[1]);
        var periodNumber = v.period_number;
        var periodId = v.period_id;
        var periodUrl = createPeriodUrl(periodId);
        var buyNum = v.nums;
        var postNum = v.post_nums;
        html += '<li onclick="location.href=\''+periodUrl+'\'">';
        html += '<span class="g-pic">';
        html += '<img src="'+goodsImgUrl+'" alt="" width="35" height="35" />';
        html += '</span>';
        html += '<p>'+goodsName+'</p>';
        html += '<p>';
        html += '<span>';
        html += '<em class="orange">'+buyNum+'</em>人次';
        html += '</span>成功支付￥'+buyNum+'.00';
        html += '</p>';
        html += '</li>';
    });
    if (someNum>0) {
        $('#somePay .goods-list').html(html);
        $('#somePay h6').html('以下<span>'+someNum+'</span>件商品部分支付成功');
        $('#somePay').show();
    }
    if (sucNum+someNum>0) {
        $('.z-pay-tips em').text(sucNum+someNum);
    } else {
        $('#payFailed').show();
        $('#paySuccess').hide();
        $('#payShare').hide();
        return;
    }

    html = '';
    var failNum = json.fail.length;
    $.each(json.fail,function(i,v) {
        var goodsName = v.name;
        var goodsImgUrl = createGoodsImgUrl(v.picture,photoSize[1],photoSize[1]);
        var periodNumber = v.period_number;
        var periodId = v.period_id;
        var periodUrl = createPeriodUrl(periodId);
        var buyNum = v.nums;
        var postNum = v.post_nums;
        html += '<li onclick="location.href=\''+periodUrl+'\'">';
        html += '<span class="g-pic">';
        html += '<img src="'+goodsImgUrl+'" alt="" width="35" height="35" />';
        html += '</span>';
        html += '<p>'+goodsName+'</p>';
        html += '<p>';
        html += '<span>';
        html += '<em class="orange">'+buyNum+'</em>人次';
        html += '</span>支付失败';
        html += '</p>';
        html += '</li>';
    });
    if (failNum>0) {
        $('#failPay .goods-list').html(html);
        $('#failPay h6').html('以下<span>'+failNum+'</span>件商品支付失败');
        $('#failPay').show();
    }

    $('#addGroup').attr('ios-url','mqqapi://card/show_pslcard?src_type=internal&version=1&uin=364218153&key=74ad2311a16fb0b24383eb61d9c51cc3421b88e5e7674e0960a6c6954ac82eb2&card_type=group&source=external');
    $('#addGroup').attr('android-url','mqqopensdkapi://bizAgent/qm/qr?url=http%3A%2F%2Fqm.qq.com%2Fcgi-bin%2Fqm%2Fqr%3Ffrom%3Dapp%26p%3Dandroid%26k%3D-GeBEdoGts8tZYG2R9YuiN9Jku9i1rjy');
    openApp($('#addGroup'));

}

