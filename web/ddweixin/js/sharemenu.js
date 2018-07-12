/**
 * Created by jun on 15/12/1.
 */
var shareMenu = function (settings) {
    settings = {
        config: settings.config,
        onready: settings.onready,
        content: settings.content,
        sucfunc: settings.sucfunc,
        canclefunc: settings.canclefunc
    }
    var setConfig = settings.config;
    if (typeof setConfig != 'undefined') {
        wx.config({
            debug: false,
            appId: setConfig.appId,
            timestamp: setConfig.timestamp,
            nonceStr: setConfig.nonceStr,
            signature: setConfig.signature,
            jsApiList: [
                'checkJsApi',
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
            ]
        });
    }


    var setOnMenuShare = function (title, desc, link, imgUrl, sucfunc, canclefunc) {
        wx.onMenuShareTimeline({
            title: title, // 分享标题
            link: link, // 分享链接
            imgUrl: imgUrl, // 分享图标
            success: sucfunc,
            cancel: canclefunc
        });

        wx.onMenuShareAppMessage({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: link, // 分享链接
            imgUrl: imgUrl, // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: sucfunc,
            cancel: canclefunc
        });

        wx.onMenuShareQQ({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: link, // 分享链接
            imgUrl: imgUrl, // 分享图标
            success: sucfunc,
            cancel: canclefunc
        });

        wx.onMenuShareQZone({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: link, // 分享链接
            imgUrl: imgUrl, // 分享图标
            success: sucfunc,
            cancel: canclefunc
        });
    }

    var setOnready = settings.onready;
    var shareContent = settings.content;
    if (setOnready) {
        wx.ready(function () {
            setOnMenuShare(shareContent.title, shareContent.desc, shareContent.link, shareContent.imgUrl);
        });
    } else {
        setOnMenuShare(shareContent.title, shareContent.desc, shareContent.link, shareContent.imgUrl);
    }
}

$(function(){
    $(document).on("click",".sharemenu",function(){
                var config = {
                    appId: 'wx2df81b52dd3f1898',
                    timestamp: timestamp,
                    nonceStr: nonceStr,
                    signature: signature
                };
                var content = {
                    title: $(this).attr("posttitle"), // 分享标题
                    desc: $(this).attr("postcontent"), // 分享描述
                    link: $(this).attr('postlink'), // 分享链接
                    imgUrl: $(this).attr('postpic') // 分享图标
                };
                shareMenu({config:config,onready:true,content:content});
                
                if(! $('#m_popUp').length) $("body").append('<div id="m_popUp" class="m_popUp" style="display: none"><div class="m_guide"></div><cite></cite></div>');
                $('#m_popUp').fadeIn().click(function(){
                   $(this).fadeOut();
               });
    });
})
