$(document).ready(function(){
    $('.password-list').find('li').on('click', function(){
        var tMoney = $(this).attr('money');
        $(this).addClass('act').siblings().removeClass('act');
        $(this).find('input').attr("checked","checked");
        $(this).siblings().find('input').removeAttr('checked');

        var i = $('input:radio[name="bank"]:checked').val();
        $('.tMoney').html(tMoney+".00");
        $('input[name="payMoney"]').val(tMoney);
        $('input[name="payBank"]').val(i);

        if(""!=$("#password-jine").val() && 0== tMoney){
            tMoney = $("#password-jine").val();
        }

        //if(!$('#password-jine').is(":checked")){
        //    $('#password-jine').val('输入其他金额');
        //}
    }).eq(0).trigger('click');

    var passwordInput = $('#password-jine').val();

    $('#password-jine').on('focus',function(){
        if ($(this).val() == '输入其他金额') {
            $(this).val("");
        }
    }).on('blur',function(){
        var money = $(this).val();
        if (money=="" || parseInt(money)<=0) {
            $(this).val("输入其他金额");
        }
    })
    $('#password-jine').on('keyup',function(){

        var money = $(this).val();
        money = parseInt(money);
        if (!(money>0)) {
            money = 0;
        }
        $(this).val(money)
        $('input[name="payMoney"]').val(money);
        $('.tMoney').html($(this).val()+".00");
    })


    $('.cyber-bank-con-list li').on('click',function(){
        $('.cyber-bank-con-list li').removeClass('act');
        $(this).addClass('act').siblings().removeClass('act');
        var i = $('input:radio[name="bank"]:checked').val();
        $('input[name="payBank"]').val(i);
    })

    $('.cyber-bank-title-list').find('li').on('click', function(){
        $(this).addClass('act').siblings().removeClass('act');
        $('.cyber-bank-con-list').find('li').removeClass('act').find('input').removeAttr('selected');
        if ($('.terrace_con_list li[class=act]').length == 0 ) {
            $('.cyber-bank-con-list').stop().hide().eq($(this).index()).fadeIn().find('li:first').addClass('act').find('input').attr('selected','selected');
        };
        $('input[name="payName"]').val($(this).attr('data-id'));
        if($(this).index() == 2){
            $('input[name="payBank"]').val('1027');
        }else if($(this).index() == 1){
            $('input[name="payBank"]').val('1025');
        }else{
            $('input[name="payBank"]').val('zhifukachat');
        }
    }).eq(0).trigger('click');
})

$('#subBtn').click(function(){
    var money = $('input[name="payMoney"]').val();
    var payName = $('input[name="payBank"]').val();
    if(money == 0 || money == '0.00'){
        $('.safety-b-box h3').html('金额不能为零');
        $('#safety-b-con').fadeIn();
        $('#safety-b-close').on('click',function(){
            $('#safety-b-con').fadeOut();
        });return false;
    }else if(isNaN(money)){
        $('.safety-b-box h3').html('金额必须为整数');
        $('#safety-b-con').fadeIn();
        $('#safety-b-close').on('click',function(){
            $('#safety-b-con').fadeOut();
        });return false;
    }else{
        $(".succeed_con").show();
    }
})

$('.succeed_con .close').click(function(){
    $(".succeed_con").hide();
    window.location.reload();
})
$('.succeed_con .pay').click(function(){
    $(".succeed_con").hide();
    window.location.reload();
})


//转账
$(function(){
    var apiBaseUrl = 'http://api.'+ baseHost;
    var memberBaseUrl = 'http://member.'+ baseHost;

    //判断账户是否存在
    /*$('input[name="username"]').blur(function(){
        var username = $(this).val();
        if(username == ''){
            $('.safety-b-box h3').html('转账账号不能为空');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },1000)
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }
        var apply_url = memberBaseUrl + '/recharge/check-username'
        $.post(apply_url, {'username':username}, function(data){
            if(data == 0){
                $('.safety-b-box h3').html('该账号不存在');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                });return false;
            }
        })
    })*/

    //提交转账数据
    $('#sub').click(function(){
        var username = $('input[name="username"]').val();
        var account = $('input[name="account"]').val();
        var paypwd = $('input[name="paypwd"]').val();
        var box = $('input[name="box"]').val();

        if(!(username && account/* && paypwd*/)){
            $('.safety-b-box h3').html('必填项不能为空');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },1000)
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }else if(!(parseInt(account) == account && account > 0 && account[0] > 0)){
            $('.safety-b-box h3').html('金额必须为正整数');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },1000)
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            });return false;
        }
        $(this).attr('disabled', 'disabled');
        var params = {
            'username': $('input[name="username"]').val(),
            'account' : $('input[name="account"]').val(),
            'comment' : $('input[name="comment"]').val(),
            //'paypwd' : $('input[name="paypwd"]').val(),
        }

        $.ajax({
            async: false,
            url: apiBaseUrl + '/recharge/transfer',
            type: "POST",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: params,
            success: function (data) {
                if(data.code == 100){
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                    $('#safety-b-con').fadeIn();
                    $('#safety-b-close').on('click',function(){
                        $('#safety-b-con').fadeOut();
                    });
                    setTimeout(function(){
                        if (box == 1) {
                            self.location = 'http://member.' + baseHost + '/recharge/money-log?index=2';
                            return true;
                        }
                        self.location= 'http://member.'+baseHost;
                    },1000)
                }else{
                    $('.safety-b-box h3').html(data.msg);
                    $('#safety-b-con').fadeIn();
                    $('#safety-b-close').on('click',function(){
                        $('#safety-b-con').fadeOut();
                    });
                    $('#sub').removeAttr("disabled");
                    return false;
                }
            }
        });
    })
})

//充值卡激活
$('input[name="card"]').blur(function(){
    var card = $(this).val();
    var applyUrl = 'http://api.'+baseHost+'/recharge/check-card';
    var params = {'card':card};
    $.ajax({
        async: false,
        url: applyUrl,
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (data) {
            if(data.code == 100){
                $('.money').html(''+data.msg+' 伙购币');
            }else if(data.code == 101){
                $('.cardMsg').show().html('<em></em>'+ data.msg );
                return false;
            }
        }
    });
})

$('#cardBtn').click(function(){
    var password = $('input[name="password"]').val();
    var card = $('input[name="card"]').val();
    if(!(password && card)){
        $('.safety-b-box h3').html('必填项不能为空');
        $('#safety-b-con').fadeIn();
        $('#safety-b-close').on('click',function(){
            $('#safety-b-con').fadeOut();
        });return false;
    }

    var applyUrl = 'http://api.'+baseHost+'/recharge/card';
    $.ajax({
        async: false,
        url: applyUrl,
        type: "POST",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: {'card':card, 'password':password},
        success: function (data) {
            if(data.code == 100){
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                $('#safety-b-con').fadeIn();
                $('#safety-b-close').on('click',function(){
                    $('#safety-b-con').fadeOut();
                })
                setTimeout(function(){
                    self.location= 'http://member.'+baseHost;
                },1000)
            }else if(data.code == 101){
                $('.cardPwdMsg').show().html('<em></em>'+ data.msg );
                return false;
            }
        }
    });
})

var moneylog_condition = {
    page: 1,
    perpage: 10,
    region: 4,
    start_time: "",
    end_time: "",
    type: 0,
    token: token
};

function moneyLog_gotoPage(currentPage) {
    if (moneylog_condition.page != currentPage) {
        moneylog_condition.page = currentPage;
        getMoneyLog();
    }
}

function getMoneyLog() {
    $.getContent(apiBaseUrl + '/recharge/money-log', moneylog_condition, 'moneyLog');
}

function moneyLog_filt() {
    $(".acc-info-list").find("a").each(function(type) {
        $(this).on("click",
            function() {
                $(this).addClass("act").siblings().removeClass("act");
                moneylog_condition.start_time = "";
                moneylog_condition.end_time = "";
                moneylog_condition.page = 1;
                moneylog_condition.type = type;
                getMoneyLog();
            })
    });

    $(".remind").find("a").each(function(status) {
        $(this).on('click', function() {
            moneylog_condition.start_time = "";
            moneylog_condition.end_time = "";
            moneylog_condition.page = 1;
            moneylog_condition.status = status;
            getMoneyLog();
        })
    });

    $(".screening").find("a").each(function(region) {
        $(this).on("click",
            function() {
                $(this).addClass("act").siblings().removeClass("act");
                moneylog_condition.start_time = "";
                moneylog_condition.end_time = "";
                moneylog_condition.page = 1;
                moneylog_condition.region = region;
                getMoneyLog();
            })
    });

    $(".screening").find("input[type=submit]").bind("click", function() {
        if ($("#acc-rl").val() && $("#acc-rl-2").val()) {
            moneylog_condition.start_time = "";
            moneylog_condition.end_time = "";
            moneylog_condition.page = 1;
            moneylog_condition.start_time = $("#acc-rl").val();
            moneylog_condition.end_time = $("#acc-rl-2").val();
            getMoneyLog();
        }
    });
}

function success_moneyLog(json) {
    if (moneylog_condition.type == 0) {
        $(".acc-info-table").find("thead").html('<tr><td class="left" width="40%">充值时间</td><td width="40%">充值金额</td><td width="20%">资金渠道</td></tr>');
        $(".total").html('充值总额：<i>' + json.totalMoney + ' 伙购币</i>');

    } else if (moneylog_condition.type == 1) {
        $(".acc-info-table").find("thead").html('<tr><td class="left" width="40%">消费时间</td><td width="40%">消费金额</td><td width="20%">资金渠道</td></tr>');
        $(".total").html('消费总额：<i>' + json.totalMoney + ' 伙购币</i>');
    } else {
        $(".acc-info-table").find("thead").html('<tr><td class="left" width="30%">转账时间</td><td width="20%">转账类型</td><td width="20%">转账金额</td><td width="30%">对方账号</td></tr>');
        $(".total").html('转入：<i>' + json.totalInMoney + ' 伙购币</i>  ' + '转出：<i>' + json.totalOutMoney + ' 伙购币</i>');
    }

    $(".acc-info-table").find("tbody").html("");
    var strHtml = '';
    $.each(json.list, function (i, v) {
        strHtml += '<tr>';
        strHtml += '<td class="left">' + v.pay_time + '</td>';
        if (moneylog_condition.type == 2) {
            strHtml += '<td>' + v.payment + '</td>';
        }

        if (v.money < 0) {
            strHtml += '<td class="orange">-' + Math.abs(v.money) + ' 伙购币</td>';
        } else {
            strHtml += '<td class="green">+' + Math.abs(v.money) + ' 伙购币</td>';
        }

        if (moneylog_condition.type == 0 || moneylog_condition.type == 1) {
            strHtml += '<td>' + v.payment + '</td>';
        } else {
            strHtml += '<td>' + v.to_account + '</td>';
        }

        strHtml += '</tr>';
    });
    $(".acc-info-table").find("tbody").append(strHtml);
    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: moneylog_condition.page,
            downPage: 1,
            gotoPage: 'moneyLog_gotoPage'
        });
    } else {
        $(".pagination").html("");
    }

    if (json.totalCount == 0) {
        $(".acc-info-table").find("tbody").append('<tr style="border-bottom: 0;"><td colspan="5"><div class="notHave"><span class="notHave_icon"></span><p class="notHave_txt">暂无记录</p></div></td></tr>');
    }
}