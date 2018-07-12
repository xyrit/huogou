$(function(){
	// $.changeByNum('divNumber',500,200,'mine-prob');
	var token = {"token":$("input[name=t]").val()};
    $.getContent(apiBaseUrl+'/cart/list',token,'cartlist');
	var data = {"flag":0,'perpage':4};
	$.getContent(apiBaseUrl+'/product/list',data,'productlist');
	$("#btnGoPay").click(function(){
		var cid = pid = 0;
		var pids = status = "";
		$.each($("#dlCartList a[type=check]"),function(){
			cid = $(this).attr("codeid");
			if ($(this).html().length > 0) {
				status += '1,';
			}else{
				status += '0,';
			}
			pid = $("#dlCartList dd[codeid="+cid+"]").find(".f-cart-plusLess input").attr("pid");
			pids += pid+","; 
		})	
		pids = pids.substring(0,pids.length-1);
		status = status.substring(0,status.length-1);
		var cartData = {"product":pids,"status":status,'token':$("input[name=t]").val()};
		$.getContent(apiBaseUrl+'/cart/check',cartData,'check');
	})
})
function success_cartlist(json){
	if (json.length > 0) {
		$("#divCartBox").show();
		var total = 0;
		$.each(json,function(i,v){
			var item = '';
			item += '<dd codeid="'+v.period_id+'">';
			item += '<ul>';
			item += '<li class="f-cart-comm">';
			item += '<cite><a isvalid="1" type="check" href="javascript:;" class="z-check" codeid="'+v.period_id+'" cid="'+v.id+'"><b class="z-comms"></b></a></cite>';
			item += '<cite class="u-cart-pic">';
			item += '<a href="/product/'+v.product_id+'.html" target="_blank" title="'+v.name+'">';
			item += '<img alt="" src="'+createGoodsImgUrl(v.picture, photoSize[1], photoSize[1])+'">';
			item += '</a></cite>';
			item += '<cite class="u-cart-name">';
			if (v.period_id != v.old_period_id && v.period_id > v.old_period_id) {
				item += '<dfn class="orange"><s class="transparent-png"></s>已为您更新至第'+v.period_number+'云</dfn>';
			};
			item += '<span>';
			item += '<a href="/product/'+v.product_id+'.html" target="_blank" title="'+v.name+'">(第'+v.period_number+'云) '+v.name+'</a>';
			item += '</span>';
			item += v.price+'</site>';
			item += '</li>';
			item += '<li class="f-cart-price" i="'+i+'">'+parseInt(v.price-v.sales_num)+'</li>';
			item += '<li class="f-cart-plusLess">';
			item += '<span id="divNumber'+i+'">';
			item += '<a href="javascript:;" class="z-arrows z-less2 mius" i="'+i+'"></a>';
			item += '<input name="num" type="text" maxlength="7" i="'+i+'" value="'+v.nums+'" pid="'+v.product_id+'" onpaste="return false" surplus="'+parseInt(v.price-v.sales_num)+'" limitbuy="'+v.limit_num+'" mylimitsales="0">';
			item += '<a href="javascript:;" class="z-arrows z-plus add" i="'+i+'"></a>';
			item += '<p style="display: none;">不能大于'+parseInt(v.price-v.sales_num)+'人次</p>';
			item += '</span>';
			item += '</li>';
			item += '<li class="f-cart-subtotal orange" i="'+i+'">￥'+v.nums+'.00</li>';
			item += '<li class="f-cart-operate fr"><a href="javascript:;" type="delete" class="z-comms" title="删除"></a></li>';
			item += '</ul>';
			item += '</dd>';
			$("#dlCartList").append(item);
			total = parseInt(total)+parseInt(v.nums);
			$.changeByNum('divNumber'+i,v.price,parseInt(v.price-v.sales_num),v.limit_num);
		});
		$("#iTotalMoney").text('￥'+total+'.00').attr("t",total);
		
		$(".f-cart-plusLess a").click(function(){
			var num = $(this).parent().find("input").val();
			var pid = $(this).parent().find("input").attr("pid");
			$.getContent(apiBaseUrl+'/cart/changenum',{"num":num,"pid":pid},'change');
			$("dd .f-cart-subtotal:eq("+$(this).attr('i')+")").text('￥'+num+'.00');
			var t = 0;
			$.each($("#dlCartList dd"),function(){
				if ($(this).find(".f-cart-comm a[type=check]").attr("isvalid") == '1') {
					t += parseInt($(this).find(".f-cart-plusLess input").val());
					$("#iTotalMoney").text('￥'+t+'.00');
				};
			})
		})
		$(".f-cart-plusLess input").change(function(){
			var num = $(this).val();
			var pid = $(this).attr("pid");
			$.getContent(apiBaseUrl+'/cart/changenum',{"num":num,"pid":pid},'change');
			$("dd .f-cart-subtotal:eq("+$(this).attr("i")+")").text('￥'+num+'.00');
			var t = 0;
			$.each($("#dlCartList dd"),function(){
				if ($(this).find(".f-cart-comm a[type=check]").attr("isvalid") == '1') {
					t += parseInt($(this).find(".f-cart-plusLess input").val());
					$("#iTotalMoney").text('￥'+t+'.00');
				};
			})
		})
		check();
		checkAll();
		$("#btnDelete").click(function(){
			var s = '';
			$.each($("#dlCartList dd .f-cart-comm a[type=check]"),function(){
				if ($(this).attr('isvalid') == 1) {
					s += $(this).attr("cid")+",";
				};
			})
			var data = {"cid":s.substring(0,s.length-1)};
			del(data);
		})
	}else{
		var html = '';
		html +='<div id="divEmpty" class="z-cart-nothing"><b></b>';
		html +='<span>您的购物车为空！<a href="/" title="立即去伙购>>">立即去伙购<em class="f-tran">&gt;&gt;</em></a></span>';
		html +='</div>';
		$("#divCartBox").hide();
		$(".m-cart-title").after(html);
	}
}

function success_productlist(json){
	$.each(json.list,function(i,v){
		var html = '';
		html += '<div class="f-recomm-list" codeid="1473037">';
		html += '<dl>';
		html += '<dt><a href="/product/'+v.id+'.html" title="'+v.name+'"><img src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'"></a></dt>';
		html += '<dd class="u-recomm-name"><a href="/product/'+v.id+'.html" title="'+v.name+'">'+v.name+'</a></dd>';
		html += '<dd class="gray9">剩余<em>'+v.left_num+'</em>人次</dd>';
		html += '</dl>';
		html += '</div>';
		$("#divRecList").append(html);
	})
}

function success_check(json){
	if (json.code == 100) {
		window.location.href = '/payment.html';
	};
}

function check(){
	$("#dlCartList a[type=check]").click(function(){
		if ($(this).html().length > 0) {
			$(this).html('');
			$(this).attr('isvalid','0');
		}else{
			$(this).attr('isvalid','1');
			$(this).html('<b class="z-comms"></b>');
		}
		isAll();
	})
}

function checkAll(){
	$("#sSelAll").click(function(){
		if ($(this).html().length > 0) {
			$("#dlCartList a[type=check]").html('');
			$(this).html('');
			$("#dlCartList a[type=check]").attr('isvalid','0');
		}else{
			$(this).html('<b class="z-comms"></b>');
			$("#dlCartList a[type=check]").attr('isvalid','1');
			$("#dlCartList a[type=check]").html('').html('<b class="z-comms"></b>');
		}
		isAll();
	})
}

function isAll(){
	var all = true;
	var t = 0;
	$.each($("#dlCartList a[type=check]"),function(){
		if ($(this).html().length == 0) {
			all = false;
		}else{
			var cid = $(this).attr("codeid");
			var m = $("#dlCartList dd[codeid="+cid+"]").find(".f-cart-plusLess input").val();
			t += parseInt(m);
		}
	})
	if (!all) {
		$("#sSelAll").html('');
	}else{
		$("#sSelAll").html('<b class="z-comms"></b>');
	}
	$("#iTotalMoney").text("￥"+t+".00").attr("t",t);
}

function del(data){
	$.getContent(apiBaseUrl+'/cart/del',data,'delResult');
}

function success_delResult(json){
	// $.each(json,function(i,v){
	// 	var codeid = $("#dlCartList dd .f-cart-comm a[cid="+v+"]").attr("codeid");
	// 	$("#dlCartList dd[codeid="+codeid+"]").remove();
	// })
	window.location.href="cart.html";
}