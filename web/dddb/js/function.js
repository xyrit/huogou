function show_num(n){
	$(".gundongshu u").remove();
	var len = String(n).length;
	for(var i=0;i<len;i++){
		//$(".gundongshu").append("<u>0<br>1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9</u>");
		$(".gundongshu").append("<u></u>");
		var num=String(n).charAt(i);
		var y = -parseInt(num)*60;
		var obj = $(".gundongshu u").eq(i);
		obj.animate({
			top :String(y)+'px' 
			},800
		);
	}
}
function add_number(){
	var nums=Math.ceil(Math.random()*100998)+10000001;
	show_num(nums);	
}

$(document).ready(function() {
	show_num(88888888);
	$('.section3 i:gt(2)').hide();
	$(".section3 .sect3_2 em").click(function(){
		if($(".section3 .sect3_2").hasClass("active")){
			
		}else{
			add_number();
			$(".section3 .sect3_2").addClass("active");
			$('.section3 i:gt(2)').hide();
			setTimeout(function(){
				$('.section3 i:gt(2)').each(function(){
					var $rel = $(this).attr('rel');
					var $arr = $rel.split(',');
					var sytime=Number($arr[4]);
					$(this).show().css({"left":$arr[0]+'px',"top":$arr[1]+'px'})
					$(this).stop().animate({
						left: $arr[2] + 'px',
						top: $arr[3] + 'px'
						//opacity:1
					}, sytime);
				});
			},800);
		}
		setTimeout(function(){
			$(".section3 .sect3_2").removeClass("active");
		},800);
	})
	$(".scrolldiv").fullpage({
		navigation: true,
		afterRender: function(){
			$('.screen-main span').each(function(){
				var $rel = $(this).attr('rel');
				var $arr = $rel.split(',');
				var sytime=Number($arr[4]);
				$(this).stop().animate({
					left: $arr[2] + 'px',
					top: $arr[3] + 'px'
					//opacity:1
				}, sytime);
			});
			$("#fixdown").removeClass().addClass("color1");
			$(".section1 .showtit").fadeIn();
			//$(".section1").animate({opacity:1},200);
		},
		afterLoad: function(anchorLink, index){
			if(index){
				$('.screen-main span').each(function(){
					var $rel = $(this).attr('rel');
					var $arr = $rel.split(',');
					var sytime=Number($arr[4]);
					$(this).stop().animate({
						left: $arr[2] + 'px',
						top: $arr[3] + 'px'
						//opacity:1
					}, sytime);
				});
				//$(".section"+index).animate({opacity:1},200);
				$("#fixdown").removeClass().addClass("color"+index);
				$(".section"+index+" .showtit").fadeIn();
				
			}
			if(index == 2){
				setTimeout(function(){
					$(".sect2_2").stop().animate({top:"320px",left:"310px"},400);
				},1000);
				
				setTimeout(function(){
					$(".sect2_2").stop().animate({top:"520px",left:"460px"},400);
					$(".sect2_3").show(500);
				},1800);
			}
			if(index == 3){
				setTimeout(function(){
					$(".sect3_shou").stop().animate({top:"325px",left:"460px"},400);
				},800);
				
				setTimeout(function(){
					$(".sect3_shou").stop().animate({top:"520px",left:"520px"},400);
				},1500);
				
				setTimeout(function(){
					$(".section3 .sect3_2").addClass("active");
					add_number();
				},1600);
				setTimeout(function(){
					$(".section3 .sect3_2").removeClass("active");
				},2300);
				
				setTimeout(function(){
					$('.section3 i:gt(2)').each(function(){
						var $rel = $(this).attr('rel');
						var $arr = $rel.split(',');
						var sytime=Number($arr[4]);
						$(this).show().css({"left":$arr[0]+'px',"top":$arr[1]+'px'})
						$(this).stop().animate({
							left: $arr[2] + 'px',
							top: $arr[3] + 'px'
							//opacity:1
						}, sytime);
					});
				},2500);
			}
			if(index == 4){
				//clearInterval();
				$(".sect4_tit").css("top","20px");
				setTimeout(function(){
					setInterval(function(){
						var syindex=Math.ceil(Math.random()*4)-1;
						if($(".section4 span:eq("+syindex+")").hasClass("active")){
							$(".section4 span:eq("+syindex+")").removeClass("active");
						}else{
							$(".section4 span:eq("+syindex+")").addClass("active");
						}
					},150);
				},1500);
			}
			if(index == 5){
				//$(".sect4_tit").show();
				//$(".sect4_tit").css("top","160px");
				//$(".fixewm_down").addClass("actives");
				$(".sect4_tit").show().animate({top:"160px"});
				$(".fixewm_down").animate({marginTop:"-180px"});
			}else{
				//$(".fixewm_down").removeClass("actives");
				$(".fixewm_down").animate({marginTop:"-55px"});
			}
		},
		onLeave: function(index, direction){
			if(index){
				$('.screen-main span').each(function(){
					var $rel = $(this).attr('rel');
					var $arr = $rel.split(',');
					$(this).stop().animate({
						left: $arr[0] + 'px',
						top: $arr[1] + 'px'
						//opacity:0
					}, 400);
				});
				//$(".section"+index).animate({opacity:0.4},200);
				$(".section"+index+" .showtit").fadeOut();
				$(".sect2_3").hide();
				$(".sect4_tit").hide();
				$('.section3 i:gt(2)').hide();
				$(".section3 .sect3_2").removeClass("active");
				//$(".sect3_6").hide();
			}
		},setAutoScrolling:true
	});
	
	/*
	$("#fp-nav ul li:eq(0) a span").html("<i>新</i><em>2.0全新体验</em>");
	$("#fp-nav ul li:eq(1) a span").html("<i>正</i><em>品牌正品 精挑细选</em>");
	$("#fp-nav ul li:eq(2) a span").html("<i>公</i><em>规则透明 公平公正</em>");
	$("#fp-nav ul li:eq(3) a span").html("<i>奖</i><em>弹指一挥间 大奖在眼前</em>");
	$("#fp-nav ul li:eq(4) a span").html("<i>喜</i><em>立即开启专属于你的惊喜</em>");
	$('#fp-nav ul li a').hover(function(){
			$(this).find("span").stop().animate({width:"180px"},400);
		},function(){
			$(this).find("span").stop().animate({width:"0px"},400);
		}
	);
	*/
	//$(".bottombg").css("height","270px");
	//$(".bottombg .fp-tableCell").css("height","270px");
});