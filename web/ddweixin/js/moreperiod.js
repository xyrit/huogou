/**
 * Created by jun on 15/12/8.
 */
var page = 1;
var perpage = 30;
var productId;
$(function () {
    productId = $('#hidProductID').val();
    var data = {id: productId, page: page, perpage: perpage, showinfo: true};
    $.getJsonp(apiBaseUrl + '/product/allperiodlist', data, function (json) {
        createMoreListHtml(json);
        $('#divLoading').hide();
    });

    $('#winList').on('click', 'li', function () {
        var periodId = $(this).attr('periodid');
        location.href = createPeriodUrl(periodId);
    });

    $('#txtPeriod').on('focus',function() {
        $('#btnGo').addClass('z-active');
        $(this).val('');
    }).on('blur',function() {
        $('#btnGo').removeClass('z-active');
    }).on('input',function() {
        var tpn = parseInt($(this).val());
        if (isNaN(tpn)) {
            tpn = 1;
        }
        $(this).val(tpn);
    });

    $('#btnGo').click(function () {
        var txtPeriod = $('#txtPeriod').val();
        goPeriod(txtPeriod);
    });

    var isLoading = false;
    $.onSrollBottom(function() {
        if (isLoading) {
            return;
        }
        isLoading = true;
        $('#divLoading').show();
        pageVal = parseInt(page) + 1;
        var url = apiBaseUrl+'/product/allperiodlist';
        var data = {id: productId, page: pageVal, perpage: perpage, showinfo: true};
        $.getJsonp(url, data, function (json) {
            var t = function() {
                $('#divLoading').hide();
                if (json.list.length==0) {
                    isLoading = true;
                } else {
                    page += 1;
                    isLoading = false;
                    createMoreListHtml(json);
                }
            }
            setTimeout(t,1000);
        });
    });

});

function createMoreListHtml(json) {
    var html = '';
    productId = $('#hidProductID').val();
    totalPage = json.totalPage;
    $.each(json.list, function (i, v) {
        var periodId = v.period_id;
        var periodNumber = v.period_number;
        var period_no = v.period_no;
        var status = v.status;

        if (status == 0) {
            var goodsImgUrl = createGoodsImgUrl(v.goods_picture, photoSize[1], photoSize[1]);
            var progress = parseFloat(v.progress / 100000)*100;
            html += '<li class="have-in-hand" periodid="' + periodId + '"><cite>' + period_no + '期</cite>';
            html += '<div class="win-con">';
            html += '<div class="during-pic">';
            html += '<img src="' + goodsImgUrl + '" />';
            html += '</div>';
            html += '<h4 class="orange">进行中<span class="dotting"></span></h4>';
            html += '<p class="u-progress" title="已完成' + progress + '%"><span class="pgbar" style="width:' + progress + '%;"><span class="pging"></span></span></p>';
            html += '</div>';
            html += '</li>';
        } else if (status == 1) {
            html += '<li periodid="' + periodId + '"><cite>' + period_no + '期</cite>';
            html += '<div class="win-con">';
            html += '<h4 class="orange">正在揭晓</h4>';
            html += '<div class="loading-progress">';
            html += '<span class="loading-pgbar"><span class="loading-pging"></span></span>';
            html += '</div>';
            html += '<h5 class="gray9">敬请期待</h5>';
            html += '</div>';
            html += '</li>';
        } else if (status == 2) {
            var userFaceImgUrl = createUserFaceImgUrl(v.user_avatar, avatarSize[1], avatarSize[1]);
            var username = v.user_name;
            var luckCode = v.lucky_code;
            var buyNum = v.user_buy_num;
            var raffTime = v.raff_time2;
            html += '<li periodid="' + periodId + '"><cite>' + period_no + '期</cite>';
            html += '<dl class="gray9">';
            html += '<dt>';
            html += '<img src="' + userFaceImgUrl + '" />';
            html += '</dt>';
            html += '<dd class="win-name">';
            html += '<a href="javascript:;" class="blue">' + username + '</a>';
            html += '</dd>';
            html += '<dd class="z-font-size">';
            html += '幸运码：';
            html += '<em class="orange">' + luckCode + '</em>';
            html += '</dd>';
            html += '<dd class="z-font-size">';
            html += '参与人次：';
            html += '<em class="orange">' + buyNum + '</em>';
            html += '</dd>';
            html += '<dd class="colorbbb">';
            html += raffTime;
            html += '</dd>';
            html += '</dl>';
            html += '</li>';
        }

    });

    $('#winList').append(html);

}

function goPeriod(txtPeriod) {
    $.getJsonp(apiBaseUrl+'/period/get-periodid',{'pid':productId,'pnum':txtPeriod},function(json) {
        if (typeof json.period_id != 'undefined' && json.period_id) {
            location.href = createPeriodUrl(json.period_id);
        } else {
            $('#txtPeriod').val('请输入数字');
            $.PageDialog.fail('查无记录');
        }
    })
}
