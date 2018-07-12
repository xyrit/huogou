(function ($) {
	$.fn.slideDown = function (duration) {
		var target = this;
		if (target.attr('animate') == 1)return;
		this.css({ display : 'block', overflow : 'hidden' }).attr( 'animate', '1' );

		var height = this.height();
		this.css({ height: 0 }).animate({ height: height }, duration,'ease-out',
			function(){
				target.attr('animate','0');
			}
		);
	};

	$.fn.slideUp = function (duration) {
		var target = this;
		if (target.attr('animate') == 1)return;
		var height = this.height();
		this.css({ height : height }).attr( 'animate', '1' );
		this.animate({ height : 0 }, duration, 'ease-out',
			function () {
				target.css({ display : 'none', height : '' }).attr( 'animate', '0' )
			}
		);
	};
})(Zepto);

function switchTab(tab,con,act,cons){
	$(tab).on('click',function(){
		$(this).addClass(act).siblings().removeClass(act);
		$(con).eq($(this).index()).addClass(act).fadeIn().siblings(cons).fadeOut().removeClass(act);
	})
}

//----------菜单展开/关闭
$(document).ready(function(){
	$('#ctrl_btn').click(function(){
		$(this).toggleClass('act')
		var oButton = $('#menu');
		if (oButton.css('display') == 'none') {
			oButton.slideDown(250);
			oButton.prev('a').css('color','#fff');
		}else{
			oButton.slideUp(250);
			oButton.prev('a').css('color','#000');
		}
	})

	$('#menu').find('a').click(function(){
		var obj = $('#menu');
		$('#ctrl_btn').removeClass('act')
		if (obj.css('display') == 'none') {
			obj.slideDown(250);
		}else{
			obj.slideUp(250);
		}
	})

//-------返回顶部
	$('#gotop').click(function(){
		$('html,body').scrollTop(0);
	})


})