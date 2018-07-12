var baseHost = getHost();
var uBaseUrl = 'http://u.'+ baseHost;
var memberBaseUrl = 'http://member.'+ baseHost;

$("#btnSendMsg").click(function(){
     $("#showMessage").show();
});

$('.btnAddFriend').click(function(){
    var apply = $(this).attr('data-id');
    var apply_url = urls = apiBaseUrl + '/group/add-friend';
    $('.safety-b-box').html('');
    if(apply){
        $.ajax({
            async: false,
            url: urls,
            type: "GET",
            dataType: 'jsonp',
            jsonp: 'callback',
            data: {id: apply},
            success: function (data) {
                if(data.code == 100 || data.code == 102){
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h4 style="width:180px;">'+data.msg+'</h4>');
                }else{
                    $('.safety-b-box').html('<h3>'+data.msg+'</h3>');
                }
                $('#safety-b-con').fadeIn();

                setTimeout(function(){$('#safety-b-con').fadeOut()}, 1000);
            }
        })
    }
})

$('.send-message').click(function(){
    var sendId = $(this).attr('data-id');
    var apply_url = uBaseUrl + '/default/send-msg';
    var user = $(this).siblings('h2').text();
    $('.safety-b-box').html('');

    if(sendId){
        $.get(apply_url, {'id':sendId}, function(data){
            if(data == 0){
                $('.safety-b-box').html('<h3>请先加为好友</h3>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){$('#safety-b-con').fadeOut()}, 1000);
            }else if(data == 2){
                $('.safety-b-box').html('<h3>请先登录</h3>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){$('#safety-b-con').fadeOut()}, 1000);
            }else{
                //私信
                $('.letter-fixed-con h2').html('对'+user+'说：');
                $('.commentid').html('<input type="hidden" name="id" value="'+ sendId +'">');
                $('#letter-fixed').stop().fadeIn('fast');

                $('#letter-no').on('click',function(){
                    $('#letter-fixed').stop().fadeOut('fast');
                })
            }
            return false;
        })
    }
})


$(document).ready(function() {
    var timer = null;
    $('.shaidan_con_pic').find('dd').on('click', function(){
        $(this).parent().find('p').hide();
        $(this).find('p').stop().slideToggle(500);
    }).hover(function(){
        clearTimeout(timer);
    },function(){
        timer = setTimeout(function(){
            $('.shaidan_con_pic').find('p').fadeOut();
        })
    })

    $('.home_fl').find('li').each(function(index){
        if ((index + 1) % 3 == 0) {
            $(this).css('marginRight',0);
        }
    })

    $('.commentBtn').click(function(){
        $('#letter-fixed').stop().fadeOut('fast');

        var id = $('.commentid  input').val();
        var content = $('textarea').val();

        if(content == ''){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h3>发送内容不能为空</h3>');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },2000)
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
                    $('#safety-b-con').fadeIn();
                    setTimeout(function(){
                        $('#safety-b-con').fadeOut();
                    },2000)
                }else{
                    $('.safety-b-box').html('<i id="safety-b-close"></i><h3>'+data.msg+'</h3>');
                    $('#safety-b-con').fadeIn();
                    setTimeout(function(){
                        $('#safety-b-con').fadeOut();
                    },2000)
                    return false;
                }

                $('textarea').val('');
            }
        })
    })
})

//交互效果
$('.show-card').hover(function(){
    $('.show-card').children('.member-infocard').hide();
    $(this).children('.member-infocard').show();
},function(){
    $(this).children('.member-infocard').hide();
})

//添加好友
$('.add-friend').click(function(){
    var id = $(this).attr('data-id');
    var urls = apiBaseUrl + '/group/add-friend';
    $.ajax({
        async: false,
        url: urls,
        type: "GET",
        dataType: 'jsonp',
        jsonp: 'callback',
        data: {id: id},
        success: function (data) {
            if(data.code == 100){
                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>请求已发送</h4>');
            }else{
                $('.safety-b-box h3').html(data.msg);
            }
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
                $('#safety-b-con').fadeOut();
            },2000)
        }
    })
})






