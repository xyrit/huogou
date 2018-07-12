
$(function() {
    $('body').css({'backgroundColor': '#F4F4F4'});

    var addressId = $('#hidAddressId').val();
    $.getJsonp(apiBaseUrl+'/info/get-address',{addressId:addressId},function(json) {
        createAddressInfo(json);
        areaSelect();
    });

    $('#spDefult span').on('click',function() {
        if ($(this).hasClass('z-pay-mentsel')) {
            $(this).removeClass('z-pay-mentsel');
            $(this).addClass('z-pay-ment');
            $('#hidDefaultAddress').val(0);
        } else if ($(this).hasClass('z-pay-ment')) {
            $(this).removeClass('z-pay-ment');
            $(this).addClass('z-pay-mentsel');
            $('#hidDefaultAddress').val(1);
        }
    });
    $('#btnCancel').on('click',function() {
        history.back();
    });
    $('#btnSure').on('click', function() {
        var username = $('#txtUserName').val();
        if (username.length==0) {
            $.PageDialog.fail('请填写收货人姓名');
            return;
        }
        var tel = $('#txtMobile').val();
        if(!checkPhone(tel)) {
            $.PageDialog.fail('请输入正确的联系电话');
            return;
        }
        var prov = $('#selAreaA').val();
        var provName = $('#selAreaA option[value="'+prov+'"]').text();
        var city = $('#selAreaB').val();
        var cityName = $('#selAreaB option[value="'+city+'"]').text();
        var area = $('#selAreaC').val();
        var areaName = $('#selAreaC option[value="'+area+'"]').text();
        var address = $('#txtAddr').val();

        if (prov<=0) {
            $.PageDialog.fail('请选择所在省份');
            return;
        }
        if (city<=0) {
            $.PageDialog.fail('请选择所在城市');
            return;
        }
        if (area<=0) {
            $.PageDialog.fail('请选择所在地区');
            return;
        }
        if (address.length==0) {
            $.PageDialog.fail('请填写详细地址');
            return;
        } else if(address.length<3 || address.length>100) {
            $.PageDialog.fail('详细地址必须为3-100字之间');
            return;
        }

        var postcode = $('#txtZip').val();
        if (postcode.length==0) {
            $.PageDialog.fail('请输入邮编');
            return;
        }
        var default_address_status = $('#hidDefaultAddress').val();
        var data = {addressId:addressId,name:username,mobilephone:tel,prov:prov,city:city,area:area,address:address,code:postcode,default_address_status:default_address_status};
        $.getJsonp(apiBaseUrl+'/info/add-address',data,function(json) {
            if (json.code==100) {
                $.PageDialog.ok('添加收货地址成功',function() {
                    window.history.back();
                });
            } else {
                $.PageDialog.fail('添加收货地址失败');
            }
            return;
        });
    });


});


function createAddressInfo(json) {
    console.log(json)
    var name = json.name;
    var mobilephone = json.mobilephone;
    var prov = json.prov;
    var city = json.city;
    var area = json.area;
    var address = json.address;
    var postcode = json.code;
    var default_address_status = json.default_address_status;
    $('#txtUserName').val(name);
    $('#txtMobile').val(mobilephone);
    $('#txtAddr').val(address);
    $('#txtZip').val(postcode);
    $('#hidDefaultAddress').val(default_address_status);
    if(default_address_status) {
        $('#spDefult span').addClass('z-pay-mentsel');
    } else {
        $('#spDefult span').addClass('z-pay-ment');
    }
    $.getJsonp(apiBaseUrl + '/record/area-list', {pid: 0}, function (json) {
        createAreaSelectHtml(json,$('#selAreaA'),prov,'<option value="-1">请选择所在省份</option>');
        $.getJsonp(apiBaseUrl + '/record/area-list', {pid: prov}, function (json) {
            createAreaSelectHtml(json,$('#selAreaB'),city,'<option value="-1">请选择所在城市</option>');

            $.getJsonp(apiBaseUrl + '/record/area-list', {pid: city}, function (json) {
                createAreaSelectHtml(json,$('#selAreaC'),area,'<option value="-1">请选择所在地区</option>');
            });

        });
    });



}

function createAreaSelectHtml(json, obj, selected,firstOptionHtml) {
    var html = '';
    html += firstOptionHtml;
    $.each(json, function (i, v) {
        var id = v.id;
        var name = v.name;
        var pid = v.pid;
        if (selected==id) {
            html += '<option value="' + id + '" selected>' + name + '</option>';
        } else {
            html += '<option value="' + id + '">' + name + '</option>';
        }
    });
    obj.html(html);
}

function areaSelect() {
    $('#selAreaA').on('change', function () {
        var id = $(this).val();
        $.getJsonp(apiBaseUrl + '/record/area-list', {pid: id}, function (json) {
            createAreaSelectHtml(json, $('#selAreaB'),id,'<option value="-1">请选择所在城市</option>');
        });
    });
    $('#selAreaB').on('change', function () {
        var id = $(this).val();
        $.getJsonp(apiBaseUrl + '/record/area-list', {pid: id}, function (json) {
            createAreaSelectHtml(json, $('#selAreaC'),id,'<option value="-1">请选择所在地区</option>');
        });
    });
}