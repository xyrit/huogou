$(function(){
  var data = {'id':getPeriodId()};
  $.getContent(apiBaseUrl+'/period/info',data,'productinfo');
})

//商品详情
function success_productinfo(json){
  var neednum = json.periodInfo.price.split('.')[0];
  $(".present").append(json.periodInfo.goods_catNav);
  $(".phase_centre_title").append('<h2>'+json.periodInfo.goods_name+'</h2>');
  $(".phase_centre_title").append('<aside>期号&nbsp;:&nbsp;'+json.periodInfo.period_no+'&nbsp;&nbsp;&nbsp;价值&nbsp;:&nbsp;￥'+neednum+'.00&nbsp;&nbsp;&nbsp;&nbsp;每满'+neednum+'人次，即抽取1人获得该商品</aside>');
    $("#contentInfo").html(json.periodInfo.goods_info);
  if (json.periodInfo.left_time > 0) {//揭晓中

      document.title = json.periodInfo.goods_name + '_伙购网';
      $(".present").append('<i></i>商品详情');
      var daojihtml = '<div class="daoji">';
          daojihtml += '<p class="title">揭晓倒计时 丨 期号：'+json.periodInfo.period_no+'   </p>';
          daojihtml += '<p class="time"><span class="left-time" left-time="'+json.periodInfo.left_time+'" col="0"' +
           ' lxfday="yes"></span></p></div>';

      $(".phase_centre_title").after(daojihtml);
      $('#total_num').html(neednum);
      $.getJsonp(apiBaseUrl+'/product/images',{'id':json.periodInfo.goods_id},function(data){
          createPhotoList(data,json.periodInfo.limit_num,null,null,json.periodInfo.buy_unit)
      });

      $("#neednum").text(neednum);
      //$(".con_title li:first").html('<i></i>计算结果');
      $(".con_title li:first").html('<i></i>计算结果').siblings('li').addClass('jisuan_end');
      $('.phase_rl_title').find('span').on('click', function(){
          $(this).addClass('act').siblings().removeClass('act');
          $('.phase_rl_list').stop().hide().eq($(this).index()).fadeIn();
      }).eq(0).trigger('click');
      leftTime(new Date().getTime(),$(".left-time"),refresh);
      isFollowed(json.userInfo.followed);
      $(".guanzhu").click(function(){
        $.getContent($(this).attr('href'),{"token":token,"pid":json.periodInfo.goods_id},'follow');
        return false;
      })

  }else{//已揭晓
      document.title = json.periodInfo.goods_name + '_揭晓详情_伙购网';
      $('#phase').removeClass('phase_fl').addClass('phase_fl2');

      $(".phase_fl2").find('#phase_pic').remove();
      $(".phase_fl2").find('article').remove();
      $("#phase picture").append('<img src="'+createGoodsImgUrl(json.periodInfo.goods_picture,photoSize[2],photoSize[2])+'">');
      $(".phase_fl2").append('<a href="'+createGoodsUrl(json.periodInfo.goods_id)+'" class="phase_fl_a">查看商品详情</a>');

      //$('.con_cut').attr('id','con_cut2');
      $(".present").append('<i></i>揭晓详情');
      $(".parse02").remove();
      $(".phase_rl").remove();
      var prePeriodUrl = 'javascript:;';
      var nextPeriodUrl = createGoodsUrl(json.periodInfo.goods_id);
      if (json.periodInfo.prePeriodId > 0) {
        prePeriodUrl = createPeriodUrl(json.periodInfo.prePeriodId);
      };

      if (json.periodInfo.nextPeriodId > 0) {
        nextPeriodUrl = createPeriodUrl(json.periodInfo.nextPeriodId);
      };
      /*$(".phase_con").append('<div class="phase-other"><a href="'+nextPeriodUrl+'" class="phase-other-prev"><i></i></a><a href="'+prePeriodUrl+'" class="phase-other-next"><i></i></a></div>');*/
      $(".phase_centre").addClass('jiexiao');
      var resulthtml='';
      resulthtml += '<div class="daoji result">';
      resulthtml += '<div class="result-left">';
      resulthtml += '<div class="result-left-bg"></div>';
      resulthtml += '<div class="result-left-img">';
      resulthtml += '<img src="'+createUserFaceImgUrl(json.periodInfo.user_avatar,avatarSize[2])+'">';
      resulthtml += '</div>';
      resulthtml += '</div>';
      resulthtml += '<div class="left"><p><a href="'+userBaseUrl+'/'+json.periodInfo.user_home_id+'/index" class="text-blue ellipsis">'+json.periodInfo.user_name+'</a>（'+json.periodInfo.user_addr+'）</p>';
      resulthtml += '<p>本期夺宝：'+json.periodInfo.user_buy_num+'人次 <a href="javascript:;" id="participate" class="text-red view-btn2">点击查看</a></p>';
      resulthtml += ' <p>揭晓时间：'+json.periodInfo.raff_time+'</p>';
      resulthtml += '<p>伙购时间：'+json.periodInfo.user_buy_time+'</p></div>';
      resulthtml += '<div class="right">'+json.periodInfo.lucky_code+'</div>';
      resulthtml += '</div>';

      $(".phase_centre_title").after(resulthtml);
      $('#total_num').html(neednum);
      $(".con_title li:first").html('<i></i>计算结果').siblings('li').addClass('jisuan_end');

      $.getJsonp(apiBaseUrl+'/product/images',{'id':json.periodInfo.goods_id},function(data){
          createPhotoList(data,json.periodInfo.limit_num,null,null,json.periodInfo.buy_unit)
      });

      //$('.calculation').hide();
      $('#participate').on('click',function(){
          if ($(".participate-in").length == 0) {
              var html='';
              html +='<div class="view-alert" id="viewalert2"><h3>幸运获得者本期总共参与<i>'+
                  json.periodInfo.user_buy_num+'</i>次<span style="font-size: 14px;padding-left: 5px;">';
              html +='</h3><button type="button" class="close-btn"></button><div id="participate-con"></div></div>';
            $("#attend_code2").html(html);
            $('.participate-in').stop().slideDown();
             $.getContent(apiBaseUrl + '/period/all-codes',{"pid":json.periodInfo.period_id},'getAllCodes');
          }
            //查看参与码弹出框
            //$('.view-btn').on('click',function(){
                $('.modal-backdrop').show();
                $('#attend_code2').show();
                $('#viewalert2').show();
            //});
            $('.close-btn').on('click',function(){
                $('.modal-backdrop').hide();
                $('#attend_code2').hide();
                $('#viewalert2').hide();
            });
        })

        // $('.participate-in-a').on(function(){
        //   // $('.participate-in').stop().slideUp();
        // })
        // $('.participate-in').stop().hide();
  }
    $.getContent(apiBaseUrl + '/period/compute',{"pid":json.periodInfo.period_id,"pno":json.periodInfo.period_number},'computeList');
  var picture = createGoodsImgUrl(json.periodInfo.goods_picture, photoSize[2], photoSize[2]);
  bShare.addEntry({
      title: json.periodInfo.goods_name,
      //url: "分享的链接，默认为当前页面URL",
      summary: "伙购网1元就可以买到你想要的商品哦，小伙伴们购起来！",
      pic: picture
  });

  $(".phase_centre").attr("pid",json.periodInfo.period_id);

  if (json.periodInfo.current_number == null) {
    var perPage = 9;
  }else{
    var perPage = 8;
    $(".phase").append('<a class="first" href="/product/'+json.periodInfo.goods_id+'.html"><i></i><b></b></a>');
  }
  var offset;
  if (json.periodInfo.period_number < 7) {
    offset = 7;
  }else{
    if (json.periodInfo.current_number - json.periodInfo.period_number < 8) {
      offset = 0;
    }else{
      offset = json.periodInfo.period_number;
      perPage = 7;
    }
  }
  $.getContent(apiBaseUrl+'/product/periodlist',{"id":getPeriodId(),"type":"period","offset":offset,"perpage":perPage},'periodList');
  $.getContent(apiBaseUrl+'/period/buylist',{'id':json.periodInfo.period_id},'buyList');
  $.getContent(apiBaseUrl+'/share/share-list',{"pid":json.periodInfo.goods_id},'topicList');
  $.getJsonp(apiBaseUrl+'/product/oldperiodlist',{'id':json.periodInfo.goods_id, 'showinfo':1, 'page':1, 'perpage': 10},function(json){
        allperiodlist(json);
    });
  $.getJsonp(apiBaseUrl+'/product/info',{'id':json.periodInfo.goods_id},function(json){
        newperiod(json);
    });
  getmycode();
  sliderPic();
}

function newperiod(json){
    var scheduleHtml='';
    var periodinfo=json.periodInfo;
    var surplus = parseInt(periodinfo.price-periodinfo.sales_num);
    scheduleHtml += '<h2>(最新一期) 正在火热进行中...</h2>';
    scheduleHtml += '<p class="progress">';
    scheduleHtml += ' <i style="width:'+changeTwoDecimal_f(periodinfo.sales_num*100/periodinfo.price)+'%"></i></p>';
    scheduleHtml += '<p>已完成<span class="text-red" id="percent">'+changeTwoDecimal_f(periodinfo.sales_num*100/periodinfo.price)+'%</span>，剩余<span class="text-red" id="remain">'+surplus+'</span>人次</p>';
    scheduleHtml += '<a href="/product/'+json.id+'.html" class="nowto-btn">立即前往</a>';
    $('#percent').html(scheduleHtml);
}
function success_computeList(json){
    if (json.list.length==50) {
        createComputeHtml(json);
    }else if(json.list.length==100) {
        createOldComputeHtml(json);
    } else {
        createOldComputeHtml(json);
    }

    $.each(json.list,function(i,v){
        var html = '<li>';
        html += '<span class="ren01">'+v.buy_time+'</span>';
        html += '<span class="ren02"><i></i>'+v.data+'</span>';
        html += '<span class="ren03"><a title="'+v.username+'" href="'+userBaseUrl+'/'+v.home_id+'">'+v.username+'</a></span>';
        html += '<span class="ren04">'+v.buy_num+'人次</span>';
        html += '<span class="ren05"><a href="/product/'+v.product_id+'.html">'+v.product_name+'</a></span>';
        html += '</li>';
        $(".jisuan_ren_list").append(html);
    })
    $('#total').html(json.price);
    if(json.luckyCode)$('#time_total').html(json.luckyCode);
    if(json.total)$('.rule-item-2 .sum').html(json.total);
    if(!json.shishiData.shishi_num)json.shishiData.shishi_num='0000';
        $('.rule-item-2 .q_mask').html(json.shishiData.shishi_num);
    var yushu=(parseInt(json.total)+parseInt(json.shishiData.shishi_num))%parseInt(json.price);
    if(yushu||yushu==0)$('#surplus').html(yushu);


}

function refresh(){
  $(".daoji").html('<img src="'+skinBaseUrl+'/img/daoji-load.gif" class="daoji-load">');
  $.getContent(apiBaseUrl + '/period/state',{'pid':$(".phase_centre").attr('pid')},'checkPeriodState');
}

function createComputeHtml(json){
    if(!json.luckyCode)json.luckyCode='?';
    if(!json.shishiData.shishi_num)json.shishiData.shishi_num='?';
    var computehtml='';
    computehtml += '<div class="jisuan_step">';
    computehtml += '<p class="title">截止该商品最后购买时间【<em id="endtime">'+json.endTime+'</em>】网站所有商品的最后50条购买时间(时、分、秒、毫秒)记录';
    computehtml += '</p>';
    computehtml += '<ul class="jisuan_list clear">';
    computehtml += '<li class="s-r03" style="width: 140px;" id="luckyCode"><p>'+json.luckyCode+'</p>';
    computehtml += '<span>最终幸运号码</span>';
    computehtml += '</li>';
    computehtml += '<li class="s-z">=</li>';
    computehtml += '<li class="s-z">(</li>';
    computehtml += '<li class="s-r02">';
    computehtml += '<p>'+json.total+'</p>';
    computehtml += '<span>以下50条时间取值之和</span><b></b></li>';
    computehtml += '<li class="s-z">+</li>';
    computehtml += '<li class="s-r03" id="shishicai">';
    computehtml += '<p>'+json.shishiData.shishi_num+'</p>';
    computehtml += '<span>“老时时彩”开奖号码</span></li>';
    computehtml += '<li class="s-z">)</li>';
    computehtml += '<li class="s-r05" style="margin: 0 10px;">';
    computehtml += '<p>%</p><span>(取余)</span><div>';
    computehtml += '<b></b>余数是指整数除法中被除数未被除尽部分,如7÷3 = 2 ......1，1就是余数。</div></li>';
    computehtml += '<li class="s-r03" id="total">';
    computehtml += '<p>'+json.price+'</p>';
    computehtml += '<span>总需参与人次</span></li>';
    computehtml += '<li class="s-z">+</li>';
    computehtml += '<li class="s-r03">';
    computehtml += '<p>10000001</p>';
    computehtml += '<span>固定数值</span></li>';
    computehtml += '</ul>';
    computehtml += '</div>';
    computehtml += '<ul class="jisuan_ren_list clear"></ul>';
    computehtml += '<a class="jisuan_more" href="javascript:;">展开全部50条数据</a>';

  $(".jisuan_detail").css("width","1140px").append(computehtml);
    var len=10;
    setTimeout(function () {
        var arr=$(".jisuan_ren_list li:not(:hidden)");
        if(arr.length>len)$('.jisuan_ren_list li:gt('+(len+1)+')').hide();
    },0.5);
    $('.con_cut').on('click','.jisuan_more',function(){
        $('.jisuan_ren_list').toggleClass('m_jisuan');
        $('.jisuan_more').toggleClass('jisuan_m_hover');
        if($('.jisuan_more').hasClass('jisuan_m_hover')){
            $('.jisuan_ren_list li:gt('+(len-1)+')').show();
            $('.jisuan_more').html('收起');
        }else{
            $('.jisuan_ren_list li:gt('+(len+1)+')').hide();
            $('.jisuan_more').html('展开全部50条数据');
        }
    })
}


function createOldComputeHtml(json) {
    if(!json.luckyCode)json.luckyCode='?';
    if(!json.shishiData.shishi_num)json.shishiData.shishi_num='?';
    var computehtml='';
    computehtml += '<div class="jisuan_step">';
    computehtml += '<p class="title">截止该商品最后购买时间【<em id="endtime">'+json.endTime+'</em>】网站所有商品的最后100条购买时间(时、分、秒、毫秒)记录';
    computehtml += '</p>';
    computehtml += '<ul class="jisuan_list clear">';
    computehtml += '<li class="s-r03" style="width: 140px;" id="luckyCode"><p>'+json.luckyCode+'</p>';
    computehtml += '<span>最终幸运号码</span>';
    computehtml += '</li>';
    computehtml += '<li class="s-z">=</li>';
    computehtml += '<li class="s-z">(</li>';
    computehtml += '<li class="s-r02">';
    computehtml += '<p>'+json.total+'</p>';
    computehtml += '<span>以下100条时间取值之和</span><b></b></li>';
    computehtml += '<li class="s-z">+</li>';
    computehtml += '<li class="s-r03" id="shishicai">';
    computehtml += '<p>'+json.shishiData.shishi_num+'</p>';
    computehtml += '<span>“老时时彩”开奖号码</span></li>';
    computehtml += '<li class="s-z">)</li>';
    computehtml += '<li class="s-r05" style="margin: 0 10px;">';
    computehtml += '<p>%</p><span>(取余)</span><div>';
    computehtml += '<b></b>余数是指整数除法中被除数未被除尽部分,如7÷3 = 2 ......1，1就是余数。</div></li>';
    computehtml += '<li class="s-r03" id="total">';
    computehtml += '<p>'+json.price+'</p>';
    computehtml += '<span>总需参与人次</span></li>';
    computehtml += '<li class="s-z">+</li>';
    computehtml += '<li class="s-r03">';
    computehtml += '<p>10000001</p>';
    computehtml += '<span>固定数值</span></li>';
    computehtml += '</ul>';
    computehtml += '</div>';
    computehtml += '<ul class="jisuan_ren_list clear"></ul>';
    computehtml += '<a class="jisuan_more" href="javascript:;">展开全部100条数据</a>';

    $(".jisuan_detail").css("width","1140px").html(computehtml);
    var len=10;//控制要显示的数量
    setTimeout(function () {
           var arr=$(".jisuan_ren_list li:not(:hidden)");
            if(arr.length>len)$('.jisuan_ren_list li:gt('+(len+1)+')').hide();
        },0.5);
   $('.con_cut').on('click','.jisuan_more',function(){
       $('.jisuan_ren_list').toggleClass('m_jisuan');
        $('.jisuan_more').toggleClass('jisuan_m_hover');
        if($('.jisuan_more').hasClass('jisuan_m_hover')){
            $('.jisuan_ren_list li:gt('+(len-1)+')').show();
            $('.jisuan_more').html('收起');
        }else{
            $('.jisuan_ren_list li:gt('+(len+1)+')').hide();
            $('.jisuan_more').html('展开全部100条数据');
        }
    })

}

function hideParticipate(){
  $('.participate-in').remove();
}

function success_getAllCodes(json){
  if (json) {
      var item = '';
      $.each(json,function(i,v){
          item +='<ul id="new2"><li>';
          item += '<p class="text-gray">'+v.buy_time+'</p>';
          item += '<p>';
          $.each(v.codes.split(","),function(ci,cv){
              if (cv == v.lucky_code)item += '<span style="color:red;">'+cv+'</span>';
              else item += '<span>'+cv+'</span>';
          })
          item += '</p>';
          item += '</li></ul>';
      $("#participate-con").html(item);
      });
      //弹出框滚动条
      $("#new2").mCustomScrollbar({
          theme:"minimal"
      });
  };
}

function success_checkPeriodState(json){
  if (json.code == 100) {
    if (json.result == 'announce') {
      window.location.reload();
      return false;
    }
  }
  $.getContent(apiBaseUrl + '/period/state',{'pid':$(".phase_centre").attr('pid')},'checkPeriodState');
}