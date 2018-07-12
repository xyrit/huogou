var baseHost = getHost();
var apiBaseUrl = 'http://api.' + baseHost;
var wwwBaseUrl = 'http://www.' + baseHost;
var skinBaseUrl = 'http://skin.' + baseHost;
var shareBaseUrl = 'http://share.' + baseHost;
var groupBaseUrl = 'http://group.' + baseHost;
var memberBaseUrl = 'http://member.' + baseHost;
var userBaseUrl = 'http://u.' + baseHost;
var passportBaseUrl = 'https://passport.' + baseHost;
var photoSize = ['58', '200', '400', 'org'];
var avatarSize = ['30', '80', '160'];
var goodsLoadingImg = skinBaseUrl+'/img/goods_loading.gif';
var divLoadingImg = skinBaseUrl+'/img/loading.gif';
jQuery.extend({
    getContent: function (url, data, callback, async, sucfunc) {
        if (typeof async == 'boolean') {
            async = async ? true : false;
        } else {
            async = true;
        }
        $.ajax({
            async: async,
            url: url,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: data,
            cache: false,
            jsonpCallback: "success_" + callback,
            success: function (json) {
                if (typeof sucfunc == "function") {
                    sucfunc(json);
                }
            }
        });
    },
    getJsonp: function(url, data, succfunc) {
        $.jsonp({
            url: url,
            data: data,
            callbackParameter: "callback",
            success: succfunc,
            error: function (xOptions, textStatus) {
               // console.log(xOptions)
            }
        });
    },
    changeByNum: function (id, max, surplus, limit, chance,buyUnit) {
        surplus = limit > 0 ? (limit > surplus ? surplus : limit) : surplus;
        $("#" + id + " .add").click(function () {
            var num = parseInt($("#" + id + " input").val()) + 1*buyUnit;
            $("#" + id + " .mius").removeClass('cur');
            if (num > surplus) {
                return false;
            } else {
                if (num == surplus) {
                    $(this).addClass('cur');
                }
                ;
                $("#" + id + " input").val(num);
            }
            showchance(num);
        })
        $("#" + id + " .mius").click(function () {
            var num = parseInt($("#" + id + " input").val()) - 1*buyUnit;
            $("#" + id + " .add").removeClass('cur');
            if (num == 0) {
                return false;
            } else {
                if (num == 1) {
                    $(this).addClass('cur');
                }
                $("#" + id + " input").val(num);
            }
            showchance(num);
        })
        $("#" + id + " input").on('input', function (e) {
            var num = parseInt($(this).val());
            if (isNaN(num)) {
                $(this).val('');
                // $("."+chance).fadeOut();
            } else {
                if (num === 0 || num < 0) {
                    num = Math.ceil(1/buyUnit)*buyUnit;
                    $(this).val(num);
                    $("#" + id + " .mius").addClass('cur');
                    $("#" + id + " .add").removeClass('cur');
                } else if (num > surplus) {
                    num = surplus;
                    $("#" + id + " .add").addClass('cur');
                    $(this).val(num);
                }
            }
            showchance(num);
        })
        $("#" + id + " input").keydown(function(){
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if (e && e.keyCode != 8 && e.keyCode != 127 && e.keyCode != 37 && e.keyCode != 39 && (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
              return false;
            };
        })
        $("#" + id + " input").blur(function () {
            var num = $(this).val();
            if (num <= 0 || isNaN(num)) {
                $(this).val(1*buyUnit);
            } else {
                num = Math.ceil(num/buyUnit)*buyUnit;
                $(this).val(num);
            }
            ;
        })
        var showchance = function(num){
            if (chance && num) {
                var chanceHtml = '<span class="win_txt">获得几率' + changeTwoDecimal_f(num / max * 100) + '%<i></i></span>';
                $("." + chance).html(chanceHtml).show();
                setTimeout(function(){
                    $("."+chance).fadeOut();
                },3000)
            };
        }
    }
});

function changeTwoDecimal_f(x) {
    var f_x = parseFloat(x);
    if (isNaN(f_x)) {
        // alert('function:changeTwoDecimal->parameter error');
        return false;
    }
    var f_x = Math.round(x * 100) / 100;
    var s_x = f_x.toString();
    var pos_decimal = s_x.indexOf('.');
    if (pos_decimal < 0) {
        pos_decimal = s_x.length;
        s_x += '.';
    }
    while (s_x.length <= pos_decimal + 2) {
        s_x += '0';
    }
    return s_x;
}

function getHost(url) {
    var host = "null";
    if (typeof url == "undefined"
        || null == url)
        url = window.location.href;
    var regex = /.*\:\/\/([^\/|:]*).*/;
    var match = url.match(regex);
    if (typeof match != "undefined"
        && null != match) {
        host = match[1];
    }
    if (typeof host != "undefined"
        && null != host) {
        var strAry = host.split(".");
        if (strAry.length > 1) {
            host = strAry[strAry.length - 2] + "." + strAry[strAry.length - 1];
        }
    }
    return host;
}


function createGoodsImgUrl(name, width, height) {
    return 'http://img.' + baseHost + '/pic-' + width + '-' + height + '/' + name;
}

function createUserFaceImgUrl(name, width) {
    if (!name) {
        name = '000000000000.jpg';
        return 'http://img.' + baseHost + '/userface/' + width + '/' + name;
    }
    return 'http://img.' + baseHost + '/userface/' + width + '/' + name;
}

function createShareImgUrl(name, size) {
    return 'http://img.' + baseHost + '/userpost/' + size + '/' + name;
}

function createTempImgUrl(name) {
    return 'http://img.' + baseHost + '/temp/' + name;
}

function createGoodsUrl(productId) {
    return 'http://www.' + baseHost + '/product/' + productId + '.html';
}

function createPeriodUrl(periodId) {
    return 'http://www.' + baseHost + '/lottery/' + periodId + '.html';
}

function createPeriodListUrl(catId, page) {
    if (catId) {
        if (page > 1) {
            return 'http://www.' + baseHost + '/lottery/i' + catId + 'm' + page + '.html';
        }
        return 'http://www.' + baseHost + '/lottery/i' + catId + '.html';
    } else {
        if (page > 1) {
            return 'http://www.' + baseHost + '/lottery/m' + page + '.html';
        }
        return 'http://www.' + baseHost + '/lottery/m1.html';
    }
}

function createUserCenterUrl(userHomeId) {
    return 'http://u.' + baseHost + '/' + userHomeId;
}

function createShareDetailUrl(shareTopicId) {
    return 'http://share.' + baseHost + '/detail-' + shareTopicId + '.html';
}

function createShareMemberDetailUrl(shareTopicId) {
    return 'http://member.' + baseHost + '/share/detail-' + shareTopicId + '.html';
}

function createLoginUrl(iframe, forword) {
    var url = 'https://passport.' + baseHost + '/login.html';
    url = url + '?iframe=' + iframe;
    url = url + '&forward=' + forword;
    return url;
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

function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURI(r[2]);
    return null;
}

function getHtmlUrlParam(j) {
    var href = window.location.href;
    var s = "list";
    for (var i = href.split("-").length - 1; i > 0; i--) {
        s += '-([0-9]*)';
    }
    ;
    s += ".html";
    var reg = new RegExp(s);
    var r = href.match(reg);
    if (r != null) {
        if (r[j]) {
            return r[j];
        } else {
            return 0;
        }
    }
    ;
    return 0;
}

function tmpcar(pid, num) {
    var car = $.cookie('tc');
    var tmp = "";
    if (car == null) {
        tmp = '{"p":' + pid + ',"nu":' + num + '}';
    } else {
        var data = eval("(" + car + ")");
        if (pid == data.p) {
            num = parseInt(data.nu) + parseInt(num);
            tmp = '{"p":' + pid + ',"nu":' + num + '}';
        } else {
            tmp = '[' + car + ',{"p":' + pid + ',"nu":' + num + '}]';
        }
    }
    $.cookie('tc', tmp, {path: '/'});
}

function createPage(page, totalPage, maxButtonCount, totalCount) {
    if (totalPage <= 1) {
        return;
    }
    page = parseInt(page);
    if (page <= 1) {
        page = 1;
    }
    if (page >= totalPage) {
        page = totalPage;
    }
    if (page <= 1) {
        var prevButton = '<a href="javascript:void(0);" class="prev disabled">上一页</a>';
    } else {
        var prevPageUrl = createPeriodListUrl(cid, page - 1);
        var prevButton = '<a href="' + prevPageUrl + '" class="prev">上一页</a>';
    }

    if (page >= totalPage) {
        var nextButton = '<a href="javascript:;" title="下一页" class="next disabled">下一页</a>';
    } else {
        var nextPageUrl = createPeriodListUrl(cid, page + 1);
        var nextButton = '<a href="' + nextPageUrl + '" title="下一页" class="next">下一页</a>';
    }

    var beginPage = Math.max(1, page - parseInt(maxButtonCount / 2));
    var endPage = beginPage + maxButtonCount - 1;
    if (endPage > totalPage) {
        endPage = totalPage;
        beginPage = Math.max(1, endPage - maxButtonCount + 1);
    }

    var firstPageUrl = createPeriodListUrl(cid, 1);
    var lastPageUrl = createPeriodListUrl(cid, totalPage);
    var firstButton = '';
    var lastButton = '';
    if (beginPage > 1) {
        firstButton += '<a href="' + firstPageUrl + '"><b></b>1</a>';
        firstButton += '<i>...</i>';
    }
    if (endPage < totalPage) {
        lastButton += '<i>...</i>';
        lastButton += '<a href="' + lastPageUrl + '"><b></b>' + totalPage + '</a>';
    }

    var buttons = '';
    for (var i = beginPage; i <= endPage; i++) {
        var lotteryListUrl = createPeriodListUrl(cid, i);
        var curClass = '';
        if (i == page) {
            curClass = 'class="act"';
        }
        buttons += '<a ' + curClass + ' href="' + lotteryListUrl + '"><b></b>' + i + '</a>';
    }

    var pageHtml = '';
    pageHtml += prevButton + firstButton + buttons + lastButton + nextButton;
    $('.pagination').html(pageHtml);
}

function success_goCart(json) {
    addNotice(json.code,'/cart.html');
}

function success_cartadd(json) {
    addNotice(json.code,'','1');
}

function addNotice(code,url,refresh){
    if (code == 100) {
        if (url.length > 0) {
            window.location.href = url;
        };
    }else if (code == 101 || code == 102) {
        $('.u-flyer').remove();
        $('.safety-b-box').html('<i id="safety-b-close"></i><h3>此商品已被抢光</h3>');
        $('#safety-b-con').fadeIn();
        if (refresh) {
            setTimeout(function () {
                window.location.reload();
            }, 1000);
        };
    }else if (code == 103) {
        $('.u-flyer').remove();
        $('.safety-b-box').html('<i id="safety-b-close"></i><h3>加入购物车失败</h3>');
        $('#safety-b-con').fadeIn();
        setTimeout(function () {
            window.location.reload();
        }, 1000);
    }else if (code == 104 || code == 105) {
        $('.u-flyer').remove();
        $('.safety-b-box').html('<i id="safety-b-close"></i><h3>已超出限购数量</h3>');
        $('#safety-b-con').fadeIn();
        setTimeout(function () {
            $('#safety-b-con').fadeOut();
        }, 1000);
    }else if (code == 10099) {
        $('.u-flyer').remove();
        $('.safety-b-box').css('width','460px').html('<i id="safety-b-close"></i><h3 style="width: 300px">账户已冻结，如有疑问请联系伙购网客服</h3>');
        $('#safety-b-con').fadeIn();
        setTimeout(function () {
            $('#safety-b-con').fadeOut();
        }, 1000);
    };
}

function checkPhone(str) {
    var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;
    if (reg.test(str)) {
        return true;
    } else {
        return false;
    }
    ;
}

function checkEmail(str) {
    var reg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    if (reg.test(str)) {
        return true;
    } else {
        return false;
    }
}

function showLoginForm() {
    var loginUrl = createLoginUrl(1, window.location.href);
    var html = '<section id="log-fixed">';
    html += '<div class="log_con">';
    html += '<i id="log-fixed-close" onclick="closeLoginForum();"></i>';
    html += '<iframe height="100%" width="100%" frameborder=0 src="' + loginUrl + '" scrolling="no"></iframe>';
    html += '</div>';
    html += '</section>';
    $('body').prepend(html);
}

function closeLoginForum() {
    $("#log-fixed").hide();
}

function checkLogin() {
    // $.ajaxSettings.async = false;
    // var isLogined = 0;
    // $.getJSON(apiBaseUrl + '/user/check-login?callback=?',{"token":token},function(data){
    //     isLogined = data.logined;
    //     alert(isLogined);
    // })
    // return isLogined;
    $.getContent(apiBaseUrl + '/user/check-login', {'token': token}, 'login');
}

function success_login(json) {
    isLogined = json.logined;
}

//模拟placeholder.js
$(function(){
    //判断注册页面清空value
    var url = window.location.pathname.substring(1).split('.')[0];
    if(url == 'register'){
        $('#register-form')[0].reset();
        if($("input[type='text']").val() != ""){
            $("input[type='text']").siblings('.input_arr').hide();
        }else{
            $("input[type='text']").siblings('.input_arr').show();
        }
        if($("input[type='password']").val() != ""){
            $("input[type='password']").siblings('.input_arr').hide();
        }else{
            $("input[type='password']").siblings('.input_arr').show();
        }
        $("input[type='text']").siblings('#usererror').hide();
        $("input[type='password']").siblings('s').hide();
    }

    setTimeout(function(){
        if($("input[type='text']").val() != ""){
            $("input[type='text']").siblings('.input_arr').hide();
        }else{
            $("input[type='text']").siblings('.input_arr').show();
        }
        if($("input[type='password']").val() != ""){
            $("input[type='password']").siblings('.input_arr').hide();
        }else{
            $("input[type='password']").siblings('.input_arr').show();
        }
    },200)
    $('input').on('keydown',function(){
        $(this).siblings('.input_arr').hide();
    }).on('blur',function(){
        if($(this).val() == ""){
            $(this).siblings('.input_arr').show();
        }
    })
    $('.input_arr').click(function(){
        $(this).siblings('input').focus();
    })
});

//阻止密码输入复制粘贴
$(function(){
    $("input:password").bind("copy cut paste",function(e){
        return false;
    })
})

$('.input_arr').on('click',function(){
    $(this).siblings('input').focus();
})



function showQuickBuy(pid){
    $.getJsonp(apiBaseUrl+'/pay/quick-buy',{'pid':pid},function(data){
        if (!data.logined) {
            showLoginForm();
        }else{
            if (data.code == '100') {
                showQuickBuyForm(pid,data.price,data.left,data.canbuy,data.money,data.ppwd,data.free);
                $("#buy_popup .close").click(function(){
                    $("#buy_popup").remove();
                })
            }else {
                addNotice(data.code,'',1);
            };
        }
    })
}

function showQuickBuyForm(pid,price,left,canbuy,money,ppwd,free){
    var html = '<section id="buy_popup">';
        html += '<div class="buy_box">';
        html += '<i class="close"></i>';
        html += '<div class="buy_con">';
        html += '<p>剩余 '+left+' 人次</p>';
        html += '<div class="renci">';
        html += '<em>我要参加</em>';
        html += '<div>';
        html += '<span class="pro_less mius">-</span><input class="num" value="1" type="text"><span class="pro_add add">+</span>';
        html += '</div>';
        html += '<i>人次</i>';
        html += '</div>';
        html += '<p class="hui">请选择参与人次</p>';
        html += '<p class="hui">您的帐户余额： '+money+'.00元</p>';
        html += '<p><a id="confirm-cayment" href="javascript:;">确认支付<i>1</i>.00元</a></p>';
        html += '</div>';
        html += '</div>';
        html += '</section>';
    $('body').append(html);
    $.changeByNum('buy_popup',price,left,canbuy);
    $("#buy_popup input").keyup(function(){
        var val = $(this).val();
        needMoney(val,money);
    })
    $("#buy_popup span").click(function(){
        var val = $("#buy_popup input").val();
        needMoney(val,money);
    })
    $("#confirm-cayment").click(function(){
        var num = $("#buy_popup input").val();
        if (ppwd && num > free) {
            var phtml = "<p>请输入支付密码</p>";
                phtml += '<input value="" type="password" id="ppwd" maxlength="6" autofocus="autofocus">';
                phtml += '<em class="input_arr">请输入6位支付密码</em>';
                phtml += '<s style="display:none;"></s>';
                phtml += '<p class="f-grayButton"><a id="confirm-pwd" href="javascript:;">确定</a></p>';
            $("#buy_popup .buy_con").html(phtml);
            $('#ppwd').focus();
            $('.input_arr').click(function(){
                $('#ppwd').focus();
            })

            $("#buy_popup #ppwd").keyup(function(event) {
                var pwd = $(this).val();
                if($('#ppwd').val() == ""){
                    $('#ppwd').siblings('.input_arr').show();
                }else{
                    $('#ppwd').siblings('.input_arr').hide();
                }
                $('#ppwd').siblings('s').hide().removeAttr('class');
                if (pwd.length == 6) {
                    $('#ppwd').siblings('s').show();
                    $.getJsonp(apiBaseUrl+'/pay/check-ppwd',{'pwd':pwd},function(json){
                        if(json.code == "1"){
                             $('#ppwd').siblings('s').addClass('tips_txt_Correct');
                             $('#confirm-pwd').parents('p').removeClass('f-grayButton').css('cursor','pointer');
                            $("#buy_popup #confirm-pwd").click(function(){
                                var loading = '<div>数据提交中，请稍后...</div>';
                                    loading += '<img src="'+skinBaseUrl+'/img/result-loading.gif">';
                                $("#buy_popup .buy_con").html(loading);
                                $("#buy_popup .buy_con").css({'textAlign':'center','padding':'20px'});
                                $.getContent(apiBaseUrl+'/pay/quick-pay',{'pid':pid,'num':num,'ppwd':pwd},'quickPayResult');
                            })
                        }else if(json.code == "0"){
                             $('#ppwd').siblings('s').addClass('tips_txt_Wrong');
                             $('#confirm-pwd').parents('p').addClass('f-grayButton');
                        }
                        checkPwd = json.code;
                    })
                };
            });
            return false;
        };
        var loading = '<div>数据提交中，请稍后...</div>';
            loading += '<img src="'+skinBaseUrl+'/img/result-loading.gif">';
        $("#buy_popup .buy_con").html(loading);
        $("#buy_popup .buy_con").css({'textAlign':'center','padding':'20px'});
        $.getContent(apiBaseUrl+'/pay/quick-pay',{'pid':pid,'num':num},'quickPayResult');
    })
}

function needMoney(v,money){
    $("#buy_popup .balance").remove();
    if (parseInt(v) > parseInt(money)) {
        var balance = '<div class="balance">';
            balance += '<p>总需支付<i>'+v+'.00</i>元</p>';
            balance += '<p>您的帐户余额不足，<a class="blue" href="'+memberBaseUrl+'/recharge/index" target="_blank">立即充值 >></a></p>';
            balance += '</div>';
        $("#buy_popup .hui:eq(0)").append(balance);
        $("#confirm-cayment").text('使用其他方式支付').click(function(){
            $("#buy_popup").remove();
        });
    }else{
        $("#confirm-cayment i").text(v);
    }
}

function success_quickPayResult(json){
    if (json.code != 100) {
        if (json.code == '10099') {
            var failHtml = '<p>支付密码错误，请重试！</p>';
                failHtml += '<a style="display: inline-block;background: #ff500b;width: 138px;height: 40px;text-align: center;line-height: 40px;color: #fff;border-radius: 3px;margin: auto;margin-top: 30px;" href="javascript:;" class="buyclose">确定</a>';
            $("#buy_popup .buy_con").html(failHtml);
        }else{
            addNotice(json.code);
        }
    }else{
        var status = getQuickPayResult(json.order);
        if (status == 0) {
            var tid = setInterval(function(){
                status = getQuickPayResult(json.order);
                if (status > 0) {
                    clearInterval(tid);
                };
            },2000);
        };
    }
}

function getQuickPayResult(orderId){
    $.getJsonp(apiBaseUrl+'/pay/result',{'o':orderId},function(json){
        if(json.code == 100 && (json.success.length > 0 || json.some.length > 0)){
            var periodId = json.success.length > 0 ? json.success[0].period_id : json.some[0].period_id;
            var successHtml = '<p class="chg">支付成功！请耐心等待揭晓结果 !</p>';
                successHtml += '<a href="'+memberBaseUrl+'/default/buy-detail?id='+periodId+'" style="display: inline-block;width: 138px;height: 38px;text-align: center;line-height: 38px;border: 1px solid #e2e2e2;border-radius: 3px;margin: auto;margin: 30px 6px 0;">查看伙购记录</a>';
                successHtml += '<a href="javascript:;" class="buyclose" style="display: inline-block;width: 138px;height: 38px;text-align: center;line-height: 38px;border: 1px solid #e2e2e2;border-radius: 3px;margin: auto;margin: 30px 6px 0;">继续伙购</a>';
            $("#buy_popup .buy_con").html(successHtml);   
        }else if (json.code == 201 || json.fail.length > 0) {
            var failHtml = '<p class="shb">支付失败，请重试！</p>';
                failHtml += '<a style="display: inline-block;background: #ff500b;width: 138px;height: 40px;text-align: center;line-height: 40px;color: #fff;border-radius: 3px;margin: auto;margin-top: 30px;" href="javascript:;" class="buyclose">确定</a>';
            $("#buy_popup .buy_con").html(failHtml);
        }
        $(".buyclose").click(function(){
            $("#buy_popup").remove();
        })
        return json.code;
    });      
}
