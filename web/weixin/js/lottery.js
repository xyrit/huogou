/**
 * Created by jun on 15/11/19.
 */

var periodId = getPeriodIdByUrl(window.location.href);
$(function() {


    $.getJsonp(apiBaseUrl+'/period/info', {id:periodId}, function(json) {
        var periodInfo = json.periodInfo;
        var periodLeftTime = periodInfo.left_time;
        var productId = periodInfo.goods_id;
        if (periodLeftTime>0) {
            $('#divLotteryTime').show();
            $('#divPic').show();
            $('.pro_info').show();
            $('.ann_btn:eq(1)').show();
            createLotterProInfo(json);
            $.getJsonp(apiBaseUrl+'/product/images',{id:productId},function(images) {
                createPhotos(images);
                $("#sliderBox").picslider();
            });
            $('#divLotteryTime').attr('left-time', periodLeftTime)
            leftTime(new Date().getTime(), $('#divLotteryTime'), function(obj) {
                window.location.reload();
            });

            var calresultUrl = wwwBaseUrl+'/app/compute.html?showBar=1&pid='+periodId;
            $('.ann_btn:eq(1) a:eq(0)').attr('href', calresultUrl);
            $('.ann_btn:eq(1) a:eq(1)').attr('href', weixinBaseUrl+'/buyrecords-'+periodId+'.html');
            $('.ann_btn:eq(1) a:eq(2)').attr('href', weixinBaseUrl+'/goodsimgdesc-'+productId+'.html');
            $('.ann_btn:eq(1) a:eq(3)').attr('href', weixinBaseUrl+'/goodspost-'+productId+'.html');

        } else {
            $('.ann_detail').show();
            $('.ann_btn:eq(0)').show();
            createLotteryDetailInfo(json);

            var buyrecordsUrl = weixinBaseUrl+'/buyrecords-'+periodId+'.html';
            var calresultUrl = wwwBaseUrl+'/app/compute.html?pid='+periodId;
            var goodspostUrl = weixinBaseUrl+'/goodspost-'+productId+'.html';
            var moreperiod = weixinBaseUrl+'/moreperiod-'+productId+'.html';

            $('.ann_btn:eq(0) a:eq(0)').attr('href', calresultUrl);
            $('.ann_btn:eq(0) a:eq(1)').attr('href', buyrecordsUrl);
            $('.ann_btn:eq(0) a:eq(2)').attr('href', goodspostUrl);
            $('.ann_btn:eq(0) a:eq(3)').attr('href', moreperiod);
        }





        var thsPeriodNumber = $('#periodNumber').val();
        $.getJsonp(apiBaseUrl+'/product/periodlist', {id:periodId,perpage:3,offset:parseInt(thsPeriodNumber)+1,type:'period'}, function(data) {
            createPeriodList(data);
        });

         //加关注
        isFollowed($('#a_sc'),json.userInfo.followed);
        $('#a_sc').click(function() {
            var url = $(this).attr('data-src');
            $.getJsonp(url,{pid:productId},function(json) {
                successFollow(json);
            });
        });

        //当前进行中
        if (periodInfo.current_number) {
            $(".pro_foot").show();
            $(".conductBtn").html('<a href="/product/'+productId+'.html">最新一期正在进行中…</a>');
        }

    });





});

function createLotterProInfo(json) {

    var periodInfo = json.periodInfo;
    var goodsName = periodInfo.goods_name;
    var productId = periodInfo.goods_id;
    var brief = periodInfo.goods_brief;
    var periodId = periodInfo.period_id;
   // var periodNumber = periodInfo.period_number;
    //var curPeriodNumber = periodInfo.current_number;
    var periodNumber = periodInfo.period_no;
    var curPeriodNumber = periodInfo.current_no;
    var price = periodInfo.price;
    var limitNum = periodInfo.limit_num;
    var period_no = periodInfo.period_no;
    var html = '';
    if (limitNum>0) {
        html += '<h2 class="gray6"> <span class="purchase-icon">限购</span>'+goodsName+'<span>'+brief+'</span></h2>';
    } else {
        html += '<h2 class="gray6">'+goodsName+'<span>'+brief+'</span></h2>';
    }
    html += '<cite class="gray9"> 价值：￥'+price+' </cite>';
    $('.pro_info').html(html);
    $('#periodNumber').val(period_no);
    $('#curPeriodNumber').val(curPeriodNumber);
    $('#productIdVal').val(productId);
}

function createLotteryDetailInfo(json) {

    var periodInfo = json.periodInfo;
    var goodsName = periodInfo.goods_name;
    var brief = periodInfo.goods_brief;
    var periodNumber = periodInfo.period_number;
    var period_no    =periodInfo.period_no
   // var curPeriodNumber = periodInfo.current_number;
    var curPeriodNumber = periodInfo.current_no;
    var productId = periodInfo.goods_id;
    var raffTime = periodInfo.raff_time;
    var luckyCode = periodInfo.lucky_code
    var userBuyTime = periodInfo.user_buy_time;
    var userBuyNum = periodInfo.user_buy_num;
    var userName = periodInfo.user_name;
    var userAddr = periodInfo.user_addr;
    var userHomeId = periodInfo.user_home_id;
    var userCenterUrl = createUserCenterUrl(userHomeId);
    var userAvatar = createUserFaceImgUrl(periodInfo.user_avatar, avatarSize[2], avatarSize[2]);
    var html = '';
    html += '<h3 class="gray6"> '+goodsName+' </h3>';
    html += '<ul>';
    html += '<li>';
    html += '<span class="fl">';
    html += '<a href="'+userCenterUrl+'"> <img src="'+userAvatar+'" /></a>';
    html += '</span>';
    html += '<div class="ann_info"><p>获得者：<a href="'+userCenterUrl+'" class="blue">'+userName+'</a></p><em>'+userAddr+'</em> 本期参与<b class="orange">'+userBuyNum+'</b>人次  <a class="orange" href="/lottery/BuyDetail-'+periodInfo.period_id+'.html">点击查看</a></div>';
    html += '</li>';
    html += '</ul>';
    html += '<dl>';
    html += '<dt class="gray6">幸运伙购码：<b class="orange">'+luckyCode+'</b></dt>';
    html += '<dd class="gray9">揭晓时间：'+raffTime+'</dd> <dd class="gray9"> 伙购时间：'+userBuyTime+' </dd> ';
    html += '</dl>';

    $('.ann_detail').html(html);
    $('#periodNumber').val(period_no);
    $('#curPeriodNumber').val(curPeriodNumber);
    $('#productIdVal').val(productId);
}

function createPeriodList(json) {

    var thsPeriodNumber = $('#periodNumber').val();
    var curPeriodNumber = $('#curPeriodNumber').val();
    var productId = $('#productIdVal').val();
    var productUrl = createGoodsUrl(productId);

    if (json.totalCount >=3) {
        var morePeriodUrl = weixinBaseUrl+'/moreperiod-'+productId+'.html';
        var moreBtnItem = '<li><a href="'+morePeriodUrl+'"><s class="fl"></s>更多 <em>&gt;&gt;</em> </a> </li>';
        $('#morePeriod').prepend(moreBtnItem);
    }
    var html = '';

    if (parseInt(curPeriodNumber)==parseInt(thsPeriodNumber)+1) {
        html += '<li><a href="'+productUrl+'" ><s class="fl"></s>'+thsPeriodNumber+'期<i class="fr"></i> </a> </li>';
    }

    $.each(json.list, function(i,v) {
        var periodId = v.id;
        var periodNumber = v.period_number;
        var period_no = v.period_no;

        if (parseInt(curPeriodNumber)==parseInt(thsPeriodNumber)+1 && i>=2) {
            return;
        }

        var periodUrl = createPeriodUrl(periodId);
        if (thsPeriodNumber==periodNumber) {
            html += '<li><a href="'+periodUrl+'" class="hover"><s class="fl"></s>'+period_no+'期<i class="fr"></i> </a> </li>';
        } else {
            html += '<li><a href="'+periodUrl+'" ><s class="fl"></s>'+period_no+'期<i class="fr"></i> </a> </li>';
        }
    });
    $('#morePeriod').prepend(html);


}
