/**
 * Created by jun on 15/11/23.
 */
var perpage  =10;
$(function() {
    var url = apiBaseUrl+'/invite/invite-list';
    var data = {page:1,perpage:perpage};
    $.getJsonp(url,data,function(json) {
        $('#divLoading').hide();
        createInviteListHtml(json);
    });


    var stopLoadPage = false;
    var isLoading = false;
    $.onSrollBottom( function() {
        if (stopLoadPage || isLoading) {
            return;
        }
        isLoading = true;

        $('#divLoading').show();

        var pageVal = $('#hidPage').val();
        pageVal = parseInt(pageVal) + 1;
        var url = apiBaseUrl+'/invite/invite-list';
        var data = {page:pageVal,perpage:perpage};
        $.getJsonp(url, data, function (json) {
            var t = function() {
                createInviteListHtml(json);
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

function createInviteListHtml(json) {
    var html = '';
    $.each(json.list, function(i,v) {

        var userName = v.user_nickname;
        var userHomeId = v.user_home_id;
        var userCenterUrl = createUserCenterUrl(userHomeId);
        var userAvatar = createUserFaceImgUrl(v.user_avatar, avatarSize[1], avatarSize[1]);
        var inviteTime = v.invite_time;
        var inviteId = v.id;
        var statusText = v.status ==0 ? '未消费' : '已消费';
        html += '<dd>';
        html += '<span>';
        html += '<a href="'+userCenterUrl+'" class="blue">';
        html += '<img src="'+userAvatar+'">';
        html += '<em>'+userName+'</em>';
        html += '</a>';
        html += '</span><span>'+inviteTime+'</span><span>'+inviteId+'</span><span>'+statusText+'</span>';
        html += '</dd>';
    });

    $('#divInviteList dl').append(html);

}