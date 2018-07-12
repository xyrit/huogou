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
};
var totalCount = 0;
var totalPage = 0;

function orderList_gotoPage(currentPage) {
    if (orderlist_condition.page != currentPage) {
        orderlist_condition.page = currentPage;
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

function createBuyDetailUrl(periodId) {
    return 'http://member.'+baseHost+'/default/buy-detail?id='+periodId;
}

function createOrderDetailUrl(orderId) {
    return 'http://member.'+baseHost+'/default/order-detail?id='+orderId;
}

function createAddShareUrl(orderId) {
    return 'http://member.'+baseHost+'/share/add?id='+orderId;
}

function getBuyList() {
    $.getContent(apiBaseUrl + '/record/buy-list', condition, 'buyList');
}

function getOrderList() {
    $.getContent(apiBaseUrl + '/record/order-list', orderlist_condition, 'orderList');
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
    $.getContent(apiBaseUrl + '/record/collect-list?page=1&perpage=12', '', 'collectList');
}

function success_buyList(json) {
    totalCount = json.totalCount;
    totalPage = json.totalPage;


    $("#record_con").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        var goods_url = createGoodsUrl(v.product_id);
        var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
        var buy_detail_url = createBuyDetailUrl(v.period_id);
        strHtml += '<tr><td><picture>';
        strHtml += '<a href="' + goods_url + '"><img src="' + goods_picture_url + '" alt=""></a>';
        strHtml += '</picture></td>';
        strHtml += '<td class="left">';
        strHtml += '<h3><a href="">(第' + v.period_number + '伙)' + v.goods_name + '</a></h3>';

        if (v.status == 2) {
            strHtml += '<p>获得者：<a href="' + createUserCenterUrl(v.user_home_id) + '">' + v.user_name + '</a></p>';
            strHtml += '<p>揭晓时间：' + v.raff_time + '</p>';
        }

        strHtml += '</td>';
        if (v.status == 0) {
            strHtml += '<td><span>正在进行……</span></td>';
        } else if (v.status == 2) {
            strHtml += '<td><span>已揭晓</span></td>';
        } else {
            strHtml += '<td><span>已满员，正在揭晓</span></td>';
        }

        strHtml += '<td><span>' + v.user_buy_num + '人次</span>';
        strHtml += '<div class="code" periodId="' + v.period_id + '" buynum="' + v.user_buy_num + '"><p>所有伙购码</p>';
        strHtml += '<article id="codeList"></article>';
        strHtml += '</div></td>';
        strHtml += '<td><a class="blue" href="' + buy_detail_url + '">详情</a></td></tr>';
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
        $("#record_con").find("tbody").append("暂无记录");
    }

    $(".code").each(function() {
        $(this).hover(function () {
            var P = $(this);
            var periodId = P.attr("periodId");
            var buyNum = P.attr("buyNum");
            var O = P.find("article");
            if (O.html() == "") {
                $.ajax({
                    async: false,
                    url: apiBaseUrl + '/record/get-buy-code',
                    type: "GET",
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    data: {period_id: periodId},
                    success: function (d) {
                        var J = d.buy_code;
                        var H = "";
                        var I = J.length > 5 ? 5 : J.length;
                        for (var G = 0; G < I; G++) {
                            H += "<i>" + J[G] + "</i>"
                        }
                        if (buyNum > I) {
                            H += '<i><a href="' + createBuyDetailUrl(periodId) + '" >查看更多</a></i>'
                        }
                        O.append(H);
                    }
                });
            }
        });
    });
    /*$('#GoodsList').html("");
    var list = msg.list;
    var strHtml = '<ul class="goods_list">';

    for (var i in list) {
        var goods_url = createGoodsUrl(list[i].product_id);
        var goods_picture_url = createGoodsImgUrl(list[i].goods_picture, 38, 38);
        var buy_detail_url = createBuyDetailUrl(list[i].period_id);
        strHtml += '<li>';
        strHtml += '<a title="" target="_blank" class="pic" href="'+goods_url+'">';
        strHtml += '<img src="'+goods_picture_url+'" /></a></li>';

        strHtml += '<li class="gname">';
        strHtml += '<a target="_blank" href="'+goods_url+'" class="blue">(第'+list[i].period_number+'云)'+list[i].goods_name+'</a>';
        strHtml += '<p class="gray02">价值：<span class="money"><i class="rmb"></i>'+list[i].code_price+'</span></p>';
        strHtml += '<div class="Progress-bar"><p><span style="width: 9.803899082568806px"></span></p><ul class="Pro-bar-li">';
        strHtml += '<li class="P-bar01"><em>'+list[i].code_sales+'</em>已参与人次</li>';
        strHtml += '<li class="P-bar02"><em>'+list[i].code_quantity+'</em>总需人次</li>';
        strHtml += '<li class="P-bar03"><em>'+(list[i].code_quantity - list[i].code_sales)+'</em>剩余人次</li>';
        strHtml += '</ul></div></li>';

        strHtml += '<li class="yg_status">';
        if (list[i].status == 0) {
            strHtml += '<span class="green">正在进行……</span><br /><a href="JavaScript:;" name="appendBuy" class="gray01">追加伙购人次</a>';
            strHtml += '<div class="add" style="display:none;"><dl><input type="hidden" value="'+(list[i].code_quantity - list[i].code_sales)+'" />';
            strHtml += '<dd><input type="text" title="购买人次" onpaste="return false" class="amount" value="" /></dd>';
            strHtml += '<dd><a class="jia" href="JavaScript:;"></a><a class="jian" href="JavaScript:;"></a></dd>';
            strHtml += '<dd><input type="hidden" value="'+list[i].product_id+'" />';
            strHtml += '<input type="button" title="追加伙购人次" class="orangebut btn29" value="确 定" name="btnAppendBuy" /></dd>';
            strHtml += '</dl></div>';
        } else if(list[i].status == 2) {
            strHtml += '<span class="orange">已揭晓</span>';
        } else {
            strHtml += '<span class="green">已满员，正在揭晓</span>';
        }

        strHtml += '</li>';

        strHtml += '<li class="joinInfo" periodId="'+list[i].period_id+'" buynum="'+list[i].user_buy_num+'"><p><em>'+list[i].user_buy_num+'</em>人次</p>';
        strHtml += '<div class="joinInfo-Pop"><div name="divTip" class="grhead-join gray01">所有伙购码<b></b></div>';
        strHtml += '<div name="divShowRNO" class="grhead-joinC" style="display:none;"><div class="grhead-joinCT gray01">所有伙购码<b></b></div>';
        strHtml += '<div name="divRNOList" class="grhead-joinClist"></div></div></div></li>';
        strHtml += '<li class="do"><a href="'+buy_detail_url+'" class="blue" title="详情">详情</a></li>';
    }

    strHtml += '</ul>';
    $('#GoodsList').append(strHtml);

    $(".joinInfo").each(function() {
        $(this).hover(function() {
            var P = $(this);
            var periodId = P.attr("periodId");
            var buyNum = P.attr("buyNum");
            var O = P.find("div[name='divRNOList']");
            if (O.html() == "") {
                var C = $("<ul></ul>");
                C.html("正在加载...");
                O.append(C);
                $.ajax({
                    async:false,
                    url: apiBaseUrl + '/record/get-buy-code',
                    type: "GET",
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    data: {period_id: periodId},
                    success: function (d) {
                        var J = d.buy_code;
                        var H = "";
                        var I = J.length > 5 ? 5 : J.length;
                        for (var G = 0; G < I; G++) {
                            H += "<li>" + J[G] + "</li>"
                        }
                        if (buyNum > I) {
                            H += '<li><a href="'+createBuyDetailUrl(periodId)+'" >查看更多</a></li>'
                        }
                        C.html(H).show()
                    }
                });
            }
            P.find("div[name='divTip']").hide();
            P.find("div[name='divShowRNO']").show()
        },
        function() {
            $(this).find("div[name='divTip']").show();
            $(this).find("div[name='divShowRNO']").hide()
        });
    });

    $("a[name='appendBuy']").each(function() {
        $(this).click(function(M) {
            $(this).parent().parent().siblings().each(function() {
                $(this).find(".add").hide();
                $(this).find("a[name='appendBuy']").show()
            });
            $(this).parent().find(".add").show().click(function(N) {
            });
            $(this).parent().find("input[type=text]").focus().val("1").select();
            $(this).parent().find("input[name='btnAppendBuy']").click(function() {
                var text = $(this).parent().parent().find("input[type=text]");
                var appendNum = text.val();
                if (!appendNum || appendNum <= 0) {
                    $(this).parent().parent().find("input[type=text]").focus();
                } else {
                    var productId = $(this).parent().find("input[type=hidden]").val();
                    $.ajax({
                        async:false,
                        url: apiBaseUrl + '/cart/add',
                        type: "GET",
                        dataType: 'jsonp',
                        jsonp: 'callback',
                        data: {productid: productId, num: appendNum},
                        success: function (data) {
                            if (data.result == 1) {
                                location.href = "http://www.huogou.com/payment.html";
                            } else {
                                alert("对不起，伙购失败！");
                            }
                        }
                    });
                }
            });
            $(this).hide();
            return false
        })
    });

    $(".jia").each(function(){
        $(this).click(function() {
            var C = $(this).parent().parent().find("input[type=text]");
            if (isNaN(parseInt(C.val()))) {
                return
            }
            s("add", $(this));
        });
    });

    $(".jian").each(function(){
        $(this).click(function() {
            var C = $(this).parent().parent().find("input[type=text]");
            var D = C.val();
            if (isNaN(parseInt(D)) || D == "0") {
                C.focus().select();
                return
            }
            s("sub", $(this));
        });
    });*/
}

var s = function(C, F) {
    var G = F.parent().parent().find("input[type=text]");
    var E = parseInt(F.parent().parent().find("input[type=hidden]").val());
    /*var D = o(G, E);
    if (!D) {
        return
    }*/
    var B = parseInt(G.val());
    if (C == "add") {
        if (B >= E) {
            B = E
        } else {
            B++
        }
    } else {
        if (C == "sub") {
            if (B <= 1) {
                B = 1
            } else {
                B--
            }
        }
    }
    G.val(B)
};

var o = function(D) {
    var B = D.val();
    var C = parseInt(D.parent().parent().find("input[type=hidden]").val());
    if (B == "") {
        FailDialog("请输入伙购人次");
        return false
    } else {
        if (isNaN(B)) {
            FailDialog("您输入的伙购人次好像不对哦", 230);
            return false
        } else {
            if (parseInt(B) < 1) {
                FailDialog("最少需伙购1人次");
                return false
            } else {
                if (parseInt(B) > C) {
                    D.val(C);
                    FailDialog("本云最多可参与" + C + "人次", 220);
                    return false
                }
            }
        }
    }
    return true
};

function filt()
{
    $(".remind").find("a").each(function() {
        $(this).click(function() {
            condition.status = parseInt($(this).attr("val"));
            getBuyList();
            /*$(".record-tab a").each(function(C, B) {
                if (C == 0) {
                    $(this).addClass("record-cur").siblings().removeClass("record-cur")
                }
            });*/
        })
    });
    $(".screening").find("a").each(function(C, B) {
        $(this).bind("click",
            function() {
                $(this).addClass("act").siblings().removeClass("act");
                condition.start_time = "";
                condition.end_time = "";
                condition.region = C;
                getBuyList();
            })
    });

    $(".screening").find("input[type=submit]").bind("click", function() {
        if ($("#J-xl").val() && $("#J-xl-2").val()) {
            condition.start_time = $("#J-xl").val();
            condition.end_time = $("#J-xl-2").val();
            getBuyList();
        }
    });
}

function success_orderList(json) {
    $("#orderList").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        var goods_url = createGoodsUrl(v.goods_id);
        var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
        var buy_detail_url = createBuyDetailUrl(v.period_id);
        var order_detail_url = createOrderDetailUrl(v.order_id);
        strHtml += '<tr><td><picture>';
        strHtml += '<a href="' + goods_url + '"><img src="' + goods_picture_url + '" alt=""></a>';
        strHtml += '</picture></td>';
        strHtml += '<td class="left">';
        strHtml += '<h3><a href="' + goods_url + '">(第' + v.period_number + '伙)' + v.goods_name + '</a></h3>';
        strHtml += '<aside>价值：' + v.price + '</aside>';
        strHtml += '<p>幸运伙购码：' + v.lucky_code + '</p>';
        strHtml += '<p>揭晓时间：' + v.raff_time + '</p></td>';
        strHtml += '<td valign="top"><span>' + v.order_id + '</span></td>';

        strHtml += '<td>';
        if (v.status == 0) {
            strHtml += '<a class="order-details" href="' + order_detail_url + '">' + v.status_name + '</a><br/>';
        } else if (v.status == 5 && v.allow_share == 1) {
            strHtml += '<a class="order-details" href="' + createAddShareUrl(v.order_id) + '">' + v.status_name + '</a><br/>';
        } else {
            strHtml += '<a class="order-details" href="javascript:;">' + v.status_name + '</a><br/>';
        }
        strHtml +='<a class="order-details" href="' + order_detail_url + '">订单详情</a>';
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
        $("#record_con").find("tbody").append("暂无记录");
    }
}

function success_topicList(json) {
    $("#topicList").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function(i, v) {
        var header_image = createShareImgUrl(v.header_image, 'small');
        if (v.is_pass == 1) {
            detail_url = createShareDetailUrl(v.id);
        } else {
            detail_url = createShareMemberDetailUrl(v.id);
        }
        strHtml += '<tr><td><picture>';
        strHtml += '<a href="' + detail_url + '"><img src="' + header_image + '" alt=""></a>';
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

        strHtml += '<td><a class="blue" href="' + detail_url + '">详情</a></td></tr>';

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
        $("#record_con").find("tbody").append("暂无记录");
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
            strHtml += '<a href="' + goods_url + '"><img src="' + goods_picture_url + '" alt=""></a>';
            strHtml += '</picture></td>';
            strHtml += '<td class="left">';
            strHtml += '<h3><a href="">' + v.goods_name + '</a></h3>';
            strHtml += '</td>';
            strHtml += '<td><a class="blue" href="' + add_share_url + '">晒单</a></td></tr>';
        }
    });
    $("#topicList").find("tbody").append(strHtml);
    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: nottopiclist_condition.page,
            downPage: 1,
            gotoPage: 'nottopicList_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $("#record_con").find("tbody").append("暂无记录");
    }
}

function success_collectList(json) {
    $('#collectList').html("");

    $.each(json.list, function (i, v) {
        var goods_url = createGoodsUrl(v.goods_id);
        var goods_picture_url = createGoodsImgUrl(v.goods_picture, 200, 200);
        var strHtml = '';
        strHtml += '<li><picture><i class="my-attention-delete"></i>';
        strHtml += '<a href="' + goods_url + '"><img src="' + goods_picture_url + '" alt=""></a></picture>';
        strHtml += '<summary class="atten_con"><h3>' + v.goods_name + '</h3>';

        if (v.is_sale == 1) {
            strHtml += '<aside>第<i>' + v.period_number + '</i>云 进行中</aside></summary>';
            strHtml += '<summary class="atten_fixed"><p><b></b></p>';
            strHtml += '<a href="javascript:;" class="add_cart" codeid="' + v.goods_id + '">加入购物车</a></summary>';
        } else {
            strHtml += '<aside>已结束</aside></summary>';
        }
        strHtml += '</li>';

        var r = $(strHtml);
        $("#collectList").append(r);
        r.find("a.add_cart").click(function () {
            var productId = $(this).attr("codeid");
            $.ajax({
                async: false,
                url: apiBaseUrl + '/cart/add',
                type: "GET",
                dataType: 'jsonp',
                jsonp: 'callback',
                data: {productid: productId, num: 1},
                success: function (data) {
                    if (data.result == 1) {
                        alert("添加成功!");
                    } else {
                        alert("添加失败");
                    }
                }
            });
        })

    });
    //删除关注
    /*r.find("a.n-btn-del").click(function() {
     var productId = $(this).attr("goodsid");

     $.ajax({
     async:false,
     url: apiBaseUrl + '/cart/add',
     type: "GET",
     dataType: 'jsonp',
     jsonp: 'callback',
     data: {productid: productId, num: 1},
     success: function (data) {
     if (data.result == 1) {
     alert("添加成功!");
     } else {
     alert("添加失败");
     }
     }
     });

     return false
     });*/
}

$('#btnSave').click(function() {
    var params = {
        'prov': $('#prov').find("option:selected").text(),
        'city': $('#city').find("option:selected").text(),
        'area': $('#area').find("option:selected").text(),
        'address': $('#address').val(),
        'code': $('#code').val(),
        'name': $('#name').val(),
        'telephone': $('#telephone').val(),
        'mobilephone': $('#mobilephone').val(),
        'default_address_status': $('#default-address').val(),
        'addressId': $('#addressId').val()
    };

    if (params.prov == '---请选择---') {
        alert('请选择的地区');
        return;
    }
    if (params.prov == '---请选择---') {
        alert('请选择的地区');
        return;
    }
    if (params.prov == '---请选择---') {
        alert('请选择的地区');
        return;
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
                alert("添加失败");
            }
        }
    });
});

$('.confirm-address-submit').click(function() {
    var params = {
        'useraddressid': $(".act").find('input[name="id"]').val(),
        'ship_time': $('#ship_time').val(),
        'mark_text': $('#mark_text').val(),
        'orderId': $('#orderId').val()
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/record/submit-address',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) {
                window.location.reload();
            } else {

            }
        }
    });
});

function getAddress(addressId) {
    var params = {
        'addressId': addressId
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/info/get-address',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            editAreaList(data.provId, data.cityId, data.areaId);
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

function resetAddressForm()
{
    $('#addressId').val(0);
    $('#prov').html('<option value="">---请选择---</option>');
    $('#city').html('<option value="">---请选择---</option>');
    $('#area').html('<option value="">---请选择---</option>');
    $('#address').val('');
    $('#code').val('');
    $('#name').val('');
    $('#telephone').val('');
    $('#mobilephone').val('');
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
        strHtml += '</td><td class="' + style + '" align="center">' + v.point + '</td>';
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
        $(".integral-table").find("tbody").append("暂无记录");
    }
}

$('#btnVirtualSubmit').on('click', function() {
    var params = {
        type: $('#type').val(),
        account: $('#account').val(),
        contact: $('#contact').val(),
        name: $('#name').val(),
        note: $('#note').val(),
        order_id: $('#orderId').val()
    };

    $.ajax({
        async: false,
        url: apiBaseUrl + '/record/virtual-product-submit',
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if (data.code == 100) { //成功
                window.location.reload();
            } else {
                //alert(data.msg);
            }
        }
    });
});


$('#transfer').click(function(){

})


