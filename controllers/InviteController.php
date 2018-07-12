<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/29
 * Time: 下午9:11
 */
namespace app\controllers;

use app\helpers\Brower;
use app\models\Invite;
use app\models\InviteLink;
use app\models\User;
use Yii;
use yii\web\NotFoundHttpException;

class InviteController extends BaseController
{

    public function actionLink()
    {
        $request = Yii::$app->request;
        $code = $request->get('code');
        $periodId = $request->get('pid');
        if (!$code) {
            throw new NotFoundHttpException('页面未找到');
        }
        $inviteLink = InviteLink::findOne(['code' => $code]);
        if (!$inviteLink) {
            throw new NotFoundHttpException('页面未找到');
        }
        $user = User::findOne($inviteLink->user_id);
        if (!$user) {
            throw new NotFoundHttpException('页面未找到');
        }
        Invite::setInviteIdCookie($user->home_id);

        if (Brower::whereFrom() == 2) {
            $appDirUrl = 'http://www.'.DOMAIN.'/didi_app/';
            $spreadDirUrl = 'http://www.'.DOMAIN.'/dd_spread/';
        } else {
            $appDirUrl = 'http://www.'.DOMAIN.'/app/';
            $spreadDirUrl = 'http://www.'.DOMAIN.'/spread/';
        }

        if ($periodId) {
            Invite::setPeriodIdCookie($periodId);
            $url = $appDirUrl . 'free_share.html';
            return $this->redirect($url);
        }
        if (Brower::isMcroMessager()) {
//            return $this->redirect(['/weixin', 's' => 'share-' . $user->home_id]);
            $url = $spreadDirUrl.'list_reg/index.html';
            return $this->redirect($url);
        } elseif (Brower::isMobile()) {
//            return $this->redirect(['/mobile', 's' => 'share-' . $user->home_id]);
            $url = $spreadDirUrl.'list_reg/index.html';
            return $this->redirect($url);
        } else {
            return $this->redirect(['/', 's' => 'share-' . $user->home_id]);
        }
    }

    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            $uid = Yii::$app->user->id;
            $tv['inviteUrl'] = InviteLink::getInviteLink($uid);
        } else {
            $tv['inviteUrl'] = '';
        }
        return $this->render('index', $tv);
    }

}