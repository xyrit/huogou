/**
 * Created by jun on 15/11/26.
 */
$(function () {

    $.getJsonp(apiBaseUrl + '/cart/list', {'check':'1',isbuy: 1}, function (json) {
        if (json.list.length == 0) {
            window.location.href = '/cart.html';
            return;
        }
        createGoodsListHtml(json);
        renderPayInfo();

        $('#btnPay').on('click', function () {
            var ths = $(this);
            if (ths.hasClass('grayBtn')) {
                return false;
            }
            ths.text('正在提交支付...');
            ths.addClass('grayBtn');
            var payPoints = parseInt($('#hidPayPoints').val());
            var payMoney = parseInt($('#hidPayMoney').val());
            var payBank = parseInt($('#hidPayBank').val());
            var payWay = $('#hidPayWay').val();
            if (payBank > 0) {
                var payName = $('#hidPayName').val();
            } else {
                payName = 'balance';
                payWay = '';
            }

            var submitPay = function(ppwd) {
                var data = {
                    payType: 'consume',
                    payName: payName,
                    payBank: payWay,
                    integral: payPoints,
                    payMoney: payBank,
                    userSource: '2',
                    ppwd:ppwd
                };
                $.post(weixinBaseUrl + '/payapi/create-order', data, function (json) {

                    changePayBtnState(true);

                    if (json.code == 100) {
                        var orderId = json.order;
                        if (payName == 'balance') {
                            window.location.href = weixinBaseUrl + '/shopok.html?o=' + orderId;
                        } else if (payName == 'platform') {
                            if (payWay == 'zhifukachat') {
                                window.location.href = 'weixinpay.html?o=' + orderId;
                            } else if (payWay == 'iapp') {
                                window.location.href = wwwBaseUrl + '/iapppay.html?o=' + orderId;
                            } else if (payWay == 'jd') {
                                window.location.href = wwwBaseUrl + '/jdpay.html?o=' + orderId;
                            } else if (payWay == 'union') {
                                window.location.href = wwwBaseUrl + '/unionpay.html?o=' + orderId;
                            }
                        } else if (payName == 'credit') {
                            if (payWay == '308') {
                                window.location.href = 'chinabankpay.html?o=' + orderId;
                            }
                        }
                        return;
                    } else {
                        if (typeof json.message != 'undefined') {
                            $.PageDialog.fail(json.message);
                        }
                    }
                });
            }


            checkPpwd(payPoints,payMoney,payBank,submitPay);

            return;

        });
    });

});


function createGoodsListHtml(json) {
    var html = '';
    var totalMoney = 0;
    $.each(json.list, function (i, v) {
        var goodsName = v.name;
        var periodNumber = v.period_number;
        var periodId = v.period_id;
        var productId = v.product_id;
        var productUrl = createGoodsUrl(productId);
        var oldPeriodId = v.old_period_id;
        var goodsImgUrl = createGoodsImgUrl(v.picture, photoSize[1], photoSize[1]);
        var buyNum = parseInt(v.nums);
        var limitNum = v.limit_num;
        var buyUnit = v.buy_unit;
        totalMoney += buyNum;

        html += '<li >';
        html += '<a href="' + productUrl + '">';
        html += '<span>';
        html += '<img src="' + goodsImgUrl + '" border="0" alt="" />';
        html += '</span>';
        html += '<dl>';
        html += '<dt>';
        if (limitNum > 0) {
            html += '<em class="purchase-icon">限购</em> ' + goodsName + '';
        } else if (buyUnit==10) {
            html += '<em class="sbei-icon">十元</em> ' + goodsName + '';
        } else {
            html += ' ' + goodsName + '';
        }
        html += '</dt>';
        html += '<dd>';
        html += '<em>' + buyNum + '</em>人次/';
        html += '<em>' + buyNum + '伙购币</em>';
        html += '</dd>';
        html += '</dl>';
        html += '</a>';
        html += '</li>';
    });

    $('.g-pay-lst ul').html(html);
    //购物车商品折叠
    if (json.list.length>4) {
        $('.g-pay-lst ul li:gt(3)').hide();
        $('#a_exp').show();
        $('#a_exp').click(function() {
            if ($(this).hasClass('pay_pack')) {
                $('.g-pay-lst ul li').show("slow")
                $(this).removeClass('pay_pack')
            } else{
                $('.g-pay-lst ul li:gt(3)').hide();
                $(this).addClass('pay_pack')
            }
        });
    }
    $('#hidShopMoney').val(totalMoney);
    $('.g-pay-lst .orange').html('<i></i>' + totalMoney + '伙购币</em>');
}

function renderPayInfo() {
    $.getJsonp(apiBaseUrl + '/user/getmoney', {}, function (json) {

        $('#hidPpwd').val(parseInt(json.ppwd));
        $('#hidPfree').val(parseInt(json.free));

        point = parseInt(json.point);
        money = parseInt(json.money);
        shopMoney = $('#hidShopMoney').val();
        canPayPointsMoney = Math.floor(point / 100);

        $('#hidMoney').val(money);
        $('#hidPoints').val(point);

        $('.other_pay a:eq(0)').html('<i></i>福分抵扣：<span class="gray9">(可用福分' + point + ')</span><em class="orange fr"></em>');
        $('.other_pay a:eq(1)').html('<i></i>余额支付：<span class="gray9">(余额' + money + '伙购币)</span><em class="orange fr"></em>');

        bindPaymentWay();

        if (canPayPointsMoney - shopMoney >= 0) {
            payPoints = shopMoney * 100;
            payMoney = 0;
            payBank = 0;
            renderPaypointHtml(payPoints);

            $('#hidPayMoney').val(payMoney);
            $('#hidPayPoints').val(payPoints);
            $('#hidPayBank').val(payBank);
        } else if (canPayPointsMoney + money - shopMoney >= 0) {
            payPoints = canPayPointsMoney * 100;
            payMoney = shopMoney - canPayPointsMoney;
            payBank = 0;


            renderPaypointHtml(payPoints);
            renderPaymoneyHtml(payMoney);

            $('#hidPayMoney').val(payMoney);
            $('#hidPayPoints').val(payPoints);
            $('#hidPayBank').val(payBank);
        } else if (canPayPointsMoney + money - shopMoney < 0) {
            payPoints = canPayPointsMoney * 100;
            payMoney = money;
            payBank = shopMoney - (canPayPointsMoney + money);

            renderPaypointHtml(payPoints);
            renderPaymoneyHtml(payMoney);
            renderPaybankHtml(payBank);

            $('#hidPayMoney').val(payMoney);
            $('#hidPayPoints').val(payPoints);
            $('#hidPayBank').val(payBank);
        }
    });
}

function checkPpwd(payPoint,payMoney,payBank,func) {

    var usePpwd = $('#hidPpwd').val()==1 ? true : false;
    var free = $('#hidPfree').val();

    if (!usePpwd || (usePpwd && free > parseInt(payPoint/100)+payMoney) || (usePpwd && parseInt(payPoint/100)+payMoney==0 && payBank>0)) {
        func('');
        return;
    }

    var m = ["", "", "", "", "", "", ""];
    var y;
    var v = function (N) {
        var T = 0;
        var P;
        var R = function (V, U) {
            return Math.random() > 0.5 ? -1 : 1
        };
        var K = function () {
            var V = '<div class="pop-pay-pwd clearfix">';
            V += "<dl>";
            V += '<dt class="gray3">请输入支付密码</dt>';
            V += '<dd id="ddpwd">';
            V += "<span><em></em></span><span><em></em></span><span><em></em></span><span><em></em></span><span><em></em></span><span><em></em></span>";
            V += "</dd>";
            V += "</dl>";
            V += '<ul class="pop-pay-pwd clearfix" id="input_pwd">';
            var X = [];
            for (var W = 0; W < 10; W++) {
                X[W] = '<li type="num"><a href="javascript:;">' + W + "</a></li>"
            }
            var Y = X.sort(R);
            var U = Y.length - 1;
            var Z = Y[U];
            Y.splice(U, 1, '<li><a id="a_close" >关闭</a></li>', Z);
            V += Y.join("");
            V += '<li><a id="a_del" >清除</a></li>';
            V += "</ul>";
            V += "</div>";
            return V
        };
        var S = function () {
            if (/^\d{6}$/.test(m.join(""))) {
                if (typeof(N) != "undefined" && N != null) {
                    N();
                    return true
                }
            }
            return false
        };
        var Q;
        var L = function (U) {
            if (T > 5) {
                return false
            }
            var V = parseInt(U.text());
            if (V >= 0 && V < 10) {
                Q.eq(T).html("●");
                m[T] = V;
                T++
            }
            S()
        };
        var M = function () {
            payFun.resetFun()
        };
        var O = function () {
            y = $("#pageDialog");
            var V = $("#a_close", y);
            var U = $("#a_del", y);
            Q = $("#ddpwd", y).find("em");
            payFun.resetFun();
            $("#input_pwd", y).children('li[type="num"]').each(function () {
                $(this).bind("click", function () {
                    L($(this))
                })
            });
            V.click(function () {
                P.cancel()
                changePayBtnState(true,'立即支付');
            });
            U.click(function () {
                M()
            })
        };
        this.closeFun = function () {
            if (P) {
                P.close()
            }
        };
        this.initFun = function () {
            var V = $(document.body).width() > 900 ? 900 : $(document.body).width();
            var U = V * 0.8;
            P = new $.PageDialog(K(), {W: U, H: 335, close: true, autoClose: false, ready: O})
        };
        this.resetFun = function () {
            T = 0;
            $(m).each(function (U) {
                m[U] = "";
                Q.eq(U).html("")
            })
        }
    };

    var W = function () {
        var Y = m.join("");
        if (Y.length == 6) {
            payFun.closeFun();

            $.getJsonp(apiBaseUrl + '/pay/check-ppwd', {pwd: Y}, function (json) {
                if (json.code == 1) {
                    func(Y);
                } else {
                    $.PageDialog.fail('支付密码错误');
                    changePayBtnState(true);
                }
            });
        }
    };
    payFun = new v(W);
    payFun.initFun()
}

function changePayBtnState(K, L) {
    var B = $('#btnPay');
    if (K) {
        a = false;
        B.removeClass("grayBtn").html("立即支付");
    } else {
        a = true;
        B.addClass("grayBtn").html(L);
    }
}

function bindPaymentWay() {
    $('.other_pay .net-pay a').on('click', function () {
        var ths = $(this);
        var payWay = ths.attr('bank');
        $('#hidPayWay').val(payWay);
        var payName = ths.attr('payName');
        $('#hidPayName').val(payName);
        $('.other_pay .net-pay a').removeClass('checked');
        ths.addClass('checked');
    });

    $('.other_pay a:eq(0)').on('click', function () {
        var ths = $(this);
        var money = parseInt($('#hidMoney').val());
        var points = parseInt($('#hidPoints').val());
        var payPoints = parseInt($('#hidPayPoints').val());
        var payMoney = parseInt($('#hidPayMoney').val());
        var payBank = parseInt($('#hidPayBank').val());
        var shopMoney = parseInt($('#hidShopMoney').val());
        var canPayPointsMoney = Math.floor(points / 100);

        if (!ths.hasClass('checked')) {
            if (points <= 0) {
                $.PageDialog.fail('福分不足支付');
                return;
            } else {
                if (canPayPointsMoney <= 0) {
                    $.PageDialog.fail('福分不足支付');
                    return;
                }
                if (payMoney > 0) {
                    if (canPayPointsMoney - shopMoney >= 0) {
                        payPoints = shopMoney * 100;
                        payMoney = 0;
                        payBank = 0;
                    } else if (canPayPointsMoney + payMoney - shopMoney >= 0) {
                        payPoints = canPayPointsMoney * 100;
                        payMoney = shopMoney - canPayPointsMoney;
                        payBank = 0;
                    } else if (canPayPointsMoney + payMoney - shopMoney < 0) {
                        payPoints = canPayPointsMoney * 100;
                        payMoney = payMoney;
                        payBank = shopMoney - (canPayPointsMoney + payMoney);
                    }
                } else {
                    if (canPayPointsMoney - shopMoney >= 0) {
                        payBank = 0;
                        payPoints = shopMoney * 100;
                    } else {
                        payBank = shopMoney - canPayPointsMoney;
                        payPoints = canPayPointsMoney * 100;
                    }
                }
            }

        } else {
            if (money>=shopMoney) {
                payMoney = shopMoney;
                payBank = 0;
                payPoints = 0;
            } else {
                payBank = shopMoney - payMoney;
                payPoints = 0;
            }
        }
        $('#hidPayPoints').val(payPoints);
        $('#hidPayBank').val(payBank);
        $('#hidPayMoney').val(payMoney);
        renderPaypointHtml(payPoints);
        renderPaybankHtml(payBank);
        renderPaymoneyHtml(payMoney);
        $('#btnPay').removeClass('grayBtn');
    });

    $('.other_pay a:eq(1)').on('click', function () {
        var ths = $(this);
        var money = parseInt($('#hidMoney').val());
        var points = parseInt($('#hidPoints').val());
        var payPoints = parseInt($('#hidPayPoints').val());
        var payMoney = parseInt($('#hidPayMoney').val());
        var payBank = parseInt($('#hidPayBank').val());
        var shopMoney = parseInt($('#hidShopMoney').val());
        if (!ths.hasClass('checked')) {
            if (money <= 0) {
                $.PageDialog.fail('余额不足支付');
                return;
            } else {
                if (payPoints > 0) {
                    var payPointsMoney = parseInt(payPoints / 100);
                    if (money - shopMoney >= 0) {
                        payMoney = shopMoney;
                        payPoints = 0;
                        payBank = 0;
                    } else if (money + payPointsMoney - shopMoney >= 0) {
                        payMoney = money;
                        payPoints = (shopMoney - money) * 100;
                        payBank = 0;
                    } else if (money + payPointsMoney - shopMoney < 0) {
                        payMoney = money;
                        payPoints = payPoints;
                        payBank = shopMoney - (money + payPointsMoney);
                    }
                } else {
                    if (money - shopMoney >= 0) {
                        payMoney = shopMoney;
                        payBank = 0;
                    } else {
                        payMoney = money;
                        payBank = shopMoney - money;
                    }
                }
                $('#hidPayPoints').val(payPoints);
                renderPaypointHtml(payPoints);
            }
        } else {
            payBank = shopMoney - Math.floor(payPoints / 100);
            payMoney = 0;
        }

        $('#hidPayMoney').val(payMoney);
        $('#hidPayBank').val(payBank);
        renderPaymoneyHtml(payMoney);
        renderPaybankHtml(payBank);
        $('#btnPay').removeClass('grayBtn');
    });

    $('.other_pay a:eq(2)').on('click', function () {

        var payMoney = parseInt($('#hidPayMoney').val());
        var payPoints = parseInt($('#hidPayPoints').val());
        var shopMoney = parseInt($('#hidShopMoney').val());

        if ($('.other_pay .net-pay').css('display') == 'block') {
            $('.other_pay .net-pay').hide();
            var payBank = 0;
            $('#hidPayBank').val(payBank);
            renderPaybankHtml(payBank);
            if (payMoney + payPoints / 100 + payBank - shopMoney < 0) {
                $('#btnPay').addClass('grayBtn');
            } else {
                $('#btnPay').removeClass('grayBtn');
            }
        } else {
            $('.other_pay .net-pay').show();
            var payBank = shopMoney - (payMoney + payPoints / 100);
            if (payBank == 0) {
                payBank = shopMoney;
                payMoney = 0;
                payPoints = 0;
            }

            $('#hidPayMoney').val(payMoney);
            $('#hidPayPoints').val(payPoints);
            $('#hidPayBank').val(payBank);
            renderPaymoneyHtml(payMoney);
            renderPaypointHtml(payPoints);
            renderPaybankHtml(payBank);
            $('#btnPay').removeClass('grayBtn');
        }
    });



}

function renderPaypointHtml(payPoints) {
    if (payPoints > 0) {
        $('.other_pay a:eq(0)').addClass('checked');
        $('.other_pay a:eq(0) .orange').html('-<b></b>' + (payPoints / 100) + '伙购币');
    } else {
        $('.other_pay a:eq(0)').removeClass('checked');
        $('.other_pay a:eq(0) .orange').html('');
    }
}

function renderPaymoneyHtml(payMoney) {
    if (payMoney > 0) {
        $('.other_pay a:eq(1)').addClass('checked');
        $('.other_pay a:eq(1) .orange').html('-<b></b>' + payMoney + '伙购币');
    } else {
        $('.other_pay a:eq(1)').removeClass('checked');
        $('.other_pay a:eq(1) .orange').html('');
    }
}

function renderPaybankHtml(payBank) {
    if (payBank > 0) {
        $('.other_pay .net-pay').show();
        if ($('.other_pay .net-pay a.checked').length == 0) {
            $('.other_pay .net-pay a:eq(0)').click();
        }else {
            $('.other_pay .net-pay a.checked').click();
        }
        $('.other_pay a:eq(2) .orange').html('<span class="colorbbb">需要支付&nbsp;</span><b></b>' + payBank + '伙购币');
    } else {
        $('.other_pay .net-pay').hide();
        $('.other_pay a:eq(2) .orange').html('');
    }
}

