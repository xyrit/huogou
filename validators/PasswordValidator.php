<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/1/19
 * Time: 下午2:55
 */
namespace app\validators;

use yii\validators\Validator;
use Yii;

class PasswordValidator extends Validator
{
    public $letterPattern = '/[a-zA-z]+/';
    public $numberPattern = '/[0-9]+/';
    public $markPattern = '/[\_\-,\.\/;\'\[\]\\`\=~\!@#\$%\^&\*\(\)\|\{\}\?\+]+/';

    public $fullPattern = '/^[0-9a-zA-Z_\-,\.\/;\'\[\]\\`\=~\!@#\$%\^&\*\(\)\|\{\}\?\+]{8,20}$/';

    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '使用8-20位字母、数字或符号两种或以上组合');
        }
    }


    protected function validateValue($value)
    {
        $len = mb_strlen($value,'utf-8');
        if ($len==0) {
            $this->message = Yii::t('yii', '请设置密码');
            return [$this->message, []];
        } elseif($len<8 || $len>20) {
            return [$this->message, []];
        }

        $valid = preg_match($this->fullPattern, $value);
        if (!$valid) {
            return [$this->message, []];
        }

        $letter = preg_match($this->letterPattern, $value);
        $number = preg_match($this->numberPattern, $value);
        $mark = preg_match($this->markPattern, $value);
        if (($letter&&$number)||($letter&&$mark)||($number&&$mark)) {
            $valid = true;
        } else {
            $valid = false;
        }
        return $valid ? null : [$this->message, []];

    }

}