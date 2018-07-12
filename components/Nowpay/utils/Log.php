<?php
    require_once 'Tools.php';
    
    class Log{
        static function outLog($api_n,$content){
            $time=Tools::getTime("Y年m月d日i分s秒x毫秒");
            $log_str="$time   $api_n\n$content\n------------------\n";
            $file_n="Log__".date("Ymd").".txt";
//            $file=fopen("$file_n", "a+");
//            fwrite($file, $log_str);
//            fclose($file);
        }
    }