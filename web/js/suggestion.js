$('#imgCode').on('click', function() {
    $('#imgCode').attr('src', passportBaseUrl +'/api/captcha?v='+Math.random())
});

$('#btnChangeCode').on('click', function() {
    $('#imgCode').attr('src', passportBaseUrl +'/api/captcha?v='+Math.random())
});

function checkPhone()
{
    var phone = $('#suggestionform-phone').val();
    if(phone){
        var telReg = !!phone.match(/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/);
        if(telReg == false){
            $('.phone-error').html('');
            $('.phone-error').html('手机号不正确');
            $('#button').attr('disabled', 'disabled');
            $('#button').attr('class', 'a_gary');
            return false;
        }else{
            $('.phone-error').html('');
            $('#button').removeAttr('disabled');
            $('#button').attr('class', 'a_but');
        }
    }
}

$('#suggestionform-phone').blur(function(){
    checkPhone();
})

function checkEmail(){
    var email = $('#suggestionform-email').val();
    if(email){
        var emailReg = !!email.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/);
        if(emailReg == false){
            $('.email-error').html('');
            $('.email-error').html('邮箱不正确');
            $('#button').attr('disabled', 'disabled');
            $('#button').attr('class', 'a_gary');
            return false;
        }else{
            $('.email-error').html('');
            $('#button').removeAttr('disabled');
            $('#button').attr('class', 'a_but');
        }
    }else{
        $('.email-error').html('');
        $('.email-error').html('邮箱必填');
        $('#button').attr('disabled', 'disabled');
        $('#button').attr('class', 'a_gary');
        return false;
    }
}

$('#suggestionform-email').blur(function(){
    checkEmail();
})

function checkText()
{
    var lenE = $('#textarea').val().length;
    if(lenE < 50){
        $('.text-error').html('');
        var need = 50 - lenE;
        $('.text-error').html('字数必须大于50，还需'+need);
        $('#button').attr('disabled', 'disabled');
        $('#button').attr('class', 'a_gary');
        return false;
    }else{
        $('.text-error').html('');
        $('#button').removeAttr('disabled');
        $('#button').attr('class', 'a_but');
    }
}

$('#textarea').blur(function(){
    checkText();
})

$('#button').click(function(){
    if(checkEmail() == false){
        $('#button').attr('class', 'a_gary');
        return false;
    }else{
        $('#button').attr('class', 'a_but');
    }

    if(checkText() == false){
        $('#button').attr('class', 'a_gary');
        return false;
    }else{
        $('#button').attr('class', 'a_but');
    }

    if(checkPhone() == false){
        $('#button').attr('class', 'a_gary');
        return false;
    } else{
        $('#button').attr('class', 'a_but');
    }
    $('form').submit();
})

$(function(){
    var status = $('#button').attr('data-id');
    var urls = 'http://help.' + baseHost + '/suggestion.html';
    if(status == 1){
        $('.safety-b-box').html('<i id="safety-b-close"></i><h4>提交成功</h4>');
        $('#safety-b-con').fadeIn();
        $('#safety-b-close').on('click',function(){
            $('#safety-b-con').fadeOut();
        })
        setTimeout(function(){
            window.location.href = urls;
        },2000)

    }
})