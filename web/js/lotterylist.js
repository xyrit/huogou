var cid = getCid();
var page = getPage();
var perpage = 20;
var maxSeconds = serverTime;
$(function() {
	$.getContent(apiBaseUrl+'/product/catlist','','cateList');

	$('.p_list').html('<img src="'+divLoadingImg+'">');
	var data = {'cid':cid, 'page':page, 'perpage': perpage};
	$.getJsonp(apiBaseUrl+'/period/list',data,function(json){
		success_lotteryList(json);
	});
	if (cid==0) {
		$.getContent(apiBaseUrl+'/period/get-start-raffle-list',{'time':maxSeconds},'startRaffleList');
		setInterval(function () {
			$.getContent(apiBaseUrl+'/period/get-start-raffle-list',{'time':maxSeconds},'startRaffleList');
		}, 3000);
	}


});

function success_cateList(json){

	var html = '';
	var catName = '全部';
	var catUrl = createPeriodListUrl(0);
	var list = json.list;
	html += '<a href="'+catUrl+'" title="'+catName+'">'+catName+'</a>';
	$.each(list,function(i,v){
		catName = v.name;
		catUrl = createPeriodListUrl(v.id);
		if (cid==v.id) {
			html += '<a  class="act" href="'+catUrl+'" title="'+catName+'">'+catName+'</a>';
		} else {
			html += '<a href="'+catUrl+'" title="'+catName+'">'+catName+'</a>';
		}
	});
	$('.announce_sub').append(html);
	if (cid == 0) {
		$(".announce_sub a:first").addClass('act');
	};
}

function success_lotteryList(json) {
	var list = json.list;
	var html = '';
	$.each(list, function(i,v) {
		var goodsName = v.goods_name;
		var goodsPic = v.goods_picture;
		var periodNumber = v.period_number;
		var price = v.price;
		var periodId = v.period_id;
		var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
		var lotterUrl = createPeriodUrl(periodId);
		var limitNum = v.limit_num;
		var buyUnit = v.buy_unit;
		var leftTime = v.left_time;
		if (leftTime>0 || typeof v.user_home_id == 'undefined') {
			cls = '';
			i%4 == 0 ? cls+= ' bl1': '';
			i<4 ? cls+= ' bt1' : '';
			html += '<ul class="announce_list '+cls+'" period-id="'+periodId+'" id="publish-goods-'+periodId+'">';
			html += '<li>';
			if (limitNum>0) {
				html += '<div class="f-callout"><span class="xgou">限购</span></div>';
			} else if (buyUnit==10) {
				html += '<div class="f-callout"><span class="sbei">十元</span></div>';
			}
			html += '<b class="announce_ico">正在<br>揭晓</b>';
			html += '<a href="'+lotterUrl+'" target="_blank"><picture><img src="'+goodsImgUrl+'"></picture></a>';
			html += '<h3><a href="'+lotterUrl+'" target="_blank" title="'+goodsName+'">'+goodsName+'</a></h3>';
			html += '<aside>总需：'+price+'人次 </aside>';
			html += '<p>揭晓倒计时</p>';
			html += '<span class="left-time" left-time="'+leftTime+'" col="0"></span>';
			html += '</li>';
			html += '</ul>';
		} else {

			var luckyCode = v.lucky_code;
			var raffTime = v.raff_time;
			var userName = v.user_name;
			var userHomeId = v.user_home_id;
			var userAvatar = v.user_avatar;
			var userBuyNum = v.user_buy_num;
			var userAddr = v.user_addr;
			var userPic = createUserFaceImgUrl(userAvatar,avatarSize[1]);
			var userCenterUrl = createUserCenterUrl(userHomeId);
			var shareId = v.share_id;
			var shareUrl = createShareDetailUrl(shareId);

			cls = '';
			i%4 == 0 ? cls+= ' bl1': '';
			i<4 ? cls+= ' bt1' : '';
			html += '<ul class="award_list '+cls+'" id="publish-goods-'+periodId+'">';
			html += '<li>';
			if (limitNum>0) {
				html += '<div class="f-callout"><span class="xgou">限购</span></div>';
			} else if (buyUnit==10) {
				html += '<div class="f-callout"><span class="sbei">十元</span></div>';
			}
			html += '<picture><a href="'+lotterUrl+'" target="_blank"><img src="'+goodsImgUrl+'"></a> </picture>';
			html += '<h3><a href="'+lotterUrl+'" target="_blank" title="'+goodsName+'">'+goodsName+'</a></h3>';
			html += '<aside>总需：'+price+'人次 </aside>';
			html += '<article>';
			html += '<summary>';
			html += '<i></i><a href="'+userCenterUrl+'" target="_blank"><picture><b></b><img src="'+userPic+'" alt=""> </picture></a> <aside>恭喜</aside><p title="'+userName+'">'+userName+'</p>';
			html += '</summary>';
			html += '<div>';
			html += '<p class="hui">来自：<i>'+userAddr+'</i></p>';
			html += '<p class="cheng">幸运号码：<i>'+luckyCode+'</i></p>';
			html += '<p class="cheng">本期参与：<i>'+userBuyNum+'人次</i></p>';
			html += '<p class="hui">揭晓时间：<i>'+raffTime+'</i></p>';
			html += '<a class="xqBtn" href="'+lotterUrl+'" target="_blank">查看详情</a>';
			if (shareId) {
				html += '<p class="ckBtn"><span>已晒单</span><a href="'+shareUrl+'" target="_blank">查看</a></p>';
			}
			html += '</div>';
			html += '</article>';
			html += '</li>';
			html += '</ul>';
		}
	});
	$('.p_list').html(html);
	createPage(page, json.totalPage, 5, json.totalCount);
	$('.left-time').each(function() {
		leftTime(new Date().getTime(),$(this),completeLeftTime);
	});
	$('.announce_title aside i').text(json.totalCount);
}

function success_startRaffleList (json) {
	var list = json.list;
	maxSeconds = json.maxSeconds;

	if (list.length>0) {
		$.each(list, function(i,v) {
			var goodsName = v.goods_name;
			var goodsPic = v.goods_picture;
			var periodNumber = v.period_number;
			var periodId = v.period_id;
			var price = v.price;
			var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
			var lotterUrl = createPeriodUrl(periodId);
			var limitNum = v.limit_num;
			var buyUnit = v.buy_unit;
			var leftTimeVar = v.left_time;
			var html = '';
			html += '<ul class="announce_list" period-id="'+periodId+'" id="publish-goods-'+periodId+'">';
			html += '<li>';
			if (limitNum>0) {
				html += '<div class="f-callout"><span class="xgou">限购</span></div>';
			} else if (buyUnit==10) {
				html += '<div class="f-callout"><span class="sbei">十元</span></div>';
			}
			html += '<b class="announce_ico">正在<br>揭晓</b>';
			html += '<a href="'+lotterUrl+'" target="_blank"><picture><img src="'+goodsImgUrl+'"></picture></a>';
			html += '<h3><a href="'+lotterUrl+'" target="_blank" title="'+goodsName+'">'+goodsName+'</a></h3>';
			html += '<aside>总需：'+price+'人次 </aside>';
			html += '<p>揭晓倒计时</p>';
			html += '<span class="left-time" left-time="'+leftTimeVar+'" col="0"></span>';
			html += '</li>';
			html += '</ul>';
			$('.p_list').prepend(html);
			$('.p_list ul').length >20 ? $('.p_list ul:last').remove() : '';
			refreshListClass();
			leftTime(new Date().getTime(),$('.p_list ul:first .left-time'), completeLeftTime);
		});


	}

}

function completeLeftTime(obj) {
	var periodId = obj.parent().parent().attr('period-id');
	obj.prev().text('已满员');
	obj.before('<summary>正在计算中<i class="dotting">...</i></summary>');
	obj.remove();

	var ms = 3000;
	var periodInfoTimes = 0;
	var s = setInterval(function() {
		$.getJsonp(apiBaseUrl+'/period/info',{'id':periodId}, function(json) {
			if (typeof json.periodInfo.user_name != 'undefined') {
				completeLotteryInfo(json)
				clearInterval(s);
				ms = 500;
			}
			if (periodInfoTimes>3) {
				clearInterval(s);
				return;
			}
			periodInfoTimes += 1;
		});

	} ,ms);
}

function completeLotteryInfo(json) {
	var periodInfo = json.periodInfo;
	var goodsName = periodInfo.goods_name;
	var goodsPic = periodInfo.goods_picture;
	var periodNumber = periodInfo.period_number;
	var price = periodInfo.price;
	var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
	var lotterUrl = createPeriodUrl(periodInfo.period_id);
	var limitNum = periodInfo.limit_num;
	var buyUnit = periodInfo.buy_unit;
	var luckyCode = periodInfo.lucky_code;
	var raffTime = periodInfo.raff_time2;
	var userName = periodInfo.user_name;
	var userHomeId = periodInfo.user_home_id;
	var userAvatar = periodInfo.user_avatar;
	var userBuyNum = periodInfo.user_buy_num;
	var userAddr = periodInfo.user_addr;
	var userPic = createUserFaceImgUrl(userAvatar,avatarSize[1]);
	var userCenterUrl = createUserCenterUrl(userHomeId);

	var html = '';
	html += '<li>';
	if (limitNum>0) {
		html += '<div class="f-callout"><span class="xgou">限购</span></div>';
	} else if (buyUnit==10) {
		html += '<div class="f-callout"><span class="sbei">十元</span></div>';
	}
	html += '<picture><a href="'+lotterUrl+'" target="_blank"><img src="'+goodsImgUrl+'"></a> </picture>';
	html += '<h3><a href="'+lotterUrl+'" target="_blank" title="'+goodsName+'">'+goodsName+'</a></h3>';
	html += '<aside>总需：'+price+'人次 </aside>';
	html += '<article>';
	html += '<summary>';
	html += '<i></i><a href="'+userCenterUrl+'" target="_blank"><picture><b></b><img src="'+userPic+'" alt=""> </picture></a> <aside>恭喜</aside><p>'+userName+'</p>';
	html += '</summary>';
	html += '<div>';
	html += '<p class="hui">来自：<i>'+userAddr+'</i></p>';
	html += '<p class="cheng">幸运号码：<i>'+luckyCode+'</i></p>';
	html += '<p class="cheng">本期参与：<i>'+userBuyNum+'人次</i></p>';
	html += '<p class="hui">揭晓时间：<i>'+raffTime+'</i></p>';
	html += '<a class="xqBtn" href="'+lotterUrl+'" target="_blank">查看详情</a>';
	html += '</div>';
	html += '</article>';
	html += '</li>';

	var objLi = $('#publish-goods-'+periodInfo.period_id);
	objLi.removeClass('announce_list');
	objLi.addClass('award_list');
	objLi.html(html);
}

function refreshListClass() {
	$('.p_list ul').each(function(i,v) {
		v = $(v);
		v.removeClass('bl1');
		v.removeClass('bt1');

		if (i%4 == 0) {
			v.addClass('bl1');
		}
		if (i<4) {
			v.addClass('bt1');
		}

	});
}


function getPage() {
	var href = window.location.href;
	var s = 'm([0-9]+).html';
	var reg = new RegExp(s);
	var r = href.match(reg);
	if (r != null) {
		return r[1];
	}
	s = 'i([0-9]+)m([0-9]+).html';
	reg = new RegExp(s);
	r = href.match(reg);
	if (r != null) {
		return r[2];
	}
	return 1;
}

function getCid() {
	var href = window.location.href;
	var s = 'i([0-9]+)(m([0-9]+))?.html';
	var reg = new RegExp(s);
	var r = href.match(reg);
	if (r != null) {
		return r[1];
	}
	return 0;
}