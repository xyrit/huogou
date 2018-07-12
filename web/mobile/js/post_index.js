/**
 * Created by jun on 15/12/3.
 */
$(function() {
    var data = {page:1,perpage:20,catId:0,orderFlag:10};
    //// 如果有分页或链接的话 这里需要获取get参数更新这些数据
    var liList =  $('.wx-port-nav li');
    liList.click(function(){
            liList.removeClass('current');
            $(this).addClass('current');
            data.orderFlag = $(this).attr('order');
            pageRefresh();
    }).eq(0).click();;
    
    // 分类菜单的单击
    $.getJsonp(apiBaseUrl+'/product/catlist',data,function(json) { 
            var box = $("#divSortMenu ul").empty().append('<li class="current" sortid="0"><a href="javascript:;">全部分类</a></li>');
            $(json.list).each(function(i,v){
                box.append('    <li sortid="'+v.id+'"><a href="javascript:;">'+v.name+'</a></li>');
            })
            var mList = $("#divSortMenu li");
             mList.click(function(){
                    mList.removeClass("current");
                    $(this).addClass("current");
                    $(".column").click().text($(this).text());
                   data.catId= $(this).attr("sortid");
                   pageRefresh();
            });
    })

    // 全部分类菜单
    $(".column").click(function(){
        $("#divSortMenu").is(":hidden") ? $("#divSortMenu").show() : $("#divSortMenu").hide();
    });
    $(".wx-port-nav li").click(function(){
        $("#divSortMenu").hide();
    });

     // 刷新页面
    function pageRefresh(padd)
    {
            data.page = padd ?  data.page+1 : 1;
            if(!padd)  $('#divPostList').html('');
            $('#postLoading').show();
            $.getJsonp(apiBaseUrl+'/share/topic-list',data,function(json) {
                createShareListHtml(json);
            });
            
    }
    
    $("#btnLoadMore").click(function(){
            pageRefresh(1);
    })


function createShareListHtml(json) {
    var html = '';
    $.each(json.list,function(i,v) {
        var postid = v.id;
        var title = v.title;
        var content = v.content;
        var created_at = v.created_at;
        var pictures = v.pictures;
        var header_image = v.header_image;
        var userName = v.user_name;
        var userHomeId = v.user_home_id;
        var userCenterUrl = createUserCenterUrl(userHomeId);
        var postDetailUrl = createPostDetailUrl(v.id);
        var headerImageUrl = createShareImgUrl(header_image,'main');
        html += '<div class="show-list">';
        html += '<a href="'+postDetailUrl+'"><h3>'+title+'</h3></a>';
        html += '<div class="show-head">';
        html += '<a href="'+userCenterUrl+'" class="show-u blue">'+userName+'</a>';
        html += '</div>';
        html += '<a href="'+postDetailUrl+'">';
        html += '<div class="show-pic">';
        html += '<ul class="pic-more clearfix">';

        $.each(pictures.slice(0,3),function(i,v) {
            var imgUrl = createShareImgUrl(v,'small');
            html += '<li><img src="'+imgUrl+'" /></li>';
        });
        html += '</ul>';
        html += '</div>';
        html += '<div class="show-con">';
        html += '<p name="content">'+content.substring(0,100)+'…</p>';
        html += '<span class="show-time">'+created_at+'</span>';
        html += '</div></a>';
//        html += '<div class="share-btn-wrap" postid="'+postid+'" postimg="'+headerImageUrl+'">';
//        html += '<i></i>分享';
//        html += '</div>';
        html += '</div>';
    });

    data.page>1 ?     $('#divPostList').append(html) : $('#divPostList').html(html);
    $('#postLoading').hide();
    (json.totalPage >data.page) ? $("#btnLoadMore").show() : $("#btnLoadMore").hide();
    
}

});