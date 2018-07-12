<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/6/15
 * Time: 09:24
 */
$mobileRoute = require(__DIR__.'/hg.mobile.route.php');


$route =  [

    'http://t.' . DOMAIN . '/<code:[\d\w]+>' => '/invite/link',

    // 图片显示链接
    'http://img.' . DOMAIN . '/pic-<width:[\d\w]+>-<height:[\d\w]+>/<basename:[\d_\.\w]+>' => '/image/product/view',
    'http://img.' . DOMAIN . '/goodsinfo/<basename:[\d_\.\w]+>' => '/image/product/info',
    'http://img.' . DOMAIN . '/userface/<width:[\d\w]+>/<basename:[\d_\.\w]+>' => '/image/user/face',
    'http://img.' . DOMAIN . '/groupicon/<basename:[\d_\.\w]+>' => '/image/group/icon',
    'http://img.' . DOMAIN . '/grouppic/<size:\w+>/<basename:[\d_\.\w]+>' => '/image/group/info',
    'http://img.' . DOMAIN . '/userpost/<size:\w+>/<basename:[\d_\.\w]+>' => '/image/share/info',
    'http://img.' . DOMAIN . '/banner/<size:\w+>/<basename:[\d_\.\w]+>' => '/image/banner/info',
    'http://img.' . DOMAIN . '/temp/<size:[\d\w]+>/<basename:[\d_\.\w]+>' => '/image/temp/view',
    'http://img.' . DOMAIN . '/temp/<basename:[\d_\.\w]+>' => '/image/temp/view',
    'http://img.' . DOMAIN . '/active/<size:\w+>/<basename:[\d_\.\w]+>' => '/image/active/info',

    //主模块
    'http://www.' . DOMAIN . '/invite.html' => '/invite',
    'http://www.' . DOMAIN . '/list.html' => '/list',
    'http://www.' . DOMAIN . '/list-<cid:\d+>-<bid:\d+>.html' => '/list',
    'http://www.' . DOMAIN . '/list-<cid:\d+>-<bid:\d+>-<page:\d+>.html' => '/list',
    'http://www.'.DOMAIN.'/product/<pid:\d+>.html' => '/product',
    'http://www.'.DOMAIN.'/lottery/<pid:\d+>.html' => '/product/lottery',
    'http://www.'.DOMAIN.'/lottery/m1.html' => '/lottery',
    'http://www.'.DOMAIN.'/lottery/m<page:\d+>.html' => '/lottery',
    'http://www.'.DOMAIN.'/lottery/i<cid:\d+>.html' => '/lottery',
    'http://www.'.DOMAIN.'/lottery/i<cid:\d+>m<page:\d+>.html' => '/lottery',
    'http://www.'.DOMAIN.'/limitbuy/m1.html' => '/limitbuy',
    'http://www.'.DOMAIN.'/ten/m1.html' => '/ten',
    'http://www.'.DOMAIN.'/payment.html' => '/payment',
    'http://www.'.DOMAIN.'/cart.html' => '/cart',
    'http://www.'.DOMAIN.'/pay.html' => '/pay',
    'http://www.'.DOMAIN.'/pay/result.html' => '/pay/result',
    'http://www.'.DOMAIN.'/alipay.html' => '/alipay',
    'http://www.'.DOMAIN.'/alipay/notify.html' => '/alipay/notify',
    /************ 现在支付 *********/
    'http://www.'.DOMAIN.'/nowpay/index.html' => '/nowpay/index',
    'http://www.'.DOMAIN.'/nowpay/notify.html' => '/nowpay/notify',
    /************ 现在支付 *********/
    'http://www.'.DOMAIN.'/chatpay.html' => '/chatpay',
    'http://www.'.DOMAIN.'/chatpay/qrcode.html' => '/chatpay/qrcode',
    'http://www.'.DOMAIN.'/qqpay.html' => '/chatpay/qqpay',
    'http://www.'.DOMAIN.'/chatpay/notify.html' => '/chatpay/notify',
    'http://www.'.DOMAIN.'/chatpay/onlinepay-notify.html' => '/chatpay/onlinepay-notify',
    'http://www.'.DOMAIN.'/iapppay.html' => '/iapppay',
    'http://www.'.DOMAIN.'/iapppay/notify.html' => '/iapppay/notify',
    'http://www.'.DOMAIN.'/iapppay/redirect.html' => '/iapppay/redirect',
    'http://www.'.DOMAIN.'/iapppay/backmoney.html' => '/iapppay/backmoney',
    'http://www.'.DOMAIN.'/jdpay.html' => '/jdpay',
    'http://www.'.DOMAIN.'/jdpay/notify.html' => '/jdpay/notify',
	'http://www.'.DOMAIN.'/jdpay/recharge-notify.html' => '/jdpay/recharge-notify',
    'http://www.'.DOMAIN.'/jdpay/redirect-<o:[\w\d]+>.html' => '/jdpay/redirect',
    'http://www.'.DOMAIN.'/kqpay.html' => '/kqpay',
    'http://www.'.DOMAIN.'/kqpay/notify.html' => '/kqpay/notify',
    'http://www.'.DOMAIN.'/kqpay/redirect-<o:[\w\d]+>.html' => '/kqpay/redirect',
    'http://www.'.DOMAIN.'/unionpay.html' => '/unionpay',
    'http://www.'.DOMAIN.'/unionpay/notify.html' => '/unionpay/notify',
    'http://www.'.DOMAIN.'/unionpay/redirect-<o:[\w\d]+>.html' => '/unionpay/redirect',
    'http://www.'.DOMAIN.'/guide.html' => '/guide',
    'http://www.'.DOMAIN.'/supervise.html' => '/supervise',
    'http://www.'.DOMAIN.'/fund.html' => '/fund',
    'http://www.'.DOMAIN.'/historybuylist.html' => '/historybuylist',
    'http://www.'.DOMAIN.'/search.html' => '/search',
    'http://www.'.DOMAIN.'/newbuylist.html' => '/newbuylist',
    'http://www.'.DOMAIN.'/chinabank/receive.html' => '/chinabank/receive',
    'http://www.'.DOMAIN.'/chinabank/autoreceive.html' => '/chinabank/auto-receive',
    // 'http://www.'.DOMAIN.'/app/msglist.html' => '/app/push-log',
    // 'http://www.'.DOMAIN.'/app/msgcontent-<id:\d+>.html' => '/app/push-info',
    'http://www.'.DOMAIN.'/app/<action:[\w-]+>' => '/app/<action>',
    'http://www.'.DOMAIN.'/ip.html' => '/ip/index',
    'http://www.'.DOMAIN.'/manual.html' => '/manual/sync',
    'http://www.'.DOMAIN.'/lottery.html' => '/active/lottery',
    'http://www.'.DOMAIN.'/active/raffle' => '/active/raffle',
    'http://www.'.DOMAIN.'/sharelink.html' => '/sharelink',
    'http://www.'.DOMAIN.'/test.html' => '/testrecharge',
    'http://www.'.DOMAIN.'/manualnewperiod.html' => '/manualnewperiod',
    'http://www.'.DOMAIN.'/manualsync.html' => '/manualsync',
    'http://www.'.DOMAIN.'/duiba/redirect.html' => '/duiba/redirect',
    'http://www.'.DOMAIN.'/duiba/consume.html' => '/duiba/consume',
    'http://www.'.DOMAIN.'/duiba/notify.html' => '/duiba/notify',


    //api模块
    'http://api.' . DOMAIN => '/api',
    'http://api.' . DOMAIN . '/<controller:[\w-]+>' => '/api/<controller>',
    'http://api.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>' => '/api/<controller>/<action>',

    //护照模块
    'https://passport.' . DOMAIN . '/login.html' => '/passport/default/login',
    'https://passport.' . DOMAIN . '/logout.html' => '/passport/default/logout',
    'https://passport.' . DOMAIN . '/register.html' => '/passport/default/register',
    'https://passport.' . DOMAIN . '/register-check.html' => '/passport/default/register-check',
    'https://passport.' . DOMAIN . '/verifycode.html' => '/passport/default/captcha',
    'https://passport.' . DOMAIN . '/oauth-client-<authclient:\w+>.html' => '/passport/oauth/auth',
    'https://passport.' . DOMAIN . '/oauth-bind.html' => '/passport/oauth/bind',
    'https://passport.' . DOMAIN . '/oauth-verify.html' => '/passport/oauth/verify',
    'https://passport.' . DOMAIN . '/oauth-success.html' => '/passport/oauth/success',
    'https://passport.' . DOMAIN . '/findpassword.html' => '/passport/findpassword',
    'https://passport.' . DOMAIN . '/resetpassword.html' => '/passport/findpassword/reset',
    'https://passport.' . DOMAIN . '/verifypassword.html' => '/passport/findpassword/verify',
    'https://passport.' . DOMAIN . '/resetpassword-success.html' => '/passport/findpassword/success',
    'https://passport.' . DOMAIN . '/api/<action:[\w-]+>' => '/passport/api/<action>',

    //用户中心模块
    'http://member.' . DOMAIN => '/member',
    'http://member.' . DOMAIN . '/<controller:[\w-]+>' => '/member/<controller>',
    'http://member.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>' => '/member/<controller>/<action>',
    'http://member.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>-<id:\d+>.html' => '/member/<controller>/<action>',

    //邀请
    'http://t.' . DOMAIN => '/invite',

    //圈子模块
    'http://group.' . DOMAIN => '/group',
    'http://group.' . DOMAIN . '/group-<id:\d+>.html' => '/group/default/view',
    'http://group.' . DOMAIN . '/topic-<id:\d+>.html' => '/group/topic/view',
    'http://group.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>' => '/group/<controller>/<action>',

    //晒单模块
    'http://share.' . DOMAIN => '/share',
    'http://share.' . DOMAIN . '/detail-<id:\d+>.html' => '/share/default/detail',
    'http://share.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>' => '/share/<controller>/<action>',

    //后台模块
//    'http://admin.' . DOMAIN => '/admin',
//    'http://admin.' . DOMAIN . '/<controller:[\w-]+>' => '/admin/<controller>',
//    'http://admin.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>' => '/admin/<controller>/<action>',

    //帮助中心模块
    'http://help.' . DOMAIN => '/help',
    'http://help.' . DOMAIN . '/<action:[\w-]+>.html' => '/help/default/<action>',
    'http://help.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>.html' => '/help/<controller>/<action>',

    //个人主页模块
    'http://u.' . DOMAIN . '/<id:\d+>' => '/user',
    'http://u.' . DOMAIN . '/<id:\d+>/<action:[\w-]+>' => '/user/default/<action>',
    'http://u.' . DOMAIN . '/<controller:[\w-]+>/<action:[\w-]+>' => '/user/<controller>/<action>',

    //微信模块
    'http://weixin.' . DOMAIN => '/weixin',
    'http://weixin.' . DOMAIN . '/about.html' => '/weixin/help/about',
    'http://weixin.' . DOMAIN . '/problem.html' => '/weixin/help/problem',
    'http://weixin.' . DOMAIN . '/suggestion.html' => '/weixin/help/suggestion',
    'http://weixin.' . DOMAIN . '/passport/registercheck.html' => '/weixin/passport/register-check',
    'http://weixin.' . DOMAIN . '/passport/registerbind.html' => '/weixin/passport/register-bind',
    'http://weixin.' . DOMAIN . '/passport/<action:[\w-]+>.html' => '/weixin/passport/<action>',

    'http://weixin.' . DOMAIN . '/list.html' => '/weixin/list',
    'http://weixin.' . DOMAIN . '/cart.html' => '/weixin/cart',
    'http://weixin.' . DOMAIN . '/cart/payment.html' => '/weixin/cart/payment',
    'http://weixin.' . DOMAIN . '/cart/weixinpay.html' => '/weixin/cart/weixinpay',
    'http://weixin.' . DOMAIN . '/cart/weixinqrpay.html' => '/weixin/cart/weixinqrpay',
    'http://weixin.' . DOMAIN . '/cart/weixinpayok.html' => '/weixin/cart/weixinpayok',
    'http://weixin.' . DOMAIN . '/cart/weixinpayok-<o:[\w\d]+>-<s:[\w\d]+>.html' => '/weixin/cart/weixinpayok',
    'http://weixin.' . DOMAIN . '/cart/iapppayok.html' => '/weixin/cart/iapppayok',
    'http://weixin.' . DOMAIN . '/cart/chinabankpay.html' => '/weixin/cart/chinabankpay',
    'http://weixin.' . DOMAIN . '/shopok.html' => '/weixin/pay/result',
    'http://weixin.' . DOMAIN . '/product/<pid:\d+>.html' => '/weixin/product',
    'http://weixin.' . DOMAIN . '/lottery/<pid:\d+>.html' => '/weixin/product/lottery',
    'http://weixin.' . DOMAIN . '/lottery/BuyDetail-<periodId:\d+>.html' => '/weixin/lottery/buy-detail',

    'http://weixin.' . DOMAIN . '/moreperiod-<pid:\d+>.html' => '/weixin/product/moreperiod',
    'http://weixin.' . DOMAIN . '/buyrecords-<pid:\d+>.html' => '/weixin/product/buyrecords',
    'http://weixin.' . DOMAIN . '/lottery/calresult-<pid:\d+>.html' => '/weixin/product/calresult',
    'http://weixin.' . DOMAIN . '/goodsimgdesc-<pid:\d+>.html' => '/weixin/product/goodsimgdesc',
    'http://weixin.' . DOMAIN . '/limitbuy.html' => '/weixin/limitbuy',
    'http://weixin.' . DOMAIN . '/lottery/m1.html' => '/weixin/lottery',
    'http://weixin.' . DOMAIN . '/ten.html' => '/weixin/ten',


    'http://weixin.' . DOMAIN . '/post/index.html' => '/weixin/post',
    'http://weixin.' . DOMAIN . '/post/detail-<pid:\d+>.html' => '/weixin/post/detail',
    'http://weixin.' . DOMAIN . '/goodspost-<pid:\d+>.html' => '/weixin/post/list',

    'http://weixin.' . DOMAIN . '/payapi/<action:[\w-]+>' => '/weixin/payapi/<action>',


    'http://weixin.' . DOMAIN . '/member/index.html' => '/weixin/member/',
    'http://weixin.' . DOMAIN . '/member/goodsbuylist.html' => '/weixin/member/buylist',
    'http://weixin.' . DOMAIN . '/member/goodsbuydetail-<pid:\d+>.html' => '/weixin/member/buydetail',
    'http://weixin.' . DOMAIN . '/member/orderdetail-<id:\d+>.html' => '/weixin/member/orderdetail',
    'http://weixin.' . DOMAIN . '/member/addressadd-<id:\d+>.html' => '/weixin/member/addressadd',
    'http://weixin.' . DOMAIN . '/member/addressedit-<id:\d+>.html' => '/weixin/member/addressedit',
    'http://weixin.' . DOMAIN . '/member/virtual-addressedit-<id:\d+>.html' => '/weixin/member/virtualaddressedit',
    'http://weixin.' . DOMAIN . '/member/postone-<id:\d+>.html' => '/weixin/member/postone',
    'http://weixin.' . DOMAIN . '/member/post-<id:\d+>.html' => '/weixin/member/post',
    'http://weixin.' . DOMAIN . '/member/postuploadimage.html' => '/weixin/member/post-upload-image',
    'http://weixin.' . DOMAIN . '/member/<action:[\w-]+>.html' => '/weixin/member/<action>',


    'http://weixin.' . DOMAIN . '/userpage/<id:\d+>' => '/weixin/user',



    'http://www.' . DOMAIN . '/' => '/',
];

return array_merge($route, $mobileRoute);