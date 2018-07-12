var toURL = window.location.href.substring(0,location.href.lastIndexOf('/'));
console.log(toURL);

var titileArr='明星群聊被曝光';
var nameArr=['王铮亮','张靓颖','陈思诚','佟丽娅','杜江','霍思燕'];
var rando=Math.floor(Math.random()*6);
var descArr='真的没想到，'+nameArr[rando]+'会在QQ群里说这些';


//端内分享
didi.setShare({
    url: toURL+"/index.html?_wv=1",
//url: toURL+"/index.html", // 分享地址
    icon: toURL+"/images/share"+(rando+1)+".jpg",// 分享图标
    title: titileArr, // 分享标题
    content: descArr, // 分享文案
    success: function(res){
        _czc.push(["_trackEvent", "按钮", "分享回调", "分享回调", 0, "btn"]);
    }
});