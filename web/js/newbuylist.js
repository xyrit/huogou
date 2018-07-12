$(function(){
	$.getContent(apiBaseUrl + '/buylist/new-buy-list',"",'newBuyList');
})

function success_newBuyList(json){
	if (json.list.length > 0) {
		$.each(json.list,function(i,v){
			var	item = '<tr>';
				item += '<td>';
				item += v.buy_time;
				item += '</td>';
				item += '<td class="blue">';
				item += '<a href="'+createUserCenterUrl(v.user_id)+'" target="_blank">'+v.user_name+'</a>';
				item += '</td>';
				item += '<td>';
				item += '<a href="'+createGoodsUrl(v.product_id)+'" target="_blank">'+v.product+'</a>';
				item += '</td>';
				item += '<td>';
				item += v.buy_nums+'人次';
				item += '</td>';
				item += '</tr>';
			$(".history-tbable tbody").append(item);	
		})
	};
}