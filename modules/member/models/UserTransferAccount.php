<?php

namespace app\modules\member\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "user_transfer_account".
 *
 * @property string $id
 * @property string $user_id
 * @property string $to_userid
 * @property string $account
 * @property string $comment
 * @property string $created_at
 */
class UserTransferAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_transfer_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'to_userid', 'account', 'created_at'], 'required'],
            [['user_id', 'to_userid', 'account', 'created_at'], 'integer'],
            [['comment'], 'string', 'max' => 255],
            [['account'], 'checkMoney'],
            [['to_userid'], 'checkUser'],
            [['account'], 'checkAccount']
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
            'to_userid' => '收款账号',
            'account' => '金额',
            'comment' => '备注',
            'created_at' => 'Created At',
        ];
    }

    //转账成功
    public static function rechageSuccess($userid,$to_userid, $account)
    {
        $findUser = User::find()->where(['id'=>$userid])->one();
        $findUser->money =  $findUser['money'] - $account;
        if($findUser->save()){
            $addUser = User::find()->where(['id'=>$to_userid])->one();
            $addUser->money = $addUser['money'] + $account;
            $addUser->save();
        }
    }

    public static function updateUserMoney($userId, $money)
    {
        $user = User::findOne($userId);
        $user->money = $user['money'] + $money;
        return $user->save();
    }

    //充值卡激活成功
    public static function cardSuccess($userId, $money)
    {
        $model = User::findOne($userId);
        $model->money = $model['money'] + $money;
        $model->save();
    }

    public function checkMoney()
    {
        $user = User::findOne($this->user_id);
        if ($user['money'] < $this->account) {
            $this->addError("account", "余额不足");
        }
    }

    public function checkUser()
    {
        if ($this->user_id == $this->to_userid) {
            $this->addError("to_userid", "不能给本人转账");
        }
    }

    public function checkAccount()
    {
        if ($this->account <= 0) {
            $this->addError("account", "金额必须大于0");
        }
    }
}
