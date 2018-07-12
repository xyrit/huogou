/**
 * Created by jun on 15/11/25.
 */
var page = 1;
var perpage = 10;
var selectedType = 'BuyRecord';
$(function () {
    var homeId = $('#hidHomeID').val();

    //点击tab栏切换对应展示域
    $('.g-snav-lst').click(function(){
        var tId = $(this).attr('id');
        $(this).addClass('mCurr').siblings('span').removeClass('mCurr');
        $('.m_lst').hide();
        $('#div'+tId).show();
        $('#div'+tId).html('');
        selectedType = tId;
        page = 1;
        $('.noRecords').hide();
        $('#divLoading').show();
        selectRecord(homeId,selectedType,page,perpage,function(json) {

            $('#divLoading').hide();
        });
    }).eq(0).trigger('click');

    var stopLoadPage = false;
    var isLoading = false;
    $.onSrollBottom(function() {
        if (stopLoadPage || isLoading) {
            return;
        }
        isLoading = true;
        $('#divLoading').show();
        pageVal = parseInt(page) + 1;

        selectRecord(homeId,selectedType,pageVal,perpage,function(json) {
            var t = function() {
                if (json.list.length==0) {
                    stopLoadPage = true;
                } else {
                    page = pageVal;
                    isLoading = false;
                }

                $('#divLoading').hide();
            }
            setTimeout(t,1000);
        })
    });

});

function selectRecord(homeId,type,page,perpage,func) {
    switch (type) {
        case 'BuyRecord' :
            $.getJsonp(apiBaseUrl + '/userpage/goodsbuylist', {home_id: homeId, page: page, perpage: perpage}, function (json) {
                createGoodsbuylistHtml(json,page);
                func(json);
            });
            break;
        case 'GetGoods' :
            $.getJsonp(apiBaseUrl+'/userpage/productlist',{home_id: homeId, page: page, perpage: perpage},function(json) {
                createProductlistHtml(json,page);
                func(json);
            });
            break;
        case 'Single' :
            $.getJsonp(apiBaseUrl+'/userpage/sharelist',{home_id: homeId, page: page, perpage: perpage},function(json) {
                createSharelistHtml(json,page);
                func(json);
            });
            break;
        default :
            $.getJsonp(apiBaseUrl + '/userpage/goodsbuylist', {home_id: homeId, page: page, perpage: perpage}, function (json) {
                createGoodsbuylistHtml(json,page);
                func(json);
            });
            break;
    }
}


function createGoodsbuylistHtml(json,toPage) {
    var html = '';
    if (json.list.length==0 && toPage==1) {
        showNorecords($('#divBuyRecord'));
        return;
    }
    if (json.limitNum>0) {
        showLimitNumDesc($('#divBuyRecord'),json.limitNum);
    }
    $.each(json.list, function (i, v) {
        var goodsName = v.goods_name;
        var goodsImgUrl = createGoodsImgUrl(v.goods_picture,photoSize[1],photoSize[1]);
        var periodNumber = v.period_number;
        var periodId = v.period_id;
        var periodUrl = createPeriodUrl(periodId);
        var price = v.code_price;
        var codeSales = v.code_sales;
        var codeQuantiity = v.code_quantity;
        var leftNum = v.left_num;
        var progress = parseFloat(codeSales/codeQuantiity)* 100;
        var status = v.status;
        var userBuyNum = v.user_buy_num;
        var userBuyTime = v.user_buy_time;

        html += '<ul onclick="location.href=\''+periodUrl+'\'">';
        html += '<li class="mBuyRecordL"><img src="'+goodsImgUrl+'" /></li>';
        html += '<li class="mBuyRecordR">'+goodsName;
        html += '<p class="mValue">价值：￥'+price+'</p>';
        if (status==0) {
            html += '<div class="pRate">';
            html += '<div class="Progress-bar">';
            html += '<p class="u-progress" title="已完成'+progress+'%"><span class="pgbar" style="width: '+progress+'%;"><span class="pging"></span></span></p>';
            html += '<ul class="Pro-bar-li"><li class="P-bar01"><em>'+codeSales+'</em>已参与</li><li class="P-bar02"><em>'+codeQuantiity+'</em>总需人次</li><li class="P-bar03"><em>'+leftNum+'</em>剩余</li></ul>';
            html += '</div>';
            html += '</div>';
        } else if(status==1) {
            leftNum = 0;
            html += '<div class="pRate">';
            html += '<div class="Progress-bar">';
            html += '<p class="u-progress" title="已完成'+progress+'%"><span class="pgbar" style="width: '+progress+'%;"><span class="pging"></span></span></p>';
            html += '<ul class="Pro-bar-li"><li class="P-bar01"><em>'+codeSales+'</em>已参与</li><li class="P-bar02"><em>'+codeQuantiity+'</em>总需人次</li><li class="P-bar03"><em>'+leftNum+'</em>剩余</li></ul>';
            html += '</div>';
            html += '</div>';
        } else if(status==2) {
            var userName = v.user_name;
            var userHomeId = v.user_home_id;
            var userCenterUrl = createUserCenterUrl(userHomeId);
            var luckyCode = v.lucky_code;
            html += '<span>获得者：';
            html += '<a style="color: #22AAff" href="'+userCenterUrl+'">'+userName+'</a>';
            html += '<br />幸运伙购码：';
            html += '<em class="orange">'+luckyCode+'</em>';
            html += '</span>';
        }

        html += '</li>';
        html += '</ul>';
    });
    $('#divBuyRecord').append(html);
}

function createProductlistHtml(json,toPage) {
    var html = '';
    if (json.list.length==0 && toPage==1) {
        showNorecords($('#divGetGoods'));
        return;
    }
    if (json.limitNum>0) {
        showLimitNumDesc($('#divGetGoods'),json.limitNum);
    }
    $.each(json.list,function(i,v) {
        var goodsName = v.goods_name;
        var goodsImgUrl = createGoodsImgUrl(v.goods_picture,photoSize[1],photoSize[1]);
        var periodId = v.period_id;
        var periodUrl = createPeriodUrl(periodId);
        var periodNumber = v.period_number;
        var luckyCode = v.lucky_code;
        var raffTime = v.end_time;
        var price = v.price;

        html += '<ul onclick="location.href=\''+periodUrl+'\'" class="BuyRecordList">';
        html += '<li class="mBuyRecordL">';
        html += '<img src="'+goodsImgUrl+'" />';
        html += '</li>';
        html += '<li class="mBuyRecordR">'+goodsName;
        html += '<p class="mValue">价值：￥'+price+'</p>';
        html += '<span>幸运伙购码：';
        html += '<em class="orange">'+luckyCode+'</em>';
        html += '<br />揭晓时间：';
        html += '<i>'+raffTime+'</i>';
        html += '</span>';
        html += '</li>';
        html += '</ul>';
    });

    $('#divGetGoods').append(html);
}

function createSharelistHtml(json,toPage) {
    var html = '';
    if (json.list.length==0 && toPage==1) {
        showNorecords($('#divSingle'));
        return;
    }
    if (json.limitNum>0) {
        showLimitNumDesc($('#divSingle'),json.limitNum);
    }
    $.each(json.list,function(i,v) {
        var id = v.id;
        var titile = v.title;
        var content = v.content;
        var created_at = v.created_at;
        var pictures = v.pictures;
        html += '<ul>';
        html += '<li>';
        html += '<a href="/post/detail-'+id+'.html">';
        html += '<h3>';
        html += '<b>'+titile+'</b>';
        html += '<em>'+created_at+'</em>';
        html += '</h3>';
        html += '<p>';
        html += content.substring(0,100) + '...';
        html += '</p>';
        html += '<dl>';
        $.each(pictures,function(i,v) {
            var imgUrl = createShareImgUrl(v,'small');
            html += '<img src="'+imgUrl+'" />';
        });
        html += '</dl>';
        html += '</a>';
        html += '</li>';
        html += '</ul>';
    });
    $('#divSingle').append(html);
}

function showNorecords(obj) {
    var html = '';
    html += '<div class="noRecords gray9">';
    html += '<s></s>';
    html += '暂无记录';
    html += '</div>';
    obj.html(html);
}

function showLimitNumDesc(obj,num) {
    var html = '';
    html += '<p id="tips" style="display: block;"><i class="leftI"></i>当前用户只允许查看近<span>'+num+'</span>条记录<i class="leftR"></i></p>';
    obj.html(html);
}
