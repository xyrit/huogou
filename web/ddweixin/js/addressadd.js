/**
 * Created by jun on 15/11/25.
 */
$(function () {
    $('body').css({'backgroundColor': '#F4F4F4'});

    var pid = 0;
    $.getJsonp(apiBaseUrl + '/record/area-list', {pid: pid}, function (json) {
        createAreaSelectHtml(json, $('#selAreaA'), '<option value="-1">请选择所在省份</option>');
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
        var data = {addressId:0,name:username,mobilephone:tel,prov:prov,city:city,area:area,address:address,code:postcode,default_address_status:default_address_status};
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
    $("select").change(changecolor);
});


function changecolor()
{
    if($('#selAreaA').val() == "-1"){
        $('#selAreaA').addClass('gray');
    }else{
        $('#selAreaA').removeClass('gray');
    }
    if($('#selAreaB').val() == "-1"){
        $('#selAreaB').addClass('gray');
    }else{
        $('#selAreaB').removeClass('gray');
    }
    if($('#selAreaC').val() == "-1"){
        $('#selAreaC').addClass('gray');
    }else{
        $('#selAreaC').removeClass('gray');
    }
}

function createAreaSelectHtml(json, obj, firstOptionHtml) {
    var html = '';
    html += firstOptionHtml;
    $.each(json, function (i, v) {
        var id = v.id;
        var name = v.name;
        var pid = v.pid;

        html += '<option value="' + id + '">' + name + '</option>';
    });
    obj.html(html);

    if($('#selAreaA').val() == "-1"){
        $('#selAreaA').addClass('gray');
    }else{
        $('#selAreaA').removeClass('gray');
    }
    if($('#selAreaB').val() == "-1"){
        $('#selAreaB').addClass('gray');
    }else{
        $('#selAreaB').removeClass('gray');
    }
    if($('#selAreaC').val() == "-1"){
        $('#selAreaC').addClass('gray');
    }else{
        $('#selAreaC').removeClass('gray');
    }
}


function areaSelect() {
    $('#selAreaA').on('change', function () {
        var id = $(this).val();
        $.getJsonp(apiBaseUrl + '/record/area-list', {pid: id}, function (json) {
            createAreaSelectHtml(json, $('#selAreaB'),'<option value="-1">请选择所在城市</option>');
        });
    });
    $('#selAreaB').on('change', function () {
        var id = $(this).val();
        $.getJsonp(apiBaseUrl + '/record/area-list', {pid: id}, function (json) {
            createAreaSelectHtml(json, $('#selAreaC'),'<option value="-1">请选择所在地区</option>');
        });
    });

}