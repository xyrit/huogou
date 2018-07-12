/**
 * Created by jun on 15/11/23.
 */
var perpage = 10;
$(function() {
    var url = apiBaseUrl+'/invite/commission-list';
    var data = {type:1,page:1,perpage:perpage};
    $.getJsonp(url,data,function(json) {
        $('#divLoading').hide();

        createCommissionListHtml(json);
    });


    var stopLoadPage = false;
    var isLoading = false;
    $.onSrollBottom(function() {
        if (stopLoadPage || isLoading) {
            return;
        }

        isLoading = true;
        $('#divLoading').show();

        var pageVal = $('#hidPage').val();
        pageVal = parseInt(pageVal) + 1;
        var url = apiBaseUrl+'/invite/commission-list';
        var data = {page:pageVal,perpage:perpage};
        $.getJsonp(url, data, function (json) {
            var t = function() {
                createCommissionListHtml(json);
                $('#divLoading').hide();
                if (json.list.length==0) {
                    stopLoadPage = true;
                } else {
                    $('#hidPage').val(pageVal);
                    isLoading = false;
                }
            }
            setTimeout(t,1000);
        });
    });
});


function createCommissionListHtml(json) {
    var html = '';

    $.each(json.list,function(i,v) {
        var userName = v.user_nickname;
        var userHomeId = v.user_home_id;
        var userCenterUrl = createUserCenterUrl(userHomeId);
        var userAvatar = createUserFaceImgUrl(v.user_avatar, avatarSize[1], avatarSize[1]);
        var createTime = v.created_time;
        var commissionPrice = v.commission;
        var money = v.money;

        html += '<dd>';
        html += '<span>';
        html += '<a href="'+userCenterUrl+'" class="blue">';
        html += '<img src="'+userAvatar+'">';
        html += '<em>'+userName+'</em>';
        html += '</a>';
        html += '</span><span>'+createTime+'</span><span>'+money+'</span><span>+'+commissionPrice+'</span>';
        html += '</dd>';
    });

    $('#divList dl').append(html);

}
