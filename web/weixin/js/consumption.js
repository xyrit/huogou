/**
 * Created by jun on 15/11/23.
 */
var perpage = 10;
$(function() {
    //    消费明细
    $('#btnConsumption').click(function(){
        if($('#btnConsumption').hasClass('z-MoneyNav-crt01') == false){
            $('#ulConsumption').show();
            $('#ulRechage').hide();
            $(this).addClass('z-MoneyNav-crt01');
            $('#btnRecharge').removeClass('z-MoneyNav-crt02');
        }
    })
    //充值明细
    $('#btnRecharge').click(function(){
        if($('#btnRecharge').hasClass('z-MoneyNav-crt02') == false){
            $('#ulRechage').show();
            $('#ulConsumption').hide();
            $(this).addClass('z-MoneyNav-crt02');
            $('#btnConsumption').removeClass('z-MoneyNav-crt01');
        }
    });

    var url = apiBaseUrl+'/recharge/money-log';
    var data = {type:0,page:1,perpage:perpage};
    $.getJsonp(url,data,function(json) {
        createRechargeListHtml(json);
        var totalRechargeMoney = json.totalMoney;
        $('#btnRecharge').html('<b>充值明细</b>总金额：'+totalRechargeMoney+' 伙购币');
    });

    var url = apiBaseUrl+'/recharge/money-log';
    var data = {type:1,page:1,perpage:perpage};
    $.getJsonp(url,data,function(json) {

        $('#divLoad').hide();
        createPayrecordListHtml(json);
        var totalPayMony = json.totalMoney;
        $('#btnConsumption').html('<b>消费明细</b>总金额：'+totalPayMony+' 伙购币');
    });
});

function createPayrecordListHtml(json) {
    var html = '';
    $.each(json.list, function(i,v) {
        var payTime = v.pay_time;
        var payPrice = parseInt(v.money);

        html += '<li class=""><span>'+payTime+'</span><span>'+payPrice+' 伙购币</span></li>';
    });

    $('#ulConsumption').append(html);
}

function createRechargeListHtml(json) {
    var html = '';
    $.each(json.list, function(i,v) {
        var payTime = v.pay_time;
        var payPrice = parseInt(v.money);
        var payMent = v.payment;

        html += '<li class=""><span>'+payTime+'</span><span>'+payPrice+' 伙购币</span><span>'+payMent+'</span></li>';
    });

    $('#ulRechage').append(html);
}
