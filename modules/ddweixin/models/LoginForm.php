<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/24
 * Time: 下午12:31
 */
namespace app\modules\ddweixin\models;

use app\helpers\Message;
use app\models\User;
use app\validators\MobileValidator;
use yii\base\Model;
use Yii;
use yii\validators\EmailValidator;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    private $_user = false;
    private $_username_type;
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // username is validated by validateUsername()
            ['username', 'validateUsername'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '手机号/邮箱地址',
            'password' => '密码',
        ];
    }

    public function validateUsername($attribute, $params)
    {
        $mobileValidator = new MobileValidator();
        $valid = $mobileValidator->validate($this->username);
        if ($valid) {
            $this->_username_type = 'phone';
            return;
        }
        $emailValidator = new EmailValidator();
        $valid = $emailValidator->validate($this->username);
        if ($valid) {
            $this->_username_type = 'email';
            return;
        }
        $this->addError($attribute, '请输入正确的手机号或邮箱地址.');
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addError('username', '账户不存在.');
            } elseif (!$user->validatePassword($this->password)) {
                $this->addError($attribute, '登录密码错误，请重新输入.');
            } else {
                if ($user->status==1) {
                    $this->addError('username', '账户被冻结，请联系客服.');
                }
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login($duration = 3600)
    {
        if ($this->validate()) {
            $logined =  Yii::$app->user->login($this->getUser(), $duration);
            if ($logined) {
                $ip = Yii::$app->request->userIP;
                Message::send(10,$this->user->id,['account'=>$this->username,'ip'=>$ip,'client'=>'微信公众号','time'=>date('Y-m-d H:i:s')]);
            }
            return $logined;
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return \app\models\User |null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            if ($this->_username_type == 'phone') {
                $this->_user = User::findByPhone($this->username);
            } elseif ($this->_username_type == 'email') {
                $this->_user = User::findByEmail($this->username);
            }
        }
        return $this->_user;
    }
}