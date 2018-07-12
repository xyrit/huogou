var productId = 0;
var p_timer = null;
var lasttime = 0;

$(function(){
  checkLogin();
  $('.phase_fl li:last').css('marginRight','0');

  $('.phase_rl_title').find('span').on('click', function(){
      $(this).addClass('act').siblings().removeClass('act');
      $('.phase_rl_list').stop().hide().eq($(this).index()).fadeIn();
      $(".phase_rl_more").hide();
      $(".phase_rl_more").eq($(this).index()).fadeIn();
  }).eq(0).trigger('click');

  $('.con_title').find('li').on('click', function(){
      if($(this).hasClass('gouw')){
          return;
      }
      //tab切换
      $(this).addClass('act').siblings().removeClass('act');
      $('.con_box').stop().hide().eq($(this).index()).fadeIn();
      if(!$('.con_title li').hasClass('jisuan_end')){
          $('.hg-ma').each(function(){
              $(this).css({'width':'120px','paddingLeft':'30px'});
              $(this).children('.hg-hide').hide();
          })
      }
  }).eq(0).trigger('click');

  $("#thumblist li a").click(function(){
    $(this).parents("li").addClass("tb-selected").siblings().removeClass("tb-selected");
    $("#pic").attr('src',$(this).find("img").attr("mid"));
    $("#pic").attr('rel',$(this).find("img").attr("big"));
  });

  var timer = null;
  $('.shaidan_con_pic').find('dd').on('click', function(){
    $(this).parent().find('p').hide();
    $(this).find('p').stop().slideToggle(500);
  }).hover(function(){
    clearTimeout(timer);
  },function(){
    timer = setTimeout(function(){
      $('.shaidan_con_pic').find('p').slideUp();
    },50)
  })

  $('.xianm').on('click',function(){
    $(this).addClass('act')
  })

  $('.shaidan li:last').css('marginBottom','0');
  // clearInterval(p_timer);
  // p_timer = setInterval("mt0()",3000);

  if ($('.phase_centre').attr('pid')) {
    setInterval(function(){
      $.getContent(apiBaseUrl + '/period/get-new-buy-list',{'lasttime':lasttime,'pid':$('.phase_centre').attr('pid')},'newBuyList');
    },5000);
  };

  $(".phase_rl_more a").not('.disabled').click(function(){
    if ($(this).attr('to') == 'all') {
      $(".con_title li").removeClass('act').eq('1').addClass('act');
      $(".con_box").hide().eq('1').show();
      $(window).scrollTop($(".con_title").offset().top);
      return false;
    }
  })

})

//往期揭晓
function allperiodlist(json) {
    var list = json.list;
    var html = '';
    if(json.totalCount>0){
        $.each(list, function(i,v) {
            var goodsName = v.goods_name;
            var goodsPic = v.goods_picture;
            var periodNo = v.period_no;
            var price = v.price;
            var periodId = v.period_id;
            var goodsImgUrl = createGoodsImgUrl(goodsPic, photoSize[1], photoSize[1]);
            var lotterUrl = createPeriodUrl(periodId);
            var limitNum = v.limit_num;
            var buyUnit = v.buy_unit;
            var leftTime = v.left_time;

            var luckyCode = v.lucky_code;
            var raffTime = v.raff_time;
            var userName = v.user_name;
            var userHomeId = v.user_home_id;
            var userAvatar = v.user_avatar;
            var userBuyNum = v.user_buy_num;
            var userBuyTime = v.user_buy_time;
            var userIp = v.user_buy_ip;
            var userAddr = v.user_addr;
            var userId = v.uid;
            var userPic = createUserFaceImgUrl(userAvatar, avatarSize[1]);
            var userCenterUrl = createUserCenterUrl(userHomeId);
            var shareId = v.share_id;
            var shareUrl = createShareDetailUrl(shareId);

            html += '<ul class="old-item-group">';
            html += '<li class="old-item clearfix">';
            html += '<div class="item-num pull-left">期号 ' + periodNo + '</div>';
            html += '<div class="item-user pull-left">';
            html += '<img src="' + userPic + '" class="user-img pull-left"/>';
            html += '<p class="user-right pull-left">';
            html += '<span class="display-b">';
            html += '<span class="display-b pull-left">恭喜</span>';
            html += ' <a  href="' + userCenterUrl + '" class="text-blue ellipsis">' + userName + '</a>';
            html += ' <span class="display-b pull-left">(IP:' + userIp + ')获得了本期商品</span>';
            html += ' </span>';
            html += '<span class="display-b">用户ID:' + userId + '(ID为用户唯一不变标识)</span>';
            html += '<span class="display-b">本次参与：<span class="text-red">' + userBuyNum + '人次</span></span>';
            html += '</p>';
            html += '</div>';
            html += '<div class="item-user2 pull-left">';
            html += '<span class="display-b">幸运号码：<span class="text-red">' + luckyCode + '</span></span>';
            html += '<span class="display-b">揭晓时间：' + raffTime + '</span>';
            html += '<span class="display-b">伙购时间：' + userBuyTime + '</span>';
            html += '</div>';
            html += '<div class="item-view pull-left">';
            html += ' <a href="' + lotterUrl + '" class="text-blue">查看详情</a>';
            html += '</div>';
            html += '</li>';
            html += '</ul>';
        });
        html += '<div class="pagination" id="announced_p"></div>';
        $('#announced').html(html);
        if (json.page == 1 && $(".phase_rl_list").length > 0) {
            showNewBuyList(json.list,json.totalCount);
        };
        createPage(json.page,json.totalCount,json.totalPage,5);
        $("#announced_p a").click(function(){
            var page = $(this).attr("p");
            if (page > 0) {
                if(!json.list[0]['product_id'])json.list[0]['product_id']=json.list[0]['goods_id'];
                $("#announced").html('');
                $.getJsonp(apiBaseUrl+'/product/oldperiodlist',{'id':json.list[0]['product_id'], 'showinfo':1, 'page':page, 'perpage': 10},function(json){
                    allperiodlist(json);
                });
            }
            return false;
        })

        $('.left-time').each(function() {
            leftTime(new Date().getTime(),$(this),completeLeftTime);
        });
        $('.announce_title aside i').text(json.totalCount);
    }else{
         html = '<li class="tishi"><p>暂无记录!</p></li>';
        $('#announced').append('<ul class="phase_rl_list" style="height:120px;">'+html+'</ul>').find('li').css("cssText","margin-top:20px!important");
    }

}
function getPeriodId() {
    var reg = new RegExp("(lottery\/)([^&]*)(.html)");
    var r = window.location.href.match(reg);
    if (r != null) return unescape(r[2]); return null;
}

function getProductId() {
    var reg = new RegExp("(product\/)([^&]*)(.html)");
    var r = window.location.href.match(reg);
    if (r != null) return unescape(r[2]); return null;
}

function getmycode(){
  $.getContent(apiBaseUrl+'/period/mycodes',{'pid':$(".phase_centre").attr('pid'),'token':token},'myCodeList');
}

function createPhotoList(photos,limit,only,pid,buyUnit){
  var photolist = picUrl = midPicUrl = bigPicUrl = iCon = '';
  if (limit > 0) {
      iCon = '<div class="f-callout"><span class="xgou">限购</span></div>';
  } else if (buyUnit==10) {
      iCon = '<div class="f-callout"><span class="sbei">十元</span></div>';
      $('.renci_pic ').html('<img src="'+skinBaseUrl+'/img/pic10810.png" alt="">');
  }
  $.each(photos,function(i,v){
    picUrl = createGoodsImgUrl(v, photoSize[0], photoSize[0]);
    midPicUrl = createGoodsImgUrl(v, photoSize[2], photoSize[2]);
    bigPicUrl = createGoodsImgUrl(v, photoSize[3], photoSize[3]);
    if (i == 0) {
      if (only) {
        photolist += '<li class="act"><img alt="" name="'+v+'" src="'+picUrl+'" midpic="'+midPicUrl+'"></li>';
      }else{
        photolist += '<li class="act"><img alt="" name="'+v+'" src="'+picUrl+'" midpic="'+midPicUrl+'" bigpic="'+bigPicUrl+'"></li>';
      }
        $(".phase_fl picture").append(iCon+'<img id="pic" src="'+midPicUrl+'" />');
        $(".phase_fl2 picture").append(iCon+'<img id="pic" src="'+midPicUrl+'" />');
    }else{
      photolist += '<li><img alt="" name="'+v+'" src="'+picUrl+'" midpic="'+midPicUrl+'" bigpic="'+bigPicUrl+'"></li>';
    }
  });
  if (!only) {
    $("#phase_pic").append(photolist);
      $('#phase_pic').find('li').each(function(index){
          if ((index + 1) % 5 == 0) {
              $(this).css('marginRight',0);
          }
      })
    var aLi = $('#phase_pic').find('li');
    aLi.on('click', function(){
        $(this).addClass('act').siblings().removeClass('act');
        $('#pic').hide().attr('src',$(this).find('img').attr('midpic')).attr('bigpic',$(this).find('img').attr('bigpic')).show();
    }).eq(0).trigger('click');
    $("#pic").imagezoom();
  }else{
      $(".phase_fl2").find('#phase_pic').remove();
      $(".phase_fl2").find('article').remove();
      $(".phase_fl2").append('<a href="'+createGoodsUrl(pid)+'" class="phase_fl_a">查看商品详情</a>');
  }
}

function success_buyList(json){
    if (json.totalCount > 0) {
      lasttime = new Date(json.list[0].buy_time.replace('-','/')).getTime();
      $.each(json.list,function(i,v){
          var item = '';
          var userFaceImgUrl = createUserFaceImgUrl(v.user_avatar,avatarSize[0]);
          var username = v.user_name;
          var userHomeUrl = createUserCenterUrl(v.user_home_id);
          item += '<tr>';
          item += '<td class="buy_time">'+v.buy_time+'</td>';
          item += '<td class="userName"><picture><img src="'+userFaceImgUrl+'" alt=""></picture><a href="'+userHomeUrl+'" target="_blank" title="'+username+'">'+username+'</a></td>';
          item += '<td class="hg-ma">'+v.buy_num+'人次<span class="hg-hide" buyid="'+v.buy_id+'" userhome="'+userHomeUrl+'" userface="'+userFaceImgUrl+'" username="'+username+'">查看伙购码</span></td>';
          item += '<td class="buyIp"><div title="'+v.buy_ip_addr+' '+v.buy_ip+'">'+v.buy_ip_addr+'&nbsp;IP:'+v.buy_ip+'</div></td>';
          item += '<td class="buyDevice"><a target="_blank" href="'+ v.buy_device.url +'"><p class="ico_'+v.buy_device.ico+'">'+v.buy_device.name+'</p></a></td>';
          item += '</tr>';
          $(".canyu tbody").append(item);
      })
      if (json.page == 1 && $(".phase_rl_list").length > 0) {
        showNewBuyList(json.list,json.totalCount);
      };

        createPageNew(json.page,json.totalCount,json.totalPage,5);

      $("#canyunew a").click(function(){
            var page = $(this).attr("p");
            if (page > 0) {
                $(".canyu tbody").html('');
                $.getContent(apiBaseUrl+'/period/buylist',{'id':$(".phase_centre").attr('pid'),'page':page},'buyList');
            }
            return false;
        })
    }else{
      item = '<li class="tishi"><p>还没有人参与？</p><p>梦想与您只有1元的距离！</p></li>';
      $("#newbuy").append(item);
      $(".canyu").remove();
      $("#canyu").html('<ul class="phase_rl_list" style="height:120px;">'+item+'</ul>').find('li').css("cssText","margin-top:20px!important");
    }
}

//最新伙购
function showNewBuyList(list,total){
  $.each(list,function(i,v){
      var item = '';
      var userFaceImgUrl = createUserFaceImgUrl(v.user_avatar,avatarSize[0]);
      var username = v.user_name;
      var userHomeUrl = createUserCenterUrl(v.user_home_id);
      item += '<li>';
      item += '<picture><img src="'+userFaceImgUrl+'"></picture>';
      item += '<a href="'+userBaseUrl+'/'+v.user_home_id+'" title="'+v.user_name+'" target="_blank">'+v.user_name+'</a>';
      item += '<aside>'+v.buy_num+'人次</aside>';
      item += '</li>';
      $("#newbuy").append(item);
  })
  if (total > 12) {
    $(".phase_rl_more ").eq(0).find('a').show();
  }else if(total <= 12){
      $(".phase_rl_more ").eq(0).find('a').addClass('disabled');
  };
}

function success_periodList(json) {
  var periodId = getPeriodId();
  var html = '';
  $.each(json.list,function(i,v) {
      var lotterUrl = createPeriodUrl(v.id);
    if (i == 0 && json.offset > 0 && json.totalCount > 8) {
      html += '<a href="javascript:;" onclick="showMorePeriod('+json.list[0].product_id+')">...<b></b></a>';
    };
    if (v.id == periodId) {
      html += '<a class="act" href="'+lotterUrl+'"><b></b></a>';
    }else{
      html += '<a href="'+lotterUrl+'"><b></b></a>';
    }
  });
  $(".phase").append(html);
  if (json.totalCount > 8) {
    $(".phase").append('<a class="last" id="phase_more" href="javascript:;" onclick="showMorePeriod('+json.list[0].product_id+')">查看更多+</a>');
  };
}

function success_myCodeList(json){
  var html = '';
  if (json.code == 100) {
    if (json.data.length > 0) {
        html +='<div class="view-alert" id="viewalert"><h3>我的伙购号码<span style="font-size: 14px;padding-left: 5px;">(本期共参与了<span class="text-red">'+json.codes+'</span>人次)</span>';
        html +='</h3><button type="button" class="close-btn"></button><ul class="mCustomScrollbar" data-mcs-theme="minimal" >';

      $.each(json.data,function(i,v){
        html +='<li>';
        html += '<p class="text-gray">'+v.time+'</p>';
        html += '<p>';
        $.each(v.codes.split(","),function(ci,cv){
            if (cv == json.lucky_code) {
                html += '<span style="color:red;">'+cv+'</span>';
            }else{
                html += '<span>'+cv+'</span>';
            }
        })
        html += '</p>';
        html += '</li>';
      })
        html +='</ul></div>';
      if (json.codes >= 100) {
        $(".phase_rl_more").eq(1).find('a').show().attr('href',memberBaseUrl+'/default/buy-detail?id='+$(".phase_centre").attr('pid'));
      };
        $("#attend_code").html(html);
        $("#mybuy").html('<p>您已拥有'+json.codes+'个伙购号码 <a href="javascript:;" class="arrow-right view-btn">查看号码</a></p>');
        //查看参与码弹出框
        $('.view-btn').on('click',function(){
            $('.modal-backdrop').show();
            $('#attend_code').show();
            $('#viewalert').show();
        });
        $('.close-btn').on('click',function(){
            $('.modal-backdrop').hide();
            $('#attend_code').hide();
            $('#viewalert').hide();
        });

    }else{
        $("#mybuy").html('<p>您还没有参与本次伙购哦</p>');
    }



  }else{
    $("#mybuy").html('<p><a href="javascript:;" class="text-red_link" id="login">请登录</a>，查看您的伙购号码</p>');
    $("#mybuy #login").click(function(){
      showLoginForm();
    })
  }
}

function success_topicList(json){
  if (json.totalCount > 0) {
    $.each(json.list,function(i,v) {
      var item = '';
          item += '<li>';
          item += '<summary>';
          item += '<picture>';
          item += '<a href="'+userBaseUrl+'/'+v.user_home_id+'" target="_blank"><img src="'+createUserFaceImgUrl(v.user_avatar,avatarSize[2])+'" alt=""></a>';
          item += '</picture>';
          item += '<a href="'+userBaseUrl+'/'+v.user_home_id+'" target="_blank">'+v.user_name+'</a>';
          item += '</summary>';
          item += '<article>';
          item += '<div class="shaidan_title">';
          item += '<p><a href="'+shareBaseUrl+'/detail-'+v.id+'.html">'+v.title+'</a></p>';
          item += '<i>'+v.created_at+'</i>';
          item += '</div>';
          item += '<div class="shaidan_con">';
          item += '<p>'+v.content+'</p>';
          item += '<dl class="shaidan_con_pic clear">';
          $.each(v.pictures,function(pi,pv){
            item += '<dd>';
            item += '<picture><i></i><img src="'+createShareImgUrl(pv,'small')+'" alt=""></picture>';
            item += '<p><img src="'+createShareImgUrl(pv,'big')+'" alt=""></p>';
            item += '</dd>';
          })
          item += '</dl>';
          item += '<div class="shaidan_con_cut">';
          item += '<span class="xianm';
          if (v.is_up == 1) {
              item += ' act';
          }
          item +='" shareTopicId="'+v.id+'"><em>'+v.up_num+'</em>人羡慕嫉妒恨</span><span class="pinl"><a href="'+createShareDetailUrl(v.id)+'">'+v.comment_num+'条评论</a></span>';
          item += '</div>';
          item += '</div>';
          item += '</article>';
          item += '</li>';
        $(".shaidan").append(item);
    });
    var timer = null;
    $('.shaidan_con_pic').find('dd').on('click', function(){
      $(this).parent().find('p').hide();
      $(this).find('p').stop().slideToggle(500);
    }).hover(function(){
      clearTimeout(timer);
    },function(){
      timer = setTimeout(function(){
        $('.shaidan_con_pic').find('p').fadeOut();
      })
    })

    $('.xianm').on('click',function(){
        if (!$(this).hasClass('act')) {
            $.ajax({
                async: false,
                url: apiBaseUrl + '/share/up',
                type: "GET",
                dataType: 'jsonp',
                jsonp: 'callback',
                data: {id: $(this).attr('shareTopicId')},
                success: function (msg) {
                    if (msg.flag == 1) {
                    }
                }
            })
            $(this).find('em').html(parseInt($(this).find('em').text()) + 1);
            $(this).addClass('act')
        }
    })

    $('.shaidan li:last').css('marginBottom','0');

  }else{
    $(".shaidan").append('<li class="tishi">暂无晒单</li>')
  }

}

function showMorePeriod(pid){
  if ($(".hg-yun").length == 0) {
    var html = '';
        html += '<div class="hg-yun" style="display:none">';
        html += '<a href="javascript:;" title="关闭" class="a_close"></a>';
        html += '<div class="input-wrapper">';
        html += '<div class="label">直达第</div>';
        html += '<div class="inp"><input maxlength="7" id="txtPeriod" onblur="if(this.value==\'\') this.value=\'请输入\'" onclick="if(this.value==\'请输入\') this.value=\'\'" value="请输入" style="color:rgb(187, 187, 187);"></div>';
        html += '<div class="unit">期</div>';
        html += '<a id="btnGo" href="javascript:;" class="fly"></a>';
        html += '</div>';
        html += '<div class="hg-yun-con">';
        html += '<div id="scrollTool" class="scrollTool"></div>';
        html += '<b class="hg-yun-con-line"></b>';
        html += '<div class="jspContainer" id="scrollTool_con">';
        html += '<ul class="ng-pt-inner" id="scrollTool_wrap">';
        html += '';
        html += '</ul>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    $(".phase").after(html);
  };
  // $('#phase_more').on('click',function(){
  $('.hg-yun').stop().slideDown();
  // })
    $('body').on('keyup','#txtPeriod',function(){
        var txtPeriod = $('#txtPeriod');
        var inpVal = txtPeriod.val();
        inpVal = parseInt(inpVal);
        if(!isNaN(inpVal)&&inpVal>0){
            txtPeriod.val(inpVal);
        }else{
            txtPeriod.val("");
        }
        $('#txtPeriod').css('color','#333');
    }).on('blur','#txtPeriod',function(){
        if($('#txtPeriod').val() == ""){
            $('#txtPeriod').css('color','rgb(187, 187, 187)');
        }
    })

  $('.a_close').on('click',function(){
    $('.hg-yun').stop().slideUp();
  })
  var Phase = new bodyScroll({
    wrapBox : 'scrollTool_con',
    wrap : 'scrollTool_wrap',
    scrollTool : 'scrollTool'
  })
  // $('.hg-yun').stop().hide();
  $.getContent(apiBaseUrl+'/product/periodlist',{"id":pid,"perpage":200},'morePeriod');



}


//查看伙购码
$('.canyu').on('mouseenter','.hg-ma',function(){
    if($('.con_title li').hasClass('jisuan_end')){
        $(this).addClass('ma-hover');
    }
}).on('mouseleave','.hg-ma',function(){
    if($('.con_title li').hasClass('jisuan_end')){
        $(this).removeClass('ma-hover');
    }
})
var oiLeft = null;
$('.canyu').on('click','.hg-hide',function(){
    var ths = $(this);
    var buyId = ths.attr('buyid');
    var userHome = ths.attr('userhome');
    var userFace = ths.attr('userface');
    var userName = ths.attr('username');
    var pid  = $('.phase_centre').attr('pid');
    var luckCode = $('.phase_centre .gouma b').text();
    $.getContent(apiBaseUrl+'/period/getuserbuycodesbybuyid',{"periodid":pid,"buyid":buyId},'getUserBuyCodes', true, function(json) {
        if (json.codes) {
            var codes = json.codes.split(',');
            var html = '';
            $.each(codes, function(i,v) {
                if (luckCode==v) {
                    html += '<li class="act">'+v+'</li>';
                } else {
                    html += '<li>'+v+'</li>';
                }
            });
            $('#hg-box-codes-ul').html(html);
            var codesLen = codes.length;
            var htmlTitle = '<span class="w">';
                htmlTitle += '<a href="'+userHome+'" target="_blank"><b class="hg-ma-img"><img src="'+userFace+'" width="22" height="22" ></b>'+userName+'</a>';
                htmlTitle += '</span>';
                htmlTitle += '本次参与<span class="s">'+codesLen+'</span>人次';
            $('#hg-box-codes-title').html(htmlTitle);
            oiLeft = ths.offset().left - 380;
            $('#hg-ma-box').css({'top':ths.offset().top + ths.height() / 2 - 111, 'left':oiLeft, 'display':'block'})
        }
    });

});
$('#hg-ma-box').on('click','.hg-ma-del',function(){
    $('#hg-ma-box').css({'top':-111, 'left':-300, 'display':'none'});
});
//查看伙购码end

function success_getUserBuyCodes(json) {

}

function success_morePeriod(json){
  var peid = getPeriodId();
  if (json.totalCount > 0) {
    $("#scrollTool_wrap").html('');
    $(".hg-yun").prepend('<input type="hidden" name="product_id" value="'+json.list[0].product_id+'">');
    $.each(json.list,function(i,v){
        var lotteryUrl = createPeriodUrl(v.id);
        if (v.id == peid) {
          var item = '<li style="background:#ff500b"><a style="color:#ffffff" href="'+lotteryUrl+'" pnum="'+v.period_number+'" pid="'+v.id+'">第'+v.period_number+'期</a></li>'
        }else{
          var item = '<li><a href="'+lotteryUrl+'" pnum="'+v.period_number+'" pid="'+v.id+'">第'+v.period_number+'期</a></li>'
        }
        $("#scrollTool_wrap").append(item);
    })
    $("#btnGo").click(function(){
      var pnum = 0;
      var l = $(".input-wrapper input").val();
      if (!parseInt(l)) {
        return false;
      };
      $("#scrollTool_wrap li").each(function(){
        if (l == $(this).find('a').attr('pnum')) {
          pnum = $(this).find('a').attr('pid');
          window.location.href = '/lottery/'+pnum+'.html';
        };
      })
      if (pnum == 0) {
        $.getJsonp(apiBaseUrl + '/period/get-periodid',{'pnum':l,'pid':$(".hg-yun input[name=product_id]").val()},function(json){
          if (json.period_id > 0) {
            window.location.href = '/lottery/'+json.period_id+'.html';
          };
          return false;
        })
      }
    })
  };
}

function success_follow(json){
  if (json.code == 1) {
    if (json.f == 'follow') {
      $(".guanzhu").attr("href",apiBaseUrl+'/follow/cancel').text("取消关注");
    }else{
      $(".guanzhu").attr("href",apiBaseUrl+'/follow/follow').text("关注");
    }
  }else{
    if (json.logined == 0) {
      showLoginForm();
    };
  }
}

function isFollowed(followed){
    if (followed) {
      $('.guanzhu').attr('href',apiBaseUrl+'/follow/cancel').text('取消关注');
    }else{
      $('.guanzhu').attr('href',apiBaseUrl+'/follow/follow').text('关注');
    }
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


function createPageNew(page,total,totalPage,maxButtonCount){
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
    $('#canyunew').html(pageHtml);
}
function success_newBuyList(json){
  if (json.list.length > 0) {
    $(".phase_rl_list .tishi").remove();
    lasttime = json.list[0].buy_time;
    $.each(json.list,function(i, v) {
      var userFaceImgUrl = createUserFaceImgUrl(v.avatar,avatarSize[0]);
      var html = '<li>';
      html += '<picture><img src="'+userFaceImgUrl+'"></picture>';
      html += '<a href="'+userBaseUrl+'/'+v.home_id+'" target="_blank">'+v.username+'</a>';
      html += '<aside>'+v.buy_num+'人次</aside>';
      html += '</li>';
      $("#newbuy").prepend(html).find("li");
      if ($("#newbuy li").length > 12) {
        $("#newbuy").find("li").last().remove();
        $(".phase_rl_more").eq(0).find('a').show();
      }
    });
  };
}

function sliderPic() {
    var num = 0;
    var nLength = $('.swiper-slide').length;
    var ulLength = nLength * 520;
    $('.swiper-wrapper').css('width',ulLength + 'px');
    $('.s_rightBtn').click(function(e) {
        num++;
        if(num == nLength){
            num = 0;
        }
        var numZhi = num * -520;
        $('.swiper-wrapper').stop().animate({'left':''+numZhi+'px'},500);
    });
    $('.s_leftBtn').click(function(e) {
        num--;
        if(num == -1){
            num = nLength-1;
        }
        var numZhi = num * -520;
        $('.swiper-wrapper').stop().animate({'left':''+numZhi+'px'},500);
    });

    var timer = null;
    var fnTimer = function(){
        num++;
        if(num == nLength){
            num = 0;
        }
        var numZhi = num * -520;
        $('.swiper-wrapper').stop().animate({'left':''+numZhi+'px'},500);
    };
    timer = setInterval(fnTimer,3000);
    $('.swiper-container,.s_leftBtn,.s_rightBtn').hover(function(e) {
        clearInterval(timer);
    },function(){
        clearInterval(timer);
        timer = setInterval(fnTimer,2000);
    });
}
