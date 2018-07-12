/**
 * Created by jun on 15/12/16.
 */
$(function() {

    $('#ul_smallmoney li').click(function() {
        $('#ul_smallmoney li').removeClass('hover');
        $(this).addClass('hover');
        var money = $(this).attr('money');
        $('#hidSmallMoney').val(money);
    });

    $('#txtCustomMoney').on('input',function() {
        var money = $(this).val();
        money = parseInt(money);
        if (isNaN(money)) {
            money = 1;
        }
        $(this).val(money);
        $('#hidSmallMoney').val(money);
    });

    var oldMoney = $('#hidOldMoney').val();
    if (oldMoney>0) {
        $('#ul_smallmoney li[money='+oldMoney+']').addClass('hover');
    }
    $('#btnSubmit').click(function() {

        var smallMoney = $('#hidSmallMoney').val();

        if (smallMoney.length==0) {
            $.PageDialog.fail('请选择免密支付额度');
            return;
        }
        if (smallMoney<=0) {
            $.PageDialog.fail('免密支付额度必须为正整数');
            return;
        }
        var url = '';
        if (oldMoney>0) {
            url = apiBaseUrl+'/info/update-small-pay';
        } else if (oldMoney==0) {
            url = apiBaseUrl+'/info/open-small-pay';
        }
        $.getJsonp(url,{smallpay:smallMoney},function(json) {
            if (json.code==100) {
                $.PageDialog.ok(json.msg,function() {
                    window.location.href = weixinBaseUrl+'/member/safesetting.html';
                });
            } else {
                $.PageDialog.fail(json.msg);
            }
        });
    });

});
