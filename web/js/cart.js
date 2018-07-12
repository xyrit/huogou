var cartData;
$(function(){

	$.getContent(apiBaseUrl+'/cart/list',{"token":token,"check":1},'cartList');	

	var data = {"flag":0,'perpage':4,'limit':0};
	$.getContent(apiBaseUrl+'/product/list',data,'productlist');
	$(".finish_a").click(function(){
		if (token.length == 0) {
			// return false;
		};
		var pids = isbuy = "";
		$.each($("tbody tr").not('.riskTip'),function(){
			pids += $(this).find('input[name=count]').attr("pid")+',';
			if ($(this).find('input[type=checkbox]').is(":checked") == true) {
				isbuy += '1,';
			}else{
				isbuy += '0,';
			}
		})
		pids = pids.substring(0,pids.length-1);
		isbuy = isbuy.substring(0,isbuy.length-1);
		cartData = {"product":pids,"status":isbuy,'token':token};

		$.getContent(apiBaseUrl+'/cart/check',cartData,'cartCheck');			
		// if (checkLogin()) {
			
		// }else{
		// 	showLoginForm();			
		// }
	})
})
function success_cartList(json){
	if (json.list.length > 0) {
		$(".shopCart_total").html(json.list.length);
		$("#divCartBox").show();
		var total = 0;
		$.each(json.list,function(i,v){
			if (v.left_num>0) {
				var item = '';
				item += '<tr codeid="'+v.period_id+'">';
				item += '<td width="40">';
				if (v.limit_num > 0 && v.my_limit_num==0) {
					item += '<input type="checkbox" value="'+v.id+'"/>';
				} else {
					item += '<input type="checkbox" checked="checked"  value="'+v.id+'"/>';
				}
				item += '</td>';
				item += '<td class="pro">';
				item += '<picture>';
				item += '<img src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'" alt="">';
				if (v.limit_num > 0) {
					item += '<i class="limitbuy-icon">限购</i>';
				} else if (v.buy_unit==10) {
					item += '<i class="limitbuy-icon">十元</i>';
				}
				item += '</picture>';
				item += '<article>';
				item += '<a href="/product/'+v.product_id+'.html">'+v.name+'</a>';
				item += '<aside>总需：'+v.price+'人次</aside>';
				item += '</article>';
				if (v.period_id > v.old_period_id && json.logined) {
					item += '<dfn class="o_hint">';
					item += '<i class="hint_icon"></i>已为您更新至第'+v.period_number+'期';
					item += '</dfn>';
				};
				item += '</td>';
				item += '<td>';
				item += v.left_num;
				item += '</td>';
				item += '<td>';
				if (v.limit_num > 0 && v.my_limit_num==0) {
					item += '<span>已满限购次数</span>';
				} else {
					item += '<div class="count" id="buynum'+i+'">';
					item += '<a href="javascript:;" class="pro_less mius" i="'+i+'">-</a><input type="text" name="count" maxlength="" value="'+v.nums+'" class="num" i="'+i+'" pid="'+v.product_id+'"><a href="javascript:;" class="pro_add add" i="'+i+'">+</a>';
					item += '</div>';
				}
				if (v.limit_num > 0) {
					item += '<aside>限购'+v.limit_num+'人次</aside>';
				} else if (v.buy_unit==10) {
					item += '<aside>十元专区</aside>';
				}
				item += '</td>';
				item += '<td class="orange">';
				if (v.limit_num > 0 && v.my_limit_num==0) {
				} else {
					item += v.nums+' 伙购币';
				}
				item += '</td>';
				item += '<td>';
				item += '<span class="delete" cid="'+v.id+'">删除</span>';
				item += '</td>';
				item += '</tr>';
				$(".shopping_list tbody").append(item);
				total = parseInt(total)+parseInt(v.nums);
				$.changeByNum('buynum'+i,v.price,parseInt(v.price-v.sales_num),v.limit_num,false, v.buy_unit);
			};	
		});
		$("#total").text(total+' 伙购币').attr("t",total);

		function totalAll(e){
			var tr = $(e).parents("tr");
			var num = tr.find("input[type=text]").val();
			var pid = tr.find("input[type=text]").attr("pid");
			$.getContent(apiBaseUrl+'/cart/changenum',{"num":num,"pid":pid,"token":token},'change');
			if(!num){
				num=0;
			}
			tr.find(".orange").text(num+' 伙购币');

			var t = 0;
			$.each($(".shopping_list tbody tr"),function(){
				if ($(this).find("input[type=checkbox]").is(":checked")) {
					var totalNum = $(this).find("input[type=text]").val();
					if(!totalNum){
						totalNum=0;
					}
					t += parseInt(totalNum);
					$("#total").text(t+' 伙购币').attr('t',t);
				};
			})
		}

		function riskTip(e){
			var tr = $(e).parents("tr");
			var $this = tr.find("input[type=text]");
			var html = "";
			html += '<tr class="riskTip">';
			html += '<td colspan="6" style="border: 0; padding: 0;">';
			html += '<div class="list_tips clrfix">';
			html += '<span>温馨提示：已超过100人次，伙购就是花1元就有可能买到1件商品，是一种分享式购物平台，可能带来超值回报的同时也存在一定风险，请谨慎参与哦！</span>';
			html += '</div>';
			html += '</td>';
			html += '</tr>';
			if($this.val() >= 100 && !$this.parents('tr').next('tr').hasClass('riskTip')){
				$this.parents('tr').after(html);
			}
			if($this.val() < 100){
				$this.parents('tr').next('.riskTip').remove();
			}
		}

		$(".count a").click(function(){
			totalAll(this);
			riskTip(this);
		})

		$("input[name=count]").keyup(function(){
			totalAll(this);
			riskTip(this);
		})

		$(function(){
			var html = "";
			html += '<tr class="riskTip">';
			html += '<td colspan="6" style="border: 0; padding: 0;">';
			html += '<div class="list_tips clrfix">';
			html += '<span>温馨提示：已超过100人次，伙购就是花1元就有可能买到1件商品，是一种分享式购物平台，可能带来超值回报的同时也存在一定风险，请谨慎参与哦！</span>';
			html += '</div>';
			html += '</td>';
			html += '</tr>';
			$("input[name=count]").each(function(){
				if($(this).val() >= 100){
					$(this).parents('tr').after(html);
				}
			})
		})

		$("input[name=count]").blur(function(){
			totalAll(this);
		})


		check();
		checkAll();


		$("#delete-all").click(function(){
			var html = "";
			html += '<section id="buy_popup">';
			html += '<div class="buy_box">';
			html += '<i class="close" onclick="cancel()"></i>';
			html += '<div class="buy_con" style="text-align: center; padding: 20px;">';
			html += '<div>';
			html += '<p class="adel_ts">确定要删除所选的商品？</p>';
			html += '<a href="javascript:;" title="取消" onclick="cancel()" class="z-btn-cancel">取消</a>';
			html += '<a href="javascript:;" title="确定" onclick="determine()" class="z-btn-determine">确定</a>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			html += '</section>';
			$(this).parents('.shopping').append(html);
		})

		//$("tbody .delete").click(function(){
		//	var cid = $(this).attr("cid");
		//	del({"cid":cid,"token":token});
		//})
		$('.delete').on('click', function(e){
			var html = '';
			html += '<div class="cart_delbox">';
			html += '<p>确定要删除吗？</p>';
			html += '<a href="javascript:;" title="确定" class="determine_btn">确定</a>';
			html += '<a href="javascript:;" title="取消" class="cancel_btn">取消</a>';
			html += '<b><s></s></b>';
			html += '</div>';
			$('.shopping_list tbody td').children('.cart_delbox').remove();
			$(this).parent('td').append(html);

		})

		$('.shopping_list tbody td').on('click','.determine_btn',function(){
			var cid = $(this).parents('.cart_delbox').siblings('.delete').attr("cid");
			del({"cid":cid,"token":token});
		})
		$('.shopping_list tbody td').on('click','.cancel_btn',function(){
			$(this).parents('.cart_delbox').remove();
		})

	}else{
		var html = '';
		html +='<div id="divEmpty" class="No_shopping"><b></b>';
		html +='<p>您的购物车为空！<a href="/list.html" title="立即去伙购>>">立即去伙购</a></p>';
		html +='</div>';
		$(".shopping h1").hide();
		$(".shopping .shopping_list").hide();
		$(".shopping .shopping_elect").hide();
		$(".shopping .finish").hide();
		$(".shopping").prepend(html);
	}
}

function success_productlist(json){
	$.each(json.list,function(i,v){
		var html = '';
		html += '<li>';
		html += '<a href="javascript:;" periodid="'+v.period_id+'" buyUnit="'+ v.buy_unit+'"></a>';
		html += '<picture>';
		html += '<img src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'" alt="">';
		html += '</picture>';
		html += '<article>';
		html += '<p>'+v.name+'</p>';
		html += '<aside>剩余'+v.left_num+'人次</aside>';
		html += '</article>';
		html += '</li>';
		$(".popularity_list").append(html);
	})

	$(".popularity_list li a").click(function() {
		var periodid = $(this).attr("periodid");
		var buyUnit = $(this).attr("buyUnit");
		var cartdata = {'periodid':$(this).attr("periodid"),'num':1*buyUnit};
		var $this = $(this);
		$.getJsonp(apiBaseUrl+'/cart/add',cartdata,function(data){
			if(data.code ==100) {
				var html = '';
				html += '<div class="cg-fixed"><p>添加成功！</p></div>';
				$this.parents('li').append(html);
				setTimeout(function(){
					success_goCart(data);
				},500);
			} else if(data.code =101){
				var html = '';
				html += '<div class="sb-fixed"><p>商品已被抢光了！</p></div>';
				$this.parents('li').append(html);
				setTimeout(function(){
					$('.sb-fixed').fadeOut();
				},500);
			}
		});
	});
}


function p_cancel(){
	$.getJsonp(apiBaseUrl+'/cart/invalid','',function(json){
		if (json.code == 100) {
			window.location.href = '/payment.html';
		};
	})
}
function p_deter(){
	window.location.href = '/payment.html';
}

function success_cartCheck(json){
	if (json.code == 100) {
		if (json.invalid == 1) {
			var notice = "";
			var html = "";
			if (json.num == 1) {
				notice = '当前商品已售完，是否伙购下一期';
			}else if (json.num > 1) {
				notice = '部分商品已售完，是否更新至下一期';
			};
			html += '<section id="buy_popup">';
			html += '<div class="buy_box">';
			html += '<i class="close" onclick="p_cancel()"></i>';
			html += '<div class="buy_con" style="text-align: center; padding: 20px;">';
			html += '<div>';
			html += '<p class="adel_ts">'+notice+'</p>';
			html += '<a href="javascript:;" title="取消" onclick="p_cancel()" class="z-btn-cancel">取消</a>';
			html += '<a href="javascript:;" title="确定" onclick="p_deter()" class="z-btn-determine">确定</a>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			html += '</section>';
			$('body').append(html);
		}else{
			window.location.href = '/payment.html';
		}
	}else{
		if (json.logined == 0) {
			showLoginForm();
		};
	}
}

function check(){
	$("tbody input[type=checkbox]").click(function(){
		var all = true;
		$.each($("tbody input[type=checkbox]"),function(i,v){
			if ($(this).is(':checked') == false) {
				all = false;
			};
		})
		$("#check-all").prop("checked",all);
		checkCountTotal();
	})
}

function checkAll(){
	$("#check-all").click(function(){
		if ($(this).is(':checked') == true) {
			// $("#total").text("￥"+$("#total").attr("t")+".00");
			var t=0;
			$.each($("tbody input[name=count]"),function(i,v){
				t += parseInt($(this).val());
			})
			$("#total").text(t+' 伙购币').attr('t',t);
		}else{
			$("#total").text(0+" 伙购币");
		}
		$("tbody input[type=checkbox]").prop('checked',$(this).is(':checked'));
	})
}

function checkCountTotal(){
	var all = true;
	var t = 0;
	$.each($("tbody input[type=checkbox]"),function(){
		if ($(this).is(':checked')) {
			var m = $(this).parent().siblings().find("input[name=count]").val();
			t += parseInt(m);
		}
	});
	$("#total").text(t+"伙购币").attr("t",t);
}

function checkCount(){
	$.each($("tbody input[name=count]"),function(){
		var num = $(this).val();
		$(".orange:eq("+$(this).attr('i')+")").text(num+' 伙购币');
	})
}

function del(data){
	$.getContent(apiBaseUrl+'/cart/del',data,'delResult');
}

function success_delResult(json){
	window.location.href="cart.html";
}

function success_change(json){
	if (json.limit > 0) {
		$("input[pid="+json.pid+"]").val(json.canBuy);
		checkCountTotal();
	};
	checkCount();
}

function cancel(){
	$('#buy_popup').remove();
}

function determine(){
	var s = '';
	$.each($("tbody input[type=checkbox]:checked"),function(){
		s += $(this).val()+',';
	})
	var data = {"cid":s.substring(0,s.length-1),"token":token};
	del(data);
}

