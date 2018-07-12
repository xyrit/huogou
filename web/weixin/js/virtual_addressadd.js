/**
 * Created by jun on 15/12/10.
 */
$(function() {
    $('body').css({'backgroundColor': '#F4F4F4'});


    $('#btnSure').on('click', function() {
        var account = $('#txtAccount').val();
        var type = $('#selType').val();
        var contact = $('#txtContact').val();
        if (account.length==0) {
            $.PageDialog.fail('请输入充值账号');
            return;
        } else {
            if(!checkPhone(account)) {
                $.PageDialog.fail('请输入正确的充值账号');
                return;
            }
        }
        var data = {addressId:0,account:account,type:type,contact:contact};
        $.getJsonp(apiBaseUrl+'/info/add-virtual-address',data,function(json) {
            if(json.code==100) {
                $.PageDialog.ok(json.msg,function() {
                    window.history.back();
                });
            } else {
                $.PageDialog.fail(json.msg);
                return;
            }
        });
    });


});