/**
 * Created by jun on 16/1/4.
 */
var orderId;
var productId;
var typeVal='yd';
$(function() {
    orderId = $('#hidOrderId').val();
    $.getJsonp(apiBaseUrl + '/order/info', {'id': orderId}, function (json) {

        var data = json.data;
        productId = data.periodInfo.goods_id;
        selectOrderStatus(data.status);
        createGoodsHtml(data.periodInfo);
    });


});

function selectOrderStatus(status) {
    if (status==0||status==6) {
        $('.m-adrs-lst:eq(1)').addClass('m-adrs-lstSel');
        $('.mode').show();
        $('.send').hide();

        $('.mode .selectMode').on('click',function() {
            $('input[type="checkbox"]').attr('checked',false);
            $(this).find('input[type="checkbox"]').attr('checked','checked');
            typeVal = $(this).find('input[type="checkbox"]').val();
            $('.mode_input label').removeClass('checked');
            $(this).find('.mode_input label').addClass('checked');
        });

        $('#getCardBtn').on('click',function() {
            getVirtual();
        });
    } else {
        $('.m-adrs-lst:eq(2)').addClass('m-adrs-lstSel');
        $('.mode').hide();
        $('.send').show();

        $.getJsonp(apiBaseUrl+'/record/get-virtual-list', {'id': orderId}, function(json) {
            createVirtualListHtml(json);
        });
    }
}

function getVirtual(){
    var loadingHtml = '<img src="'+skinBaseUrl+'/img/daoji-load.gif" style="padding:30px 0">';
    $(".mode").html(loadingHtml).css("textAlign","center");
    // var typeVal = $('input[name="mode"]:checked').val();
    $.getJsonp(apiBaseUrl+'/record/get-virtual',{'id':orderId,'type':typeVal},function(json) {
        if (json.code==100) {
            // $.PageDialog.ok('获取卡密成功',function() {
                location.reload();
            // });
        } else {
            // $.PageDialog.fail('获取卡密失败',function() {
                // location.reload();
            // });
            var failHtml = '';
                failHtml += '<div class="fail">';
                failHtml += '<img src="'+skinBaseUrl+'/mobile/images/send01.png">';
                failHtml += '<aside>抱歉,派发失败</aside>';
                failHtml += '<p>请点击重新派发，如有疑问可以<a href="/app/contact.html">联系我们</a></p>';
                failHtml += '<a class="send_a" href="javascript:;" onclick="getVirtual()">重新派发</a>';
                failHtml += '</div>';
            $(".mode").remove();
            $(".send").html(failHtml).show();
        }
    })
}

function createVirtualListHtml(json) {
    var cardNumber = json.list.length;
    var modeName = json.type;
    var cardParVal = json.list[0].parvalue;
    var html = '';

    if(json.type == '伙购币'){
        $.each(json.list,function(i,v){
            html += '<div class="card_info">';
            html += '<p style="padding-bottom: 10px; color: #999">您已成功兑换'+v.card+'，请到伙购币明细页面查询余额。</p>';
            html += '<a href="/member/consumption.html" target="_blank" style="color: #2af;">前往查看></a>';
            html += '</div>';
        })
    }else{
        html += '<div class="card_info">';
        html += '<aside>您获得'+cardNumber+'张'+cardParVal+'元'+modeName+'话费充值卡，可充值到支付宝或话费。(长按可复制)</aside>';
        html += '<a href="/goodsimgdesc-'+productId+'.html">卡密使用说明&gt;</a>';
        html += '</div>';
        html += '<ul class="card_list">';
        $.each(json.list,function(i,v) {
            var card = v.card;
            var cardId = v.id;
            var parvalue = v.parvalue;
            var pwd = v.pwd;

            html += '<li>';
            html += '<div class="kh_num">卡号：'+card+'</div>';
            html += '<div>卡密：<input class="card_input" type="text" value="'+pwd+'" maxlength="10" readonly="readonly"><span class="card_cat" isshow="0" cardid="'+cardId+'"href="javascript:;">显示卡密</span></div>';
            html += '</li>';
        });
        html += '</ul>';

    }

    $('.send').html(html);

    $('.card_cat').on('click',function() {
        var ths = $(this);
        var cardId = ths.attr('cardid');
        var isshow = ths.attr('isshow');
        if (isshow==0) {

            $.getJsonp(apiBaseUrl+'/record/get-pwd', {'id':cardId},function(json) {
                if (json.code==100) {
                    ths.prev().val(json.result);
                    ths.attr('isshow',1);
                    ths.text('隐藏卡密');
                }
            })
        } else {
            ths.prev().val('***************');
            ths.attr('isshow',0);
            ths.text('显示卡密');
        }

    })
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
    html += '<p>幸运伙购码：<em class="orange">'+luckyCode+'</em></p>';
    html += '<p>揭晓时间：'+raffTime+'</p>';
    html += '</div>';
    html += '</li>';

    $('#loadingPicBlock').html(html);
}
