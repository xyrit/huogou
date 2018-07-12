<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/18
 * Time: 下午7:19
 */
namespace app\modules\weixin\controllers;


use app\helpers\DateFormat;
use app\models\CurrentPeriod;
use app\models\Image;
use app\models\Invite;
use app\models\InviteCommission;
use app\models\InviteLink;
use app\models\Order;
use app\models\ShareTopic;
use app\modules\image\models\UploadForm;
use app\services\Member;
use app\services\Period;
use app\services\Product;
use app\services\User;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\models\ShareTopicImage;

class MemberController extends BaseController
{
    public $userId;

    public function init()
    {
        parent::init();
        if (!\Yii::$app->user->isGuest) {
            $this->userId = \Yii::$app->user->id;
        }
    }

    public function beforeAction($action)
    {
        if (\Yii::$app->user->isGuest && $action->id != 'index') {
            \Yii::$app->user->loginRequired();
            \Yii::$app->end();
            return false;
        }
        return true;
    }

    public function actionIndex()
    {
        $userInfo = User::baseInfo($this->userId);
        $userInfo['avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 80);
        return $this->render('index', [
            'userInfo' => $userInfo,
        ]);
    }

    public function actionUnbind()
    {
        $userInfo = User::baseInfo($this->userId);
        $userInfo['avatar'] = Image::getUserFaceUrl($userInfo['avatar'], 80);
        return $this->render('unbind', [
            'userInfo' => $userInfo,
        ]);
    }

    public function actionBuylist()
    {
        return $this->render('buylist');
    }

    public function actionBuydetail()
    {
        $request = \Yii::$app->request;
        $periodId = $request->get('pid');

        $member = new Member(['id' => $this->userId]);

        // 购买信息
        $buyDetail = $member->getBuyDetail($periodId);
        $buyNumber = 0;
        foreach ($buyDetail as &$detail) {
            $detail['buy_time'] = DateFormat::microDate($detail['buy_time']);
            $detail['codes'] = explode(',', $detail['codes']);
            $buyNumber += $detail['buy_num'];
        }

        $periodInfo = Period::info($periodId);
        if (empty($periodInfo)) {
            $periodInfo = CurrentPeriod::find()->where(['id' => $periodId])->asArray()->one();
            $productInfo = Product::info($periodInfo['product_id']);
            $periodInfo['goods_picture'] = $productInfo['picture'];
            $periodInfo['goods_id'] = $productInfo['id'];
            $periodInfo['goods_name'] = $productInfo['name'];
            $periodInfo['period_id'] = $periodInfo['id'];
            $periodInfo['current_period_number'] = $periodInfo['period_number'];

        } else {
            $currentPeriod = CurrentPeriod::find()->where(['product_id' => $periodInfo['goods_id']])->asArray()->one();
            $periodInfo['current_period_number'] = $currentPeriod['period_number'];
        }
        $periodInfo['user_buy_num'] = $buyNumber;
        $periodInfo['product_picture_url'] = Image::getProductUrl($periodInfo['goods_picture'], '200', '200');

        $data['periodInfo'] = $periodInfo;
        $data['buyDetail'] = $buyDetail;

        return $this->render('buydetail', $data);
    }


    public function actionOrderlist()
    {

        return $this->render('orderlist', []);
    }

    public function actionOrderdetail()
    {
        $orderId = \Yii::$app->request->get('id');
        $orderInfo = Order::findOne(['id' => $orderId]);
        $data['orderId'] = $orderId;
        $data['orderInfo'] = $orderInfo;
        $prodcutInfo = \app\models\Product::findOne($orderInfo['product_id']);
        if ($prodcutInfo->delivery_id == 3) {
            return $this->render('orderdetail-card', $data);
        } elseif (in_array($prodcutInfo->delivery_id , [5,6,7,8,9])) {
            return $this->redirect(Url::to(['/app/down']));
        }
        if ($orderInfo['status'] == 0 || $orderInfo['status'] == 6) {
            return $this->render('orderdetail-address', $data);
        } elseif ($orderInfo['status'] >= 1) {
            return $this->render('orderdetail-submit', $data);
        }
    }

    public function actionFavorite()
    {
        return $this->render('favorite', []);
    }

    public function actionAddresslist()
    {
        return $this->render('addresslist', []);
    }

    public function actionAddressadd()
    {
        $orderId = \Yii::$app->request->get('id');
        if ($orderId) {
            $orderInfo = Order::findOne(['id' => $orderId]);
            $prodcutInfo = \app\models\Product::findOne($orderInfo['product_id']);
            if ($prodcutInfo->delivery_id == 2) {
                return $this->render('virtual-addressadd', [
                    'orderId' => $orderId,
                ]);
            }
        }

        return $this->render('addressadd', [
            'orderId' => $orderId,
        ]);

    }

    public function actionAddressedit()
    {
        $addressId = \Yii::$app->request->get('id');

        return $this->render('addressedit', [
            'addressId' => $addressId,
        ]);
    }

    public function actionVirtualaddressedit()
    {
        $addressId = \Yii::$app->request->get('id');

        return $this->render('virtual-addressedit', [
            'addressId' => $addressId,
        ]);
    }

    public function actionSharelist()
    {
        $query = Invite::find()->where(['user_id' => $this->userId]);
        $inviteCount = $query->count();
        $userInfo = User::baseInfo($this->userId);
        $inviteUrl = InviteLink::getInviteLink($this->userId);
        return $this->render('sharelist', [
            'inviteCount' => $inviteCount,
            'commission' => $userInfo['commission'],
            'inviteUrl' => $inviteUrl,
        ]);
    }

    public function actionInvitelist()
    {
        return $this->render('invitelist', [

        ]);
    }

    public function actionCommission()
    {

        $payCommissionSum = InviteCommission::find()->where(['user_id' => $this->userId, 'type' => InviteCommission::TYPE_PAY])->sum('commission');
        return $this->render('commission', [
            'payCommissionSum' => $payCommissionSum
        ]);
    }

    public function actionConsumption($tpl="accounts")
    {
        return $this->render($tpl, []);
    }

    public function actionRecharge()
    {
        $userInfo = \app\models\User::find()->select('money')->where(['id' => $this->userId])->one();
        return $this->render('recharge', [
            'money' => $userInfo['money']
        ]);
    }

    public function actionCardrecharge()
    {
        $userInfo = \app\models\User::find()->select('money')->where(['id' => $this->userId])->one();
        return $this->render('cardrecharge', [
            'money' => $userInfo['money']
        ]);
    }

    public function actionPostone()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $order = Order::findOne(["id"=>$id,"user_id"=>\Yii::$app->user->id]);
        if (!$order ) {
            throw new NotFoundHttpException('不存在');
        }

        return $this->render('postone', []);
    }

    public function actionPostUploadImage()
    {
        $model = new UploadForm();

        if (\Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstanceByName('upload');
            if ($uploadData = $model->uploadShareInfo()) {
                // file is uploaded successfully
                return "<script type=\"text/javascript\">window.parent.successUploadImage('" . Json::encode($uploadData) . "')</script>";
            }
        }
    }

    public function actionAvatar()
    {
        $model = new UploadForm();

        $model->imageFile = UploadedFile::getInstanceByName('avatar');
        $uploadData = $model->uploadAvatar();
        if (!$uploadData['error']) {
            \app\models\User::updateAll(['avatar' => $uploadData['basename']], ['id' => $this->userId]);
        }
        return "<script type=\"text/javascript\">window.parent.successUploadImage('" . Json::encode($uploadData) . "')</script>";
    }

    public function actionSafesetting()
    {
        return $this->render('safesetting', []);
    }

    public function actionLoginpwdupdate()
    {
        $userInfo = User::baseInfo($this->userId);
        if ($userInfo['phone']) {
            $account = User::privatePhone($userInfo['phone']);
        } elseif($userInfo['email']) {
            $account = User::privatePhone($userInfo['email']);
        }
        return $this->render('loginpwdupdate', [
            'account'=>$account
        ]);
    }

    public function actionPaypwdcheck()
    {
        $request = \Yii::$app->request;
        $update = $request->get('update',0);

        $userInfo = User::baseInfo($this->userId);
        $mobile = User::privatePhone($userInfo['phone']);
        $isOpen = $userInfo['pay_password'] ? 1 :0;
        return $this->render('paypwdcheck', [
            'mobile'=>$userInfo['phone'],
            'privateMobile'=>$mobile,
            'isOpen'=>$isOpen,
            'update'=>$update,
        ]);
    }

    public function actionPaypwdupdate()
    {
        $request = \Yii::$app->request;
        $flag = $request->get('flag',0);
        return $this->render('paypwdupdate', [
            'flag'=>$flag,
        ]);
    }

    public function actionSmallpaymoneycheck()
    {
        $request = \Yii::$app->request;
        $update = $request->get('update',0);

        $userInfo = User::baseInfo($this->userId);
        $mobile = User::privatePhone($userInfo['phone']);
        $isOpen = $userInfo['micro_pay'] ? 1 :0;
        return $this->render('smallpaymoneycheck', [
            'mobile'=>$userInfo['phone'],
            'privateMobile'=>$mobile,
            'isOpen'=>$isOpen,
            'update'=>$update,
            'micro'=>$userInfo['micro_pay'],
        ]);
    }

    public function actionSmallpaymoneyupdate()
    {
        $userInfo = User::baseInfo($this->userId);
        return $this->render('smallpaymoneyupdate', [
            'micro'=>$userInfo['micro_pay'],
        ]);
    }

    public function actionMobilecheck()
    {
        $userInfo = User::baseInfo($this->userId);
        if (empty($userInfo['phone'])) {
            return $this->redirect(['/weixin/member/mobilebind']);
        }
        $mobile = User::privatePhone($userInfo['phone']);
        return $this->render('mobilecheck', [
            'mobile'=>$userInfo['phone'],
            'privateMobile'=>$mobile,
            'oldMobile'=>$userInfo['phone'],
        ]);
    }

    public function actionMobilebind()
    {
        $userInfo = User::baseInfo($this->userId);
        return $this->render('mobilebind', [
            'oldMobile'=>$userInfo['phone'],
        ]);
    }

    public function actionEmailcheck()
    {
        $userInfo = User::baseInfo($this->userId);
        if (empty($userInfo['email'])) {
            return $this->redirect(['/weixin/member/emailbind']);
        }
        $email = User::privateEmail($userInfo['email']);
        return $this->render('emailcheck', [
            'email'=>$userInfo['email'],
            'privateEmail'=>$email,
            'oldEmail'=>$userInfo['email'],
        ]);
    }

    public function actionEmailbind()
    {
        $userInfo = User::baseInfo($this->userId);
        return $this->render('emailbind', [
            'oldEmail'=>$userInfo['email'],
        ]);
    }

    public function actionLoginremindset()
    {
        $userInfo = User::baseInfo($this->userId);
        return $this->render('loginremindset', [
            'flag'=>$userInfo['protected_status']
        ]);
    }

    /**
     * 晒单修改
     * @param type $id share_id
     * @return page or json_encode falg =>true|false
     * @throws NotFoundHttpException
     * 
     */
    public function actionPost($id)
    {
        $model = ShareTopic::findOne(["id"=>$id,"user_id"=>\Yii::$app->user->id]);
        if (!$model) {
            throw new NotFoundHttpException('不存在');
        }

        if (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();
            $params['user_id'] = \Yii::$app->user->id;
            $params['period_id'] = $model->period_id;
            $result = ShareTopic::edit($id, $params);
            return json_encode(['flag' => $result]);
        }

        //获取图片
        $pictures = array();
        $shareTopicImage = ShareTopicImage::getImagesByShareTopicId($model->id);
        foreach ($shareTopicImage as $image) {
            $imagePath = Image::getShareInfoFullPath($image, 'share');
            $imagePath = \Yii::$app->sftp->getSFtpPath($imagePath);
            $sourceSize = getimagesize($imagePath);
            $picture['width'] = $sourceSize['0'];
            $picture['height'] = $sourceSize['1'];
            $picture['basename'] = $image;
            $picture['url'] = Image::getShareInfoUrl($image, 'share');
            $pictures[] = $picture;
        }
        return $this->render('postone', ['model' => $model, "pictures" => $pictures]);
    }

    /**
     * 我的晒单
     * @return type
     */
    public function actionPostlist()
    {
        return $this->render('postlist', [
        ]);
    }

    /**
     * 提现记录
     */
    function actionMentionList($tpl="mention-list")
    {
        return $this->render($tpl, [
        ]);
    }
    
    /**
     * 佣金提现
     */
    function actionMentionApply($tpl="mention-apply")
    {
        return $this->render($tpl, [
            
        ]);
    }
    
    /**
     * 福分明细
     */
    function actionUserpoints($tpl="userpoints")
    {
        return $this->render($tpl, [
            
        ]);
    }
    
     /**
     * 转账记录
     */
    function actionTransfer($tpl="transfer")
    {
        return $this->render($tpl, [
            
        ]);
    }
    
}
