$(function(){
	$.getContent(apiBaseUrl+'/cart/list',{"is_buy":1,'token':$("input[name=t]").val()},'ordercheck');
	$.getContent(apiBaseUrl+'/user/getmoney',{'token':$("input[name=t]").val()},'getusermoney');
	
	$("#checkBalance").click(function(){
		var choosed = $(this).html();
		if (choosed.length > 0) {
			$(this).find("b").remove();
			$("#spBalance").hide();
			$("#divBankBox").show();
			// $("#hidPayType").val($("#hidPayType").val().replace(/balance\+/,''));
			$("#iBankPay").text("￥"+$("#divPayWayBox i").attr("t")+".00");
			$("#hidUseBalance").val(0);
		}else{
			$(this).html('<b class="z-comms"></b>');
			$("#spBalance").show();
			var b = parseInt($("#userBalance").attr("b"));
			var t = parseInt($("#divPayWayBox i").attr("t"));

			if (b >= t) {
				$("#divBankBox").hide();
				$("#iBankPay").text("￥"+parseInt(b-t)+".00");
			};
			// $("#hidPayType").val("balance+"+$("#hidPayType").val());
			
			$("#hidUseBalance").val(b);
		}
	})
	$("#divBankBox input[type=radio]").click(function() {
		$("#divBankBox input[type=radio]").parent().removeClass("checked");
		$(this).parent().addClass("checked");
		var payType = $(this).parent().parent().attr("id");
		$("#hidPayType").val($(this).parent().parent().attr("id"));
		$("#hidPayName").val($(this).val());
	});
})

function success_ordercheck(json){
	var total = 0;
	if (json.length <= 0) {
		window.location.href = '/cart.html';
		return false;
	};
	$.each(json,function(i,v){
		var item = '';
		item += '<dd>';
		item += '<ul>';
		item += '<li class="f-pay-comm">';
		item += '<cite class="u-pay-pic">';
		item += '<a href="/product/'+v.product_id+'.html" target="_blank" title="'+v.name+'">';
		item += '<img alt="" src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'">';
		item += '</a>';
		item += '</cite>';
		item += '<cite class="u-pay-name limitbuy ">';
		item += '<em class="limitbuy-icon"></em>';
		item += '<a href="/product/'+v.product_id+'.html" target="_blank" title="'+v.name+'">(第'+v.period_number+'云) '+v.name+'</a>';
		item += '</cite>';
		item += '</li>';
		item += '<li class="f-pay-price">'+parseInt(v.price-v.sales_num)+'</li>';
		item += '<li class="f-pay-plusLess">'+v.nums+'人次</li>';
		item += '<li class="f-pay-subtotal orange">￥'+v.nums+'.00</li>';
		$("#divCartList dl").append(item);
		total = parseInt(total)+parseInt(v.nums);
	});
	$("#divPayWayBox dt").append('<span class="fr">支付总额：<i class="orange" t="'+total+'">￥'+total+'.00</i></span>');
	var balance = parseInt($("#hidUseBalance").val());
	// while(balance == 0){}
	if (balance >= total) {
		$("#spBalance").text("-￥"+total+".00");
	}else{
		$("#spBalance").text("-￥"+balance+".00");
		$("#iBankPay").text("￥"+parseInt(total-balance)+".00");
		$("#divBankBox").show();
		$("#hidPayType").val("dlCXK");
		$("#hidPayName").val($("#divBankBox dd[class=checked]").find("input").val());
	}
}

function success_getusermoney(json){
	$("#ddPointBox cite").after('<span>使用福分支付，您的福分<i>'+json.point+'</i></span>');
	$("#spBalance").after('<span id="userBalance" b="'+json.money+'">使用账户余额支付，您的账户余额 ￥'+json.money+'.00</span>');
	if (json.money > 0) {
		$("#ddBalanceBox").removeClass('f-pay-grayBg');
		$("#checkBalance").html('<b class="z-comms"></b>');
		$("#spBalance").show();
		$("#hidPayType").val("balance");
		$("#hidUseBalance").val(json.money);
	}
}

function showbank(){}

function chooseBank(){

}

function chooseThird(){

}