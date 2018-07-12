$(function(){
	var data = {'id':getProductId()};
	$.getContent(apiBaseUrl+'/product/info',data,'productinfo');
	$.getContent(apiBaseUrl+'/product/periodlist',data,'periodlist');
	data = {"catId":getProductId()};
	$.getContent(apiBaseUrl+'/share/topic-list',data,'periodlist');
	$("#divBuy a").click(function(){
		var cartdata = {'productid':getProductId,'num':$("#divNumber input").val()};
		if ($(this).attr('class') == 'consume-now') {
			$.getContent(apiBaseUrl+'/cart/add',cartdata,'gocart');
		}else if ($(this).attr('class') == 'consume-addcar'){
			$.getContent(apiBaseUrl+'/cart/add',cartdata,'cartadd');
		}
	})
	$("#div_menu ul li").click(function(){
		$("#div_menu ul li").removeClass("current");
		$(this).addClass("current");
		var i = $(this).index();
		if (i == 0) {
			$("#div_desc").show();
			$("#div_allrecord").show().find('.rocord-header').show();
			$("#div_postlist").show().find('.ng-share-header').show();
		}else if (i == 1) {
			$("#div_desc").hide();
			$("#div_allrecord").show().find('.rocord-header').hide();
			$("#div_postlist").hide();
		}else if (i == 2) {
			$("#div_desc").hide();
			$("#div_allrecord").hide();
			$("#div_postlist").show().find('.ng-share-header').hide();
		};
	})
})
function getProductId() {
    var reg = new RegExp("(product\/)([^&]*)(.html)"); 
    var r = window.location.href.match(reg); 
    if (r != null) return unescape(r[2]); return null; 
}
function success_productinfo(json){
	var title = '';
	$(".ng-sort .now a").attr('title','第'+json.periodinfo.period_number+'云进行中');
	$(".ng-sort .now span").before('第'+json.periodinfo.period_number+'云进行中');
	$("title").text(json.name);
	title = '<span class="num">(第'+json.periodinfo.period_number+'云)</span>'+json.name+'<span class="o-info">'+json.brief+'</span>';
	$(".o-title").html(title);
	$('.product-con').html(json.intro);
	$(".ng-goods-detail .price").text("价值：￥"+json.price);
	$("#div_desc .product-con").html(json.intro);
	createScheduleHtml(json.periodinfo,json.limit_num);
	createPhotoList(json.photoList);
	$.getContent(apiBaseUrl+'/period/buylist',{'id':json.periodinfo.id},'buylist');
}

function success_buylist(json){
	$.each(json.list,function(i,v){
		var item1 = item2 = '';
		item1 += '<li>'
		item1 += '<span class="time">'+v.buy_time+'</span>';
		item1 += '<span class="name"><span class="w"><a href="http://u.1yyg.com/1008109771" target="_blank" title="'+v.user_name+'"><i class="head-s-img"><img src="http://faceimg.1yyg.com/UserFace/30/20150803185711489.jpg" width="22" height="22" /></i>'+v.user_name+'</a></span></span>';
		item1 += '<span class="people">'+v.buy_num+'</span>';
		item1 += '<span class="ip">'+v.buy_ip_addr+' '+v.buy_ip+'</span>';
		item1 += '<span class="form"><a href="http://info.1yyg.com/app/microchannel.html" target="_blank">微信公众平台<i class="f-icon wx"></i></a></span>';
		item1 += '</li>';
		$("ul.record-list").append(item1);
		item2 += '<li>';
		item2 += '<a rel="nofollow" href="http://u.1yyg.com/1010612082" title="'+v.user_name+'" target="_blank" class="buy-name">';
		item2 += '<i class="head-s-pic"><img src="http://faceimg.1yyg.com/UserFace/30/20151015154535464.jpg" width="22" height="22"></i>'+v.user_name+'</a>';
		item2 += '<span class="buy-num">'+v.buy_num+'</span>人次';
		item2 += '</li>';
		$("#UserBuyNewList").append(item2);
	})
}

function createScheduleHtml(periodinfo,limit){
	var scheduleHtml = '';
	var schedule = parseInt(periodinfo.sales_num/periodinfo.price*508);
	scheduleHtml += '<div class="line-wrapper u-progress" title="完成'+changeTwoDecimal_f(periodinfo.sales_num/periodinfo.price)+'">';
	scheduleHtml += '<span class="pgbar" style="width:'+schedule+'px"><span class="pging"></span></span></div>';
	scheduleHtml += '<div class="text-wrapper clearfix">';
	scheduleHtml += '<div class="now-has">';
	scheduleHtml += '<span>'+periodinfo.sales_num+'</span><p>已参与</p>';
	scheduleHtml += '</div>';
	scheduleHtml += '<div class="total-has">';
	scheduleHtml += '<span  id="CodeQuantity">'+periodinfo.price+'</span><p>总需人次</p>';
	scheduleHtml += '</div>';
	scheduleHtml += '<div class="overplus-has">';
	scheduleHtml += '<span id="CodeLift">'+parseInt(periodinfo.price-periodinfo.sales_num)+'</span><p>剩余</p>';
	scheduleHtml += '</div>';
	scheduleHtml += '</div>';
	$(".line-time").html(scheduleHtml);	
	if (limit > 0) {
		tip ='<div class="xg-tips"><i></i>限购<span>'+limit+'</span>人次</div>';
		$("#span_tip").append(tip);
	};
	$.changeByNum('divNumber',periodinfo.price,parseInt(periodinfo.price-periodinfo.sales_num),limit,'mine-prob');
}

function createPhotoList(photos){
	var photolist = picUrl = bigPicUrl = bigPicList = '';
	$.each(photos,function(i,v){
		picUrl = createGoodsImgUrl(v, photoSize[0], photoSize[0]);
		photolist += '<li><img width="40" height="40" alt="" name="'+v+'" src="'+picUrl+'"></li>';
		bigPicUrl = createGoodsImgUrl(v, photoSize[2], photoSize[2]);
		if (i == 0) {
			bigPicList += '<img src="'+bigPicUrl+'" />';
		}else{
			bigPicList += '<img style="display:none" src="'+bigPicUrl+'" />';
		}
	})
	$("#mycarousel").append(photolist);	
	$("#BigViewImage").append(bigPicList);
	$("#mycarousel li").hover(function(){
		var i = $(this).index();
		$("#BigViewImage img").hide().eq(i).show();
	})
	// $(".jqzoom").jqueryzoom({
	// 			xzoom: 400, //zooming div default width(default width value is 200)
	// 			yzoom: 400, //zooming div default width(default height value is 200)
	// 			offset: -300, //zooming div default offset(default offset value is 10)
	// 			position: "right", //zooming div position(default position value is "right")
	// 		});

}

function success_gocart(json){
	if (json) {
		window.location.href = '/cart.html';
	};
}