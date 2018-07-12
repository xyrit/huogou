$(function(){
	$('.history-tbable tbody').find('tr').each(function(index){
		if ((index + 1) % 2 == 0){
			$(this).css('backgroundColor','#f7f7f7');
		}
	})

	//日历调取
    laydate({
        elem: '#history-time',
    });
    laydate({
        elem: '#history-time02'
    });

    var h = new Date().getHours();
    var m = new Date().getMinutes();

    $("#starttime_h").children('a').text(timeFormat(parseInt(h-1))).attr('value',timeFormat(parseInt(h-1)));
    $("#starttime_m").children('a').text(timeFormat(m)).attr('value',timeFormat(m));
    $("#endtime_h").children('a').text(timeFormat(h)).attr('value',timeFormat(h));
    $("#endtime_m").children('a').text(timeFormat(m)).attr('value',timeFormat(m));

    getData(1);

    $(".history-time input[type=submit]").click(function(){
        $(".history-tbable tbody").html('');
        $(".pagination").html('');
        getData(1);        
    })
})

function success_allBuyList(json){
    if (json.total > 0) {
        $.each(json.list,function(i,v){
            var item = '<tr>';
                item += '<td>';
                item += v.buy_time;
                item += '</td>';
                item += '<td class="blue">';
                item += '<a href="'+userBaseUrl+'/'+v.user_id+'" target="_blank">' + v.user_name + '</a>';
                item += '</td>';
                item += '<td class="drakBlue">';
                item += '<a href="'+createGoodsUrl(v.product_id)+'">' + v.product + '</a>';
                item += '</td>';
                item += '<td>';
                item += v.buy_nums + '人次';
                item += '</td>';
                item += '</tr>';
            $(".history-tbable tbody").append(item);    
        });
        createPage(json.page,json.total,json.totalPage,5);
        $(".pagination a").click(function(){
            var page = $(this).attr("p");
            if (page > 0) {
                $(".history-tbable tbody").html('');
                getData(page);
            }
            return false;
        })
    }else{
        $(".pagination").html('');
    }
}

function getData(page){
    var starttime = $("#history-time").val() + ' ' + $("#starttime_h").children('a').text() + ':' + $("#starttime_m").children('a').text();
    var endtime = $("#history-time02").val() + ' ' + $("#endtime_h").children('a').text() + ':' + $("#endtime_m").children('a').text();

    if ((parseInt(Date.parse(new Date(endtime))) - parseInt(Date.parse(new Date(starttime))))/3600/1000 > 1) {
        $('.safety-b-box').html('<i id="safety-b-close"></i><h3 style="width: 250px;">对不起，查询跨度不能超过1小时</h3>');
        $('#safety-b-con').fadeIn();
        setTimeout(function () {
            window.location.reload();
        }, 1000);
        return false;
    };

    var data = {"page":page,"starttime":starttime,"endtime":endtime};

    $.getContent(apiBaseUrl + '/buylist',data,'allBuyList');
}

function createPage(page,total,totalPage,maxButtonCount){
    page = parseInt(page);
    if (totalPage <= 1) {
        return;
    }
    if (page<=1) {
        page = 1;
    }
    if (page>=totalPage) {
        page = totalPage;
    }
    if (page<=1) {
        var prevButton = '<a href="javascript:void(0);" class="prev disabled">上一页</a>';
    } else {
        var prevButton = '<a href="javascript:void(0);" p="'+parseInt(page-1)+'" class="prev">上一页</a>';
    }

    if (page>=totalPage) {
        var nextButton = '<a href="javascript:void(0);" title="下一页" class="next disabled">下一页</a>';
    } else {
        var nextButton = '<a href="javascript:void(0);" p="'+parseInt(page+1)+'" title="下一页" class="next">下一页</a>';
    }

    var beginPage = Math.max(1,page - parseInt(maxButtonCount/2));
    var endPage = beginPage + maxButtonCount - 1;
    if (endPage > totalPage) {
        endPage = totalPage;
        beginPage = Math.max(1,endPage - maxButtonCount + 1);
    }

    var firstButton = '';
    var lastButton = '';
    if (beginPage > 1) {
        firstButton += '<a href="javascript:void(0);" p="1"><b></b>1</a>';
        firstButton += '<i>...</i>';
    }
    if (endPage<totalPage) {
        lastButton += '<i>...</i>';
        lastButton += '<a href="javascript:void(0);" p="'+totalPage+'"><b></b>'+totalPage+'</a>';
    }

    var buttons = '';
    for (var i=beginPage;i<=endPage;i++) {
        var curClass = '';
        if (i==page) {
            curClass = 'class="act"';
        }
        buttons += '<a '+curClass+' href="javascript:void(0);" p="'+i+'"><b></b>'+i+'</a>';
    }

    var pageHtml = '';
    pageHtml += prevButton + firstButton + buttons + lastButton + nextButton ;
    $('.pagination').html(pageHtml);
}

$(function(){
    $('.select_ck').click(function(e){
        $('.select_o').hide();
        $(this).siblings('.select_o').show();
    })
    $(document).click(function(e) {
        if(!$(e.target).parent().siblings().is('.select_o') && !$(e.target).parent().is('.select_ck')){
            $('.select_o').hide();
        }
    });
    $('.select_o').on('click','a',function(){
        $(this).parent('.select_o').siblings('.select_ck').find('a').text($(this).text());
        $(this).parent('.select_o').siblings('.select_ck').find('a').attr('value',$(this).attr('value'));
        $(this).parent('.select_o').siblings('.select_ck').find('a').addClass($(this).attr('class'));
        $(this).parent('.select_o').hide();
        $('.select_o').find('a').removeClass('selected');
        $(this).addClass('selected');
        $(this).parent().val($(this).text());
    })
})

