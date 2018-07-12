var Invitetimer = null;
function copyText(obj){
  var tips = '';
  try{
      var rng = document.body.createTextRange();
      rng.moveToElementText(obj);
      rng.scrollIntoView();
      rng.select();
      rng.execCommand("Copy");
      rng.collapse(false);
      tips = '复制成功！<(￣︶￣)↗[GO!]';
  }catch(e){
      tips = '对不起，复制失败，请手动复制！';
  }
  $('#wechat-con').fadeIn().find('p').text(tips).on('mouseenter',function(){
    clearTimeout(Invitetimer);
    console.log('aa');
  }).on('mouseleave',function(){
    console.log('bb');
    clearTimeout(Invitetimer);
    $('#wechat-con').fadeOut();
  })
  inviteTime();
}

function inviteTime(){
  Invitetimer = setTimeout(function(){
        $('#wechat-con').fadeOut();
    },2000)
}