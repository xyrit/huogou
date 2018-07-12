$(document).ready(function(){
	$('#mobile').on('mouseenter', function(){
			$(this).find('p').stop().fadeIn();
		}).on('mouseleave', function(){
			$(this).find('p').stop().fadeOut();
	})

    $('.sort').find('dl').each(function(index){
        if ((index + 1) % 2 == 0){
            $(this).css('backgroundColor','#f3f3f3');
        }
    })

	$('.log_arr').each(function(){
		$(this).on('mouseenter', function(){
			$(this).find('div').stop().fadeIn();
		}).on('mouseleave', function(){
			$(this).find('div').stop().fadeOut();
		})
	})

	$("#get_top").click(function(){  
        $('body,html').animate({scrollTop:0},1000);  
        return false;  
    });

    var searchInput = $('#search_input').val();
	$('#search_input').on('focus',function(){
		$('#search_span').hide();
		if ($(this).val() == searchInput) {
			$(this).val("");
		}
	}).on('blur',function(){
		$('#search_span').show();
		if($(this).val() == ""){
			$(this).val(searchInput);
		}
	})

	$('.sort').on('mouseenter', function(){
		$(this).find('div').stop().fadeIn();
	}).on('mouseleave', function(){
		$(this).find('div').stop().fadeOut();
	})

    var headerOn = 0;
    function headerMin(){
        if (!headerOn && $(window).scrollTop()>20){
            $(".sidebar").fadeIn();
            headerOn = 1;
        }else if(headerOn && $(window).scrollTop()<20){
            $(".sidebar").fadeOut();  
            headerOn = 0;
        }
    }

    $(window).on('scroll', function(){
        headerMin();
    })
})
	lxfEndtime();
    function lxfEndtime() {
        $(".lxftime").each(function () {
            var lxfday = $(this).attr("lxfday");
            var endtime = new Date($(this).attr("data-endtime")).getTime();
            var nowtime = new Date().getTime();
            var youtime = endtime - nowtime;
            var seconds = youtime / 1000;
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);
            var CDay = days;
            var CHour = hours % 24;
            var CMinute = minutes % 60;
            var CSecond = Math.floor(seconds % 60); 
            var CMSecond = Math.floor(seconds*100%100);

            if (endtime <= nowtime) {
                $(this).html("已过期"); 
            } else {
                $(this).html("<i>" + (CMinute<10?"0"+CMinute:CMinute) + "</i>:<i>" + (CSecond<10?"0"+CSecond:CSecond) + "</i>:<i>" + (CMSecond<10?"0"+CMSecond:CMSecond) + "</i>");
                $(this).attr("nowtime", zhtime(nowtime+100));
            }
        });
        setTimeout("lxfEndtime()", 1);
    }
    
    function zhtime(needtime) {
        var oks = new Date(needtime);
        var year = oks.getFullYear();
        var month = oks.getMonth() + 1;
        var date = oks.getDate();
        var hour = oks.getHours();
        var minute = oks.getMinutes();
        var second = oks.getSeconds();
        var msecond=oks.getMilliseconds()
        return month + '/' + date + '/' + year + ' ' + hour + ':' + minute + ':' + second+'.'+msecond;
    }

	function shoucang(sTitle,sURL){
	try{window.external.addFavorite(sURL, sTitle);}
		catch (e){
	try{window.sidebar.addPanel(sTitle, sURL, "");}
		catch (e){
			alert("加入收藏失败，请使用Ctrl+D进行添加");}
		}
	}