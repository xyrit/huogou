$(function(){
	$("input[type='text']").not(".no").each(function(){
		$(this).placeholder();
	});
	$(".tabs").each(function(){
		$(this).tabs();
	});
	resize();
	$(window).resize(function(event) {
		resize();
	});










});

/*main*/
//

/*call*/
//
function resize(){
	var ht=$(window).height();
	$(".flht").height(ht);
}
$.fn.placeholder = function () {
    var $obj = this;
    var v = $(this).val();
    $obj.focus(function (event) {
        if ($obj.val() == v) {
            $obj.val("");
        }
    });
    $obj.blur(function (event) {
        if ($obj.val() == "") {
            $obj.val(v);
        }
    });
}
$.fn.tabs = function () {
    var $obj = this;
    var $tabs = $obj.find(".ts >.t");
    var $cnts = $obj.find(".cs >.c");

    $tabs.click(function (event) {
        var i = $tabs.index(this);
        $cnts.hide();
        $cnts.eq(i).show();

        $tabs.removeClass('on');
        $(this).addClass('on');

        return false;
    });
    $tabs.first().click();
}

var stopBubble = function (a) {
    if (a && a.stopPropagation) {
        a.stopPropagation()
    } else {
        window.event.cancelBubble = true
    }
}

function getCookie(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}
function setCookie(name, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    var host=getDomain(document.domain);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";domain=" + host;
}
function getDomain (str) {
    if (!str) return '';
    if (str.indexOf('://') != -1) str = str.substr(str.indexOf('://') + 3);
    var topLevel = ['com', 'net', 'org', 'gov', 'edu', 'mil', 'biz', 'name', 'info', 'mobi', 'pro', 'travel', 'museum', 'int', 'areo', 'post', 'rec'];
    var domains = str.split('.');
    if (domains.length <= 1) return str;
    if (!isNaN(domains[domains.length - 1])) return str;
    var i = 0;
    while (i < topLevel.length && topLevel[i] != domains[domains.length - 1]) i++;
    if (i != topLevel.length) return domains[domains.length - 2] + '.' + domains[domains.length - 1];
    else {
        i = 0;
        while (i < topLevel.length && topLevel[i] != domains[domains.length - 2]) i++;
        if (i == topLevel.length) return domains[domains.length - 2] + '.' + domains[domains.length - 1];
        else return domains[domains.length - 3] + '.' + domains[domains.length - 2] + '.' + domains[domains.length - 1];
    }
};