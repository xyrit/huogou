/**
 * copyright @idea0086.com
 * by lane
 * @2015-05-29
 */
function getCookie(key) {
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i = 0; i < ARRcookies.length; i++) {
        x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
        y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
        x = x.replace(/^\s+|\s+$/g, "");
        if (x == key) {
            return unescape(y);
        }
    }
}

function setCookie(key, value, seconds) {
    var exdate = new Date();
    exdate.setTime(exdate.getTime() + seconds * 1000);
    var c_value = escape(value) + ((seconds == null) ? "" : "; expires=" + exdate.toUTCString());
    c_value += '; path=/';
    document.cookie = key + "=" + c_value;
}

(function () {
    var apiUrl = 'http://log.xinyuemin.com',
        api = 'basic',
        type = 'url',
        source = typeof(IDEA_LogSource) != 'undefined' ? IDEA_LogSource : location.hostname,
        data = getUAData();
    var sData = typeof(JSON.stringify) != 'undefined' ? JSON.stringify(data) : objectToJsonString(data);   //只支持一维数组
    var dImg = document.createElement('img');
    dImg.style.display = 'none';
    dImg.src = apiUrl + '/?api=' + api +
        '&type=' + type +
        '&source=' + encodeURIComponent(source) +
        '&data=' + encodeURIComponent(sData) +
        '&rnd=' + parseInt(Math.random() * 10000);
    document.body.appendChild(dImg);

    //获取客户端信息
    function getUAData() {
        var data = {};
        data.openid = typeof(openid) != 'undefined' ? openid : '';
        if (!data.openid) {
            data.openid = typeof(_bkoid) != 'undefined' ? _bkoid : '';
        }
        if (!data.openid) {
            var dMetas = document.getElementsByTagName('meta');
            for (i in dMetas) {
                if (typeof(dMetas[i].name) != 'undefined' && dMetas[i].name == 'wxopenid') {
                    data.openid = dMetas[i].content;
                    break;
                }
            }
        }
        data.requestUrl = window.location.href;
        data.referer = typeof(document.referrer) != 'undefined' ? document.referrer : '';
        data.timestamp = Math.ceil((new Date()).getTime() / 1000);

        //添加用户唯一标识，以便统计UV和活跃用户数量
        data.uid = getUID();

        //添加停留时间统计
        var ot = getLiveTime();
        data.livetime = ot.liveTime;
        data.firstvisittime = ot.firstVisitTime;

        return data;
    }

    //生成用户唯一标识
    function getUID() {
        var k = 'IDEAUID4LOG';
        var uid = getCookie(k);
        if (!uid) {
            var d = new Date(),
                uid = d.getTime() + parseInt(Math.random() * 10000);
            setCookie(k, uid, 365 * 3600 * 24);     //one year
        }

        return uid;
    }

    //获取停留时间统计
    function getLiveTime() {
        var kf = 'IDEAUFV4LOG',
            kl = 'IDEAUFVTLOG';
        var firstVisitTime = getCookie(kf),
            lastVisitTime = getCookie(kl);
        var d = new Date(),
            now = d.getTime();

        //init lastVisitTime and firstVisitTime
        if (!lastVisitTime) {
            lastVisitTime = now;

            //refresh fist visit time
            firstVisitTime = now;
            setCookie(kf, firstVisitTime, 3600 * 24);     //one day
        }

        //get live time
        var liveTime = parseInt((now - lastVisitTime) / 1000);

        //flush last visit time
        lastVisitTime = now;
        setCookie(kl, lastVisitTime, 1800);     //half hour

        //return seconds
        return {'liveTime': liveTime, 'firstVisitTime': firstVisitTime};
    }

    //对象转json格式字符串
    function objectToJsonString(data) {
        var timestamp = Math.ceil((new Date()).getTime() / 1000);
        if (typeof(data.timestamp) != 'undefined') {
            timestamp = data.timestamp;
            delete data.timestamp;
        }

        var sData = '{';
        for (key in data) {
            if (isNaN(data[key])) {
                sData += '"' + key + '":"' + data[key].toString() + '",';
            } else {
                sData += '"' + key + '":' + data[key] + ',';
            }
        }
        sData += '"timestamp":' + timestamp;
        sData += '}';

        return sData;
    }
})();