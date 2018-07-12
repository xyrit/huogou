<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 下午4:18
 */
namespace app\modules\api\controllers;

use app\helpers\Brower;
use app\helpers\Code;
use app\helpers\Curl;
use app\helpers\Message;
use app\models\Area;
use app\models\Image;
use app\models\LoginLog;
use app\models\MpUser;
use app\models\Oauth;
use app\models\PkOrders;
use app\models\RechargeOrderDistribution;
use app\models\UserAppInfo;
use app\models\UserSystemMessage;
use app\models\WinShare;
use app\modules\admin\models\Keyword;
use app\modules\passport\models\LoginForm;
use app\modules\passport\models\RegisterForm;
use app\services\Coupon;
use app\services\PkProduct;
use app\services\User;
use app\models\User as UserModel;
use app\validators\MobileValidator;
use yii\base\Exception;
use yii\captcha\CaptchaValidator;
use yii\helpers\Json;
use yii\validators\EmailValidator;
use app\helpers\DateFormat;
use app\models\AppInstall;
use app\models\Config;
use app\models\UserCoupons;

class UserController extends BaseController
{
	public function actions()
	{
		return [
			'captcha' => [
				'class' => 'app\actions\CaptchaAction',
				'maxLength' => 5,
				'minLength' => 5,
				'fontFile' => '../web/img/captcha.ttf'
			],
		];
	}
	
	public function actionCheckToken()
	{
		return $this->userId ? ['code' => 100] : ['code' => 201];
	}
	
	public function actionGetmoney()
	{
		if (!$this->userId) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$baseInfo = \app\models\User::find()
			->select('money,commission,point,experience,pay_password,micro_pay')
			->where(['id' => $this->userId])
			->asArray()
			->one();
		$couponList = Coupon::getUserValidList($this->userId, 'all');
		$data['money'] = $baseInfo['money'] ? $baseInfo['money'] : '0';
		$data['commission'] = $baseInfo['commission'] ? sprintf('%.2f', $baseInfo['commission'] / 100) : '0';
		$data['point'] = $baseInfo['point'] ? $baseInfo['point'] : '0';
		$data['experience'] = $baseInfo['experience'] ?: '0';
		$data['ppwd'] = $baseInfo['pay_password'] ? '1' : '0';
		$data['free'] = $baseInfo['micro_pay'];
		$data['coupon'] = count($couponList);
		
		return $data;
	}
	
	public function actionCheckNickname()
	{
		$request = \Yii::$app->request;
		$name = $request->get('name');
		$model = UserModel::findOne(['nickname' => $name]);
		return $model ? ['state' => 1] : ['state' => 0];
	}
	
	public function actionCheckAccount()
	{

		$request = \Yii::$app->request;
		$account = $request->get('account');
		
		//过滤邮箱 163等
		$keywords = Keyword::findAll(['type' => 3]);
		foreach ($keywords as $k) {
			if (strstr($account, $k['content']) !== false) {
				return ['status' => 3];
			}
		}
		$from = Brower::whereFrom();
		$mobileValidator = new MobileValidator();
		$valid = $mobileValidator->validate($account);
		if ($valid) {
			$model = UserModel::findByPhone($account);
			return $model ? ['state' => 1] : ['state' => 0];
		}
		$emailValidator = new EmailValidator();
		$valid = $emailValidator->validate($account);
		if ($valid) {
			$model = UserModel::findByEmail($account);
			return $model ? ['state' => 1] : ['state' => 0];
		}
		return ['state' => 2];
	}
	
	public function actionCheckEmail()
	{
		$request = \Yii::$app->request;
		$name = $request->get('email');
		$model = UserModel::findByEmail($name);
		return $model ? ['state' => 1] : ['state' => 0];
		
	}
	
	public function actionCheckPhone()
	{
		$request = \Yii::$app->request;
		$name = $request->get('phone');
        if($name){
		$model = UserModel::findByPhone($name);
            if(!$model)
            {
                return ['state' => 0];
            }
        }
        return ['state' => 1];
	}
	
	public function actionSendCode()
	{
		$request = \Yii::$app->request;
		$account = $request->get('account');
		$type = $request->get('type');
		$vcode = $request->get('vcode', '');
		$callback = $request->get('callback', '');
		$referer = $request->referrer;
		if (empty($referer)) {
			if (!Brower::isMobile()) {
				return ['errcode' => 100];
			}
		}
//        else {
//            $parseUrl = parse_url($referer);
//            $domain = GetUrlToDomain($parseUrl['host']);
//            $whiteDomains = [
//                'huogou.com',
//                '5ykd.com',
//                'huogou.dev',
//                'miduogift.com',
//                'jiefangwu.com',
//                'mengmeijj.com',
//                'jixueting.com',
//                'colorfuns.com',
//                'aishacheng.com',
//                'qdyingjiete.com',
//                '5v1.com',
//                'youdiangaoxiao.com',
//                'jinrefu.com',
//            ];
//            if (!in_array($domain, $whiteDomains)) {
//                return ['errcode'=>100];
//            }
//        }
		
		if (strpos($callback, 'jQuery203024445776524953544') === 0) {
			return ['errcode' => 100];
		}
		$send = User::sendCode($account, $type, true, $vcode);
		return $send;
	}
	
	public function actionAppSendCode()
	{
		if (!Brower::isMobile()) {
			return ['errcode' => 100];
		}
		$request = \Yii::$app->request;
		$account = $request->get('account');
		$type = $request->get('type');
		$vcode = $request->get('vcode', '');
		$send = User::sendCode($account, $type, true, $vcode);
		return $send;
	}
	
	public function actionCheckCode()
	{
		$request = \Yii::$app->request;
		$code = $request->get('code');
		$account = $request->get('account');
		$type = $request->get('type');
		
		$check = User::checkCode($code, $account, $type);
		return $check ? ['state' => 1] : ['state' => 0];
	}
	
	public function actionVerifyCode()
	{
		$request = \Yii::$app->request;
		$code = $request->get('code');
		$captchaValidator = new CaptchaValidator();
		$controllerUniqueId = $this->getUniqueId();
		$captchaValidator->captchaAction = '/' . $controllerUniqueId . '/captcha';
		$valid = $captchaValidator->validate($code);
		return $valid ? ['state' => 1] : ['state' => 0];
	}
	
	public function actionCheckLogin()
	{
		if ($this->userId) {
			$token = $this->token;
		} else {
			$token = \app\models\User::createToken();
		}
		return $this->userId ? ['logined' => 1, 'token' => $token] : ['logined' => 0, 'token' => $token];
	}
	
	public function actionLoginUserinfo()
	{
		$request = \Yii::$app->request;
		$account = $request->post('account');
		$password = $request->post('password');
		$loginForm = new LoginForm();
		$loginForm->username = $account;
		$loginForm->password = $password;
		if ($loginForm->validate()) {
			$user = $loginForm->getUser();
			$userInfo = User::allInfo($user->id);
			unset($userInfo['password']);
			unset($userInfo['pay_password']);
			$userInfo['commission'] = sprintf('%.2f', $userInfo['commission'] / 100);
			
			// 充值总额
			$tableId = RechargeOrderDistribution::getTableIdByUserHomeId($userInfo['home_id']);
			$query = RechargeOrderDistribution::findByTableId($tableId)->where(['user_id' => $user->id]);
			$query->andWhere(['=', 'status', RechargeOrderDistribution::STATUS_PAID]);
			$query->andWhere(['<>', 'money', 0]);
			$totalMoney = $query->select('SUM(post_money) as totalMoney')->asArray()->one();
			$rechargeTotal = intval($totalMoney['totalMoney']);
			$userInfo['rechargeTotal'] = $rechargeTotal;
			
			return [
				'code' => 100,
				'userinfo' => $userInfo,
			];
		} else {
			return ['code' => 202];
		}
		
	}
	
	public function actionLogin()
	{
		try {
			$request = \Yii::$app->request;
			$account = $request->post('account');
			$password = $request->post('password');
			
			$clientid = $request->post('clientid');
			$source = $request->post('source');
			$package = $request->post('package');
			$code = $request->post('code');
			$loginForm = new LoginForm();
			$loginForm->username = $account;
			$loginForm->password = $password;
			if ($loginForm->validate()) {
				$user = $loginForm->getUser();
				if ($clientid && $source) {
					UserAppInfo::updateAppInfo($user->id, $clientid, $source, 1);
				}
				
				$ip = \Yii::$app->request->userIP;
				if ($source == 3) {
					$loginType = '4';
					$client = 'IOS客户端';
				} elseif ($source == 4) {
					$loginType = '3';
					$client = '安卓客户端';
				} else {
					$loginType = '5';
					$client = '未知客户端';
				}
				Message::send(10, $user->id, ['account' => $account, 'ip' => $ip, 'client' => $client, 'time' => date('Y-m-d H:i:s')]);
				//添加用户登录日志
				LoginLog::addLog($user->id, 0, $loginType);
				AppInstall::appInstallLog($code, $package, $account);
				$userInfo = User::allInfo($user->id);
				unset($userInfo['password']);
				unset($userInfo['pay_password']);
				$userInfo['commission'] = sprintf('%.2f', $userInfo['commission'] / 100);
				$token = $user->getAccessToken();
				
				//查询新注册送的京东E卡红包是否使用
				$config = Config::getValueByKey('regconfig');
				$reg_red = UserCoupons::findByUserId($user->id)->where(['status' => 0, 'user_id' => $user->id, 'packet_id' => $config['packet_id']])->asArray()->one();
				$userInfo['is_new'] = 0;
				if ($config['status'] == '1' && (!$config['starttime'] || $config['starttime'] < time()) && (!$config['endtime'] || $config['endtime'] > time())) {
					if ($reg_red) {
						$userInfo['is_new'] = 1;
					}
				}


				return ['token' => $token, 'userinfo' => $userInfo, 'code' => 100];
			} else {
				$token = \app\models\User::createToken();
				return ['token' => $token, 'code' => 101, 'errorMsg' => $loginForm->getFirstErrors()];
			}
		} catch (\Exception $e) {
			$token = \app\models\User::createToken();
			return ['token' => $token, 'logined' => 0, 'code' => -1];
		}
		
	}
	
	public function actionLogout()
	{
		$request = \Yii::$app->request;
		$source = $request->get('source');
		if ($this->userId) {
			UserAppInfo::changeStatus($this->userId, 0);
			if ($source == 3) {
				$loginType = '4';
			} elseif ($source == 4) {
				$loginType = '3';
			} else {
				$loginType = '5';
			}
			//添加用户登出日志
			LoginLog::addLog($this->userId, 1, $loginType);
		}
		$token = \app\models\User::createToken();
		return ['code' => 100, 'token' => $token];
		
	}
	
	public function actionRegister()
	{
		try {
			$request = \Yii::$app->request;
			$account = $request->post('account') ?: $request->get('account');
			$password = $request->post('password') ?: $request->get('password');
			$smsCode = $request->post('smscode') ?: $request->get('smscode');
			$source = $request->post('source') ?: $request->get('source');
			$clientid = $request->post('clientid') ?: $request->get('clientid');
			$spreadSource = $request->get('spreadSource') ?: $request->post('package');
			
			$registerForm = new RegisterForm(['scenario' => 'registerCheck']);
			$registerForm->username = $account;
			$registerForm->password = $password;
			$registerForm->confirmPassword = $password;
			$registerForm->smsCode = $smsCode;
			$registerForm->spreadSource = $spreadSource;
			if (in_array($source, [3, 4, 99]) || $source > 99) {  //3-ios,4-android,99-联盟，100-分享出去的链接，101-硬推
				$registerForm->setRegSource($source);
			}
			if ($registerForm->validate()) {
				$user = $registerForm->register();
				if ($clientid && $source) {
					UserAppInfo::updateAppInfo($user->id, $clientid, $source, 1);
				}
				$userInfo = User::allInfo($user->id);
				unset($userInfo['password']);;
				unset($userInfo['pay_password']);
				$token = $user->getAccessToken();


				//查询新注册送的京东E卡红包是否使用
				$config = Config::getValueByKey('regconfig');
				$reg_red = UserCoupons::findByUserId($user->id)->where(['status' => 0, 'user_id' => $user->id, 'packet_id' => $config['packet_id']])->asArray()->one();
				$userInfo['is_new'] = 0;
				if ($config['status'] == '1' && (!$config['starttime'] || $config['starttime'] < time()) && (!$config['endtime'] || $config['endtime'] > time())) {
					if ($reg_red) {
						$userInfo['is_new'] = 1;
					}
				}

				User::destroyCode($account, 1);
				return ['token' => $token, 'userinfo' => $userInfo, 'code' => 100];
			} else {
				$token = \app\models\User::createToken();
				return ['token' => $token, 'code' => 101, 'errorMsg' => $registerForm->getFirstErrors()];
			}
		} catch (\Exception $e) {
			$token = \app\models\User::createToken();
			return ['token' => $token, 'code' => -1, 'errorMsg' => $registerForm->getFirstErrors()];
		}
		
	}
	
	/** 绑定第三方账号        接口失效
	 * @return array
	 */
	public function actionBindThird()
	{
		$request = \Yii::$app->request;
		$openId = $request->get('openid');
		$type = $request->get('thirdtype');
		$account = $request->get('account');
		$pwd = $request->get('pwd');
		$user = \app\models\User::findByAccount($account);
		$valid = $user->validatePassword($pwd);
		if (!$valid) {
			return ['code' => 104, 'msg' => '登录密码错误'];
		}
		if ($openId == '(null)' || mb_strlen($openId, 'utf8') < 8) {
			return ['code' => 103, 'msg' => '信息不完整或错误'];
		}
		//第三方登录绑定
		if ($user && $openId && in_array($type, [1, 2])) {
			$auth = Oauth::findOne(['source_id' => $openId, 'source' => $type, 'user_id' => $user->id]);
			if (!$auth) {
				$auth = new Oauth();
				$auth->source = $type;
				$auth->source_id = $openId;
				$auth->user_id = $user->id;
				return $auth->save(false) ? ['code' => 100, 'msg' => '账号绑定成功'] : ['code' => 102, 'msg' => '账号绑定失败'];
			} else {
				return ['code' => 101, 'msg' => '账号已绑定过'];
			}
		} else {
			return ['code' => 103, 'msg' => '信息不完整或错误'];
		}
		
	}
	
	/** 是否绑定第三方账号登录      接口失效
	 * @return array
	 */
	public function actionIsBindThird()
	{
		$request = \Yii::$app->request;
		$opendId = $request->get('openid');
		$type = $request->get('thirdtype');
		$clientid = $request->get('clientid');
		$source = $request->get('source');
		if ($opendId == '(null)' || mb_strlen($opendId, 'utf8') < 8) {
			return ['state' => 0];
		}
		$oauth = Oauth::find()->where(['source_id' => $opendId, 'source' => $type])->one();
		if ($oauth) {
			$user = \app\models\User::find()->where(['id' => $oauth->user_id])->one();
			$userInfo = User::allInfo($user->id);
			unset($userInfo['password']);
			unset($userInfo['pay_password']);
			$token = $user->getAccessToken();
			if ($clientid && $source) {
				UserAppInfo::updateAppInfo($user->id, $clientid, $source, 1);
			}
			$ip = \Yii::$app->request->userIP;
			if ($source == 3) {
				$loginType = '4';
				$client = 'IOS客户端';
			} elseif ($source == 4) {
				$loginType = '3';
				$client = '安卓客户端';
			} else {
				$loginType = '5';
				$client = '未知客户端';
			}
			Message::send(10, $user->id, ['account' => $userInfo['username'], 'ip' => $ip, 'client' => $client, 'time' => date('Y-m-d H:i:s')]);
			//添加用户登录日志
			LoginLog::addLog($user->id, 0, $loginType);
			$userInfo = User::allInfo($user->id);


			//查询新注册送的京东E卡红包是否使用
			$config = Config::getValueByKey('regconfig');
			$reg_red = UserCoupons::findByUserId($user->id)->where(['status' => 0, 'user_id' => $user->id, 'packet_id' => $config['packet_id']])->asArray()->one();
			$userInfo['is_new'] = 0;
			if ($config['status'] == '1' && (!$config['starttime'] || $config['starttime'] < time()) && (!$config['endtime'] || $config['endtime'] > time())) {
				if ($reg_red) {
					$userInfo['is_new'] = 1;
				}
			}
			return ['state' => 1, 'token' => $token, 'userinfo' => $userInfo, 'code' => 100];
		}
		return ['state' => 0];
	}
	
	/**
	 * 发送站内信
	 */
	public function actionSendMessage()
	{
		$userids = \Yii::$app->request->get('userids');
		$content = \Yii::$app->request->get('content');
		
		$userids = explode(',', $userids);
		foreach ($userids as $userid) {
			$model = new UserSystemMessage();
			$model->to_userid = $userid;
			$model->message = $content;
			$model->created_at = time();
			$model->save();
		}
		
		return ['code' => 100];
	}
	
	/**
	 * 冻结
	 */
	public function actionFreeze()
	{
		/*$userids = \Yii::$app->request->get('userids');
		\app\models\User::updateAll(['status' => 1], ['id' => $userids]);

		return ['code' => 100];*/
	}
	
	/**
	 * 解冻
	 */
	public function actionUnFreeze()
	{
		/*$userids = \Yii::$app->request->get('userids');
		\app\models\User::updateAll(['status' => 0], ['id' => $userids]);

		return ['code' => 100];*/
	}
	
	/**
	 * 找回密码
	 * @param string $account 手机号或者邮箱 只传这个参数就是获取验证码
	 * @param inter $code 验证码
	 * @pwd     string $pwd 新密码
	 * @return rt
	 */
	public function actionFindpassword($account, $code = '', $pwd = null)
	{
		$rt = ['error' => 0, 'msg' => 'ok'];
		if ($code) {
			$sendCode = User::getCode($account, 2);
			if ($sendCode && $sendCode == $code) {
				if ($pwd) {
					$member = new \app\services\Member(['account' => $account]);
					$change = $member->changePassword($pwd);
					if (!$change)$rt = ['error' => 5, 'msg' => '保存失败,请稍后重试!'];
						else User::destroyCode($account, 2);
				} elseif ($pwd === "")
					$rt = ['error' => 2, 'msg' => '密码不可以为空'];
			} else
				$rt = ['error' => 3, 'msg' => '验证码错误'];
		} else {
			$member = new \app\services\Member(['account' => $account]);
			if ($member->id) {
				if (!User::sendCode($account, 2, false)) $rt = ['error' => 4, 'msg' => '发送验证码错误,请稍后重试'];
			} else
				$rt = ['error' => 1, 'msg' => '帐号未找到,请确认是否正确填写'];
		}
		
		return $rt;
	}
	
	
	public function actionInfo()
	{
		$userId = \Yii::$app->request->get('id');
		$userHomeId = \Yii::$app->request->get('home_id');
		if ($userId) {
			$where = ['users.id' => $userId];
		} elseif ($userHomeId) {
			$where = ['users.home_id' => $userHomeId];
		}
		$userInfo = \app\models\User::find()->select('*,users.id id')
			->leftJoin('user_profile as p', 'users.id = p.id')
			->where($where)
			->asArray()
			->one();
		unset($userInfo['password_reset_token']);
		unset($userInfo['token']);
		unset($userInfo['pay_password']);
		unset($userInfo['password']);
		unset($userInfo['money']);
		unset($userInfo['commission']);
		unset($userInfo['point']);
		
		$userInfo['email'] && $userInfo['email'] = User::privateEmail($userInfo['email']);
		$userInfo['phone'] && $userInfo['phone'] = User::privatePhone($userInfo['phone']);
		$userInfo['backup_phone'] && $userInfo['backup_phone'] = User::privatePhone($userInfo['backup_phone']);
		$userInfo['level'] = User::level($userInfo['experience'], 0);
		
		$userInfo['hometownname'] = '';
		$userInfo['livecityname'] = '';
		if ($userInfo['live_city']) {
			$live_city = explode(',', $userInfo['live_city']);
			$area = Area::find(['id' => $live_city])->indexBy('id')->asArray()->all();
			$userInfo['livecityname'] = $area[$live_city[0]]['name'] . $area[$live_city[1]]['name'];
		}
		if ($userInfo['hometown']) {
			$hometown = explode(',', $userInfo['hometown']);
			$area = Area::find(['id' => $hometown])->indexBy('id')->asArray()->all();
			
			$userInfo['hometownname'] = $area[$hometown[0]]['name'] . $area[$hometown[1]]['name'];
		}
		
		return $userInfo;
	}
	
	//用户中奖提醒接口
	function actionLastGoods()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$orders = \app\models\Order::findAll(['user_id' => $this->userId, "status" => 0, "push_msg" => 0]);
		$list = [];
		if ($orders) {
			foreach ($orders as $order) {
				$periodInfo = \app\models\Period::find()->where(['id' => $order->period_id])->andWhere(['<=',
					'result_time', time()])->one();
				$productInfo = \app\models\Product::findOne($order->product_id);
				$userBuyInfo = \app\models\UserBuylistDistribution::findByUserHomeId($this->userInfo['home_id'])
					->where(['user_id' => $this->userId, 'period_id' => $order->period_id])
					->asArray()
					->one();
				if (!$periodInfo) {
					continue;
				}
				$list[] = [
					"order_id" => $order->id,           //订单id
					"product_id" => $order->product_id,           //商品id
					"period_id" => $order->period_id,              //期数id
					'lucky_code' => $periodInfo->lucky_code,      //幸运码
					"period_number" => $periodInfo->period_no,       //商品期数
					"name" => $productInfo->name,                //商品名称
					"price" => sprintf('%.2f', $periodInfo->price),    //'商品价格'
					'user_buy_num' => $userBuyInfo['buy_num'],      //购买次数
					"picture" => $productInfo->picture,          //商品图片
					'brief' => $productInfo->brief,          //商品简介
				];
				
				$order->push_msg = 1;
				$order->save(false);
			}
		}
		$pkOrders = PkOrders::findAll(['user_id' => $this->userId, "status" => 0, "push_msg" => 0]);
		if ($pkOrders) {
			foreach ($pkOrders as $order) {
				$periodInfo = \app\models\PkPeriod::find()->where(['id' => $order->period_id])->one();
				$productInfo = PkProduct::info($order->product_id);
				$userBuyInfo = \app\models\PkUserBuylistDistribution::findByUserHomeId($this->userInfo['home_id'])
					->where(['user_id' => $this->userId, 'period_id' => $order->period_id])
					->asArray()
					->all();
				if (!$periodInfo) {
					continue;
				}
				$list[] = [
					"order_id" => $order->id,           //订单id
					"product_id" => $order->product_id,           //商品id
					"period_id" => $order->period_id,              //期数id
					'lucky_code' => $periodInfo->lucky_code,      //幸运码
					"period_number" => $periodInfo->period_no,       //商品期数
					"name" => '[PK场]' . $productInfo['name'],                //商品名称
					"price" => sprintf('%.2f', $periodInfo->price),    //'商品价格'
					'user_buy_num' => count($userBuyInfo),      //购买次数
					"picture" => $productInfo['picture'],          //商品图片
					'brief' => $productInfo['brief'],          //商品简介
				];
				
				$order->push_msg = 1;
				$order->save(false);
			}
		}
		//生成分享订单信息
        $data=["code" => 200, "msg" => "获取获奖信息成功", "info" => $list, 'info_type' => $pkOrders ? 'pk' : 'yy'] ;
        if($list){
        $sharelist=[];
        foreach ($list as $row)
        {
            $sharelist[]=[
                'picture'=>$row['picture'],
                'price'=>$row['price'],
                'name'=>$row['name'],
                'user_buy_num'=>$row['user_buy_num'],
            ];
        }
        $shareinfo= json_encode($sharelist);

        $winshare= new WinShare();
        $winshare->user_id=$this->userId;
        $winshare->share=$shareinfo;
        $winshare->add_time=time();
        $winshare->save();

        $shareid = $winshare->attributes['id'];
        $data['share']=$shareid;
        }
		if ($orders || $pkOrders) {
			UserAppInfo::updateAll(['new_order_tip' => 0], ['uid' => $this->userId]);
		}


		return $list ? $data:['code' => 404, 'msg' => '没有获奖信息奥'];
	}
	
	//用户中奖订单推送信息
	public function actionPrizeinfo()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$orderId = $request->get('id');
		$order = \app\models\Order::find()->where(['user_id' => $this->userId, "id" => $orderId])->one();
		$periodInfo = \app\models\Period::findOne($order->period_id);
		$productInfo = \app\models\Product::findOne($order->product_id);
		$userBuyInfo = \app\models\UserBuylistDistribution::findByUserHomeId($this->userInfo['home_id'])
			->select('buy_num')
			->where(['user_id' => $this->userId, 'period_id' => $order->period_id])
			->asArray()
			->one();
		$info = [
			"order_id" => $order->id,           //订单id
			"product_id" => $order->product_id,           //商品id
			"period_id" => $order->period_id,              //期数id
			'lucky_code' => $periodInfo->lucky_code,      //幸运码
			"period_number" => $periodInfo->period_number,       //商品期数
			"name" => $productInfo->name,                //商品名称
			"price" => sprintf('%.2f', $periodInfo->price),    //'商品价格'
			"push_msg" => $order->push_msg,//是否已推送
			'user_buy_num' => $userBuyInfo['buy_num'],      //购买次数
			"picture" => $productInfo->picture,          //商品图片
			'brief' => $productInfo->brief,          //商品简介
		];
		if (!$order->push_msg) {
			$order->push_msg = 1;
			$order->save();
		}
		return $info;
	}
	
	/** 中奖提醒
	 * @return array
	 */
	public function actionPrizeTips()
	{
		if ($this->userId) {
			$tips = UserAppInfo::find()->select('new_order_tip,new_act_order_tip')->where(['uid' => $this->userId])->one();
			$newOrderTip = $tips['new_order_tip'];
			$newActOrderTip = $tips['new_act_order_tip'];
		} else {
			$newOrderTip = 0;
			$newActOrderTip = 0;
		}
		
		return [
			'new_order_tip' => $newOrderTip,
			'new_act_order_tip' => $newActOrderTip,
		];
	}
	
	
	/** 是否绑定第三方账号自动登录注册
	 * @return array
	 */
	public function actionIsBind()
	{
		
		$request = \Yii::$app->request;
		$opendId = $request->get('openid');
		$type = $request->get('thirdtype');
		$clientid = $request->get('clientid');
		$source = $request->get('source');
		$nickname = $request->get('nickname');
		$headimg = $request->get('headimg');
		$spread_source = $request->get('spread_source');   //推广id
		
		if ($opendId == '(null)' || mb_strlen($opendId, 'utf8') < 8 || !$nickname || !$headimg) {
			return ['state' => 0];
		}
		$oauth = Oauth::find()->where(['source_id' => $opendId, 'source' => $type])->one();
		if ($oauth) {
			$user = \app\models\User::find()->where(['id' => $oauth->user_id])->one();
			$userInfo = User::allInfo($user->id);
			unset($userInfo['password']);
			unset($userInfo['pay_password']);
			$token = $user->getAccessToken();
			if ($clientid && $source) {
				UserAppInfo::updateAppInfo($user->id, $clientid, $source, 1);
			}
			$ip = \Yii::$app->request->userIP;
			if ($source == 3) {
				$loginType = '4';
				$client = 'IOS客户端';
			} elseif ($source == 4) {
				$loginType = '3';
				$client = '安卓客户端';
			} else {
				$loginType = '5';
				$client = '未知客户端';
			}
			Message::send(10, $user->id, ['account' => $userInfo['id'], 'ip' => $ip, 'client' => $client, 'time' => date('Y-m-d H:i:s')]);
			//添加用户登录日志
			LoginLog::addLog($user->id, 0, $loginType);
			$userInfo = User::allInfo($user->id);

			//查询新注册送的京东E卡红包是否使用
			$config = Config::getValueByKey('regconfig');
			$reg_red = UserCoupons::findByUserId($user->id)->where(['status' => 0, 'user_id' => $user->id, 'packet_id' => $config['packet_id']])->asArray()->one();
			$userInfo['is_new'] = 0;
			if ($config['status'] == '1' && (!$config['starttime'] || $config['starttime'] < time()) && (!$config['endtime'] || $config['endtime'] > time())) {
				if ($reg_red) {
					$userInfo['is_new'] = 1;
				}
			}

			
			return ['state' => 1, 'token' => $token, 'userinfo' => $userInfo, 'code' => 100];
		} else {
			//不存在 执行第三方注册
			$account = '';
			$password = 'dd_hg_123456';
			$nickname = trim($nickname);
			if (empty($nickname)) {
				$nicknameSub = Code::generateShortCode(date('YmdHis') . rand(11111, 99999));
				$nickname = '夺宝' . $nicknameSub;
			}
			$registerForm = new RegisterForm(['scenario' => 'registerCheck']);
			$registerForm->username = $account;
			$registerForm->password = $password;
			$registerForm->confirmPassword = $password;
			$registerForm->nickname = $nickname;
			if ($spread_source) {
				$registerForm->spreadSource = $spread_source;
			}
			if (in_array($source, [3, 4, 99]) || $source > 99) {  //3-ios,4-android,99-联盟，100-分享出去的链接，101-硬推
				$registerForm->setRegSource($source);
			}
			
			$user = $registerForm->register();
			
			if ($clientid && $source) {
				UserAppInfo::updateAppInfo($user->id, $clientid, $source, 1);
			}
			$userInfo = User::allInfo($user->id);
			unset($userInfo['password']);;
			unset($userInfo['pay_password']);
			$userInfo['actoken'] = $user->getAccessToken();
			
			if ($userInfo) {
				$auth = new Oauth();
				$auth->source = $type;
				$auth->source_id = $opendId;
				$auth->user_id = $userInfo['id'];
				$save = $auth->save(false);
				$return = $save ? ['state' => 1, 'token' => $userInfo['actoken'], 'userinfo' => $userInfo, 'code' => 100] : ['code' => 102, 'msg' => '账号注册'];
				echo Json::encode($return);
				
				fastcgi_finish_request();
				
				//将图片保存本地
				$curl = new Curl();
				$headimgBlob = $curl->get($headimg);
				$tempName = Image::generateName() . '.jpg';
				Image::createTempImageByContent($headimgBlob, $tempName, 160, 160);
				$tempFilePath = Image::getTempImageFullPath($tempName, 160, 160);
				$tempFilePath = \Yii::$app->sftp->getSFtpPath($tempFilePath);
				$newName = Image::generateName() . '.jpg';
				Image::createUserFaceImage($tempFilePath, $newName, 0, 0, 160, 160);
				@unlink($tempFilePath);
				\app\models\User::updateAll(['avatar' => $newName], ['id' => $user->id]);
			}
			return ['code' => 102, 'msg' => '账号注册失败'];
		}
		
	}
	
	
	/** 绑定手机
	 * @return array
	 */
	public function actionBindPhone()
	{
		
		$request = \Yii::$app->request;
		$phone = $request->post('phone') ?: $request->get('phone');
		$confirmPassword = $request->post('confirmPassword') ?: $request->get('confirmPassword');
		$password = $request->post('password') ?: $request->get('password');
		$smsCode = $request->post('smscode') ?: $request->get('smscode');
		$user_id = $this->userId;
		
		
		if ($user_id == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$registerForm = new RegisterForm(['scenario' => 'registerCheck']);
		$registerForm->username = $phone;
		$registerForm->password = $password;
		$registerForm->confirmPassword = $confirmPassword;
		$registerForm->smsCode = $smsCode;
		
		
		if ($registerForm->validate()) {
			$rs = $registerForm->bindPhone($user_id);
			if ($rs) {
				$userInfo = User::allInfo($user_id);
                User::destroyCode($phone,1);
				return ['state' => 1, 'userinfo' => $userInfo, 'code' => 100];
			}
		} else {
			$token = \app\models\User::createToken();
			return ['token' => $token, 'code' => 101, 'errorMsg' => $registerForm->getFirstErrors()];
		}
		
		return ['code' => 102, 'msg' => '绑定失败'];
	}
	
}