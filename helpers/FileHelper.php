<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/22
 * Time: 下午12:57
 */
namespace app\helpers;

class FileHelper
{
    public static function computerFileSize($str) {
        $str = strtolower($str);
        if (is_numeric($str)) {
            return floatval($str);
        }

        $bytes_array = array(
            'b' => 1,
            'k' => 1024,
            'kb' => 1024,
            'mb' => 1024 * 1024,
            'm' => 1024 * 1024,
            'gb' => 1024 * 1024 * 1024,
            'g' => 1024 * 1024 * 1024,
            'tb' => 1024 * 1024 * 1024 * 1024,
            't' => 1024 * 1024 * 1024 * 1024,
            'pb' => 1024 * 1024 * 1024 * 1024 * 1024,
            'p' => 1024 * 1024 * 1024 * 1024 * 1024,
        );

        $bytes = floatval($str);

        if (preg_match('#([kmgtp]?b?)$#si', $str, $matches) && !empty($bytes_array[$matches[1]])) {
            $bytes *= $bytes_array[$matches[1]];
        } else {
            return false;
        }

        $bytes = round($bytes);

        return $bytes;
    }
}