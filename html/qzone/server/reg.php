<?php
	error_reporting(0);
	$baseUrl = "http://api.huogou.com/user";

	$account = $_POST['username'];
	$password = $_POST['password'];
	$code = $_POST['code'];
	$did = $_POST['did'];
	
	$message = '';
	
	if (!$account || !$password) {
		$message = "账号密码错误，请重试";
	}

	if (!$code) {
		$message = "验证码错误";
	}

	$aUrl = $baseUrl."/check-phone?phone=".$account;
	$aResult = json_decode(file_get_contents($aUrl),true);

	if ($aResult['state'] == 1) {
		$message = "手机号码已存在";
	}

	if (strlen($password) < 6 || strlen($password) > 20) {
		$message = "密码长度为8-20位字符";
	}
	
	if ($message) {
		echo '<script type="text/javascript">';
		echo 'alert("'.$message.'");';
		echo 'history.back(-1);';
		echo '</script>';
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>页面跳转中</title>
	<script src="../js/jquery-2.js"></script>
<style type="text/css">
	div{font-size: 4rem;}
	div span{font-size: 3rem}
	div i{font-style: normal;color: red}
</style>
</head>
<body>
	<div style="width:100%;text-align: center;margin-top: 100px">页面跳转中....<span>(<i>10</i>)</span></div>
</body>
<script type="text/javascript">
	var baseUrl = "http://api.huogou.com/user";
	$(function(){
		var error = "<?php echo $message?>";
		var i = 0;
		var timer = setInterval(function(){
			i++;
			if (i < 10) {
				$("div i").text(10-i);
			}else{
				var redirect = '<div style="margin-top:40px;text-align:center;width:100%">';
					redirect += '<a href="http://m.huogou.com">点击跳转</a>';
					redirect += '</div>';
				$("body").append(redirect);
				$("div span").remove();
				clearInterval(timer);
			}
		},1000);
		if (error.length < 1) {
			$.getJSON(baseUrl+"/register?account="+<?php echo $account ?>+"&password="+<?php echo $password ?>+"&smscode="+<?php echo $code ?>+"&source=99&spreadSource=wy_"+<?php echo $did ?>+"&callback=?",function(data){
	            if (data.code == 100) {
	                window.location.href = "http://m.huogou.com";
	            }else{
	            	alert('注册失败，请重试');
	            	history.back(-1);
	            }
	        })
		};
	})
</script>
</html>

