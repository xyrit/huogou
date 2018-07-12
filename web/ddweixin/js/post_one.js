/**
 * Created by jun on 15/12/3.
 */
$(function() {
    $('#btnUpload').on('change', function() {
        startUploadImage();
    });
    
    //单击图片
    $('#ulPicBox').on('click',"li.item", function() {
        var box = $("#prePicBox");
        //box.find("span").toggle();
        $("#imgBig").attr("src",$(this).find("img").attr("src")).css("width","100%").show();
        box.show();
        delImg.edit = this;
    });
    
    //关闭编辑页
    $("#btnClose").click(function(){
            $("#prePicBox").hide();
    })
    
    //删除图片操作
    function delImg()
    {
         var e = $(delImg.edit);
        var m =e.find('img').attr("src").replace(/.+\//,'');
        var v = $("#hidPicAll").val();
        if(! v.match(m)) return ;
        e.remove();
        $("#hidPicAll").val(v.replace(m,'').replace(/,,/,',').replace(/^,|,$/,''));
    }
    
    //删除图片
    $("#btnDelete").click(function(){
            $.PageDialog.confirm("确定要删除该图片吗?",function(){
                    delImg();
                    $("#btnClose").click();
            })
    })

    $('#btnSubmit').on('click', function() {
        if($(this).is(".z-grayBtn")) return;
        
        var title = $('#txtTitle').val();
        var content = $('#txtContent').val();
        var picture = $('#hidPicAll').val();
        if (title.length==0) {
            $.PageDialog.fail('请填写晒单标题');
            return;
        }
        if (content.length==0) {
            $.PageDialog.fail('请填写晒单内容');
            return;
        }
        if (picture.split(',').length<3) {
            $.PageDialog.fail('晒单图片至少3张');
            return;
        }
        $(this).addClass("z-grayBtn");
        var orderId = getOrderid();
        var shareid = getShareid();
        if (orderId) {
        var data = {title:title,content:content,picture:picture,"order_id":orderId};
        }else if(shareid) {
        var data = {title:title,content:content,picture:picture,"id":shareid};
        }
        var url = orderId ? apiBaseUrl+"/share/add-share" : apiBaseUrl+"/share/edit-share";
        $.getJsonp(url,data,function(json) {
            $("#btnSubmit").removeClass("z-grayBtn");
            
            if (json.code ==100) {
                $.PageDialog.ok('发布晒单成功',function() {
                    window.location.href = (orderId) ? "/member/orderdetail-"+orderId+".html" : "/member/postlist.html"; 
               });
            } else {
                var errMsg = '发布晒单失败';
                if (typeof json.msg !='undefined') {
                    for(var p in json.msg) {
                        errMsg = json.msg[p];
                        break;
                    }
                }
                $.PageDialog.fail(errMsg,function() {
                        
                });
            }
        });
    });

});

function getOrderid()
{
    var m =[];
    if(m = document.URL.match(/postone-(\d+)/))
    return m[1];
}

function getShareid()
{
    var m =[];
    if(m = document.URL.match(/post-(\d+)/))
    return m[1];
}

function startUploadImage()
{
        $("#imageLoading").show();
        $('#pageForm').submit();
}

function successUploadImage(data) {
    $("#imageLoading").hide();
    
    data = JSON.parse(data);
    if (data.error) {
        return $.PageDialog.fail(data.message);
    }
    var imgUrl = createShareImgUrl(data.basename, 'share');

    var liImg = '<li class="item"> <img src="'+imgUrl+'" border="0" alt="" width="50" height="50" /></li>';
    $('#imageLoading').before(liImg);

    var picAll = $('#hidPicAll').val();
    if (picAll.length>0) {
        $('#hidPicAll').val(picAll+','+data.basename);
    } else {
        $('#hidPicAll').val(data.basename);
    }

}
