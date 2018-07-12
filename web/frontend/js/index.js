$(function(){
	//首页轮播图
	$.getContent( apiBaseUrl + '/banner/banner-list?type=0&num=3','','indexBannerList');

	//即将揭晓商品
	$.getContent( apiBaseUrl + '/product/public-goods?page=1&orderFlag=10&perpage=24','','publicGoodsList');

	//获取新品上架
	$.getContent( apiBaseUrl + '/product/new-product?page=1&orderFlag=40&perpage=8','','newProductList');

	//热门推荐商品
	$.getContent( apiBaseUrl + '/product/recommend-product?page=1&orderFlag=60&perpage=8', '', 'recommendProductList');

	//晒单分享
	$.getContent( apiBaseUrl + '/share/topic-list?page=1&orderFlag=30&perpage=7', '', 'shareTopicList');
})

function success_indexBannerList(json){
	var html = '';
	$.each(json,function(i,v){
		html += '<li style="float: left;">';
		html += '<a href="'+ v.src+'" target="_blank">';
		html += '<img src="'+ v.picture+'" alt="'+ v.name +'" width="'+ v.width +'" height="'+ v.height +'">';
		html += '</a>';
		html += '</li>';
	})
	$("#slideImg").append(html);
}

function success_publicGoodsList(msg){
	$("#divLoadingLine").hide();
	for(var i=0,l=msg.length;i<l;i++){
		goodsUrl = createGoodsUrl( msg[i].id);
		var num = msg[i]['sales_num']/msg[i]['price']*100;
		var progress = num.toFixed(2);

		if(progress == 0){
			var backgroud = "backgroud:#ddd;"
		}else{
			backgroud = "";
		}

		if(msg[i]['limit_num'] != 0){
			var limit = "<div class='f-callout'><span class='F_goods_xg transparent-png'>限购</span></div>";
		}else{
			var limit = '';
		}

		picUrl = createGoodsImgUrl(msg[i].picture, photoSize[1], photoSize[1]);
		$('#divSoonGoodsList').append(
			"<div class='soon-list-con' idx='1'><div class='soon-list'><ul><li class='g-soon-pic'><a href='"+goodsUrl+"' target='_blank'>" +
			"<img src='"+picUrl+"' /></a></li>" +
			"<li class='soon-list-name'><a href='"+goodsUrl+"' target='_blank'>(第"+msg[i]['period_number']+"云)&nbsp;"+msg[i]['name']+"</a></li>" +
			"<li class='gray'>价值：￥"+msg[i]['price']+"</li>" +
			"<li class='g-progress'><dl class='m-progress'><dt><b style='width:"+progress+"%;"+backgroud+"'></b></dt><dd><span class='orange fl'><em>"+msg[i]['sales_num']+"</em>已参与</span> <span class='gray6 fl'><em>"+msg[i]['price']+"</em>总需人次</span> <span class='blue fr'><em>"+(msg[i]['left_num'])+"</em>剩余</span> </dd> </dl> </li>"+
			"<li><a href='"+goodsUrl+"' target='_blank' class='u-now'>立即伙购</a><a href='javascript:;' title='加入到购物车' codeid='1075811' surplus='6118' class='u-cart'> <s></s></a></li>"+
			""+limit+
			"</ul></div></div>"
		)
	}
}

function success_newProductList(msg){
	for(var i=0,l=msg.length;i<l;i++){
		goodsUrl = createGoodsUrl( msg[i].id);
		var num = msg[i]['sales_num']/msg[i]['price']*100;
		var progress = num.toFixed(2);

		if(progress == 0){
			var backgroud = "backgroud:#ddd;"
		}else{
			backgroud = "";
		}

		if(msg[i]['limit_num'] != 0){
			var limit = "<div class='f-callout'><span class='F_goods_xg transparent-png'>限购</span></div>";
		}else{
			var limit = '';
		}
		picUrl = createGoodsImgUrl(msg[i].picture, photoSize[1], photoSize[1]);
		$('#divNewGoodsList').append(
			"<div class='soon-list-con' idx='1'><div class='soon-list'><ul><li class='g-soon-pic'><a href='"+goodsUrl+"' target='_blank'>" +
			"<img src='"+picUrl+"' /></a></li>" +
			"<li class='soon-list-name'><a href='"+goodsUrl+"' target='_blank'>(第"+msg[i]['period_number']+"云)&nbsp;"+msg[i]['name']+"</a></li>" +
			"<li class='gray'>价值：￥"+msg[i]['price']+"</li>" +
			"</ul></div></div>"
		)
	}
}

function success_recommendProductList(msg){
	$("#divLoadingLine").hide();
	for(var i=0,l=msg.length;i<l;i++){
		goodsUrl = createGoodsUrl( msg[i].id);
		var num = msg[i]['sales_num']/msg[i]['price']*100;
		var progress = num.toFixed(2);
		picUrl = createGoodsImgUrl(msg[i].picture, photoSize[1], photoSize[1]);

		if(progress == 0){
			var backgroud = "backgroud:#ddd;"
		}else{
			backgroud = "";
		}

		$('#divHotGoodsList').append(
			"<div class='g-hotL-list'><div class='g-hotL-con'><ul><li class='g-hot-pic'><a href='"+goodsUrl+"' target='_blank'>" +
			"<img src='"+picUrl+"' /></a></li>" +
			"<li class='g-hot-name'><a href='"+goodsUrl+"' target='_blank'>(第"+msg[i]['period_number']+"云)&nbsp;"+msg[i]['name']+"</a></li>" +
			"<li class='gray'>价值：￥"+msg[i]['price']+"</li>" +
			"<li class='g-progress'><dl class='m-progress'><dt><b style='width:"+progress+"%;"+backgroud+"'></b></dt><dd><span class='orange fl'><em>"+msg[i]['sales_num']+"</em>已参与</span> <span class='gray6 fl'><em>"+msg[i]['price']+"</em>总需人次</span> <span class='blue fr'><em>"+msg[i]['left_num']+"</em>剩余</span> </dd> </dl> </li>"+
			"<li><a href='"+goodsUrl+"' target='_blank' class='u-imm'>立即伙购</a></li>"+
			"</ul></div><div class='u_buyCount' style='top:169px;' codeid='1240223'></div></div>"
		)

		if((l - i) <= 2){
			$('.slide-comd').append(
				"<div class='commodity'><ul>" +
				"<li class='comm-info fl'><span><a href='"+goodsUrl+"'>"+msg[i]['name']+"</a></span><p class='gray'>以参与<em class='orange'>"+msg[i]['sales_num']+"</em>人次</p></li>" +
				"<li class='comm-pic fr'><a href='"+goodsUrl+"' target='_blank' rel='nofollow'><cite><img  src='"+picUrl+"' border='0'  width='100' height='100'></cite></a></li>" +
				"</ul></div>"
			)
		}
	}
}

function success_shareTopicList(msg){
	$("#divLoadingLine").hide();
	for(var i=0,l=msg.length;i<l;i++){
		var shareUrl = createShareImgUrl(msg[i].header_image, 'big');
		var userFace = createUserFaceImgUrl(msg[i].user_avatar, 40);
		var detailUrl = createShareDetailUrl(msg[i].id);
		var userUrl = createUserCenterUrl(msg[i].user_id);

		if (i == 0) {
			$('#divPostRec').append(
				"<dl><dt><a href='http://share.huogou.com/detail-"+msg[i].id+".html' target='_blank' title='"+msg[i].title+"'>" +
				"<img src='"+shareUrl+"' /></a></dt>" +
				"<dd class='u-user'><p class='u-head'><a href='"+userUrl+"' target='_blank' title='"+msg[i].user_name+"'>" +
				"<img alt='"+msg[i].user_name+"' src='"+userFace+"' width='40' height='40' />" +
				"<i class='transparent-png'></i></a></p>" +
				"<p class='u-info'><span><a href='"+userUrl+"' target='_blank' title='"+msg[i].user_name+"'>"+msg[i].user_name+"</a><em>"+msg[i].created_at+"</em></span><cite><a href='"+detailUrl+"' target='_blank' title='"+msg[i].title+"'>"+msg[i].title+"</a></cite></p></dd>" +
				"<dd class='m-summary'><cite><a href='"+detailUrl+"' target='_blank'>"+msg[i].content+"</a></cite><b><s></s></b></dd></dl>"
			);
		} else {
			$('#ul_PostList').append(
				"<li>" +
				"<a href='"+detailUrl+"' target='_blank' title='"+msg[i].title+"'>" +
				"<cite>" +
				"<img alt='"+msg[i].title+"' src='"+shareUrl+"' />" +
				"</cite>" +
				"<p title='"+msg[i].title+"'>"+msg[i].title+"</p>" +
				"</a>" +
				"</li>"
			);
		}
	}
}