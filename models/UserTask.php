<?php

namespace app\models;

use app\helpers\MyRedis;
use app\services\Coupon;
use app\services\Member;
use app\services\Period;
use Yii;

/**
 * This is the model class for table "user_tasks".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $task_id
 * @property integer $status
 * @property integer $progress
 * @property integer $complete_time
 */
class UserTask extends \yii\db\ActiveRecord
{
    const DAILY_TASK_KEY = 'DAILY_TASK_';
    const USER_TASK = 'ACTIVE_USER_TASK_';
    const TASK_LIMIT = 'TASK_LIMIT_';
    const START_TIME = '2016-04-12';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_tasks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'task_id', 'status', 'progress', 'complete_time'], 'integer']
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
            'task_id' => 'Task ID',
            'status' => 'Status',
            'progress' => 'Progress',
            'complete_time' => 'Complete Time',
        ];
    }

    public static function taskList($userId, $type, $level = 0)
    {
        switch ($type) {
            case 1:
                self::newTask($userId);
                break;
            case 2:
                return self::dailyTask($userId);
                break;
            case 3:
                self::growTask($userId, $level);
                break;
        }
        $userTask = UserTask::find()->leftJoin('tasks', 'user_tasks.task_id=tasks.id')->where(['user_tasks.user_id' => $userId, 'tasks.type' => $type, 'tasks.level' => $level])->select(['user_tasks.*', 'tasks.*'])->orderBy('tasks.cate asc, tasks.id asc')->asArray()->all();
        foreach ($userTask as &$one) {
            if ($one['type'] == 3 && in_array($one['level'], [1, 2])) {
                $one['name'] = $one['name'] . $one['num'] . ($one['level'] == 1 ? '次' : '元');
            }
            if ($one['award_type'] == 1) {
                $one['award'] = '奖励' . $one['award_num'] . '福分';
            } elseif ($one['award_type'] == 2) {
                $one['award'] = '奖励' . $one['award_num'] . '伙购币';
            } elseif ($one['award_type'] == 3) {
                $packet = Packet::findOne($one['award_num']);
                $one['award'] = '奖励' . $packet['name'];
            }
        }
        return ['list' => $userTask];
    }

    // 新手任务
    private static function newTask($userId)
    {
        $userInfo = User::findOne($userId);
        $userTask = UserTask::find()->leftJoin('tasks', 'user_tasks.task_id=tasks.id')->where(['user_tasks.user_id' => $userId, 'tasks.type' => 1])->asArray()->all();
        if (empty($userTask)) {
            $tasks = Task::find()->where(['type' => 1])->all();
            foreach ($tasks as $one) {
                $status = 0;
                if ($one['id'] == 2) {// 修改昵称
                    $status = $userInfo['nickname'] ? 1 : 0;
                } elseif ($one['id'] == 3) {// 修改头像
                    $status = $userInfo['avatar'] != '000000000000.jpg' ? 1 : 0;
                } elseif ($one['id'] == 4) {// 手机验证
                    $status = $userInfo['phone'] ? 1 : 0;
                } elseif ($one['id'] == 5) {// 完善收货地址
                    $status = self::getAddress($userId);
                } elseif ($one['id'] == 6) {// 成功够买商品1次
                    $status = self::successBuy($userId);
                } elseif ($one['id'] == 7) {// 获得好友充值返利
                    $status = self::getCommisson($userId);
                }
                $progress = $status == 0 ? 0 : 1;
                self::addTask($userId, $one['id'], $status, $progress);
            }
        } else {
            foreach ($userTask as $one) {
                if ($one['status'] == 0) {
                    if ($one['task_id'] == 6 && self::successBuy($userId) == 1) {
                        self::updateTask($one['id'], ['status' => 1, 'progress' => 1]);
                    } elseif ($one['task_id'] == 2 && $userInfo['nickname']) {
                        self::updateTask($one['id'], ['status' => 1, 'progress' => 1]);
                    }  elseif ($one['task_id'] == 3 && $userInfo['avatar'] != '000000000000.jpg') {
                        self::updateTask($one['id'], ['status' => 1, 'progress' => 1]);
                    }  elseif ($one['task_id'] == 4 && $userInfo['phone']) {
                        self::updateTask($one['id'], ['status' => 1, 'progress' => 1]);
                    }  elseif ($one['task_id'] == 5 && self::getAddress($userId) == 1) {
                        self::updateTask($one['id'], ['status' => 1, 'progress' => 1]);
                    } elseif ($one['task_id'] == 7 && self::getCommisson($userId) == 1) {
                        self::updateTask($one['id'], ['status' => 1, 'progress' => 1]);
                    }
                }
            }
        }
    }

    private static function successBuy($userId, $today = 0)
    {
        $user = User::findOne($userId);
        $tableId = PaymentOrderDistribution::getTableIdByUserHomeId($user['home_id']);
        $query = PaymentOrderDistribution::findByTableId($tableId)->where(['user_id' => $userId, 'status' => 1]);
        if ($today) {
            $query->andWhere(['between', 'create_time', strtotime(date('Y-m-d')), time()]);
        }
        $payment = $query->one();
        return $payment ? 1 : 0;
    }

    private static function getAddress($userId)
    {
        $address = UserAddress::findOne(['uid' => $userId]);
        $Virtualaddress  = UserVirtualAddress::findOne(['user_id' => $userId]);
        if($address || $Virtualaddress)
        {
            return 1;
        }else{
            return 0;
        }

    }

    private static function getCommisson($userId)
    {
        $invite = Invite::findOne(['user_id' => $userId, 'status' => 1]);
        return $invite ? 1 : 0;
    }

    private static function winning($userId, $today = 0)
    {
        $query= Order::find()->leftJoin('periods as p', 'orders.period_id=p.id')->where(['orders.user_id' => $userId]);
        if ($today) {
            $query->andWhere(['between', 'orders.create_time', strtotime(date('Y-m-d')), time()]);
        }
        $query->andWhere(['<=','p.result_time', time()]);
        $order = $query->one();
        return $order ? 1 : 0;
    }

    // 日常任务
    private static function dailyTask($userId)
    {
        $key = self::DAILY_TASK_KEY . date('Y-m-d') . '_' . $userId;
        $redis = new MyRedis();

        $data = $redis->hget($key, 'all');
        $tasks = Task::find()->where(['type' => 2])->asArray()->all();
        foreach ($tasks as &$one) {
            if ($one['award_type'] == 1) {
                $one['award'] = '奖励' . $one['award_num'] . '福分';
            } elseif ($one['award_type'] == 2) {
                $one['award'] = '奖励' . $one['award_num'] . '伙购币';
            } elseif ($one['award_type'] == 3) {
                $packet = Packet::findOne($one['award_num']);
                $one['award'] = '奖励' . $packet['name'];
            }
            if (!isset($data[$one['id']]) || $data[$one['id']] == 0) {
                switch ($one['id']) {
                    case 8:
                        $one['status'] = 0;
                        break;
                    case 9: //成功伙购1次
                        $one['status'] = self::successBuy($userId, 1);
                        break;
                    case 10: //中奖1次
                        $one['status'] = self::winning($userId, 1);
                        break;
                    case 11: //登荣誉榜1次
                        $one['status'] = self::gloryNum($userId, 1) > 0 ? 1 : 0;
                        break;
                    case 12: //土豪君称号1次
                        $one['status'] = self::richNum($userId, 1) > 0 ? 1 : 0;
                        break;
                    case 13: //沙发君称号1次
                        $one['status'] = self::firstNum($userId, 1) > 0 ? 1 : 0;
                        break;
                    case 14: //收尾君称号1次
                        $one['status'] = self::endNum($userId, 1) > 0 ? 1 : 0;
                        break;
                }
            } else {
                $one['status'] = $data[$one['id']];
            }
            $one['progress'] = $one['status'] == 0 ? 0 : 1;
            $redis->hset($key, [$one['id'] => $one['status']]);
            $one['task_id'] = $one['id'];
        }
        if ($redis->ttl($key) <= 0) {
            $time = strtotime("tomorrow") - time() - 1;
            $redis->expire($key, $time);
        }
        return ['list' => $tasks];
    }

    // 成长任务
    private static function growTask($userId, $level)
    {
        switch ($level) {
            case 1:
                self::glory($userId);
                break;
            case 2:
                self::payment($userId);
                break;
            case 3:
                self::level($userId);
                break;
        }
    }

    // 称号
    private static function glory($userId)
    {
        $userTask = UserTask::find()->leftJoin('tasks', 'user_tasks.task_id=tasks.id')->where(['user_tasks.user_id' => $userId, 'tasks.type' => 3, 'tasks.level' => 1])->select(['user_tasks.*', 'tasks.num', 'tasks.cate'])->asArray()->all();
        if (empty($userTask)) {
            self::gloryTask($userId);
            self::richTask($userId);
            self::firstTask($userId);
            self::endTask($userId);
        } else {
            foreach ($userTask as $one) {
                $status = 0;
                $progress = 0;
                if ($one['status'] == 0) {
                    if ($one['cate'] == 1) {
                        $progress = self::gloryNum($userId);
                        if ($progress >= $one['num']) {
                            $progress = $one['num'];
                            $status = 1;
                        }
                    } elseif ($one['cate'] == 2) {
                        $progress = self::richNum($userId);
                        if ($progress >= $one['num']) {
                            $progress = $one['num'];
                            $status = 1;
                        }
                    } elseif ($one['cate'] == 3) {
                        $progress = self::firstNum($userId);
                        if ($progress >= $one['num']) {
                            $progress = $one['num'];
                            $status = 1;
                        }
                    } elseif ($one['cate'] == 4) {
                        $progress = self::endNum($userId);
                        if ($progress >= $one['num']) {
                            $progress = $one['num'];
                            $status = 1;
                        }
                    }
                    if ($status > 0 || $progress > 0) {
                        self::updateTask($one['id'], ['status' => $status, 'progress' => $progress]);
                    }
                }
            }
        }
    }

    private static function gloryTask($userId, $num = 0)
    {
        $query = Task::find()->where(['type' => 3, 'level' => 1, 'cate' => 1]);
        if ($num) {
            $query->andWhere(['>', 'num', $num]);
        }
        $gloryTasks = $query->orderBy('num asc')->one();
        $progress = self::gloryNum($userId);
        $status = 0;
        if ($progress >= $gloryTasks['num']) {
            $progress = $gloryTasks['num'];
            $status = 1;
        }
        self::addTask($userId, $gloryTasks['id'], $status, $progress);
        return $gloryTasks['id'];
    }

    private static function richTask($userId, $num = 0)
    {
        $query = Task::find()->where(['type' => 3, 'level' => 1, 'cate' => 2]);
        if ($num) {
            $query->andWhere(['>', 'num', $num]);
        }
        $richTasks = $query->orderBy('num asc')->one();
        $progress = self::richNum($userId);
        $status = 0;
        if ($progress >= $richTasks['num']) {
            $progress = $richTasks['num'];
            $status = 1;
        }
        self::addTask($userId, $richTasks['id'], $status, $progress);
        return $richTasks['id'];
    }

    private static function firstTask($userId, $num = 0)
    {
        $query = Task::find()->where(['type' => 3, 'level' => 1, 'cate' => 3]);
        if ($num) {
            $query->andWhere(['>', 'num', $num]);
        }
        $firstTasks = $query->orderBy('num asc')->one();
        $progress = self::firstNum($userId);
        $status = 0;
        if ($progress >= $firstTasks['num']) {
            $progress = $firstTasks['num'];
            $status = 1;
        }
        self::addTask($userId, $firstTasks['id'], $status, $progress);
        return $firstTasks['id'];
    }

    private static function endTask($userId, $num = 0)
    {
        $query = Task::find()->where(['type' => 3, 'level' => 1, 'cate' => 4]);
        if ($num) {
            $query->andWhere(['>', 'num', $num]);
        }
        $endTasks = $query->orderBy('num asc')->one();
        $status = 0;
        $progress = self::endNum($userId);
        if ($progress >= $endTasks['num']) {
            $progress = $endTasks['num'];
            $status = 1;
        }
        self::addTask($userId, $endTasks['id'], $status, $progress);
        return $endTasks['id'];
    }

    private static function gloryNum($userId, $today = 0)
    {
        $richNum = self::richNum($userId, $today);
        $firstNum = self::firstNum($userId, $today);
        $endNum = self::endNum($userId, $today);
        return $richNum + $firstNum + $endNum;
    }

    private static function richNum($userId, $today = 0)
    {
        $startTime = strtotime(self::START_TIME);
        $query = Honour::find()->leftJoin('periods p', 'p.id=honour.period')
            ->where(['rich_userid' => $userId])->andWhere(['<>', 'end_userid', 0]);
        if ($today) {
            $query->andWhere(['>', 'p.end_time', strtotime(date('Y-m-d'))]);
            $query->andWhere(['<=','p.result_time', time()]);
            $query->andWhere(['>','p.user_id', 0]);
        } else {
            $query->andWhere(['>', 'p.end_time', $startTime]);
            $query->andWhere(['<=','p.result_time', time()]);
            $query->andWhere(['>','p.user_id', 0]);
        }
        return $query->count();
    }

    private static function firstNum($userId, $today = 0)
    {
        $startTime = strtotime(self::START_TIME);
        $query = Honour::find()->leftJoin('periods p', 'p.id=honour.period')
            ->where(['first_userid' => $userId])->andWhere(['<>', 'end_userid', 0]);
        if ($today) {
            $query->andWhere(['>', 'p.end_time', strtotime(date('Y-m-d'))]);
            $query->andWhere(['<=','p.result_time', time()]);
            $query->andWhere(['>','p.user_id', 0]);
        } else {
            $query->andWhere(['>', 'p.end_time', $startTime]);
            $query->andWhere(['<=','p.result_time', time()]);
            $query->andWhere(['>','p.user_id', 0]);
        }
        return $query->count();
    }

    private static function endNum($userId, $today = 0)
    {
        $startTime = strtotime(self::START_TIME);
        $query = Honour::find()->leftJoin('periods p', 'p.id=honour.period')
            ->where(['end_userid' => $userId]);
        if ($today) {
            $query->andWhere(['>', 'p.end_time', strtotime(date('Y-m-d'))]);
            $query->andWhere(['<=','p.result_time', time()]);
            $query->andWhere(['>','p.user_id', 0]);
        } else {
            $query->andWhere(['>', 'p.end_time', $startTime]);
            $query->andWhere(['<=','p.result_time', time()]);
            $query->andWhere(['>','p.user_id', 0]);
        }
        return $query->count();
    }

    // 充值
    private static function payment($userId)
    {
        $userTask = UserTask::find()->leftJoin('tasks', 'user_tasks.task_id=tasks.id')->where(['user_tasks.user_id' => $userId, 'tasks.type' => 3, 'tasks.level' => 2])->select(['user_tasks.*', 'tasks.num'])->asArray()->all();
        $totalMoney = self::totalMoney($userId);
        if (empty($userTask)) {
            $tasks = Task::find()->where(['type' => 3, 'level' => 2])->all();
            foreach ($tasks as $one) {
                $status = 0;
                $progress = $totalMoney;
                if ($totalMoney >= $one['num']) {
                    $progress = $one['num'];
                    $status = 1;
                }
                self::addTask($userId, $one['id'], $status, $progress);
            }
        } else {
            foreach ($userTask as $one) {
                if ($one['status'] == 0) {
                    $params = ['progress' => $totalMoney];
                    if ($totalMoney >= $one['num']) {
                        $params['progress'] = $one['num'];
                        $params['status'] = 1;
                    }
                    self::updateTask($one['id'], $params);
                }
            }
        }
    }

    private static function totalMoney($userId)
    {
        $startTime = strtotime("2016-04-12");
        $user = User::findOne($userId);
        $tableId = RechargeOrderDistribution::getTableIdByUserHomeId($user['home_id']);
        $query = RechargeOrderDistribution::findByTableId($tableId)->where(['user_id' => $userId])->andWhere(['>=', 'pay_time', $startTime])->andWhere(['payment' => [1, 2, 3]]);
        $query->andWhere(['=', 'status', RechargeOrderDistribution::STATUS_PAID]);
        $query->andWhere(['<>', 'money', 0]);
        $totalMoney = $query->select('SUM(post_money) as totalMoney')->scalar();
        return $totalMoney ? $totalMoney : 0;
    }

    // 等级
    private static function level($userId)
    {
        $userTask = UserTask::find()->leftJoin('tasks', 'user_tasks.task_id=tasks.id')->where(['user_tasks.user_id' => $userId, 'tasks.type' => 3, 'tasks.level' => 3])->select(['user_tasks.*', 'tasks.num'])->asArray()->all();
        $user = User::findOne($userId);
        if (empty($userTask)) {
            $tasks = Task::find()->where(['type' => 3, 'level' => 3])->all();
            foreach ($tasks as $one) {
                $status = 0;
                $progress = $user['experience'];
                if ($user['experience'] >= $one['num']) {
                    $progress = $one['num'];
                    $status = 1;
                }
                self::addTask($userId, $one['id'], $status, $progress);
            }
        } else {
            foreach ($userTask as $one) {
                if ($one['status'] == 0) {
                    $params = ['progress' => $user['experience']];
                    if ($user['experience'] >= $one['num']) {
                        $params['progress'] = $one['num'];
                        $params['status'] = 1;
                    }
                    self::updateTask($one['id'], $params);
                }
            }
        }
    }

    private static function addTask($userId, $taskId, $status, $progress = 0)
    {
        $userTask = UserTask::findOne(['user_id' => $userId, 'task_id' => $taskId]);
        if ($userTask) {
            return true;
        }
        $userTask = new UserTask();
        $userTask->user_id = $userId;
        $userTask->task_id = $taskId;
        $userTask->status = $status;
        $userTask->progress = $progress;
        $userTask->save();
    }

    private static function updateTask($id, $params)
    {
        $userTask = UserTask::findOne($id);
        if ($userTask) {
            isset($params['status']) && $userTask->status = $params['status'];
            isset($params['progress']) && $userTask->progress = $params['progress'];
            isset($params['complete_time']) && $userTask->complete_time = $params['complete_time'];
            $userTask->save();
        }
    }

    // 领取奖励
    public static function completeTask($userId, $taskId, $source)
    {
        $task = Task::findOne($taskId);
        $status = 0;
        if ($task) {
            $redis = new MyRedis();
            $limitKey = self::TASK_LIMIT . date('Y-m-d');

            if ($task['type'] == 2) {// 日常任务
                $key = self::DAILY_TASK_KEY . date('Y-m-d') . '_' . $userId;
                $status = $redis->hget($key, $taskId);
                if ($status == 1) {
                    if (!$redis->sset($limitKey, $userId . '_' .$taskId)) {
                        return false;
                    }
                    $redis->hset($key, [$taskId => 2]);
                    self::award($userId, $task['award_type'], $task['award_num'], $source);
                    $member = new Member(['id' => $userId]);
                    $member->taskLog($task['name'], $source, UserTaskFollowDistribution::TASK_DAILY);
                }
            } else {
                $userTask = UserTask::find()->where(['user_id' => $userId, 'task_id' => $taskId])->one();
                $status = $userTask['status'];
                if ($userTask['status'] == 1) {
                    if (!$redis->sset($limitKey, $userId . '_' .$taskId)) {
                        return false;
                    }
                    self::award($userId, $task['award_type'], $task['award_num'], $source);
                    $member = new Member(['id' => $userId]);
                    if ($task['type'] == 1) {
                        $member->taskLog($task['name'], $source, UserTaskFollowDistribution::TASK_NEW);
                    } elseif ($task['type'] == 3) {
                        if ($task['level'] == 1) {
                            $content = $task['name'] . $task['num'] . '次';
                        } elseif ($task['level'] == 2) {
                            $content = $task['name'] . $task['num'] . '元';
                        } elseif ($task['level'] == 3) {
                            $content = $task['name'];
                        }
                        $member->taskLog($content, $source, UserTaskFollowDistribution::TASK_GROW, $task['level'], $task['cate'], $task['num']);
                    }
                    if ($task['type'] == 3 && $task['level'] == 1) {
                        return self::updateGloryTask($userId, $userTask['id'], $task);
                    } else {
                        self::updateTask($userTask['id'], ['status' => 2, 'complete_time' => time()]);
                    }
                }
            }
            $redis->expire($limitKey, strtotime("tomorrow") - time() - 1);
        }
        return $status;
    }

    private static function award($userId, $type, $num, $source)
    {
        switch ($type) {
            case 1: // 福分
                $member = new Member(['id' => $userId]);
                $member->editPoint($num, PointFollowDistribution::POINT_TASK, '任务获得' . $num . '福分');
                $name = $num . "福分";
                break;
            case 2: // 伙购币
                $member = new Member(['id' => $userId]);
                $member->editMoney($num, MoneyFollowDistribution::MONEY_TASK, '任务获得' . $num . '伙购币', $source);
                $name = $num . "伙购币";
                break;
            case 3: // 红包
                $packet = Coupon::receivePacket($num, $userId, 'task');
                if ($packet['code'] == 0) {
                    Coupon::openPacket($packet['data']['pid'], $userId);
                }
                $packet = Packet::findOne($num);
                $name = $packet['name'];
                break;
        }

        return $name;
    }

    private static function updateGloryTask($userId, $userTaskId, $task)
    {
        $query = Task::find()->where(['type' => 3, 'level' => 1, 'cate' => $task['cate']]);
        $query->andWhere(['>', 'num', $task['num']]);
        $gloryTasks = $query->orderBy('num asc')->one();
        if (empty($gloryTasks)) {
            self::updateTask($userTaskId, ['status' => 2, 'complete_time' => time()]);
            return 1;
        } else {
            UserTask::deleteAll(['user_id' => $userId, 'task_id' => $task['id']]);
            switch ($task['cate']) {
                case 1:
                    $taskId = self::gloryTask($userId, $task['num']);
                    break;
                case 2:
                    $taskId = self::richTask($userId, $task['num']);
                    break;
                case 3:
                    $taskId = self::firstTask($userId, $task['num']);
                    break;
                case 4:
                    $taskId = self::endTask($userId, $task['num']);
                    break;
            }
            if ($taskId) {
                $userTask = UserTask::find()->leftJoin('tasks', 'user_tasks.task_id=tasks.id')->where(['user_tasks.user_id' => $userId, 'task_id' => $taskId])->select(['user_tasks.*', 'tasks.*'])->asArray()->one();
                if ($userTask) {
                    if ($userTask['type'] == 3 && in_array($userTask['level'], [1, 2])) {
                        $userTask['name'] = $userTask['name'] . $userTask['num'] . ($userTask['level'] == 1 ? '次' : '元');
                    }
                    if ($userTask['award_type'] == 1) {
                        $userTask['award'] = '奖励' . $userTask['award_num'] . '福分';
                    } elseif ($userTask['award_type'] == 2) {
                        $userTask['award'] = '奖励' . $userTask['award_num'] . '伙购币';
                    } elseif ($userTask['award_type'] == 3) {
                        $packet = Packet::findOne($userTask['award_num']);
                        $userTask['award'] = '奖励' . $packet['name'];
                    }
                }
                return $userTask;
            }
        }
    }
}
