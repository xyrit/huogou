(function() {
    var pcurl = "http://vac.qq.com/common/pc/pc.html?title=" + encodeURIComponent(document.title) + "&acturl=" + encodeURIComponent(window.location.href);
    var os = function() {
        var ua = navigator.userAgent,
            isWindowsPhone = /(?:Windows Phone)/.test(ua),
            isSymbian = /(?:SymbianOS)/.test(ua) || isWindowsPhone,
            isAndroid = /(?:Android)/.test(ua),
            isFireFox = /(?:Firefox)/.test(ua),
            isChrome = /(?:Chrome|CriOS)/.test(ua),
            isTablet = /(?:iPad|PlayBook)/.test(ua) || (isAndroid && !/(?:Mobile)/.test(ua)) || (isFireFox && /(?:Tablet)/.test(ua)),
            isIPhone = /(?:iPhone)/.test(ua) && !isTablet,
            isPc = !isIPhone && !isAndroid && !isSymbian && !isTablet;
        return {
            isWindowsPhone: isWindowsPhone,
            isTablet: isTablet,
            isIPhone: isIPhone,
            isAndroid: isAndroid,
            isPc: isPc
        };
    } ();
    if (os.isPc) {
        window.location.href = pcurl;
    }
})();