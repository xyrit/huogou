<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/17
 * Time: 上午9:40
 */
namespace app\queue;

use app\helpers\Rename;
use app\models\AppMessageDistribution;
use app\models\MpUser;
use app\models\NoticeMessage;
use app\models\NoticeTemplate;
use app\models\User;
use app\models\UserAppInfo;
use app\models\UserNotice;
use app\models\UserSystemMessage;
use app\validators\MobileValidator;
use yii\helpers\Json;
use yii\validators\EmailValidator;

class SendMessageQueue extends BaseQueue
{
	
	/** 内置字符替换 与 变量名 对应 关系
	 * @var array
	 */
	public static $replace = [
		'{验证码}' => '{$code}',
		'{会员昵称}' => '{$nickname}',
		'{对方会员昵称}' => '{$oppositeNickname}',
		'{商品名称}' => '{$goodsName}',
		'{快递公司}' => '{$expressCompany}',
		'{快递单号}' => '{$expressNo}',
		'{话题标题}' => '{$topicTitle}',
		'{经验数额}' => '{$experience}',
		'{等级变量}' => '{$level}',
		'{消费排名}' => '{$consumeRank}',
		'{订单号}' => '{$orderNo}',
		'{时间}' => '{$time}',
		'{金额}' => '{$money}',
		'{邮箱}' => '{$email}',
		'{收货地址}' => '{$address}',
		'{商品ID}' => '{$goodsId}',
		'{验证邮箱}' => '{$checkEmail}',
		'{账号}' => '{$account}',
		'{IP}' => '{$ip}',
		'{客户端}' => '{$client}',
		'{手机号}' => '{$phone}',
		'{期数}' => '{$periodNumber}',
		'{晒单驳回原因}' => '{$shareReason}',
		'{活动名称}' => '{$activeName}',
		'{类型}' => 'type',
		'{卡号}' => '{$card}',
		'{密码}' => '{$pwd}'
	];
	
	public function run()
	{
		$args = $this->args;
		$type = $args['type'];
		$to = $args['to'];
		$data = $args['data'];
		$ip = isset($args['ip']) ? $args['ip'] : '';
		$ip = ip2long($ip);
		$this->send($type, $to, $data, $ip);
	}
	
	
	public function send($type, $to, $data, $ip)
	{
		$noticeInfo = $this->noticeInfo($type);
		if (!$noticeInfo) {
			return false;
		}
		
		$status = $noticeInfo['status'];
		if ($status == 0) {
			return false;
		}
		$ways = $noticeInfo['ways'];
		
		$mobileValidator = new MobileValidator();
		$valid = $mobileValidator->validate($to);
		if ($valid && in_array(NoticeTemplate::WAY_SMS, $ways)) {
			
			if ($type == 44) {
				$this->sendVoiceSms($to, $noticeInfo, $data, $ip);
			} else {
				$this->sendSms($to, $noticeInfo, $data, $ip);
			}
			return true;
		}
		$emailValidator = new EmailValidator();
		$valid = $emailValidator->validate($to);
		if ($valid && in_array(NoticeTemplate::WAY_EMAIL, $ways)) {
			$this->sendEmail($to, $noticeInfo, $data, $ip);
			return true;
		}
		
		$user = \app\models\User::find()->where(['id' => $to])->asArray()->one();
		if ($user) {
			$uid = $user['id'];
			$phone = $user['phone'];
			$email = $user['email'];
			$data['__userInfo__'] = $user;
			
			$this->args['from'] = $user['from'];//赋值用户来源 1=伙购 2=滴滴
			if (in_array(NoticeTemplate::WAY_SMS, $ways)) {
				if ($phone) {
					if ($type == 44) {
						$this->sendVoiceSms($phone, $noticeInfo, $data, $ip);
					} else {
						$this->sendSms($phone, $noticeInfo, $data, $ip);
					}
				}
			}
			if (in_array(NoticeTemplate::WAY_EMAIL, $ways)) {
				if ($email) {
					$this->sendEmail($email, $noticeInfo, $data, $ip);
				}
			}
			if (in_array(NoticeTemplate::WAY_SYSMSG, $ways)) {
				$this->sendSysMsg($uid, $noticeInfo, $data, $ip);
			}
			if (in_array(NoticeTemplate::WAY_WECHAT, $ways)) {
				$this->sendWechat($uid, $noticeInfo, $data, $ip);
			}
			if (in_array(NoticeTemplate::WAY_APP, $ways)) {
				$this->sendAppMsg($uid, $noticeInfo, $data, $ip);
			}
			
			return true;
		}
		
		return false;
	}
	
	public function replaceContent($content, $data)
	{
		$replaceContent = strtr($content, static::$replace);
		extract($data);
		$replaceContent = "\$returnContent=\"" . $replaceContent . "\";";
		eval($replaceContent);
		if (!empty($returnContent)) {
			return $returnContent;
		}
		return '';
	}
	
	public function sendSms($phone, $noticeInfo, $data, $ip)
	{
		
		$content = $noticeInfo['smsContent'];
		$desc = $noticeInfo['desc'];
		
		$content = static::replaceContent($content, $data);
		if (empty($content)) {
			return;
		}
		$from = $this->args['from'];
		if ($from == 2) { //来源是滴滴夺宝,用滴滴短信平台发送
			$smsConfig = require(\Yii::getAlias('@app/config/didi_sms.php'));
			$sms = \Yii::createObject($smsConfig);
			
			$content = Rename::replaceText($content);
			$desc = Rename::replaceText($desc);
		} else {
			$sms = \Yii::$app->sms;
		}
		$sendResult = $sms->send($phone, $content);
		
		NoticeMessage::addMessage($phone, 1, $desc, $content, $ip);
		
		try {
			
			$result = $sms->getSendResult($sendResult);
			$key = '__sendSmsErrorNum__';
			if ($result['result'] < 0) {
				$cache = \Yii::$app->cache;
				if ($errInfo = $cache->get($key)) {
					$errInfo[] = [
						'phone' => $phone,
						'ip' => $ip,
						'msg' => $result['message'],
						'time' => date('Y-m-d H:i:s')
					];
					$cache->set($key, $errInfo);
				} else {
					$errInfo = [];
					$errInfo[] = [
						'phone' => $phone,
						'ip' => $ip,
						'msg' => $result['message'],
						'time' => date('Y-m-d H:i:s')
					];
					$cache->set($key, $errInfo);
				}
			}
		} catch (\Exception $e) {
			
		}
	}
	
	//语音验证
	public function sendVoiceSms($phone, $noticeInfo, $data, $ip)
	{
		$content = $noticeInfo['smsContent'];
		$desc = $noticeInfo['desc'];
		
		$content = static::replaceContent($content, $data);
		if (empty($content)) {
			return;
		}
		$from = $this->args['from'];
		if ($from == 2) { //来源是滴滴夺宝,用滴滴短信平台发送
			$smsConfig = require(\Yii::getAlias('@app/config/didi_sms.php'));
			$sms = \Yii::createObject($smsConfig);
			
			$content = Rename::replaceText($content);
			$desc = Rename::replaceText($desc);
		} else {
			$sms = \Yii::$app->sms;
		}
		$sendResult = $sms->sendVoice($phone, $data);
		NoticeMessage::addMessage($phone, 1, $desc, $content, $ip);
		
		
		try {
			$result = $sendResult;
			$key = '__sendSmsErrorNum__';
			if ($result['result'] < 0) {
				$cache = \Yii::$app->cache;
				if ($errInfo = $cache->get($key)) {
					$errInfo[] = [
						'phone' => $phone,
						'ip' => $ip,
						'msg' => $result['message'],
						'time' => date('Y-m-d H:i:s')
					];
					$cache->set($key, $errInfo);
				} else {
					$errInfo = [];
					$errInfo[] = [
						'phone' => $phone,
						'ip' => $ip,
						'msg' => $result['message'],
						'time' => date('Y-m-d H:i:s')
					];
					$cache->set($key, $errInfo);
				}
			}
		} catch (\Exception $e) {
			
		}
	}
	
	public function sendEmail($email, $noticeInfo, $data = [], $ip)
	{
		
		$data['email'] = $email;
		
		$title = $noticeInfo['title'];
		$content = $noticeInfo['emailContent'];
		$desc = $noticeInfo['desc'];
		
		$content = static::replaceContent($content, $data);
		if (empty($title) || empty($content)) {
			return;
		}
		
		$from = $this->args['from'];
		
		if ($from == 2) { //来源是滴滴夺宝
			$emailConfig = require(\Yii::getAlias('@app/config/didi_email.php'));
			$emailComp = \Yii::createObject($emailConfig);
			
			$title = Rename::replaceText($title);
			$content = Rename::replaceText($content);
			$desc = Rename::replaceText($desc);
		} else {
			$emailComp = \Yii::$app->email;
		}
		
		$emailComp->send($email, $title, $content);
		
		NoticeMessage::addMessage($email, 2, $desc, $content, $ip);
		
	}
	
	public function sendSysMsg($uid, $noticeInfo, $data = [], $ip)
	{
		
		$title = $noticeInfo['title'];
		$content = $noticeInfo['sysmsgContent'];
		$desc = $noticeInfo['desc'];
		
		$content = static::replaceContent($content, $data);
		if (empty($title) || empty($content)) {
			return;
		}
		$from = $this->args['from'];
		if ($from == 2) { //来源是滴滴夺宝,替换文字
			$title = Rename::replaceText($title);
			$content = Rename::replaceText($content);
			$desc = Rename::replaceText($desc);
		}
		$user = User::findOne($uid);
		if ($user) {
			$sysMsg = new UserSystemMessage();
			$sysMsg->to_userid = $uid;
			$sysMsg->message = $content;
			$sysMsg->created_at = time();
			$sysMsg->status = 0;
			$sysMsg->save();
			NoticeMessage::addMessage($uid, 3, $desc, $content, $ip);
		}
		
	}
	
	public function sendWechat($uid, $noticeInfo, $data = [], $ip)
	{
		$typeId = $noticeInfo['id'];
		if ($typeId == 10) {
			if (!empty($data['__userInfo__'])) {
				$userInfo = $data['__userInfo__'];
				$protectedStatus = $userInfo['protected_status'];
				if ($protectedStatus == 0) {
					return;
				}
			}
		}
		
		$title = $noticeInfo['title'];
		$content = $noticeInfo['wechatContent'];
		$desc = $noticeInfo['desc'];
		$content = addslashes($content);
		$content = static::replaceContent($content, $data);
		if (empty($title) || empty($content)) {
			return;
		}
		
		$from = $this->args['from'];
		
		if ($from == 2) { //来源是滴滴夺宝
			$wechatConfig = require(\Yii::getAlias('@app/config/didi_wechat.php'));
			$wechatComp = \Yii::createObject($wechatConfig);
			
			$title = Rename::replaceText($title);
			$content = Rename::replaceText($content);
			$desc = Rename::replaceText($desc);
		} else {
			$wechatComp = \Yii::$app->wechat;
		}
		
		$content = strtr($content, ['[time]' => date('Y-m-d H:i:s')]);
		$mpUser = MpUser::findOne(['user_id' => $uid]);
		if (!$mpUser) {
			return;
		}
		if (empty($mpUser->open_id)) {
			return;
		}
		$openId = $mpUser->open_id;
		
		$data = Json::decode($content);
		$data['touser'] = $openId;
		
		$wechatComp->sendTemplateMessage($data);
		NoticeMessage::addMessage($uid, 4, $desc, $content, $ip);
	}
	
	public function sendAppMsg($uid, $noticeInfo, $data = [], $ip)
	{
		$userAppInfo = UserAppInfo::find()->where(['uid' => $uid])->one();
		if (!$userAppInfo || !$userAppInfo->client_id) {
			return;
		}
		$typeId = $noticeInfo['id'];
		$title = $noticeInfo['title'];
		$content = $noticeInfo['appContent'];
		$desc = $noticeInfo['desc'];
		$content = static::replaceContent($content, $data);
		if (empty($title) || empty($content)) {
			return;
		}
		if (!in_array($userAppInfo->source, [3, 4])) {
			return;
		}
		$from = $this->args['from'];
		if ($from == 2) { //来源是滴滴夺宝,替换文字
			$title = Rename::replaceText($title);
			$content = Rename::replaceText($content);
			$desc = Rename::replaceText($desc);
			
			$getuiConfig = require(\Yii::getAlias('@app/config/didi_getui.php'));
			$getui = \Yii::createObject($getuiConfig);
			
			$logo = 'app_icon.png';
		} else {
			$getui = \Yii::$app->getui;
			$logo = 'logo.png';
		}
		
		if ($userAppInfo->status == 1) {
			if (in_array($typeId, [11, 38, 45, 46])) {
				$customInfo = ['type' => 'prize', 'id' => $data['orderNo']];
			} else {
				$customInfo = [];
			}
			if ($userAppInfo->source == 3) {
				//ios发送
				$req = $getui->setTemplate('Transmission', [
					'transmissionType' => '2',//透传消息类型
					'transmissionContent' => Json::encode($customInfo),//透传内容
				])->setAPNPayload([
					'body' => $content,
					'title' => $title,
					'badge' => 0,
					'customMsg' => [
						'url' => $customInfo,
					],
				])->pushOne($userAppInfo->client_id);
			} elseif ($userAppInfo->source == 4) {
				//Android发送
				$req = $getui->setTemplate('Notification', [
					'transmissionType' => '2',//透传消息类型
					'transmissionContent' => Json::encode($customInfo),//透传内容
					'title' => $title,
					'text' => $content,
					'logo' => $logo,
				])->pushOne($userAppInfo->client_id);

				$req = $getui->setTemplate('Transmission', [
					'transmissionType' => '2',//透传消息类型
					'transmissionContent' => Json::encode($customInfo),//透传内容
				])->pushOne($userAppInfo->client_id);
			}
		}
		NoticeMessage::addMessage($uid, 5, $desc, $content, $ip);
		
		$userInfo = $data['__userInfo__'];
		$userHomeId = $userInfo['home_id'];
		
		$appMsg = new AppMessageDistribution($userHomeId);
		$appMsg->user_id = $uid;
		$appMsg->title = $title;
		$appMsg->content = $content;
		$appMsg->view = 0;
		$appMsg->created_at = time();
		$appMsg->save(false);
		
		$userAppAttrs = [
			'new_pm' => 1,
		];
		if ($typeId == 11 || $typeId == 38) {
			$userAppAttrs['new_order_tip'] = 1;
		} elseif ($typeId == 40) {
			$userAppAttrs['new_act_order_tip'] = 1;
		}
		UserAppInfo::updateAll($userAppAttrs, ['uid' => $uid]);
		
	}
	
	/**
	 * @param int $type
	 * @return bool
	 */
	public function noticeInfo($type)
	{
		$notice = NoticeTemplate::find()->where(['id' => $type])->asArray()->one();
		if ($notice) {
			$id = $notice['id'];
			$title = $notice['title'];
			$smsContent = $notice['sms_content'];
			$emailContent = $notice['email_content'];
			$sysmsgContent = $notice['sysmsg_content'];
			$wechatContent = $notice['wechat_content'];
			$appContent = $notice['app_content'];
			$noticeWay = $notice['notice_way'];
			$ways = explode(',', $noticeWay);
			$status = $notice['status'];
			$desc = $notice['desc'];
			return [
				'id' => $id,
				'title' => $title,
				'status' => $status,
				'smsContent' => $smsContent,
				'emailContent' => $emailContent,
				'sysmsgContent' => $sysmsgContent,
				'wechatContent' => $wechatContent,
				'appContent' => $appContent,
				'ways' => $ways,
				'desc' => $desc,
			];
		}
		return [];
	}
	
}