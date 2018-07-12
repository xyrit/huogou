var baseHost = getHost();
var groupBaseUrl = 'http://group.'+ baseHost;
var uploadUrl = 'http://group.'+ baseHost + '/topic/upload-topic-image';

$(function(){
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('#topic_editor', {
            resizeType : 2,
            allowPreviewEmoticons : false,
            allowImageUpload : true,
            minHeight: 300,
            uploadJson : uploadUrl,
            items : [
                'image', 'emoticons', 'link', 'bold',
            ]
        });
    });

    KindEditor.ready(function(K) {
        editor = K.create('#comment_editor', {
            resizeType : 2,
            allowPreviewEmoticons : false,
            allowImageUpload : true,
            minHeight: 100,
            uploadJson : '',
            items : [
                'emoticons'
            ]
        });
    });
})

$(".blue").click(function(){
    var floor = $(this).parents().siblings(".Comment_User").children(".getFloor").children('span').text();
    var name = $(this).parents().siblings(".Comment_User").children(".blue").text();

    $('#floor').html("对" + floor + "楼(<a href=''>" + name + "</a>)说&nbsp;<a href='javascript:cancle();' class='close'>" +
        "取消</a><input name='Topic[floor]' type='hidden' value='"+ floor +"'/> ");
});

function cancle(){
    $('#floor').html('');
}

$(".subMenu a").click(function(){
    //alert($(".subMenu a").eq(0).html());
    //alert($(this).index());
    var aIndex = $(this).index();
    $(this).addClass("current").siblings("a").removeClass("current");
    $(".single-C").hide();//获得商品
    $(".page_nav").hide();//获得商品
    $(".list-tab").hide();//获得商品
    $("#tbList"+aIndex).css('display','block'); //获得商品
    $("#PostList"+aIndex).css('display','block'); //获得商品
    $("#divTopic"+aIndex).css('display','block'); //获得商品
    $("#divPageNav"+aIndex).css('display','block');

})

$('.topicblue').click(function(){
    var event = event || window.event;
    event.preventDefault();
    $('.balance_con').fadeIn();
    $('.close, #del-cancle, #del-sure').on('click',function(){
        $('.balance_con').fadeOut();
    })

    var id = $(this).data('id');

    $('#del-sure').on('click',function(){
        var urls = '/group/del';
        $.get(urls, {delId: id}, function (data) {
            if(data){
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>删除成功</h4>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },2000)
                setTimeout(function(){
                    window.location.reload();
                },1000)
            }else{
                $('.safety-b-box h3').html('删除失败');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },2000);
                return false;
            }
        });
    })
})

$('.topicedit').on('click', function () {
    var id = $(this).data('id');
    var urls = '/group/edit';
    window.location.href = urls + "?editId=" + id;
});

$('.postdel').on('click', function () {
    var event = event || window.event;
    event.preventDefault();
    $('.balance_con').fadeIn();
    $('.close, #del-cancle, #del-sure').on('click',function(){
        $('.balance_con').fadeOut();
    })

    var id = $(this).data('id');

    $('#del-sure').on('click',function(){
        var urls = '/group/del-post';
        $.get(urls, {id: id}, function (data) {
            if(data){
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>删除成功</h4>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },2000)
                setTimeout(function(){
                    window.location.reload();
                },1000)
            }
        });
    })
});

$('.group-join').click(function(){
    var id = $(this).data('id');
    var urls = groupBaseUrl + '/default/join-group';
    $.get(urls, {id: id}, function (data) {
        if(data){
            window.location.reload();
        }
    });
})

$('.group-quit').click(function(){
    var id = $(this).data('id');
    var urls = groupBaseUrl + '/default/quit';
    $.get(urls, {id: id}, function(data){
        if(data){
            window.location.reload();
        }
    });
})

$('.del-comment').click(function(){
    var id = $(this).data('id');
    var urls = groupBaseUrl + '/topic/del-comment';
    $.get(urls, {id: id}, function (data) {
        if(data){
            window.location.reload();
        }
    });
})

//验证话题关键字
$('.checkTitle').blur(function(){
    var title = $(this).val();
    var urls = 'default/check';
    $.get(urls, {'title': title}, function(data){
        if(data){
            $('.checkMsg').html(data+'为关键字，不允许出现');
            $('#sub').attr('disabled','true');
        }else{
            $('#sub').removeAttr("disabled");
            $('.checkMsg').html('');
        }
    })
})

$('.am-form-horizontal').submit(function(){
    editor.sync();
    var content = document.getElementById("editor_id").value;
    var urls = 'default/check';
    var title = $('.checkTitle').val();
    var h = false;

    if(title == ''){
        $('.checkMsg').html('标题不能为空');
        return false;
    }else if(content == ''){
        $('.checkMsg').html('内容不能为空');
        return false;
    }

    $.ajaxSetup({
        async: false
    });

    $.get(urls, {'title': content}, function(data){
        if(data){
            $('.checkMsg').html(data+'为关键字，不允许出现');
            h = true;
        }else{
            $('.checkMsg').html('');
        }
    })

    if(h == true){
        return false;
    }
})

$('.comment-form').submit(function(){
    editor.sync();
    var content = document.getElementById("editor_id").value;
    var urls = 'default/check-comment';
    var h = false;

    if(content == ''){
        $('.checkMsg').html('内容不能为空');
        return false;
    }

    $.ajaxSetup({
        async: false
    });

    $.get(urls, {'content': content}, function(data){
        if(data){
            $('.checkMsg').html(data+'为关键字，不允许出现');
            h = true;
        }else{
            $('.checkMsg').html('');
        }
    })

    if(h == true){
        return false;
    }

})

function getHost(url) {
    var host = "null";
    if (typeof url == "undefined"
        || null == url)
        url = window.location.href;
    var regex = /.*\:\/\/([^\/|:]*).*/;
    var match = url.match(regex);
    if (typeof match != "undefined"
        && null != match) {
        host = match[1];
    }
    if (typeof host != "undefined"
        && null != host) {
        var strAry = host.split(".");
        if (strAry.length > 1) {
            host = strAry[strAry.length - 2] + "." + strAry[strAry.length - 1];
        }
    }
    return host;
}

//移动到加入圈子n个 显示div
$("#divJoinGroup").mouseover(function(){
    $(this).children("#pJoinGroup").hide();
    $(this).children("#divGroupList").show();
}).mouseout(function(){
    $(this).children("#divGroupList").hide();
    $(this).children("#pJoinGroup").show();
})

