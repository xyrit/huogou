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
                $("#" + city).html('<option value="">---请选择---</option>');
                $("#" + area).html('<option value="">---请选择---</option>');
            } else if (select == city) {
                $("#" + area).html('<option value="">---请选择---</option>');
            }

            var strHtml = '';
            $("#" + select).html('<option value="">---请选择---</option>');
            if (json == '') {
                $("#" + select).remove();
                return;
            }
            $.each(json, function (i, v) {
                strHtml += '<option value="' + v.id + '">' + v.name + '</option>'
            });
            if ($("#" + select).length == 0) {
                $("#" + city).parent().append('<select name="' + area + '" id="' + area + '"></select>');
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
                strHtml += '<option ';
                if (v.id == provId) {
                    strHtml += 'selected ';
                }
                strHtml += 'value="' + v.id + '">' + v.name + '</option>'
            });
            $("#" + prov).append(strHtml);

            var strHtml = '';
            $.each(json.cityList, function (i, v) {
                strHtml += '<option ';
                if (v.id == cityId) {
                    strHtml += 'selected ';
                }
                strHtml += 'value="' + v.id + '">' + v.name + '</option>'
            });
            $("#" + city).append(strHtml);

            if (area != 'not_area') {
                var strHtml = '';

                if (json.areaList == '') {
                    $("#" + area).remove();
                    return;
                }
                $.each(json.areaList, function (i, v) {
                    strHtml += '<option ';
                    if (v.id == areaId) {
                        strHtml += 'selected ';
                    }
                    strHtml += 'value="' + v.id + '">' + v.name + '</option>'
                });
                if ($("#" + area).length == 0) {
                    $("#" + city).parent().append('<select name="' + area + '" id="' + area + '"></select>');
                }
                $("#" + area).append(strHtml);
            }
        }
    });
}