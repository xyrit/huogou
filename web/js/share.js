/**
 * Created by chenyi on 2015/10/10.
 */

$(document).ready(function(){
    $('#xianmu').on('click',function(){
        var shareTopicId = getShareTopicId();
        if ($(this).attr('class') != 'act') {
            $.ajax({
                async: false,
                url: apiBaseUrl + '/share/up',
                type: "GET",
                dataType: 'jsonp',
                jsonp: 'callback',
                data: {id: shareTopicId},
                success: function (msg) {
                    if (msg.code == 100) {
                        $('#xianmu').addClass('act');
                        $('#xianmu').find('em').html('已羡慕');
                        $('#xianmu').find('span').html(parseInt($('#xianmu').find('span').text()) + 1);
                    }
                }
            })
        }
    })

    //更多选项
    $('#share_title_more').mouseenter(function(){
        $(this).addClass('hover');
        $(this).children('div').show();
    }).mouseleave(function(){
        $(this).removeClass('hover');
        $(this).children('div').hide();
    })
})

var shareComment_condition = {
    page: 1,
    perpage : 10,
    token: token
};

var otherShareOrder_condition = {
    page: 1,
    perpage : 5,
    productId: 0,
    exceptUserId: 0,
    token: token
};

function shareCommentList() {
    var shareTopicId = getShareTopicId();
    //晒单评论
    $.getContent(apiBaseUrl + '/share/comment-list?id='+shareTopicId, shareComment_condition, 'shareCommentList');
}

function shareComment_gotoPage(currentPage) {
    if (shareComment_condition.page != currentPage) {
        shareComment_condition.page = currentPage;
        shareCommentList();
    }
}

function getShareTopicId() {
    var str = window.location.href;
    var result = str.match(/\-(\S*)\./);
    return result[1];
}

function getOtherShareOrder() {
    $.getContent(apiBaseUrl + '/share/other-share-order', otherShareOrder_condition, 'otherShareOrder');
}

var reply_content = '';
var reply_floor = 0;
function success_shareCommentList(json) {
    $("#commentList").empty();
    $(".pagination").empty();
    $.each(json.list, function(i, v) {
        userFaceUrl = createUserFaceImgUrl(v.user_avatar, 160);
        userCenterUrl = createUserCenterUrl(v.user_home_id);
        var strHtml = '<li><a href="' + userCenterUrl + '"><picture><img src="' + userFaceUrl + '"></picture></a>';
        strHtml += '<article><h3><a href="' + userCenterUrl + '">' + v.user_name + '</a>' + v.created_at + '</h3>';
        strHtml += '<aside>' + v.content + '</aside></article>';
        strHtml += '<span class="fl_hjr_pl_cut" data-create="0" onclick="showEdit(this, ' + v.id + ')">回复';
        if (v.reply_num > 0) {
            strHtml += '(' + v.reply_num + ')';
        }
        strHtml += '</span>';
        strHtml += '<div class="reply"><picture><img src="' + user_avatar + '"></picture>';
        if (isGuest) {
            strHtml += '<article><i></i>请您<a href="javascript:;" onclick="showLoginForm()">登录</a>或<a href="' + register_url + '">注册</a>后再评论</article>';
        } else {
            strHtml += '<span class="duilou"></span><div class="reply-con"><textarea></textarea></div>';
        }

        strHtml += '</div>';
        strHtml += '<div class="comment-main clrfix">';
        strHtml += '<ul>';

        strHtml += '</ul>';
        strHtml += '</div>';
        strHtml += '</li>';
        $("#commentList").append(strHtml);
    });

    if (json.totalPage > 1) {
        $(".pagination").createPage({
            pageCount: json.totalPage,
            current: shareComment_condition.page,
            downPage: 1,
            gotoPage: 'shareComment_gotoPage',
            backFn: function(p){
                //console.log(p);
            }
        });
    } else {
        $(".pagination").html("");
    }


    $('.fl_hjr_pl_cut').on('click',function(){
        $(this).parent('li').siblings().children('.reply').hide();
        $(this).parent('li').siblings().children('.comment-main').hide();
        if($(this).siblings('.reply').css('display') == 'block'){
            $(this).siblings('.comment-main').toggle();
        }
    })


    $('.fl_hjr_pl').on('click','.put-awayA',function(){
        if(!($('.comment-main ul li').css('display') == "none")){
            $('.comment-main ul li').slideUp();
            $('.put-awayA').html('展开');
        }else{
            $('.comment-main ul li').slideDown();
            $('.put-awayA').html('收起');
        }
    })
}

var reply_editor;

function showEdit(obj, commentId) {
    $('.duilou').html('');
    reply_content = '';
    reply_floor = 0;
    $(obj).siblings('.reply').slideToggle();
    if ($(obj).attr('data-create') != 1) {
        $(obj).attr('data-create',1);
        //$(this).siblings('.reply').find('textarea').attr('id', 'editor' + $(this).attr('data-reply'));
        KindEditor.create($(obj).siblings('.reply').find('textarea'), {
            resizeType : 0,
            allowPreviewEmoticons : true,
            allowImageUpload : false,
            items : ['emoticons'],
            themeType : 'simple',
            pasteType : 1,
            replyNum : $(obj).attr('data-reply'),
            width : '680px',
            height : '130px',
            afterChange : function(){
                var textNum = this.count('text');
                if (textNum > 150) {
                    $('#edit-submit'+this.replyNum).css({'color':'#fff','backgroundColor':'grey','borderColor':'grey'}).addClass('dis');
                    $('#edit-count'+this.replyNum).html('<span>已超过' + (textNum - 150) + '个字了，请适当删除部分内容</span>');
                }else{
                    $('#edit-submit'+this.replyNum).removeClass('dis');
                    $('#edit-count'+this.replyNum).html('<span>' + this.count('text') + '</span>/150');
                    reply_content = this.text();
                    reply_editor = this;
                    if (this.text() != '' && textNum > 0) {
                        $('#edit-submit' + $(obj).attr('data-reply')).css({'backgroundColor': '#ff500b','borderColor': '#ff500b'});
                    } else {
                        $('#edit-submit' + $(obj).attr('data-reply')).css({'backgroundColor':'grey','border': 'grey'});
                    }
                }
            },
            layout: '<div class="container"><i><em></em></i><div class="edit"></div><div class="toolbar"></div><div class="edit-info"><div class="edit-count" id="edit-count' + $(obj).attr('data-reply') + '">0/150</div><a href="javascript:;" class="edit-submit" id="edit-submit' + $(obj).attr('data-reply') + '" style="background: grey;border: 1px solid grey">提交</a></div><div class="statusbar"></div></div>'
        })
    }

    $('#edit-submit' + $(obj).attr('data-reply')).on('click', function() {
        reply_commit(commentId);
    });

    reply_list(commentId);
}

function success_otherShareOrder(msg) {
    $("#dl_otherget").empty();
    $('.else_prev').unbind('click');
    $('.else_next').unbind('click');
    $.each(msg.list, function(i, v) {
        UserFaceUrl = createUserFaceImgUrl(v.user_avatar, avatarSize[1]);
        userCenterUrl = createUserCenterUrl(v.user_home_id);
        var strHtml = '<li>';
        strHtml += '<picture>';
        strHtml += '<img src="' + UserFaceUrl + '" width="52" height="52">';
        strHtml += '</picture>';
        strHtml += '<article>';
        strHtml += '<p><a class="user_name" href="'+userCenterUrl+'">' + v.user_name.substr(0, 10) + '</a>获得</p>';

        if (v.share_topic_id != 0 && v.is_pass == 1) {
            strHtml += '<a href="' + createShareDetailUrl(v.share_topic_id) + '" class="link-btn see-btn">查看详情</a>'
        } else {
            strHtml += '<a rel="nofollow" href="javascript:;" class="link-btn">暂未晒单</a>';
        }

        strHtml += '</article>';
        strHtml += '</li>';
        $("#dl_otherget").append(strHtml);
    });

    if (otherShareOrder_condition.page <= 1) {
        otherShareOrder_condition.page = 1;
        $('.else_prev').addClass('left_none');
    }
    if (otherShareOrder_condition.page >= msg.totalPage) {
        otherShareOrder_condition.page = msg.totalPage;
        $('.else_next').addClass('right_none');
    }

    if (otherShareOrder_condition.page > 1) {
        $('.else_prev').removeClass('left_none');
        $('.else_prev').click(function() {
            otherShareOrder_condition.page -= 1;
            getOtherShareOrder();
            $('.else_prev').unbind('click');
            setTimeout(function() {
                $('.else_prev').bind('click');
            }, 1000);
        });
    }

    if (otherShareOrder_condition.page < msg.totalPage) {
        $('.else_next').removeClass('right_none');
        $('.else_next').click(function() {
            otherShareOrder_condition.page += 1;
            getOtherShareOrder();
            $('.else_next').unbind('click');
            setTimeout(function() {
                $('.else_next').bind('click');
            }, 1000);
        });
    }

    if (msg.totalPage == 1) {
        $('.else_prev').hide();
        $('.else_next').hide();
    }
}

function reply(obj) {
    var reply_id = $(obj).attr('reply_id');
    var shareCommentId = $(obj).attr('share_comment_id');
    reply_floor = $(obj).attr('reply_floor');

    var ext = '';
    if (reply_floor != 0) {
        $('.duilou').html('对' + reply_floor + '楼 说');
    }
}

function reply_list(id) {
    $.ajax({
        async:false,
        url: apiBaseUrl + '/share/reply-list',
        type: "GET",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: {id: id, page: 1, perpage: 10, token: token},
        success: function (data) {
            $('.comment-main').find('ul').empty();
            $.each(data.list, function(i, v){
                userFaceUrl = createUserFaceImgUrl(v.user_avatar, avatarSize[2]);
                userCenterUrl = createUserCenterUrl(v.user_home_id);
                var strHtml = '';
                strHtml += '<li>';
                strHtml += '<div class="input-pic"><a href="' + userCenterUrl + '" target="_blank"><img src="' + userFaceUrl + '" alt=""></a></div>';
                strHtml += '<div class="m-review"><dl>';
                strHtml += '<dt><a href="' + userCenterUrl + '" target="_blank">' + v.user_name + '</a>';
                if (v.reply_floor != 0) {
                    strHtml += '回复 ' + v.reply_floor + '楼 ';
                }
                strHtml += v.created_at + '<em>' + v.floor + '楼</em></dt>';
                strHtml += '<dd><span class="gray3">' + v.content + '</span><cite>';
                if (user_id == v.user_id) {
                    strHtml += '<a onclick="reply_del(this)" href="javascript:;" name="signDelete" reply_id="' + v.id + '" share_comment_id="' + v.share_comment_id + '">删除</a>';
                } else {
                    strHtml += '<a onclick="reply(this)" href="javascript:;" name="signReply" reply_id="' + v.id + '" reply_floor="' + v.floor + '" share_comment_id="' + v.share_comment_id + '">回复</a>'
                }
                strHtml += '</cite></dd>';
                strHtml += '</dl></div>';
                strHtml += '</li>';
                $('.comment-main').find('ul').append(strHtml);
            });
            if (data.totalCount > 0) {
                $('.comment-main').find('ul').append('<div class="put-away"><a href="javascript:;" class="put-awayA">收起</a></div>');
            }
        }
    });
}

function filterContent(content){
    content = content.replace(/\r\n/ig, "").replace(/\r/ig, "").replace(/\n/ig, "").replace(/<br[^>]*>/ig, "[br]").replace(/<img[^>]*src=\"[\w:\.\/]+\/([\d]{1,2})\.gif\"[^>]*>/ig, "[s:$1]").replace(/<a[^>]*href=[\'\"\s]?([^\s\'\"]*)[^>]*>(.+?)<\/a>/ig, "[url=$1]$2[/url]").replace(/<[^>]*?>/ig, "").replace(/&nbsp;/ig, " ").replace(/&amp;/ig, "&").replace(/&lt;/ig, "<").replace(/&gt;/ig, ">")

    return content;
}

function reply_commit(id) {
    content = filterContent(reply_content);
    if (content == '' || $('#edit-submitundefined').hasClass('dis') ) {
        return false;
    }
    reply_content = '';
    reply_editor.text("");
    $.ajax({
        async:true,
        url: apiBaseUrl + '/share/comment-reply',
        type: "POST",
        dataType: 'jsonp',
        data: {share_comment_id: id, content: content, reply_floor: reply_floor, token: token},
        success: function (data) {
            if (data.code == 201) {
                showLoginForm();
                return false;
            } else if (data.code == 101) {
                $('.safety-b-box h3').html(data.msg);
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
                return false
            }
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>回复成功</h4>');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
                reply_list(id);
            },1000)
        }
    });
}

function reply_del(obj) {
    reply_id = $(obj).attr('reply_id');
    shareCommentId = $(obj).attr('share_comment_id');
    $.ajax({
        async:false,
        url: apiBaseUrl + '/share/reply-del',
        type: "POST",
        dataType: 'jsonp',
        data: {id: reply_id, token: token},
        success: function (msg) {
            reply_list(shareCommentId);
        }
    });
}

function commit(topic_id) {
    var txt = filterContent(editor.text());
    if (txt == '' || $('#edit-submit').hasClass('dis')) {
        return false;
    }
    editor.text("");
    $.ajax({
        async:false,
        url: apiBaseUrl + '/share/comment',
        type: "POST",
        dataType: 'jsonp',
        data: {share_topic_id: topic_id, content: txt, token: token},
        success: function (data) {
            if (data.code == 201) {
                showLoginForm();
                return false;
            } else if (data.code == 101) {
                $('.safety-b-box').html('<i class="safety-b-close"></i><h3></h3>');
                $('.safety-b-box h3').html(data.msg);
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
                return false
            }
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>回复成功</h4>');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut(function() {
                    $('.safety-b-box h3').html("");
                });
                shareCommentList();
            },1000)
        }
    });
}