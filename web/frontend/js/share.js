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
                    if (msg.flag == 1) {
                        $('#xianmu').addClass('act');
                        $('#xianmu').find('em').html('已羡慕');
                        $('#xianmu').find('span').html(parseInt($('#xianmu').find('span').html()) + 1);
                    }
                }
            })
        }
    })
})

var shareComment_condition = {
    page: 1,
    perpage : 10
};

var otherShareOrder_condition = {
    page: 1,
    perpage : 5,
    productId: 0,
    exceptUserId: 0
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
        var strHtml = '<li><picture><img src="' + userFaceUrl + '"></picture>';
        strHtml += '<article><h3><a href="' + userCenterUrl + '">' + v.user_name + '</a>' + v.created_at + '</h3>';
        strHtml += '<aside>' + v.content + '</aside></article>';
        strHtml += '<span class="fl_hjr_pl_cut" data-create="0" onclick="showEdit(this, ' + v.id + ')">回复';
        if (v.reply_num > 0) {
            strHtml += '(' + v.reply_num + ')';
        }
        strHtml += '</span>';
        strHtml += '<div class="reply"><picture><img src="img/pic98.jpg"></picture>';
        if (isGuest) {
            strHtml += '<article><i></i>请您<a href="' + login_url + '">登录</a>或<a href="' + register_url + '">注册</a>后再评论</article>';
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
}

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
                    $('#edit-submit'+this.replyNum).addClass('dis');
                    $('#edit-count'+this.replyNum).html('<span>已超过' + (textNum - 150) + '个字了，请适当删除部分内容</span>');
                }else{
                    $('#edit-submit'+this.replyNum).removeClass('dis');
                    $('#edit-count'+this.replyNum).html('<span>' + this.count('text') + '</span>/150');
                    reply_content = this.html();
                }
            },
            layout: '<div class="container"><i><em></em></i><div class="edit"></div><div class="toolbar"></div><div class="edit-info"><div class="edit-count" id="edit-count' + $(obj).attr('data-reply') + '">0/150</div><a href="javascript:;" class="edit-submit" id="edit-submit' + $(obj).attr('data-reply') + '">提交</a></div><div class="statusbar"></div></div>'
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
        strHtml += '<img src="' + UserFaceUrl + '" width="60" height="60">';
        strHtml += '</picture>';
        strHtml += '<article>';
        strHtml += '<p><a class="user_name" href="">' + v.user_name.substr(0, 10) + '</a>获得第 ' + v.period_number + ' 伙</p>';

        if (v.share_topic_id != 0) {
            strHtml += '<a href="' + createShareDetailUrl(v.share_topic_id) + '" class="link-btn see-btn">查看详情</a>'
        } else {
            strHtml += '<a rel="nofollow" href="javascript:;" class="link-btn">暂未晒单</a>';
        }

        strHtml += '</article>';
        strHtml += '</li>';
        $("#dl_otherget").append(strHtml);
    });

    if (otherShareOrder_condition.page > 1) {
        $('.else_prev').click(function() {
            otherShareOrder_condition.page -= 1;
            getOtherShareOrder();
        });
    }

    if (otherShareOrder_condition.page < msg.totalPage) {
        $('.else_next').click(function() {
            otherShareOrder_condition.page += 1;
            getOtherShareOrder();
        });
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
        data: {id: id, page: 1, perpage: 10},
        success: function (data) {
            $('.comment-main').find('ul').empty();
            $.each(data.list, function(i, v){
                userFaceUrl = createUserFaceImgUrl(v.user_avatar, avatarSize[2]);
                userCenterUrl = createUserCenterUrl(v.user_home_id);
                var strHtml = '';
                strHtml += '<li>';
                strHtml += '<div class="input-pic"><a href="' + userCenterUrl + '" target="_blank" title="没意思有规则"><img src="' + userFaceUrl + '" alt=""></a></div>';
                strHtml += '<div class="m-review"><dl>';
                strHtml += '<dt><a href="' + userCenterUrl + '" target="_blank" title="没意思有规则">' + v.user_name + '</a>';
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
                $('.comment-main').find('ul').append('<div class="put-away"><a href="javascript:;">收起</a></div>');
            }
        }
    });
}

function reply_commit(id) {
    $.ajax({
        async:true,
        url: apiBaseUrl + '/share/comment-reply',
        type: "POST",
        dataType: 'jsonp',
        data: {share_comment_id: id, content: reply_content, reply_floor: reply_floor},
        success: function (msg) {
            reply_list(id);
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
        data: {id: reply_id},
        success: function (msg) {
            reply_list(shareCommentId);
        }
    });
}

function commit(topic_id) {
    var txt = editor.html();
    $.ajax({
        async:false,
        url: apiBaseUrl + '/share/comment',
        type: "POST",
        dataType: 'jsonp',
        data: {share_topic_id: topic_id, content: txt},
        success: function (msg) {
            shareCommentList();
        }
    });
}

function getTopicList(orderFlag, catId)
{
    var params = {
        page: 1,
        perpage: 16,
        catId: catId,
        orderFlag: orderFlag
    };
    var totalPage = 0;
    var downPage = 4; //下拉4页再分页
    var isScroll = false;
    window.onscroll=function(){
        var a = document.documentElement.scrollTop==0? document.body.clientHeight : document.documentElement.clientHeight;
        var b = document.documentElement.scrollTop==0? document.body.scrollTop : document.documentElement.scrollTop;
        var c = document.documentElement.scrollTop==0? document.body.scrollHeight : document.documentElement.scrollHeight;
        if(a+b==c && b!=0 && isScroll){
            more();
        }
    }
    /*瀑布流开始*/
    var container = $('.waterfull ul');
    var loading=$('#imloading');
    // 初始化loading状态
    loading.data("on",true);
    /*判断瀑布流最大布局宽度，最大为1280*/
    function tores(){
        var tmpWid=$(window).width();
        if(tmpWid>1200){
            tmpWid=1200;
        }else{
            var column=Math.floor(tmpWid/300);
            tmpWid=column*300;
        }
        $('.waterfull').width(tmpWid);
    }
    tores();
    $(window).resize(function(){
        //tores();
    });

    container.imagesLoaded(function(){
        container.masonry({
            columnWidth: 300,
            itemSelector : '.item',
            isFitWidth: true,//是否根据浏览器窗口大小自动适应默认false
            isAnimated: false,//是否采用jquery动画进行重拍版
            isRTL:false,//设置布局的排列方式，即：定位砖块时，是从左向右排列还是从右向左排列。默认值为false，即从左向右
            isResizable: true,//是否自动布局默认true
            animationOptions: {
                duration: 800,
                easing: 'easeInOutBack',//如果你引用了jQeasing这里就可以添加对应的动态动画效果，如果没引用删除这行，默认是匀速变化
                queue: false//是否队列，从一点填充瀑布流
            }
        });
    });
    more();

    function gotoPage(currentPage) {
        if (params.page != currentPage) {
            isScroll = false;
            params.page = currentPage;
            $('#bands-list').empty();
            container.imagesLoaded(function(){
                container.masonry({
                    columnWidth: 300,
                    itemSelector : '.item',
                    isFitWidth: false,//是否根据浏览器窗口大小自动适应默认false
                    isAnimated: false,//是否采用jquery动画进行重拍版
                    isRTL:false,//设置布局的排列方式，即：定位砖块时，是从左向右排列还是从右向左排列。默认值为false，即从左向右
                    isResizable: true,//是否自动布局默认true
                    animationOptions: {
                        duration: 800,
                        easing: 'easeInOutBack',//如果你引用了jQeasing这里就可以添加对应的动态动画效果，如果没引用删除这行，默认是匀速变化
                        queue: false//是否队列，从一点填充瀑布流
                    }
                });
            });
            more();
            tores();
        }
    }

    function more() {
        $(".pagination").empty();
        loading.data("on",false).fadeIn(800);
        $.ajax({
            async: true,
            url: apiBaseUrl + '/share/topic-list',
            type: "POST",
            dataType: 'jsonp',
            data: params,
            success: function (msg) {
                var strHtml = '';
                $.each(msg.list, function (i, v) {
                    strHtml += '<li class="item">';
                    strHtml += '<picture><a href="' + createShareDetailUrl(v.id) + '"><img src="' + createShareImgUrl(v.header_image, 'big') + '" alt=""></a></picture>';
                    strHtml += '<h3><a href="' + createShareDetailUrl(v.id) + '">' + v.title + '</a></h3>';
                    strHtml += '<aside>' + v.content.substr(0, 50) + '</aside>';
                    strHtml += '<article>';
                    strHtml += '<span><img src="' + createUserFaceImgUrl(v.user_avatar, 160) + '" alt=""></span><a href="' + createUserCenterUrl(v.user_home_id) + '" target="_blank">' + v.user_name + '</a><i>' + v.created_at + '</i>';
                    strHtml += '</article>';
                    strHtml += '</li>';
                });
                $(strHtml).find('img').each(function(index){
                    loadImage($(this).attr('src'));
                })
                var $newElems = $(strHtml).css({ opacity: 0}).appendTo(container);
                $newElems.imagesLoaded(function(){
                    $newElems.animate({ opacity: 1},800);
                    container.masonry( 'appended', $newElems,true);
                    loading.data("on",true).fadeOut();
                });
                totalPage = msg.totalPage;
                if (params.page % downPage == 0 && Math.ceil(totalPage / downPage) > 1) {
                    $(".pagination").createPage({
                        pageCount: Math.ceil(totalPage / downPage),
                        current: params.page/downPage,
                        downPage: downPage,
                        backFn: function(p){
                            //console.log(p);
                        }
                    });
                    isScroll = false;
                    return;
                }
                params.page++;
                isScroll = true;
            }
        });
    }

    function loadImage(url) {
        var img = new Image();
        //创建一个Image对象，实现图片的预下载
        img.src = url;
        if (img.complete) {
            return img.src;
        }
        img.onload = function () {
            return img.src;
        };
    };
}