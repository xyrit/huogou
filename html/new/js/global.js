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
    var headerShow = $('.sidebar').attr('data-show');
    function headerMin(){
        if (!headerOn && $(window).scrollTop()>20){
            $(".sidebar,.push-fixed").fadeIn();
            headerOn = 1;
        }else if(headerOn && $(window).scrollTop()<20){
            $(".sidebar,.push-fixed").fadeOut();  
            headerOn = 0;
        }
    }

    if (!headerShow) {
        $(window).on('scroll', function(){
            headerMin();
        })
    }else{
        $('.sidebar').fadeIn();
    }

    //----------------侧边栏购物车------------
    $('.car-modify-less').on('click',function(){
        var numIpt = $(this).siblings('.car-list-num');
        var iNum = parseInt(numIpt.val());
        if (iNum > 1) {
            numIpt.val(iNum - 1);
        }
    })

    $('.car-modify-add').on('click',function(){
        var numIpt = $(this).siblings('.car-list-num');
        numIpt.val(parseInt(numIpt.val())+1);
    })

    $('.car-list-num').on('keyup',function(){
        var iNum = parseInt($(this).val());
        if (iNum < 1 || !iNum) {
            $(this).val(1);
        }else{
            $(this).val(iNum);
        }
    }).on('mouseenter',function(){
        $(this).select();
    }).on('mouseleave',function(){
        $(this).focus().val($(this).val());
    })

    function carInfoUpdate(This){
        var val = This.find('.car-list-num').val();
        This.find('.car-info-num').html(val);
        This.find('.car-info-money').html('￥' + val + '.00');
    }


    $('#car-list').find('dl').on('mouseenter',function(){
        $(this).addClass('hover');
    }).on('mouseleave',function(){
        $(this).removeClass();
        carInfoUpdate($(this));
    })

    $('.car-list-delete').on('click',function(){
        $(this).parents('dl').remove();
    })


    var innerTimer = null;
    $('.sidebar_shop, #car-inner').on('mouseenter',function(){
        $('#car-inner').stop().fadeIn();
        clearTimeout(innerTimer);
    }).on('mouseleave',function(){
        clearTimeout(innerTimer);
        innerTimer = setTimeout(function(){
            $('#car-inner').stop().fadeOut();
        },1e3)
    })

    // ---------------累计参与----------------
    var arrCount = [];
    var oldCountLen = null;

    function countInit(){
        var strCounts = "";
        var arrComma = [];
        for (var i = 1, l = arrCount.length - 1; i < Math.floor(l / 3) + 1; i++) {
            arrComma.unshift(l - 3 * i);
        };

        for (var i = 0; i < arrCount.length; i++) {
            strCounts += "<li class='num'><aside>";
            for (var j = arrCount[i]; j >= 0; j--) {
                strCounts += "<em>" + j + "</em>";
            }
            strCounts += "</aside></li>";
            if (i == arrComma[0]) {
                strCounts +="<li class='nobor'>,</li>";
                arrComma.shift();
            }
        }

        $('#counts').html(strCounts).find('aside').each(function(){
            $(this).stop().animate({'bottom':($(this).find('em').size() - 1) * -32},2e3);
        });
    }

    function countUpdate(){
        $('#counts').find('aside').each(function(index){
            var old = $(this).find('em').eq(0).html();
            var newNum = arrCount[index] - 1;
            var strCounts = "";

            do{
                newNum++;
                if (newNum > 9) newNum = 0;
                strCounts += "<em>" + newNum + "</em>";
            }while(old != newNum)

            $(this).html(strCounts).css('bottom',0).stop().animate({'bottom':($(this).find('em').size() - 1) * -32},2e3);
        })
    }

    function count(){
        $.ajax({
            url : 'abc'+Math.floor(Math.random()*5+1)+'.txt', //请求地址现为测试txt文件
            success : function(data,status,xhr){
                arrCount = data.replace(/[^0-9]/ig,"").split("");
                if (arrCount.length > 0 && arrCount.length == oldCountLen) {
                    countUpdate();
                }else if(arrCount.length > 0){
                    countInit()
                }
                oldCountLen = arrCount.length;
                setTimeout(count, 1e4)
            }
        })
    }

    if ($('#counts').size() > 0) {
        count();
    }
    // ---------------累计参与----------------

    // ---------------服务器时间----------------
    function timeFormat(data){
        return data > 9 ? data : "0" + data;
    }
    if ($('#server-time').size() > 0) {
        var serverTime = null;
        $.ajax({
            url : 'abc1.txt', //请求地址改为任意可访问的地址即可
            success : function(data,status,xhr){
                serverTime = xhr.getResponseHeader("Date");
                serverTime = Date.parse(serverTime);
                setInterval(function(){
                    var sH = new Date(serverTime).getHours();
                    var sM = new Date(serverTime).getMinutes();
                    var sS = new Date(serverTime).getSeconds();
                    $('#server-time').html('<span>' + timeFormat(sH) + '</span> : <span>' + timeFormat(sM) + '</span> : <span>' + timeFormat(sS) + '</span>');
                    serverTime += 1000;
                },1e3);
            }
        })
    }
        
    // ---------------服务器时间----------------
        
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