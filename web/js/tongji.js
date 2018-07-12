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

if(MUSER !=''){
    if(MUSER == '4pp'){
        document.write('<script src="http://s4.cnzz.com/z_stat.php?id=1259560086&web_id=1259560086" language="JavaScript"></script>')
    }else if(MUSER == '3as'){
        document.write('<script src="http://s11.cnzz.com/z_stat.php?id=1259560109&web_id=1259560109" language="JavaScript"></script>')
    }else if(MUSER == '4dg'){
        document.write('<script src="http://s95.cnzz.com/z_stat.php?id=1259560328&web_id=1259560328" language="JavaScript"></script>')
    }else if(MUSER == '2j6'){
        document.write('<script src="http://s11.cnzz.com/z_stat.php?id=1259560472&web_id=1259560472" language="JavaScript"></script>')
    }else if(MUSER == '9op'){
        document.write('<script src="http://s4.cnzz.com/z_stat.php?id=1259560520&web_id=1259560520" language="JavaScript"></script>')
    }else if(MUSER == 'l2e'){
        document.write('<script src="http://s4.cnzz.com/z_stat.php?id=1259560531&web_id=1259560531" language="JavaScript"></script>')
    }else if(MUSER == 'rt5'){
        document.write('<script src="http://s95.cnzz.com/z_stat.php?id=1259560538&web_id=1259560538" language="JavaScript"></script>')
    }else if(MUSER == 'momo'){
        document.write('<script  src="http://s11.cnzz.com/z_stat.php?id=1259829714&web_id=1259829714"  language="JavaScript"></script>')
    }else if(MUSER == 'sms'){
        document.write('<script  src="http://s95.cnzz.com/z_stat.php?id=1259829793&web_id=1259829793"  language="JavaScript"></script>')
    }else if(MUSER == 'wechat'){
        document.write('<script  src="http://s11.cnzz.com/z_stat.php?id=1259829954&web_id=1259829954"  language="JavaScript"></script>')
    }else if(MUSER == 'wechat2'){
        document.write('<script  src="http://s4.cnzz.com/z_stat.php?id=1259839449&web_id=1259839449"  language="JavaScript"></script>')
    }else if(MUSER == 'DDQQ'){
        document.write('<script src="https://s95.cnzz.com/z_stat.php?id=1260078494&web_id=1260078494" language="JavaScript"></script>')
    }

}