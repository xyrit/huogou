<?php

    class Tools{
        public static function getTime($tag){
            list($usec,$sec)=explode(" ", microtime());
            $now_time=((float)$usec+(float)$sec);
            list($usec,$sec)=explode(".", $now_time);
            $date=date($tag,$usec);
            return str_replace('x', $sec, $date);
        }
    }