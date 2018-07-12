<?php
/**
 * @category  huogou.com
 * @name  AppConfig
 * @version 1.0
 * @date 2015-12-29
 * @author  keli <liwanglai@gmail.com>
 *
 */
namespace app\modules\api\controllers;


use app\models\AppMessageDistribution;
use app\models\Invite;
use app\models\InviteLink;
use yii;
use app\models\AppConfig;
use app\models\AppInstall;
use app\models\SendMessage;
use app\models\User;
use app\models\NoticeRead;
use app\models\UserAppInfo;
use app\models\UserSystemMessage;
use app\helpers\Brower;
use yii\helpers\Json;
use app\services\Coupon;

//app数据获取控制器
class AppController extends BaseController
{
	public $os = '';
	
	public function init()
	{
		parent::init();
		$source = Yii::$app->request->get('source', '');
		if ($source == '3') {
			$this->os = 'ios';
		} else if ($source == '4') {
			$this->os = 'android';
		} else {
			$this->os = Yii::$app->request->get('os', 'android');
		}
	}
	
	// 开机图片
	public function actionOpenpic()
	{
		$result = AppConfig::find()->where(["from" => $this->from, "type" => "image", "system" => $this->os, "status" => 1])->orderBy("sort asc")->all();
		$rt = [];
		foreach ($result as $model) {
			$data = Json::decode($model->content);
			$t = date("Y-m-d H:i:s");
			if (($data['start_time'] and $t < $data['start_time']) || ($data['end_time'] and $t > $data['end_time'])) continue;
			$rt[] = array(
				"name" => $data['image_title'],
				"src" => $data['image_src'],
				"link" => $data['image_link'],
			);
		}
		if (!isset($rt[0])) exit("{}");
		return $rt[0];
//        
//         $rt = AppConfig::findOne(["type"=>"image","system"=>$this->os,"status"=>1]);
//         if(! $rt) return [];
//         $rt->open_images =join(",",preg_split("/[\r\n,\s]+/",$rt->open_images));
//         
//         return $rt->data;
	}
	
	//版本更新
	public function actionUpdate()
	{
		$model = AppConfig::find()->where(["from" => $this->from, "type" => "upgrade", "system" => $this->os, "status" => 1])->orderBy('id desc')->one();
		return $model ? Json::decode($model->content) : exit("{}");
	}
	
	//分享设置
	public function actionShare()
	{
		$request = Yii::$app->request;
		$uid = $request->get('uid');
		
		$result = AppConfig::find()->where(["from" => $this->from, "type" => "share", "system" => $this->os, "status" => 1])->orderBy("sort desc")->all();
		$list = array();
		foreach ($result as $key => $model) {
			$data = Json::decode($model->content);
			if (($data['share_type'] == 'invite' || $data['share_type'] == 'free') && $uid) {
				$data['share_link'] = InviteLink::getInviteLink($uid);
				$list[$data['share_type']] = $data;
			} else {
				$list[$data['share_type']] = $data;
			}
		}
		return $list;
	}
	
	//支付接口
	public function actionSdk()
	{
		$rt = [];
		$list = AppConfig::find()->where(["from" => $this->from, "type" => "sdk", "status" => 1, "system" => $this->os])->orderBy("sort desc")->all();
		foreach ($list as $i => $p)
			$rt[] = Json::decode($p->content);
		
		return $rt;
	}
	
	/**
	 * 启动记录唯一标示
	 * @return [type] [description]
	 */
	public function actionStart()
	{
		$request = Yii::$app->request;
		$code = $request->post('code');
		$source = $request->post('package');
		$rs = AppInstall::appInstallLog($code, $source);
		return array('code' => $rs);
	}
	
	//第三方登录开关
	public function actionLogin()
	{
		$model = AppConfig::findOne(["from" => $this->from, "type" => "login", "system" => $this->os, "status" => 1]);
		$content = Json::decode($model['content'], true);
		$result = [];
		if (!isset($content['version']) || $content['version'] != $this->version) {
			$result = $content;
		} else {
			$result['login_qq'] = 0;
			$result['login_wechat'] = 0;
		}
		return $model ? $result : false;
	}
	
	//虚拟商品列表
	public function actionVirtual()
	{
		$rt = [];
		$list = \app\models\AppConfig::find()->where(["from" => $this->from, "type" => "virtual", "status" => 1, "system" => $this->os])->orderBy("sort desc")->all();
		foreach ($list as $model) {
			$data = Json::decode($model->content);
			$rt[] = array(
				"name" => $data['virtual_name'],
				"type" => $data['virtual_type'],
				"icon" => "http://skin." . DOMAIN . $data['virtual_icon'],
			);
		}
		
		return array("list" => $rt);
	}
	
	// 是否有新消息
	public function actionNewPm()
	{
		$info = UserAppInfo::find()->where(['uid' => $this->userId])->asArray()->one();
		return $info['new_pm'] ? array('new_pm' => $info['new_pm']) : array('new_pm' => 0);
	}
	
	// 是否开启新手引导
	public function actionGuide()
	{
		$guideswitch = 'guideswitch';
		$config = \app\models\Config::getValueByKey($guideswitch);
		if ($config['status'] == '1' && (isset($config['starttime']) && ($config['starttime'] < time())) && (isset($config['starttime']) && ($config['endtime'] > time()))) return ['status' => 1];
		else return ['status' => 0];
	}
	
	//h5pay状态
	public function actionH5pay()
	{
		$list = \app\models\AppConfig::find()->where(["from" => $this->from, "type" => "h5pay"])->orderBy("id desc")->one();
		$content = json_decode($list['content'], true);
		if ($content['version'] != $this->version) {
			$list['status'] = 0;
		}
		return ['state' => $list['status']];
	}
	
	/** 推送消息数量[旧版本]
	 * @return array
	 */
	public function actionMsgNum()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$msg = AppMessageDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['user_id' => $this->userId, 'view' => 0])->count();
		if (!$msg) {
			UserAppInfo::updateAll(['new_pm' => 0], ['uid' => $this->userId]);
		}
		
		$publicNotice = AppConfig::find()->where(["type" => "public_notice", "status" => 1])->orderBy("time desc")->one();
		$publicNoticeData = yii\helpers\Json::decode($publicNotice['content']);
		return [
			'msg' => $msg,
			'public_notice' => [
				'desc' => $publicNoticeData['notice_desc'],
				'time' => date('Y-m-d', strtotime($publicNoticeData['notice_time'])),
			],
		];
	}
	
	/** 推送消息总数量
	 * @return array
	 */
	public function actionMsgCount()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		//中奖消息
		$lotteryNum = AppMessageDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['user_id' => $this->userId, 'view' => 0])->count();
		$lotteryNew = AppMessageDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['user_id' => $this->userId])->orderBy("created_at desc")->one();
		
		//系统消息
		$publicNotice = AppConfig::find()->where(["from" => $this->from, "type" => "public_notice", "status" => 1])->orderBy("time desc")->asArray()->all();
		$publicNum = NoticeRead::find()->where(['user_id' => $this->userId, 'type' => 1])->count();
		if ($publicNotice) {
			$publicNoticeData = Json::decode($publicNotice[count($publicNotice) - 1]['content']);
		} else {
			$publicNoticeData = [];
		}
		$publicNum = count($publicNotice) - $publicNum;
		//发货消息
		$deliveryNum = SendMessage::find()->where(['user_id' => $this->userId, 'view' => 0])->orderBy("create_time desc")->count();
		$deliveryNew = SendMessage::find()->where(['user_id' => $this->userId])->orderBy("create_time desc")->one();
		if ($deliveryNew) $deliveryContent = Json::decode($deliveryNew['content']);
		//活动消息
		$activityNotice = AppConfig::find()->where(["from" => $this->from, "type" => "activity_notice", "status" => 1])->orderBy("time desc")->asArray()->all();
		$activityNum = NoticeRead::find()->where(['user_id' => $this->userId, 'type' => 2])->count();
		if ($activityNotice) {
			$activityNoticeData = Json::decode($activityNotice[count($activityNotice) - 1]['content']);
		} else {
			$activityNoticeData = [];
		}
		$activityNum = count($activityNotice) - $activityNum;
		if (!$lotteryNum && !$deliveryNum && !$publicNum && !$activityNum) {
			UserAppInfo::updateAll(['new_pm' => 0], ['uid' => $this->userId]);
		}
		$device = ($this->tokenSource == '__ios__') ? 'ios' : 'android';
		$url = (Brower::whereFrom() == 1) ? 'http://' . $_SERVER['HTTP_HOST'] . '/app/' : 'http://' . $_SERVER['HTTP_HOST'] . '/didi_app/';
		$PicUrl = $url . 'images/' . $device . '/';
		return [
			[
				'type' => 1,
				'title' => '中奖消息',
				'picture' => $PicUrl . 'lottery_msg.png',
				'num' => $lotteryNum,
				'desc' => isset($lotteryNew["content"]) ? $lotteryNew["content"] : '',
				'time' => isset($lotteryNew['created_at']) ? date('Y-m-d H:i:s', $lotteryNew['created_at']) : '',
				'link' => $url . 'message_list.html',
			],
			[
				'type' => 2,
				'title' => '活动消息',
				'picture' => $PicUrl . 'activity_notice.png',
				'num' => $activityNum,
				'desc' => isset($activityNoticeData['notice_desc']) ? $activityNoticeData['notice_desc'] : '',
				'time' => isset($activityNoticeData['notice_time']) ? date('Y-m-d H:i:s', strtotime($activityNoticeData['notice_time'])) : '',
				'link' => 'http://' . $_SERVER['HTTP_HOST'] . '/app/activity-notice',
			],
			[
				'type' => 3,
				'title' => '发货消息',
				'picture' => $PicUrl . 'delivery_msg.png',
				'num' => $deliveryNum,
				'desc' => isset($deliveryContent["goodsName"]) ? $deliveryContent["goodsName"] : '',
				'time' => isset($deliveryNew["create_time"]) ? date('Y-m-d H:i:s', $deliveryNew["create_time"]) : '',
				'link' => 'http://' . $_SERVER['HTTP_HOST'] . '/app/delivery-msg-list',
			],
			
			[
				'type' => 4,
				'title' => '系统消息',
				'picture' => $PicUrl . 'system_notice.png',
				'num' => $publicNum,
				'desc' => isset($publicNoticeData['notice_desc']) ? $publicNoticeData['notice_desc'] : '',
				'time' => isset($publicNoticeData['notice_time']) ? date('Y-m-d H:i:s', strtotime($publicNoticeData['notice_time'])) : '',
				'link' => $url . 'public_notice_list.html',
			],
		];
	}
	
	/** 发货消息列表
	 * @return array
	 */
	public function actionDeliveryMsgList()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$page = $request->get('page', 1);
		$perpage = $request->get('perpage', 20);
		$type = $request->get('type', 0);
		$query = ($type != 1) ? SendMessage::find()->where(['user_id' => $this->userId]) : SendMessage::find()->where(['user_id' => $this->userId, 'type' => 1]);
		
		$countQuery = clone $query;
		$totalCount = $countQuery->count();
		$pagination = new yii\data\Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
		$query->orderBy('create_time desc');
		$messageList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
		foreach ($messageList as &$one) {
			$one['create_time'] = date('Y-m-d H:i:s', $one['create_time']);
			$one['content'] = yii\helpers\Json::decode($one['content']);
			$one['picture'] = isset($one['content']['picture']) ? $one['content']['picture'] : '';
			$one['periodNumber'] = isset($one['content']['periodNumber']) ? $one['content']['periodNumber'] : '';
			$one['goodsName'] = isset($one['content']['goodsName']) ? $one['content']['goodsName'] : '';
			$one['phone'] = isset($one['content']['phone']) ? $one['content']['phone'] : '';
			if ($type == 1) {
				$one['card'] = isset($one['content']['card']) ? $one['content']['card'] : '';
				$one['pwd'] = isset($one['content']['pwd']) ? $one['content']['pwd'] : '';
			}
			unset($one['content']);
			unset($one['admin_id']);
		}
		$return['list'] = $messageList;
		$return['totalCount'] = $totalCount;
		$return['totalPage'] = $pagination->getPageCount();
		return $return;
		
	}
	
	/** 发货消息详情
	 * @return array
	 */
	public function actionDeliveryMsgInfo()
	{
		
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$msgId = $request->get('id');
		$msg = SendMessage::find()->where(['id' => $msgId, 'user_id' => $this->userId])->one();
		if (!$msg) return [];
		$content = yii\helpers\Json::decode($msg['content']);
		$orderId = isset($content['order_id']) ? $content['order_id'] : '';
		$content['create_time'] = date('Y-m-d H:i:s', $msg['create_time']);
		if ($msg->view == 0) {
			$msg->view = 1;
			$msg->save(false);
		}
		if ($msg['type'] != 0) return $content;
		else return ['order_id' => $orderId];
		
	}
	
	/** 活动公告列表
	 * @return array
	 */
	public function actionActivityNotice()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$page = $request->get('page', 1);
		$perpage = $request->get('perpage', 20);
		
		$query = AppConfig::find()->where(["from" => $this->from, "type" => "activity_notice"]);
		$countQuery = clone $query;
		$totalCount = $countQuery->count();
		$pagination = new yii\data\Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
		$query->orderBy('id desc');
		$messageList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
		
		foreach ($messageList as &$one) {
			$one['content'] = yii\helpers\Json::decode($one['content']);
			$one['picture'] = isset($one['content']['notice_icon']) ? 'http://' . $_SERVER['HTTP_HOST'] . $one['content']['notice_icon'] : '';
			$one['title'] = isset($one['content']['notice_title']) ? $one['content']['notice_title'] : '';
			$one['desc'] = isset($one['content']['notice_desc']) ? $one['content']['notice_desc'] : '';
			$one['link'] = isset($one['content']['notice_link']) ? $one['content']['notice_link'] : '';
			$one['time'] = isset($one['content']['notice_time']) ? $one['content']['notice_time'] : '';
			$one['is_read'] = NoticeRead::isRead($this->userId, $one['id']);
			unset($one['content']);
		}
		
		$return['list'] = $messageList;
		$return['totalCount'] = $totalCount;
		$return['totalPage'] = $pagination->getPageCount();
		return $return;
	}
	
	/** 活动公告查看
	 * @return array
	 */
	public function actionActivityNoticeInfo()
	{
		
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$id = $request->get('id');
		$msg = AppConfig::findOne($id);
		if (!$msg) return [];
		$data = [
			'user_id' => $this->userId,
			'type' => 2,
			'notice_id' => $id,
			'created_time' => time()
		];
		$info = NoticeRead::addRead($data);
		return ['is_read' => $info];
	}
	
	/** 推送消息列表
	 * @return array
	 */
	public function actionMsgList()
	{
		
		
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$page = $request->get('page', 1);
		$perpage = $request->get('perpage', 20);
		$query = AppMessageDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['user_id' => $this->userId]);
		$countQuery = clone $query;
		$totalCount = $countQuery->count();
		$pagination = new yii\data\Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
		$query->orderBy('id desc');
		$messageList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
		
		foreach ($messageList as &$one) {
			$one['time'] = date('Y-m-d H:i:s', $one['created_at']);
			$one['content'] = mb_substr($one['content'], 0, 20, 'utf-8');
		}
		
		$return['list'] = $messageList;
		$return['totalCount'] = $totalCount;
		$return['totalPage'] = $pagination->getPageCount();
		return $return;
	}
	
	/** 中奖消息列表
	 * @return array
	 */
	public function actionLotteryMsgList()
	{
		
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$page = $request->get('page', 1);
		$perpage = $request->get('perpage', 20);
		$query = AppMessageDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['user_id' => $this->userId]);
		$countQuery = clone $query;
		$totalCount = $countQuery->count();
		$pagination = new yii\data\Pagination(['totalCount' => $totalCount, 'page' => $page - 1, 'defaultPageSize' => $perpage]);
		$query->orderBy('id desc');
		$messageList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
		
		foreach ($messageList as &$one) {
			$one['time'] = date('Y-m-d H:i:s', $one['created_at']);
			$one['content'] = mb_substr($one['content'], 0, 20, 'utf-8');
		}
		
		$return['list'] = $messageList;
		$return['totalCount'] = $totalCount;
		$return['totalPage'] = $pagination->getPageCount();
		return $return;
	}
	
	/** 中奖消息详情
	 * @return array
	 */
	public function actionLotteryMsgInfo()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$msgId = $request->get('id');
		$msg = AppMessageDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['id' => $msgId, 'user_id' => $this->userId])->one();
		if (!$msg) return ['info' => ''];
		if ($msg->view == 0) {
			$msg->view = 1;
			$msg->save(false);
		}
		$msg = yii\helpers\ArrayHelper::toArray($msg);
		$msg['time'] = date('Y-m-d H:i:s', $msg['created_at']);
		
		return ['info' => $msg];
	}
	
	/** 推送消息详情
	 * @return array
	 */
	public function actionMsgInfo()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$request = \Yii::$app->request;
		$msgId = $request->get('id');
		$msg = AppMessageDistribution::findByUserHomeId($this->userInfo['home_id'])->where(['id' => $msgId, 'user_id' => $this->userId])->one();
		if ($msg->view == 0) {
			$msg->view = 1;
			$msg->save(false);
		}
		$msg = yii\helpers\ArrayHelper::toArray($msg);
		$msg['time'] = date('Y-m-d H:i:s', $msg['created_at']);
		
		return ['info' => $msg];
	}
	
	/**
	 * 系统公告
	 */
	public function actionSystemNotice()
	{
		$list = AppConfig::find()->where(["from" => $this->from, "type" => "public_notice", "status" => 1])->orderBy("sort desc")->all();
		$notices = [];
		$userMessage = UserSystemMessage::find()->where(["to_userid" => $this->userId])->orderBy("status desc,created_at desc")->asArray()->all();

		$from = (Brower::whereFrom() == 1) ? 'app' : 'didi_app';
		foreach ($userMessage as &$v) {
			$v['title'] = '【系统提示】';
			$v['url'] = 'http://www.' . DOMAIN . '/'.$from.'/public_notice_detail.html?id=' . $v['id'].'&type=touser';
			$v['desc'] = strip_tags(mb_substr($v['message'], 0, 20, 'utf-8'));
			$v['time'] = isset($v['created_at']) ? date('Y-m-d H:i:s', $v['created_at']) : '';
			$v['icon'] = '/'.$from.'/images/notice_icon.png';
			$v['is_read'] = $v['status'];
			unset($v['created_at']);
			unset($v['status']);
			unset($v['message']);
		}
		foreach ($list as $key => $one) {
			$data = yii\helpers\Json::decode($one['content']);
			if ($data['notice_type'] == 'default') {
				$url = 'http://www.' . DOMAIN . '/'.$from.'/public_notice_detail.html?id=' . $one['id'];
			} elseif ($data['notice_type'] == 'link') {
				$url = $data['notice_link'].'?id='.$one['id'];
			}
			
			$notices[] = [
				'id' => $one['id'],
				'title' => '【公告】' . $data['notice_title'],
				'url' => $url,
				'desc' => $data['notice_desc'],
				'time' => $data['notice_time'],
				'icon' => $data['notice_icon'],
				'is_read' => NoticeRead::isRead($this->userId, $one['id']),
				'to_userid' => 0,
			];
		}
		$all = array_merge($notices, $userMessage);
		return ['list' => $all];
	}
	
	/**
	 * 系统公告详情
	 */
	public function actionSystemNoticeInfo()
	{
		if ($this->userId == 0) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		$type = \Yii::$app->request->get('type');
		$id = \Yii::$app->request->get('id');
		$result = $type != 'touser' ? AppConfig::find()->where(["from" => $this->from, 'id' => $id, "type" => "public_notice", "status" => 1])->asArray()->one() : UserSystemMessage::find()->where(["id" => $id])->asArray()->one();
		
		if (!$result) return ['info' => ''];
		$info = [];
		if (isset($result['content'])) {
			$data = yii\helpers\Json::decode($result['content']);
			$info['title'] = $data['notice_title'];
			$info['time'] = $data['notice_time'];
			$info['desc'] = $data['notice_desc'];
			$info['content'] = nl2br($data['notice_content']);
			$info['is_read'] = NoticeRead::isRead($this->userId, $result['id']);
			$data = [
				'user_id' => $this->userId,
				'type' => 1,
				'notice_id' => $id,
				'created_time' => time()
			];
			NoticeRead::addRead($data);
		} else {
			$MessageModel = UserSystemMessage::findOne($id);
			$info['title'] = '系统提示!';
			$info['time'] = isset($result['created_at']) ? date('Y-m-d', $result['created_at']) : '';
			$info['desc'] = strip_tags(mb_substr($result['message'], 0, 20, 'utf-8'));
			$info['content'] = $result['message'];
			$info['is_read'] = $result['status'];
			$MessageModel->status = 1;
			$MessageModel->save();
		}
		return [
			'info' => $info
		];
	}
	
	/**
	 * APP公告
	 */
	public function actionPublicNotice()
	{
		
		$list = AppConfig::find()->where(["from" => $this->from, "type" => "public_notice", "status" => 1])->orderBy("sort desc")->all();
		$notices = [];
		foreach ($list as $key => $one) {
			$data = yii\helpers\Json::decode($one['content']);
			if ($data['notice_type'] == 'default') {
				$url = 'http://www.' . DOMAIN . '/app/public_notice_detail.html?id=' . $one['id'];
			} elseif ($data['notice_type'] == 'link') {
				$url = $data['notice_link'];
			}
			
			$notices[] = [
				'id' => $one['id'],
				'title' => $data['notice_title'],
				'url' => $url,
				'desc' => $data['notice_desc'],
				'time' => $data['notice_time'],
				'icon' => $data['notice_icon'],
				//'is_read' => SendMessage::is_read(['id' => $one['id'], 'userid' => $this->userId]),
			];
		}
		
		return ['list' => $notices];
	}
	
	/**
	 * APP公告详情
	 */
	public function actionPublicNoticeInfo($id)
	{
		$result = AppConfig::find()->where(["from" => $this->from, 'id' => $id, "type" => "public_notice", "status" => 1])->asArray()->one();
		
		$data = yii\helpers\Json::decode($result['content']);
		
		$info = [];
		$info['title'] = $data['notice_title'];
		$info['time'] = $data['notice_time'];
		$info['desc'] = $data['notice_desc'];
		$info['content'] = nl2br($data['notice_content']);
		//$info['is_read'] = SendMessage::is_read(['id' => $id, 'userid' => $this->userId], 1);
		return [
			'info' => $info
		];
	}
	
	/** APP首页按钮
	 * @return array
	 */
	public function actionIndexBtn()
	{
		$indexBtn = AppConfig::find()->where(["from" => $this->from, "system" => $this->os, "type" => "index-btn", "status" => 1])->orderBy('sort desc,id asc')->all();
		$footerBar = AppConfig::find()->where(["from" => $this->from, "system" => $this->os, "type" => "footer-bar", "status" => 1])->orderBy('sort desc,id asc')->all();
		$indexBtnList = [];
		$footerBarList = [];
		foreach ($indexBtn as $btn) {
			$indexBtnList[] = Json::decode($btn['content']);
		}
		foreach ($footerBar as $bar) {
			$footerBarList[] = Json::decode($bar['content']);
		}
		
		return [
			'index_btn' => $indexBtnList,
			'footer_bar' => $footerBarList,
		];
	}
	
	//新手分享领取红包
	public function actionShareConpon()
	{
		$userId = $this->userId;
		
		if (!$userId) {
			return ['code' => 201, 'msg' => '未登录'];
		}
		
		$packetId = 39;
		$source = 'new_share';
		$rs = Coupon::receivePacket($packetId, $userId, $source);
		if ($rs['code'] == '0') {
			$pid = $rs['data']['pid'];
			$info = Coupon::openPacket($pid, $userId);
			if ($info['code'] == '0') {
				$result['code'] = 100;
				$result['msg'] = '领取成功';
			} else {
				$result['code'] = 102;
				$result['msg'] = $info['msg'];
			}
		} else {
			$result['code'] = 101;
			$result['msg'] = $rs['msg'];
		}
		
		return $result;
	}

	//启动公告
	public function actionAnnounce(){
		$version = Yii::$app->request->get('version','');
		$model = AppConfig::find()->where(["from" => $this->from, "type" => "announce", "system" => $this->os, "status" => 1])->orderBy('id desc')->one();
		$content= $model ? Json::decode($model->content) : exit("{}");
		if(version_compare($content['an_code'],$version,'>')){
			exit("{}");
		}
		unset($content['an_code']);
		$content['more'] = $content['an_url'] ? 1 :0;
		return $content;

	}
}