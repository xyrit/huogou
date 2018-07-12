function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);  //获取url中"?"符后的字符串并正则匹配
    var context = "";
    if (r != null)
        context = r[2];
    reg = null;
    r = null;
    return context == null || context == "" || context == "undefined" ? "" : context;
}

var ref = document.referrer,
    MUSER = GetQueryString("f");

if(MUSER !='') {
    if (MUSER == 'momo') {
        document.write('<script src="https://s95.cnzz.com/z_stat.php?id=1260060874&web_id=1260060874" language="JavaScript"></script>')
    } else if (MUSER == 'weibo') {
        document.write('<script src="https://s4.cnzz.com/z_stat.php?id=1260060924&web_id=1260060924" language="JavaScript"></script>')
    } else if (MUSER == 'dd_wx') {
        document.write('<script src="https://s4.cnzz.com/z_stat.php?id=1260061244&web_id=1260061244" language="JavaScript"></script>')
    } else if (MUSER == 'tieba') {
        document.write('<script src="https://s11.cnzz.com/z_stat.php?id=1260061277&web_id=1260061277" language="JavaScript"></script>')
    } else if (MUSER == 'duanxin') {
        document.write('<script src="https://s95.cnzz.com/z_stat.php?id=1260061311&web_id=1260061311" language="JavaScript"></script>')
    }

}