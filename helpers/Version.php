<?php

/**
 * Created by PhpStorm.
 * User: hui
 * Date: 16/8/5
 * Time: 下午12:28
 */
namespace app\helpers;
class Version
{

    public static function compare($version,$operator,$min_version='2.0.3')
    {
        return version_compare($version, $min_version, $operator);
    }

}