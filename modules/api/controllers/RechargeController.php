<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: ����4:19
 */
namespace app\modules\api\controllers;

use app\helpers\DateFormat;
use app\helpers\MyRedis;
use app\models\RechargeOrderDistribution;
use app\services\Member;
use app\services\Thirdpay;
use Yii;
use app\modules\member\models\UserTransferAccount;
use app\models\UserSystemMessage;
use app\models\User;
use app\services\User as ServiceUser;
use app\modules\member\models\Card;
use app\helpers\Message;
class RechargeController extends BaseController
{
    //转账
    public function actionTransfer()
    {
        if (1) {
            return ['code' => 107, 'msg' => '转账功能已停用'];
        }
        if (empty($this->userId)) {
            return ['code' => 201, 'msg'=>'未登录'];
        }
        $request = Yii::$app->request;
        $uid = $this->userId;
        $userInfo = $this->userInfo;
        $baseInfo = ServiceUser::baseInfo($uid);
        $paypwd = $request->get('paypwd');
        $username = $request->get('username');
        $account = $request->get('account');
        $comment = $request->get('comment');

        if (empty($account)) {
            return ['code' => 107, 'msg' => '请填写转账金额'];
        }

        if (empty($username)) {
            return ['code' => 106, 'msg' => '请填写收款账号'];
        }
        $userId = User::findByAccount($username);
        if(! $userId) return ['code' => 104, 'msg' => '请正确填写收款账号信息'];

        if (empty($paypwd)) {
            return ['code' => 105, 'msg' => '请填写支付密码'];
        }

        if(!Yii::$app->security->validatePassword($paypwd, $userInfo['pay_password'])){
            $redis = new MyRedis();
            $wrongPasswordKey = 'WRONG_PAY_PASSWORD_NUMS_'.$this->userId;
            $wrongPasswordNum = $redis->incr($wrongPasswordKey);
            $redis->expire($wrongPasswordKey, 3600);
            if ($wrongPasswordNum >= 5) {
                User::updateAll(['status'=>1], ['id'=>$this->userId]);
                return ['code' => 101, 'msg' => '支付密码错误5次,账户被锁定,请联系客服!'];
            }
            return ['code' => 101, 'msg' => '支付密码错误'];
        }

        if($account > $userInfo['money']){
            return ['code' => 103, 'msg' => '余额不足'];
        }

        if($uid == $userId['id']){
            return ['code' => 104, 'msg' => '不能给本人转账'];
        }

        $recharge = RechargeOrderDistribution::findByTableId($userInfo['home_id'])
            ->select('sum(money) as total')
            ->where(['user_id'=>$uid, 'status'=>1, 'payment'=>[1,2,3]])
            ->asArray()
            ->one();
        if (!$recharge || $recharge['total']==0) {
            return ['code' => 104, 'msg' => '不能给本人转账!'];
        }
        $tranferOutTodayNum = UserTransferAccount::find()->where(['user_id'=>$this->userId])->andWhere(['>','created_at',time()-3600])->count();
        if ($tranferOutTodayNum>=10) {
            User::updateAll(['status'=>1], ['id'=>$this->userId]);
            return ['code' => 101, 'msg' => '账户被锁定,请联系客服!'];
        }
        $tranferInTodayNum = UserTransferAccount::find()->where(['to_userid'=>$userId['id']])->andWhere(['>','created_at',time()-3600])->count();
        if ($tranferInTodayNum>=10) {
            User::updateAll(['status'=>1], ['id'=>$userId['id']]);
            return ['code' => 101, 'msg' => '被转账户已被锁定,请联系该用户!'];
        }
        $model = new UserTransferAccount();
        $model->created_at = time();
        $model->user_id = $uid;
        $model->to_userid = $userId['id'];
        $model->account = $account;
        $model->comment = isset($comment) ? $comment : '';

        if($model->validate()){
            if($model->save(false)){
                UserTransferAccount::rechageSuccess($uid,$userId['id'], $account);
                $username = User::userName($userId['id']);
                Message::send(26, $userId['id'], ['nickname'=>$username['username'], 'oppositeNickname'=>$baseInfo['username'], 'time'=>date('Y-m-d H:i:s', time()), 'money'=>$account]);
                return ['code' => 100, 'msg' => '转账成功'];
            }
            return ['code' => 102, 'msg' => '转账失败'];
        }
        return ['code' => 102, 'msg' => current($model->getFirstErrors())];
    }

    /**
     * 验证卡号，返回卡金额
     **/
    public function actionCheckCard()
    {
        $request = Yii::$app->request;
        $card = $request->get('card');
        $exist = Card::find()->where(['number'=>$card, 'status'=>1])->one();
        if(!$exist){
            return ['code'=> 101, 'msg'=> '充值卡号不正确'];
        }else{
            return ['code'=> 100, 'msg' => $exist['money']];
        }
    }

    /**
     * 验证充值卡信息
     */
    public function actionCard()
    {
        if (empty($this->userId)) {
            return ['code' => 201, 'msg'=>'未登录'];
        }
        $request = Yii::$app->request;
        $card = $request->get('card');
        $password = $request->get('password');
        $where = ['number'=>$card, 'password'=>$password, 'status'=>1];
        $exist = Card::find()->where($where)->one();
        if(!$exist){
            return ['code'=>101, 'msg'=>'密码错误'];
        }else{
            $exist->status = 1;
            $exist->save();
            UserTransferAccount::cardSuccess($this->userId, $exist['money']);
            return ['code'=>100, 'msg'=>'激活成功'];
        }
    }

    /**
     * 余额明细
     */
    public function actionMoneyLog()
    {
        $type = Yii::$app->request->get('type', 0); //账户明细类型 0 充值记录 1 消费记录  2 转账记录
        $page = Yii::$app->request->get('page', 1);
        $perpage = Yii::$app->request->get('perpage', 5);
        $region = Yii::$app->request->get('region', '');
        $startTime = Yii::$app->request->get('start_time', '');
        $endTime = Yii::$app->request->get('end_time', '');

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime . " 23:59:59");
        }

        if (!($startTime && $endTime) && $region) {
            list($startTime, $endTime) = DateFormat::formatConditionTime($region);
        }

        $member = new Member(['id' => $this->userId]);

        if ($type == 0) {
            $payment = [
                1 => "储蓄卡充值",
                2 => "信用卡充值",
                3 => "充值平台充值",
                4 => "佣金充值",
                5 => "充值卡充值",
                6 => '充值卡兑换',
                7 => '平台赠送',
            ];
            $bankArr = [
                'chinaBank' => '网银在线',
                'iapp' => '爱贝支付',
                'zhifukachat' => '微信支付',
                'chat' => '微信支付',
                'alipay' => '支付宝支付',
                'nowpay' => '现在支付',
                'jd' => '京东支付',
                'kq' => '快钱支付',
                'union' => '银联支付',
                'zhifukaqq' => '手Q支付',
                'sign' => '签到赠送',
                'coupon'  => '优惠券',
                'active' => '活动赠送',
                'task' => '任务赠送',
            ];
            $record = $member->getRechargeRecord($startTime, $endTime, $page, $perpage);
            foreach ($record['list'] as $key => &$r) {
                if ($r['money'] == 0) {
                    unset($record['list'][$key]);
                    continue;
                }
                $r['pay_time'] = DateFormat::microDate($r['pay_time']);
                if ($r['payment']==3 || $r['payment'] == 7) {
                    $r['payment'] = isset($bankArr[$r['bank']]) ? $bankArr[$r['bank']] : $payment[$r['payment']];
                } else {
                    $r['payment'] = $payment[$r['payment']];
                }
            }
        } elseif ($type == 1) {
            $payFor = [
                0 => '一元购商品',
                1 => '欧洲杯竞猜',
                2 => 'PK场商品',
            ];
            $record = $member->getPayRecord($startTime, $endTime, $page, $perpage);
            foreach ($record['list'] as $key => &$r) {
                if ($r['money'] == 0) {
                    unset($record['list'][$key]);
                    continue;
                }
                $r['money'] = '-' . $r['money'];
                $r['pay_time'] = DateFormat::microDate($r['buy_time']);
                $r['payment'] = $payFor[$r['pay_for']];
            }
        } elseif ($type == 2) {
            $record = $member->getTransferRecord($startTime, $endTime, $page, $perpage);
            foreach ($record['list'] as $key => &$r) {
                if ($r['money'] == 0) {
                    unset($record['list'][$key]);
                    continue;
                }
                $r['pay_time'] = DateFormat::microDate($r['created_at']);
            }
        }

        $record['list'] = array_values($record['list']);
        return $record;
    }

    public function actionResult()
    {
        $order = Yii::$app->request->get('o');
        $pay = new Thirdpay();
        return $pay->result('result',$order);
    }

}