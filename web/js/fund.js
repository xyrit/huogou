var lastBuyTime = 0;
$(function(){
	$.getContent(apiBaseUrl + '/product/new-buy-list?num=20',{},'fundList');


	$(function(){
		setInterval('autoScroll(".maquee")',3000);
	})
})

function autoScroll(obj){
	if($('.maquee').find('li').length <= 10){
		return;
	}
	$(obj).find("ul").animate({
		marginTop : "-65px"
	},500,function(){
		//$(this).children('li').css({borderTop:"1"}).find("li:first").css({borderTop:"0"});
		$(this).css({marginTop : "0px"}).find("li:first").appendTo(this);
	})
}

function success_fundList(json){
	if (json) {
		$.each(json,function(i,v){
			var item = '';
				item += '<li>';
				item += '<a href="'+createUserCenterUrl(v.home_id)+'">';
				item += '<picture>';
				item += '<img src="'+createUserFaceImgUrl(v.avatar,80)+'">';
				item += '</picture>';
				item += '</a>';
				item += '<summary>';
				item += '<a href="'+createUserCenterUrl(v.home_id)+'" target="_blank"><h4>'+v.username+'</h4></a>';
				item += '<h5>'+ v.created_at+'伙购'+v.buy_num+'人次，贡献'+v.buy_num+'份爱心</h5>';
				item += '</summary>';
				item += '</li>';
			lastBuyTime = lastBuyTime > v.buy_time ? lastBuyTime : v.buy_time;
			var l = $(".con-right-wrap ul li").length;
	        if (l>=20) {
	            $('.con-right-wrap ul li:last').remove();
	            $('.con-right-wrap ul').prepend(item);
	        }else{
	            $('.con-right-wrap ul').append(item);
	        }

		})

	};
}