var uploadUrl = 'http://group.'+ baseHost + '/topic/upload-topic-image';
var groupBaseUrl = 'http://group.'+ baseHost;
var editor;

$(document).ready(function(){
    editor = KindEditor.create('#hg-neweditor',{
        resizeType : 0,
        allowPreviewEmoticons : true,
        allowImageUpload : true,
        items : ['emoticons','fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
            'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
            'insertunorderedlist', '|',  'multiimage', 'link'],
        uploadJson : 'http://api.'+ baseHost + '/group/upload-topic-img' ,
        width : '895px',
        height : '396px',
        layout: '<div class="container"><div class="toolbararea"><div class="toolbar"></div></div><div class="edit"></div><div class="edit-info edit-info2"><a href="javascript:;" id="edit-cancle">取消</a><a href="javascript:;" id="edit-submit" style="padding:0 10px">立即发送</a></div><div class="statusbar"></div></div>'
    });

    KindEditor.ready(function(K) {
        $('.hg-neweditor-ipt').find('*').on('click',function(){
            var user = $('.hg-neweditor-ipt input[type="text"]').attr('data-id');
            var join = $('.hg-neweditor-ipt input[type="text"]').attr('join-id');
            if(!user){
                showLoginForm();return false;
            }

            if(!join){
                $('.safety-b-box h3').html('请先加入');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }

            $(this).css({'width' : '875px'});
            $('.hg-neweditor-container').show();
            $('.topicBtn').hide();
            if($('.hg-neweditor-ipt input[type="text"]').val() == '点击展开'){
                $('.hg-neweditor-ipt input[type="text"]').val('').attr('placeholder', '请输入标题');
            }
        })
        $('#edit-cancle').on('click',function(){
            $('.hg-neweditor-ipt').show();
            $('.hg-neweditor-container').hide();
            $('.hg-neweditor-ipt input[type="text"]').css({'width' : '760px'});
            $('.hg-neweditor-ipt input[type="text"]').val('').attr('placeholder', '点击展开');
            $('.topicBtn').show();
        })

        $('#edit-submit').click(function(){
            var title = $('.hg-neweditor-ipt input[type="text"]').val();
            title = title.replace(/\s+/g,"");
            var content = filterContent(editor.html());
            var test=content.replace(/&nbsp;/ig, "");
            var strimContent = test.replace(/\s+/g,"");
            var groupId = $('input[name="Topic[groupId]"]').val();
            var titlestr = (title.replace(/\w/g,"")).length;
            var titlelen = titlestr*2+(title.length - titlestr);
            var contentstr = (strimContent.replace(/\w/g,"")).length;
            var contentlen = strimContent*2+(strimContent.length - contentstr);

            if(title == '' || strimContent == ''){
                $('.safety-b-box h3').html('标题或内容不为空');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }

            if(titlelen < 12 || titlelen > 80){
                $('.safety-b-box').html('<h2 style="font-size:14px;">标题字数大于5小于40</h2>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }

            if(contentlen < 40 || titlelen > 2000){
                $('.safety-b-box').html('<h2 style="font-size: 14px">内容字数大于40小于2000</h2>');
                $('#safety-b-con').fadeIn();
                setTimeout(function(){
                    $('#safety-b-con').fadeOut();
                },1000);
                return false;
            }
            $('#edit-submit').attr('disabled', 'disabled');

            $.ajax({
                async: false,
                url: 'http://api.'+ baseHost + '/limit/topic-num' ,
                type: "GET",
                dataType: 'jsonp',
                jsonp: 'callback',
                data: {},
                success: function (data) {
                    if(data.code != 100){
                        $('.safety-b-box h3').html(data.message);
                        $('#safety-b-con').fadeIn();
                        setTimeout(function(){
                            $('#safety-b-con').fadeOut();
                        },1000)
                    }else if(data.code == 100){
                        var urls = groupBaseUrl + '/default/add-topic';
                        $.post(urls, {'Topic[title]': title, 'Topic[content]' : content, 'Topic[groupId]':groupId }, function (data) {
                            if(data == 2){
                                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>请等待审核</h4>');
                                $('#safety-b-con').fadeIn();
                                setTimeout(function(){
                                    $('#safety-b-con').fadeOut();
                                },1000)
                                setTimeout(function(){
                                    window.location.reload();
                                },500)
                            }else if(data != 0){
                                $('.safety-b-box').html('<i id="safety-b-close"></i><h4>发表成功</h4>');
                                $('#safety-b-con').fadeIn();
                                setTimeout(function(){
                                    $('#safety-b-con').fadeOut();
                                },1000)
                                setTimeout(function(){
                                    window.location.href=groupBaseUrl + "/topic-" + data + '.html';
                                },500)
                            }else if(data == 0){
                                $('.safety-b-box').html('<h2 style="font-size: 14px">您的内容里含有敏感词汇</h2>');
                                $('#safety-b-con').fadeIn();
                                setTimeout(function(){
                                    $('#safety-b-con').fadeOut();
                                },1000);
                                $('#edit-submit').attr('disabled', false);
                                return false;
                            }
                        });
                    }
                }
            })
        })
    });
})

function filterContent(content){
    var con = content.replace(/<img[^>]*src=\"[\w:\.\/-]+\/([\d]{1,2})\.gif\"[^>]*>/ig, "[s:$1]").replace(/<img[^>]*src=[\'\"\s]?([\w:\.\/]+([\d]{17}\.(jpg|gif))[\s\'\"]+)[^>]*>/ig, "[img]$2[/img]").replace(/<a[^>]*href=[\'\"\s]?([^\s\'\"]*)[^>]*>(.+?)<\/a>/ig, "[url=$1]$2[/url]");

    return con;
}