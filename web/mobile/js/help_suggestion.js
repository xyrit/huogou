$(function(){
      //发表评论成功按钮的单击事件
    $("#btnSuggestion").click(function () {
            var txt = $("#txtContent").val();   
            if(txt.length<150) return  $.PageDialog.fail("描述至少150字");
            var contact = $("#txtInfo").val();
            var data = {contact: contact, content: txt,type:1};
            //if ($(this).is(".orangeBtn")) return false;

            $.getJsonp(apiBaseUrl + '/suggestion/suggestion', data, function (json) {
                    if(json.code == "100") 
                    {
                        $.PageDialog.ok("提交成功!");
                        setTimeout(function(){history.back();},500);
                    }
                    else
                    {
                        $.PageDialog.fail(json.msg);
                    }
            });
        });
});