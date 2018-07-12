
function createAreaSelectHtml(json, obj, firstOptionHtml) {
    var html = '';
    html += firstOptionHtml;
    $.each(json, function (i, v) {
        var id = v.id;
        var name = v.name;
        var pid = v.pid;

        html += '<option value="' + id + '">' + name + '</option>';
    });
    obj.html(html);
}

$(function () {
     var money = parseInt($('#txtMoney').val());
     $("input").each(function(){$(this).keyup(function(){
         if(this.value ) $(this).css("color","#666");  
         else if(! this.value) $(this).css("color","#bbb");
     })});;
 
    var pid = 0;
    if( parseFloat($('#emCurrMoney').text()) < 100) { $.PageDialog.fail('提现金额必须大于100! ');  setTimeout(function(){window.history.back()},2000);}
    
    $.getJsonp(apiBaseUrl + '/record/area-list', {pid: pid}, function (json) {
        createAreaSelectHtml(json, $('#selAreaA'), '<option value="-1">请选择所在省份</option>');
        $('#selAreaA').on('change', function () {
             var id = $(this).val();
             $.getJsonp(apiBaseUrl + '/record/area-list', {pid: id}, function (json) {
                 createAreaSelectHtml(json, $('#selAreaB'),'<option value="-1">请选择所在城市</option>');
             });
         });
    });
    
    
    //提交按钮   
    $('#btnSure').on('click', function() {
        var rt=true;
        $("input").each(function()
        {
            if(!rt || this.value) return;
            $.PageDialog.fail("请填写" + $(this).parents('li').find('span').text().replace("：",''));
            $(this).focus();
            rt = false;
        });
        if(!rt) return;
        
         var prov = $('#selAreaA').val();
         if (prov<=0)   return $.PageDialog.fail('请选择所在省份');
         
        var city = $('#selAreaB').val();
        if (city<=0)   return $.PageDialog.fail('请选择所在城市');
        
        var money = parseFloat($('#txtMoney').val());
        if(isNaN(money) || money <0 || money >parseFloat($('#emCurrMoney').text()))       return $.PageDialog.fail('请正确填写提现金额');
        if(! $("#txtAccount").val().match(/^\d+$/) || $("#txtAccount").val().length<8)  return $.PageDialog.fail('请正确输入银行帐号');
        if(!/^(13[0-9]|14[0-9]|15[0-9]|18[0-9])\d{8}$/i.test($("#txtPhone").val())  )  return $.PageDialog.fail('请输入正确的电话号码');
        
        var data = {money: money,account:$("#txtUserName").val(),bank:$("#txtBank").val(),branch:$("#txtSubBank").val(),bank_number:$("#txtAccount").val(),phone:$("#txtPhone").val()};
        
        $.getJsonp(apiBaseUrl+'/invite/mention-apply',data,function(json) {
            if (json.error==0) {
                $.PageDialog.ok('提现申请成功',function() {
                    window.history.back();
                });
            } else {
                $.PageDialog.fail('提现申请失败! ');
            }
            return;
        });
    });

});

