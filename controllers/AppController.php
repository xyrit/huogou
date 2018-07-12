<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/18
 * Time: 下午12:55
 */

namespace app\controllers;

use app\helpers\Brower;
use Yii;

class AppController extends BaseController
{

    public function actionDown()
    {
        $os = Brower::getDeviceType();

        $from = Brower::whereFrom();
        if ($from == 2) {
            $androidUrl = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.chengguo.didi';
            $iosUrl = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.chengguo.didi';

            $weixinUrl = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.chengguo.didi';
        } else {
            $androidUrl = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.huogou.app';
            $iosUrl = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.huogou.app';

            $weixinUrl = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.huogou.app';
        }

        if (Brower::isMcroMessager()) {
            if ($os == 'android') {
                return $this->redirect($weixinUrl);
            }else if ($os == 'ios') {
                return $this->redirect($weixinUrl);
            }
            return $this->redirect($weixinUrl);
        } elseif(Brower::isMobile()) {
            if ($os == 'android') {
                return $this->redirect($androidUrl);
            }else if ($os == 'ios') {
                return $this->redirect($iosUrl);
            }
            return $this->redirect($androidUrl);
        } else {
            return $this->redirect(['/']);
        }
    }

}