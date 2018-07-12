/**
 * 说明：
 * 1、占用全局变量（两个函数）
 *      window.spImgAsyncReq 异步发送请求
 *      window.spClickLog 打点调用函数
 * 2、占用class名称
 *      sp_click_log
 *      与sp_click_log配合的自定义属性data-sp-log
 * 3、发送链接
 *      示例：http://hostname/empty.gif?sid=" + SID + param+"&uuid="+uuid;
 * 4、没有try catch处理
 *      TODO try catch | window.onerror 处理
 */
String.prototype.trim = function() {
    return this.replace(/^\s\s*/, "").replace(/\s\s*$/, "")
}; (function() {

    window.onerror = function() {
        //TODO 页面JS报错
        //alert("window onerror");
    };

    /**
     * 全局变量
     */
    var SID = "-"; //sid默认值"-"，判断非空
    var PAGETYPE = "";

    //判断有sid，则进行赋值，如果没有____trace4sp 或者没有sid属性，则自动赋值
    if (("undefined" != typeof ____trace4sp) && ("sid" in ____trace4sp) && (____trace4sp["sid"] != "")) {
        SID = ____trace4sp["sid"];
    }
    if (("undefined" != typeof ____trace4sp) && ("pagetype" in ____trace4sp) && (____trace4sp["pagetype"] != "")) {
        PAGETYPE = ____trace4sp["pagetype"];
    }

    /**
     * 判断是否存在某个class
     * @param ele
     * @param cls
     * @returns {boolean}
     */
    function hasClass(ele, cls) {
        var obj_class = ele.className,
        //获取 class 内容.
            obj_class_lst = obj_class.split(/\s+/); //通过split空字符将cls转换成数组.
        var x = 0;
        for (x in obj_class_lst) {
            if (obj_class_lst[x] == cls) { //循环数组, 判断是否包含cls
                return true;
            }
        }
        return false;
    }

    function addPSID(eleparam) {
        var ele = eleparam;
        isadd = (eleparam.tagName == "a" || eleparam.tagName == "A") && (eleparam.getAttribute("href") != null);
        myparent = eleparam.parentNode;
        if (isadd == false && myparent) {
            isadd = (myparent.tagName == "a" || myparent.tagName == "A") && (myparent.getAttribute("href") != null);
            ele = myparent;

            grandfather = myparent.parentNode;
            if (isadd == false && grandfather) {
                isadd = (grandfather.tagName == "a" || grandfather.tagName == "A") && (grandfather.getAttribute("href") != null);
                ele = grandfather;
            }

        }
        if (isadd) {
            href = ele.getAttribute("href");
            if (href.indexOf("#") == 0 || href.indexOf("javascript") == 0 || href.indexOf("tel") == 0 || href.indexOf("mail") == 0) {
                return;
            } else if (href.indexOf("&psid=") == -1 && href.indexOf("?psid=") == -1) {
                if (("undefined" != typeof ____trace4sp) && ("sid" in ____trace4sp) && (____trace4sp["sid"] != "")) {
                    SID = ____trace4sp["sid"]; //再次赋值
                }
                href = href.trim() + ( - 1 == href.indexOf("?") ? "?": "&") + "psid=" + SID;
                ele.setAttribute("href", href);
            }
        }
    }

    /**
     * 工具方法 - 跨浏览器操作事件
     */
    var EventUtil = {
        // 添加事件
        addHandler: function(element, type, handler) {
            if (element.addEventListener) {
                element.addEventListener(type, handler, false);
            } else if (element.attachEvent) {
                element.attachEvent('on' + type, handler);
            } else {
                element['on' + type] = handler;
            }
        },
        // 移除事件
        removeHandler: function(element, type, handler) {
            if (element.removeEventListener) {
                element.removeEventListener(type, handler, false);
            } else if (element.detachEvent) {
                element.detachEvent('on' + type, handler);
            } else {
                element['on' + type] = null;
            }
        },
        // 取得事件对象
        getEvent: function(event) {
            return event ? event: window.event;
        },
        // 取得事件目标
        getTarget: function(event) {
            return event.target || event.srcElement;
        },
        // 阻止默认行为
        preventDefault: function(event) {
            if (event.preventDefault) {
                event.preventDefault();
            } else {
                event.returnValue = false;
            }
        },
        // 阻止事件冒泡
        stopPropagation: function(event) {
            if (event.stopPropagation) {
                event.stopPropagation();
            } else {
                event.cancelBubble = true;
            }
        }
    };

    /**
     * Image 发送异步请求
     */
    //统计事件的UUID
    var unique = (function() {
        var time = (new Date()).getTime() + '-',
            i = 0;
        return function() {
            return time + (i++);
        }
    })();

    /**
     * 利用图片发送异步请求
     * @param url
     */
    function sendImgReq(url) {
        var data = window['spImgAsyncReq'] || (window['spImgAsyncReq'] = {});
        var img = new Image();
        var uuid = unique();
        img.onload = img.onerror = function() { //销毁一些对象
            img.onload = img.onerror = null;
            img = null;
            delete data[uuid];
        };
        img.src = url + '&uuid=' + uuid;
    }

    /**
     * 打点发送函数
     */
    function handler(param) {
        //TODO 对接接口
        if (("undefined" != typeof ____trace4sp) && ("sid" in ____trace4sp) && (____trace4sp["sid"] != "")) {
            SID = ____trace4sp["sid"]; //再次赋值
        }
        var url = document.location.protocol + "//sptrack.58supin.com/empty.gif?sid=" + SID + "&" + param;
        sendImgReq(url);
    }

    //打点第一种方式 标签绑定 click事件
    EventUtil.addHandler(document, "click",
        function(event) {
            var node = EventUtil.getTarget(event);
            //addPSID(node);
            if (hasClass(node, "sp_click_log")) {
                var param = node.getAttribute("data-sp-log") + "&eventType=click";
                handler(param);
            }
        });

    //打点第二种方式：函数调用 click
    window.spClickLog = function(param) {
        param += "&eventType=click";
        handler(param);
    };

    //打点第二种方式：函数调用 hover
    window.spHoverLog = function(param) {
        param += "&eventType=mouseenter";
        handler(param);
    }

    handler("source=" + PAGETYPE);

})();