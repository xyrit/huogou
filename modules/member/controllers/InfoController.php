<?php
namespace app\modules\member\controllers;
use app\helpers\Message;
use app\helpers\MyRedis;
use app\models\Area;
use app\models\Image;
use app\models\NoticeMessage;
use app\models\UserAddress;
use app\models\UserInfo;
use app\models\UserLimit;
use app\models\UserNotice;
use app\models\User as ModelUser;
use app\modules\image\models\UploadForm;
use app\models\UserProfile;
use yii\helpers\Json;
use app\services\Member;
use app\services\User;
use yii;
use yii\web\NotFoundHttpException;
use app\models\PointFollowDistribution;
use yii\web\Response;
use yii\helpers\Url;
use yii\captcha\CaptchaValidator;

class InfoController extends BaseController
{
    public $userId;
    public $userInfo;

    public function init()
    {
        parent::init();
        $userId = Yii::$app->user->id;
        $userInfo = User::allInfo($userId);
        $this->userId = $userId;
        $this->userInfo = $userInfo;
    }

    /**
     * 会员个人资料
     */
    public function actionModify()
    {
        $userId = Yii::$app->user->id;

        $user = User::baseInfo($userId);
        $userProfile = UserProfile::findOne(['id' => $userId]);

        if (!empty($userProfile)) {
            $userProfile = yii\helpers\ArrayHelper::toArray($userProfile);
        }

        if (!empty($userProfile)) {
            if (!empty($userProfile['live_city'])) {
                $areas = explode(',', $userProfile['live_city']);
                $userProfile['now_province'] = isset($areas[0]) ? $areas[0] : '';
                $userProfile['now_city'] = isset($areas[1]) ? $areas[1] : '';

                if (!empty($areas)) {
                    $area = Area::find()->where(['id' => $areas])->indexBy('id')->asArray()->all();
                    $userProfile['now_province_name'] = $area[$areas[0]]['name'];
                    $userProfile['now_city_name'] = $area[$areas[1]]['name'];
                }
            }

            if (!empty($userProfile['hometown'])) {
                $areas = explode(',', $userProfile['hometown']);
                $userProfile['old_province'] = isset($areas[0]) ? $areas[0] : '';
                $userProfile['old_city'] = isset($areas[1]) ? $areas[1] : '';
                if (!empty($areas)) {
                    $area = Area::find()->where(['id' => $areas])->indexBy('id')->asArray()->all();
                    $userProfile['old_province_name'] = $area[$areas[0]]['name'];
                    $userProfile['old_city_name'] = $area[$areas[1]]['name'];
                }
            }

            $birthday = explode('-', $userProfile['birthday']);
            $userProfile['year'] = isset($birthday[0]) ? $birthday[0] : '';
            $userProfile['month'] = isset($birthday[1]) ? $birthday[1] : '';
            $userProfile['day'] = isset($birthday[2]) ? $birthday[2] : '';
        }

        $user['phone'] = $user['phone'] ? User::privatePhone($user['phone']) : '';
        $user['email'] = $user['email'] ? User::privateEmail($user['email']) : '';

        return $this->render('modify', ['user' => $user, 'info' => $userProfile]);
    }

    public function actionAddPhone()
    {
        return $this->render('photo');
    }

    /**
     * 会员修改头像
     */
    public function actionPhoto()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $sourceFilePath = $request->post('hidPicUrl');
            $basename = basename($sourceFilePath);
            $cutX = $request->post('cutX');
            $cutY=  $request->post('cutY');
            $width=$request->post('cutW');
            $height=$request->post('cutH');
            Image::createUserFaceImage($sourceFilePath, $basename, $cutX, $cutY, $width, $height);
            $user = \app\models\User::findOne($this->userId);
            $user->avatar = $basename;
            $user->save();
            return Json::encode(['basename'=>$basename]);
        }
        $userId = Yii::$app->user->id;
        $baseInfo = User::baseInfo($userId);
        $userFaces['160'] = Image::getUserFaceUrl($baseInfo['avatar'], 160);
        $userFaces['80'] = Image::getUserFaceUrl($baseInfo['avatar'], 80);
        $userFaces['30'] = Image::getUserFaceUrl($baseInfo['avatar'], 30);

        return $this->render('photo', [
            'userFaces' => $userFaces,
        ]);

    }

    public function actionPhotoUpload()
    {
        try{
        $model = new UploadForm();
        if (Yii::$app->request->isPost) {
            $model->imageFile = yii\web\UploadedFile::getInstanceByName('uploadFace');
            if ($uploadData = $model->uploadTempImg(250,250)) {
            
                // file is uploaded successfully
                $return = "<script type=\"text/javascript\">
if(typeof(window.parent.successUploadImage) == 'function'){window.parent.successUploadImage('".Json::encode($uploadData)."');}
</script>";
                return $return;
            }
        }
        }catch(\Exception $e){ 
                $uploadData = ['error'=>$e->getCode(),'message'=>'服务器错误',"info"=>$e->getMessage()];
                $uploadData = preg_replace("/'/", '', $uploadData);
               $return = "<script type=\"text/javascript\">
            if(typeof(window.parent.successUploadImage) == 'function'){window.parent.successUploadImage('".Json::encode($uploadData)."');}
            </script>";
                
                return $return;
            
        }
    }

    /**
     * 会员收货地址
     */
    public function actionAddress()
    {
        $userId = Yii::$app->user->id;
        $page = 1;
        $perpage = 10;
        $member = new Member(['id' => $userId]);
        $data = $member->getAddressList($page, $perpage);
        return $this->render('address', $data);
    }

    public function actionAddAddress()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $post = $request->post();
            $post['user_id'] = Yii::$app->user->id;
            $newAddress = new UserAddress();
            UserAddress::editAddress($newAddress, $post);
            return $this->redirect('/info/address');
        }
        return $this->render('addaddress');
    }

    public function actionEditAddress()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $addressInfo = UserAddress::findOne(['id' => $id]);

        if ($request->isPost) {
            $post = $request->post();
            UserAddress::editAddress($addressInfo, $post);
            return $this->redirect('/info/address');
        }
        $addressInfo = yii\helpers\ArrayHelper::toArray($addressInfo);
        $area = Area::find(['id' => [$addressInfo['prov'], $addressInfo['city'], $addressInfo['area']]])->indexBy('id')->asArray()->all();

        $addressInfo['provName'] = $area[$addressInfo['prov']]['name'];
        $addressInfo['cityName'] = $area[$addressInfo['city']]['name'];
        $addressInfo['areaName'] = $area[$addressInfo['area']]['name'];
        $data['addressInfo'] = $addressInfo;

        return $this->render('editaddress', $data);
    }

    public function actionDeleteAddress()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        UserAddress::deleteAll(['id' => $id]);
        return $this->actionAddress();
    }

    /**
     * 会员隐私设置
     */
    public function actionSettings()
    {
        $request = Yii::$app->request;
        $userId = Yii::$app->user->id;
        $limitInfo = UserLimit::findOne(['user_id' => $userId]);
        if (empty($limitInfo)) {
            $limitInfo = new UserLimit();
        }
        if ($request->isPost) {
            $post = $request->post();
            UserLimit::editLimit($limitInfo, $post);
            $params['flag'] = 1;
        }
        $params['limitInfo'] = $limitInfo;
        return $this->render('settings', $params);
    }

    /**
     * 会员消息设置
     */
    public function actionNotice()
    {
        $request = Yii::$app->request;
        $userId = Yii::$app->user->id;
        $noticeInfo = UserNotice::findOne(['user_id' => $userId]);
        if (empty($noticeInfo)) {
            $noticeInfo = new UserNotice();
        }
        if ($request->isPost) {
            $post = $request->post();
            UserNotice::editNoitce($noticeInfo, $post);
            return $this->render('notice', ['noticeInfo' => $noticeInfo, 'flag' => 1]);
        }

        return $this->render('notice', ['noticeInfo' => $noticeInfo]);
    }

    /**
     * 安全设置
     * @return string
     */
    public function actionSafety()
    {
        $userId = Yii::$app->user->id;
        $userInfo = User::baseInfo($userId);
        $userInfo['phone'] = $userInfo['phone'] ? User::privatePhone($userInfo['phone']) : '';
        $userInfo['email'] = $userInfo['email'] ? User::privateEmail($userInfo['email']) : '';
        return $this->render('safety', $userInfo);
    }

    /**
     * 登录密码
     * @return string
     */
    public function actionEditPwd()
    {
        return $this->render('editpwd');
    }

    /**
     * 支付密码
     * @return string
     */
    public function actionPayPwd()
    {
        $t = Yii::$app->request->get('t');
        $user = User::baseInfo($this->userId);
        $key = 'sendMsg_' . $t . '_' . $user['phone'];

        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            throw new NotFoundHttpException('非法操作');
        }
        return $this->render('paypwd', ['t' => $t]);
    }

    /**
     * 支付密码验证
     * @return string
     */
    public function actionPayValid()
    {
        $flag = Yii::$app->request->get('flag', 0);
        $userId = Yii::$app->user->id;
        $user = User::baseInfo($userId);
        $user['phone'] = $user['phone'] ? User::privatePhone($user['phone']) : '';
        return $this->render('payvalid', ['user' => $user, 'flag' => $flag]);
    }

    /**
     * 手机短信验证
     **/
    public function actionMicroSendSms()
    {
        $flag = Yii::$app->request->get('flag', 0);
        $user = User::baseInfo($this->userId);
        $user['phone'] = $user['phone'] ? User::privatePhone($user['phone']) : '';
        return $this->render('microsms', [
           'user'=> $user,
           'flag' => $flag,
        ]);
    }

    /**
     * 小额免密码设置
     * @return string
     */
    public function actionPayNotPwd()
    {
        $request = Yii::$app->request;
        $t = $request->get('t');
        $user = User::baseInfo($this->userId);
        $key = 'sendMsg_' . $t . '_' . $user['phone'];

        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            throw new NotFoundHttpException('非法操作');
        }

        $user['phone'] = $user['phone'] ? User::privatePhone($user['phone']) : '';
        $user['t'] = $t;
        return $this->render('paynotpwd', $user);
    }

    /**
     * 关闭小额支付
     */
    public function actionCloseMicroPay()
    {
        $request = Yii::$app->request;
        $model = ModelUser::findOne($this->userId);
        if ($model['micro_pay'] != 0) {
            $model->micro_pay = 0;
            if ($model->save()) return 0;
        } else {
            return 1;
        }
    }

    /**
     * 验证小额支付
     */
    public function actionMicroCheck()
    {
        $uid = Yii::$app->user->id;
        $userInfo = User::baseInfo($uid);
        if(!$userInfo['phone']){
            return 1;
        }elseif(!$userInfo['pay_password']){
            return 2;
        }else{
            return 0;
        }
    }

    /**
     * 修改手机号
     **/
    public function actionEditPhone()
    {
        $phone = User::privatePhone($this->userInfo['phone']);
        return $this->render('editphone', [
            'phone' => $phone,
            'flag' => 2
        ]);
    }

    public function actionNewPhone()
    {
        $t = Yii::$app->request->get('t');
        $user = User::baseInfo($this->userId);
        $key = 'sendMsg_' . $t . '_' . $user['phone'];

        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            throw new NotFoundHttpException('非法操作');
        }

        return $this->render('newphone', ['flag' => 1]);
        /*$request = Yii::$app->request;
        $t = $request->get('t');
        $cache = Yii::$app->cache;
        $jsonInfo = $cache->get($t);
        if ($jsonInfo) {
            $info = Json::decode($jsonInfo);
            if ($info['web'] == 'huogou.com') {
                return $this->render('newphone');
            }
        }else{
            throw new NotFoundHttpException("页面不存在");
        }*/
    }

    public function actionPhone()
    {
        if($this->userInfo['phone']){
            throw new NotFoundHttpException("页面不存在");
        }else{
            if (Yii::$app->request->get('flag') == 1) {
                $t = Yii::$app->request->get('t');
                $user = User::baseInfo($this->userId);
                $key = 'sendMsg_' . $t . '_' . $user['email'];

                $redis = new MyRedis();
                if ($redis->get($key) != 1) {
                    throw new NotFoundHttpException('非法操作');
                }
                return $this->render('phone', ['flag' => 1]);
            }
            $email = User::privateEmail($this->userInfo['email']);
            return $this->render('checkemail', ['email' => $email]);
        }
    }

    public function actionEmail()
    {
        if($this->userInfo['email']){
            throw new NotFoundHttpException("页面不存在");
        }else{
            if (Yii::$app->request->get('flag') == 1) {
                $t = Yii::$app->request->get('t');
                $user = User::baseInfo($this->userId);
                $key = 'sendMsg_' . $t . '_' . $user['phone'];

                $redis = new MyRedis();
                if ($redis->get($key) != 1) {
                    throw new NotFoundHttpException('非法操作');
                }
                return $this->render('email');
            }
            $phone = User::privatePhone($this->userInfo['phone']);
            return $this->render('checkphone', ['phone' => $phone]);
        }
    }

    public function actionNewPhoneComplete()
    {
        return $this->render('newphonecomplete');
    }

    public function actionNewEmail()
    {
        $t = Yii::$app->request->get('t');
        $user = User::baseInfo($this->userId);
        $key = 'sendMsg_' . $t . '_' . $user['email'];

        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            throw new NotFoundHttpException('非法操作');
        }

        return $this->render('newemail', ['flag' => 1]);
    }

    public function actionNewEmailComplete()
    {
        return $this->render('newemailcomplete');
    }

    //发送邮件
    public function actionSendMail()
    {
        $email = Yii::$app->request->get('email');
        $type = Yii::$app->request->get('type');
        $cache = Yii::$app->cache;
        $key = Yii::$app->security->generateRandomString();
        $arr['web'] = 'send';
        $arr['num'] = mt_rand(1000, 9999);

        $host = Yii::$app->request->hostInfo;
        if($email){
            $exist = ModelUser::findByEmail($email);
            if($exist){
                return ['error'=>1, 'url'=>''];
            }else{
                $email = $email;
                $url = $host.'/info/check-mail?on=success&t='.$key;
                $arr['email'] = $email;
            }
        }else{
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $send = User::sendCode($this->userInfo['email'], 2);
            $userInfo = ['account'=>$this->userInfo['email']];
            $key = 'changeemail'. (string)microtime(true) . (string)mt_rand(100000,999999);
            $key = md5($key);
            $cache = \Yii::$app->cache;
            $cache->set($key, $userInfo, 3600);
            $url = Url::to($host.'/info/check-mail?t='.$key);
            return ['error'=>0, 'url'=>$url];
        }

        $jsonInfo = Json::encode($arr);
        $cache->set($key, $jsonInfo, 1800);

        Message::send($type, $email, ['checkEmail' => $url]);
        //$message = '请点击此链接确认邮箱：' . $url;
        //Yii::$app->email->send($email, "伙购网验证码", $message);
        //NoticeMessage::addMessage($email, 2, '邮件发送', $message);
    }



    //检查邮件信息
    public function actionCheckMail()
    {
        $cache = Yii::$app->cache;
        $key = Yii::$app->request->get('t');
        $status = Yii::$app->request->get('on');
        $oldKey = $cache->get($key);
        $jsonInfo = Json::decode($oldKey);
        if($jsonInfo['web'] == 'send'){
            if(isset($status) && $status == 'success'){
                $model = ModelUser::findOne($this->userId);
                // 添加福分
                if (!$model['email']) {
                    $member = new Member(['id' => $this->userId]);
                    $member->editPoint(UserProfile::POINTS_EMAIL, PointFollowDistribution::POINT_PROFILE, "完善个人资料（绑定邮箱）获得福分");
                }
                $model->email = $jsonInfo['email'];
                if($model->save()){
                    return $this->redirect('/info/modify');
                }else{
                    throw new NotFoundHttpException("邮箱已被使用");
                }
            }else{
                return $this->render('newemail');
            }
        }else{
            throw new NotFoundHttpException("页面不存在");
        }
    }

    public function actionEditEmail()
    {
        $request = Yii::$app->request;
        $userInfo = User::baseInfo($this->userId);
        if ($userInfo && !empty($userInfo['email'])) {

            return $this->render('verify', [
                'email' => $userInfo['email'],
                'privacyAccount' => User::privateAccount($userInfo['email'])
            ]);
        }
        //if($request->isPost){
            //$code = $request->post('code');
            //$captchaValidator = new CaptchaValidator();
            //$captchaValidator->captchaAction = '/api/user/captcha';
            //$valid = $captchaValidator->validate($code);
            //$response = \Yii::$app->response;
            //$response->format = Response::FORMAT_JSON;
            //if ($valid) {
                $email = $request->post('email');
                if(isset($email) && $email){
                    $exist = ModelUser::findByEmail($email);
                  if($exist) return ['error'=>2, 'msg'=>'邮箱已存在'];
                    $userInfo = ['email'=>$email];
                }else{
                    $userInfo = ['email'=>$this->userInfo['email']];
                }

                $key = 'email_'. (string)microtime(true) . (string)mt_rand(100000,999999);
                $key = md5($key);
                $cache = \Yii::$app->cache;
                $cache->set($key, $userInfo, 3600);
                if(isset($email) && $email){
                    $url = Url::to(['info/enter-code', 'key'=>base64_encode($key), 'sign'=>1]);
                    User::sendCode($email, 6, false);
                }else{
                    $url = Url::to(['info/enter-code', 'key'=>base64_encode($key)]);
                    User::sendCode($this->userInfo['email'], 5, false);
                }

                $this->redirect($url);

                //return ['error'=>0, 'url'=>$url];
            //}else {
            //    return ['error'=>1, 'msg'=>'验证码不正确'];
            //}
        //}

        //$this->userInfo['email'] = $this->userInfo['email'] ? User::privateEmail($this->userInfo['email']) : '';

        //return $this->render('editemail', [
        //    'userInfo' => $this->userInfo,
        //]);
    }

    public function actionEnterCode()
    {
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $key = $request->post('key');
            $account = $request->post('account');
            $code = $request->post('code');
            $key = base64_decode($key);
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            if ($key) {
                $cache = \Yii::$app->cache;
                $userInfo = $cache->get($key);
                if ($userInfo && !empty($userInfo['email']) && $userInfo['email']==$account) {
                    $sign = $request->get('sign');
                    if(isset($sign) && $sign == 1){
                        $sendCode = User::getCode($account, 6);
                    }else{
                        $sendCode = User::getCode($account, 5);
                    }

                    if ($sendCode && $sendCode==$code) {
                        if(isset($sign) && $sign == 1){
                            $model = ModelUser::findOne($this->userId);
                            $model->email = $account;
                            if($model->save()){
                                return ['error'=>0, 'url'=>Url::to(['info/modify'])];
                            }
                        }else{
                            $key = 'newphone_'. (string)microtime(true) . (string)mt_rand(100000,999999);
                            $key = md5($key);
                            $cache->set($key, $userInfo, 1800);

                            $url = Url::to(['info/reset', 'key'=>base64_encode($key)]);
                            return ['error'=>0, 'url'=>$url];
                        }
                        $cache->delete($key);
                    }
                }
            }
            return ['error'=>1];
        }else{
            $key = $request->get('key');
            $key = base64_decode($key);
            if ($key) {
                $cache = \Yii::$app->cache;
                $userInfo = $cache->get($key);
                if ($userInfo && !empty($userInfo['email'])) {

                    return $this->render('verify', [
                        'email' => $userInfo['email'],
                        'privacyAccount' => User::privateAccount($userInfo['email']),
                        'key' => base64_encode($key),
                    ]);
                }
            }
        }
        throw new NotFoundHttpException('页面不合法');
    }

    public function actionReset()
    {
        $request = \Yii::$app->request;

        $key = $request->get('key');
        $key = base64_decode($key);
        if ($key) {
            $cache = \Yii::$app->cache;
            $userInfo = $cache->get($key);
            if ($userInfo && !empty($userInfo['email'])) {

                return $this->render('newemail', [
                    'key' => base64_encode($key),
                    'account' => $userInfo['email']
                ]);
            }
        }
    }
}
