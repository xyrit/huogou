//领取红包列表 及 获得规则切换
var but0 = $('.carCenterTitle a:eq(0)');
var but1 = $('.carCenterTitle a:eq(1)');
but0.click(function(){
    var imgUrl = $(this).find('img').attr('data');
    var imgsrc = 'images/'+imgUrl+'Hover.png';
    $(this).find('img').attr('src',imgsrc);
    var but2Img = but1.find('img').attr('data');
    var imgsrc2 = 'images/'+but2Img+'.png';
    but1.find('img').attr('src',imgsrc2);
    $('.redBox').fadeIn();
    $('.ruletext').fadeOut();
});

but1.click(function(){
    var imgUrl = $(this).find('img').attr('data');
    var imgsrc = 'images/'+imgUrl+'Hover.png';
    $(this).find('img').attr('src',imgsrc);
    var but2Img = but0.find('img').attr('data');
    var imgsrc2 = 'images/'+but2Img+'.png';
    but0.find('img').attr('src',imgsrc2);
    $('.redBox').fadeOut();
    $('.ruletext').fadeIn();
});



