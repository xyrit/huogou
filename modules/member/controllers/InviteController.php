<?php
namespace app\modules\member\controllers;

use app\helpers\TimeHelper;
use app\models\CurrentPeriod;
use app\models\Image;
use app\models\Invite;
use app\models\InviteCommission;
use app\models\InviteLink;
use app\models\PaymentOrderDistribution;
use app\models\PaymentOrderItemDistribution;
use app\models\PointFollowDistribution;
use app\models\Product;
use app\models\RechargeOrderDistribution;
use app\models\Withdraw;
use app\services\Period;
use app\services\Thirdpay;
use app\services\User;
use yii;

class InviteController extends BaseController
{

    //邀请列表
    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $inviteUrl = InviteLink::getInviteLink($userId);
        $userBaseInfo = User::baseInfo($userId);
        $inviteConsumeUserCount = Invite::find()->where(['user_id' => $userId, 'status' => Invite::STATUS_CONSUME])->count();
        $invitePointsCount = PointFollowDistribution::findByUserHomeId($userBaseInfo['home_id'])->where(['user_id' => $userId,'type'=>PointFollowDistribution::POINT_FRIEND])->sum('point');
        $query = Invite::find()->where(['user_id' => $userId]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new yii\data\Pagination(['totalCount' => $totalCount, 'defaultPageSize' => 10]);
        $query->orderBy('id desc');
        $invite = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $userIds = yii\helpers\ArrayHelper::getColumn($invite, 'invite_uid');
        $usersBaseInfo = User::baseInfo($userIds);
        foreach ($invite as &$one) {
            $userBaseInfo = $usersBaseInfo[$one['invite_uid']];
            $one['user_nickname'] = $userBaseInfo['username'];
            $one['user_home_id'] = $userBaseInfo['home_id'];
            $one['user_avatar'] = Image::getUserFaceUrl($userBaseInfo['avatar'], 80);
        }
        return $this->render('index', [
            'inviteUrl' => $inviteUrl,
            'invite' => $invite,
            'totalCount' => $totalCount,
            'inviteConsumeUserCount' => $inviteConsumeUserCount,
            'invitePointsCount' => (int)$invitePointsCount,
            'pagination' => $pagination,
        ]);

    }

    //佣金明细
    public function actionCommissionList()
    {
        $userId = Yii::$app->user->id;
        $payCommissionSum = InviteCommission::find()->where(['user_id' => $userId, 'type'=>InviteCommission::TYPE_PAY])->sum('commission');
        $otherCommissionSum = InviteCommission::find()->where(['user_id' => $userId, 'type'=>[InviteCommission::TYPE_RECHARGE, InviteCommission::TYPE_WITHDRAW]])->sum('commission');
        $request = Yii::$app->request;
        $startTime = $request->get('start');
        $endTime = $request->get('end');
        $timeType = $request->get('type');
        $commissionType = $request->get('commissionType');

        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime . ' 23:59:59');
        }

        if ($timeType == 1) { //今天
            $startTime = strtotime(date('Y-m-d'));
            $endTime = strtotime(date('Y-m-d', strtotime('+1 day')));
        } elseif ($timeType == 2) {//本周
            $timeArr = TimeHelper::getCurWeekStartEnd();
            $startTime = strtotime($timeArr['start']);
            $endTime = strtotime($timeArr['end']);
        } elseif ($timeType == 3) {//本月
            $timeArr = TimeHelper::getCurMonthStartEnd();
            $startTime = strtotime($timeArr['start']);
            $endTime = strtotime($timeArr['end']);
        } elseif ($timeType == 4) {//最近三个月
            $startTime = strtotime(date('Y-m-01', strtotime('-2 month')));
            $endTime = strtotime(date('Y-m-d'));
        }
        $query = InviteCommission::find()->where(['user_id' => $userId]);
        if ($startTime) {
            $query->andWhere(['>', 'created_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'created_time', $endTime]);
        }
        if ($commissionType==1) {
            $query->andWhere(['=', 'type', InviteCommission::TYPE_PAY]);
        } elseif ($commissionType==2) {
            $query->andWhere(['type'=>[InviteCommission::TYPE_WITHDRAW]]);
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new yii\data\Pagination(['totalCount' => $totalCount, 'defaultPageSize' => 10]);
        $query->orderBy('id desc');
        $commissionList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $userIds = yii\helpers\ArrayHelper::getColumn($commissionList, 'action_user_id');
        $usersBaseInfo = User::baseInfo($userIds);
        foreach ($commissionList as &$one) {
            $userBaseInfo = $usersBaseInfo[$one['action_user_id']];
            $one['user_nickname'] = $userBaseInfo['username'];
            $one['user_home_id'] = $userBaseInfo['home_id'];
            $one['user_avatar'] = Image::getUserFaceUrl($userBaseInfo['avatar'], 80);
            $one['commission'] = sprintf('%.2f', $one['commission'] / 100);
            if ($one['type'] == InviteCommission::TYPE_PAY) {
                $desc = unserialize($one['desc']);
                $periodId = $desc['periodId'];
                $info = CurrentPeriod::findOne($periodId);
                if ($info) {
                    $periodNumber = $info->period_number;
                    $productInfo = Product::findOne($info->product_id);
                    $productName = $productInfo->name;
                } else {
                    $info = \app\models\Period::findOne($periodId);
                    $periodNumber = $info->period_number;
                    $productInfo = Product::findOne($info->product_id);
                    $productName = $productInfo->name;
                }

                $one['desc'] = '<a target="_blank" href="'.yii\helpers\Url::to(['/product/lottery', 'pid'=>$periodId]).'">'.$productName.'</a>';
            } elseif($one['type'] == InviteCommission::TYPE_WITHDRAW) {
                $desc = unserialize($one['desc']);
                $bank = $desc['bank'];
                $bankNumber = $desc['bank_number'];
                $one['desc'] = '用户佣金提取到银行账户(' . $bank . ' ' . $bankNumber . ')';
            }
            if ($one['type'] == InviteCommission::TYPE_PAY) {
                $one['type'] = '收入';
            } elseif ($one['type'] == InviteCommission::TYPE_RECHARGE) {
                $one['type'] = '充值到账户';
            } elseif ($one['type'] == InviteCommission::TYPE_WITHDRAW) {
                $one['type'] = '提现';
            }
        }
        return $this->render('commissionlist', [
            'commissionList' => $commissionList,
            'pagination' => $pagination,
            'payCommissionSum' => $payCommissionSum,
            'otherCommissionSum' => $otherCommissionSum,
        ]);
    }

    //提现申请
    public function actionApplyMention()
    {
        $userId = Yii::$app->user->id;
        $commissionCount = InviteCommission::find()->where(['user_id' => $userId])->sum('commission');
        $model = new Withdraw();
        $request = Yii::$app->request;
        if ($request->isPost) {
            if ($model->load($request->post())) {
                $trans = Yii::$app->db->beginTransaction();
                $model->user_id = $userId;
                $model->apply_time = time();
                if ($model->validate()) {
                    if (!$model->save()) {
                        $trans->rollBack();
                    } else {
                        $user = \app\models\User::findOne($userId);
                        $user->commission = $user->commission - $model->money * 100;
                        if (!$user->save()) {
                            $trans->rollBack();
                        } else {
                            $trans->commit();
                            return $this->redirect(['invite/mention-list']);
                        }
                    }
                } else {
                    $trans->rollBack();
                }
            }
        }
        return $this->render('applymention', [
            'model' => $model,
            'commissionCount' => $commissionCount,
        ]);
    }

    //提现记录
    public function actionMentionList()
    {
        $request = Yii::$app->request;
        $startTime = $request->get('start');
        $endTime = $request->get('end');
        $timeType = $request->get('type');
        if ($startTime && $endTime) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime . ' 23:59:59');
        }
        if ($timeType == 1) { //今天
            $startTime = strtotime(date('Y-m-d'));
            $endTime = strtotime(date('Y-m-d', strtotime('+1 day')));
        } elseif ($timeType == 2) {//本周
            $timeArr = TimeHelper::getCurWeekStartEnd();
            $startTime = strtotime($timeArr['start']);
            $endTime = strtotime($timeArr['end']);
        } elseif ($timeType == 3) {//本月
            $timeArr = TimeHelper::getCurMonthStartEnd();
            $startTime = strtotime($timeArr['start']);
            $endTime = strtotime($timeArr['end']);
        } elseif ($timeType == 4) {//最近三个月
            $startTime = strtotime(date('Y-m-01', strtotime('-2 month')));
            $endTime = strtotime(date('Y-m-d'));
        }
        $userId = Yii::$app->user->id;
        $query = Withdraw::find()->where(['user_id' => $userId]);
        if ($startTime) {
            $query->andWhere(['>', 'apply_time', $startTime]);
        }
        if ($endTime) {
            $query->andWhere(['<', 'apply_time', $endTime]);
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new yii\data\Pagination(['totalCount' => $totalCount, 'defaultPageSize' => 10]);
        $query->orderBy('id desc');
        $withdrawList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        foreach ($withdrawList as &$one) {
            $one['money'] = sprintf('%.2f', $one['money']);
        }
        return $this->render('mentionlist', [
            'withdrawList' => $withdrawList,
            'pagination' => $pagination,
        ]);
    }

    /** 佣金充值
     * @return string
     */
    public function actionRecharge()
    {
        $userId = Yii::$app->user->id;
        $request = Yii::$app->request;
        $pirce = $request->post('price', 0);
        $pay = new Thirdpay();
        $orderId = $pay->createRechargeOrder($userId, $pirce, 1, 4, 'commission', 1, 0);
        $payResult = $pay->pay($orderId, 'commission');
        $return = $payResult ? ['error' => 0] : ['error' => 1];
        return yii\helpers\Json::encode($return);
    }


}
