<?php
/**
 * 账户充值
 */
namespace app\modules\member\controllers;
use app\helpers\Brower;
use app\helpers\Message;
use app\helpers\MyRedis;
use app\models\RechargeOrderDistribution;
use yii;
use app\services\User as ServiceUser;
use app\modules\member\models\UserTransferAccount;
use app\models\User;

class RechargeController extends BaseController
{
    public $userInfo;

    public function init()
    {
        parent::init();
        $userId = Yii::$app->user->id;
        $this->userInfo = ServiceUser::allInfo($userId);
        if(!$userId){
            return \Yii::$app->user->loginRequired();
        }
    }

    /**
     * 网银充值
     */
    public function actionIndex()  
    {  
        
     return $this->render('index', [
         'userInfo' => $this->userInfo
     ]);
  
    }
    
   /**
    * 充值卡充值 
    */
    public function actionCard(){
        
        return $this->render('card');
    }

    /**
     * 转账
     **/
    public function actionTransfer()
    {
        $request = Yii::$app->request;
        $userId = Yii::$app->user->id;
        $model = new UserTransferAccount();
        $result = [];
        if ($request->isPost) {
            $post = $request->post();
            if (1) {
                $result = ['code' => 101, 'msg' => ['error' => '转账功能已停用']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }
            if (empty($post['username'])) {
                $result = ['code' => 101, 'msg' => ['error' => '收款账号不能为空']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }
            if (empty($post['paypwd'])) {
                $result = ['code' => 101, 'msg' => ['error' => '支付密码不能为空']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }
            if(!Yii::$app->security->validatePassword($post['paypwd'], $this->userInfo['pay_password'])){
                $redis = new MyRedis();
                $wrongPasswordKey = 'WRONG_PAY_PASSWORD_NUMS_'.$this->userId;
                $wrongPasswordNum = $redis->incr($wrongPasswordKey);
                $redis->expire($wrongPasswordKey, 3600);
                if ($wrongPasswordNum>=5) {
                    User::updateAll(['status'=>1], ['id'=>$userId]);
                    $result = ['code' => 101, 'msg' => ['error'=>'支付密码错误5次,账户被锁定,请联系客服!']];
                } else {
                    $result = ['code' => 101, 'msg' => ['error' => '支付密码错误']];
                }
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }
            $user = \app\services\User::baseInfo($userId);
            if ($user['status'] == 1) {
                $result = ['code' => 101, 'msg' => ['error' => '您的账号已冻结，请联系客服']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }
            $touser = User::find()->where(['or', 'email="' . $post['username'] . '"', 'phone="' . $post['username'] . '"'])->andWhere(['=','from',Brower::whereFrom()])->one();
            if (empty($touser)) {
                $result = ['code' => 101, 'msg' => ['error' => '收款账号不存在']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            } elseif ($touser['status'] == 1) {
                $result = ['code' => 101, 'msg' => ['error' => '收款账号已冻结，请联系该用户']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }

            $homeId = Yii::$app->user->identity->home_id;
            $recharge = RechargeOrderDistribution::findByTableId($homeId)
                ->select('sum(money) as total')
                ->where(['user_id'=>$userId, 'status'=>1, 'payment'=>[1,2,3]])
                ->asArray()
                ->one();
            if (!$recharge || $recharge['total']==0) {
                return ['code' => 104, 'msg' => '转账失败!'];
            }

            $tranferOutTodayNum = UserTransferAccount::find()->where(['user_id'=>$userId])->andWhere(['>','created_at',time()-3600])->count();
            if ($tranferOutTodayNum>=10) {
                User::updateAll(['status'=>1], ['id'=>$userId]);
                $result = ['code' => 101, 'msg' => ['error'=>'账户被锁定,请联系客服!']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }
            $tranferInTodayNum = UserTransferAccount::find()->where(['to_userid'=>$touser['id']])->andWhere(['>','created_at',time()-3600])->count();
            if ($tranferInTodayNum>=10) {
                User::updateAll(['status'=>1], ['id'=>$touser['id']]);
                $result = ['code' => 101, 'msg' => ['error'=>'被转账户已被锁定,请联系该用户!']];
                echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
                Yii::$app->end();
            }
            if ($model->load($post)) {
                $trans = Yii::$app->db->beginTransaction();
                $model->user_id = $userId;
                $model->to_userid = $touser['id'];
                $model->created_at = time();
                if ($model->validate()) {
                    if (!$model->save()) {
                        $trans->rollBack();
                        $result = ['code' => 101, 'msg' => ['error' => '转账失败']];
                    } else {
                        $account = $post['UserTransferAccount']['account'];
                        if (!UserTransferAccount::updateUserMoney($userId, -1 * $account)) {
                            $trans->rollBack();
                            $result = ['code' => 101, 'msg' => ['error' => '转账失败']];
                        } elseif (!UserTransferAccount::updateUserMoney($touser['id'], $account)) {
                            $trans->rollBack();
                            $result = ['code' => 101, 'msg' => ['error' => '转账失败']];
                        } else {
                            $result = ['code' => 100];
                            $tousername = User::userName($touser['id']);
                            Message::send(26, $touser['id'], ['nickname' => $tousername['username'],
                                                                'oppositeNickname' => $user['username'],
                                                                'time' => date('Y-m-d H:i:s', time()),
                                                                'money' => $account]);
                            $trans->commit();
                        }
                    }
                } else {
                    $trans->rollBack();
                    $result = ['code' => 101, 'msg' => $model->firstErrors];
                }
            } else {
                $result = ['code' => 101, 'msg' => ['error' => '转账失败']];
            }
        }
        echo '<script type="text/javascript">window.parent.result(' . json_encode($result) . ')</script>';
        Yii::$app->end();
    }

    /**
     * 验证账号是否正确
     **/
    public function actionCheckUsername()
    {
        $request = Yii::$app->request;
        if($request->isPost){
            $user = $request->post('username');
            $exist = User::find()->where(['or', 'email="'.$user.'"', 'phone="'.$user.'"'])->andWhere(['=','from',Brower::whereFrom()])->one();
            if($exist['id']){
                return 1;
            }else{
                return 0;
            }
        }
    }

    /**
     * 余额明细
     */
    public function actionMoneyLog()
    {
        $index = Yii::$app->request->get('index', 0);
        $userInfo = User::findOne(['id' => Yii::$app->user->id]);
        return $this->render('moneylog', ['userInfo' => $userInfo, 'index' => $index]);
    }

}
