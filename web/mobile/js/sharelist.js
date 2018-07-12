/**
 * Created by jun on 15/11/23.
 */
$(function () {

    //佣金提现 判定 
    $("#liMention a").click(function () {
        var price = parseInt($('#price').text().substr(1));
        if (price >= 100) return;
        $.PageDialog.fail("佣金满100元才可提现");
        return false;
    })


    //一键转入伙购账户
    $("#liRechagre").click(function () {
        var price = parseInt($('#price').text().substr(1));
        if (price < 1) return $.PageDialog.fail("佣金满1元才可转入");
        var source = 2;
        $.getJsonp(apiBaseUrl + "/invite/recharge", {price: price,source:source}, function (json) {
            $.PageDialog.ok(json.error ? "转入失败,请稍后重试!" : "已成功转入伙购账户");
            if (json.error == 0)
                setTimeout(function () {
                    window.location.reload();
                }, 500);
        });
    })
});
