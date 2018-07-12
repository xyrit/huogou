/**
 * Created by jun on 15/12/11.
 */

$(function () {

            selectAddress();
            $.getJsonp(apiBaseUrl + '/info/address-list', {}, function (json) {

                createAddresslistHtml(json);
                
            });
            $('#btnUpdateAddr').on('click',function() {
                var addressId = getAddressId();
                if (!checkSelectAddress()) {
                    return false;
                }
                window.location.href = weixinBaseUrl+'/member/addressedit-'+addressId+'.html';
                return;
            });

            $('#btnConfimAddr').on('click', function (e) {
                if (!checkSelectAddress())     return false;
                
                var data = {addressId:getAddressId(),default_address_status:1};
                $.getJsonp(apiBaseUrl+'/info/add-address',data,function(json) {
                        if (json.code==100) {
                            $.PageDialog.ok('设置默认收货地址成功!',function() {
                                window.history.back();
                            });
                        } else {
                            $.PageDialog.fail('添加收货地址失败');
                        }
                });
            });
            
});

function createAddresslistHtml(json) {
    var html = '';
    $.each(json.list, function (i, v) {
        var addressId = v.id;
        var name = v.name;
        var postcode = v.code;
        var phone = v.mobilephone;
        var prov = v.prov;
        var provName = v.provName;
        var city = v.city;
        var cityName = v.cityName;
        var area = v.area;
        var areaName = v.areaName;
        var street = v.street;
        var address = v.address;
        var defaultAddress = v.default_address_status;


        html += '<li id="' + addressId + '" postcode="'+postcode+'">';
        html += '<span class="name gray6">' + name + '</span>';
        html += '<span class="tel gray6">' + phone + '</span>';
        html += '<p>';
        html += '<span class="gray9">' + provName + cityName + '</span>';
        html += '<span class="gray9">' + areaName + '</span>';
        html += '<span class="gray9">' + address + '</span>';
        html += '</p>';
        if (defaultAddress == 1) {
            setAddressId(addressId);
            html += '<i class="z-set" style="display: block;"></i>';
        } else {
            html += '<i class="z-set" style="display: none;"></i>';
        }
        html += '</li>';
    });
    $('.addre-list').html(html);
}

function createVirtualAdressListHtml(json) {
    var html = '';
    $.each(json.list, function (i, v) {
        var account = v.account;
        var contact = v.contact;
        var addressType = v.type;
        var addressTypeName = getVirtualTypeName(addressType);
        var addressId = v.id;


        html += '<li id="' + addressId + '" account="'+account+'" addressType="'+addressType+'">';
        html += '<span class="name gray6">' + addressTypeName + '</span>';
        html += '<span class="tel gray6">' + account + '</span>';
        html += '<i class="z-set" style="display: none;"></i>';
        html += '</li>';
    });
    $('.addre-list').html(html);
}

function getVirtualTypeName(addressType) {
    addressTypeName = '';
    if (addressType==1) {
        addressTypeName = '话费充值';
    }
    return addressTypeName;
}

function selectAddress() {
    $('.addre-list').on('click', 'li', function () {
        var addressId = $(this).attr('id');
        setAddressId(addressId);
        $('.addre-list li .z-set').hide();
        $(this).find('.z-set').css({'display': 'block'});
    });
}

function checkSelectAddress() {
    var addressId = getAddressId();
    var addObj = $('#'+addressId);
    if (addObj.length==0) {
        $.PageDialog.fail('请添加收货地址');
        return false;
    }
    return true;
}

function confirmVirtualAddress() {
    var addressId = getAddressId();
    var addObj = $('#'+addressId);
    if (addObj.length==0) {
        $.PageDialog.fail('请添加收货地址');
        return false;
    }
    var account = addObj.attr('account');
    var addressType = addObj.attr('addressType');
    var addressTypeName = getVirtualTypeName(addressType);
    var s = function () {

        var w = '<div class="addnew-inner">';
        w += '<h3 class="title">确认地址后不可更改哦！</h3>';
        w += '<div class="info">';
        w += '<p><span class="name">'+addressTypeName+'</span><span>'+account+'</span></p>';
        w += '</div>';
        w += '<div class="btn-wrapper clearfix">';
        w += '<a id="a_cancle" href="javascript:;" class="btn"><span class="cancle">取消</span></a>';
        w += '<a id="a_submit" href="javascript:;" class="btn"><span class="submit">确认</span></a>';
        w += '</div>';
        w += '</div>';
        return w
    };
    var v = function () {
        _DialogObj = $("#pageDialog");
        $("#a_cancle", _DialogObj).click(function (w) {
            t.cancel()
        });
        $("#a_submit", _DialogObj).click(function (w) {
            submitAddress(true,addressId,orderId);
            t.close();
        });
        $(".addnew-inner", _DialogObj).click(function (w) {
            stopBubble(w)
        });
        $("#pageDialogBG").click(function () {
            t.cancel()
        });
    };
    var t = new $.PageDialog(s(), {W: 300, H: 194, close: true, autoClose: false, ready: v})
}

function submitAddress(isVir,addressId,orderId) {
    var apiUrl = '';
    var data = {};
    if (!isVir) {
        apiUrl = apiBaseUrl+'/record/submit-address';
        data = {'useraddressid':addressId,'orderId':orderId};
    } else {
        apiUrl = apiBaseUrl+'/record/virtual-product-submit';
        data = {'addressid':addressId,'orderId':orderId,'mark_text':''};
    }
    $.getJsonp(apiUrl,data,function(json) {
        if (json.code==100) {
            $('#div_confirm').hide();
            $('#div_share').show();
        } else {
            $.PageDialog.fail('添加收货地址失败');
            return false;
        }
    });
}

function setAddressId(id) {
    $('#hidAddressId').val(id);
}

function getAddressId() {
    return $('#hidAddressId').val();
}

function createGoodsHtml(json) {
    var periodInfo = json.data.periodInfo;
    var goodsName = periodInfo.goods_name;
    var goodsImgUrl = createGoodsImgUrl(periodInfo.goods_picture, photoSize[1], photoSize[1]);
    var periodNumber = periodInfo.period_number;
    var periodUrl = createPeriodUrl(periodInfo.period_id);
    var luckyCode = periodInfo.lucky_code;
    var raffTime = periodInfo.raff_time;

    var html = '';
    html += '<h6 class="gray6"><a href="' + periodUrl + '">' + goodsName + '</a></h6>';
    html += '<p class="gray9">幸运夺宝码：' + luckyCode + '</p>';
    html += '<p class="gray9">揭晓时间：' + raffTime + '</p>';
    html += '<a href="' + periodUrl + '" class="h-pic"> <img src="' + goodsImgUrl + '" alt="" /> </a>';

    $('.suc-about').html(html);
}
