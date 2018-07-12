/**
 * Created by jun on 15/11/19.
 */
var cartPids = '';
var cartIsbuy = '';
$(function() {
    $('#a_payment').click(function(){
        cartPids = cartPids.substring(0,cartPids.length-1);
        cartIsbuy = cartIsbuy.substring(0,cartIsbuy.length-1);
        var cartData = {"product":cartPids,"status":cartIsbuy};
        $.getJsonp(apiBaseUrl+'/cart/check',cartData,function(json) {
            if (json.code == 100) {
                if (json.invalid == 1) {
                    var notice = "部分商品已失效，继续伙购？";
                    $.PageDialog.confirm(notice,function() {
                        location.href = '/cart/payment.html';
                    });
                }else{
                    location.href = '/cart/payment.html';
                }
            }else{
                if (json.logined == 0) {
                    location.href = '/passport/login.html?forward='+encodeURIComponent(weixinBaseUrl+'/cart.html');
                }
            }
        });
        return;
    });

    $('#cartBody').on('click', '.z-del', function() {
        var ths = $(this);
        var t = function() {
            var cid = ths.attr('cid');
            $.getJsonp(apiBaseUrl+'/cart/del', {cid:cid}, function(data) {
                $.getJsonp(apiBaseUrl+'/cart/list', {}, function(json) {
                    createCartListHtml(json);
                    getCartListInfo(json);
                });
            });
        }
        $.PageDialog.confirm('您确认要删除吗？', t);

    });

    $.getJsonp(apiBaseUrl+'/cart/list', {'check':'1'}, function(json) {
        createCartListHtml(json);
        renderCartBtnInfo(json);
    });

    $('#cartBody').on('input', 'input[name="pnum"]',function() {
        changeOneCartNum($(this));
    });

    $('#cartBody').on('blur', 'input[name="pnum"]',function() {
        var number = parseInt($(this).val());
        if (isNaN(number)) {
            number = 1;
            $(this).val(number);
        }
        var productId = $(this).attr('pid');
        $(this).val(number);
        setTotalPaymentMoney();
        changeCartNum(productId,number,function(json) {

        });
    });

    $('#cartBody').on('click','.pro_less',function() {
        var obj = $(this).next('input');
        var number = obj.val();
        var buyUnit = obj.attr('buyUnit');
        obj.val(parseInt(number)-1*buyUnit);
        changeOneCartNum(obj);
    });

    $('#cartBody').on('click','.pro_add',function() {
        var obj = $(this).prev('input');
        var number = obj.val();
        var buyUnit = obj.attr('buyUnit');
        obj.val(parseInt(number)+1*buyUnit);
        changeOneCartNum(obj);
    });

});

function setTotalPaymentMoney() {
    var total = 0;
    $('#cartBody input[name="pnum"]').each(function(i,v) {
        var num = parseInt($(this).val());
        total += num;
    });
    $('#cartpayInfo .orange:eq(1)').html(total+'.00');
}

function changeOneCartNum(obj) {
    var number = parseInt(obj.val());
    if (isNaN(number)) {
        return;
    }
    var productId = obj.attr('pid');
    var limitNum = parseInt(obj.attr('limitnum'));
    var mylimitNum = parseInt(obj.attr('mylimitnum'));
    var leftNum = parseInt(obj.attr('leftnum'));
    var finalNum = number;
    if (limitNum>0) {
        finalNum = finalNum>limitNum ? limitNum : number;
    }
    finalNum = finalNum>leftNum ? leftNum : finalNum;
    if (mylimitNum>0) {
        finalNum = finalNum>mylimitNum ? mylimitNum : finalNum;
    }
    if(finalNum<=0) {
        finalNum = 1;
    }
    obj.val(finalNum);
    setTotalPaymentMoney();
    changeCartNum(productId,finalNum,function(json) {

    });
}

function createCartListHtml(json) {
    var list = json.list;
    if (list.length<=0) {
        $('#divNone').show();
    } else {
        $('#mycartpay').show();
    }
    var totalMoney = 0;
    $('#cartBody').html('');
    $.each(list, function(i,v) {
        var item = '';
        var cartId = v.id;
        var goodsName = v.name;
        var periodNumber = v.period_number;
        var oldPeriodId = v.old_period_id;
        var periodId = v.period_id;
        var productId = v.product_id;
        var goodsImgUrl = createGoodsImgUrl(v.picture, photoSize[1], photoSize[1]);
        var goodsUrl = createGoodsUrl(productId);
        var limitNum = v.limit_num;
        var myLimitNum = v.my_limit_num;
        var buyUnit = v.buy_unit;
        var leftNum = v.left_num;
        var buyNum = parseInt(v.nums);
        totalMoney += parseInt(buyNum);

        cartPids += productId+',';
        cartIsbuy += '1,';

        item += '<li>';
        if (limitNum>0) {
            item += '<a class="fl u-Cart-img" href="'+goodsUrl+'"><img src="'+goodsImgUrl+'" border="0" alt="" /> <div class="pTitle pPurchase" style="">限购</div></a>';
        } else if (buyUnit==10) {
            item += '<a class="fl u-Cart-img" href="'+goodsUrl+'"><img src="'+goodsImgUrl+'" border="0" alt="" /> <div class="pTitle sbei" style="">十元</div></a>';
        } else {
            item += '<a class="fl u-Cart-img" href="'+goodsUrl+'"><img src="'+goodsImgUrl+'" border="0" alt="" /> </a>';
        }
        item += '<div class="u-Cart-r">';
        if (oldPeriodId!=0 && oldPeriodId!=periodId) {
            item += '<a href="'+goodsUrl+'" class="gray6"><strong>已更新至第最新</strong>'+goodsName+'</a>';
        } else {
            item += '<a href="'+goodsUrl+'" class="gray6">'+goodsName+'</a>';
        }
        if (limitNum>0) {
            item += '<span class="gray9"> <em> 剩余'+leftNum+'人次</em> <em >/</em> <em class="gray9" style="margin:0;">限购'+limitNum+'人次</em> </span>';
        } else {
            item += '<span class="gray9"> <em> 剩余'+leftNum+'人次</em> <em style="display:none;">/</em> <em class="gray9" style="margin:0;"></em> </span>';
        }
        item += '<div class="count" id="buynum'+i+'">';
        if (limitNum>0 && myLimitNum==0) {
            item += '<span>已满限购次数</span>';
        } else {
            item += '<a href="javascript:;" class="pro_less mius" >-</a>';
            item += '<input pid="'+productId+'" name="pnum" maxlength="7" leftnum="'+leftNum+'" limitnum="'+limitNum+'" mylimitnum="'+myLimitNum+'" buyUnit="'+buyUnit+'" type="text" class="gray6 num" value="'+buyNum+'" min="0" />';
            item += '<a href="javascript:;" class="pro_add add" >+</a>';
        }
        item += '</div>';
        item += '<a href="javascript:;" name="delLink" cid="'+cartId+'" class="z-del"><s></s></a>';
        item += '</div>';
        item += '</li>';
        $('#cartBody').append(item);
    });
    var productNum = list.length;
    if (productNum>0) {
        $('#cartpayInfo').html('<span>共<em class="orange">'+productNum+'</em>个商品</span>合计<em class="orange">'+totalMoney+'.00</em>伙购币');
    }else{
        $('#cartpayInfo').html('');
    }
}
