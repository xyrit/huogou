var baseHost = getHost();
var memberBaseUrl = 'http://member.'+ baseHost;
var editor;

function comment(){
   var id = $('.system-reminder').attr('data-id');
   var content = editor.html();
   var apply_url = '/message/send-priv-msg';
   var con = filterContent(content);

   $.post(apply_url, {'homeId':id, 'content':con}, function(data){
      if(data == 1){
         $('.safety-b-box').html('<i id="safety-b-close"></i><h4>发送成功</h4>');
         $('#safety-b-con').fadeIn();
         setTimeout(function(){
            $('#safety-b-con').fadeOut();
         },2000)
         window.location.reload();
      }else if(data == 0){
         $('.safety-b-box h3').html('发送失败，请重试');
         $('#safety-b-con').fadeIn();
         setTimeout(function(){
            $('#safety-b-con').fadeOut();
         },2000);
         return false;
      }else if(data == 2){
         $('.safety-b-box h3').html('您有广告嫌疑');
         $('#safety-b-con').fadeIn();
         setTimeout(function(){
            $('#safety-b-con').fadeOut();
         },2000);
         return false;
      }else if(data == 3){
         $('.safety-b-box h3').html('用户禁止发送私信');
         $('#safety-b-con').fadeIn();
         setTimeout(function(){
            $('#safety-b-con').fadeOut();
         },2000);
         return false;
      }
   })
}

function filterContent(content){
   content = content.replace(/\r\n/ig, "").replace(/\r/ig, "").replace(/\n/ig, "").replace(/<br[^>]*>/ig, "[br]").replace(/<img[^>]*src=\"[\w:\.\/]+\/([\d]{1,2})\.gif\"[^>]*>/ig, "[s:$1]").replace(/<a[^>]*href=[\'\"\s]?([^\s\'\"]*)[^>]*>(.+?)<\/a>/ig, "[url=$1]$2[/url]").replace(/<[^>]*?>/ig, "").replace(/&nbsp;/ig, " ").replace(/&amp;/ig, "&").replace(/&lt;/ig, "<").replace(/&gt;/ig, ">")

   return content;
}

$('.add-friends').click(function(){
   var apply = $(this).attr('userid');
   var apply_url = apiBaseUrl + '/group/add-friend';

   if(apply){
      $.ajax({
         async: false,
         url: apply_url,
         type: "GET",
         dataType: 'jsonp',
         jsonp: 'callback',
         data: {id: apply},
         success: function (data) {
            if(data.code == 100 || data.code == 102){
               $('.safety-b-box').html('<i id="safety-b-close"></i><h4>'+data.msg+'</h4>');
            }else{
               $('.safety-b-box h3').html(data.msg);
            }
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
            },2000)
         }
      })
   }
})

$('.greenbut').click(function(){
   var applyid = $(this).attr('applyid');
   var applyuserid = $(this).attr('applyuserid');
   var apply_url = memberBaseUrl + '/friend/agree-friend-apply';

   if(applyid){
      $.get(apply_url, {'applyid':applyid, 'applyuserid':applyuserid}, function(data){
         if(data == 1){
            $('.safety-b-box h3').html('请先登录');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
            },2000);
            return false;
         }else if(data == 2){
            $('.safety-b-box h3').html('不存在该好友请求');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
            },2000);
            return false;
         }else if(data == 0){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>添加好友成功</h4>');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
            },2000)
            window.location.reload();
         }
      })
   }
})

$('.delFriend').click(function(e){
   //var event = event || window.event;
   e.preventDefault();
   $('.balance_con').fadeIn();
   $('.close, #del-cancle, #del-sure').on('click',function(){
      $('.balance_con').fadeOut();
   })

   var userid = $(this).attr('userid');
   var apply_url = memberBaseUrl + '/friend/del-friend';
   $('#del-sure').on('click',function(){
      if(userid){
         $.get(apply_url, {'userid':userid}, function(data){
            if(data == 2){
               $('.safety-b-box h3').html('不存在该好友');
               $('#safety-b-con').fadeIn();
               setTimeout(function(){
                  $('#safety-b-con').fadeOut();
               },2000);
               return false;
            }else{
               $('.safety-b-box').html('<i id="safety-b-close"></i><h4>操作成功</h4>');
               $('#safety-b-con').fadeIn();
               setTimeout(function(){
                  $('#safety-b-con').fadeOut();
               },2000)
               window.location.reload();
            }
         })
      }
   })
})

//删除私信单条内容
$('.close-msg').click(function(){
   var id = $(this).attr('data-id');
   var home_id = $(this).attr('home-id');
   var msg_all = $(this).attr('msgall');
   var apply_url = memberBaseUrl + '/message/del-priv-msg';

   if(id || home_id || msg_all){
      $.get(apply_url, {'id':id, 'home_id':home_id, 'msg_all':msg_all}, function(data){
         if(data == 1){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>操作成功</h4>');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
            },2000)
            window.location.reload();
         }else{
            $('.safety-b-box h3').html('操作失败');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
            },2000);
            return false;
         }
      })
   }
})

$('.close-sys').click(function(){
   var id = $(this).attr('data-id');
   var sysall = $(this).attr('sysall');
   var apply_url = memberBaseUrl + '/message/del-sys-msg';

   if(id || sysall){
      $.get(apply_url, {'id':id, 'sysall':sysall}, function(data){
         if(data == 1){
            $('.safety-b-box').html('<i id="safety-b-close"></i><h4>操作成功</h4>');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
               window.location.reload();
            },2000)

         }else{
            $('.safety-b-box h3').html('操作失败');
            $('#safety-b-con').fadeIn();
            setTimeout(function(){
               $('#safety-b-con').fadeOut();
            },2000);
            return false;
         }
      })
   }
})

