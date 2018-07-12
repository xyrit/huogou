/**
 * 会员中心主页
 */
$(function(){
  
  $('#loading').dialog({
          autoOpen : false, 
          modal : true,
          closeOnEscape : false, //按下esc 无效
          resizable : false,
          draggable : false,
          width : 195,
          height: 50,
    }).parent().find('.ui-widget-header').hide();
    
    //复制文本框内容
    $('#btnCopy').on('click',function(){
             $('#txtInfo').select();
             document.execCommand("Copy");
             $('#loading').dialog('open').html('已复制好，可贴粘!');
         setTimeout(function(){
             $('#loading').dialog('close'); 
         },1000);
     }); 
});
     