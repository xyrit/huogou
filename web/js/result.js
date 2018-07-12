var b = 1;
var data;
$(function(){
	r = $('#rorder').val();
	if (r.length>0) {
		var t = setInterval(function() {
			$.getJsonp(apiBaseUrl+'/recharge/result',{o:r},function(json) {
				if (typeof json.url!='undefined') {
					clearInterval(t);
					if (json.type==1) {
						location.href = json.url;
						return;
					} else if(json.type==2) {
						data = {o:json.order};
						$.getContent(apiBaseUrl+'/pay/pay-order',data,'payorder');
						$.getContent(apiBaseUrl+'/pay/result',data,'payresult');
					}
				}
			});
		},1000);
	} else {
		data = {o:$("#order").val()};
		$.getContent(apiBaseUrl+'/pay/result',data,'payresult');
	}

});

function success_payorder(json) {

}


function success_payresult(json){
	if (json.code == 0) {
		setTimeout(function(){
			$.getContent(apiBaseUrl+'/pay/result',data,'payresult');		
		},2000);
	}else if(json.code == 100){
			$(".shopCart_total").html(json.count);
		var html =  (json.success.length > 0 || json.some.length > 0) ? '<h3 class="payment-success-title">伙购成功！请耐心等待揭晓结果 !</h3>' : '<h3 class="failure-pay-title">支付失败！您下手太慢了 !</h3>';
			html += "<summary>";
			html += '<a href="'+memberBaseUrl+'/default/buy-list">查看伙购记录</a><a href="/list.html">继续伙购</a>';
			html += '</summary>';
			if (json.some.length > 0) {
				html += '<div class="payment-success-piece">';
	 			html += '<span><i>'+json.some.length+'</i>件商品部分人次失败</span>';
	 			html += '</div>';
	 			html += '<table class="payment-success-table">';
	 			html += '<thead>';
				html += '<tr>';
				html += '<td width="65%">商品名称</td>';
				html += '<td width="35%"></td>';
				html += '</tr>';
				html += '</thead>';
				html += '<tbody>';
				$.each(json.some,function(i,v){
					html += '<tr>';
					html += '<td>';
					html += '<a href="/product/'+v.product_id+'.html" title="'+v.name+'" target="_blank" style="width:auto">'+v.name+'</a>';
					html += '</td>';
					html += '<td>';
					html += '成功支付￥'+v.nums+'.00，剩余金额￥'+parseInt(v.post_nums-v.nums)+'.00已退回您的账户,<a href="'+memberBaseUrl+'" style="color:red;display:inline">查看</a>';
					html += '</td>';
					html += '</tr>';
				})
				html += '</tbody>';
				html += '</table>';
			};
			if (json.fail.length > 0) {
				html += '<div class="payment-success-piece">';
	 			html += '<span><i>'+json.fail.length+'</i>件商品伙购失败</span>';
	 			html += '</div>';
	 			html += '<table class="payment-success-table">';
	 			html += '<thead>';
				html += '<tr>';
				html += '<td width="65%">商品名称</td>';
				html += '<td width="35%"></td>';
				html += '</tr>';
				html += '</thead>';
				html += '<tbody>';
				$.each(json.fail,function(i,v){
					html += '<tr>';
					html += '<td>';
					html += '<a href="/product/'+v.product_id+'.html" title="'+v.name+'" target="_blank" style="width:auto">'+v.name+'</a>';
					html += '</td>';
					html += '<td>';
					html += '支付失败，支付金额￥'+v.nums+'.00已退回您的账户,<a href="'+memberBaseUrl+'" style="color:red;display:inline">查看</a>';
					html += '</td>';
					html += '</tr>';
				})
				html += '</tbody>';
				html += '</table>';
			};
			if (json.success.length > 0) {
		 			html += '<div class="payment-success-piece">';
		 			html += '<span><i>'+json.success.length+'</i>件商品伙购成功</span>';
		 			html += '</div>';
		 			html += '<table class="payment-success-table">';
		 			html += '<thead>';
					html += '<tr>';
					html += '<td width="42%">商品名称</td>';
					html += '<td width="20%">伙购时间</td>';
					html += '<td width="20%">伙购人次</td>';
					html += '<td width="18%"></td>';
					html += '</tr>';
					html += '</thead>';
					html += '<tbody>';
				$.each(json.success,function(i,v){
					html += '<tr>';
					html += '<td>';
					html += '<a href="/product/'+v.product_id+'.html" title="'+v.name+'" target="_blank">'+v.name+'</a>';
					html += '</td>';
					html += '<td>';
					html += v.item_buy_time;
					html += '</td>';
					html += '<td>';
					html += v.nums;
					html += '</td>';
					html += '<td>';
					html += '<a class="orange" href="'+memberBaseUrl+'/default/buy-detail?id='+v.period_id+'">查看所有伙购码</a>';
					html += '</td>';
					html += '</tr>';
				})
				html += '</tbody>';
				html += '</table>';
			}
		$("section .payment-result").html(html);
	}else if (json.code == 201) {
		var html = '<h3 class="failure-pay-title">支付失败！您下手太慢了 !</h3>';
			html += '<summary>';
			html += '<a href="'+memberBaseUrl+'/default/buy-list">查看伙购记录</a><a href="/list.html">继续伙购</a>';
			html += '</summary>';
		$("section .payment-result").html(html);	
	};
}