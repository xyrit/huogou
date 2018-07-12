/**
 * Created by jun on 15/12/8.
 * 简单的模板填充
 * 模板规则:t
 *                  变量规则 {var}
 *                  函数规则 {func($var)}
 * 元素新建属性
 *                  ajax=src,onajax=ajax-after-func(json),onajax_before=ajax-before-func ,  data-src=img-src(img.src=/img/e.gif)
 *                  example: <ul id="ul_list" ajax="/share/share-list?pid={{productId}}" onajax=';$("#divLoading").hide();'  onajax_before='$("#divLoading").show();' >
 * 扩展功能:
 *                  下一页功能:  $('#ul_list')[0].getnext(); 
 */

(function(namespace){  
    var pageinfo = {page:1,perpage:10};
    function replacetpl(tpl,data)
    {
        var a = tpl.replace(/{/g,"#n$&").replace(/}/g,"$&#n").split("#n");
        for(var i=0;i<a.length;i++)
        {
            var s = a[i];    if(s[0] != "{") continue;
            
            var attr = s.substr(1,s.length-2).replace(/^\s+|\s+$/,'');
            if(attr.match(/^\w+$/))
                   attr= data[attr];
             else 
             {
                  var m = attr .match(/\$\w+/gm);
                  for(var j=0;j<m.length;j++)
                  {
                      attr = attr.replace(m[j],"data."+m[j].substr(1));
                  }
                  attr= eval(attr);
             }
           a[i] = attr;
        }
        
        return a.join("");
    }
    
    function getlist(e){
        $.getJsonp(apiBaseUrl+e.src,pageinfo,function(json){
                var strHtml = '';
                $.each(json.list, function (i, v) {
                    strHtml += replacetpl(e.tpl,v);
                });
                
                (pageinfo.page++ ==1)  ? $(e).html(strHtml) :  $(e).append(strHtml);
                e.onajax(json);
        });
    }
    
    function init(e)
    {
        var ajaxBefore = function(){
                var before = $(e).attr("onajax_before");
                if(before)  before = (before.match(/^\w+$/)) ? before() : eval(before); 
                if(before === false) return;
                
                if($(e).next().is('.loading'))  $(e).next().show();
                $(e).html('').show();
                $(e).after(
                        '<div id="divLoading" class="loading clearfix g-acc-bg" style="display: none;"><b></b>正在加载 </div>'
                        +'<div class="noRecords colorbbb clearfix" style="display:none;" id="nohave"><s></s>最近三个月无记录 <div class="z-use">请下载“滴滴夺宝”APP查看更多</div> </div>'
                        +'<div style="display: none;" id="btnLoadMore" class="load_more"><a title="加载更多" href="javascript:void(0);" >点击加载更多</a><b></b> </div>'
                    );
            $("#btnLoadMore").click(function(){e.getnext(); });
         }
        
         var callback =  function(json){
             var onajax = $(e).attr("onajax");
             if(onajax)  onajax= (onajax.match(/^\w+$/)) ? onajax(json) : eval(onajax); 
             if(onajax ===false) return;
             
             if($(e).next().is('.loading'))  $(e).next().hide(); 
             $(e).find("img").each(function(){if($(this).attr("data-src")) this.src= $(this).attr("data-src"); });
             if(json.totalCount<1) $("#nohave").show();
             (!json.totalPage  || pageinfo.page >=json.totalPage) ? $("#btnLoadMore").hide() : $("#btnLoadMore").show() ;
         };
        
        $.extend(e,{
                tpl : $(e).html(),
                src : $(e).attr("ajax"),
                page : pageinfo,
               getnext : function(){ getlist(e); },
               onajax: callback
        });
        
        ajaxBefore.apply(e);
    }
    
    
    namespace.jsonpInit = function(){
        $(this).each(function(i,item){
                init(item);
                getlist(item);
        });
    }
    
    namespace.parsetpl = function(data){
        $(this).each(function(i,item){
              init(item);
              item.html(replacetpl(item.tpl,data));
        });
    }
})($.fn);

$(function() {
    $("[ajax]").jsonpInit();
});
