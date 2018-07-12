<?php

namespace app\modules\api\controllers;

use app\helpers\MyRedis;
use app\models\Area;
use app\models\NoticeMessage;
use app\models\PointFollowDistribution;
use app\models\User;
use app\models\UserAddress;
use app\models\UserProfile;
use app\models\UserVirtualAddress;
use app\models\WxVirtualAddr;
use app\modules\admin\models\Keyword;
use app\modules\image\models\UploadForm;
use app\services\Member;
use app\validators\MobileValidator;
use Yii;
use app\services\User as ServicesUser;
use app\services\Invite;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

use app\helpers\Upload;
use yii\validators\EmailValidator;
use yii\web\UploadedFile;

class InfoController extends BaseController
{
    public $userInfo;

    public function init()
    {
        parent::init();
        if (!empty($this->userId)) {
            $userInfo = ServicesUser::allInfo($this->userId);
            $this->userInfo = $userInfo;
        }
    }

    public function actionEditProfile()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $params = Yii::$app->request->get();
        $params['live_city'] = !empty($params['now_province']) ? $params['now_province'] . ',' . $params['now_city'] : '';
        $params['hometown'] = !empty($params['old_province']) ? $params['old_province'] . ',' . $params['old_city'] : '';
        $params['nickname'] = trim($params['nickname']);
        if (isset($params['nickname']) && !empty($params['nickname'])) {
            $check = User::checkNickName($params['nickname'], $this->userId);
            if ($check['code'] != 100) {
                return $check;
            }
        }

        if (UserProfile::saveUserProfile($this->userId, $params)) {
            return ['code' => 100, 'msg' => '保存成功'];
        }

        return ['code' => 101, 'msg' => '保存失败'];
    }

    /**
     * 获取用户个人资料
     */
    public function actionUserProfile()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $allInfo = User::find()->select('*,users.id id')
            ->leftJoin('user_profile as p', 'users.id = p.id')
            ->where(['users.id' => $this->userId])
            ->asArray()
            ->one();
        if ($allInfo['hometown']) {
            $split = explode(',', $allInfo['hometown']);
            $hometown = Area::find()->where(['id' => $split])->indexBy('id')->asArray()->all();
            $allInfo['hometownname'] = $hometown[$split[0]]['name'] . ',' . $hometown[$split[1]]['name'];
        }
        if ($allInfo['live_city']) {
            $split = explode(',', $allInfo['live_city']);
            $livecity = Area::find()->where(['id' => $split])->indexBy('id')->asArray()->all();
            $allInfo['livecityname'] = $livecity[$split[0]]['name'] . ',' . $livecity[$split[1]]['name'];
        }
        return $allInfo;
    }

    public function actionEditPwd()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $oldPwd = Yii::$app->request->get('oldPwd');
        $newPwd = Yii::$app->request->get('newPwd');
        $newPwdAgain = Yii::$app->request->get('newPwdAgain');

        $user = User::findOne(['id' => $this->userId]);
        if (!$user->validatePassword($oldPwd)) {
            return ['code' => 101, 'msg' => '原密码输入错误'];
        }

        if ($newPwd != $newPwdAgain) {
            return ['code' => 101, 'msg' => '两次密码不一致'];
        }
        $user->setPassword($newPwd);
        if (!$user->save()) {
            return ['code' => 101, 'msg' => '修改密码失败'];
        }
        Yii::$app->user->logout();
        return ['code' => 100, 'msg' => '修改密码成功'];
    }

    /**
     * 设置/修改支付密码
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPayPwd()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $t = Yii::$app->request->get('t');
        $payPwd = Yii::$app->request->get('payPwd');
        $payPwdAgain = Yii::$app->request->get('payPwdAgain');

        $user = User::findOne(['id' => $this->userId]);
        $key = 'sendMsg_' . $t . '_' . $user['phone'];
        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            return ['code' => 101, 'msg' => '非法操作'];
        }

        if (empty($payPwd)) {
            return ['code' => 101, 'msg' => '密码不能为空'];
        }

        if ($payPwd != $payPwdAgain) {
            return ['code' => 101, 'msg' => '两次密码不一致'];
        }

        if (!User::updateAll(['pay_password' => Yii::$app->security->generatePasswordHash($payPwd)], ['id' => $this->userId])) {
            return ['code' => 101, 'msg' => '修改密码失败'];
        }

        $redis->del($key);
        return ['code' => 100, 'msg' => '修改密码成功'];
    }

    /**
     * 关闭支付密码
     * @return array
     */
    public function actionClosePayPwd()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $t = Yii::$app->request->get('t');
        $user = User::findOne(['id' => $this->userId]);
        $key = 'sendMsg_' . $t . '_' . $user['phone'];
        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            return ['code' => 101, 'msg' => '非法操作'];
        }

        if (!User::updateAll(['pay_password' => '', 'micro_pay' => 0], ['id' => $this->userId])) {
            return ['code' => 101, 'msg' => '关闭失败'];
        }

        $redis->del($key);
        return ['code' => 100, 'msg' => '关闭成功'];
    }

    /*
     * 小额支付免密
     */
    public function actionPayNotPwd()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $t = Yii::$app->request->get('t');
        $user = User::findOne(['id' => $this->userId]);
        $key = 'sendMsg_' . $t . '_' . $user['phone'];
        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            return ['code' => 101, 'msg' => '非法操作'];
        }

        $other = Yii::$app->request->get('other');
        $password = Yii::$app->request->get('password');
        if (is_numeric($other)) {
            $user->micro_pay = $other;
        } elseif ($password) {
            $user->micro_pay = $password;
        }

        if (!$user->save()) {
            return ['code' => 101, 'msg' => '设置失败'];
        }

        $redis->del($key);
        return ['code' => 100, 'msg' => '设置成功'];
    }

    /**
     * 关闭小额支付免密
     * @return array
     */
    public function actionClosePayNotPwd()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $t = Yii::$app->request->get('t');
        $user = User::findOne(['id' => $this->userId]);
        $key = 'sendMsg_' . $t . '_' . $user['phone'];
        $redis = new MyRedis();
        if ($redis->get($key) != 1) {
            return ['code' => 101, 'msg' => '非法操作'];
        }

        if (!User::updateAll(['micro_pay' => 0], ['id' => $this->userId])) {
            return ['code' => 101, 'msg' => '关闭失败'];
        }

        $redis->del($key);
        return ['code' => 100, 'msg' => '关闭成功'];
    }

    /**
     * 发送短信验证码
     * @return array
     */
    public function actionSendMsg()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $user = ServicesUser::baseInfo($this->userId);
        $f = Yii::$app->request->get('f', 0);
        if ($f == 1) {
            $email = Yii::$app->request->get('email');
            //验证手机是否存在
            if (!empty($email)) {
                //过滤邮箱 163等
                $keywords = Keyword::findAll(['type' => 3]);
                foreach ($keywords as $k) {
                    if (strstr($email, $k['content']) !== false) {
                        return ['code' => 101, 'msg' => '暂不支持此类邮箱，建议您使用QQ邮箱或手机号'];
                    }
                }

                $exist = User::find()->where(['and', 'email="'.$email.'"', 'id!='.$this->userId])->one();
                if ($exist) {
                    return ['code' => 101, 'msg' => '该邮箱已存在'];
                }

                if (User::findOne(['email' => $email, 'id' => $this->userId])) {
                    return ['code' => 101, 'msg' => '与原邮箱相同'];
                }
            }
            $type = Yii::$app->request->get('type');
            $email = !empty($email) ? $email : $user['email'];
            \app\services\User::sendCode($email, $type, false);
        } else {
            $phone = Yii::$app->request->get('phone');

            //验证手机是否存在
            if (!empty($phone)) {
                $exist = User::find()->where(['and', 'phone="'.$phone.'"', 'id!='.$this->userId])->one();
                if ($exist) {
                    return ['code' => 101, 'msg' => '该手机号已存在'];
                }

                if (User::findOne(['phone' => $phone, 'id' => $this->userId])) {
                    return ['code' => 101, 'msg' => '与原手机号相同'];
                }
            }

            $type = Yii::$app->request->get('type');
            $phone = !empty($phone) ? $phone : $user['phone'];
            \app\services\User::sendCode($phone, $type, false, '', ['nickname' => $user['username']]);
        }
        return ['code' => 100, 'msg' => '发送成功'];
    }

    /**
     * 验证短信验证码
     * @return array
     */
    public function actionValidMsg()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $user = User::findOne(['id' => $this->userId]);
        $f = Yii::$app->request->get('f', 0);
        if ($f == 1) {
            $email = Yii::$app->request->get('email');
            $type = Yii::$app->request->get('type');
            $email = !empty($email) ? $email : $user['email'];
            $code = Yii::$app->request->get('code');
            $getcode = \app\services\User::getCode($email, $type);
            if ($getcode == $code) {
                \app\services\User::destroyCode($email, $type);
                if (in_array($type, [6])) { //修改绑定手机
                    $model = User::findOne($this->userId);
//                    if ($type == 6 && $model['email']) {
//                        $key = 'sendMsg_6_' . $email;
//                        $redis = new MyRedis();
//                        if (!$redis->get($key)) {
//                            return ['code' => 101, 'msg' => '非法操作'];
//                        }
//                    }
                    // 添加福分
                    if (!$model['email']) {
                        $member = new Member(['id' => $this->userId]);
                        $member->editPoint(UserProfile::POINTS_EMAIL, PointFollowDistribution::POINT_PROFILE, "完善个人资料（绑定邮箱）获得福分");
                    }
                    $model->email = $email;
                    $model->save();
                }
                $key = 'sendMsg_' . $type . '_' . $email;
                $redis = new MyRedis();
                $redis->set($key, 1, 1800);
                return ['code' => 100, 'msg' => '验证成功'];
            }
            return ['code' => 101, 'msg' => '验证失败'];
        } else {
            $phone = Yii::$app->request->get('phone');
            $type = Yii::$app->request->get('type');
            $phone = !empty($phone) ? $phone : $user['phone'];
            $code = Yii::$app->request->get('code');
            $getcode = \app\services\User::getCode($phone, $type);
            if ($getcode == $code) {
                \app\services\User::destroyCode($phone, $type);
                if (in_array($type, [3, 4])) { //修改绑定手机
                    $model = User::findOne($this->userId);
//                    if ($type == 4 && $model['phone']) {
//                        $key = 'sendMsg_4_' . $phone;
//                        $redis = new MyRedis();
//                        if (!$redis->get($key)) {
//                            return ['code' => 101, 'msg' => '非法操作'];
//                        }
//                    }
                    // 添加福分
                    if (!$model['phone']) {
                        $member = new Member(['id' => $this->userId]);
                        $member->editPoint(UserProfile::POINTS_PHONE, PointFollowDistribution::POINT_PROFILE, "完善个人资料（绑定手机）获得福分");
                    }
                    $model->phone = $phone;
                    $model->save();
                }
                $key = 'sendMsg_' . $type . '_' . $phone;
                $redis = new MyRedis();
                $redis->set($key, 1, 1800);
                return ['code' => 100, 'msg' => '验证成功'];
            }
            return ['code' => 101, 'msg' => '验证失败'];
        }
    }

    /**
     * 验证小额免密短信验证码
     * @return array
     */
    public function actionValidMicroMsg()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $type = Yii::$app->request->get('type');
        $code = Yii::$app->request->get('code');
        $cache = Yii::$app->cache;
        $key = $type . '-' . $this->userId;
        $oldKey = $cache->get($key);
        if ($oldKey == $code) {
            $cache->delete($key);
            $cache = Yii::$app->cache;
            $key = Yii::$app->security->generateRandomString();
            $arr['web'] = 'huogou.com';
            $arr['num'] = rand(1000, 9999);
            $jsonInfo = Json::encode($arr);
            $cache->set($key, $jsonInfo, 1800);
            return ['code' => 100, 'msg' => '验证成功','t'=>$key];
        }
        return ['code' => 101, 'msg' => '验证失败'];
    }

    /**
     * 添加收货地址
     * @return array
     */
    public function actionAddAddress()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = Yii::$app->request;
        $params = $request->get();
        $params['user_id'] = $this->userId;

        $addressCount = UserAddress::find()->where(['uid'=>$this->userId])->count();
        $virtualCount = UserVirtualAddress::find()->where(['user_id'=>$this->userId])->count();
        if ($params['addressId'] == 0) {
            if ($addressCount+$virtualCount>=20) {
                return ['code' => 102, 'msg' => '所有地址最多20个'];
            }
            $newAddress = new UserAddress();
        } else {
            $newAddress = UserAddress::findOne(['id' => $params['addressId']]);
        }

        $addrId = UserAddress::editAddress($newAddress, $params);
        if (!$addrId) {
            return ['code' => 101, 'msg' => '信息不完整'];
        }
        return ['code' => 100, 'msg' => '添加成功', 'id'=>$addrId];
    }

    /**
     * 添加虚拟物品收货地址
     * @return array
     */
    public function actionAddVirtualAddress()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $request = Yii::$app->request;
        $addressId = $request->get('addressId', 0);
        $type = $request->get('type');//支付宝:tb QQ:qb 话费:dh 微信 wx
        $account = $request->get('account');
        $name = $request->get('name');
        $nickname = $request->get('nickname');  //微信昵称
        $headimg = $request->get('headimg');  //微信 头像
        $account = trim($account);
        $name = trim($name);


        if (!($type && $account)) {
            return ['code' => 101, 'msg' => '参数有误'];
        }
        /****正式环境参数传错****/
        if($type=='jd')
        {
            $type='dh';
        }
        /****正式环境参数传错****/
        if (!in_array($type, ['tb','qb','dh','wx'])) {
            return ['code' => 101, 'msg' => '参数有误'];
        }
        if ($type==3 && !$name) {
            return ['code' => 102, 'msg' => '姓名不能为空'];
        }
        $addressCount = UserAddress::find()->where(['uid'=>$this->userId])->count();
        $virtualCount = UserVirtualAddress::find()->where(['user_id'=>$this->userId])->count();
        if ($addressId==0) {
            if ($addressCount+$virtualCount>=20) {
                return ['code' => 103, 'msg' => '所有地址最多20个'];
            }
        }

        if ($addressId == 0) {
            $hasAccount = UserVirtualAddress::find()->where(['user_id' => $this->userId, 'type' => $type, 'account'=>$account])->count();
            if ($hasAccount) {
                return ['code' => 104, 'msg' => '已存在的虚拟地址'];
            }
            $newAddress = new UserVirtualAddress();
            $newAddress->user_id = $this->userId;
            $newAddress->created_at = time();
            $newAddress->type = $type;
        } else {
            $newAddress = UserVirtualAddress::findOne(['id' => $addressId, 'type' => $type, 'user_id' => $this->userId]);
            if (!$newAddress) {
                return ['code' => 101, 'msg' => '保存失败'];
            }
            $newAddress->updated_at = time();
        }

        if($type=='wx')   //如果是微信则开启事务
        {
            if(!$nickname || !$headimg)
            {
                return ['code' => 101, 'msg' => '参数有误'];
            }

            $transaction=\Yii::$app->db->beginTransaction();
            $newAddress->account = $account;
            $newAddress->name = $name;
            $newAddress->save();
            $addr_id = $newAddress->attributes['id'];
            $wxvirtualaddr=new WxVirtualAddr();
            $wxvirtualaddr->nickname=$nickname;
            $wxvirtualaddr->headimg=$headimg;
            $wxvirtualaddr->virtual_addr_id=$addr_id;
            $wxvirtualaddr->create_time=time();
            if($wxvirtualaddr->save())
            {
                $transaction->commit();
                return ['code' => 100, 'msg' => '保存成功', 'id'=>$newAddress->id];
            }else{
                $transaction->rollback();
                return ['code' => 101, 'msg' => '保存失败'];
            }
        }
        $newAddress->account = $account;
        $newAddress->name = $name;
        if ($newAddress->save()) {

            return ['code' => 100, 'msg' => '保存成功', 'id'=>$newAddress->id];
        } else {
            return ['code' => 101, 'msg' => '保存失败'];
        }
    }

    /**
     * 获得虚拟物品收货地址
     * @return array
     */
    public function actionGetVirtualAddress()
    {

        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $addressId = Yii::$app->request->get('addressId');

        $userAddress = UserVirtualAddress::findOne(['id' => $addressId]);
        $userAddress = ArrayHelper::toArray($userAddress);
        return $userAddress;
    }

    public function actionGetAddress()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $addressId = Yii::$app->request->get('addressId');
        $userAddress = UserAddress::findOne(['id' => $addressId])->toArray();
        $area = Area::find()->where(['id' => [$userAddress['prov'], $userAddress['city'], $userAddress['area']]])->indexBy('id')->asArray()->all();
        $userAddress['provName'] = $area[$userAddress['prov']]['name'];
        $userAddress['cityName'] = $area[$userAddress['city']]['name'];
        $userAddress['areaName'] = $area[$userAddress['area']]['name'];

        return $userAddress;
    }

    //验证邮箱
    public function actionCheckMailCode()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $code = Yii::$app->request->get('code');
        $cache = Yii::$app->cache;
        $key = 'mail' . '-' . $this->userId;
        $oldKey = $cache->get($key);
        if ($oldKey == $code) {
            $cache->delete($key);
            $key = Yii::$app->security->generateRandomString();
            $arr['web'] = 'send';
            $arr['num'] = rand(1000, 9999);
            $jsonInfo = Json::encode($arr);
            $cache->set($key, $jsonInfo, 1800);
            return ['code' => 100, 'msg' => '验证成功', 't'=>$key];
        }
        return ['code' => 101, 'msg' => '验证失败'];
    }

    /**
     * 验证昵称
     */
    public function actionCheckNickname()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $nickname = Yii::$app->request->get('nickname');
        return User::checkNickName($nickname, $this->userId);
    }

    /**
     * 登录保护
     */
    public function actionLoginProtected()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $user = User::findOne($this->userId);

        if ($user['protected_status'] == 1) {
            User::updateAll(['protected_status' => 0], ['id' => $this->userId]);
            return ['code' => 100, 'msg' => '关闭成功'];
        } else {
            User::updateAll(['protected_status' => 1], ['id' => $this->userId]);
            return ['code' => 100, 'msg' => '开启成功'];
        }
    }

    /**
     * 获取实物和虚拟收货地址列表
     */
    public function actionAllAddressList()
    {

        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $member = new Member(['id' => $this->userId]);
        $addressList = $member->getAddressList(1, 20);
        $virtualAddressList = $member->getVirtualAddressList(null,1,20);
        $alipayList = [];
        $phoneList = [];
        $qqList = [];
        $wxList = [];
        foreach($virtualAddressList['list'] as $virtual) {
            if ($virtual['type']=='tb') {
                $alipayList [] = $virtual;
            } elseif ($virtual['type']=='dh') {
                $phoneList [] = $virtual;
            } elseif ($virtual['type']=='qb') {
                $qqList [] = $virtual;
            }
            elseif ($virtual['type']=='wx') {

                //查询微信信息User::find()->where(['name' => '小伙儿'])->one();
                $wxinfo=WxVirtualAddr::find()->where(['virtual_addr_id'=>$virtual['id']])->one();
                $virtual['nickname']=$wxinfo['nickname'];
                $virtual['headimg']=$wxinfo['headimg'];
                $wxList [] = $virtual;
            }
        }
        $virtualAddressList['list'] = [];
        $virtualAddressList['list'] = array_merge($alipayList, $phoneList, $qqList,$wxList);
        $return = [
            'goods' => $addressList,
            'virtuals' => $virtualAddressList,
        ];
        return $return;
    }

    /**
     * 获取收货地址列表
     */
    public function actionAddressList()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $page = Yii::$app->request->get('page', 1);
        $perpage = Yii::$app->request->get('perpage', 10);
        $member = new Member(['id' => $this->userId]);
        $data = $member->getAddressList($page, $perpage);
        return $data;
    }

    /**
     * 获取虚拟收货地址列表
     */
    public function actionVirtualAddressList()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $type = Yii::$app->request->get('type');
        $page = Yii::$app->request->get('page', 1);
        $perpage = Yii::$app->request->get('perpage', 5);
        $member = new Member(['id' => $this->userId]);
        $data = $member->getVirtualAddressList($type, $page, $perpage);
        return $data;
    }

    /**
     * 登录用户的信息
     */
    public function actionUserInfo()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $userInfo = \app\services\User::allInfo($this->userId);
        unset($userInfo['password_reset_token']);
        unset($userInfo['token']);
        unset($userInfo['pay_password']);
        unset($userInfo['password']);
        return ['userinfo'=>$userInfo];
    }

    /** 用户上传头像
     * @return array
     */
    public function actionAvatar()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $model = new UploadForm();

        $model->imageFile = UploadedFile::getInstanceByName('avatar');
        $uploadData = $model->uploadAvatar();
        if ($uploadData['error']==0) {
            $save = User::updateAll(['avatar'=>$uploadData['basename']],['id'=>$this->userId]);
            return $save ? $uploadData : ['error'=>1,'message'=>'更新头像失败'];
        }
        return $uploadData;

    }

    public function actionChangeNickname()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $nickname = Yii::$app->request->get('nickname');
        $nickname = trim($nickname);
        $check = User::checkNickName($nickname,$this->userId);
        if ($check['code']!='100') {
            return $check;
        }
        $member = new Member(['id'=>$this->userId]);
        $update = $member->editNickName($nickname);
        return $update ? ['code'=>100] : ['code'=>101,'msg'=>'修改昵称失败'];
    }

    /**
     * 是否开启支付密码
     * @return array
     */
    public function actionCheckPayPwd()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $user = User::findOne($this->userId);

        if (empty($user['pay_password'])) {
            return ['code' => 101];
        }

        return ['code' => 100];
    }

    /**
     * 删除收货地址
     * @return mixed
     */
    public function actionDeleteAddress()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = Yii::$app->request;
        $id = $request->get('addressId');
        $delete = UserAddress::deleteAll(['id' => $id, 'uid' => $this->userId]);
        return $delete ? ['code' => 100, 'msg' => '删除成功'] : ['code' => 101, 'msg' => '删除失败'];
    }

    /**
     * 删除虚拟收货地址
     * @return mixed
     */
    public function actionDeleteVirtualAddress()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = Yii::$app->request;
        $id = $request->get('addressId');
        $delete = UserVirtualAddress::deleteAll(['id' => $id, 'user_id' => $this->userId]);
        return $delete ? ['code' => 100, 'msg' => '删除成功'] : ['code' => 101, 'msg' => '删除失败'];
    }

    public function actionValidEmail()
    {
        $request = \Yii::$app->request;
        $email = $request->get('email');

        //过滤邮箱 163等
        $keywords = Keyword::findAll(['type' => 3]);
        foreach ($keywords as $k) {
            if (strstr($email, $k['content']) !== false) {
                return ['code' => 101, 'msg' => '暂不支持此类邮箱，建议您使用QQ邮箱或手机号'];
            }
        }
    }

    /** 安全设置信息
     * @return array
     */
    public function actionSafeSetting()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $baseInfo = \app\services\User::baseInfo($this->userId);
        $settingInfo = [];
        $settingInfo['loginPwd'] = true;
        $settingInfo['payPwd'] = $baseInfo['pay_password'] ? true : false;
        $settingInfo['smallPayMoney'] = $baseInfo['micro_pay'];
        $settingInfo['phone'] = [
            'flag'=>$baseInfo['phone'] ? true : false,
            'val'=>\app\services\User::privatePhone($baseInfo['phone']),
            'phone'=>$baseInfo['phone'],
        ];
        $settingInfo['email'] = [
            'flag'=>$baseInfo['email'] ? true : false,
            'val'=>\app\services\User::privateEmail($baseInfo['email']),
            'email'=>$baseInfo['email'],
        ];
        $settingInfo['loginRemind'] = $baseInfo['protected_status'] ? true : false;
        return $settingInfo;
    }


    /** 开启支付密码
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionOpenPayPassword()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $request = \Yii::$app->request;
        $payPwd = $request->get('paypwd');
        if (!preg_match('/\d{6}/',$payPwd,$match)) {
            return ['code' => 104, 'msg' => '支付密码由6位数字组成'];
        }
        $user = User::findOne($this->userId);
        if (empty($user->phone)) {
            return ['code' => 101, 'msg' => '未绑定手机'];
        }
        if (!\app\services\User::isCheckedCode($user->phone,8)) {
            return ['code' => 102, 'msg' => '非法操作'];
        }

        $user->pay_password = Yii::$app->security->generatePasswordHash($payPwd);
        if ($user->save()) {
            \app\services\User::destroyCode($user->phone,8);
            return ['code' => 100, 'msg' => '设置支付密码成功'];
        }
        return ['code'=>103,'msg'=>'设置支付密码失败'];

    }

    /** 更改支付密码
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdatePayPassword()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $request = \Yii::$app->request;
        $payPwd = $request->get('paypwd');
        if (!preg_match('/\d{6}/',$payPwd,$match)) {
            return ['code' => 104, 'msg' => '支付密码由6位数字组成'];
        }
        $user = User::findOne($this->userId);
        if (empty($user->phone)) {
            return ['code' => 101, 'msg' => '未绑定手机'];
        }
        if (!\app\services\User::isCheckedCode($user->phone,9)) {
            return ['code' => 102, 'msg' => '非法操作'];
        }

        $user->pay_password = Yii::$app->security->generatePasswordHash($payPwd);
        if ($user->save()) {
            \app\services\User::destroyCode($user->phone,9);
            return ['code' => 100, 'msg' => '更改支付密码成功'];
        }
        return ['code'=>103,'msg'=>'更改支付密码失败'];
    }

    /** 关闭支付密码
     * @return array
     */
    public function actionClosePayPassword()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $user = User::findOne($this->userId);
        if (empty($user->phone)) {
            return ['code' => 101, 'msg' => '未绑定手机'];
        }
        if (!\app\services\User::isCheckedCode($user->phone,34)) {
            return ['code' => 102, 'msg' => '非法操作'];
        }
        $user->pay_password = '';
        $user->micro_pay = 0;
        if ($user->save()) {
            \app\services\User::destroyCode($user->phone,34);
            return ['code' => 100, 'msg' => '关闭支付密码成功'];
        }
        return ['code'=>103,'msg'=>'关闭支付密码失败'];
    }

    public function actionOpenSmallPay()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $request = \Yii::$app->request;
        $micro = $request->get('smallpay');
        $user = User::findOne($this->userId);
        if ($micro<=0) {
            return ['code' => 105, 'msg' => '小额免密必须为正整数'];
        }
        if (empty($user->phone)) {
            return ['code' => 101, 'msg' => '未绑定手机'];
        }
        if (empty($user->pay_password)) {
            return ['code' => 104, 'msg' => '未设置支付密码'];
        }
        if (!\app\services\User::isCheckedCode($user->phone,35)) {
            return ['code' => 102, 'msg' => '非法操作'];
        }

        $user->micro_pay = intval($micro);
        if ($user->save()) {
            \app\services\User::destroyCode($user->phone,35);
            return ['code' => 100, 'msg' => '设置小额免密成功'];
        }
        return ['code'=>103,'msg'=>'设置小额免密失败'];

    }

    public function actionUpdateSmallPay()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $request = \Yii::$app->request;
        $micro = $request->get('smallpay');
        $user = User::findOne($this->userId);
        if ($micro<=0) {
            return ['code' => 105, 'msg' => '小额免密必须为正整数'];
        }
        if (empty($user->phone)) {
            return ['code' => 101, 'msg' => '未绑定手机'];
        }
        if (empty($user->pay_password)) {
            return ['code' => 104, 'msg' => '未设置支付密码'];
        }
        if (!\app\services\User::isCheckedCode($user->phone,37)) {
            return ['code' => 102, 'msg' => '非法操作'];
        }

        $user->micro_pay = intval($micro);
        if ($user->save()) {
            \app\services\User::destroyCode($user->phone,37);
            return ['code' => 100, 'msg' => '修改小额免密成功'];
        }
        return ['code'=>103,'msg'=>'修改小额免密失败'];
    }

    public function actionCloseSmallPay()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $user = User::findOne($this->userId);
        if (empty($user->phone)) {
            return ['code' => 101, 'msg' => '未绑定手机'];
        }
        if (empty($user->pay_password)) {
            return ['code' => 104, 'msg' => '未设置支付密码'];
        }


        $user->micro_pay = 0;
        if ($user->save()) {
            return ['code' => 100, 'msg' => '关闭小额免密成功'];
        }
        return ['code'=>103,'msg'=>'关闭小额免密失败'];
    }

    public function actionBindMobile()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $mobile = $request->get('mobile');
        $code = $request->get('code');

        $validator = new MobileValidator();
        $valid = $validator->validate($mobile);
        if (!$valid) {
            return ['code' => 102, 'msg' => '手机格式不正确'];
        }
        $mobileUser = User::findOne(['phone'=>$mobile]);
        if ($mobileUser) {
            return ['code' => 104, 'msg' => '手机已被占用'];
        }

        $user = User::findOne($this->userId);
        if (!empty($user->phone)) {
            return ['code' => 103, 'msg' => '已经绑定手机'];
        }

        if (!\app\services\User::checkCode($code,$mobile,4)) {
            return ['code' => 104, 'msg' => '验证码错误'];
        }

        $user->phone = $mobile;
        if ($user->save()) {
            \app\services\User::destroyCode($user->phone,4);
            return ['code' => 100, 'msg' => '绑定手机成功'];
        }

        return ['code' => 101, 'msg' => '绑定手机失败'];
    }

    public function actionUpdateMobile()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $mobile = $request->get('mobile');
        $code = $request->get('code');

        $validator = new MobileValidator();
        $valid = $validator->validate($mobile);
        if (!$valid) {
            return ['code' => 102, 'msg' => '手机格式不正确'];
        }
        $mobileUser = User::findOne(['phone'=>$mobile]);
        if ($mobileUser) {
            return ['code' => 103, 'msg' => '手机已被占用'];
        }
        if (!\app\services\User::checkCode($code,$mobile,4)) {
            return ['code' => 104, 'msg' => '验证码错误'];
        }
        $user = User::findOne($this->userId);
        if (!\app\services\User::isCheckedCode($user->phone,3)) {
            return ['code' => 105, 'msg' => '非法操作'];
        }

        $oldMobile = $user->phone;
        $user->phone = $mobile;
        if ($user->save()) {
            \app\services\User::destroyCode($oldMobile,3);
            \app\services\User::destroyCode($mobile,4);
            return ['code' => 100, 'msg' => '修改手机成功'];
        }

        return ['code' => 101, 'msg' => '修改手机失败'];
    }

    public function actionBindEmail()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $email = $request->get('email');
        $code = $request->get('code');

        $validator = new EmailValidator();
        $valid = $validator->validate($email);
        if (!$valid) {
            return ['code' => 102, 'msg' => '邮箱格式不正确'];
        }
        $emailUser = User::findOne(['email'=>$email]);
        if ($emailUser) {
            return ['code' => 104, 'msg' => '邮箱已被占用'];
        }

        $user = User::findOne($this->userId);
        if (!empty($user->email)) {
            return ['code' => 103, 'msg' => '已经绑定邮箱'];
        }

        if (!\app\services\User::checkCode($code,$email,6)) {
            return ['code' => 104, 'msg' => '验证码错误'];
        }

        $user->email = $email;
        if ($user->save()) {
            \app\services\User::destroyCode($user->email,6);
            return ['code' => 100, 'msg' => '绑定邮箱成功'];
        }

        return ['code' => 101, 'msg' => '绑定邮箱失败'];
    }

    public function actionUpdateEmail()
    {

        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $email = $request->get('email');
        $code = $request->get('code');

        $validator = new EmailValidator();
        $valid = $validator->validate($email);
        if (!$valid) {
            return ['code' => 102, 'msg' => '邮箱格式不正确'];
        }
        $emailUser = User::findOne(['email'=>$email]);
        if ($emailUser) {
            return ['code' => 104, 'msg' => '邮箱已被占用'];
        }

        if (!\app\services\User::checkCode($code,$email,6)) {
            return ['code' => 104, 'msg' => '验证码错误'];
        }
        $user = User::findOne($this->userId);
        if (!\app\services\User::isCheckedCode($user->email,5)) {
            return ['code' => 105, 'msg' => '非法操作'];
        }
        $oldEmail = $user->email;
        $user->email = $email;
        if ($user->save()) {
            \app\services\User::destroyCode($oldEmail,5);
            \app\services\User::destroyCode($email,6);
            return ['code' => 100, 'msg' => '修改邮箱成功'];
        }

        return ['code' => 101, 'msg' => '修改邮箱失败'];
    }

    public function actionLoginRemind()
    {
        if ($this->userId == 0) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        $request = \Yii::$app->request;
        $open = $request->get('open');
        $user = User::findOne($this->userId);
        $user->protected_status = $open ? 1 : 0;
        return $user->save() ? ['code' => 100, 'msg' => '成功'] : ['code' => 101, 'msg' => '失败'];
    }


}
