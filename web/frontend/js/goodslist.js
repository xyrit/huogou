var cid = bid = page = 0;
cid = getHtmlUrlParam('1');
bid = getHtmlUrlParam('2');
page = getHtmlUrlParam('3') ? getHtmlUrlParam('3') : 1;

$(function(){
  if (getUrlParam('tag')) {
    var limit = 1;
  };
  data = {'cid':cid,"bid":bid,"page":page,"limit":limit};
  $.getContent(apiBaseUrl+'/product/list',data,'productlist');
  $.getContent(apiBaseUrl+'/product/catlist','','catlist');
  $.getContent(apiBaseUrl+'/product/brandlist','','brandlist');
  createFlagUrl();
})
function success_productlist(json){
  $("#divLoadingLine").hide();
  var html = '';
  $.each(json.list,function(i,v){
    var schedule = parseInt(v.sales_num/v.price*266);
    var extra_class = '';
    if (i == 3 || i == (json.list.length-1)) {
      extra_class = 'soon-list4';
    };
    html += '<div idx="'+parseInt(i+1)+'" class="soon-list-con '+extra_class+'" codeid="" goodsid="'+v.id+'">';
    html += '<div class="soon-list">';
    html += '<ul>';
    html += '<li class="g-soon-pic"><a href="/product/'+v.id+'.html" target="_blank" title="(第'+v.period_number+'云)'+v.name+'"><img name="goodsImg" src="'+createGoodsImgUrl(v.picture,photoSize[1],photoSize[1])+'"></a></li>';
    html += '<li class="soon-list-name"><a href="/product/'+v.id+'.html" target="_blank" title="(第'+v.period_number+'云)'+v.name+'">(第'+v.period_number+'云)'+v.name+'</a></li>';
    html += '<li class="gray6">价值：￥'+v.price+'.00</li>';
    html += '<li class="g-progress">';
    html += '<dl class="m-progress">';
    html += '<dt title="已完成'+changeTwoDecimal_f(v.sales_num/v.price)+'"><b style="width:'+schedule+'px"></b></dt>';
    html += '<dd><span class="orange fl"><em>'+v.sales_num+'</em>已参与</span><span class="gray6 fl"><em>'+v.price+'</em>总需人次</span><span class="blue fr"><em>'+parseInt(v.price-v.sales_num)+'</em>剩余</span></dd>';
    html += '</dl>';
    html += '</li>';
    html += '<li name="buyBox" limitbuy="0"><a href="javascript:;" title="立即伙购" class="u-now">立即伙购</a><a href="javascript:;" class="u-cart"><s></s></a></li>';
    html += '</ul>';
    html += '<div class="f-add-attention"></div>';
    html += '</div>';
    html += '</div>';
  })
  $("#ulGoodsList").append(html);
  createPage(json.totalCount,json.totalPage);
}

function createPage(total,totalpage){
  var html = '<p class="Fl">共<em class="orange">'+total+'</em>件商品<b>'+page+'</b>/'+totalpage+'</p>';
  if (totalpage > 1) {
    html += '<div class="u-list-btn fl">';
    if (page <= 1) {
      html += '<a href="javascript:;" class="u-btn-gray" title="上一页"><span class="f-tran f-tran-prev"><</span>上一页</a>';
    }else{
      html += '<a href="/list-'+cid+'-'+bid+'-'+parseInt(page-1)+'.html" class="" title="上一页"><span class="f-tran f-tran-prev"><</span>上一页</a>';
    }
    if (page >= totalpage) {
      html += '<a href="javascript:;" class="u-btn-gray" title="下一页">下一页<span class="f-tran f-tran-next">></span></a>';
    }else{
      html += '<a href="/list-'+cid+'-'+bid+'-'+parseInt(page+1)+'.html" class="" title="下一页">下一页<span class="f-tran f-tran-next">></span></a>';
    }
    html += '</div>';
  };
  $("#divTopPageInfo").html(html);
}

function success_catlist(json){
  var html = '';
  $.each(json,function(i,v){
    if (cid==v.id) {
      html += '<li class="current"><a href="/list-'+v.id+'-'+bid+'.html" cid="'+v.id+'">'+v.name+'</a></li>';
    }else{
      html += '<li class=""><a href="/list-'+v.id+'-'+bid+'.html" cid="'+v.id+'">'+v.name+'</a></li>';
    }
  })
  if (cid == 0) {
    $("#catlist li:first").addClass('current');
  };
  $("#ulBrandList li:first").find('a').attr("href","/list-"+cid+"-0.html");
  $("#catlist").append(html);
}

function success_brandlist(json){
  var html = '';
  $.each(json,function(i,v){
    var ename = v.alias ? '('+v.alias+')' : '';
    if (bid == v.id) {
      html += '<li class="current"><a href="/list-'+cid+'-'+v.id+'.html" title="'+v.name+ename+'">'+v.name+'<em class="arial">'+ename+'</em></a></li>';
    }else{
      html += '<li class=""><a href="/list-'+cid+'-'+v.id+'.html" title="'+v.name+ename+'">'+v.name+'<em class="arial">'+ename+'</em></a></li>';
    }
  })
  if (bid == 0) {
    $("#ulBrandList li:first").addClass('current');
  };
  $("#catlist li:first").find('a').attr("href","/list-0-"+bid+".html");
  $("#ulBrandList").append(html);
}

function createFlagUrl(){
  var url = window.location.href.split("?")[0];
  $(".f-list-sorts li a").removeClass("current");
  $.each($(".f-list-sorts li a"),function(){
    var rn = $(this).attr("href").split("?")[1];
    if (window.location.search.substring(1) == rn) {
      $(this).parent().addClass("current");
      $(this).attr("href",url+"?"+rn)
    };
  })
}