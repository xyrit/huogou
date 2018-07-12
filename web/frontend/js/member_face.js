/**
 * 会员中心个人信息
 */
$(function(){
    
    var str = '{"A":".\/Uploads\/face\/1_a.jpg","B":".\/Uploads\/face\/1_b.jpg","C":".\/Uploads\/face\/1_c.jpg","D":".\/Uploads\/face\/1_d.jpg"}';

   // alert(JSON.parse(str).A);
    
        //error
    $('#error').dialog({
          autoOpen : false, 
          modal : false,
          closeOnEscape :false, //按下esc 无效
          resizable : false,
          draggable : false,
          width : 190,
          height: 40,
    }).parent().find('.ui-widget-header').hide();  
    
     //loading
    $('#loading').dialog({
          autoOpen : false, 
          modal : true,
          closeOnEscape :false, //按下esc 无效
          resizable : false,
          draggable : false,
          width : 190,
          height: 40,
    }).parent().find('.ui-widget-header').hide(); 

      $('#face').Jcrop({
          onChange:showPreview,
          onSelect:showPreview,
          aspectRatio:1 //锁定纵横比
    
      });
      

      //简单的事件处理程序，响应自onChange,onSelect事件，
    function showPreview(coords){
       
        $("#x").val(coords.x);
        $("#y").val(coords.y);
        $("#w").val(coords.w);
        $("#h").val(coords.h);
        if(parseInt(coords.w) > 0){
         
            var rx = $("#preview_box").width() / coords.w; 
            var ry = $("#preview_box").height() / coords.h;       
            var rx2 = $("#preview_box2").width() / coords.w; 
            var ry2 = $("#preview_box2").height() / coords.h;
            var rx3 = $("#preview_box3").width() / coords.w; 
            var ry3 = $("#preview_box3").height() / coords.h;
            
            $("#crop_preview").css({
                width:Math.round(rx * $("#face").width()) + "px",  
                height:Math.round(rx * $("#face").height()) + "px",  
                marginLeft:"-" + Math.round(rx * coords.x) + "px",
                marginTop:"-" + Math.round(ry * coords.y) + "px",

            });
      
             $("#crop_preview2").css({
                width:Math.round(rx * $("#face").width()) + "px", 
                height:Math.round(rx * $("#face").height()) + "px", 
                marginLeft:"-" + Math.round(rx * coords.x) + "px",
                marginTop:"-" + Math.round(ry * coords.y) + "px",

            });
            
             $("#crop_preview3").css({
                width:Math.round(rx * $("#face").width()) + "px",  
                height:Math.round(rx * $("#face").height()) + "px", 
                marginLeft:"-" + Math.round(rx * coords.x) + "px",
                marginTop:"-" + Math.round(ry * coords.y) + "px",

            });
            
        }
    }



        //头像上传
        var csrf = $('#photo').children('input').val(); 
        $('#file_upload').uploadifive({
                   'auto'            : true,
                   'onInit': function () {
                                    $("#uploadifive-file_upload-queue").hide();
                                    $("#uploadifive-file_upload").removeClass('uploadifive-button');
                                    $('.upload').css({background:'#44B6FF',width:'130px',cursor:'pointer'});
                                    $('.upLeft').css({cursor:'pointer'});
                    },
                   'uploadScript' : '/file/face',
                    width : 120,
                    buttonText : '上传头像',
                    queueSizeLimit : '5MB',//限制上传大小
                    fileType : '*.jpeg; *.jpg; *.png; *.gif',
                    'formData'         : {
                                       'csrf' : csrf,
                                       'MAX_FILE_SIZE' : '999999',
                                       'token'     : ''
                                     }, 
                    'onUploadComplete' : function(file, data) 
                    {                        
                        console.log(data); 
                         if(data){
                              $('#face,#crop_preview,#crop_preview2,#crop_preview3').attr('src',Yii['skinUrl']+$.parseJSON(data));
                              $("#url").val($.parseJSON(data));
  
                              $('.save,.cancel').button().show();
                              
                            //判断图片上传
                            $('#face').one('load',function(){  
                                if ($('#face').attr('src').indexOf('/big.jpg') < 0) {
                                 
                          
                               
                                 jcrop = $.Jcrop('#face',{
                                          onChange:showPreview,
                                          onSelect:showPreview,
                                          aspectRatio:1
                                 });
                                 
                                 //上传图片自动显示选取
                                 jcrop.setSelect([0,0,100,100]);
                                 
      
                                 //图片还未加载，提前隐藏会报错
                                  $('#file').hide();
                 
                               }
                           });     
                       }
                          
                        
                     },
                    onError : function (file, errorCode, errorMsg) {
                        switch(errorCode){
                            case -110:
                                 $('#error').dialog('open').html('超过1024KB...');
                            setTimeout(function () {
                                $('#error').dialog('close').html('...');
                            }, 1000);
                            break;
                                                    
                        }
                    },
          });     
     
         
  
     
    function nothing(e){
        e.stopPropagation();
        e.preventDefault();
        return false;
    };
    
   
    //取消
    $('.cancel').click(function(e){
        jcrop.destroy();
        
        //取消上传时显示头像
        // if(Yii['BIGFACE'].length>0){
            // $('#face,#crop_preview,#crop_preview2,#crop_preview3').attr('src', +'?random='+Math.random()); 
        // }else{
           // $('#face,#crop_preview,#crop_preview2,#crop_preview3').attr('src', Yii['Front'] +'/frontend/images/big.jpg'); 
        // }
        
        $('.save,.cancel').hide();
        $('#file').show();
        return nothing(e);
    });
    
    
    var csrf2 = $('#cropUp').children('input').val();
    $('.save').click(function(){
            $.ajax({
               url : '/file/crop',
               type : 'POST',
               data : {
                  '_csrf' : csrf2,
                   x :  $("#x").val(),
                   y :  $("#y").val(),
                   w :  $("#w").val(),
                   h :  $("#h").val(),
                   url : $("#url").val(),
               },
               beforeSend : function(xhr, settings){
                     jcrop.destroy();
                     $('.save,.cancel').hide();
                     $('#loading').html('头像保存中...').dialog('open');
               },
               success : function(data,response,status){   
                   if(response == 'success'){
                        $('#loading').html('头像保存成功...');
                        $('#imgUserPhoto').attr('src',Yii['skinUrl']+data.B+'?random='+Math.random());
                        //这里两次返回完全相同

                        //$('#face,#crop_preview,#crop_preview2,#crop_preview3').attr('src',Yii['skinUrl']+data.B+'?random='+Math.random());                                              
                        $('#file').show();

                        setTimeout(function () {
                           $('#loading').html('...').dialog('close');
                         
                        }, 500);
                    }
                    
               }
          }); 
       });    

  
});
     