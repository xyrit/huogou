<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/10
 * Time: 下午4:13
 */

$form = whereFrom(DOMAIN);

if ($form == 2) {
    return require(__DIR__.'/didi.route.php');
} else {
    return require(__DIR__.'/hg.route.php');
}