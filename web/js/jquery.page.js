(function($) {
    var ms = {
        init:function(obj, args) {
            return (function() {
                ms.fillHtml(obj, args);
                ms.bindEvent(obj, args);
            })();
        },
        // 填充html
        fillHtml:function(obj, args) {
            return (function() {
                obj.empty();
                // 上一页
                obj.append('<a href="javascript:;" class="prev">上一页</a>');

                // 中间页码
                if (args.current != 1 && args.current >= 4 && args.pageCount != 4) {
                    obj.append('<a href="javascript:;" class="tcdNumber">'+1+'</a>');
                }

                if(args.current - 2 > 2 && args.current <= args.pageCount && args.pageCount > 5){
                    obj.append('<i>...</i>');
                }

                var start = args.current - 2, end = args.current + 2;
                if ((start > 1 && args.current < 4) || args.current == 1) {
                    end++;
                }
                if (args.current > args.pageCount - 4 && args.current >= args.pageCount) {
                    start--;
                }

                for (; start <= end; start++) {
                    if (start <= args.pageCount && start >= 1) {
                        if (start != args.current) {
                            obj.append('<a href="javascript:;" class="tcdNumber">'+ start +'</a>');
                        } else {
                            obj.append('<a href="javascript:;" class="act"><b></b>'+ start +'</a>');
                        }
                    }
                }

                if (args.current + 2 < args.pageCount - 1 && args.current >= 1 && args.pageCount > 5) {
                    obj.append('<i>...</i>');
                }
                if (args.current != args.pageCount && args.current < args.pageCount - 2  && args.pageCount != 4) {
                    obj.append('<a href="javascript:;" class="tcdNumber">' + args.pageCount + '</a>');
                }

                //下一页
                obj.append('<a href="javascript:;" class="next">下一页</a>');
            })();
        },
        //绑定事件
        bindEvent:function(obj,args) {
            return (function() {
                $('a.tcdNumber').click(function(){
                    var current = parseInt($(this).text());
                    window[args.gotoPage]((current - 1) * args.downPage + 1);
                });
                $('a.prev').click(function(){
                    var current = parseInt(obj.children("a.act").text());
                    if (current <= 1) return;
                    window[args.gotoPage]((current - 2) * args.downPage + 1);
                });
                $('a.next').click(function(){
                    var current = parseInt(obj.children("a.act").text());
                    if (current >= args.pageCount) return;
                    window[args.gotoPage](current * args.downPage + 1);
                });
            })();
        }
    }

    $.fn.createPage = function(options){
        var args = $.extend({
            pageCount : 10,
            current : 10,
            downPage: 10,
            gotoPage: 'gotoPage',
            backFn : function(){}
        },options);
        ms.init(this,args);
    }
})(jQuery);