<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/21
 * Time: 上午11:01
 */

namespace app\modules\admin\controllers;

use app\helpers\Ip;
use app\helpers\MyRedis;
use app\models\Area;
use app\models\Invite;
use app\models\Order;
use app\models\UserLimit;
use app\models\UserNotice;
use app\models\UserPrivateMessage;
use app\models\UserSystemMessage;
use app\services\AdminMember;
use Yii;
use app\models\User as Member;
use app\services\User;
use app\helpers\DateFormat;
use app\services\AdminMember as UserRelate;
use app\models\User as ModelUser;
use app\modules\admin\models\AdjustBalance;

class MemberController extends BaseController
{
    public function render($view, $params = [])
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        if ($id) {
            $userInfo = User::allInfo($id);
            if($userInfo['live_city']){
                $split = explode(',', $userInfo['live_city']);
                $one = Area::findOne([$split[0]]);
                $two = Area::findOne([$split[1]]);
                $userInfo['live_city'] = $one['name'].$two['name'];
            }

            $invite = User::getInviteUser($id);
            $userInfo['invite'] = $invite['user_name'];
            $userInfo['totalPayment'] = User::getTotalPayment($id);
            $limit = UserLimit::findOne(['user_id'=>$id]);
            $notice = UserNotice::findOne($id);
            $userInfo['last_login_ip'] = long2ip($userInfo['last_login_ip']);
            $params['userInfo'] = $userInfo;
            $params['limit'] = $limit;
            $params['notice'] = $notice;
        }

        return parent::render($view, $params);
    }
    

    public function actionIndex()
    {
        $status = Yii::$app->request->get('status', '-1');
        $order = Yii::$app->request->get('order', '1');
        $type = Yii::$app->request->get('type', 1);
        $start_time = Yii::$app->request->get('start_time', '');
        $end_time = Yii::$app->request->get('end_time', '');
        $account = Yii::$app->request->get('account', '');
        $limit = 10;

        $condition = ['status' => $status, 'order' => $order, 'type' => $type, 'start_time' => $start_time, 'end_time' => $end_time, 'account' => $account];
        if ($type != '-1') {
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
        }

        $list = User::UserList($limit, $status, $order, $type, $start_time, $end_time, $account);

        $arr = [];
        foreach ($list['list'] as &$val) {
            $val['username'] = Member::userName($val['id']);
            $val['reg_terminal'] = Order::getSource($val['reg_terminal']);
            $val['reg_ip'] = Ip::getAddressByIp(long2ip($val['reg_ip']));
            $val['avatar'] = \app\models\Image::getUserFaceUrl($val['avatar'], 80);
        }
        
        return $this->render('index',[
            'list' => $list['list'],
            'pagination' => $list['pagination'],
            'condition' => $condition
        ]);
    }

    /**
     * 编辑用户
     */
    public function actionEditUser()
    {
        $request = Yii::$app->request;
        $userId = $request->get('user_id');
        $userInfo = User::baseInfo($userId);

        if ($request->isPost) {
            $phone = $request->post('phone');
            $email = $request->post('email');
            $password = $request->post('password');
            $userId = $request->post('user_id');

            $phone && $params['phone'] = $phone;
            $email && $params['email'] = $email;
            $password && $params['password'] = Yii::$app->security->generatePasswordHash($password);

            if ($params) {
                \app\models\User::updateAll($params, ['id' => $userId]);
            }
            echo json_encode(['code' => 100]);
            Yii::$app->end();
        }

        return $this->render('edituser', $userInfo);
    }

    /**
     * 编辑用户
     */
    public function actionSendMessage()
    {
        $request = Yii::$app->request;
        $userIds = $request->get('userids');

        return $this->render('sendmessage', ['userids' => $userIds]);
    }

    //伙购记录
    public function actionView()
    {
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $status = $request->get('status', -1);
        $region = $request->get('region', 0);
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['status' => $status, 'start_time' => $startTime, 'end_time' => $endTime, 'region' => $region];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getBuyList($startTime, $endTime, $status, $page);
        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('view', $list);
    }

    //伙购记录
    public function actionBuy()
    {
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $status = $request->get('status', -1);
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['status' => $status, 'start_time' => $startTime, 'end_time' => $endTime];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getBuyList($startTime, $endTime, $status, $page);
        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('buy', $list);
    }
    
    //中奖记录
    public function actionWinning(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $status = $request->get('status', -1);
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['status' => $status, 'start_time' => $startTime, 'end_time' => $endTime];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getOrderList($startTime, $endTime, $status, $page);
        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('winning', $list);
    }
    
    //晒单记录
    public function actionShare(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $status = $request->get('status', -1);
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['status' => $status, 'start_time' => $startTime, 'end_time' => $endTime];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getShareList($startTime, $endTime, $status, $page);
        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('share', $list);
    }
    
    //账户明细
    public function actionAccount(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');
        $flag = $request->get('flag', 1);

        $condition = ['start_time' => $startTime, 'end_time' => $endTime, 'flag' => $flag];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        if ($flag == 1) {
            $list = $adminMember->getRechargeList($startTime, $endTime, $page);
        } else {
            $list = $adminMember->getTransferList($startTime, $endTime, $page);
        }

        $list['id'] = $id;
        $list['condition'] = $condition;
        $list['balance_list'] = AdjustBalance::findAll(["user_id"=>$id]);
        return $this->render('account', $list);
    }
    
    //佣金明细
    public function actionCommission(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['start_time' => $startTime, 'end_time' => $endTime];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getCommission($startTime, $endTime, $page);

        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('commission', $list);
    }
    
    //积分明细
    public function actionPoint(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['start_time' => $startTime, 'end_time' => $endTime];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getPointsList($startTime, $endTime, $page);

        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('point', $list);
    }
    
    //邀请列表
    public function actionInvite(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['start_time' => $startTime, 'end_time' => $endTime];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getInviteList($startTime, $endTime, $page);

        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('invite', $list);
    }
    
    //好友列表
    public function actionFriend(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        $condition = ['start_time' => $startTime, 'end_time' => $endTime];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getFriendList($startTime, $endTime, $page);

        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('friend', $list);
    }
    
    //收货地址
    public function actionAddress(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getAddress($page);

        foreach($list['list'] as $key => $val){
            $list['list'][$key]['prov'] = Area::findOne($val['prov']);
            $list['list'][$key]['city'] = Area::findOne($val['city']);
            $list['list'][$key]['area'] = Area::findOne($val['area']);
        }

        $list['id'] = $id;
        return $this->render('address', $list);
    }
    
    //圈子
    public function actionGroup(){

        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');

        $adminMember = new AdminMember(['id' => $id]);
        $list = $adminMember->getGroupList($page);

        $list['id'] = $id;
        return $this->render('group', $list);
    }
    
    //话题
    public function actionTopic(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');
        $flag = $request->get('flag', 1);

        $condition = ['start_time' => $startTime, 'end_time' => $endTime, 'flag' => $flag];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        if ($flag == 1) {
            $list = $adminMember->getTopicList($startTime, $endTime, $page);
        } else {
            $list = $adminMember->getTopicCommentList($startTime, $endTime, $page);
        }

        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('topic', $list);
    }

    //消息
    public function actionMessage(){
        $request = Yii::$app->request;
        $page = $request->get('page');
        $id = $request->get('id');
        $region = $request->get('region', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');
        $flag = $request->get('flag', 1);

        $condition = ['start_time' => $startTime, 'end_time' => $endTime, 'flag' => $flag];

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $adminMember = new AdminMember(['id' => $id]);
        if ($flag == 1) {
            $list = $adminMember->getMessageList($startTime, $endTime, $page);
        } elseif ($flag == 2) {
            $list = $adminMember->getPrivateMessageList($startTime, $endTime, $page);
        } else {
            $list = $adminMember->getFriendApplyList($startTime, $endTime, $page);
        }

        $list['id'] = $id;
        $list['condition'] = $condition;
        return $this->render('message', $list);
    }
 
    
    //获取用户信息
    public function actionUser() {
        $request = Yii::$app->request;
        $id = $request->post('user_id');
        return json_encode(User::baseInfo($id));
    }
    
    //更新用户信息
    public function actionUpdate() {
        $request = Yii::$app->request;
        $id = $request->post('user_id');
        echo  UserRelate::getUser($id);
    }
    
    //站内信
    public function actionSysmsg()
    {
        $request = Yii::$app->request;
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');
        $account = $request->get('account', '');
        $condition = ['account'=>$account, 'start_time'=>$startTime, 'end_time'=>$endTime];
        $where = ['to_userid'=>$account, 'starttime'=>strtotime($startTime), 'endtime'=>strtotime($endTime)];

        $list = UserSystemMessage::systemMsg($where);
        foreach($list['list'] as $key => $user){
            $list['list'][$key]['to_userid'] = ModelUser::findOne($user['to_userid']);
        }

        return $this->render('sysmessage', [
            'list' => $list,
            'condition' => $condition,
        ]);
    }

    public function actionChangeStatus()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $id = $request->post('id');
            $status = $request->post('status');
            $ids = explode(',', $id);
            if (\app\models\User::updateAll(['status' => $status], ['id' => $ids])) {
                $message = $status == 0 ? '解冻成功' : '冻结成功';
                echo json_encode(['code' => 100,'message' => $message]);
                Yii::$app->end();
            }
            $message = $status == 0 ? '解冻失败' : '冻结失败';
            echo json_encode(['code' => 100,'message' => $message]);
            Yii::$app->end();
        }
    }

}