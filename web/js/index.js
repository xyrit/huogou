var lastPublishTime = 0;
$(function(){
    sidebarCart(true);
    //首页轮播图
    $.getContent( apiBaseUrl + '/banner/banner-list?type=0&num=5','','indexBannerList');

    $.getContent( apiBaseUrl + '/period/publish-goods?catId=0&isRevealed=all&page=1&perpage=4','','publishGoodsList');

    //最新揭晓商品
    setInterval(function () {
        $.getContent( apiBaseUrl + '/period/get-start-raffle-list',{"time":lastPublishTime},'startRaffleList');
    }, 3000);

    //即将揭晓商品
    $.getContent( apiBaseUrl + '/product/public-goods?page=1&orderFlag=10&perpage=25','','publicGoodsList');

    //网站最新购买记录
    $.getContent( apiBaseUrl + '/product/new-buy-list',{'num':20},'newBuyList');

    //热门推荐商品
    $.getContent( apiBaseUrl + '/product/recommend-product?page=1&orderFlag=60&perpage=9', '', 'recommendProductList');

    //晒单分享-左边
    $.getContent( apiBaseUrl + '/share/topic-list?page=1&orderFlag=30&perpage=5', '', 'leftShareTopicList');

    //晒单分享-右边
    $.getContent( apiBaseUrl + '/share/topic-list?page=1&orderFlag=10&perpage=3', '', 'rightShareTopicList');

    //最新公告
    $.getContent( apiBaseUrl + '/group/new-topic-list?groupId=1&perpage=3', '', 'groupNewTopicList');


    $("#quickSearch a").click(function(){
        window.location.href=wwwBaseUrl+"/search.html?q="+encodeURIComponent($(this).text());
    })
});

//最新伙购达人
$(document).ready(function(){
    var timer = null;
    timer = setInterval(autoScroll,3000);
    $('#myeredar').mouseenter(function(){
        clearInterval(timer);

    }).mouseleave(function(){
        clearInterval(timer);
        timer = setInterval(autoScroll,3000);
    })

});

function autoScroll(){
    $('#myeredar').find('ul').animate({
        marginTop : "-88px"
    },800,function(){
        $(this).css({marginTop : "0px"}).find("li:lt(4)").appendTo(this);
    })
}
//最新伙购达人



//首页轮播图
function success_indexBannerList(json){

    $.each(json,function(i,v){
        var pic = v.pictrue;
        var html = '';
        html = "<li style='display: list-item;width: "+ v.width +";height: "+ v.height +"'>";
        //html += "<li style='display: list-item;background:"+ v.picture +" width: "+ v.width +";height:"+ v.height+'>";
        html += ' <a href="'+ v.src+'" target="_blank">';
        //html += '<img src="'+ v.picture+'" alt="'+ v.name +'" width="'+ v.width +'" height="'+ v.height +'">';
        html += '</a>';
        html += '</li>';
        $("#banner_main").append(html);
        $('#banner_main li:eq('+i+')').css({'backgroundImage': 'url('+v.picture+')'});
    })


    var iNow = 0,
        aMain = $('#banner_main').find('li'),
        num = aMain.size(),
        timer = null;

    var indicatorStr = '';
    for (var i = 0; i < num; i++) {
        indicatorStr += "<li></li>"
    };
    $('#indicator').append(indicatorStr);
    var aIndicator = $('#indicator').find('li');

    aIndicator.hover(function(){
        iNow = $(this).index();
        change();
        clearInterval(timer);
    },function(){
        if (num > 1) autoPlay();
    })

    function change(){
        aIndicator.eq(iNow).addClass('act').siblings('li').removeClass('act');
        aMain.eq(iNow).stop().fadeIn().siblings('li').stop().fadeOut();
    }

    function autoPlay(){
        timer = setInterval(function(){
            iNow++;
            if (iNow > num - 1) {
                iNow = 0;
            }
            change();
        },6000)
    }

    change();
    if (num > 1) autoPlay();
}

//最新揭晓商品
function success_publishGoodsList(json){
    if (json.list.length>0) {
        $.each(json.list, function(i,v){
            var item = '';
            var linkUrl = createPeriodUrl(v.period_id);
            var picUrl = createGoodsImgUrl(v.goods_picture, photoSize[1], photoSize[1]);
            var userUrl = createUserCenterUrl(v.user_home_id);
            lastPublishTime = lastPublishTime > v.end_time ? lastPublishTime : v.end_time;
            if(v.user_addr == undefined){
                var addr = '（暂无）';
            }else{
                var addr = v.user_addr;
            }

            item += '<li period-id="'+v.period_id+'" id="publish-goods-'+v.period_id+'">';
            if(v.left_time>0 || typeof v.user_home_id == 'undefined'){
                item += '<b class="publish_ico">正在<br>揭晓</b>';
            }else{
                item += '<b class="publish_ico publish_ico_h">已经<br>揭晓</b>';
            }

            item += '<article>';
            item += '<h3 title="'+ v.goods_name +'"><a href="'+ linkUrl +'" target="_blank">'+ v.goods_name +'</a></h3>';
            item += '<aside>总需: '+ parseInt(v.price) +'人次</aside>';
            if(v.left_time>0 || typeof v.user_home_id == 'undefined') {
                item += '<p>揭晓倒计时:</p>';
                item += '<span class="left-time" left-time="' + v.left_time + '" col="0" lxfday="yes"></span>';
            }else{
                item += '<summary>';
                item += '<p><a href="'+userUrl+'" target="_blank"><i class="blue">'+ v.user_name +'</i></a> 获得该商品</p>';
                item += '<p>来自：<i class="grey">'+ addr +'</i></p>';
                item += '<p>幸运伙购码：<i class="orange">'+ v.lucky_code +'</i></p>';
                item += '<p>本期参与：<i class="orange">'+ v.user_buy_num +'人次</i></p>';
                item += '<p>揭晓时间：<i class="grey">'+ v.raff_time +'</i></p>';
                item += '</summary>'
            }
            item += '</article>';
            item += '<a href="'+ linkUrl +'" target="_blank"><picture><img src="'+ picUrl +'" alt=""></picture></a>';
            item += '</li>';
            $('.publish_list').append(item);
            if (v.left_time) {
                leftTime(new Date().getTime(),$('.publish_list li:eq('+i+') .left-time'),completeLeftTime);
            }
        });
    }
}

function success_startRaffleList(json) {
    if (json.list.length>0) {
        lastPublishTime = json.maxSeconds;
        $.each(json.list, function(i,v){
            var item = '';
            var linkUrl = createPeriodUrl(v.period_id);
            var picUrl = createGoodsImgUrl(v.goods_picture, photoSize[1], photoSize[1]);

            item += '<li period-id="'+v.period_id+'" id="publish-goods-'+v.period_id+'">';
            item += '<b class="publish_ico">正在<br>揭晓</b>';
            item += '<article>';
            item += '<h3 title="'+ v.goods_name +'"><a href="'+ linkUrl +'" target="_blank">'+ v.goods_name +'</a></h3>';
            item += '<aside>总需: '+ parseInt(v.price) +'人次</aside>';
            item += '<p>揭晓倒计时:</p>';
            item += '<span class="left-time" left-time="' + v.left_time + '" col="0" lxfday="yes"></span>';
            item += '</article>';
            item += '<a href="'+ linkUrl +'" target="_blank"><picture><img src="'+ picUrl +'" alt=""></picture></a>';
            item += '</li>';
            var l = $(".publish_list li").length;
            if (l>=4) {
                $('.publish_list li:last').remove();
            }
            $('.publish_list').prepend(item);
            leftTime(new Date().getTime(),$('.publish_list li:first .left-time'),completeLeftTime);
        });
    }
}

function completeLeftTime(obj) {

    var periodId = obj.parent().parent().attr('period-id');
    obj.prev().text('已满员:');
    obj.before('<summary class="sum_dotting">正在计算中<i class="dotting">...</i></summary>');
    obj.remove();
    var ms = 3000;
    var periodInfoTimes = 0;
    var s = setInterval(function() {
        $.getJsonp(apiBaseUrl+'/period/info',{'id':periodId}, function(json) {
            if (typeof json.periodInfo.user_name != 'undefined') {
                completeLotteryInfo(json)
                clearInterval(s);
                ms = 1000;
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
    var userPic = createUserFaceImgUrl(userAvatar,avatarSize[0]);
    var userUrl = createUserCenterUrl(userHomeId);
    var needPeople = parseInt(price);

    var html = '';
    if(userAddr == undefined){
        var addr = '（暂无）';
    }else{
        var addr = userAddr;
    }
    var html = '';
    html += '<b class="publish_ico publish_ico_h">已经<br>揭晓</b>';
    html += '<article>';
    html += '<h3 title="'+ goodsName +'"><a href="'+ lotterUrl +'" target="_blank">'+ goodsName +'</a></h3>';
    html += '<aside>总需: '+ needPeople +'人次</aside>';

    html += '<summary>';
    html += '<p><a href="'+userUrl+'" target="_blank"><i class="blue">'+ userName +'</i></a> 获得该商品</p>';
    html += '<p>来自：<i class="grey">'+ addr +'</i></p>';
    html += '<p>幸运伙购码：<i class="orange">'+ luckyCode +'</i></p>';
    html += '<p>本期参与：<i class="orange">'+ userBuyNum +'人次</i></p>';
    html += '<p>揭晓时间：<i class="grey">'+ raffTime +'</i></p>';
    html += '</summary>';

    html += '</article>';
    html += '<a href="'+ lotterUrl +'" target="_blank"><picture><img src="'+ goodsImgUrl +'" alt=""></picture></a>';

    var objLi = $('#publish-goods-'+periodInfo.period_id);
    objLi.html(html);
}

//最新公告
function success_groupNewTopicList(json){
    var html = '';

    $.each(json, function(i,v){
        var linkUrl = groupBaseUrl + '/topic-' + v.id + '.html';

        html += '<a href="'+ linkUrl +'" target="_blank">'+ v.subject +'</a>'
    })

    $('.notice article').append(html);
}

//网站最新购买记录
function success_newBuyList(json){
    var html = '';
     $.each(json,function(i,v){
         var linkUrl = createGoodsUrl(v.product_id);
         var userFace = createUserFaceImgUrl(v.avatar, 80);
         var userUrl = createUserCenterUrl(v.home_id);
         html += '<li>';
         html += '<picture><i><a href="'+userUrl+'" target="_blank"><img src="'+ userFace +'"> </a></i></picture>';
         html += '<article>';
         html += '<h3><a href="'+ userUrl +'" target="_blank">'+ v.username +'</a></h3>';
         html += '<a href="'+ linkUrl +'" target="_blank" title="'+ v.name +'"><p>'+ v.name +'</p></a>';
         html += '<aside>'+ v.created_at +'</aside>';
         html += '</article>';
         html += '</li>';

     });
    $('.eredar_list').html(html);
 }

//即将揭晓
function success_publicGoodsList(json){
    var html = '';

    $.each(json,function(i,v){
        var picUrl = createGoodsImgUrl(v.picture, photoSize[1], photoSize[1]);
        var goodsUrl = createGoodsUrl( v.id);
        var num = (v.sales_num / v.price )* 100;
        var progress = num.toFixed(2);
        var periodId = v.period_id;
        var limitNum = v.limit_num;
        var buyUnit = v.buy_unit;

        html += '<dd periodid="'+ periodId +'" buyUnit="'+buyUnit+'">';
        html += '<div class="p_listDiv">';
        html += '<picture><a href="'+ goodsUrl +'" target="_blank"><img src="'+goodsLoadingImg+'" data-original="'+ picUrl +'"></a> </picture>';
        html += '<div>';
        html += '<h3><a href="'+ goodsUrl +'" target="_blank" title="'+ v.name +'">'+ v.name +'</a> </h3>';
       // html += '<aside>总需：'+ v.price +'人次 </aside>';
        html += '<p><i style="width:'+ progress +'%"></i></p>';
        html += '<summary>';
        html += '<span class="fl"><i>'+ v.sales_num +'</i><br>已参与</span>';
        html += '<span class="rl"><i>'+ v.left_num +'</i><br>剩余</span>';
        html += '</summary>';
        html += '</div>';
        html += '<article><a class="buy" href="'+ goodsUrl +'" target="_blank">立即伙购</a><a class="car" href="javascript:;"></a></article>';
        html += '</div>';
        if (limitNum>0) {
            html += '<div class="f-callout"><span class="xgou">限购</span></div>';
        } else if (buyUnit==10) {
            html += '<div class="f-callout"><span class="sbei">十元</span></div>';
        }
        html += '</dd>';
    })
    $(".announce_list").html(html);

    $('.announce_list img').lazyload({ threshold : 200 });

    $('.announce_list .car').on('click', function(){
        productImg = $(this).parents('dd').find('img').attr('src');
        addProduct(productImg, $(this));
        var periodId = $(this).parents('dd').attr('periodid');
        var buyUnit = $(this).parents('dd').attr('buyUnit');
        var cartdata = {'periodid':periodId,'num':1*buyUnit};
        $.getContent(apiBaseUrl+'/cart/add',cartdata,'cartadd');
    });
}

//最热推荐
function success_recommendProductList(json){
    var html = '';

    $.each(json,function(i,v){
        var picUrl = createGoodsImgUrl(v.picture, photoSize[1], photoSize[1]);
        var goodsUrl = createGoodsUrl( v.id);
        var num = (v.sales_num / v.price )* 100;
        var progress = num.toFixed(2);
        var periodId = v.period_id;
        var limitNum = v.limit_num;
        var buyUnit = v.buy_unit;

        html += '<dd periodid="'+ periodId +'" buyUnit="'+buyUnit+'">';
        html += '<div class="p_listDiv">';
        html += '<picture><a href="'+ goodsUrl +'" target="_blank"><img src="'+goodsLoadingImg+'" data-original="'+ picUrl +'"></a></picture>';
        html += '<div>';
        html += '<h3><a href="'+ goodsUrl +'" target="_blank" title="'+ v.name +'">'+ v.name +'</a> </h3>';
        //html += '<aside>总需：'+ v.price +'人次 </aside>';
        html += '<p><i style="width:'+ progress +'%"></i></p>';
        html += '<summary>';
        html += '<span class="fl"><i>'+ v.sales_num +'</i><br>已参与</span>';
        html += '<span class="rl"><i>'+ v.left_num +'</i><br>剩余</span>';
        html += '</summary>';
        html += '</div>';
        html += '<article><a class="buy" href="'+ goodsUrl +'" target="_blank">立即伙购</a><a class="car" href="javascript:;"></a></article>';
        html += '</div>';
        if (limitNum>0) {
            html += '<div class="f-callout"><span class="xgou">限购</span></div>';
        } else if (buyUnit==10) {
            html += '<div class="f-callout"><span class="sbei">十元</span></div>';
        }
        html += '</dd>';
    });
    $(".recommend_list").append(html);

    $('.recommend_list img').lazyload({ threshold : 200 });

    $('.recommend_list .car').on('click', function(){
        productImg = $(this).parents('dd').find('img').attr('src');
        addProduct(productImg, $(this));
        var periodId = $(this).parents('dd').attr('periodid');
        var buyUnit = $(this).parents('dd').attr('buyUnit');
        var cartdata = {'periodid':periodId,'num':1*buyUnit};
        $.getContent(apiBaseUrl+'/cart/add',cartdata,'cartadd');
    });
}

//晒单分享-左边
function success_leftShareTopicList(json){
    var html = '';
    var other = '';

    $.each(json.list,function(i,v){
        var shareUrl = shareBaseUrl + '/detail-'+ v.id +'.html';
        var recommendImgUrl = createShareImgUrl(v.recommend_image, 'recommend');
        var userFace = createUserFaceImgUrl(v.user_avatar, avatarSize[1]);

        if(i <= 4){
            html += '<li>';
            html += '<a href="'+ shareUrl +'" target="_blank">';
            html += '<img class="share_pic" src="'+ recommendImgUrl +'" alt="">';
            html += '<div class="share_fixed" data-id="'+ v.id +'">';
            html += '<article><h3>'+ v.title +'</h3><p>'+ v.content +'</p></article>';
            html += '<summary><picture><img src="'+ userFace +'" alt=""></picture><p>'+ v.user_name +'</p></summary>';
            html += '</div>';
            html += '</a>';
            html += '</li>';

        }else{
            other += '<li>';
            other += '<a href="'+shareBaseUrl+'" target="_blank">';
            other += '<picture><img src="'+ recommendImgUrl +'" alt=""></picture>';
            other += '<article>';
            other += '<h3>'+ v.title +'</h3>';
            other += '<p>'+ v.content +'</p>';
            other += '<summary><div><span><b></b><img src="'+ userFace +'" alt=""></span><i>'+ v.user_name +'</i></div><aside>'+ v.created_at +'</aside></summary>';
            other += '</article>';
            other += '</a>';
            other += '</li>';
        }

    })
    $(".share_con").append(html);
    $(".share_rl").append(other);

    var iNowB = 0,
        aMainB = $('.share_con').find('li'),
        numB = aMainB.size(),
        timerB = null;

    var indicatorStrB = '';
    for (var i = 0; i < numB; i++) {
        indicatorStrB += "<li><i></i></li>"
    };
    $('.share_cut').append(indicatorStrB);
    var aIndicatorB = $('.share_cut').find('li');

    aIndicatorB.hover(function(){
        iNowB = $(this).index();
        changeB();
        clearInterval(timerB);
    },function(){
        if (numB > 1) autoPlayB();
    })

    function changeB(){
        aIndicatorB.eq(iNowB).addClass('act').siblings('li').removeClass('act');
        aMainB.eq(iNowB).stop().fadeIn().siblings('li').stop().fadeOut();
    }

    function autoPlayB(){
        timerB = setInterval(function(){
            iNowB++;
            if (iNowB > numB - 1) {
                iNowB = 0;
            }
            changeB();
        },3000)
    }

    changeB();
    if (numB > 1) autoPlayB();
}

//晒单分享-右边
function success_rightShareTopicList(json){
    var html = '';
    var other = '';

    $.each(json.list,function(i,v){
        var shareUrl = shareBaseUrl + '/detail-'+ v.id +'.html';
        var rollImgUrl = createShareImgUrl(v.roll_image, 'roll');
        var userFace = createUserFaceImgUrl(v.user_avatar, avatarSize[1]);

        html += '<li>';
        html += '<a href="'+shareUrl+'" target="_blank">';
        html += '<picture><img src="'+ rollImgUrl +'" alt=""></picture>';
        html += '<article>';
        html += '<h3>'+ v.title +'</h3>';
        html += '<p>'+ v.content +'</p>';
        html += '<summary><div><span><b></b><img src="'+ userFace +'" alt=""></span><i>'+ v.user_name +'</i></div><aside>'+ v.created_at +'</aside></summary>';
        html += '</article>';
        html += '</a>';
        html += '</li>';
    })
    $(".share_rl").append(html);
}


$(document).ready(function(){

    $('#sort_list_dev').css({'display':'block','opacity':1});

    $('.sort').find('dl').each(function(index){
        if ((index + 1) % 2 == 0){
            $(this).css('backgroundColor','#f3f3f3');
        }
    })
    $('.recommend_list dt').find('a').each(function(index){
        if ((index + 1) % 2 == 0){
            $(this).css('float','right');
        }
    })

    var liLen = $('.recommend_list').find('dd').size();
    var aRow = Math.ceil(liLen / 5);
    $('.recommend_list').find('dd').each(function(index){
        if ((index+1) > (aRow - 1) * 4){
            $(this).css('borderTop',0);
        }
    })
    $('.recommend_list dd:last-of-type,.recommend_list dd:nth(3)').each(function(index){
        $(this).css('width',242);
    })

    $('.sort').find('div').css('display','block');

    //鼠标移上出现红色外边框
    function pHover(par){
        $(par).on('mouseover', 'dd', function(){
            $(this).addClass('p_hover').siblings().removeClass('p_hover');
        })
        $(par).on('mouseout', 'dd', function(){
            $(this).removeClass('p_hover');
        })
    }
    pHover(".recommend_list");
    pHover(".announce_list");

})


