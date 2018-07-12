var baseHost = getHost();
var apiBaseUrl = 'http://api.'+ baseHost;
var photoSize = ['38','200','400'];
var avatarSize = ['80','160'];
jQuery.extend({
	getContent: function(url,data,callback){
		$.ajax({
	       async:false,
	       url: url,
	       type: "GET",
	       dataType: 'jsonp',
	       jsonp: 'callback',
	       data: data,
	       jsonpCallback:"success_"+callback,
	       success: function (json) {
	          // showcontent(json);
	       }
	    });
	},
    changeByNum: function(id,max,surplus,limit,chance){
        $("#"+id+" .add").click(function(){
            var num = parseInt($("#"+id+" input").val())+1;
            $("#"+id+" .mius").removeClass('cur');
            surplus = limit > 0 ? limit : surplus;
            if (num > surplus) {
                return false;
            }else {
                if (num == surplus) {
                    $(this).addClass('cur');
                };
                $("#"+id+" input").val(num);
            }
            if (chance) {
                var chanceHtml = '<span class="txt">获得几率'+changeTwoDecimal_f(num/max*100)+'%<i></i></span>';
                $("."+chance).html(chanceHtml).show();
            };
        })
        $("#"+id+" .mius").click(function(){
            var num = parseInt($("#"+id+" input").val())-1;
            $("#"+id+" .add").removeClass('cur');
            if (num == 0) {
                return false;
            }else{
                if (num == 1) {
                    $(this).addClass('cur');
                }
                $("#"+id+" input").val(num);
            }
            if (chance) {
                var chanceHtml = '<span class="txt">获得几率'+changeTwoDecimal_f(num/max*100)+'%<i></i></span>';
                $("."+chance).html(chanceHtml).show();
            };
        })
        $("#"+id+" input").on('input',function(e){
            var num = parseInt($(this).val());
            surplus = limit > 0 ? limit : surplus;
            if (isNaN(num)) {
                return false;
            }else{
                if (num === 0) {
                    $(this).val(1);
                    $("#"+id+" .mius").addClass('cur');
                    $("#"+id+" .add").removeClass('cur');
                }else if (num > surplus) {
                    num = surplus;
                    $("#"+id+" .add").addClass('cur');
                }
                $(this).val(num);
                
                if (chance) {
                    var chanceHtml = '<span class="txt">获得几率'+changeTwoDecimal_f(num/max*100)+'%<i></i></span>';
                    $("."+chance).html(chanceHtml).show();
                };
            }
        })
        $("#"+id+" input").change(function(){
            var num = $(this).val();
            if (num == 0) {
                $(this).val(1);
            };
        })
    }
});

function changeTwoDecimal_f(x) {
    var f_x = parseFloat(x);
    if (isNaN(f_x)) {
        alert('function:changeTwoDecimal->parameter error');
        return false;
    }
    var f_x = Math.round(x * 100) / 100;
    var s_x = f_x.toString();
    var pos_decimal = s_x.indexOf('.');
    if (pos_decimal < 0) {
        pos_decimal = s_x.length;
        s_x += '.';
    }
    while (s_x.length <= pos_decimal + 2) {
        s_x += '0';
    }
    return s_x;
}

function getHost(url) {
    var host = "null";
    if (typeof url == "undefined"
        || null == url)
        url = window.location.href;
    var regex = /.*\:\/\/([^\/|:]*).*/;
    var match = url.match(regex);
    if (typeof match != "undefined"
        && null != match) {
        host = match[1];
    }
    if (typeof host != "undefined"
        && null != host) {
        var strAry = host.split(".");
        if (strAry.length > 1) {
            host = strAry[strAry.length - 2] + "." + strAry[strAry.length - 1];
        }
    }
    return host;
}


function createGoodsImgUrl(name, width, height) {
    return 'http://www.'+baseHost+'/pic-'+width+'-'+height+'/'+name;
}

function createGoodsInfoImgUrl(name) {
    return 'http://www.'+baseHost+'/goodsinfo/'+name;
}

function createUserFaceImgUrl(width, uid) {
    return 'http://www.'+baseHost+'/userface/'+width+'/'+uid;
}

function createShareImgUrl(name, size) {
    return 'http://www.'+baseHost+'/userpost/'+size+'/'+name;
}

function createGoodsUrl(productId) {
    return 'http://www.'+baseHost+'/product/'+productId+'.html';
}

function createPeriodUrl(periodId) {
    return 'http://www.'+baseHost+'/lottery/'+periodId+'.html';
}

function createPeriodListUrl(catId, page) {
    if(catId) {
        if (page>1) {
            return 'http://www.'+baseHost+'/lottery/i'+catId+'m'+page+'.html';
        }
        return 'http://www.'+baseHost+'/lottery/i'+catId+'.html';
    } else {
        if (page>1) {
            return 'http://www.'+baseHost+'/lottery/m'+page+'.html';
        }
        return 'http://www.'+baseHost+'/lottery/m1.html';
    }
}

function createUserCenterUrl(userHomeId) {
    return 'http://u.'+baseHost+'/'+userHomeId;
}

function createShareDetailUrl(shareTopicId) {
    return 'http://share.'+baseHost+'/detail-'+shareTopicId+'.html';
}

function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); 
    var r = window.location.search.substr(1).match(reg);  
    if (r != null) return unescape(r[2]); return null;
}

function getHtmlUrlParam(j){
    var href = window.location.href;
    var s = "list";
    for (var i = href.split("-").length - 1; i > 0; i--) {
        s += '-([0-9]*)';
    };
    s += ".html";
    var reg = new RegExp(s); 
    var r = href.match(reg);
    if (r != null) {
        if (r[j]) {
            return r[j];
        }else{
            return 0;
        }
     };
    return 0;
}