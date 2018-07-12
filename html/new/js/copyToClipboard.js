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
      tips = '已经复制到粘贴板!你可以使用Ctrl+V 贴到需要的地方去了哦!';
  }catch(e){
      tips = '您的浏览器不支持此复制功能，请选中相应内容并使用Ctrl+C进行复制!';
  }
  $('#invite_tishi').fadeIn().find('p').text(tips).on('mouseenter',function(){
    clearTimeout(Invitetimer);
    console.log('aa');
  }).on('mouseleave',function(){
    console.log('bb');
    clearTimeout(Invitetimer);
    $('#invite_tishi').fadeOut();
  })
  inviteTime();
}

function inviteTime(){
  Invitetimer = setTimeout(function(){
        $('#invite_tishi').fadeOut();
    },2000)
}