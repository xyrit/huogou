/**
 * Created by jun on 15/12/2.
 */
$(function() {


    $('#btnSubmit').click(function() {
        var payPoints = 0;
        var payMoney = 0;
        var payBank = parseInt($('#hidRechargeMoney').val());
        var payWay = $('#hidRechargeWay').val();
        var payName = 'platform';
        var data = {payType:'recharge',payName:payName,payBank:payWay,integral:payPoints,payMoney:payBank,userSource:'2'};
        $.post(weixinBaseUrl+'/payapi/create-order',data,function(json) {

            if(json.code==100) {
                var orderId = json.order;
                if (payWay=='zhifukachat') {
                    window.location.href = '/cart/weixinpay.html?o='+orderId;
                } else if (payWay=='chinabank') {
                    window.location.href = '/cart/chinabankpay.html?o='+orderId;
                } else if (payWay=='iapp') {
                    window.location.href = wwwBaseUrl+'/iapppay.html?o='+orderId;
                } else if (payWay == 'jd') {
                    window.location.href = wwwBaseUrl + '/jdpay.html?o=' + orderId;
                } else if (payWay == 'union') {
                    window.location.href = wwwBaseUrl + '/unionpay.html?o=' + orderId;
                }
                return;
            } else {
                if (typeof json.message != 'undefined') {
                    $.PageDialog.fail(json.message);
                }
            }
        });
    });


    $('#ulOption li').click(function(){
        var rechargeMoney = $(this).attr('money');
        $('#ulOption li').children('a').removeClass('z-sel');
        $(this).children('a').addClass('z-sel');
        $('#ulBankList').find('.orange').html(rechargeMoney+".00");
        $(this).siblings('li').children('b').removeClass('z-initsel');
        $('#hidRechargeMoney').val(rechargeMoney);
    });

    //输入金额
    $('.z-init').keyup(function(){
        var rechargeMoney = $(this).val();
        rechargeMoney = parseInt(rechargeMoney);
        if (isNaN(rechargeMoney)) {
            rechargeMoney = 0;
            $(this).val('');
        }else {
            $(this).val(rechargeMoney);
        }
        $('#ulOption li:eq(5)').attr('money',rechargeMoney);
        $('#hidRechargeMoney').val(rechargeMoney);
        $('#rechargeMoney').text(rechargeMoney+".00");
    });

    //选择支付方式
    $('#ulBankList li.gray9').click(function() {
        $('#ulBankList li').removeClass('checked');
        $(this).addClass('checked');
        var rechargeWay = $(this).attr('bank');
        $('#hidRechargeWay').val(rechargeWay);
    });

});
