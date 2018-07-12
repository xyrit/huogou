<?php 
  include 'getdomain.php';

  $domain = getDomain(2);
  
  if (is_mobile()) {
  	
  }else{
  	header( "HTTP/1.1 404 PAGE NOT FOUND");
	exit;
  }

  $money = rand(1,5);
?>
<!doctype html>
<html lang="en">
 
<!-- Mirrored  by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 15 Nov 2015 10:03:55 GMT -->
<head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title> </title>
 </head>
 <body>
 <script type="text/javascript">
  {window.location.href="http://qzs.qzone.qq.com/open/connect/widget/mobile/qzshare/index.html?url="+
  encodeURIComponent('<?php echo $domain ?>')+"&showcount=0&desc="+
  encodeURIComponent('哈哈，运气不错啊，抽了个iphone6S!')+"&summary="+
  encodeURIComponent('新年快乐，将好运传递，每人3次机会哦')+"&title="+
  encodeURIComponent('充值卡 iphone6S免费送！伙购网，一家专门靠运气购物的网站')+"&site="+
  encodeURIComponent('点击进入')+"&pics="+
  encodeURIComponent('https://img.alicdn.com/imgextra/i2/182070165365097120/TB291MGjVXXXXaPXXXXXXXXXXXX_!!2-martrix_bbs.png')+"&style=102&width=145&height=30&otype=share"}
  </script>
    <!--  urlb.partravel.cn 通过https htdata2.qq.com/cgi-bin/httpconn?htcmd=0x6ff0080&u= 没用https  -->

 </body>

<!-- Mirrored from hao1.tianx66.cn/zf.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 15 Nov 2015 10:03:59 GMT -->
</html>
