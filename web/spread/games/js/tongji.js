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
    if (MUSER == 'hg_wx') {
        document.write('<script src="https://s4.cnzz.com/z_stat.php?id=1260061229&web_id=1260061229" language="JavaScript"></script>')
    } else if (MUSER == 'duanxin') {
        document.write('<script src="https://s4.cnzz.com/z_stat.php?id=1260061346&web_id=1260061346" language="JavaScript"></script>')
    }

}