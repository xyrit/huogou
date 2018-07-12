/**
 * 
 */

$(function(){
    
    
   $('.am-btn-toolbar').on('click','#btnEdit',function(e){
        var comment_this = this; 
        e.preventDefault();
        $('#member_edit').dialog('open'); 
        $.ajax({
                type : 'POST',
                url : '/member/user',
                data : {
                     user_id : $(comment_this).attr('user_id'),
                },
                success : function(response,status,xhr){
                     var json_user = $.parseJSON(response);
                     $('#id').html(json_user.id);
                     $('#user_id').val(json_user.id);
                     $('#user_name').html(json_user.user);
                     $('#user_nick').html(json_user.nickname);
                     $('#user_phone').val(json_user.phone);
                     $('#user_email').val(json_user.email);
                     $('#user_password').val(json_user.password);  
                      
                     var json_avatar = $.parseJSON(json_user.avatar); 
                     $('#user_img').attr('src','http://skin.huogou.com/'+json_avatar.face.B);
                
                }
            });
        
        
   }); 

    
   $('#member_edit').dialog({
       autoOpen : false,
       modal : true,
       resizable : false,
       width : 590,
       height : 500,       
       buttons : {
            '保存' : function(){
                $(this).submit();
            },
            '关闭' : function(){
                $(this).dialog('close');
            },
        },
    });
    
    // .validate({
           // submitHandler : function(form){
                // $(form).ajaxSubmit({
                    // url : '/user',
                    // type : 'POST',
                    // beforeSubmit : function(formData,jqForm,options){
                        // //alert($('#reg').dialog('widget').html());
                        // alert();
//                
//                         
                    // },
                    // success : function(responseText,statusText){
//                        
                    // }
                // });
            // },
            // errorLabelContainer : 'ol.login_error',
            // wrapper : 'li', 
//             
            // showErrors : function(errorMap,errorList){
// 
                // var errors = this.numberOfInvalids();
                // if (errors>0) {
                    // $('#login').dialog('option','height',errors*20+240);
                // } else {
                    // $('#login').dialog('option','height',240);
                // }
                // this.defaultShowErrors(); //执行默认错误
            // },
            // highlight : function(element,errorClass){
                // $(element).css('border','1px solid #630');
                // $(element).parent().find('span').html('&nbsp;').removeClass('succ');
            // },
            // unhighlight : function(element, errorClass){
                // $(element).css('border','1px solid #ccc');
                // $(element).parent().find('span').html('&nbsp;').addClass('succ');
            // },
            // rules : {
                // user : {
                    // required : true,
                    // minlength : 2,
                // },
                // pass : {
                    // required : true,
                    // minlength : 6,
                    // remote : {
                        // url : 'login.php',
                        // type : 'POST',
                        // data : {
                            // user : function(){
                                // return $('#login_user').val();
                            // }
                        // },
                    // },
                // },
//                 
            // },
            // messages : {
                // user : {
                    // required : '用户名不得为空!',
                    // minlength : '用户名不得小于{0}位!', 
                // },
                // pass : {
                    // required : '密码不得为空!',
                    // minlength : '密码不得小于{0}位!',  
                    // remote : '用户名或密码不正确!',
                // },
            // }
    // }); 

    
});
 