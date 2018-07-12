/**
 * Created by han on 2015/9/7.
 */

$(function(){
	//顶部二维码云购下拉菜单展示
	$("#liMobile").hover(function(){
		$(this).toggleClass("u-arr-hover");
	})
	$("#liMember").hover(function(){
		$(this).toggleClass("u-arr-hover");
	})


	//移动到侧边主导航 变色并添加下划线阴影
	$("#divSortList dl").hover(function(){
		$("#divSortList dl").children("i").remove();
		$(this).toggleClass("hover");
		$(this).append("<i></i>");
	})
})


//文字滚动效果
var p_timer = null;
function mt0(){
	var liHeight = $("#UserBuyNewList li").outerHeight();
	var lastLi = $("#UserBuyNewList li:last-child").html(); //取到最后一个li中的内容
	lastLi = "<li>"+lastLi+"</li>";  //加上li标签
	$("#UserBuyNewList li:last-child").remove(); //删除最后一个li
	var ulHtml = lastLi+$("#UserBuyNewList").html();  //重新排列ul中的li标签，最后一个li加上ul中剩余的  ***
	$("#UserBuyNewList").html("").html(ulHtml);        //清空ul的html  加上重新排列的html  ***
	$("#UserBuyNewList").css('marginTop','-'+liHeight+'px');
	$("#UserBuyNewList").animate({marginTop:"0px"},1000);
}
$(function(){
	clearInterval(p_timer);
	p_timer = setInterval("mt0()",3000);
})


//弹窗展示盒隐藏
//显示弹窗
function pageShow(){
	$("#pageDialogBG").show();
	$("#pageDialogBorder").show();
	$("#pageDialog").show();
}
//确认关闭弹窗
function pageHide(){
	$("#pageDialogBG").hide();
	$("#pageDialogBorder").hide();
	$("#pageDialog").hide();
}




