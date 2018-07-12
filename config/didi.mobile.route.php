<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/19
 * Time: 下午3:18
 */

return [
    //微信模块
    'http://m.' . DOMAIN => '/mobile',
    'http://m.' . DOMAIN . '/about.html' => '/mobile/help/about',
    'http://m.' . DOMAIN . '/problem.html' => '/mobile/help/problem',
    'http://m.' . DOMAIN . '/no-winning.html' => '/mobile/help/nowinning',
    'http://m.' . DOMAIN . '/suggestion.html' => '/mobile/help/suggestion',
    'http://m.' . DOMAIN . '/help/<action:\w+>.html' => '/mobile/help/<action>',
    
    'http://m.' . DOMAIN . '/passport/registercheck.html' => '/mobile/passport/register-check',
    'http://m.' . DOMAIN . '/passport/registerbind.html' => '/mobile/passport/register-bind',
    'http://m.' . DOMAIN . '/passport/<action:[\w-]+>.html' => '/mobile/passport/<action>',
    
    'http://m.' . DOMAIN . '/list.html' => '/mobile/list',
    'http://m.' . DOMAIN . '/cart.html' => '/mobile/cart',
    'http://m.' . DOMAIN . '/cart/payment.html' => '/mobile/cart/payment',
    'http://m.' . DOMAIN . '/cart/weixinpay.html' => '/mobile/cart/weixinpay',
    'http://m.' . DOMAIN . '/cart/weixinpayok.html' => '/mobile/cart/weixinpayok',
    'http://m.' . DOMAIN . '/cart/weixinpayok-<o:[\w\d]+>-<s:[\w\d]+>.html' => '/mobile/cart/weixinpayok',
    'http://m.' . DOMAIN . '/cart/iapppayok.html' => '/mobile/cart/iapppayok',
    'http://m.' . DOMAIN . '/cart/chinabankpay.html' => '/mobile/cart/chinabankpay',
    'http://m.' . DOMAIN . '/shopok.html' => '/mobile/pay/result',
    'http://m.' . DOMAIN . '/product/<pid:\d+>.html' => '/mobile/product',
    'http://m.' . DOMAIN . '/lottery/<pid:\d+>.html' => '/mobile/product/lottery',
    'http://m.' . DOMAIN . '/lottery/BuyDetail-<periodId:\d+>.html' => '/mobile/lottery/buy-detail',
    
    'http://m.' . DOMAIN . '/moreperiod-<pid:\d+>.html' => '/mobile/product/moreperiod',
    'http://m.' . DOMAIN . '/buyrecords-<pid:\d+>.html' => '/mobile/product/buyrecords',
    'http://m.' . DOMAIN . '/lottery/calresult-<pid:\d+>.html' => '/mobile/product/calresult',
    'http://m.' . DOMAIN . '/goodsimgdesc-<pid:\d+>.html' => '/mobile/product/goodsimgdesc',
    'http://m.' . DOMAIN . '/limitbuy.html' => '/mobile/limitbuy',
    'http://m.' . DOMAIN . '/lottery/m1.html' => '/mobile/lottery',
    'http://m.' . DOMAIN . '/ten.html' => '/mobile/ten',

    'http://m.' . DOMAIN . '/post/index.html' => '/mobile/post',
    'http://m.' . DOMAIN . '/post/detail-<pid:\d+>.html' => '/mobile/post/detail',
    'http://m.' . DOMAIN . '/goodspost-<pid:\d+>.html' => '/mobile/post/list',

    'http://m.' . DOMAIN . '/payapi/<action:[\w-]+>' => '/mobile/payapi/<action>',

    'http://m.' . DOMAIN . '/redirect.html' => '/mobile/redirect',


    'http://m.' . DOMAIN . '/member/index.html' => '/mobile/member/',
    'http://m.' . DOMAIN . '/member/goodsbuylist.html' => '/mobile/member/buylist',
    'http://m.' . DOMAIN . '/member/goodsbuydetail-<pid:\d+>.html' => '/mobile/member/buydetail',
    'http://m.' . DOMAIN . '/member/orderdetail-<id:\d+>.html' => '/mobile/member/orderdetail',
    'http://m.' . DOMAIN . '/member/addressadd-<id:\d+>.html' => '/mobile/member/addressadd',
    'http://m.' . DOMAIN . '/member/addressedit-<id:\d+>.html' => '/mobile/member/addressedit',
    'http://m.' . DOMAIN . '/member/virtual-addressedit-<id:\d+>.html' => '/mobile/member/virtualaddressedit',
    'http://m.' . DOMAIN . '/member/postone-<id:\d+>.html' => '/mobile/member/postone',
    'http://m.' . DOMAIN . '/member/post-<id:\d+>.html' => '/mobile/member/post',
    'http://m.' . DOMAIN . '/member/postuploadimage.html' => '/mobile/member/post-upload-image',
    'http://m.' . DOMAIN . '/member/<action:[\w-]+>.html' => '/mobile/member/<action>',


    'http://m.' . DOMAIN . '/userpage/<id:\d+>' => '/mobile/user',
];