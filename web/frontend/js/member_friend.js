var baseHost = getHost();
var memberBaseUrl = 'http://member.'+ baseHost;

$('#addFriend').click(function(){
   var apply = $(this).attr('userid');
   var apply_url = memberBaseUrl + '/friend/apply-friend';

   if(apply){
      $.get(apply_url, {'id':apply}, function(data){
         if(data == 1){
            alert('请先登录');
         }else if(data == 2){
            alert('请求已发送，请等待通过')
         }else if(data == 0){
            alert('申请成功');
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
            alert('请先登录');
         }else if(data == 2){
            alert('不存在该好友请求')
         }else if(data == 0){
            alert('操作成功');
            window.location.reload();
         }
      })
   }
})

$('#delFriend').click(function(){
   var userid = $(this).attr('userid');
   var apply_url = memberBaseUrl + '/friend/del-friend';

   if(userid){
      $.get(apply_url, {'userid':userid}, function(data){
         if(data == 1){
            alert('请先登录');
         }else if(data == 2){
            alert('不存在好友')
         }else if(data == 0){
            alert('操作成功');
            window.location.reload();
         }
      })
   }
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
            window.location.reload();
         }else{
            alert('操作失败，请重试');
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
         window.location.reload();
      })
   }
})


$(function() {
   var editor;
   KindEditor.ready(function(K) {
      editor = K.create('#private_message_editor', {
         resizeType : 2,
         allowPreviewEmoticons : false,
         allowImageUpload : true,
         minWidth: 590,
         minHeight: 110,
         uploadJson : '',
         items : [
            'emoticons'
         ]
      });
   });
})
