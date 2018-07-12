/**
 * Created by jun on 15/12/8.
 * 简单的模板填充
 * 模板规则:
 *                  变量规则 {var}
 *                  函数规则 {func($var)}
 * 元素新建属性
 *                  ajax=src,onajax=ajax-after-func(json),on_ajax=ajax-before-func ,  data-src=img-src(img.src=/img/e.gif)
 *                  example: <ul id="ul_list" ajax="/share/share-list?pid={{productId}}" onajax='$("#share_num").html(json.totalCount).hide();$("#user_num").html(json.totalCount);$("#divLoading").hide();'  on_ajax='$("#divLoading").show();' >
 * 扩展功能:
 *                  下一页功能:  $('#ul_list')[0].getnext(); 
 */

(function(namespace){
    String.prototype.replaceAll = function(s1, s2) {      
        return this.replace(new RegExp(s1, "gm"), s2); //g全局     
    }  

    String.prototype.replace_callback = function(s1, callback) { 
        var rt = this.toString();
        var m = rt.match($.type(s1) == "regexp" ? s1 : new RegExp(s1, "gm"));
        $(m).each(function(i,item){
            var newitem = callback(item);
            rt = rt.replace(item,newitem);
        });

        return rt;
    }
    
    var pageinfo = {page:1,perpage:20};
    function replacetpl(tpl,data)
    {
        var a = tpl.replace(/{/g,"#n$&").replace(/}/g,"$&#n").split("#n");
        for(var i=0;i<a.length;i++)
        {
            var s = a[i];    if(s[0] != "{") continue;
            
            var attr = s.substr(1,s.length-2).trim();
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
        
//        return tpl.replace_callback("{[^}]+}",function(s){
//              var attr = s.substr(1,s.length-2).trim();
//              var m = [];
//              if(attr.match(/^\w+$/))
//                    return data[attr];
//              else 
//              {
//                   var m = attr .match(/\$\w+/gm);
//                   for(var i=0;i<m.length;i++)
//                   {
//                       attr = attr.replace(m[i],"data."+m[i].substr(1));
//                   }
//                   return eval(attr);
//              }        
//        });
    }
    
    function getlist(e){
        $.getJsonp(apiBaseUrl+e.src,pageinfo,function(json){
                var strHtml = '';
                $.each(json.list, function (i, v) {
                    strHtml += replacetpl(e.tpl,v);
                });
                
                (pageinfo.page++ ==1)  ? $(e).html(strHtml) :  $(e).append(strHtml);
                e.onajax(json);
                $(e).find("img").each(function(){if($(this).attr("data-src")) this.src= $(this).attr("data-src"); });
        });
    }
    
    function init(e)
    {
        e.tpl = $(e).html();
        e.src = $(e).attr("ajax");
        var code = e.onajax = $(e).attr("onajax");
        if(! e.onajax ) e.onajax = function(){};
        else if(! e.onajax.match(/^\w+$/)) e.onajax = function(json){eval(code); }
        e.page = pageinfo;
        e.getnext= function(){ getlist(e); }
        $(e).html('').show();
    }
    
    
    namespace.jsonpInit = function(){
        $(this).each(function(i,item){
                init(item);
                if($(item).attr("on_ajax"))
                {
                    eval($(item).attr("on_ajax"));
                }
                getlist(item);
        });
    }
})($.fn);

$(function() {
    $("[ajax]").jsonpInit();
    
    //增加li跳转功能
    $("#ul_list").on("click","li",function(event){
            if($(event.target).is("a")) return;
            location.href = $(this).find("dt a").attr("href");
    })
});


var t = function() {
    //分享商品
    $('#ul_list').on('click', '.z-set-wrap', function (e) {
        stopBubble(e);
        
        wxShareFun({
            shareTitle: "晒单分享",
            shareImg: $(this).attr("postpic"),
            shareLink: $(this).attr("postlink"),
            shareDesc: $(this).attr('postcontent'),
            shareMoney: false,
            showMask: true
        });
        return false
    });
}
Base.getScript(skinBaseUrl + '/weixin/js/wxshare.js', t);