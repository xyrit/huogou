$(function(){

   //左侧栏目 
   $("div.sidebar-nav h3.sid-icon04").next('ul').find('li').show();
   var flag = true;
   $("div.sidebar-nav h3.sid-icon04").click(function () {
      if (flag) {
            $(this).next('ul').find('li').hide();
            flag = false;
       }else{
            $(this).next('ul').find('li').show();
            flag = true;
       } 
    });   
    
    //复制文本框内容
    $('#btnCopy').on('click',function(){
             $('#txtInfo').select();
             document.execCommand("Copy");
             $('#loading').dialog('open').html('已复制好，可贴粘!');
         setTimeout(function(){
             $('#loading').dialog('close'); 
         },1000);
     }); 
     
    //充值卡充值选项卡
    $('#linkRecharge').click(function(){
         $('#linkApply').removeClass('current').addClass('current2');
         $(this).addClass('current');
         
         $('#divTip,#divSQTX').hide();
         $('#divSQCZ').show();
    });
    $('#linkApply').click(function(){
         $('#linkRecharge').removeClass('current');
         $(this).addClass('current');
         
         $('#divTip,#divSQTX').show();
         $('#divSQCZ').hide();
         
    });

       //提现申请
        $('#btnApply').click(function(e){
                 e.preventDefault();
                 $('#Apply').submit();
                 //return false;
       });
       
       //拥金充值到云账户
        $('#btnRecharge').click(function(e){
                 e.preventDefault();
                 $('#Recharge').submit();
                 return false;
       });
       $('#loading').dialog('open');
       
       
       $('#Recharge').validate({
            submitHandler : function($this){
                $($this).ajaxSubmit({
                    url : Yii['API'] + '/invite/recharge',
                    type : 'POST',
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    beforeSubmit : function(formData,jqForm,options){
                        //alert($('#reg').dialog('widget').html());
                
                       // $('#loading').dialog('open'); //这里高度会多加30px,
                       // $('#reg').dialog('widget').find('button').eq(1).button('disable');
                        
                    },
                    success : function(responseText,statusText){
                        //console.log(responseText.repose);
                        if(responseText.repose != 'succ'){
                            $('#waring').dialog('open').html(responseText.repose);
                            
                            setTimeout(function(){
                            $('#waring').dialog('close');
                            $('#Recharge').resetForm();

                        },1000);
                            
                        }
                        
                        // $('#reg').dialog('widget').find('button').eq(1).button('enable');
//                         
                        // if(responseText){
                            // $('#loading').css('background','url(img/success.gif) no-repeat 20px center').html('数据新增成功...');
                        // }
//                         
                        // //写入到cookie
                       // // $.cookie('user',$('#user').val());
//                         
                        // setTimeout(function(){
                            // $('#loading').dialog('close');
                            // $('#loading').css('background','url(img/loading.gif) no-repeat 20px center').html('数据交互中...');
                            // $('#reg').dialog('close');
                            // $('#reg').resetForm();
                            // $('#reg span.star').html('*').removeClass('succ');
//                             
                            // //更新一下cookie部分
                            // $('#member,#logout').show();
                            // $('#reg_a,#login_a').hide();
                            // $('#member').html($.cookie('user'));
                        // },1000);
//                         
                    },
                });

            },
          //  errorLabelContainer : 'ol.reg_error',
          //  wrapper : 'li', 
            
            showErrors : function(errorMap,errorList){
                   
                var errors = this.numberOfInvalids();
                if (errors>0) {
                    $('#reg').dialog('option','height',errors*20+340);
                } else {
                    $('#reg').dialog('option','height',340);
                }
                this.defaultShowErrors(); //执行默认错误
            },
            highlight : function(element,errorClass){
                $(element).css('border','1px solid #630');
                $(element).parent().find('span').html('&nbsp;').removeClass('succ');
            },
            unhighlight : function(element, errorClass){
                $(element).css('border','1px solid #ccc');
                $(element).parent().find('span').html('&nbsp;').addClass('succ');
            },
            rules : {
               // user : {
                    // required : true,
                    // minlength : 2,
                // },
                // pass : {
                    // required : true,
                    // minlength : 6,
                // },
                // email : {
                    // required : true,
                    // email : true,
                // },
                // birthday : {
                    // date : true,
                // },
                
            },
            messages : {
                // user : {
                    // required : '用户名不得为空!',
                    // minlength : '用户名不得小于{0}位!', 
                // },
                // pass : {
                    // required : '密码不得为空!',
                    // minlength : '密码不得小于{0}位!',              
                // },
                // email : {
                    // required : '电子邮件不得为空!',
                    // email : '请输入正确的电子邮件!',              
                // },
            }
    }); 

  
  
     $('#loading').dialog({
          autoOpen : false, 
          modal : true,
          closeOnEscape : false, //按下esc 无效
          resizable : false,
          draggable : false,
          width : 195,
          height: 50,
    }).parent().find('.ui-widget-header').hide();
    
    
    $('#waring').dialog({
          autoOpen : false, 
          modal : true,
          closeOnEscape : false, //按下esc 无效
          resizable : false,
          draggable : false,
          width : 260,
          height: 45,
    }).parent().find('.ui-widget-header').hide();

  

});




