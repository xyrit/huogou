/**
 * Created by jun on 15/11/19.
 */

function createPhotos(photolist) {
    $.each(photolist, function(i,v) {
        var item = '';
        var goodsPic = createGoodsImgUrl(v, photoSize[1],photoSize[1]);
        item += '<li style="width: 210px; float: left; display: block;"><img src="'+goodsPic+'" /></li>';
        $('.slides').append(item);
        $('#PicPostion').append('<dd></dd>');
    });

}

function getPeriodIdByUrl(url) {
    var periodId = '';
    var s = 'lottery/([0-9]+)\.html';
    var reg = new RegExp(s);
    var r = url.match(reg);
    if (r != null) {
        periodId = r[1];
    }
    return periodId;
}

function getProductIdByUrl(url) {
    var productId = '';
    var s = 'product/([0-9]+)\.html';
    var reg = new RegExp(s);
    var r = url.match(reg);
    if (r != null) {
        productId = r[1];
    }
    return productId;
}

function isFollowed(obj, followed){
    if (followed) {
        obj.attr('data-src',apiBaseUrl+'/follow/cancel').addClass('z-foot-fansed');
    }else{
        obj.attr('data-src',apiBaseUrl+'/follow/follow').addClass('z-foot-fans');
    }
}

function successFollow(json) {
    if (json.code == 1) {
        if (json.f == 'follow') {
            $.PageDialog.ok('关注成功');
            $('#a_sc').attr("data-src",apiBaseUrl+'/follow/cancel').removeClass('z-foot-fans').addClass('z-foot-fansed');
        }else{
            $.PageDialog.ok('取消关注成功');
            $('#a_sc').attr("data-src",apiBaseUrl+'/follow/follow').removeClass('z-foot-fansed').addClass('z-foot-fans');
        }
    }else{
        if (json.logined == 0) {
            location.href = weixinBaseUrl+'/passport/login.html?forward='+encodeURIComponent(location.href);
        }
    }
}


