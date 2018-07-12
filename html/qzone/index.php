<?php 
  include 'server/getdomain.php';

  //$domain = getDomain(1);
  if (is_mobile()) {
  	if ($_COOKIE['ti'] == 'yes' && $_COOKIE['name'] >= '3') {
  		if ($_COOKIE['t']) {
  			//header("location:http://m.huogou.com/cart.html?t=".$_COOKIE['t']);
			//header("location:http://m.huogou.com/redirect.html?t=".$_COOKIE['t'].'&target='.urlencode('http://m.huogou.com/cart.html'));
			header("location:success.php");
  		}else{
			header("location:reg.php?did=".$_GET['did']);
  		}
	}
  	header("location:lottery.php?did=".$_GET['did']);	
  }else{
	if ($_COOKIE['ti'] == 'yes' && $_COOKIE['name'] >= '3') {
  		if ($_COOKIE['t']) {
  			//header("location:http://m.huogou.com/cart.html?t=".$_COOKIE['t']);
			//header("location:http://m.huogou.com/redirect.html?t=".$_COOKIE['t'].'&target='.urlencode('http://www.huogou.com/cart.html'));
			header("location:success.php");
  		}else{
			header("location:reg.php?did=".$_GET['did']);
  		}
	}
	
  	header("location:lottery.php?did=".$_GET['did']);
  }
  // 
?>