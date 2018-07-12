$(function(){
        $("#dlList").after(
                        '<div id="divLoading" class="loading clearfix g-acc-bg" style="display: none;"><b></b>正在加载 </div>'
                        +'<div class="noRecords colorbbb clearfix" style="display:none;" id="nohave"><s></s>最近三个月无记录 <div class="z-use">请下载“滴滴夺宝”APP查看更多</div> </div>'
                        +'<div style="display: none;" id="btnLoadMore" class="load_more"><a title="加载更多" href="javascript:void(0);" >点击加载更多</a><b></b> </div>'
                        //+'<div class="g-suggest clearfix"  id="">请下载“滴滴夺宝”APP查看更多</div>'
         );
        $("#btnLoadMore").click(loadlist);
        
        var page =1;
        function loadlist()
        {
            $("#divLoading").show();
            $.getJsonp(apiBaseUrl+'/record/points-list?status=0&region=1',{page:page++},function(json) {
                $("#divLoading").hide();
                var shtml = "";
                $(json.list).each(function(i,item){
                    var point =  item.point>0 ?  '<em class="green" >+'+item.point+'</em>':   '<em class="orange" >'+item.point+'</em>';
                    shtml += '<dd class="colorbbb">'
                        +'<span class="gray6">'+item.desc+'</span>'+item.created_at+
                        point +'</dd>';
                 }); 
                $("#dlList").append(shtml);
                if(json.totalCount ==0) 
                {
                        $("#dlList").hide();
                        $("#nohave").show();
                }
                (!json.totalPage  || page >=json.totalPage) ? $("#btnLoadMore").hide() : $("#btnLoadMore").show() ;
            });
        }
        
        loadlist();
})