<?php

namespace app\modules\api\controllers;

use app\helpers\DateFormat;
use app\models\Invite;
use app\models\InviteCommission;
use app\models\InviteLink;
use app\models\PointFollowDistribution;
use Yii;
use app\services\Member;
use app\models\Withdraw;
use app\services\Thirdpay;

class InviteController extends BaseController
{

	public function actionUrl()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = Yii::$app->request;
		$source = $request->get('source', 3);
		$link = InviteLink::getInviteLink($this->userId);
		if ($source == 3) {
			$banner = 'http://skin.' . DOMAIN . '/img/app/ios/invite_banner_1080.png';
		} elseif ($source == 4) {
			$banner = 'http://skin.' . DOMAIN . '/img/app/android/invite_banner_1242.png';
		} else {
			$banner = 'http://skin.' . DOMAIN . '/img/app/android/invite_banner_1242.png';
		}
		$instruction = [
			'1、每成功邀请一位好友注册并参与伙购后，最多可获得188元现金奖励。在邀请有礼>邀请历史可随时查看佣金明细及邀请记录。',
			'奖励规则',
			'新用户累计消费满50元，获得佣金1元；',
			'新用户累计消费满100元，获得佣金2元；',
			'新用户累计消费满200元，获得佣金3元；',
			'新用户累计消费满500元，获得佣金4元；',
			'新用户累计消费满600元，获得佣金5元；',
			'新用户累计消费满1000元，获得佣金6元；',
			'新用户累计消费满1200元，获得佣金7元；',
			'新用户累计消费满1500元，获得佣金10元；',
			'新用户累计消费满1800元，获得佣金20元；',
			'新用户累计消费满2500元，获得佣金30元；',
			'新用户累计消费满3500元，获得佣金40元；',
			'新用户累计消费满5000元以上，获得佣金60元。',
			'故每成功邀请一位好友注册并参与伙购后，累计最多可获得188元现金奖励。',
			'2、佣金永久有效，满1元即可充值到伙购账户，满100元即可申请提现。',
			'3、推荐您将专属邀请链接分享至微博，微信朋友圈，QQ空间等社交平台，也可直接复制邀请链接发送给您的好友。',
			'4、严禁利用非法手段恶意获取佣金，一经查实，将立即冻结账号，扣除所有佣金。',
		];
		if ($source == 3) {
			$instruction[] = '声明：所有奖品抽奖活动与苹果公司（Apple Inc.）无关';
		}
		$return = [
			'title' => '1元就能买iPhone 6s哦，快去看看吧！',
			'desc' => '只要1元，iPhone 6s拿到手。更多实用商品，全部1元获得，快来点我，点我!',
			'link' => $link,
			'banner' => $banner,
			'instruction' => $instruction,
		];
		return $return;

	}

	public function actionRule()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = Yii::$app->request;
		$source = $request->get('source', 3);
		$detail = [
			'新用户累计消费满50元',
			'新用户累计消费满100元',
			'新用户累计消费满200元',
			'新用户累计消费满500元',
			'新用户累计消费满600元',
			'新用户累计消费满1000元',
			'新用户累计消费满1200元',
			'新用户累计消费满1500元',
			'新用户累计消费满1800元',
			'新用户累计消费满2500元',
			'新用户累计消费满3500元',
			'新用户累计消费满5000元以上',
		];
		$money = ['1', '2', '3', '4', '5', '6', '7', '10', '20', '30', '40', '60'];
		$link = InviteLink::getInviteLink($this->userId);
		if ($source == 3) {
			$banner = 'http://skin.' . DOMAIN . '/img/app/ios/invite_banner_1080.png';
		} elseif ($source == 4) {
			$banner = 'http://skin.' . DOMAIN . '/img/app/android/invite_banner_1242.png';
		} else {
			$banner = 'http://skin.' . DOMAIN . '/img/app/android/invite_banner_1242.png';
		}
		$instruction = [
			'1、每成功邀请一位好友注册并参与伙购后，最多可获得188元现金奖励。在邀请有礼>邀请历史可随时查看佣金明细及邀请记录。',
			'2、佣金永久有效，满1元即可充值到伙购账户，满100元即可申请提现。',
			'3、推荐您将专属邀请链接分享至微博，微信朋友圈，QQ空间等社交平台，也可直接复制邀请链接发送给您的好友。',
			'4、严禁利用非法手段恶意获取佣金，一经查实，将立即冻结账号，扣除所有佣金。',
		];
		if ($source == 3) {
			$instruction[] = '声明：所有奖品抽奖活动与苹果公司（Apple Inc.）无关';
		}
		$return = [
			'title' => '1元就能买iPhone 6s哦，快去看看吧！',
			'desc' => '只要1元，iPhone 6s拿到手。更多实用商品，全部1元获得，快来点我，点我!',
			'link' => $link,
			'banner' => $banner,
			'instruction' => $instruction,
			'detail' => $detail,
			'total' => 188,
			'name' => '奖励规则',
			'money' => $money,
		];
		return $return;

	}

	public function actionHistory()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$memberNums = Invite::find()->where(['user_id' => $this->userId])->count();
		$memberConsumeNums = Invite::find()->where(['user_id' => $this->userId, 'status' => 1])->count();
		$points = PointFollowDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['user_id' => $this->userId, 'type' => PointFollowDistribution::POINT_FRIEND])->sum('point');
		$commission = InviteCommission::find()->where(['user_id' => $this->userId, 'type' => InviteCommission::TYPE_PAY])->sum('commission');
		return [
			'invited_num' => $memberNums,
			'consume_num' => $memberConsumeNums,
			'points' => $points ?: 0,
			'commission' => floatval($commission / 100) ?: 0,
			'has_commission' => floatval($this->userInfo['commission'] / 100),
		];
	}

	/**
	 * 佣金转入伙购账户
	 */
	public function actionRecharge()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = Yii::$app->request;
		$price = $request->get('price', 0);
		$source = $request->get('source', 0);
		$price = floatval($price);
		if ($price <= 0) {
			return ['error' => 1];
		}
		$pay = new Thirdpay();
		$orderId = $pay->createRechargeOrder($this->userId, $price, 1, 4, 'commission', $source, 0);
		$payResult = $pay->pay($orderId, 'commission');
		return $payResult ? ['error' => 0] : ['error' => 1];
	}

	/**
	 * 佣金提现
	 */
	public function actionMentionApply()
	{
		$request = Yii::$app->request;
		$model = new Withdraw();
		if ($model->load($request->get(), '')) {
			$trans = Yii::$app->db->beginTransaction();
			$model->user_id = $this->userId;
			$model->apply_time = time();
			if ($model->validate()) {
				if (!$model->save()) {
					$trans->rollBack();
				} else {
					$user = \app\models\User::findOne($this->userId);
					$user->commission = $user->commission - $model->money * 100;
					if (!$user->save()) {
						$trans->rollBack();
					} else {
						$trans->commit();
						return ["error" => 0, "msg" => '提现申请成功'];
					}
				}
			} else {
				return ['error' => 1, 'msg' => current($model->getFirstErrors())];
			}
		}

		return ['error' => 1, 'msg' => '提现申请失败'];

	}

	/**
	 * 邀请好友列表
	 */
	public function actionInviteList()
	{
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 10);
		$member = new Member(['id' => $this->userId]);
		$inviteList = $member->getInvitedList($page, $perpage);

		return $inviteList;
	}

	/**
	 * 佣金明细
	 */
	public function actionCommissionList()
	{
		$request = Yii::$app->request;
		$page = $request->get('page', 1);
		$perpage = $request->get('perpage', 10);
		$type = $request->get('type', -1);
		$region = $request->get('region', '');
		$startTime = $request->get('start_time', '');
		$endTime = $request->get('end_time', '');

		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime);
		}

		if (!($startTime && $endTime) && $region) {
			list($startTime, $endTime) = DateFormat::formatConditionTime($region);
		}

		$member = new Member(['id' => $this->userId]);
		$inviteList = $member->getCommissionList($type, $startTime, $endTime, $page, $perpage);
		foreach ($inviteList['list'] as &$one) {
			$one['created_time'] = date('Y-m-d H:i:s', $one['created_time']);
		}
		return $inviteList;
	}

	/**
	 * 提现记录
	 * @param $region 日期限制方式
	 * @return type
	 */
	public function actionMentionList()
	{
		$page = Yii::$app->request->get('page', 1);
		$perpage = Yii::$app->request->get('perpage', 10);
		$region = Yii::$app->request->get('region', '');
		$startTime = Yii::$app->request->get('start_time', '');
		$endTime = Yii::$app->request->get('end_time', '');

		if ($startTime && $endTime) {
			$startTime = strtotime($startTime);
			$endTime = strtotime($endTime);
		}

		if (!($startTime && $endTime) && $region) {
			list($startTime, $endTime) = DateFormat::formatConditionTime($region);
		}

		$member = new Member(['id' => $this->userId]);
		$mentionList = $member->getWithdrawList($startTime, $endTime, $page, $perpage);
		foreach ($mentionList['list'] as &$mention) {
			$status = $mention['status'];
			switch ($status) {
				case 0 :
					$statusText = '审核中';
					$statusColor = '#ff6600';
					break;
				case 1 :
					$statusText = '处理中';
					$statusColor = '#00b4ff';
					break;
				case 2 :
					$statusText = '失败' . '(' . $mention['fail_reason'] . ')';
					$statusColor = '#000';
					break;
				case 3 :
					$statusText = '完成';
					$statusColor = '#999';
					break;
				case 4 :
					$statusText = '失败' . '(' . $mention['fail_reason'] . ')';
					$statusColor = '#999';
					break;
				default:
					$statusText = '处理中';
					$statusColor = '#00b4ff';
					break;
			}
			$mention['statusText'] = $statusText;
			$mention['statusColor'] = $statusColor;
		}
		return $mentionList;
	}

	public function actionRank()
	{

	}

}
