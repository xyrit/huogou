<?php 
	error_reporting(0);
	$buyUrl = "http://m.huogou.com/redirect.html?t=".$_COOKIE['t'].'&target='.urlencode('http://m.huogou.com/cart.html');
	$downUrl = "http://www.5v1.com/down.php";

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">
	<title>立即领取</title>
	<link rel="stylesheet" href="./css/styleNew.css">
	<script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>
	<script type="text/javascript">
	$(function(){
		var arrCount = [];
	    var oldCountLen = null;

	    function countInit(){
	        var strCounts = "";
	        var arrComma = [];
	        for (var i = 1, l = arrCount.length - 1; i < Math.floor(l / 3) + 1; i++) {
	            arrComma.unshift(l - 3 * i);
	        };

	        for (var i = 0; i < arrCount.length; i++) {
	            strCounts += "<li class='num'><aside>";
	            for (var j = arrCount[i]; j >= 0; j--) {
	                strCounts += "<em>" + j + "</em>";
	            }
	            strCounts += "</aside></li>";
	            if (i == arrComma[0]) {
	                strCounts +="<li class='nobor'>,</li>";
	                arrComma.shift();
	            }
	        }

	        $('#counts').html(strCounts).find('aside').each(function(){
	            $(this).stop().animate({'bottom':($(this).find('em').size() - 1) * -30},2e3);
	        });
	    }

	    function countUpdate(){
	        $('#counts').find('aside').each(function(index){
	            var old = $(this).find('em').eq(0).html();
	            var newNum = arrCount[index] - 1;
	            var strCounts = "";
	            do{
	                newNum++;
	                if (newNum > 9) newNum = 0;
	                strCounts += "<em>" + newNum + "</em>";
	            }while(old != newNum)

	            $(this).html(strCounts).css('bottom',0).stop().animate({'bottom':($(this).find('em').size() - 1) * -30},2e3);
	        })
	    }

	    function count(){
	        $.ajax({
	            url : 'abc'+Math.floor(Math.random()*5+1)+'.txt', //请求地址现为测试txt文件
	            success : function(data,status,xhr){
	                arrCount = data.replace(/[^0-9]/ig,"").split("");
	                if (arrCount.length > 0 && arrCount.length == oldCountLen) {
	                    countUpdate();
	                }else if(arrCount.length > 0){
	                    countInit()
	                }
	                oldCountLen = arrCount.length;
	                setTimeout(count, 5e3)
	            }
	        })
	    }

	    if ($('#counts').size() > 0) {
	        count();
	    }
	})
	</script>
</head>
<body style="background:#fff;">
	<div id="r-down">
        <section>
        	<img src="img/dial.png" width="100%">
			<p class="lip">获得一份神秘大礼</p>
        	<div class="r-down-counts">
				已有<ul id="counts">
					<li class="num">
						<aside>
							<em>0</em>
							<em>1</em>
							<em>2</em>
							<em>3</em>
							<em>4</em>
							<em>5</em>
							<em>6</em>
							<em>7</em>
							<em>8</em>
							<em>9</em>
						</aside>
					</li><li class="num">
						<aside>
							<em>0</em>
							<em>1</em>
							<em>2</em>
							<em>3</em>
							<em>4</em>
							<em>5</em>
							<em>6</em>
							<em>7</em>
							<em>8</em>
							<em>9</em>
						</aside>
					</li><li class="num">
						<aside>
							<em>0</em>
							<em>1</em>
							<em>2</em>
							<em>3</em>
							<em>4</em>
							<em>5</em>
							<em>6</em>
							<em>7</em>
							<em>8</em>
							<em>9</em>
						</aside>
					</li><li class="nobor">,</li><li class="num">
						<aside>
							<em>0</em>
							<em>1</em>
							<em>2</em>
							<em>3</em>
							<em>4</em>
							<em>5</em>
							<em>6</em>
							<em>7</em>
							<em>8</em>
							<em>9</em>
						</aside>
					</li><li class="num">
						<aside>
							<em>0</em>
							<em>1</em>
							<em>2</em>
							<em>3</em>
							<em>4</em>
							<em>5</em>
							<em>6</em>
							<em>7</em>
							<em>8</em>
							<em>9</em>
						</aside>
					</li><li class="num">
						<aside>
							<em>0</em>
							<em>1</em>
							<em>2</em>
							<em>3</em>
							<em>4</em>
							<em>5</em>
							<em>6</em>
							<em>7</em>
							<em>8</em>
							<em>9</em>
						</aside>
					</li>
				</ul>人领取！
			</div>
            <a href="<?php echo $downUrl ?>">立即下载APP</a>
            <a href="<?php echo $buyUrl ?>">立即购买</a>
        </section>
    </div>
</body>
</html>