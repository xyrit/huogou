/**
 * Created by jun on 15/11/23.
 */
var perpage = 20;
$(function () {

    var page = $('#hidPage').val();
    var url = apiBaseUrl + '/record/buy-list';
    var data = {page:1,perpage:perpage};
    $.getJsonp(url, data, function (json) {

        $('#divGoodsLoading').hide();
        createBuylistHtml(json);

    });

    $.getJsonp(apiBaseUrl+'/record/buy-tips',{},function(json) {
        var tips = json.tips;
        var buylistTotal = parseInt(tips[0]) + parseInt(tips[1]) + parseInt(tips[2]);
        $('#navBox a:eq(0)').html('全部<em>' + buylistTotal + '</em>');
        $('#navBox a:eq(1)').html('进行中<em>' + parseInt(tips[0]) + '</em>');
        $('#navBox a:eq(2)').html('已揭晓<em>' + parseInt(tips[2]) + '</em>');
    });

    $('#navBox a').on('click', function () {
        $('#navBox a').removeClass('hover');
        $(this).addClass('hover');


        $('#divBuyList').html('');
        $('#divGoodsLoading').show();
        
        var state = $(this).attr('state');
        var url = apiBaseUrl + '/record/buy-list';
        var data = {status:state,page:1,perpage:perpage};
        $.getJsonp(url, data, function (json) {

            createBuylistHtml(json);
            $('#divGoodsLoading').hide();
            $('#hidState').val(state);
        });
    });


    var stopLoadPage = false;
    var isLoading = false;
    $.onSrollBottom(function() {
        if (stopLoadPage || isLoading) {
            return;
        }

        isLoading = true;
        $('#divGoodsLoading').show();

        var pageVal = $('#hidPage').val();
        pageVal = parseInt(pageVal) + 1;
        var state = $('#hidState').val();
        var url = apiBaseUrl + '/record/buy-list';
        var data = {status:state,page:pageVal,perpage:perpage};
        $.getJsonp(url, data, function (json) {
            var t = function() {
                createBuylistHtml(json,true);
                $('#divGoodsLoading').hide();
                if (json.list.length==0) {
                    stopLoadPage = true;
                } else {
                    isLoading = false;
                    $('#hidPage').val(pageVal);
                }
            }
            setTimeout(t,1000);
        });
    });

    $('#divBuyList').on('click','ul',function() {
        var periodId = $(this).attr('periodid');
        location.href = 'goodsbuydetail-'+periodId+'.html';
    });

});


function createBuylistHtml(json, append) {
    var html = '';
    $.each(json.list, function (i, v) {
        var goodsName = v.goods_name;
        var picture = createGoodsImgUrl(v.goods_picture, photoSize[1], photoSize[1]);
        var periodId = v.period_id;
        var peirodUrl = createPeriodUrl(periodId);
        var periodNumber = v.period_number;
        var price = v.price;
        var status = v.status;
        var buyNum = v.user_buy_num;

        var statusText = '';
        if (status == 0) {
            statusText = '进行中';
        } else if (status == 1) {
            statusText = '揭晓中';
        } else if (status == 2) {
            statusText = '已揭晓';
        }

        html += '<ul periodid="'+periodId+'">';
        html += '<li>';
        html += '<cite>';
        html += '<img src="' + picture + '" />';
        html += '<i>' + statusText + '</i>';
        html += '</cite>';
        html += '<dl>';
        html += '<dt>';
        html += '<a href="'+peirodUrl+'">' + goodsName + '</a>';
        html += '</dt>';

        if (status == 0) {

            var codeSales = v.code_sales;
            var codeQuantity = v.code_quantity;
            var leftNum = v.left_num;
            var progress = parseFloat(codeSales/codeQuantity) * 100;

            html += '<dd>';
            html += '已参与';
            html += '<em class="orange">'+buyNum+'</em>人次';
            html += '</dd>';
            html += '<dd>';
            html += '<div class="gRate short">';
            html += '<div class="Progress-bar">';
            html += '<p class="u-progress"><span style="width:'+progress+'%;" class="pgbar"><span class="pging"></span></span></p>';
            html += '<ul class="Pro-bar-li">';
            html += '<li class="P-bar01"><em>'+codeSales+'</em>已参与</li>';
            html += '<li class="P-bar02"><em>'+codeQuantity+'</em>总需人次</li>';
            html += '<li class="P-bar03"><em>'+leftNum+'</em>剩余</li>';
            html += '</ul>';
            html += '</div>';
            html += '</div>';
            html += '</dd>';

        } else if (status == 1) {
            html += '<dd>';
            html += '已参与：';
            html += '<em class="orange">'+buyNum+'</em>人次';
            html += '</dd>';
            html += '<dd>';
            html += '<a href="'+peirodUrl+'"><span class="z-announced-btn">正在揭晓...</span></a>';
            html += '</dd>';
        } else if (status == 2) {
            var userName = v.user_name;
            var userHomeId = v.user_home_id;
            var raffTime = v.raff_time;
            var userCenterUrl = createUserCenterUrl(userHomeId);
            html += '<dd>';
            html += '已参与：';
            html += '<em class="orange">'+buyNum+'</em>人次';
            html += '</dd>';
            html += '<dd>';
            html += '获得者：';
            html += '<a href="'+userCenterUrl+'" class="blue">'+userName+'</a>';
            html += '</dd>';
            html += '<dd>';
            html += '揭晓时间：';
            html += '<em>'+raffTime+'</em>';
            html += '</dd>';
        }

        html += '</dl>';
        html += '</li>';
        html += '</ul>';
    });

    if (append) {
        $('#divBuyList').append(html);
    } else {
        $('#divBuyList').html(html);
    }

}