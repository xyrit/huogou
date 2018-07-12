<?php
include_once (__DIR__ . '/log.class.php');
include_once (__DIR__ . '/SDKConfig.php');
include_once (__DIR__ . '/secureUtil.php');
include_once (__DIR__ . '/httpClient.php');
// 初始化日志
$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );

/**
 * 字符串转换为 数组
 *
 * @param unknown_type $str        	
 * @return multitype:unknown
 */
function convertStringToArray($str) {
	return parseQString($str);
}

/**
 * key1=value1&key2=value2转array
 * @param $str key1=value1&key2=value2的字符串
 * @param $$needUrlDecode 是否需要解url编码，默认不需要
 */
function parseQString($str, $needUrlDecode=false){
	$result = array();
	$len = strlen($str);
	$temp = "";
	$curChar = "";
	$key = "";
	$isKey = true;
	$isOpen = false;
	$openName = "\0";
	
	for($i=0; $i<$len; $i++){
		$curChar = $str[$i];
		if($isOpen){
			if( $curChar == $openName){
				$isOpen = false;
			}
			$temp = $temp . $curChar;
		} elseif ($curChar == "{"){
			$isOpen = true;
			$openName = "}";
			$temp = $temp . $curChar;
		} elseif ($curChar == "["){
			$isOpen = true;
			$openName = "]";
			$temp = $temp . $curChar;
		} elseif ($isKey && $curChar == "="){
			$key = $temp;
			$temp = "";
			$isKey = false;
		} elseif ( $curChar == "&" && !$isOpen){
			putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
			$temp = "";
			$isKey = true;
		} else {
			$temp = $temp . $curChar;
		}	
	}
	putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
	return $result;		
}


function putKeyValueToDictionary($temp, $isKey, $key, &$result, $needUrlDecode) {
	if ($isKey) {
		$key = $temp;
		if (strlen ( $key ) == 0) {
			return false;
		}
		$result [$key] = "";
	} else {
		if (strlen ( $key ) == 0) {
			return false;
		}
		if ($needUrlDecode)
			$result [$key] = urldecode ( $temp );
		else
			$result [$key] = $temp;
	}
}

/**
 * 压缩文件 对应java deflate
 *
 * @param unknown_type $params        	
 */
function deflate_file(&$params) {
	global $log;
	foreach ( $_FILES as $file ) {
		$log->LogInfo ( "---------处理文件---------" );
		if (file_exists ( $file ['tmp_name'] )) {
			$params ['fileName'] = $file ['name'];
			
			$file_content = file_get_contents ( $file ['tmp_name'] );
			$file_content_deflate = gzcompress ( $file_content );
			
			$params ['fileContent'] = base64_encode ( $file_content_deflate );
			$log->LogInfo ( "压缩后文件内容为>" . base64_encode ( $file_content_deflate ) );
		} else {
			$log->LogInfo ( ">>>>文件上传失败<<<<<" );
		}
	}
}

/**
 * 处理报文中的文件
 *
 * @param unknown_type $params        	
 */
function deal_file($params) {
	global $log;
	if (isset ( $params ['fileContent'] )) {
		$log->LogInfo ( "---------处理后台报文返回的文件---------" );
		$fileContent = $params ['fileContent'];
		
		if (empty ( $fileContent )) {
			$log->LogInfo ( '文件内容为空' );
			return false;
		} else {
			// 文件内容 解压缩
			$content = gzuncompress ( base64_decode ( $fileContent ) );
			$root = SDK_FILE_DOWN_PATH;
			$filePath = null;
			if (empty ( $params ['fileName'] )) {
				$log->LogInfo ( "文件名为空" );
				$filePath = $root . $params ['merId'] . '_' . $params ['batchNo'] . '_' . $params ['txnTime'] . '.txt';
			} else {
				$filePath = $root . $params ['fileName'];
			}
			$handle = fopen ( $filePath, "w+" );
			if (! is_writable ( $filePath )) {
				$log->LogInfo ( "文件:" . $filePath . "不可写，请检查！" );
				return false;
			} else {
				file_put_contents ( $filePath, $content );
				$log->LogInfo ( "文件位置 >:" . $filePath );
			}
			fclose ( $handle );
		}
		return true;
	} else {
		return false;
	}
}

/**
 * 构造自动提交表单
 *
 * @param unknown_type $params        	
 * @param unknown_type $action        	
 * @return string
 */
function create_html($params, $action) {
	// <body onload="javascript:document.pay_form.submit();">
	$encodeType = isset ( $params ['encoding'] ) ? $params ['encoding'] : 'UTF-8';
	$html = <<<eot
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={$encodeType}" />
</head>
<body onload="javascript:document.pay_form.submit();">
    <form id="pay_form" name="pay_form" action="{$action}" method="post">
	
eot;
	foreach ( $params as $key => $value ) {
		$html .= "    <input type=\"hidden\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />\n";
	}
	$html .= <<<eot
   <!-- <input type="submit" type="hidden">-->
    </form>
</body>
</html>
eot;
	return $html;
}

/**
 * map转换string
 *
 * @param
 *        	$customerInfo
 */
function getCustomerInfoStr($customerInfo) {
	return base64_encode ( "{" . createLinkString ( $customerInfo, false, false ) . "}" );
}

/**
 * map转换string，按新规范加密
 *
 * @param
 *        	$customerInfo
 */
function getCustomerInfoStrNew($customerInfo) {
	$encryptedInfo = array();
	foreach ( $customerInfo as $key => $value ) {
		if ($key == 'phoneNo' || $key == 'cvn2' || $key == 'expired' ) {
		//if ($key == 'phoneNo' || $key == 'cvn2' || $key == 'expired' || $key == 'certifTp' || $key == 'certifId') {
			$encryptedInfo [$key] = $customerInfo [$key];
			unset ( $customerInfo [$key] );
		}
	}
	if(count($encryptedInfo) != 0){
		$encryptedInfo = createLinkString ( $encryptedInfo, false, false );
		$encryptedInfo = encryptData ( $encryptedInfo, SDK_ENCRYPT_CERT_PATH );
		$customerInfo ['encryptedInfo'] = $encryptedInfo;
	}
	return base64_encode ( "{" . createLinkString ( $customerInfo, false, false ) . "}" );
}

/**
 * 讲数组转换为string
 *
 * @param $para 数组        	
 * @param $sort 是否需要排序        	
 * @param $encode 是否需要URL编码        	
 * @return string
 */
function createLinkString($para, $sort, $encode) {
	
	if($para == NULL || !is_array($para))
		return "";
	
	$linkString = "";
	if ($sort) {
		$para = argSort ( $para );
	}
	while ( list ( $key, $value ) = each ( $para ) ) {
		if ($encode) {
			$value = urlencode ( $value );
		}
		$linkString .= $key . "=" . $value . "&";
	}
	// 去掉最后一个&字符
	$linkString = substr ( $linkString, 0, count ( $linkString ) - 2 );
	
	return $linkString;
}

/**
 * 对数组排序
 *
 * @param $para 排序前的数组
 *        	return 排序后的数组
 */
function argSort($para) {
	ksort ( $para );
	reset ( $para );
	return $para;
}

/**
 * 后台交易 HttpClient通信
 *
 * @param unknown_type $params        	
 * @param unknown_type $url        	
 * @return mixed
 */
function post($params, $url, &$errmsg) {
	$opts = createLinkString ( $params, false, true );
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false ); // 不验证证书
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false ); // 不验证HOST
	curl_setopt ( $ch, CURLOPT_SSLVERSION, 1 ); // http://php.net/manual/en/function.curl-setopt.php页面搜CURL_SSLVERSION_TLSv1
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
			'Content-type:application/x-www-form-urlencoded;charset=UTF-8' 
	) );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $opts );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	$html = curl_exec ( $ch );
	if(curl_errno($ch)){
		$errmsg = curl_error($ch);
		curl_close ( $ch );
		return false;
	}
    if( curl_getinfo($ch, CURLINFO_HTTP_CODE) != "200"){
		$errmsg = "http状态=" . curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ( $ch );
		return false;
    }
	curl_close ( $ch );
	return $html;
}

/**
 * 打印请求应答
 *
 * @param
 *        	$url
 * @param
 *        	$req
 * @param
 *        	$resp
 */
function printResult($url, $req, $resp) {
	echo "=============<br>\n";
	echo "地址：" . $url . "<br>\n";
	echo "请求：" . str_replace ( "\n", "\n<br>", htmlentities ( createLinkString ( $req, false, true ) ) ) . "<br>\n";
	echo "应答：" . str_replace ( "\n", "\n<br>", htmlentities ( $resp ) ) . "<br>\n";
	echo "=============<br>\n";
}

/**
 * 解析customerInfo。
 * 为方便处理，encryptedInfo下面的信息也均转换为customerInfo子域一样方式处理，
 * @param unknown $customerInfostr       	
 * @return array形式ParseCustomerInfo
 */
function ParseCustomerInfo($customerInfostr, $cert_path,$sign_cert_pwd) {
	$customerInfostr = base64_decode($customerInfostr);
	$customerInfostr = substr($customerInfostr, 1, strlen($customerInfostr) - 2);
	$customerInfo = parseQString($customerInfostr);
	if(array_key_exists("encryptedInfo", $customerInfo)) {
		$encryptedInfoStr = $customerInfo["encryptedInfo"];
		unset ( $customerInfo ["encryptedInfo"] );
		$encryptedInfoStr = decryptData($encryptedInfoStr, $cert_path,$sign_cert_pwd);
		$encryptedInfo = parseQString($encryptedInfoStr);
		foreach ($encryptedInfo as $key => $value){
			$customerInfo[$key] = $value;
		}
	}
	return $customerInfo;
}