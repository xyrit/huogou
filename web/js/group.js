var groupBaseUrl = 'http://group.'+ baseHost;
var apiBaseUrl = 'http://api.'+ baseHost;
var uploadUrl = 'http://group.'+ baseHost + '/topic/upload-topic-image';

$(function(){
    $('.hot-member').find('li').each(function(index){
        if ((index + 1) % 3 == 0) {
            $(this).css('marginRight',0);
        }
    })

    $('#qz-show').on('mouseenter',function(){
        $(this).find('div').stop().slideDown();
    }).on('mouseleave',function(){
        $(this).find('div').stop().slideUp();
    })

    $('.show-card').hover(function(){
        $('.show-card').children('.member-infocard').hide();
        $(this).children('.member-infocard').show();
    },function(){
        $(this).children('.member-infocard').hide();
    })

    $('.bottom_reply').on('click',function(){
        $('html, body').animate({scrollTop : $('#reply-editor').offset().top})
    })

    //私信框
    var liLen = $('.find-friends-list').find('li').size();
    var aRow = Math.ceil(liLen / 2);
    $('.find-friends-list').find('li').each(function(index){
        if ((index+1) > (aRow - 1) * 2){
            $(this).css('borderBottom',0);
        }
    })

    $('#letter-no').on('click',function(){
        $('#letter-fixed').stop().fadeOut('fast');
    })

    $('.commentBtn').click(function(){
        $('#letter-fixed').stop().fadeOut('fast');

        var id = $('.commentid  input').val();
        var content = $('textarea').val();

        if(content == ''){
            $('.safety-b-box h3').html('发送内容不能为空');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },1000)
            return false;
        }

        var urls = apiBaseUrl + '/group/prv-msg';
        $.ajax({
            async: false,
            url: urls,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: {id: id, 'content':content},
            success: function (data) {
                if(data.code == 100){
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
                }else{
                    $('.safety-b-box h3').html(data.msg);
                }
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
                $('textarea').val('');
            }
        })
    })

})

//提交评论
var commitTopic = function(id){
    if ($('#edit-submit').hasClass('dis')) {
        //if ($('#editor').text().length == 0) {
        //    $('.safety-b-box h3').html('内容不能为空');
        //    $('#safety-b-con').fadeIn();
        //    setTimeout(function(){
        //        $('#safety-b-con').fadeOut();
        //    },1500)
        //}
        return false;
    }
    var id = id;
    var content = editor.text();
    var apply_url = groupBaseUrl + '/topic/comment';
    var floor = $('#floor span').text();
    var homeId = $('#floor input').val();
    if(floor == ''){
        floor = 0;
        homeId = 0;
    }

    var con = filterContent(content);

    $.ajax({
        async: false,
        url: 'http://api.'+ baseHost + '/limit/comment-num' ,
        type: "GET",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: {'content':con},
        success: function (data) {
            if(data.code != 100){
                $('.safety-b-box h3').html(data.message);
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000)
            }else if(data.code == 100){
                $.post(apply_url, {'content':con, 'id':id, 'floor':floor, 'homeId':homeId}, function(data){
                    if(data == 0){
                        $('.safety-b-box').html('<i id="safety-b-close"></i><h4>回复成功</h4>');
                        $('#safety-b-con').fadeIn();
                        setTimeout(function(){
                            window.location.reload();
                        },1000)
                    }else if(data == 1){
                        $('.safety-b-box h3').html('内容不能为空');
                        $('#safety-b-con').fadeIn();
                        setTimeout(function(){
                            $('#safety-b-con').fadeOut();
                        },1000)
                        return false;
                    }else if(data == 2){
                        $('.safety-b-box h3').html('内容有敏感词汇');
                        $('#safety-b-con').fadeIn();
                        setTimeout(function(){
                            $('#safety-b-con').fadeOut();
                        },1000)
                        return false;
                    }
                })
            }
        }
    })
}

function filterContent(content){
    content = content.replace(/\r\n/ig, "").replace(/\r/ig, "").replace(/\n/ig, "").replace(/<br[^>]*>/ig, "[br]").replace(/<img[^>]*src=\"[\w:\.\/]+\/([\d]{1,2})\.gif\"[^>]*>/ig, "[s:$1]").replace(/<a[^>]*href=[\'\"\s]?([^\s\'\"]*)[^>]*>(.+?)<\/a>/ig, "[url=$1]$2[/url]").replace(/<[^>]*?>/ig, "").replace(/&nbsp;/ig, " ").replace(/&amp;/ig, "&").replace(/&lt;/ig, "<").replace(/&gt;/ig, ">")

    return content;
}

//对指定楼层的评论
$('.reply').click(function(){
    var floor = $(this).siblings('span').children('em').text();
    var id = $(this).attr('data-id');
    $('html, body').animate({scrollTop : $('#reply-editor').offset().top});
    $('#floor').html('对 <span>'+floor+'</span>楼 说：<input type="hidden" value="'+id+'">');
})

//删除评论
$('.del-comment').click(function(){
    var id = $(this).data('id');
    var urls = groupBaseUrl + '/topic/del-comment';
    $.get(urls, {id: id}, function (data) {
        if(data){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>删除成功</h4>');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                window.location.reload();
            },1000)
        }
    });
})

//加入圈子
$('.group-join').click(function(){
    var id = $(this).data('id');
    var urls = groupBaseUrl + '/default/join-group';
    $.get(urls, {id: id}, function (data) {
        if(data){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>加入成功</h4>');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            })
            setTimeout(function(){
                window.location.reload();
            },1000)
        }
    });
})

//退出圈子
$('.group-quit').click(function(){
    var id = $(this).data('id');
    var urls = groupBaseUrl + '/default/quit';
    $.get(urls, {id: id}, function(data){
        if(data){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>退出成功</h4>');
            $('#safety-b-con').fadeIn();
            $('#safety-b-close').on('click',function(){
                $('#safety-b-con').fadeOut();
            })
            setTimeout(function(){
                window.location.reload();
            },1000)
        }
    });
})

