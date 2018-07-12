/**
 * Created by jun on 15/11/11.
 */
    $('.register-form-con ul li em').click(function(){
        $(this).siblings('input').focus();
    });

    $('#btnSubmitLogin').on('click', function () {
        postLoginForm();
    });

    $(function() {
        document.onkeydown = function(e){
            var ev = document.all ? window.event : e;
            if(ev.keyCode==13) {
                postLoginForm();
            }
        }

        if ($('#username').val().length>0) {
            $('#pwd').focus();
        }
    });

    function postLoginForm() {
        var url = window.location.href;
        var data = $('#login-form').serialize();
        $('#btnSubmitLogin').val('正在登录...');
        $.post(url, data, function(e) {
            if (e.error) {
                $('#btnSubmitLogin').val('登  录');
                var msg = e.message;
                if (msg.username) {
                    $('#dd_error_msg').css('display','block').html('<i></i>'+msg.username);
                    $('#username').focus();
                } else if (msg.password) {
                    $('#dd_error_msg').css('display','block').html('<i></i>'+msg.password);
                    $('#password').focus();
                }
            } else {
                if (e.isframe) {
                    window.parent.location.href = e.url;
                } else {
                    window.location.href = e.url;
                }
            }
        });
    }


    $('#wx_login_btn').on('click',function () {
        $('#wx_div').css({'left':'0'});
    });
    $('.fixed_wx_act').on('click',function () {
        $('#wx_div').css({'left':'100%'});
    });

    $('#qq_login_btn').on('click',function () {
        window.open($(this).attr('data-url'));
    });


    //    登录框获取焦点变色换图
    $(function(){
        setTimeout(function(){
            if($("input[type='text']").val() != ""){
                $("input[type='text']").siblings('.input_arr').hide();
            }else{
                $("input[type='text']").siblings('.input_arr').show();
            }
            if($(".log_code").val() != ""){
                $(".log_code").siblings('.input_arr').hide();
            }else{
                $(".log_code").siblings('.input_arr').show();
            }
        },200)
    });

    //阻止密码输入复制粘贴
    $(function(){
        $("input:password").bind("copy cut paste",function(e){
            return false;
        })
    })

    function logImg(par1){
        $(par1).on('focus',function (){
            $(this).parent().addClass('log_hover');
        });
        $(par1).on('blur',function (){
            $(this).parent().removeClass('log_hover');
            if($(this).val() == ""){
                $(this).siblings('.input_arr').show();
            }
        });
        $(par1).on('keydown',function (){
            $(this).siblings('.input_arr').hide();
        });
    }
    logImg('.log_id')
    logImg('.log_code')

    $('.input_arr').on('click',function(){
        $(this).siblings('input').focus();
    })

    var wxRedirectUri = $('#wx_login_btn').attr('data-url');
    var obj = new WxLogin({
        id:"wx_qrcode",
        appid: "wxd2e1c893e6cdd045",
        scope: "snsapi_login",
        redirect_uri: wxRedirectUri,
        style: "black",
        href: "https://skin.huogou.com/css/wxlogin.css"
        //href: "https://skin.1yyg.com/Passport/css/layout.css"
    });

//刷新登陆变图
    $(document).ready(function(){
    var random_bg=Math.floor(Math.random()*3+1);
    var bg='url(../img/log_bg'+random_bg+'.jpg)';
    if(bg == "url(../img/log_bg1.jpg)"){
        $('.log_in').css('backgroundColor','#82ace8');
    }else if(bg == "url(../img/log_bg2.jpg)"){
        $('.log_in').css('backgroundColor','#6dbdfa');
    }else if(bg == "url(../img/log_bg3.jpg)"){
        $('.log_in').css('backgroundColor','#86d7ea');
    }
    $(".log_in").css("background-image",bg);
});