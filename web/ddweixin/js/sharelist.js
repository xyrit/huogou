/**
 * Created by jun on 15/11/23.
 */
$(function () {
    //分享功能
//    var t = function () {
//        var h = $("#hidInviteLink").val();
//        var d = $('.shareSpan').text();
//        wxShareFun({
//            shareTitle: "1元就能买iPhone 6S，一种很有意思的购物方式，快来看看吧！",
//            shareImg: "http://img.huogou.com/pic-58-58/20151119425868128.jpg",
//            shareLink: h,
//            shareDesc: d,
//            shareMoney: true,
//            showMask: false
//        });
//        $("#btnShare").bind("click", function (i) {
//            wxShowMaskFun(true);
//            return false
//        });
//    }
//    Base.getScript(skinBaseUrl + '/weixin/js/wxshare.js', t);
//    
     //设置页面分享
        Base.getScript(skinBaseUrl + '/weixin/js/wxshare.js', function () {
            wxShareFun({
                shareTitle: "1元就能买iPhone 6S，一种很有意思的购物方式，快来看看吧！",
                shareImg: 'http://img.huogou.com/pic-58-58/20151119425868128.jpg',
                shareLink: $("#hidInviteLink").val(),
                shareDesc: $('.shareSpan').text(),
                shareMoney: false,
                showMask: false
            });
        });

        $("#btnShare").bind("click", function (i) {
            wxShowMaskFun(true);
            return false
        });

    //佣金提现 判定 
    $("#liMention a").click(function () {
        var price = parseInt($('#price').text().substr(1));
        if (price >= 100) return;
        $.PageDialog.fail("佣金满100元才可提现");
        return false;
    })


    //一键转入夺宝账户
    $("#liRechagre").click(function () {
        var price = parseInt($('#price').text().substr(1));
        if (price < 1) return $.PageDialog.fail("佣金满1元才可转入");
        var source = 2;
        $.getJsonp(apiBaseUrl + "/invite/recharge", {price: price,source:source}, function (json) {
            $.PageDialog.ok(json.error ? "转入失败,请稍后重试!" : "已成功转入夺宝账户");
            if (json.error == 0)
                setTimeout(function () {
                    window.location.reload();
                }, 500);
        });
    })
});
