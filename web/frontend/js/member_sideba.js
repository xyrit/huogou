$(function(){
   /*左侧展开*/
    var _NavState = [true, false, false, false, false];
    
    var _CurObj = $("div.sidebar-nav").find('ul li.sid-cur');
    //第一项默认展开
    var _FirstObj = $("div.sidebar-nav").find('h3.sid-icon02 ');
    _FirstObj.find('s').remove();
    _FirstObj.addClass('sid-iconcur').next('ul').find('li').show();
    _NavState[0] = true;
    if (_CurObj.length > 0) {
        var H3Obj = _CurObj.parent().prev('h3');
        _CurObj.siblings().show();
        var _idx = parseInt(H3Obj.attr('class').substr(8, 2));
        _NavState[_idx-2] = true;
    }

    $("div.sidebar-nav").find("h3").each(function (i, v) {
        if (i > 0) {
            $(this).click(function (e) {
                var _This = $(this);
                var _HasClild = _This.attr("hasChild") == "1";
                var _SObj = _This.find("s");
                if (_HasClild) {
                    var _State = _NavState[i];

                    /* 一级栏目更改样式 */
                    if (_State) {
                        _This.addClass("sid-iconcur");
                        _SObj.attr("title", "展开");
                    }
                    else {
                        _This.removeClass("sid-iconcur");
                        _SObj.attr("title", "收起");
                    }

                    /* 二级栏目显示或隐藏 */
                    _This.next("ul").children().each(function () {
                        if (_State) {
                            $(this).hide(50);
                        }
                        else {
                            $(this).show(50);
                        }
                    });
                    _NavState[i] = !_State;
                }
            });
        }
    });
    
});
