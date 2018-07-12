$(function(){
      check_transfer_premise();     //检查页面载入是否正常
        
        //回车提交表单
        $('input').keydown(function(event){
            if(event.keyCode == 13) $("#btnSubmit").click();
        })
     //提交按钮单击
       //提交按钮单击
    $("#btnSubmit").click(function(){
        if($(this).text() == '转账中...') return false;
        if(! check_transfer_form()) return;         //检查字段规范
        $(this).css("background-color","#ccc").text("转账中...");
        
        var url = apiBaseUrl+"/recharge/transfer";
        var data = {account:$('#txtMoney').val(),username:$('#txtAccount').val(),comment:$('#txtMemo').val(),paypwd:$('#txtPaypwd').val()};
        var succfunc = function(json) {
            if (json.code=="100") {
                $.PageDialog.ok('转账成功',function() {
                     setTimeout(function(){window.history.back();},500);
                });
            } else {
                $.PageDialog.fail(json.msg);
                $("#btnSubmit").css("background-color","#f60").text("确认提交");
            }
        }
         $.jsonp({
            url: url,
            data: data,
            callbackParameter: "callback",
            success: succfunc,
            error: function (xOptions, textStatus) {
                $.PageDialog.fail("服务器错误,请稍后重试");
                $("#btnSubmit").css("background-color","#f60").text("确认提交");
            }
        });
        //$.getJsonp(url,data,succfunc);
    })
   
    
    function check_transfer_form()
    {
        var money = $('#txtMoney').val();
        var account = $('#txtAccount').val();
        var memo = $('#txtMemo').val();
        var paypwd = $('#txtPaypwd').val();
        
        if (money.length==0) {
            return $.PageDialog.fail('请填写转账金额');

        }
        else if(! money.match(/^[\d\.]+$/))
        {
            return $.PageDialog.fail('转账金额必须是数字');
        }
        else 
        {
            money = parseInt(money);
            if(money>getTotel() | money<=0)
                return $.PageDialog.fail('转账金额不正确');
        }
        
        if (account.length==0) {
            return $.PageDialog.fail('请填写收款账号');
        }
        else if(!account.match(/^1\d{10}$|\w@.+/))
        {
            return $.PageDialog.fail('请正确填写收款帐号信息');
        }
        
         if (paypwd.length==0) {
              return $.PageDialog.fail('请填写支付密码');
         }
         
          return true;
    }
    
    function check_transfer_premise()
    {
        if (getTotel() <= 0)        return $.PageDialog.fail("您当前账户没有余额 请先充值，再进行转账操作");

        if (!isSetPhone()) {
            setTimeout(function () {
                location.href = "/member/mobilecheck.html";
            }, 2000);

            return $.PageDialog.fail("需要验证手机才能进行转账");
        }
        if (!isSetPayPassword()) {
            setTimeout(function () {
                location.href = "/member/paypwdcheck.html";
            }, 2000);

            return $.PageDialog.fail("需要设置支付密码才能进行转账");
        }
    }
    
     function getTotel()
    {
        return $("#totel").val();
    }
    
     function isSetPhone()
    {
        return $("#isSetPhone").val();
    }
    
    function isSetPayPassword()
    {
        return $("#isSetPayPassword").val();
    }
    
});