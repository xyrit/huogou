<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/28
 * Time: 14:37
 */
namespace app\modules\help\models;

use yii\base\Model;
use Yii;
use yii\captcha\CaptchaValidator;

class SuggestionForm extends Model
{
    public $type;
    public $nickname;
    public $email;
    public $content;
    public $verifyCode;
    public $phone;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['type', 'phone'], 'integer'],
            [['email', 'content','verifyCode'], 'required'],
            [['email'], 'email'],
            ['phone','match','pattern'=>'/^1[0-9]{10}$/','message'=>'{attribute}必须为1开头的11位纯数字'],
            [['content'], 'string'],
            [['content'], 'string', 'min'=>20, 'tooShort'=>'评价太短了'],
            [['email'], 'string', 'max' => 100],
            ['verifyCode', 'captcha', 'captchaAction'=> '/api/user/captcha', 'on'=>'suggestion'],
            ['verifyCode', 'checkCaptcha'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '主题',
            'nickname' => '昵称',
            'phone' => '电话',
            'email' => 'E-mail',
            'content' => '反馈内容',
            'verifyCode' => '验证码',
        ];
    }

    public function checkCaptcha($attribute)
    {
        $captchaValidator = new CaptchaValidator();
        $captchaValidator->captchaAction = '/api/user/captcha';
        $valid = $captchaValidator->validate($this->verifyCode);
        if (!$valid) {
            $this->addError($attribute, '验证码错误.');
            return;
        }
    }
}