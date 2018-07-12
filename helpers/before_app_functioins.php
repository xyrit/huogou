<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/6/15
 * Time: 09:19
 */

/**
 * 取得根域名
 * @param type $domain 域名
 * @return string 返回根域名
 */
function GetUrlToDomain($domain)
{
    $re_domain = '';
    $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn", "dev");
    $array_domain = explode(".", $domain);
    $array_num = count($array_domain) - 1;
    if ($array_domain[$array_num] == 'cn') {
        if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
            $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        } else {
            $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        }
    } else {
        $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
    }
    return $re_domain;
}


function whereFrom($domain)
{
    $ddDomains = [
        'dddb.com',
        'dddb.co',
        'dddb.dev',
    ];
    if(in_array($domain, $ddDomains)) {
        return 2;
    } else {
        return 1;
    }
}