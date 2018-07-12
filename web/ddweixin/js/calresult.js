/**
 * Created by jun on 15/11/21.
 */
$(function() {


    var periodId = getPeriodIdByUrl(window.location.href);

    var url = apiBaseUrl+'/period/compute';
    var data = {"pid":periodId};
    $.getJsonp(url, data, function(json) {
        createComputeListHtml(json);
    });

    var url = apiBaseUrl+'/period/info';
    var data = {"id":periodId};
    $.getJsonp(url, data, function(json) {
        createComputeResultHtml(json);
    });

    $('#dt_showmore').on('click', function() {
        var isUp = $(this).find('cite').hasClass('up');
        if (isUp) {
            $('#dl_nginner').css({'height':'350px'});
            $(this).html('展开全部100条数据 <cite ><b></b></cite>');
        } else {
            $('#dl_nginner').css({'height':'3500px'});
            $(this).html('收起 <cite class="up"><b></b></cite>');
        }
    });


});

function createComputeResultHtml(json) {
    var periodInfo = json.periodInfo;
    var luckCode = periodInfo.lucky_code;
    var endTime = periodInfo.end_time;
    var peopleNum = parseInt(periodInfo.price);
    var totalDataSum = $('#hidBaseNum').val();
    var html = '';
    html += '<div class="for-con1 z-oval clearfix">';
    html += '<em class="orange">'+luckCode+'</em>';
    html += '<i class="colorbbb">最终计算结果</i>';
    html += '</div>';
    html += '<p></p>';
    html += '<div class="for-con2 clearfix">';
    html += '<cite>(</cite>';
    html += '<span class="z-oval"><em class="orange">'+totalDataSum+'</em><i class="colorbbb">时间取值之和</i></span>';
    html += '<cite>%</cite>';
    html += '<span class="z-oval"><em class="orange">'+peopleNum+'</em><i class="colorbbb">商品总需人次</i></span>';
    html += '<cite>)</cite>';
    html += '<cite>+</cite>';
    html += '<span class="z-oval"><em class="orange">10000001</em><i class="colorbbb">固定数值</i></span>';
    html += '</div>';
    html += '<div class="orange z-and">';
    html += '截止该商品最后购买时间【'+endTime+'】';
    html += '<br />网站所有商品的最后100条购买时间取值之和';
    html += '  <a id="a_showway" href="javascript:;" class="orange">如何计算<i class="z-set"></i></a>';
    html += '</div>';

    $('.g-formula').html(html);


    $("#a_showway").on("click", function (u) {
        stopBubble(u);
        var s = function () {
            var w = "";
            w += '<div id="div_container" class="acc-pop clearfix z-box-width">';
            w += '<a id="a_cancle" href="javascript:;" class="z-set box-close" style="color:#999"></a>';
            w += "<dl>";
            w += '<dt class="gray6">如何计算？</dt>';
            w += "<dd>1、取该商品最后购买时间前网站所有商品的最后100条购买时间记录；</dd>";
            w += "<dd>2、按时、分、秒、毫秒排列取值之和，除以该商品总参与人次后取余数；</dd>";
            w += "<dd>3、余数加上10000001 即为“幸运夺宝码”；</dd>";
            w += "<dd>4、余数是指整数除法中被除数未被除尽部分， 如7÷3 = 2 ......1，1就是余数。</dd>";
            w += "</dl>";
            w += "</div>";
            return w
        };
        var v = function () {
            _DialogObj = $("#pageDialog");
            $("#a_cancle", _DialogObj).click(function (w) {
                t.cancel()
            });
            $("#div_container", _DialogObj).click(function (w) {
                stopBubble(w)
            });
            $("#pageDialogBG").click(function () {
                t.cancel()
            })
        };
        var t = new $.PageDialog(s(), {W: 290, H: 257, close: true, autoClose: false, ready: v})
    });
}

function createComputeListHtml(json) {
    var html = '';
    $.each(json.list, function(i,v) {
        var buyTime = v.buy_time;
        var buyTimeArr = buyTime.split(' ');
        var timeData = v.data;
        var buyNum = v.buy_num;
        var userName = v.username;
        var userHomeId = v.home_id;
        var userCenterUrl = createUserCenterUrl(userHomeId);

        html += '<dd>';
        html += '<span>'+buyTimeArr[0]+'<b></b></span>';
        html += '<span>'+buyTimeArr[1]+'<s></s></span>';
        html += '<span><i><em></em></i>'+timeData+'<s></s></span>';
        html += '<span><a href="'+userCenterUrl+'">'+userName+'</a></span>';
        html += '</dd>';
    });

    $('#dl_nginner').html(html);
    $('#hidBaseNum').val(json.total);
}

function getPeriodIdByUrl(url) {
    var periodId = '';
    var s = '/calresult-([0-9]+)\.html';
    var reg = new RegExp(s);
    var r = url.match(reg);
    if (r != null) {
        periodId = r[1];
    }
    return periodId;
}
