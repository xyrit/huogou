/**
 * Created by jun on 15/11/26.
 */
$(function() {

    var orderId = $('#hidOrderId').val();
    $.getJsonp(apiBaseUrl + '/order/info', {'id': orderId}, function (json) {
        var data = json.data;
        selectOrderStatus(data.status);
        if (data.status<4) {

        } else if (data.status==4) {
            $('#btnShiped').removeClass('z-grayBtn');
        } else if (data.status==5) {
            if (data.allow_share==0) {
                $('#btnShiped').html('订单已完成');
            } else if(data.allow_share==1) {
                $('#btnShiped').html('晒单');
                $('#btnShiped').removeClass('z-grayBtn');
                $('#btnShiped').addClass('btnShare');
            }
        } else if (data.status==8) {
            $('#btnShiped').html('订单已完成');
        }
        createLogsHtml(data.orderLogs);
        createGoodsHtml(data.periodInfo);
        var delivery_id = data.delivery_id;
        if (delivery_id==2) {
            createVirtualAddressHtml(data.addressInfo);
        } else {
            createAddressHtml(data.shipInfo);
        }
    });

    $('#btnShiped').on('click',function() {
        var ths = $(this);
        if (ths.hasClass('z-grayBtn')) {
            return false;
        }
        if (ths.hasClass('btnShare')) {
            window.location.href = weixinBaseUrl+'/member/postone-'+orderId+'.html';
            return false;
        }
        $.PageDialog.confirm('您确定收到货了吗？',function() {

            $.getJsonp(apiBaseUrl+'/record/submit-goods',{id:orderId},function(json) {
                if (json.code==100) {
                    $.PageDialog.ok('提交成功',function() {
                        window.location.reload();
                    });
                } else if (json.msg.length>0) {
                    $.PageDialog.fail(json.msg,function() {
                       window.location.reload();
                    });
                } else {
                    $.PageDialog.fail('提交失败',function() {
                        window.location.reload();
                    });
                }
            });

        });
        return;
    });
});

function selectOrderStatus(status) {
    if (status>=1 && status<=3) {
        $('.m-adrs-lst:eq(1)').addClass('m-adrs-lstSel');
    } else if(status==4) {
        $('.m-adrs-lst:eq(2)').addClass('m-adrs-lstSel');
    } else if(status>=5) {
        $('.m-adrs-lst:eq(3)').addClass('m-adrs-lstSel');
    }
}

function createLogsHtml(orderLogs) {
    var html = '';
    $.each(orderLogs,function(i,v) {
        var actTime = v.time;
        var actContent = v.content;
        var actName = v.name;
        html += '<li>';
        html += '<p class="gray9">';
        html += '<span class="fr">操作：'+actName+'</span> '+actTime+'';
        html += '</p> '+actContent;
        html += '</li>';
    });

    $('.m-address-ct ul').html(html);

}

function createGoodsHtml(periodInfo) {
    var goodsName = periodInfo.goods_name;
    var goodsImgUrl = createGoodsImgUrl(periodInfo.goods_picture, photoSize[1], photoSize[1]);
    var periodNumber = periodInfo.period_number;
    var periodUrl = createPeriodUrl(periodInfo.period_id);
    var luckyCode = periodInfo.lucky_code;
    var raffTime = periodInfo.raff_time;
    var price = periodInfo.price;

    var html = '<li>';
    html += '<a class="fl z-Limg" href="'+periodUrl+'">';
    html += '<img src="'+goodsImgUrl+'" />';
    html += '</a>';
    html += '<div class="u-gds-r gray9">';
    html += '<p class="z-gds-tt"><a href="'+periodUrl+'" class="gray6">'+goodsName+'</a></p>';
    html += '<p>价值：￥'+price+'</p>';
    html += '<p>幸运夺宝码：<em class="orange">'+luckyCode+'</em></p>';
    html += '<p>揭晓时间：'+raffTime+'</p>';
    html += '</div>';
    html += '</li>';

    $('#loadingPicBlock').html(html);
}

function createAddressHtml(shipInfo) {
    var area = shipInfo.area;
    var addr = shipInfo.addr;
    var username = shipInfo.username;
    var tel = shipInfo.tel;
    var html = '';
    html += '<span>'+area+' '+addr+'</span>';
    html += '<span>'+username+' '+tel+'</span>';

    $('.f-addressLH').html(html);

}

function getVirtualTypeName(addressType) {
    addressTypeName = '';
    if (addressType==1) {
        addressTypeName = '话费充值';
    }
    return addressTypeName;
}

function createVirtualAddressHtml(addressInfo) {
    var addressTypeName = getVirtualTypeName(addressInfo.type);
    var account = addressInfo.account;
    var html = '';
    html += '<span>'+addressTypeName+'</span>';
    html += '<span>'+account+'</span>';

    $('.f-addressLH').html(html);
}
