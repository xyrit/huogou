<?php
    /**
     * 
     * @author Jupiter
     * 
     * 应用配置类
     */
    class Config{
        static $timezone="Asia/Shanghai";
//        static $app_id="1467289650231653";//该处配置您的APPID
//        static $secure_key="Fm00laUPZcgIDdlUb2q6ruJS1fztg7rl";//该处配置您的应用秘钥
//        static $query_url="http://api.ipaynow.cn";
        
        const VERIFY_HTTPS_CERT=false;
        const QUERY_FUNCODE_KEY="B001";
        const SIGNATURE_KEY="signature";
        const SIGNTYPE_KEY="signType";
        const MHT_SIGN_TYPE_KEY="mhtSignType";
        const MHT_SIGNATURE_KEY="mhtSignature";
//        const SIGNATURE_KEY="mSignature";
//        const SIGNTYPE_KEY="mhtSignType";
        const CHARSET="UTF-8";
        const SIGN_TYPE="MD5";
        const QSTRING_EQUAL="=";
        const QSTRING_SPLIT="&";
    }