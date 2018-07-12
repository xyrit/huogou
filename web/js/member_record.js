/**
 * Created by chenyi on 2015/10/15.
 * 我的伙购
 */
var condition = {
    page: 1,
    perpage: 10,
    status: -1,
    region: 4,
    start_time: "",
    end_time: "",
};

var orderlist_condition = {
    page: 1,
    perpage: 10,
    status: 0,
    region: 0,
    start_time: "",
    end_time: "",
};

var exchangeOrderList_condition = {
    page: 1,
    perpage: 10,
    status: 0,
    region: 0,
    start_time: "",
    end_time: "",
};

var topiclist_condition = {
    page: 1,
    perpage: 10,
};

var nottopiclist_condition = {
    page: 1,
    perpage: 10,
};

var pointslist_condition = {
    page: 1,
    perpage: 10,
    status: 0,
    start_time: "",
    end_time: ""
};

var collectlist_condition = {
    page: 1,
    perpage: 8,
};
var totalCount = 0;
var totalPage = 0;

/*$(function(){
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/get-position',
        type: "GET",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: "",
        success: function (data) {
            $('#position').html(data.location);
        }
    });
});*/

function orderList_gotoPage(currentPage) {
    if (orderlist_condition.page != currentPage) {
        orderlist_condition.page = currentPage;
        getOrderList();
    }
}

function exchangeOrderList_gotoPage(currentPage) {
    if (exchangeOrderList_condition.page != currentPage) {
        exchangeOrderList_condition.page = currentPage;
        getOrderList();
    }
}

function buyList_gotoPage(currentPage) {
    if (condition.page != currentPage) {
        condition.page = currentPage;
        getBuyList();
    }
}

function topicList_gotoPage(currentPage) {
    if (topiclist_condition.page != currentPage) {
        topiclist_condition.page = currentPage;
        getTopicList();
    }
}

function notTopicList_gotoPage(currentPage) {
    if (nottopiclist_condition.page != currentPage) {
        nottopiclist_condition.page = currentPage;
        getNotTopicList();
    }
}

function pointsList_gotoPage(currentPage) {
    if (pointslist_condition.page != currentPage) {
        pointslist_condition.page = currentPage;
        getPointsList();
    }
}

function collectList_gotoPage(currentPage) {
    if (collectlist_condition.page != currentPage) {
        collectlist_condition.page = currentPage;
        getCollectList();
    }
}

function createBuyDetailUrl(periodId) {
    return 'http://member.'+baseHost+'/default/buy-detail?id='+periodId;
}

function createOrderDetailUrl(orderId) {
    return 'http://member.'+baseHost+'/default/order-detail?id='+orderId;
}

function createAddShareUrl(orderId) {
    return 'http://member.'+baseHost+'/share/add?id='+orderId;
}

function createLotteryUrl($periodId) {
    return 'http://www.' + baseHost + '/lottery/' + $periodId + '.html';
}

function getBuyList() {
    $.getContent(apiBaseUrl + '/record/buy-list', condition, 'buyList');
}

function getOrderList() {
    $.getContent(apiBaseUrl + '/record/order-list', orderlist_condition, 'orderList');
}

function getExchangeOrderList() {
    $.getContent(apiBaseUrl + '/record/exchange-order-list', exchangeOrderList_condition, 'exchangeOrderList');
}

function getTopicList() {
    $.getContent(apiBaseUrl + '/record/topic-list', topiclist_condition, 'topicList');
}

function getNotTopicList() {
    $.getContent(apiBaseUrl + '/record/not-topic-list', nottopiclist_condition, 'notTopicList');
}

function getPointsList() {
    $.getContent(apiBaseUrl + '/record/points-list', pointslist_condition, 'pointsList');
}

function getCollectList() {
    $.getContent(apiBaseUrl + '/record/collect-list', collectlist_condition, 'collectList');
}


function success_buyList(json) {
    totalCount = json.totalCount;
    totalPage = json.totalPage;

    var cart_url = 'http://www.'+ baseHost + '/cart.html';
    $("#record_con").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        var goods_url = createGoodsUrl(v.product_id);
        var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
        var lottery_url = createLotteryUrl(v.period_id);
        var buy_detail_url = createBuyDetailUrl(v.period_id);
        strHtml += '<tr';
        if (v.status == 2) {
            strHtml += ' style="background:#F9F9F9"';
        }
        strHtml += '><td><picture>';
        strHtml += '<a href="' + goods_url + '" target="_blank"><img src="' + goods_picture_url + '" alt=""></a>';
        var canBuy = 1;
        if (v.limit_num > 0) {
            strHtml += '<i class="limitbuy-icon">限购</i>';
            if (v.limit_num <= v.user_buy_num) {
                canBuy = 0;
            }
        }
        strHtml += '</picture></td>';
        strHtml += '<td class="left">';
        strHtml += '<h3><a target="_blank" href="';
        if (v.status == 0) {
            strHtml += goods_url;
        } else {
            strHtml += lottery_url;
        }
        strHtml += '">' + v.goods_name + '</a></h3>';
        if (v.status == 0) {
            strHtml += '<aside>价值：￥' + v.code_price + '</aside>';
            strHtml += '<b class="jindu"><i style="width: ' + (v.code_sales / v.code_quantity).toFixed(4) * 100 + '%"></i></b>';
        }

        if (v.status == 2) {
            strHtml += '<p>获得者：<a target="_blank" href="' + createUserCenterUrl(v.user_home_id) + '">' + v.user_name + '</a></p>';
            strHtml += '<p>揭晓时间：' + v.raff_time + '</p>';
        }

        strHtml += '</td>';
        if (v.status == 0) {
            strHtml += '<td><span>正在进行<i class="dotting">...</i></span><a class="addcart" target="_blank" productId="' + v.product_id + '" canBuy="' + canBuy + '">追加</a></td>';
        } else if (v.status == 2) {
            strHtml += '<td><span>已揭晓</span></td>';
        } else {
            strHtml += '<td><span>揭晓中</span></td>';
        }

        strHtml += '<td><span>' + v.user_buy_num + '人次</span>';
        strHtml += '<div class="code" periodId="' + v.period_id + '" buynum="' + v.user_buy_num + '"><p>所有伙购码</p>';
        strHtml += '<article id="codeList"></article>';
        strHtml += '</div></td>';
        strHtml += '<td><a class="blue" href="' + buy_detail_url + '" target="_blank">详情</a></td></tr>';
    });
    $("#record_con").find("tbody").append(strHtml);
    if (totalPage > 1) {
        $(".pagination").createPage({
            pageCount: totalPage,
            current: condition.page,
            downPage: 1,
            gotoPage: 'buyList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (totalCount == 0) {
        $("#record_con").find("tbody").html('<tr style="border-bottom: 0"><td colspan="5"><div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div></td></tr>');
    }

    $.ajax({
        async: false,
        url: apiBaseUrl + '/record/buy-tips',
        type: "GET",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: '',
        success: function (d) {
            $('#tips1').html('(' + (d.tips[0] + d.tips[1]) + ')');
            $('#tips2').html('(' + d.tips[2] + ')');
            $('#tips3').html('(0)');
        }
    });

    var hover = true;
    $(".code").each(function() {
        $(this).hover(function () {
            var P = $(this);
            var periodId = P.attr("periodId");
            var buyNum = P.attr("buyNum");
            var O = P.find("article");
            if (O.html() == "" && hover == true) {
                hover = false;
                $.ajax({
                    async: false,
                    url: apiBaseUrl + '/record/get-buy-code',
                    type: "GET",
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    data: {period_id: periodId, token: token},
                    success: function (d) {
                        var J = d.buy_code;
                        var H = "";
                        var I = J.length > 5 ? 5 : J.length;
                        for (var G = 0; G < I; G++) {
                            H += "<i>" + J[G] + "</i>"
                        }
                        if (buyNum > I) {
                            H += '<i><a href="' + createBuyDetailUrl(periodId) + '" target="_blank" >查看更多</a></i>'
                        }
                        O.append(H);
                        hover = true;
                    }
                });
            }
        });
    });

    $(".addcart").each(function() {
        $(this).click(function() {
            productId = $(this).attr('productId');
            if ($(this).attr('canBuy') == 0) {
                $('.safety-b-box h3').html("已达最大购买次数");
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
                return false;
            } else {
                var goods_url = createGoodsUrl(productId);
                window.location.href = goods_url;
            }
        });
    });

}

function filt(params, callback)
{
    $(".remind").find("a").each(function(status) {
        $(this).on('click', function() {
            params.start_time = "";
            params.end_time = "";
            params.page = 1;
            params.status = status;
            callback();
        })
    });
    $(".screening").find("a").each(function(region) {
        $(this).on("click",
            function() {
                $(this).addClass("act").siblings().removeClass("act");
                params.start_time = "";
                params.end_time = "";
                params.page = 1;
                if (region == 0) params.status = -1;
                params.region = region;
                callback();
            })
    });

    $(".screening").find("input[type=submit]").bind("click", function() {
        if ($("#J-xl").val() && $("#J-xl-2").val()) {
            params.start_time = "";
            params.end_time = "";
            params.page = 1;
            params.start_time = $("#J-xl").val();
            params.end_time = $("#J-xl-2").val();
            callback();
        }
    });
}


function success_exchangeOrderList(json) {
    $("#orderCount").html("换货商品(" + json.totalCount + ")");
    $("#orderList").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        var goods_url = createGoodsUrl(v.goods_id);
        var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
        var order_detail_url = createOrderDetailUrl(v.order_id);
        strHtml += '<tr><td><picture>';
        strHtml += '<a href="" target="_blank"><img src="' + goods_picture_url + '" alt=""></a>';
        if (v.limit_num > 0) {
            strHtml += '<i class="limitbuy-icon">限购</i>';
        }
        strHtml += '</picture></td>';
        strHtml += '<td class="left">';
        strHtml += '<h3><a href="">' + v.goods_name + '</a></h3>';
        strHtml += '<aside>价值：' + v.price + '</aside>';
        strHtml += '<p>&nbsp;</p>';
        strHtml += '<p>&nbsp;</p></td>';
        strHtml += '<td valign="top"><span>' + v.ex_id + '</span></td>';

        strHtml += '<td>';
        if (v.status == 0 || v.status == 6) {
            strHtml += '<a class="order-details-submit" href="' + order_detail_url + '" target="_blank">' + v.status_name + '</a><br/>';
        } else if (v.status == 4) {
            strHtml += '<a class="order-details-submit" href="' + order_detail_url + '&on=exchange" target="_blank">' + v.status_name + '</a><br/>';
        }else if(v.status == 5 && v.allow_share == 1 && v.is_exchange != 0){
            strHtml += '<a class="order-details" href="javascript:;" target="_blank">' + v.status_name + '</a><br/>';
        } else {
            strHtml += '<a class="order-details" href="javascript:;" target="_blank">' + v.status_name + '</a><br/>';
        }
        strHtml +='<a href="' + order_detail_url + '&on=exchange" target="_blank">订单详情</a>';
        strHtml += '</td></tr>';
    });
    $("#orderList").find("tbody").append(strHtml);
    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: exchangeOrderList_condition.page,
            downPage: 1,
            gotoPage: 'exchangeOrderList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $("#orderList").find("tbody").html('<tr style="border-bottom: 0;"><td colspan="5"><div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div></td></tr>');
    }
}

function success_orderList(json) {
    $("#orderCount").html("获得的商品(" + json.totalCount + ")");
    $("#orderList").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        var goods_url = createGoodsUrl(v.goods_id);
        var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
        var buy_detail_url = createBuyDetailUrl(v.period_id);
        var order_detail_url = createOrderDetailUrl(v.order_id);
        var lottery_url = createLotteryUrl(v.period_id);
        strHtml += '<tr><td><picture>';
        strHtml += '<a href="' + goods_url + '" target="_blank"><img src="' + goods_picture_url + '" alt=""></a>';
        if (v.limit_num > 0) {
            strHtml += '<i class="limitbuy-icon">限购</i>';
        }
        strHtml += '</picture></td>';
        strHtml += '<td class="left">';
        strHtml += '<h3><a href="' + lottery_url + '" target="_blank">' + v.goods_name + '</a></h3>';
        strHtml += '<aside>价值：' + v.price + '</aside>';
        strHtml += '<p>幸运伙购码：' + v.lucky_code + '</p>';
        strHtml += '<p>揭晓时间：' + v.raff_time + '</p></td>';
        strHtml += '<td valign="top"><span>' + v.order_id + '</span></td>';

        strHtml += '<td>';
        if(v.is_exchange != 0){
            strHtml += '<a class="order-details" href="javascript:;">已完成</a><br/>';
        }else{
            if (v.status == 0 || v.status == 6) {
                strHtml += '<a class="order-details-submit" href="' + order_detail_url + '" target="_blank">' + v.status_name + '</a><br/>';
            } else if (v.status == 4) {
                strHtml += '<a class="order-details-submit" href="' + order_detail_url + '" target="_blank">' + v.status_name + '</a><br/>';
            } else if (v.status == 5 && v.allow_share == 1) {
                strHtml += '<a class="order-details-submit" href="' + createAddShareUrl(v.order_id) + '" target="_blank">' + v.status_name + '</a><br/>';
            } else {
                strHtml += '<a class="order-details" href="' + order_detail_url + '">' + v.status_name + '</a><br/>';
            }
        }

        strHtml +='<a href="' + order_detail_url + '" target="_blank">订单详情</a>';
        strHtml += '</td></tr>';
    });
    $("#orderList").find("tbody").append(strHtml);
    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: orderlist_condition.page,
            downPage: 1,
            gotoPage: 'orderList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $("#orderList").find("tbody").html('<tr style="border-bottom: 0;"><td colspan="5"><div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div></td></tr>');
    }
}

function success_topicList(json) {
    $("#topicList").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        var header_image = createShareImgUrl(v.header_image, 'main');
        if (v.is_pass == 1) {
            detail_url = createShareDetailUrl(v.id);
        } else {
            detail_url = createShareMemberDetailUrl(v.id);
        }
        strHtml += '<tr><td><picture>';
        strHtml += '<a href="' + detail_url + '" target="_blank"><img src="' + header_image + '" alt=""></a>';
        strHtml += '</picture></td>';
        strHtml += '<td class="left">';
        strHtml += '<h3><a href="">' + v.title + '</a><i>' + v.created_at + '</i></h3>';
        strHtml += '<p class="width">' + v.content.substr(0, 50) + '</p></td><td>';

        if (v.is_pass == 0) {
            strHtml += '<span class="green">审核中</span></td>';
        } else if (v.is_pass == 1) {
            strHtml += '<span class="green">审核通过</span></td>';
        } else {
            strHtml += '<span class="green">审核未通过</span></td>';
        }

        strHtml += '<td><a class="blue" href="' + detail_url + '" target="_blank">详情</a></td></tr>';

    });
    $("#topicList").find("tbody").append(strHtml);
    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: topiclist_condition.page,
            downPage: 1,
            gotoPage: 'topicList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $("#topicList").find("tbody").html('<tr style="border-bottom: 0;"><td colspan="5"><div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div></td></tr>');
    }
}

function success_notTopicList(json) {
    $("#topicList").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        if (v.allow_share == 1) {
            var goods_url = createGoodsUrl(v.goods_id);
            var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
            var add_share_url = createAddShareUrl(v.order_id);
            strHtml += '<tr><td><picture>';
            strHtml += '<a href="' + goods_url + '" target="_blank"><img src="' + goods_picture_url + '" alt=""></a>';
            strHtml += '</picture></td>';
            strHtml += '<td class="left">';
            strHtml += '<h3><a href="' + goods_url + '" target="_blank">' + v.goods_name + '</a></h3>';
            strHtml += '</td>';
            strHtml += '<td><a class="blue" href="' + add_share_url + '" target="_blank">晒单</a></td></tr>';
        }
    });
    $("#topicList").find("tbody").append(strHtml);
    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: nottopiclist_condition.page,
            downPage: 1,
            gotoPage: 'notTopicList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $("#topicList").find("tbody").html('<tr style="border-bottom: 0;"><td colspan="5"><div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div></td></tr>');
    }
}

function success_collectList(json) {
    $('#collectList').html("");

    $.each(json.list, function (i, v) {
        var goods_url = createGoodsUrl(v.goods_id);
        var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
        var strHtml = '';
        if (v.is_sale == 1) {
            strHtml += '<li>';
        } else {
            strHtml += '<li class="goods_picture_li">';
        }
        strHtml += '<picture>';
        if (v.is_sale == 1) {
            strHtml += '<i pid="' + v.goods_id + '" class="my-attention-delete"></i>';
        }
        strHtml += '<a href="' + goods_url + '" target="_blank"><img src="' + goods_picture_url + '" alt=""></a></picture>';
        if (v.is_sale != 1) {
            strHtml += '<div class="conclude"><span class="conclude_icon">已结束</span></div>';
        }
        strHtml += '<summary class="atten_con"><h3 style="text-align: center">' + v.goods_name + '</h3>';

        if (v.is_sale == 1) {
            strHtml += '<aside>最新一期进行中</aside></summary>';
            strHtml += '<summary class="atten_fixed"><p><b style="width:'+ v.sales / v.quantity * 100 +'%"></b></p>';
            strHtml += '<a href="javascript:;" class="add_cart" codeid="' + v.period_id + '">加入购物车</a></summary>';
        } else {
            strHtml += '<aside>已结束</aside></summary>';
        }
        strHtml += '</li>';

        var r = $(strHtml);
        $("#collectList").append(r);
        r.find("a.add_cart").click(function () {
            var periodId = $(this).attr("codeid");
            $.ajax({
                async: false,
                url: apiBaseUrl + '/cart/add',
                type: "GET",
                dataType: 'jsonp',
                jsonp: 'callback',
                data: {periodid: periodId, num: 1, token: token},
                success: function (data) {
                    if (data.code == 100) {
                        $('.safety-b-box').html('<i id="safety-b-close"></i><h4>添加成功</h4>');
                        $('#safety-b-con').fadeIn();
                        setTimeout(function(){
                            $('#safety-b-con').fadeOut();
                        },1000);
                        $.getContent(apiBaseUrl + "/cart/list", {"token": token}, 'sideBarCart');
                        return false;
                    } else {
                        $('.safety-b-box h3').html('添加失败');
                        $('#safety-b-con').fadeIn();
                        setTimeout(function(){
                            $('#safety-b-con').fadeOut();
                        },1000);
                        return false;
                    }
                }
            });
        });
        //删除关注
        r.find("i.my-attention-delete").click(function() {
            $('.balance_con').fadeIn();
            $('.close, #del-cancle, #del-sure').on('click',function(){
                $('.balance_con').fadeOut();
            })

            var productId = $(this).attr("pid");

            $('#del-sure').on('click',function(){
                $.ajax({
                    async:false,
                    url: apiBaseUrl + '/follow/cancel',
                    type: "GET",
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    data: {pid: productId, token: token},
                    success: function (data) {
                        if (data.code == 1) {
                            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>删除成功</h4>');
                            $('#safety-b-con').fadeIn();
                            setTimeout(function(){
                                $('#safety-b-con').fadeOut();
                            },1000)
                            setTimeout(function(){
                                window.location.reload();
                            },1000)
                        } else {
                            $('.safety-b-box h3').html('删除失败');
                            $('#safety-b-con').fadeIn();
                            setTimeout(function(){
                                $('#safety-b-con').fadeOut();
                            },1000);
                            return false;
                        }
                    }
                });
            })
        });
    });

    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: collectlist_condition.page,
            downPage: 1,
            gotoPage: 'collectList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $("#collectList").html('<div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div>');
    }

}

$('#btnSave').click(function() {
    var params = {
        'prov': $('#pro_code').val(),
        'city': $('#city_code').val(),
        'area': $('#area_code').val(),
        'address': $('#address').val(),
        'code': $('#code').val(),
        'name': $('#name').val(),
        'telephone': $('#telephone').val(),
        'mobilephone': $('#mobilephone').val(),
        'default_address_status': $('#default-address').val(),
        'addressId': $('#addressId').val(),
        'token': token
    };

    $('.safety-b-box h3').css('width', 250);
    if (params.prov == '' || params.city == '' || params.area == '') {
        $('.safety-b-box h3').html('请选择完整的所在地区');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000)
        return false;
    }

    if (params.address == '') {
        $('.safety-b-box h3').html("请填写详细地址");
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000)
        return false;
    }

    if (params.name == '') {
        $('.safety-b-box h3').html("请填写收货人");
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000)
        return false;
    }

    if (params.mobilephone == '') {
        $('.safety-b-box h3').html("请填写手机号码");
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000)
        return false;
    }

    if (!checkPhone(params.mobilephone)) {
        $('.safety-b-box h3').html('请填写有效的手机号码');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000)
        return false;
    }

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/add-address',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                window.location.reload();
            } else {
                $('.safety-b-box h3').html(data.msg);
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
            }
        }
    });
});

$('#btnSaveVirtual').click(function() {
    var params = {
        type: $("input[type=radio]:checked").val(),
        account: $('#account').val(),
        contact: $('#contact').val(),
        addressId: $('#addressId').val(),
        token: token
    };

    if (!params.type || !params.account) {
        $('.safety-b-box').html('<i id="safety-b-close"></i><h4>请将信息填写完整</h4>');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000)
        return false;
    }

    if (params.account != $('#submit_account').val()) {
        $('.safety-b-box').html('<i id="safety-b-close"></i><h4>确认账号错误</h4>');
        $('#safety-b-con').fadeIn();
        setTimeout(function(){
            $('#safety-b-con').fadeOut();
        },1000)
        return false;
    }

    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/add-virtual-address',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                window.location.reload();
            } else {
                $('.safety-b-box h3').html(data.msg);
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
            }
        }
    });
});

function getAddress(addressId) {
    var params = {
        'addressId': addressId,
        'token': token
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/get-address',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            editAreaList(data.prov, data.city, data.area);
            $('#prov').siblings('.select_ck').find('a').html(data.provName);
            $('#city').siblings('.select_ck').find('a').html(data.cityName);
            $('#area').siblings('.select_ck').find('a').html(data.areaName);
            $('#pro_code').val(data.prov);
            $('#city_code').val(data.city);
            $('#area_code').val(data.area);
            $('#addressId').val(addressId);
            $('#address').val(data.address);
            $('#code').val(data.code);
            $('#name').val(data.name);
            $('#telephone').val(data.telephone);
            $('#mobilephone').val(data.mobilephone);
            if (data.default_address_status == 1) {
                $('#default-address').attr("checked", true);
            }
        }
    });
}

function getVirtualAddress(addressId) {
    var params = {
        'addressId': addressId,
        'token': token
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/get-virtual-address',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            $('#addressId').val(data.id);
            $('#account').val(data.account);
            $('#submit_account').val(data.account);
            $('#contact').val(data.contact);
            $("input[type=radio][value="+data.type+"]").attr("checked",'checked');
        }
    });
}

function resetAddressForm()
{
    $('#addressId').val(0);
    $('#prov').html('<option value="">---请选择---</option>');
    $('#city').html('<option value="">---请选择---</option>');
    $('#area').html('<option value="">---请选择---</option>');
    $('#address').val('');
    $('#code').val('');
    $('#name').val('');
    $('#pro_code').val('');
    $('#city_code').val('');
    $('#area_code').val('');
    $('#telephone').val('');
    $('#mobilephone').val('');
}

function resetVirtualAddressForm()
{
    $('#addressId').val(0);
    $('#account').val('');
    $('#submit_account').val('');
    $('#contact').val('');
}

// 确认收货
$('#btnSubmitGoods').click(function() {
    var params = {
        'id': $('#orderId').val()
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/record/submit-goods',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) { //成功
                window.location.reload();
            } else {
                alert(data.msg);
            }
        }
    });
});


function success_pointsList(json) {
    $(".integral-table").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        strHtml += '<tr><td>' + v.created_at;
        if (v.point > 0) {
            style = 'green';
        } else {
            style = 'orange';
        }
        strHtml += '</td><td class="' + style + '" align="center">';
        if (v.point > 0) {
            strHtml += '+';
        }
        strHtml += v.point + '</td>';
        strHtml += '<td>' + v.desc + '</td></tr>';
    });
    $(".integral-table").find("tbody").append(strHtml);
    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: pointslist_condition.page,
            downPage: 1,
            gotoPage: 'pointsList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $(".integral-table").find("tbody").html('<tr style="border-bottom: 0;"><td colspan="5"><div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div></td></tr>');
    }
}

function hotOrderList() {
    $.getContent(apiBaseUrl + '/record/hot-order-list', {token: token}, 'hotOrderList');
}

function success_hotOrderList(json) {
    $('.week_list').html('');
    var strHtml = '';
    $.each(json, function (i, v) {
        var user_center_url = createUserCenterUrl(v.home_id);
        var grade_name_pic_url = 'http://skin.' + baseHost + '/img/' + v.grade_name.pic;
        var privUrl = 'http://member.' + baseHost + '/message/msg-detail?id=' + v.home_id;

        strHtml += '<li><a href="' + user_center_url + '" target="_blank"><picture><i></i><img src="' + v.user_avatar + '" alt=""></picture></a>';
        strHtml += '<summary><a href="' + user_center_url + '" target="_blank"><h3>' + v.username.substr(0, 10) + '</h3></a>';
        strHtml += '<p><i><img src="' + grade_name_pic_url + '" alt=""></i>' + v.grade_name.name + '</p></summary>';

        if (v.user_id != user_id && v.friend == undefined) {
            strHtml += '<a href="javascript:;" class="add-friends hy" userid="' + v.home_id + '">加为好友</a>';
        }else{
            if(v.self == 0){
                strHtml += '<a href="'+privUrl+'" class="hy" target="_blank">发私信</a>';
            }
        }

        strHtml += '</li>';
    });

    $('.week_list').html(strHtml);

    $('.add-friends').click(function(){
        var apply = $(this).attr('userid');
        var apply_url = apiBaseUrl + '/group/add-friend';

        if(apply){
            $.ajax({
                async: false,
                url: apply_url,
                type: "GET",
                dataType: 'jsonp',
                jsonp: 'callback',
                data: {id: apply},
                success: function (data) {
                    if(data.code == 100 || data.code == 102){
                        $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                    }else{
                        $('.safety-b-box h3').html(data.msg);
                    }
                    $('#safety-b-con').fadeIn();
                    setTimeout(function(){
                        $('#safety-b-con').fadeOut();
                    },2000)
                }
            })
        }
    })
}

$(function(){
    setTimeout(function(){
        if($('#postTitle').val() == ""){
            $('#postTitle').siblings('.input_arr').show();
        }
        if($('#postContent').val() == ""){
            $('#postContent').siblings('.input_arr').show();
        }
    },200)

    $('#postTitle').focus(function(){
        $(this).siblings('.input_arr').hide();
    }).blur(function(){
        if($('#postTitle').val() == ""){
            $(this).siblings('.input_arr').show();
        }else{
            $(this).siblings('.input_arr').hide();
        }
    })

    $('.input_arr').click(function(){
        $('#postContent').focus();
    })
    $('#postContent').focus(function(){
        $(this).siblings('.input_arr').hide();
    }).blur(function(){
        if($('#postContent').val() == ""){
            $(this).siblings('.input_arr').show();
        }else{
            $(this).siblings('.input_arr').hide();
        }
    })
})