<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "withdraw".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $money
 * @property string $account
 * @property string $bank
 * @property string $branch
 * @property string $phone
 * @property string $bank_number
 * @property integer $status
 * @property integer $payment
 * @property string $payment_no
 * @property integer $payment_money
 * @property integer $apply_time
 * @property integer $audit_time
 * @property integer $pass_time
 * @property integer $audit_user
 * @property integer $pass_user
 * @property integer $fail_reason
 */
class Withdraw extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'withdraw';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'money', 'account', 'bank', 'branch', 'phone', 'bank_number'], 'required'],
            [['user_id', 'money', 'status', 'payment', 'payment_money', 'apply_time', 'audit_time', 'pass_time', 'audit_user', 'pass_user'], 'integer'],
            [['account'], 'string', 'max' => 20],
            [['bank'], 'string', 'max' => 40],
            [['branch', 'payment_no'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 11],
            [['bank_number'], 'string', 'max' => 30],
            [['money'], 'compare', 'compareValue' => 100, 'operator'=>'>='],
            [['phone'], '\app\validators\MobileValidator', 'message'=>'请输入正确的手机号'],
            [['money'], 'checkMoney'],
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
            'money' => '提现金额',
            'account' => '开户人',
            'bank' => '银行名称',
            'branch' => '开户支行',
            'phone' => '联系电话',
            'bank_number' => '银行帐号',
            'status' => 'Status',
            'payment' => 'Payment',
            'payment_no' => 'Payment No',
            'payment_money' => 'Payment Money',
            'apply_time' => 'Apply Time',
            'audit_time' => 'Audit Time',
            'pass_time' => 'Pass Time',
            'audit_user' => 'Audit User',
            'pass_user' => 'Pass User',
            'fail_type' => 'Fail Type',
        ];
    }

    public function checkMoney()
    {
        $user = User::findOne($this->user_id);
        if ($user['commission'] < $this->money * 100) {
            $this->addError("money", "佣金余额不足");
        }
    }
}
