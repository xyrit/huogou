/**
 * Created by jun on 15/10/14.
 */
var cid = getCid();
var page = getPage();
var perpage = 20;
$(function() {
    var data = {'cid':cid, 'page':page, 'perpage': perpage};
    $.getContent(apiBaseUrl+'/product/catlist','','cateList');
    $.getContent(apiBaseUrl+'/period/list',data,'lotteryList');
});


function success_cateList(json) {
    var html = '';
    var catName = '全部';
    var catUrl = createPeriodListUrl(0);
    html += '<li class=""><a href="'+catUrl+'" title="'+catName+'">'+catName+'</a></li>';
    $.each(json,function(i,v){
        catName = v.name;
        catUrl = createPeriodListUrl(v.id);
        if (cid==v.id) {
            html += '<li class="current"><a href="'+catUrl+'" title="'+catName+'">'+catName+'</a></li>';
        } else {
            html += '<li class=""><a href="'+catUrl+'" title="'+catName+'">'+catName+'</a></li>';
        }
    });
    $('.m-lott-menu ul:first').append(html);
    if (cid == 0) {
        $(".m-lott-menu ul li:first").addClass('current');
    };
}


function success_lotteryList(json) {
    var list = json.list;
    var html = '';
    $.each(list, function(i,v) {
        var goodsName = v.goods_name;
        var goodsPic = v.goods_picture;
        var luckyCode = v.lucky_code;
        var periodNumber = v.period_number;
        var price = v.price;
        var raffTime = v.raff_time;
        var userName = v.user_name;
        var userHomeId = v.user_home_id;
        var userAvatar = v.user_avatar;
        var userBuyNum = v.user_buy_num;
        var userAddr = v.user_addr;
        var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
        var lotterUrl = createPeriodUrl(v.period_id);
        var userPic = createUserFaceImgUrl(userAvatar,avatarSize[0]);
        var userCenterUrl = createUserCenterUrl(userHomeId);
        html += '<div class="m-lottery-list" type="isRaff">';
        html += '<ul>';
        html += '<li class="f-lott-comm"><a href="'+lotterUrl+'" target="_blank" title="'+goodsName+'"><img src="'+goodsImgUrl+'" /></a></li>';
        html += '<li class="f-lott-detailed">';
        html += '<div class="u-user-info">';
        html += '<p class="fl"><a href="'+userCenterUrl+'" target="_blank" title="'+userName+'"><img type="userPhoto" src="'+userPic+'" /> <s></s></a></p>';
        html += '<dl class="fl">';
        html += '<dt><em>获得者：</em><span><a href="'+userCenterUrl+'" target="_blank" title="'+userName+'">'+userName+'</a></span> </dt>';
        html += '<dd class="z-lott-lz">来自：'+userAddr+'</dd><dd>幸运伙购码：<strong class="orange">'+luckyCode+'</strong></dd><dd>本云参与：<i class="orange">'+userBuyNum+'</i>人次</dd>';
        html += '</dl>';
        html += '</div>';
        html += '<div class="u-comm-info">';
        html += '<dl>';
        html += '<dt><a href="'+lotterUrl+'" target="_blank" title="(第'+periodNumber+'云)'+goodsName+'">(第'+periodNumber+'云)'+goodsName+'</a> </dt>';
        html += '<dd> 商品价值：￥'+price+' </dd>';
        html += '<dd> 揭晓时间：'+raffTime+' </dd>';
        html += '<dd class="z-lott-btn"> <span><a href="'+lotterUrl+'" target="_blank" title="查看详情">查看详情</a></span> </dd>';
        html += '</dl>';
        html += '</div>';
        html += '</li>';
        html += '</ul>';
        html += '</div>';
    });
    $('#divLottery').append(html);
    createPage(json.totalCount, json.totalPage, 5);
}

function createPage(total, totalPage, maxButtonCount) {
    if (totalPage <= 1) {
        return;
    }
    if (page<=1) {
        page = 1;
    }
    if (page>=totalPage) {
        page = totalPage;
    }
    if (page<=1) {
        var prevButton = '<span class="f-noClick"><a href="javascript:;"><i class="f-tran f-tran-prev">&lt;</i>上一页</a></span>';
    } else {
        var prevPageUrl = createPeriodListUrl(cid, page - 1);
        var prevButton = '<span><a href="'+prevPageUrl+'"><i class="f-tran f-tran-prev">&lt;</i>上一页</a></span>';
    }

    if (page>=totalPage) {
        var nextButton = '<span class="f-noClick"><a href="javascript:;" title="下一页">下一页<i class="f-tran f-tran-next">&gt;</i></a></span>';
    } else {
        var nextPageUrl = createPeriodListUrl(cid, page + 1);
        var nextButton = '<span><a href="'+nextPageUrl+'" title="下一页">下一页<i class="f-tran f-tran-next">&gt;</i></a></span>';
    }
    var totalButton = '<span class="f-mar-left">共<em>'+totalPage+'</em>页，去第</span>';
    var goButton = '<span><input type="text" value="1" maxlength="6" id="goPageNum"/>页</span>';
    var submitButton = '<span class="f-mar-left"><a id="btnGotoPage" href="javascript:;" title="确定">确定</a></span>';

    var beginPage = Math.max(1,page - parseInt(maxButtonCount/2));
    var endPage = beginPage + maxButtonCount - 1;
    if (endPage > totalPage) {
        endPage = totalPage;
        beginPage = Math.max(1,endPage - maxButtonCount + 1);
    }

    var firstPageUrl = createPeriodListUrl(cid, 1);
    var lastPageUrl = createPeriodListUrl(cid, totalPage);
    var firstButton = '';
    var lastButton = '';
    if (beginPage > 1) {
        firstButton += '<span><a href="'+firstPageUrl+'">1</a></span>';
        firstButton += '<span>...</span>';
    }
    if (endPage<totalPage) {
        lastButton += '<span>...</span>';
        lastButton += '<span><a href="'+lastPageUrl+'">'+totalPage+'</a></span>';
    }

    var buttons = '';
    for (var i=beginPage;i<=endPage;i++) {
        var lotteryListUrl = createPeriodListUrl(cid, i);
        var curClass = '';
        if (i==page) {
            curClass = 'class="current"';
        }
        buttons += '<span '+curClass+'><a href="'+lotteryListUrl+'">'+i+'</a></span>';
    }

    var pageHtml = '';
    pageHtml += prevButton + firstButton + buttons + lastButton + nextButton + totalButton + goButton + submitButton;
    $('#divPage').html(pageHtml);
    $('#goPageNum').val(page==1? totalPage : page);
    $('#btnGotoPage').on('click', function () {
        var pageNum = $('#goPageNum').val();
        window.location.href = createPeriodListUrl(cid, pageNum);
    });
}

function getPage() {
    var href = window.location.href;
    var s = 'm([0-9]+).html';
    var reg = new RegExp(s);
    var r = href.match(reg);
    if (r != null) {
        return r[1];
    }
    s = 'i([0-9]+)m([0-9]+).html';
    reg = new RegExp(s);
    r = href.match(reg);
    if (r != null) {
        return r[2];
    }
    return 1;
}

function getCid() {
    var href = window.location.href;
    var s = 'i([0-9]+)(m([0-9]+))?.html';
    var reg = new RegExp(s);
    var r = href.match(reg);
    if (r != null) {
        return r[1];
    }
    return 0;
}
