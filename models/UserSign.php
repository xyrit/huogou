<?php

namespace app\models;

use app\helpers\MyRedis;
use app\services\Coupon;
use app\services\Member;
use app\helpers\Brower;
use Yii;

/**
 * This is the model class for table "user_signs".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $signed_at
 * @property integer $continue
 * @property integer $total
 */
class UserSign extends \yii\db\ActiveRecord
{
    const SIGN_IN = 'SIGN_IN_';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_signs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'signed_at', 'continue', 'total'], 'integer'],
            [['user_id'], 'unique']
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
            'signed_at' => 'Signed At',
            'continue' => 'Continue',
            'total' => 'Total',
        ];
    }

    public static function getList($userId, $source)
    {
        $sign = Sign::getList();
        $userSign = UserSign::find()->where(['user_id' => $userId])->one();
        if (empty($userSign) || $userSign['signed_at'] < date("Ymd", strtotime('-1 days'))) {
            $isSign = 0;
            $continue = 0;
        } else {
            $isSign = $userSign['signed_at'] == date("Ymd") ? 1 : 0;
            $continue = $userSign['continue'];
        }

        if ($userSign && ($userSign['continue'] >= count($sign['list']) && $userSign['signed_at'] != date("Ymd") || $userSign['signed_at'] < date("Ymd", strtotime('-1 days')))) {
            foreach ($sign['list'] as &$one) {
                $one['status'] = 0;
            }
            $userSign->signed_at = 0;
            $userSign->continue = 0;
            $userSign->save();
        } else {
            foreach ($sign['list'] as &$one) {
                $one['status'] = $continue >= $one['days'] ? 1 : 0;
            }
        }

        unset($one);

        //判断是滴滴夺宝 2还是伙购 1
        $from=Brower::whereFrom();
        if($from==1){
        $imgPath = Yii::$app->params['skinUrl'] . '/img/active/' . ($source == 3 ? 'ios' : 'android') . '/';
        }else{
        $imgPath = Yii::$app->params['skinUrl'] . '/img/dd_active/' . ($source == 3 ? 'ios' : 'android') . '/';
        }

        foreach ($sign['list'] as &$one) {
            $one['icon'] = $imgPath . 'sign_icon_' . $one['type'] . '_' . ($one['status'] == 1 ? ($continue == $one['days'] ? 1 : 0) : 1) . '.png';
        }

        $sign['content'] = explode(';', $sign['content']);
        if ($source == 3) {
            $sign['content'] = array_merge($sign['content'], ['声明：所有奖品抽奖活动与苹果公司（Apple Inc.）无关']);
        }
        $sign['isSign'] = $isSign;
        $sign['continueDays'] = $continue;

        return $sign;
    }

    public static function award($userId, $days, $source)
    {
        $sign = Sign::find()->where(['days' => $days])->asArray()->one();
        if ($sign) {
            $member = new Member(['id' => $userId]);
            switch ($sign['type']) {
                case 1: // 福分
                    $member->editPoint($sign['num'], PointFollowDistribution::POINT_SIGN, '签到获得' . $sign['num'] . '福分');
                    $sign['name'] = $sign['num'] . "福分";
                    break;
                case 2: // 伙购币
                    $member->editMoney($sign['num'], MoneyFollowDistribution::MONEY_SIGN, '签到获得' . $sign['num'] . '伙购币', $source);
                    $sign['name'] = $sign['num'] . "伙购币";
                    break;
                case 3: // 红包
                    $packet = Coupon::receivePacket($sign['num'], $userId, 'sign');
                    if ($packet['code'] == 0) {
                        Coupon::openPacket($packet['data']['pid'], $userId);
                    }
                    $packet = Packet::findOne($sign['num']);
                    $sign['name'] = $packet['name'];
                    break;
            }
            $member->taskLog('签到第' . $sign['days'] . '天', $source, UserTaskFollowDistribution::TASK_SIGN);
        }



        $sign['icon'] = Yii::$app->params['skinUrl'] . '/img/active/' . ($source == 3 ? 'ios' : 'android') . '/' . 'sign_popup_icon_' . $sign['type'] . '.png';
        return $sign;
    }
}