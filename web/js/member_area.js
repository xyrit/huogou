/**
 * Created by chenyi on 2015/10/27.
 */
function getAreaList(pid, select) {
    var prov = arguments[2] ? arguments[2] : 'prov';
    var city = arguments[3] ? arguments[3] : 'city';
    var area = arguments[4] ? arguments[4] : 'area';

    $.ajax({
        async:false,
        url: apiBaseUrl + '/record/area-list?pid=' + pid,
        type: "GET",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: '',
        success: function (json) {
            if (select == prov) {
                $("#" + city).html('');
                $("#" + area).html();
            } else if (select == city) {
                $("#" + area).html('');
            }

            var strHtml = '';
            $("#" + select).html('');
            if (json == '') {
                $("#" + select).remove();
                return;
            }
            $.each(json, function (i, v) {
                strHtml += '<a value="' + v.id + '">' + v.name + '</a>'
            });
            if ($("#" + select).length == 0) {
                $("#" + city).parent().append('<div  name="' + area + '" id="' + area + '"></div>');

            }
            $("#" + select).append(strHtml);
        }
    });
}

function editAreaList(provId, cityId, areaId) {
    var prov = arguments[3] ? arguments[3] : 'prov';
    var city = arguments[4] ? arguments[4] : 'city';
    var area = arguments[5] ? arguments[5] : 'area';

    var params = {
        'provId': provId,
        'cityId': cityId
    };
    $.ajax({
        async: false,
        url: apiBaseUrl + '/record/edit-area-list',
        type: "GET",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: params,
        success: function (json) {
            var strHtml = '';
            $.each(json.provList, function (i, v) {
                strHtml += '<a ';
                if (v.id == provId) {
                    strHtml += 'class="selected" ';
                    $(this).parent('.select_o').siblings('.select_ck').find('a').text($(this).text());
                    $(this).parent('.select_o').siblings('.select_ck').find('a').attr('value',$(this).attr('value'));
                }
                strHtml += 'value="' + v.id + '">' + v.name + '</a>'
            });
            $("#" + prov).append(strHtml);

            var strHtml = '';
            $.each(json.cityList, function (i, v) {
                strHtml += '<a ';
                if (v.id == cityId) {
                    strHtml += 'selected ';
                }
                strHtml += 'value="' + v.id + '">' + v.name + '</a>'
            });
            $("#" + city).append(strHtml);

            if (area != 'not_area') {
                var strHtml = '';

                if (json.areaList == '') {
                    $("#" + area).remove();
                    return;
                }
                $.each(json.areaList, function (i, v) {
                    strHtml += '<a ';
                    if (v.id == areaId) {
                        strHtml += 'selected ';
                    }
                    strHtml += 'value="' + v.id + '">' + v.name + '</a>'
                });
                if ($("#" + area).length == 0) {
                    $("#" + city).parent().append('<select name="' + area + '" id="' + area + '"></select>');
                }
                $("#" + area).append(strHtml);
            }
        }
    });
}