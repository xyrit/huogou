<?php

namespace app\models;

use app\services\Member;
use Yii;
use yii\db\Expression;
use yii\web\Cookie;
use app\services\User as UserServices;

/**
 * This is the model class for table "invite".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $invite_uid
 * @property integer $status
 * @property integer $invite_time
 */
class Invite extends \yii\db\ActiveRecord
{
    const INVITE_COOKIE_NAME = 'inviteId';
    const FREE_BUY_PERIOD_ID_COOKIE_NAME = 'fpid';

    const STATUS_UNCONSUME = 0;
    const STATUS_CONSUME = 1;

    const NEW_RULE_BEGIN = 20160601;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'invite_uid', 'status', 'invite_time'], 'required'],
            [['user_id', 'invite_uid', 'status', 'invite_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'invite_uid' => 'Invite Uid',
            'status' => 'Status',
            'invite_time' => 'Invite Time',
        ];
    }

    /** 设置邀请唯一ID
     * @param $homeId
     */
    public static function setInviteIdCookie($homeId)
    {
        $cookies = Yii::$app->response->cookies;
        $expire = time() + 3600*24*30;
        $cookies->add(new Cookie([
            'name' => static::INVITE_COOKIE_NAME,
            'value' => $homeId,
            'expire' => $expire,
            'domain' => '.'.DOMAIN,
        ]));
    }

    /** 设置零元购期数ID
     * @param $homeId
     */
    public static function setPeriodIdCookie($pid)
    {
        $cookies = Yii::$app->response->cookies;
        $expire = time() + 3600*24*30;
        $cookies->add(new Cookie([
            'name' => static::FREE_BUY_PERIOD_ID_COOKIE_NAME,
            'value' => $pid,
            'expire' => $expire,
            'domain' => '.'.DOMAIN,
        ]));
    }

    /** 获取邀请唯一ID
     * @return mixed
     */
    public static function getInviteIdCookie()
    {
        if (isset(Yii::$app->request->cookies)) {
            $cookies = Yii::$app->request->cookies;
            return $cookies->getValue(static::INVITE_COOKIE_NAME);
        }
        return '';
    }

    /** 获取邀请零元购期数ID
     * @return mixed
     */
    public static function getPeriodIdCookie()
    {
        if (isset(Yii::$app->request->cookies)) {
            $cookies = Yii::$app->request->cookies;
            return $cookies->getValue(static::FREE_BUY_PERIOD_ID_COOKIE_NAME);
        }
        return '';
    }

    /** 移除邀请唯一ID
     * @return mixed
     */
    public static function removeInviteIdCookie()
    {
        $cookies = Yii::$app->response->cookies;
        $expire = time()-120;
        $cookies->add(new Cookie([
            'name' => static::INVITE_COOKIE_NAME,
            'value' => '',
            'expire' => $expire,
            'domain' => '.'.DOMAIN,
        ]));
    }

    /** 移除邀请零元购期数ID
     * @return mixed
     */
    public static function removePeriodIdCookie()
    {
        $cookies = Yii::$app->response->cookies;
        $expire = time()-120;
        $cookies->add(new Cookie([
            'name' => static::FREE_BUY_PERIOD_ID_COOKIE_NAME,
            'value' => '',
            'expire' => $expire,
            'domain' => '.'.DOMAIN,
        ]));
    }

    /** 发放佣金
     * @param $chargeUserId
     * @param $money
     * @param string $desc
     */
    public static function commissionPayoff($chargeUserId, $money, $periodId)
    {
        $invite = static::findOne(['invite_uid'=>$chargeUserId]);

        if ($invite && $invite->user_id != 0 ) {
            if ($invite->status == static::STATUS_UNCONSUME) {
                //改变邀请状态
                $invite->status = static::STATUS_CONSUME;
                $invite->save();
                //送福分和经验
                static::addPointAndExp($invite->user_id);
            }
            if (time() >= strtotime(static::NEW_RULE_BEGIN)) {
                //新规则佣金
                $commission = static::getCommission($chargeUserId);
                foreach ($commission as $key => $value) {
                    // //发放佣金
                    $update = User::updateAll(['commission'=>new Expression('commission +'.$value)], ['id'=>$invite->user_id]);
                    if ($update) {
                        $desc = serialize(['periodId'=>$periodId]);
                        static::addCommissionLog($invite->user_id, $chargeUserId, $key, $value, InviteCommission::TYPE_PAY, $desc);
                    }
                }
            }else{
                //老佣金（每笔的%6）
                $commissionPrice = $money*100*0.03;//佣金(分)
                // //发放佣金
                $update = User::updateAll(['commission'=>new Expression('commission +'.$commissionPrice)], ['id'=>$invite->user_id]);
                if ($update) {
                    $desc = serialize(['periodId'=>$periodId]);
                    static::addCommissionLog($invite->user_id, $chargeUserId, $money, $commissionPrice, InviteCommission::TYPE_PAY, $desc);
                }
            }

        }
    }

    /** 佣金充值到余额
     * @param $chargeUserId
     * @param $money
     * @param $periodId
     * @return bool
     */
    public static function commissionRecharge($chargeUserId, $money)
    {
        if ($money<1) {
            return false;
        }
        $commissionPrice = $money*100*-1;
        $user = User::find()->where(['id'=>$chargeUserId])->one();
        $commission = $user->commission;
        if ($commission>=$commissionPrice) {
            $user->commission = $commission + $commissionPrice;
            $user->money += $money;
            $save = $user->save();
            if ($save) {
                $desc = '用户佣金提取到账户余额';
                static::addCommissionLog($chargeUserId, $chargeUserId, 0, $commissionPrice, InviteCommission::TYPE_RECHARGE, $desc);
            }
            return $save;
        }

        return false;
    }

    /** 佣金提现
     * @param $chargeUserId
     * @param $money
     * @param $desc
     * @return bool
     */
    public static function commissionWithdraw($chargeUserId, $money, $desc)
    {
        if ($money<100) {
            return false;
        }
        $commissionPrice = $money*100*-1;
        $user = User::find()->where(['id'=>$chargeUserId])->one();
        $commission = $user->commission;
        if ($commission>=$commissionPrice) {
            static::addCommissionLog($chargeUserId, $chargeUserId, 0, $commissionPrice, InviteCommission::TYPE_WITHDRAW, $desc);
        }
    }

    public static function addPointAndExp($uid)
    {
        $desc = '成功邀请1位好友并消费';
        $member = new Member(['id'=>$uid]);
        $member->editPoint(10, PointFollowDistribution::POINT_FRIEND, $desc);
        $member->editExperience(10, ExperienceFollowDistribution::EXPR_FRIEND_BUY, $desc);
    }

    public static function addCommissionLog($userId, $actionUserId, $money, $commissionPrice, $type, $desc)
    {
        $time = time();
        $commission = new InviteCommission();
        $commission->user_id = $userId;
        $commission->action_user_id = $actionUserId;
        $commission->money = $money;
        $commission->commission = $commissionPrice;
        $commission->type = $type;
        $commission->desc = $desc;
        $commission->created_time = $time;
        $commission->save();
    }

    private static function getCommission($chargeUserId){
        $time = strtotime(static::NEW_RULE_BEGIN);
        $totalPayment = UserServices::getTotalPayment($chargeUserId, $time);
        $reward = [
            '50'=>'1',
            '100'=>'2',
            '200'=>'3',
            '500'=>'4',
            '600'=>'5',
            '1000'=>'6',
            '1200'=>'7',
            '1500'=>'10',
            '1800'=>'20',
            '2500'=>'30',
            '3500'=>'40',
            '5000'=>'60'
        ];
        $commission = static::getCommissionLog($chargeUserId);
        $money = 0;
        if (empty($commission)) {
            $max = 0;
        }else{
            $maxArr = array_keys($commission);
            $max = end($maxArr);
        }
        $data = [];
        foreach ($reward as $key => $value) {
            if ($totalPayment >= $key && $key > $max) {
                $data[$key] = $value*100;
            }
        }
        $i = 0;
        $count = count($data);
        foreach ($data as $key => $value) {
            $i++;
            if($i == $count){
                unset($data[$key]);
                $data[$totalPayment] = $value;
            }
        }
        return $data;
    }
    /**
     * 贡献的佣金记录
     * @param  int $userId 用户ID
     * @return array         贡献佣金时的总消费额=>佣金
     */
    private static function getCommissionLog($userId){
        $list = InviteCommission::find()->where(['action_user_id'=>$userId])->andWhere(['>=','created_time',strtotime(static::NEW_RULE_BEGIN)])->asArray()->all();
        $data = [];
        foreach ($list as $key => $value) {
            $data[$value['money']] = $value['commission'];
        }
        return $data;
    }

}
