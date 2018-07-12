$(function(){
	var data = {o:getUrlParam('o'),'token':$("input[name=t]").val()};
	$.getContent(apiBaseUrl+'/pay/result',data,'payresult');
})

function success_payresult(json){
	if (json.code == 0) {

	}else if(json.code == 100){
		$(".g-resulst-prompt").html('<span>'+json.buy.length+'件商品购买成功</span><b></b>');
		$.each(json.buy,function(i,v){
			var item = '';
			item += '<dd>';
			item += '<span class="u-results-name">';
			item += '<a href="/product/'+v.product_id+'.html" title="(第'+v.period_number+'云)'+v.name+'" target="_blank">(第'+v.period_number+'云)'+v.name+'</a>';
			item += '</span>';
			item += '<span class="u-results-time">'+v.buy_time+'</span>';
			item += '<span class="u-results-visitors">'+v.nums+'</span>';
	        item += '<span class="u-results-code">';
			item += '<em>';                        
			item += '<a href="http://member.1yyg.com/UserBuyDetail-1506400.do" target="_blank">查看所有伙购码</a>';
			item += '</em>';
			item += '</span>';
			item += '</dd>';
			$(".g-results-info dl").append(item);
		})
	}
}