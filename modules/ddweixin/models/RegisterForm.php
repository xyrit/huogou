<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/24
 * Time: 下午2:19
 */
namespace app\modules\ddweixin\models;

use Yii;

class RegisterForm extends \app\modules\passport\models\RegisterForm
{
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', 'confirmPassword'], 'required'],
            // username is validated by validateUsername()
            ['username', 'validateUsername'],

            ['password','\app\validators\PasswordValidator'],

            ['confirmPassword', 'compare', 'compareAttribute'=>'password', 'message'=>'两次密码不一致'],

            ['smsCode', 'validateSmsCode', 'skipOnEmpty'=>false],
        ];
    }

}