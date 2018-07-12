/**
 * Created by jun on 15/12/3.
 */
$(function () {
    $(".userurl").attr("href", createUserCenterUrl($("#userurl").attr("href")));

    //    点击我要评论 列表页隐藏 弹出输入发表窗口 我要评论按钮隐藏
    $('.saysome').click(function () {
        if ($(".s_comments article").length)
            location.href = "/passport/login.html?forward="+encodeURIComponent(location.href);
        $(this).siblings('.singleDetail').hide();
        $(this).hide();
        $(this).siblings('.s_comments').show();

    })

    //    点击取消返回列表页
    $('#a_cancel').click(function () {
        $(this).siblings('.singleDetail').hide();
        $(".s_comments").hide();
        $('.singleDetail').show();
        $('.saysome').show();
    })

    // 输入框输入事件
    $("#comment").keyup(function (event) {
        var max = 150;
        var textNum = $(this).val().length;
        if (textNum > max || textNum == 0) {
            $('#a_sendok').removeClass('orangeBtn').hide();
            if (textNum)
                $('#p_size').html('<span>已超过' + (textNum - max) + '个字了，请适当删除部分内容</span>');
        } else {
            $('#a_sendok').addClass('orangeBtn').show();
            $('#p_size').html($(this).val().length + '/' + max);
        }

        if (event.keyCode == 13 && event.ctrlKey)
            $("#a_sendok").click();
    }).keydown(function (event) {
        //if( $(this).val().length > 150 && event.keyCode > && event.keyCode< ) return false;
    })

    //发表评论成功按钮的单击事件
    $("#a_sendok").click(function () {

        var txt = $("#comment").val();
        $("#comment").val('');
        var data = {share_topic_id: topic_id, content: txt};
        if (!$(this).is(".orangeBtn") || !txt)
            return false;

        $.getJsonp(apiBaseUrl + '/share/comment', data, function (json) {
                if(json.code == "100") 
                {
                    $.PageDialog.ok("评论成功!");
                    setTimeout(function(){location.reload();},500);
                }
                else
                {
                    $.PageDialog.fail(json.msg);
                }
//                $("#replyList").html('');
//                upReplyList();
//                $('#a_cancel').click();
        });
    });



    //刷新评论列表
    upReplyList();
});

function upReplyList()
{
    $.getJsonp(apiBaseUrl + '/share/comment-list?id=' + topic_id, shareComment_condition, function (json) {
        $.each(json.list, function (i, v) {
            var userFaceUrl = createUserFaceImgUrl(v.user_avatar, avatarSize[1]);
            var userCenterUrl = createUserCenterUrl(v.user_home_id);
            var strHtml = '<div class="mess-list"><a href="' + userCenterUrl + '" class="photo"> <img src="' + userFaceUrl + '" alt="头像"></a> <p class="name"><a href="' + userCenterUrl + '" class="blue">' + v.user_name + '</a><span class="fr time">' + v.created_at + '</span></p><p>' + v.content + '</p></div>';
            $("#replyList").append(strHtml);
        });
        $("#emReplyNum").html(json.totalCount);
        if (json.totalCount == "0")
            $("#replyList").html('<div class="null-mess">沙发耶，还不快坐？</div>');
        return;
    });

}