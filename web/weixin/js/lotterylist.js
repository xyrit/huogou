/**
 * Created by jun on 15/11/23.
 */
var perpage = 20;
var maxSeconds;
$(function() {
    var cid = getCid();
    var page = getPage();

    $.getJsonp(apiBaseUrl+'/product/catlist','',function(json) {
        createCateListHtml(json);
        sortListSelect();

    });

    var url = apiBaseUrl+'/period/list';
    var data = {'cid':cid, 'page':page, 'perpage': perpage};
    $.getJsonp(url,data,function(json) {
        $('#divLoading').hide();
        createLotteryListHtml(json)
    });

    if (cid==0) {
        $.getJsonp(apiBaseUrl+'/period/get-start-raffle-list',{},startRaffleList);
        setInterval(function () {
            $.getJsonp(apiBaseUrl+'/period/get-start-raffle-list',{'time':maxSeconds},startRaffleList);
        }, 3000);
    }

    var stopLoadPage = false;
    var isLoading = false;
    $.onSrollBottom(function() {
        if (stopLoadPage || isLoading) {
            return;
        }
        isLoading = true;
        $('#divLoading').show();
        var cid = getCid();
        var pageVal = getPage();
        pageVal = parseInt(pageVal) + 1;
        var url = apiBaseUrl+'/period/list';
        var data = {cid:cid,page:pageVal,perpage:perpage};
        $.getJsonp(url, data, function (json) {
            var t = function() {
                createLotteryListHtml(json,true);
                $('#divLoading').hide();
                if (json.list.length==0) {
                    stopLoadPage = true;
                } else {
                    setPage(pageVal);
                    isLoading = false;
                }
            }
            setTimeout(t,1000);
        });
    });


    $('#divLottery').on('click','ul',function() {
       var periodId = $(this).attr('periodid');
        var periodUrl = createPeriodUrl(periodId);
        window.location.href = periodUrl;
    });
});

function createCateListHtml(json) {
    var html = '';
    var catName = '全部分类';
    var catId = 0;
    var list = json.list;
    html += '<a href="javascript:;" cid="'+catId+'">'+catName+'</a>';
    $.each(list,function(i,v){
        var catId = v.id;
        var catName = v.name;
        html += '<a href="javascript:;" cid="'+catId+'">'+catName+'</a>';
    });
    $('.announced-sort').append(html);
}

function sortListSelect() {
    $('#div_sort').on('click',function() {
        $('.announced-sort').toggle();
    });
    $('.announced-sort a').on('click',function() {
        $('.announced-sort a').removeClass('orange');
        $(this).addClass('orange');

        var catText = $(this).text();
        $('#div_sort span').html('<a href="javascript:;" class="z-set fr"></a> <span>'+catText+'</span> <cite style="display: none;"><em></em></cite>');

        $('.announced-sort').hide();
        var cid = $(this).attr('cid');
        setCid(cid);

        var url = apiBaseUrl+'/period/list';
        var data = {'cid':getCid(), 'page':getPage(), 'perpage': perpage};
        $.getJsonp(url,data,function(json) {
            createLotteryListHtml(json);
        });
    });

}


function createLotteryListHtml(json,append) {
    var html = '';
    $.each(json.list, function(i,v) {
        var goodsName = v.goods_name;
        var goodsPic = v.goods_picture;
        var periodNumber = v.period_number;
        var price = v.price;
        var periodId = v.period_id;
        var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
        var lotterUrl = createPeriodUrl(periodId);

        var leftTimeVar = v.left_time;

        if (leftTimeVar>0) {
            html += '<ul class="rNow" periodId="'+periodId+'">';
           // html += '<li class="revConL"><a href="'+lotterUrl+'"><img alt="" src="'+goodsImgUrl+'" /></a><cite><em>第'+periodNumber+'期</em><i></i></cite></li>';
            html += '<li class="revConL"><a href="'+lotterUrl+'"><img alt="" src="'+goodsImgUrl+'" /></a></li>';
            html += '<li class="revConR"><h4>'+goodsName+'</h4><h5>价值：￥'+price+'</h5>';
            html += '<p class="pTime" left-time="'+leftTimeVar+'"><s></s>揭晓倒计时 <strong><em>00</em> : <em>00</em> : <em><i>0</i><i>0</i></em></strong></p>';
            html += '<b class="fr z-arrow"></b>';
            html += '</li>';
            html += '<div class="rNowTitle">';
            html += '正在揭晓';
            html += '</div>';
            html += '</ul>';
        } else {
            var luckyCode = v.lucky_code;
            var raffTime = v.raff_time;
            var userName = v.user_name;
            var userHomeId = v.user_home_id;
            var userAvatar = v.user_avatar;
            var userBuyNum = v.user_buy_num;
            var userAddr = v.user_addr;
            var userPic = createUserFaceImgUrl(userAvatar,avatarSize[1]);
            var userCenterUrl = createUserCenterUrl(userHomeId);
            var shareId = v.share_id;
            //var shareUrl = createShareDetailUrl(shareId);

            html += '<ul class="" periodId="'+periodId+'">';
            html += '<li class="revConL"><a href="'+lotterUrl+'"><img alt="" src="'+goodsImgUrl+'"></a></li>';
            html += '<li class="revConR">';
            html += '<dl>';
            html += '<dt>获得者：<a href="'+userCenterUrl+'" class="blue">'+userName+'</a></dt>';
            html += '<dd>商品价值：'+price+'</dd><dd>本期参与：<em class="orange">'+userBuyNum+'</em>人次</dd>';
            html += '<dd class="jx_time">揭晓时间：'+raffTime+'</dd></dl>';
            html += '<b class="fr z-arrow"></b>';
            html += '</li>';
            html += '</ul>';
        }
    });
    if (append) {
        $('#divLottery').append(html);
    } else {
        $('#divLottery').html(html);
    }
    $('.pTime').each(function() {
        leftTime(new Date().getTime(),$(this),completeLeftTime);
    });
}

function completeLeftTime(obj) {
    obj.html('正在计算，请稍候...');

    var periodId = obj.parent().parent().attr('periodId');
    var ms = 3000;
    var periodInfoTimes = 0;
    var s = setInterval(function() {
        $.getJsonp(apiBaseUrl+'/period/info',{'id':periodId}, function(json) {
            if (typeof json.periodInfo.user_name != 'undefined') {
                completeLotteryInfo(json)
                clearInterval(s);
                ms = 500;
            }
            if (periodInfoTimes>3) {
                clearInterval(s);
                return;
            }
            periodInfoTimes += 1;
        });

    } ,ms);
}

function completeLotteryInfo(json) {
    var periodInfo = json.periodInfo;
    var goodsName = periodInfo.goods_name;
    var goodsPic = periodInfo.goods_picture;
    var periodNumber = periodInfo.period_number;
    var price = periodInfo.price;
    var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
    var lotterUrl = createPeriodUrl(periodInfo.period_id);

    var luckyCode = periodInfo.lucky_code;
    var raffTime = periodInfo.raff_time2;
    var userName = periodInfo.user_name;
    var userHomeId = periodInfo.user_home_id;
    var userAvatar = periodInfo.user_avatar;
    var userBuyNum = periodInfo.user_buy_num;
    var userAddr = periodInfo.user_addr;
    var userPic = createUserFaceImgUrl(userAvatar,avatarSize[1]);
    var userCenterUrl = createUserCenterUrl(userHomeId);

    var html = '';
    html += '<li class="revConL"><a href="'+lotterUrl+'"><img alt="" src="'+goodsImgUrl+'"></a></li>';
    html += '<li class="revConR">';
    html += '<dl>';
    html += '<dt>获得者：<a href="'+userCenterUrl+'" class="blue">'+userName+'</a></dt>';
    html += '<dd>商品价值：'+price+'</dd><dd>本期参与：<em class="orange">'+userBuyNum+'</em>人次</dd>';
    html += '<dd class="jx_time">揭晓时间：'+raffTime+'</dd></dl>';
    html += '</li>';

    var objUl = $('#divLottery ul[periodId='+periodInfo.period_id+']');
    objUl.removeClass('rNow');
    objUl.html(html);
}

function startRaffleList(json) {
    var list = json.list;
    maxSeconds = json.maxSeconds;

    if (list.length>0) {
        $.each(list, function(i,v) {
            var goodsName = v.goods_name;
            var goodsPic = v.goods_picture;
            var periodNumber = v.period_number;
            var periodId = v.period_id;
            var price = v.price;
            var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
            var lotterUrl = createPeriodUrl(periodId);

            var leftTimeVar = v.left_time;
            var html = '';

            html += '<ul class="rNow" periodId="'+periodId+'">';
            html += '<li class="revConL"><a href="'+lotterUrl+'"><img alt="" src="'+goodsImgUrl+'" /></a></li>';
            html += '<li class="revConR"><h4>'+goodsName+'</h4><h5>价值：￥'+price+'</h5>';
            html += '<p class="pTime" left-time="'+leftTimeVar+'"><s></s>揭晓倒计时 <strong><em>00</em> : <em>00</em> : <em><i>0</i><i>0</i></em></strong></p>';
            html += '</li>';
            html += '<div class="rNowTitle">';
            html += '正在揭晓';
            html += '</div>';
            html += '</ul>';


            $('#divLottery').prepend(html);
            $('#divLottery ul').length >40 ? $('#divLottery ul:last').remove() : '';
            leftTime(new Date().getTime(),$('#divLottery ul:first .pTime'), completeLeftTime);
        });


    }
}


function getCid() {
    return $('#hidCateId').val();
}

function setCid(cid) {
    $('#hidCateId').val(cid);
}

function getPage() {
    return $('#hidPage').val();
}

function setPage(page) {
    $('#hidPage').val(page);
}