var baseHost = getHost();
var uBaseUrl = 'http://u.'+ baseHost;

$("#btnSendMsg").click(function(){
     $("#showMessage").show();

});

$('.btnAddFriend').click(function(){
    var apply = $(this).attr('data-id');
    var apply_url = uBaseUrl + '/default/apply-friend';

    if(apply){
        $.get(apply_url, {'id':apply}, function(data){
            if(data == 1){
                alert('请先登录');
            }else if(data == 2){
                alert('请求已发送，请等待通过')
            }else if(data == 0){
                alert('申请成功');
            }
        })
    }
})

$(function() {
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('#private_message_editor', {
            resizeType : 2,
            allowPreviewEmoticons : false,
            allowImageUpload : true,
            minWidth: 50,
            minHeight: 50,
            uploadJson : '',
            items : [
                'emoticons'
            ]
        });
    });
})

function getHost(url) {
    var host = "null";
    if (typeof url == "undefined"
        || null == url)
        url = window.location.href;
    var regex = /.*\:\/\/([^\/|:]*).*/;
    var match = url.match(regex);
    if (typeof match != "undefined"
        && null != match) {
        host = match[1];
    }
    if (typeof host != "undefined"
        && null != host) {
        var strAry = host.split(".");
        if (strAry.length > 1) {
            host = strAry[strAry.length - 2] + "." + strAry[strAry.length - 1];
        }
    }
    return host;
}

//私信
