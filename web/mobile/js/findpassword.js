$(function(){
    //修改页面标题
    function T(title)
    {
        $("title").text(title);
        $(".g-header h2").text(title);
    }
      //第一步发送验证码
    $("#btnGetCode").click(function(){
        var v = $("#userAccount").val();
        if(! v) return $.PageDialog.fail("请输入手机号或邮箱");
        else if(! v.match(/\w+@\w+/) && ! v.match(/^1\d{10}$/))  return $.PageDialog.fail("请正确输入手机号或邮箱");
        $(this).parent().hide().next().show();
        $.getJsonp(apiBaseUrl+"/user/findpassword",{'account':v},function(json){
                if(json.error == "0")
                {
                    $("section").hide().eq(1).show();
                    T("验证身份");
                    $("#retrySend").trigger("click");
                }
                else 
                {
                    $("#btnGetCode").parent().show().next().hide();
                    $.PageDialog.fail(json.msg);
                }  
        });
    }); 
    
    //第二步输入验证码
    $("#btnSubmitVerify").click(function(){
        //检查验证码合法性
        if(! $("#mobileCode").val()) return $.PageDialog.fail("请输入验证码");
        $.getJsonp(apiBaseUrl+"/user/findpassword",{'account':$("#userAccount").val(),"code":$("#mobileCode").val()},function(json){
                if(json.error > 0) return $.PageDialog.fail(json.msg);
                $("section").hide().eq(2).show();
                T("设置新密码");
        });
    })
    
    //重新发送验证码
    $("#retrySend").click(function(){
                if($(this).is('.grayBtn')) return false;
                $(this).addClass("grayBtn");
                
                var but  = this;
                but.times = 150;
                but.timer = setInterval(function(){
                       $(but).html("重新发送("+but.times--+")" );
                       if(but.times ==0) 
                       {
                           $(but).removeClass("grayBtn").text('重新发送');
                           window.clearInterval(but.timer);
                       }
                },1000);
                
                if(but.Senddo)
                    $.getJsonp(apiBaseUrl+"/user/findpassword",{'account':$("#userAccount").val()},function(json){
                        if(json.error == "0") $.PageDialog.ok("发送成功");
                        else $.PageDialog.fail(json.msg);
                    });
                but.Senddo = true;
    })
    
     // 输入新密码显示密码按钮
     $("#isCheck em").click(function(){
         var p = $(this).parent();
         this.checked = p.is(".noCheck");
         this.checked ? p.removeClass("noCheck") : p.addClass("noCheck");
         $("#txtPasswordObj").attr("type",(this.checked) ? "text" : "password");
     })
     
     //修改密码
     $("#btnPostSet").click(function(){
            var v = $("#txtPasswordObj").val();
            if(v.length<8 || v.length>20 || v.match(/^\d+$/) || v.match(/^[a-z]+$/i) || v.match(/^[^\w]+$/))
                  return  $.PageDialog.fail("密码由8-20位字母、数字或符号两种或以上组合");
              
            $.getJsonp(apiBaseUrl+"/user/findpassword",{'account':$("#userAccount").val(),"code":$("#mobileCode").val(),"pwd":$("#txtPasswordObj").val()},function(json){
              if(json.error > 0) return $.PageDialog.fail(json.msg);
                    $.PageDialog.ok("修改密码成功!");
                    setTimeout(function(){history.back(); } ,500);
            });      
     })
})