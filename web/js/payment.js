var balance = totalMoney = needMoney = thirdMoney = ppwd = free = checkPwd = 0;

$(function(){
	
	$.getContent(apiBaseUrl+'/cart/list',{"is_buy":1,'check':1,'token':token},'orderCheck');

	$.getContent(apiBaseUrl + '/usercoupons/list',{'type':'use'},'coupons');
	
	$("#checkBalance").click(function(){
		var choosed = $(this).html();
		if (choosed.length > 0) {
			$(this).find("b").remove();
			$("#spBalance").hide();
			$("#divBankBox").show();
			$("#iBankPay").text(""+$("#divPayWayBox i").attr("t")+"伙购币");
			$("#hidUseBalance").val(0);
		}else{
			$(this).html('<b class="z-comms"></b>');
			$("#spBalance").show();
			var b = parseInt($("#userBalance").attr("b"));
			var t = parseInt($("#divPayWayBox i").attr("t"));

			if (b >= t) {
				$("#divBankBox").hide();
				$("#iBankPay").text(""+parseInt(b-t)+"伙购币");
			};

			$("#hidUseBalance").val(b);
		}
	})
	$("#submit input[type=button]").click(function(){
		if ( $("#divPaypwd").css("display") == 'block' && checkPwd != 1) {
			return false;
		};
		if ($("#hidPayName").val() == 'balance') {
			$("#toPayForm").attr("target","_self");
			if ($("#hidIntegral").val()>0) {
				$('.balance_txt').html('您确定使用福分支付吗？')
			}
			if ($("#hidUseBalance").val()>0) {
				$('.balance_txt').html('您确定使用账户余额支付吗？')
			}
			if($("#hidIntegral").val()>0 && $("#hidUseBalance").val()>0){
				$('.balance_txt').html('您确定使用福分加账户余额支付吗？')
			}
			$(".balance_con").show();
		}else{
			$("#toPayForm").attr("target","_blank");
			$(".succeed_con").show();
			$("#toPayForm").submit();
		}
	})

	$(".balance_con a").click(function(){
		if ($(this).attr('id') == 'pay-cancle') {
			$(".balance_con").hide();
		}else if ($(this).attr('id') == 'pay-sure') {
			$("#toPayForm").submit();
		};
	})
	$(".balance_con .close").click(function(){
		$(".balance_con").hide();
	})
	//银行
	$('.banking_title_list').find('li').on('click', function(){
        $(this).addClass('act').siblings().removeClass('act');
        $('.banking_con_list').find('li').removeClass('act').find('input').removeAttr('selected');
        if ($('.terrace_con_list li[class=act]').length == 0 ) {
	        $('.banking_con_list').stop().hide().eq($(this).index()).fadeIn().find('li:first').addClass('act').find('input').attr('selected','selected');
	        $("#hidPayName").val($('.banking_con_list li[class=act] input').attr('name'));
	        $("#hidPayBank").val($('.banking_con_list li[class=act] input').val());
        };
    }).eq(0).trigger('click');

	$('.banking_con_list').each(function(){
		$(this).find('li').each(function(index){
			if (index == 0) {
				$(this).addClass('act');
			};
			if ((index + 1) % 5 == 0){
				$(this).css('marginRight','0');
			}
		})
	})

	$('.banking_con_list li').click(function(){
		$(".terrace_con_list li").removeClass('act').find('input').removeAttr('selected');
		$('.banking_con_list li').removeClass('act').find('input').removeAttr('selected');
		$(this).addClass('act').find('input').attr('selected', 'selected');
		$('#hidPayName').val($(this).find('input').attr('name'));
		$("#hidPayBank").val($(this).find('input').val());
	})

	$('.terrace_con_list').find('li').each(function(index){
		// if (index == 0) {
		// 	$(this).addClass('act');
		// };
		if ((index + 1) % 5 == 0){
			$(this).css('marginRight','0');
		}
	})

	$('.terrace_con_list li').click(function(){
		$('.banking_con_list li').removeClass('act').find('input').removeAttr('selected');
		$('.terrace_con_list li').removeClass('act').find('input').removeAttr('selected');
		$(this).addClass('act').find('input').attr('selected', 'selected');
		$('#hidPayName').val($(this).find('input').attr('name'));
		$("#hidPayBank").val($(this).find('input').val());
	})

	$('.succeed_con .close').click(function(){
		$(".succeed_con").hide();
		window.location.reload();
	})
	$('.succeed_con .pay').click(function(){
		$(".succeed_con").hide();
	})

	$('#txtPaypwd').focus();
	setInterval(function(){
		if($("#txtPaypwd").val() == ""){
			$("#txtPaypwd").siblings('.input_arr').show();
		}else{
			$("#txtPaypwd").siblings('.input_arr').hide();
		}
	},200)


	$("#txtPaypwd").keyup(function(){
		$('#txtPaypwd').siblings('s').hide().removeAttr('class');
		$('#submitOK').parents('article').addClass('f-grayButton');
		//var e = event || window.event || arguments.callee.caller.arguments[0];
		// if (e && e.keyCode == 8 && e.keyCode == 127 && e.keyCode == 37 && e.keyCode == 39 && (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
			var pwd = $("#txtPaypwd").val();
			if (pwd.length == 6) {
				$('#txtPaypwd').siblings('s').show();
				$.getJsonp(apiBaseUrl+'/pay/check-ppwd',{'pwd':pwd},function(json){
					if(json.code == "1"){
						$('#txtPaypwd').siblings('s').addClass('tips_txt_Correct');
						$('#submitOK').parents('article').removeClass('f-grayButton').css('cursor','pointer');
					}else if(json.code == "0"){
						$('#txtPaypwd').siblings('s').addClass('tips_txt_Wrong');
						$('#submitOK').parents('article').addClass('f-grayButton');
					}
					checkPwd = json.code;
				})
			};
		// }
	})

})
function success_orderCheck(json){
	if (json.list.length <= 0 || !json.logined) {
		window.location.href = '/cart.html';
		return false;
	};
	$.each(json.list,function(i,v){
		if (parseInt(v.price-v.sales_num) > 0 && v.nums > 0) {
			var item = '';
			item += '<tr>';
			item += '<td class="pro order">';
			item += '<picture>';
			item += '<img src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'" alt="">';
			if (v.limit_num > 0) {
				item += '<i class="limitbuy-icon">限购</i>';
			};
			item += '</picture>';
			item += '<article>';
			item += '<a href="/product/'+v.product_id+'.html">'+v.name+'</a>';
			item += '</article>';
			item += '</td>';
			item += '<td>';
			item += parseInt(v.price-v.sales_num);
			item += '</td>';
			item += '<td>';
			item += v.nums+'人次';
			item += '</td>';
			item += '<td class="orange">';
			item += ''+v.nums+'伙购币';
			item += '</td>';
			item += '</tr>';
			$(".shopping_list tbody").append(item);
			totalMoney = parseInt(totalMoney)+parseInt(v.nums);
		};
	});
	$(".shopping_elect .return").after('<div class="rl">金额总计：<i t="'+totalMoney+'">'+totalMoney+'伙购币</i></div>');

	$.getContent(apiBaseUrl+'/user/getmoney',{'token':token},'getusermoney');

	// balance = parseInt($("#hidUseBalance").val());
	// // while(balance == 0){}
	// if (balance >= totalMoney) {
	// 	$("#balance em").text("-"+totalMoney+"伙购币").show();
	// 	$("#third").hide();
	// 	$(".payment em").text(""+totalMoney+"伙购币");
	// 	$("#hidPayName").val('balance');
	// 	$("#hidPayBank").val('');
	// }else{
	// 	$("#balance em").text("-"+balance+"伙购币").show();
	// 	$(".payment em").text(""+parseInt(totalMoney-balance)+"伙购币");
	// 	$("#hidPayMoney").val(parseInt(totalMoney-balance));
	// 	$("#divBankBox").show();
	// 	// $('#hidPayName').val($('.banking_con_list:visible').find('li[class=act]').find('input').attr('name'));
	// 	// $('#hidPayBank').val($('.banking_con_list:visible').find('li[class=act]').find('input').val());
	// 	$('#hidPayName').val($('.terrace_con_list li[class=act]').find('input').attr('name'));
	// 	$("#hidPayBank").val($('.terrace_con_list li[class=act]').find('input').val());
	// 	$("#third").show();
	// }



	var aTr = $('.shopping_list').find('tbody tr');
	function trHide(){
		aTr.each(function(index){
			if (index > 3) {
				$(this).hide();
			}
		}).eq(3).css({'opacity': 0.4, 'filter' : 'alpha(opacity=40)'});
		if (arguments.length > 0) arguments[0].html('展开全部' + aTr.size() + '件商品');
	}

	function trShow(){
		aTr.stop().fadeIn().eq(3).css({'opacity': 1, 'filter' : 'alpha(opacity=100)'});
		if (arguments.length > 0) arguments[0].html('收起');
	}
	if (aTr.size() > 4) {
		trHide($('.open').find('span'));
		$('.open').find('span').on('click',function(){
			$(this).hasClass('act') ? trHide($(this)) : trShow($(this));
			$(this).toggleClass('act');
		})
	}else{
		$('.open').hide();
	}

}

function success_getusermoney(json){
	free = json.free;
	ppwd = json.ppwd;

	showPoint(json.point,json.money,totalMoney);

	showBalance(json.money,needMoney);

	$("#submit").show();
}

function showPoint(myPoint,myMoney,totalMoney){
	var pointHtml = "";
	pointHtml += '<article >';
	pointHtml += '<input type="checkbox">';
	pointHtml += '<span>使用福分支付，您的福分'+myPoint+'</span>';
	if (myPoint >= 100) {
		if (myPoint > totalMoney*100) {
			pointMoney = totalMoney*100;
			pointHtml += '<input type="text" value="'+pointMoney+'">';
			needMoney = 0;
		}else{
			pointMoney = parseInt(myPoint/100)*100;
			pointHtml += '<input type="text" value="'+pointMoney+'">';
			needMoney = parseInt(totalMoney-pointMoney/100);
		}
		$("#hidIntegral").val(pointMoney);
		pointHtml += '<b><i></i>必须为100的整数倍</b>';
		pointHtml += '<em>-'+(pointMoney/100)+'伙购币</em>';
		pointHtml += '</article>';
		$("#point").html(pointHtml);
		$("#hidPayName").val('balance');
		$("#point input[type=checkbox]").prop("checked",true);
	}else{
		pointHtml += '</article>';
		$("#point").html(pointHtml);
		$("#point").css('background', '#f8f8f8').attr("disabled","disabled").find('input[type=checkbox]').prop('disabled', 'true');
		needMoney = totalMoney;
	}
	$("#point input[type=text]").on('input',function(){
		var num = parseInt($(this).val());
		if (num >= 100) {
			var minus = num > myPoint ? myPoint : num;
				minus = minus/100 > totalMoney ? totalMoney*100 : minus;
			$("#point em").text("- "+parseInt(minus/100)+"伙购币").fadeIn();
			$(this).val(minus);
			$("#point input[type=checkbox]").prop("checked",true);
			$("#hidIntegral").val(parseInt(minus/100)*100);
			showBalance(myMoney,parseInt(totalMoney-parseInt(minus/100)));
		}else{
			$("#point em").fadeOut();
			$("#point input[type=checkbox]").prop("checked",false);
			$("#hidIntegral").val('0');
			showBalance(myMoney,totalMoney);
		}
	})
	$("#point input[type=text]").change(function(){
		var p = parseInt($(this).val()/100) > totalMoney ? totalMoney*100 : parseInt($(this).val()/100)*100;
		$(this).val(p);
	})
	$("#point input[type=checkbox]").click(function(){
		if ($(this).prop('checked')) {
			$("#point em").show();
			showPoint(myPoint,myMoney,totalMoney);
			showBalance(myMoney,needMoney);
			$("#hidIntegral").val($("#point input[type=text]").val());
		}else{
			$("#point em").hide();
			showBalance(myMoney,totalMoney);
			$("#hidIntegral").val('0');
		}
	})
	// return needMoney;
}

function showBalance(myMoney,needMoney){
	var moneyHtml = "";
	moneyHtml += '<article>';
	moneyHtml += '<input class="balance_y" type="checkbox">';
	moneyHtml += '<span>使用账户余额支付，您的账户余额'+myMoney+'</span>';
	if (myMoney > 0) {
		if (myMoney > needMoney) {
			moneyHtml += '<em>-'+needMoney+'伙购币</em>';
			$("#hidUseBalance").val(needMoney);
			thirdMoney = 0;
		}else{
			moneyHtml += '<em>-'+myMoney+'伙购币</em>';
			$("#hidUseBalance").val(myMoney);
			thirdMoney = parseInt(needMoney-myMoney);
		}
		moneyHtml += '</article>';
		$("#balance").html(moneyHtml);
		$("#hidPayName").val('balance');
		if (needMoney == 0) {
			$("#balance em").hide();
			$("#balance input[type=checkbox]").prop("checked",false);
		}else{
			$("#balance input[type=checkbox]").prop("checked",true);
		}
	}else{
		moneyHtml += '</article>';
		$("#balance").html(moneyHtml);
		$("#balance").css('background',"#f8f8f8").attr("disabled","disabled").find('input[type=checkbox]').prop('disabled', 'true');
		thirdMoney = needMoney;
	}

	$("#submit aside").text("成功支付即可获得"+needMoney+"福分");

	showBank(thirdMoney);

	$('#balance .balance_y').on('click',function(){
		if ($(this).prop('checked')) {
			$("#balance em").show();
			thirdMoney = showBalance(myMoney,needMoney);
			$("#hidUseBalance").val(needMoney);
		}else{
			$("#balance em").hide();
			thirdMoney = needMoney;
			$("#hidUseBalance").val('0');
		}
		if (!isNaN(thirdMoney)) {
			showBank(thirdMoney);
		};
	})
	// return thirdMoney;
}

function showBank(thirdMoney){
	thirdMoney = parseInt(thirdMoney);
	if (thirdMoney > 0) {
		$("#third").show();
		$(".payment em").text(thirdMoney+'伙购币');
		$("#third li").each(function(){
			if ($(this).attr('class') == 'act') {
			// 	$('#hidPayName').val($('.banking_con_list:eq(0) li[class=act]').find('input').attr('name'));
			// 	$("#hidPayBank").val($('.banking_con_list:eq(0) li[class=act]').find('input').val());		
				$("#hidPayName").val($(this).find('input').attr('name'));
				$("#hidPayBank").val($(this).find('input').val());
			};	
		})
		$("#hidPayMoney").val(thirdMoney);
	}else{
		$("#third").hide();
		$("#hidPayMoney").val('0');
		$("#hidPayBank").val('');
	}
	if (ppwd == 1) {
		if ((totalMoney-thirdMoney) > free) {
			$("#divPaypwd").show();
		}else{
			$("#divPaypwd").hide();
			$("#submit article").removeClass('f-grayButton').css('cursor','pointer');
		}
	}else{
		$("#submit article").removeClass('f-grayButton').css('cursor','pointer');
	}
}

$('.input_arr').click(function(){
	$('#txtPaypwd').focus();
})
$('#txtPaypwd').keyup(function(){
	if($(this).val() == ""){
		$(this).siblings('.input_arr').show();
	}else{
		$(this).siblings('.input_arr').hide();
	}
})

function success_coupons(json){
	if (json.total > 0) {
		var packetsHtml = "";
			packetsHtml += '<article>';
			packetsHtml += '<input id="hb-deduct-input" type="checkbox">';
			packetsHtml += '<span>使用红包抵扣，您可使用的红包数量为<em>' + json.total + '</em>个</span>';
			packetsHtml += '<span>已选择<em id="choosed_nums">0</em>个红包</span>';
			packetsHtml += '<a id="hb-reset" href="javascript:;">重新选择</a>';
			packetsHtml += '</article>';
			packetsHtml += '<div id="hb-deduct">';
			packetsHtml += '</div>';
			$('#packets').html(packetsHtml);
		var couponListHtml = '';
			couponListHtml += '<div class="balance_box">';
			couponListHtml += '<i class="close"></i>';
			couponListHtml += '<h2>选择使用红包</h2>';
			couponListHtml += '<ul id="hb-fixed-list">';
			$.each(json.list,function(i,v){
				couponListHtml += '<li couponid = '+v.user_code_id+' code = '+v.coupon_code+' type='+v.type+'>';
				couponListHtml += '<em class="hb-box-check"></em>';
				couponListHtml += '<div class="hb-icon">';
				couponListHtml += '<div class="fold">';
				couponListHtml += '<p>'+v.amount+'</p>';
				if (v.type == 1) {
					couponListHtml += '<span>伙购币</span>';
					couponListHtml += '</div>';
					couponListHtml += '</div>';
					couponListHtml += '<article>';
					couponListHtml += '<h3>余额：'+v.amount+'伙购币</h3>';
					couponListHtml += '<p>有效期至：'+v.date+'</p>';
				}else if (v.type == 2) {
					couponListHtml += '<span>折</span>';
					couponListHtml += '</div>';
					couponListHtml += '</div>';
					couponListHtml += '<article>';
					couponListHtml += '<h3>支付立享：'+v.amount+'折</h3>';
					couponListHtml += '<p>有效期至：'+v.date+'</p>';
				}
				couponListHtml += '<p>'+v.name+'</p>';
				couponListHtml += '<p>'+v.range+'</p>';
				couponListHtml += '</article>';
				couponListHtml += '</li>';
			})
			couponListHtml += '</ul>';
			couponListHtml += '<a href="javascript:;" id="hb-cancle">取 消</a><a class="determine" href="javascript:;" id="hb-sure">确定</a>';
			couponListHtml += '</div>';
			$(".hb-box").html(couponListHtml);
	}

	//点击弹出红包弹窗
	$('#hb-deduct-input').on('click',function(event){
		var event = event || window.event;
		event.preventDefault();
		$('.hb-box').fadeIn();
	})

	$("#hb-reset").on('click',function(){
		$('.hb-box').fadeIn();	
	})

	$('.close, #hb-cancle, #hb-sure').on('click',function(){
		$('.hb-box').fadeOut();
	})

	$('#hb-sure').on('click',function(){
		$("#hb-fixed-list li").each(function(){
			var choosedHtml = '';
			var money = 0;
			var couponid = code = '';
			if ($(this).hasClass('act')) {
				couponid = $(this).attr('couponid');
				code = $(this).attr('code');
				
				$("#hb-deduct p").each(function(){
					if ($(this).attr('couponid') == couponid && $(this).attr('code') == code) {
						return false;
					}
				})
				choosedHtml += '<p couponid='+couponid+' code='+code+'>使用红包抵扣,';
				money = $(this).find(".fold p").text();
				if ($(this).attr('type') == '1') {
					choosedHtml += '金额<span>' + money + '伙购币</span>';
					$("#balance em").text(parseInt(needMoney-money)+'伙购币');
				}else if ($(this).attr('type') == '2') {
					choosedHtml += '折扣<span>' + money + '折</span>';
				}
				choosedHtml += '</p>';
				$("#coupons").val(couponid+'_'+code);
				$("#hb-deduct").append(choosedHtml);
			}

		})
		$('#hb-deduct-input').prop('checked',true);
	})
	$('#hb-cancle').on('click',function(){
		$('#hb-deduct-input').prop('checked',false);
	})

	$('#hb-fixed-list').find('li').on('click',function(){
		$(this).addClass('act').siblings().removeClass();
	})
}