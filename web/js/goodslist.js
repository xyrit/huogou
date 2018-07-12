var cid = bid = page = 0;
cid = getHtmlUrlParam('1');
bid = getHtmlUrlParam('2');
page = getHtmlUrlParam('3') ? getHtmlUrlParam('3') : 1;
var orderFlag = getUrlParam('r') ? getUrlParam('r'): 10;
var limit;
var keyWords = decodeURI(getUrlParam('q') ? getUrlParam('q') : '');

$(function(){
  sidebarCart(false);
  if (getUrlParam('tag')) {
    limit = 1;
    $(".present a:last").text("限购");
  };
  // if (orderFlag == '20') {
  //   $(".present a:last").text("人气");
  // }else if (orderFlag == '30') {
  //   $(".present a:last").text("剩余人次");
  // }else if (orderFlag == '40') {
  //   $(".present a:last").text("最新");
  // }else if (orderFlag == '50') {
  //   $(".present a:last").text("价值");
  // }else if (!limit) {
  //   $(".present a:last").text("即将揭晓");
  // };

  data = {'cid':cid,"bid":bid,"page":page,"limit":limit,'orderFlag':orderFlag,'token':token,'keywords':keyWords};

  $.getContent(apiBaseUrl+'/product/list',data,'productList');
  // getData(cid,bid,page);
  $.getJsonp(apiBaseUrl+'/product/catlist',{"catid":cid},success_catList);

  $("div.option article a").each(function(){
    $(this).attr("href",$(this).attr("href").replace(/list/,'list-'+cid+'-'+bid));
  })

})
function success_productList(json){
  $("#divLoadingLine").hide();
  var html = '';
  if(json.totalCount == "0"){
    html += '<div class="notHaveB" style="display: block;">';
    html += '<span class="notHaveB_icon"></span>';
    html += '<p class="notHaveB_txt">暂无记录！</p>';
    html += '</div>';
    $("ul.quota_list").append(html);
    return;
  }
  $.each(json.list,function(i,v){
    var followed = v.followed;
    var productId = v.product_id;
    var productUrl = createGoodsUrl(productId);
    
    if (v.period_number) {
      html += '<li>';
      html += '<div class="limitDiv">';
      html += '<picture><a href="'+productUrl+'" target="_blank"><img src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'" alt=""></a></picture>';
      html += '<div>';
      html += '<h3><a href="'+productUrl+'" target="_blank" title="'+v.name+'">'+v.name+'</a></h3>';
      html += '<aside>总需：'+v.price+'人次</aside>';
      html += '<p><i style="width:'+changeTwoDecimal_f(v.sales_num/v.price)*100+'%"></i></p>';
      html += '<summary>';
      html += '<span class="fl"><i>'+v.sales_num+'</i>已参与</span>';
      html += '<span class="rl">剩余<i>'+v.left_num+'</i></span>';
      html += '</summary>';
      html += '</div>';
      html += '<article>';
      html += '<a class="buy" href="'+productUrl+'" target="_blank">立即伙购</a><a class="car" href="javascript:;" periodid="'+v.period_id+'" buyUnit="'+ v.buy_unit+'"></a>';
      html += '</article>';
      html += '</div>';
    }else{
      html += '<li class="g_conclude">';
      html += '<div class="limitDiv">';
      html += '<picture style="margin-bottom: 20px;"><a href="'+productUrl+'" target="_blank"><img src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'" alt=""></a></picture>';
      html += '<h3><a href="'+productUrl+'" target="_blank" title="'+v.name+'">'+v.name+'</a></h3>';
      html += '<aside>总需：'+v.price+'人次</aside>';
      html += '<article>';
      html += '<a class="buy" href="'+productUrl+'">查看详情</a>';
      html += '</article>';
      html += '<div class="conclude"><span class="conclude_icon">已结束</span></div>';
      html += '</div>';
      html += '</li>';
    }
    
    if (followed==1) {
      html += '<div class="h-add-attention h-add-click" data-type="cancel" productId="'+productId+'"><span>已关注</span><a href="javascript:;" class="ng-box-bg transparent-png"></a></div>';
    } else {
      html += '<div class="h-add-attention" data-type="follow" productId="'+productId+'"><span>关注</span><a href="javascript:;" class="ng-box-bg transparent-png"></a></div>';
    }
    if (v.limit_num > 0) {
      html += '<div class="f-callout"><span class="xgou">限购</span></div>';
    } else if (v.buy_unit==10) {
      html += '<div class="f-callout"><span class="sbei">十元</span></div>';
    }
    html += '</li>';
  })
  $(".whole_title span i").text(json.totalCount);
  $("ul.quota_list").append(html);
  createPage(json.totalCount,json.totalPage,5);

  $(".car").on('click',function(){
    var img = $(this).parents('li').find('img').attr('src');
    addProduct(img,$(this));
    var periodid = $(this).attr('periodid');
    var buyUnit = $(this).attr('buyUnit');
    $.getContent(apiBaseUrl+'/cart/add',{'periodid':periodid,'num':1*buyUnit,'token':token},'cartadd');
  });
}

function createPage(total,totalPage,maxButtonCount){
  var page1 = '';
  var query = window.location.search;
  if (totalPage == 0) {
    page = 0;
  };
  page1 += '<span>共'+total+'件商品'+page+'/'+totalPage+'</span>';
  if (totalPage > 1) {
    if (page <= 1) {
      page1 += '<a href="javascript:;" class="disabled">上一页</a>';
    }else{
      page1 += '<a href="/list-'+cid+'-'+bid+'-'+(parseInt(page)-1)+'.html'+query+'">上一页</a>';
    }
    if (page >= totalPage) {
      page1 += '<a href="javascript:;" class="disabled">下一页</a>';
    }else{
      page1 += '<a href="/list-'+cid+'-'+bid+'-'+(parseInt(page)+1)+'.html'+query+'">下一页</a>';
    }
  };
  $("div.option summary").html(page1);
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
        var prevPageUrl = '/list-'+cid+'-'+bid+'-'+(parseInt(page)-1)+'.html'+query;
        var prevButton = '<a href="'+prevPageUrl+'" class="prev">上一页</a>';
    }

    if (page>=totalPage) {
        var nextButton = '<a href="javascript:;" title="下一页" class="next disabled">下一页</a>';
    } else {
       var nextPageUrl = '/list-'+cid+'-'+bid+'-'+(parseInt(page)+1)+'.html'+query;
       var nextButton = '<a href="'+nextPageUrl+'" title="下一页" class="next">下一页</a>';
    }

    var beginPage = Math.max(1,page - parseInt(maxButtonCount/2));
    var endPage = beginPage + maxButtonCount - 1;
    if (endPage > totalPage) {
        endPage = totalPage;
        beginPage = Math.max(1,endPage - maxButtonCount + 1);
    }

    var firstPageUrl = '/list-'+cid+'-'+bid+'-1.html'+query;
    var lastPageUrl = '/list-'+cid+'-'+bid+'-'+totalPage+'.html'+query;
    var firstButton = '';
    var lastButton = '';
    if (beginPage > 1) {
        firstButton += '<a href="'+firstPageUrl+'"><b></b>1</a>';
        firstButton += '<i>...</i>';
    }
    if (endPage<totalPage) {
        lastButton += '<i>...</i>';
        lastButton += '<a href="'+lastPageUrl+'"><b></b>'+totalPage+'</a>';
    }

    var buttons = '';
    for (var i=beginPage;i<=endPage;i++) {
        var lotteryListUrl = '/list-'+cid+'-'+bid+'-'+i+'.html'+query;
        var curClass = '';
        if (i==page) {
            curClass = 'class="act"';
        }
        buttons += '<a '+curClass+' href="'+lotteryListUrl+'"><b></b>'+i+'</a>';
    }

    var pageHtml = '';
    pageHtml += prevButton + firstButton + buttons + lastButton + nextButton ;
    $('.pagination').html(pageHtml);
}

function success_catList(json){
  var html = '';
  var s = ['a','b','c','d','f','h','k','m','i','j','g','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
  $.each(json.list,function(i,v){
    if (v.id == json.currCat.cid) {
      html += '<li class="whole_list_'+s[i]+' act" cid="'+v.id+'" url="/list-'+v.id+'-0-1.html">';
    }else{
      html += '<li class="whole_list_'+s[i]+'" cid="'+v.id+'" url="/list-'+v.id+'-0-1.html">';
    }
    html += '<b></b>';
    html += '<p>'+v.name+'</p>';
    html += '</li>';
    if (v.id == json.currCat.cid) {
      $(".whole").append('<article class="whole_list_box" cid="'+v.id+'" style="display:block"></article>');
    }else{
      $(".whole").append('<article class="whole_list_box" cid="'+v.id+'"></article>');
    }
  })
  $(".whole_list").append(html);
  if (!cid) {
    $(".present i:last").remove();
  }else{
    $(".present").append(json.currCat.name);
  }

  $.getJsonp(apiBaseUrl+'/product/brandlist','',success_brandList);

  $('.whole_list').find('li').on('click', function(){
    window.location.href = $(this).attr('url');
  })
}

function success_brandList(json){
  var catList = $(".whole_list li");
  catList.each(function(i, v) {
    var listCid = $(this).attr('cid');
    var catId = cid ? cid : listCid;
    if (typeof json.list[listCid] != 'undefined') {
      $.each(json.list[listCid],function(j,k){
        var ename = k.alias ? '('+k.alias+')' : ''; 
        if (j) {
          if (j == bid) {
            $(".whole article").eq(i).append('<a href="/list-'+cid+'-'+j+'-'+page+'.html" bid="'+j+'" class="act">'+k.name+ename+'</a>');
          }else{
            $(".whole article").eq(i).append('<a href="/list-'+cid+'-'+j+'-'+page+'.html" bid="'+j+'">'+k.name+ename+'</a>');  
          }
        }
      })
    };
  })
}




$(document).ready(function(){
  //鼠标移上出现红色外边框
  function pHover(par){
    $(par).on('mouseover', 'li', function(){
      $(this).addClass('p_hover').siblings().removeClass('p_hover');
    })
    $(par).on('mouseout', 'li', function(){
      $(this).removeClass('p_hover');
    })
  }
  pHover(".quota_list");


  $('.quota_list').on('mouseover','.h-add-attention',function(){
    $(this).addClass('h-add-hover');
  }).on('mouseout', '.h-add-attention', function(){
    $(this).removeClass('h-add-hover');
  })


  //关注商品
  $('.quota_list').on('click', '.h-add-attention', function() {
    var ths = $(this);
    var dataType = ths.attr('data-type');
    var productId = ths.attr('productId');
    var url = apiBaseUrl+'/follow/'+dataType;
    $.getJsonp(url,{"token":token,"pid":productId},function(json) {
      //console.log(json)
      if (json.code == 1) {
        if (json.f == 'follow') {
          ths.addClass('h-add-click');
          ths.attr("data-type",'cancel');
          ths.children('span').text('已关注');
        }else{
          ths.addClass('h-add-hover').removeClass('h-add-click');
          ths.attr("data-type", 'follow');
          ths.children('span').text('已取消');
          setTimeout(function() {
            ths.children('span').text('关注');
          }, 1000);
        }
      }else{
        if (json.logined == 0) {
          showLoginForm();
        };
      }
    });
    return false;
  });

});