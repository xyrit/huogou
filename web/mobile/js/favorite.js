$(function() {
    //展开删除和分享
    $('.goodList').on('click', '.share', function (e) {
        stopBubble(e);
        $('.share').children('.option').hide();
        $(this).children('.option').toggle();
    })

    $("html").bind("click", function () {
        $(this).find("div.share").attr("isshow", "0").find("div.option").hide()
    });

//添加到购物车
    $('.goodList').on('click', ".car-btn", function (e) {
        stopBubble(e);
        var periodid = $(this).parents('li').attr('periodid');
        $.getJsonp(apiBaseUrl + '/cart/add', {periodid: periodid, num: 1}, function (json) {
            if (json.code == 100)
                $.PageDialog.ok('添加到购物车成功!');
            else
                $.PageDialog.fail('添加失败请稍后重试!');
        });
    });

//取消收藏
    $('.goodList').on('click', '.z-del', function (e) {
        stopBubble(e);
        var pid = $(this).parents("li").attr('product_id');
        var t = function () {
            $.getJsonp(apiBaseUrl + '/follow/cancel', {pid: pid}, function (json) {
                if (json.code == 1) {
                    $.PageDialog.ok('取消关注成功!', function () {
                        window.location.reload();
                    });
                } else {
                    $.PageDialog.fail('取消关注成功,请稍后重试!');
                }
            });
        }
        $.PageDialog.confirm('您确认要删除吗？', t);
    });


    $('.goodList').on('click', '#ul_list li', function (e) {
        var goodsId = $(this).attr('product_id');
        var url = createGoodsUrl(goodsId);
        location.href = url;
    });
});