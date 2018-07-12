$(function(){
     var tabs = $("#divNav a");                  //标签
    var list = $(".g-accounts-details");        // 内容
    tabs.click(function () {                              //标签的单击事件
        var index = tabs.index(this);
        tabs.removeClass("z-checked").eq(index).addClass("z-checked");
        list.hide().eq(index).show();
        //实现标签动作之后 兼容分页获取
        if (!this.page)  this.page = 1;
        if (this.page == this.loadpage) return;
        this.loadpage = this.page;
        //加载内容
        list.eq(index).find(".loading").show();

        $.getJsonp(apiBaseUrl + '/recharge/money-log', {
            page: this.page,
            type: [1, 0, 2][index],
            perpage: 20,
            region: 4
        }, function (json) {
            list.eq(index).find(".loading").hide();
            var strHtml = "";
            $(json.list).each(function (i, p) {
                switch (index) {
                    case 1:
                        strHtml += "<dd>          <span>" + p.pay_time.substr(0, 19) + "</span>             <span>" + p.post_money + " 伙购币</span>  <span>" + p.payment + "</span>      </dd>";  //payment
                        break;
                    case 0:
                        strHtml += "<dd>          <span>" + p.pay_time.substr(0, 19) + "</span>             <span>" + p.money * -1 + " 伙购币</span>         </dd>";
                        break;
                    case 2:
                        strHtml += "<dd>          <span>" + p.pay_time + "</span>              <span>" + p.money + " 伙购币</span>   <span>" + p.to_account + "[" + p.payment + "]</span>      </dd>";
                        break;
                }
            });
            list.eq(index).find("dl").append(strHtml);        //设置内容
            //设置总量
            var totel = list.eq(index).find("h4");
            totel.html(totel.html().replace(/[\d\.]+/, json.totalMoney));
            if (index == 2) totel.html("转入总额：" + json.totalInMoney + " 伙购币<cite>转出总额：" + json.totalOutMoney + " 伙购币</cite>");
            //显示加载更多和 没有数据
            (json.totalCount < 1) ? list.eq(index).find(".noRecords").show() : list.eq(index).find(".noRecords").hide();
            (json.totalCount <= list.eq(index).find("dd").length) ? list.eq(index).find(".load_more").hide() : list.eq(index).find(".load_more").show();
        })
    });

    var tabHash = location.hash;
    switch (tabHash) {
        case '#consumption':
            tabs.eq(0).click();
            break;
        case '#recharge':
            tabs.eq(1).click();
            break;
        case '#transfer':
            tabs.eq(2).click();
            break;
        default :
            tabs.eq(0).click();
            break;
    }

    $("dl").each(function () {
        $(this).after($("#tools").html());
    });
    //加载更多
    $(".load_more").click(function () {
        $("a.z-checked")[0].page++;
        $("a.z-checked").click();
    })


    var o = $("#isSetPhone").val();
    var q = $("#isSetPayPassword").val();
    var f = $("#totel").val() > 0;
    var C = $("div.weixin-mask");
    $("#btnTransfer").click(function () {
        var D = function (G) {
            $("body").attr("style", "overflow:hidden;");
            var F = $(G);
            $("body").append(F).find("#closeDiag").click(function () {
                $("div.acc-pop").remove();
                C.hide();
                $("body").attr("style", "");
                IsMasked = false
            });
            F.css({
                top: ($(window).height() - F.height() - 44) / 2,
                left: ($(window).width() - F.width() - 24) / 2
            }).show();
            C.css("height", $(document).height() > $(window).height() ? $(document).height() : $(window).height()).show();
            IsMasked = true
        };
        if (!f) {
            var E = '<div class="acc-pop"><h3 class="gray6">您当前账户没有余额</h3><h6 class="gray9">请先充值，再进行转账操作</h6><a id="closeDiag" href="javascript:;" class="blueBtn fl">知道了</a><a id="submitDiag" href="recharge.html" class="orangeBtn fr">去充值</a></div>';
            D(E)
        } else {
            if (!o) {
                var E = '<div class="acc-pop"><h3 class="gray6">需要验证手机才能进行转账</h3><h6 class="gray9">请先验证手机，再进行转账操作</h6><a id="closeDiag" href="javascript:;" class="blueBtn fl">知道了</a><a id="submitDiag" href="mobilecheck.html" class="orangeBtn fr">去绑定</a></div>';
                D(E)
            } else {
                if (!q) {
                    var E = '<div class="acc-pop"><h3 class="gray6">需要设置支付密码才能进行转账</h3><h6 class="gray9">请先设置支付密码，再进行转账操作</h6><a id="closeDiag" href="javascript:;" class="blueBtn fl">知道了</a><a id="submitDiag" href="paypwdcheck.html" class="orangeBtn fr">去设置</a></div>';
                    D(E)
                } else {
                    window.location.href = "transfer.html";
                }
            }
        }
    })
});