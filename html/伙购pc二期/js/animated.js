jQuery.extend( jQuery.easing,
{
	easeInOutExpo: function (x, t, b, c, d) {
		if (t==0) return b;
		if (t==d) return b+c;
		if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
		return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
	}
});
$(document).ready(function(){
	var oContainer = $('#interact-con'),
		aScene = $('.scene'),
		sceneLen = $('.scene').size(),
		iNow = 0,
		iNew = null,
		headerShow = 1,
		footerShow = 0,
		footerResize = 0,
		iMoving = 0,
		titleH2 = ['海量奖品随意挑选', '正品潮货一应俱全', '随时随地想买就买', '立即开启你的伙购'],
		titleP = ['分类精选 应有尽有', '惊喜无限 一网打尽', '支付方式选择更多更便捷', '专属于你的惊喜'];

	oContainer.css('height', sceneLen * 100 + '%');
	aScene.css('height', 100 / sceneLen + '%');
	aScene.eq(0).addClass('animated');


	$('body').on('mousewheel', function(event) {
		if (iMoving) return;
		var dir = event.deltaY;
		if (dir > 0 && footerShow){
			footerToggle();
		}else if (dir > 0 && iNow > 0) {
			iNew = iNow - 1;
			pageSwitch();
		}else if(dir < 0 && iNow < sceneLen - 1){
			iNew = iNow + 1;
			pageSwitch();
		}else if(dir < 0 && iNow == sceneLen - 1 && !footerShow){
			footerToggle();
		}else{
			return;
		}
	});

	$(document).on('keydown',function(e){
		var event = e || window.event;
		if (!iMoving) {
			if (event.keyCode == 38 && footerShow) {
				footerToggle();
			}else if (event.keyCode == 38 && iNow > 0) {
				iNew = iNow - 1;
				pageSwitch();
			}else if(event.keyCode == 40 && iNow < sceneLen - 1){
				iNew = iNow + 1;
				pageSwitch();
			}else if(event.keyCode == 40 && iNow == sceneLen - 1 && !footerShow){
				footerToggle();
			}else{
				return;
			}
		}
	}) 

	function pageSwitch(){
		if (iNew == iNow || iMoving)return;
		iNew = parseInt(iNew);
		iMoving = 1;
		oContainer.animate({'top' : -100 * iNew + "%"}, 900, 'easeInOutExpo',function(){
			iMoving = 0;
			aScene.eq(iNow).removeClass('animated');
			aScene.eq(iNew).addClass('animated');
			iNow = iNew;
		});
		$('#interact-fixed').find('h2,aside').hide();
		$('#interact-fixed').find('h2').text(titleH2[iNew]);
		$('#interact-fixed').find('aside').text(titleP[iNew]);
		$('#interact-fixed').find('h2,aside').show(300);
		indicatorSwitch();
	}

	function indicatorSwitch(){
		$('#indicator').find('li').eq(iNew).addClass('act').siblings().removeClass();
	}

	var iStr = '<ul id="indicator">';
	for (var i = 0; i < sceneLen; i++) {
		if (i == 0) {
			iStr += '<li class="act"><i></i></li>';
		}else{
			iStr += '<li><i></i></li>';
		}
	};
	iStr += '</ul>';
	oContainer.append(iStr);

	$('#indicator').find('li').on('click',function(){
		if ($(this).hasClass('act')) return;
		iNew = $(this).index();
		pageSwitch();

	})

	$('#indicator').css('marginTop',sceneLen / 2 * -24);


})
