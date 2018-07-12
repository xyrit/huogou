/**
 * Created by han on 2015/9/8.
 */
    //主侧导航显示隐藏
$("#divGoodsSort").mouseover(function(){
    $(this).find("#divSortList").show();
}).mouseout(function(){
    $(this).find("#divSortList").hide();
})


//个人中心侧边导航
$(".sidebar-nav h3").click(function(){
    $(this).toggleClass("sid-iconcur");
    if($(this).is('.sid-iconcur')){
        $(this).next("ul").children("li").hide();
    }else{
        $(this).next("ul").children("li").show();
    }
})


//点击li加cur效果
$(".sidebar-nav ul li").click(function(){
    $(this).parents(".sidebar-nav").find("li").removeClass("sid-cur");
    $(this).addClass("sid-cur");
})


//tab切换
$(".subMenu a").click(function(){
    //alert($(".subMenu a").eq(0).html());
    //alert($(this).index());
    var aIndex = $(this).index();
    $(this).addClass("current").siblings("a").removeClass("current");
    $(".single-C").hide();//获得商品
    $(".page_nav").hide();//获得商品
    $(".list-tab").hide();//获得商品
    $("#tbList"+aIndex).css('display','block'); //获得商品
    $("#PostList"+aIndex).css('display','block'); //获得商品
    $("#divTopic"+aIndex).css('display','block'); //获得商品
    $("#divPageNav"+aIndex).css('display','block');

})


