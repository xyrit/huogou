<?php

	$os = get_device_type();

	if ($os == 'android') {
		if (isWeixin()) {
			header("location:http://a.app.qq.com/o/simple.jsp?pkgname=com.huogou.app");
		}else{
			header("location:http://dl.huogou.com/huogou-huogou-1.1.2.apk");
		}
		exit;
	}else if ($os == 'ios') {
		header('location:http://a.app.qq.com/o/simple.jsp?pkgname=com.huogou.app');
		exit;
	}else{
		header('location:http://www.huogou.com');
		exit;
	}


	function get_device_type(){
	 	//全部变成小写字母
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$type = 'other';
		//分别进行判断
		if(strpos($agent, 'iphone') || strpos($agent, 'ipad')){
		  $type = 'ios';
		} 

		if(strpos($agent, 'android')){
		  $type = 'android';
		}
		return $type;
	}

	function isWeixin(){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		return strpos($user_agent, 'MicroMessenger');
	}