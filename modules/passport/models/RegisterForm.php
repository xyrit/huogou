<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/24
 * Time: 下午2:19
 */
namespace app\modules\passport\models;

use app\helpers\Brower;
use app\models\FreeCurrentPeriod;
use app\models\FreeInvite;
use app\models\Invite;
use app\models\InviteLink;
use app\models\PointFollowDistribution;
use app\models\User;
use app\models\UserProfile;
use app\models\Config;
use app\services\Coupon;
use app\services\FreeBuy;
use app\services\Member;
use app\validators\MobileValidator;
use yii\base\Model;
use Yii;
use yii\validators\EmailValidator;

class RegisterForm extends Model
{
    public $username;
    public $smsCode;
    public $password;
    public $confirmPassword;
    public $verifyCode;
    public $spreadSource;
    private $_username_type;
    public $nickname;
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
//
//            ['password','\app\validators\PasswordValidator'],

            ['confirmPassword', 'compare', 'compareAttribute'=>'password', 'message'=>'两次密码不一致'],

            ['smsCode', 'validateSmsCode', 'on'=>'registerCheck', 'skipOnEmpty'=>false],

            ['verifyCode', 'captcha', 'captchaAction'=> '/api/user/captcha', 'on'=>'register'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '手机号/邮箱地址',
            'password' => '密码',
            'confirmPassword' => '确认密码',
            'verifyCode' => '验证码',
        ];
    }

    public function validateUsername($attribute, $params)
    {
        $mobileValidator = new MobileValidator();
        $valid = $mobileValidator->validate($this->username);
        if ($valid) {
            $this->_username_type = 'phone';
            $user = User::findByPhone($this->username);
            if ($user) {
                $this->addError($attribute, '账号已注册.');
            }
            return;
        }
        $emailValidator = new EmailValidator();
        $valid = $emailValidator->validate($this->username);
        if ($valid) {
            $this->_username_type = 'email';
            $user = User::findByEmail($this->username);
            if ($user) {
                $this->addError($attribute, '账号已注册.');
            }
            return;
        }
        $this->addError($attribute, '请输入正确的手机号或邮箱地址.');
    }

    public function validateSmsCode($attribute, $params)
    {
        $smsCode = $this->smsCode;
        $code = \app\services\User::getCode($this->username, 1);
        if ($smsCode && $smsCode==$code) {
            return;
        }
        $this->addError($attribute, '手机/邮箱验证码不正确.');
    }

    public function register()
    {

        $user = new User();
        if ($this->_username_type == 'email') {
            $user->email = $this->username;
        } elseif ($this->_username_type == 'phone') {
            $user->phone = $this->username;
        } else {

        }
        $user->setPassword($this->password);
        $user->generateToken();
        $time = time();
        $user->from = Brower::whereFrom();
        $user->created_at = $time;
        $user->updated_at = $time;
        if($this->spreadSource)
        {
            $user->spread_source=$this->spreadSource;
        }
        $user->updated_at = $time;
        $user->reg_terminal = $this->getRegSource();
        $user->reg_ip = ip2long(Yii::$app->request->userIP);
        if($this->nickname)
        {
          //查询用户
//          if(User::find()->where(['nickname' => $this->nickname])->one())
//          {
//              $user->nickname=$this->nickname.'_'.mt_rand(1000,9999);
//          } else {
//              $user->nickname=$this->nickname;
//          }

            $nickname=$this->nickname;
            do{
                $rs= User::find()->where(['nickname' => $nickname])->one();
                if($rs)
                {
                    $nickname=$this->nickname.'_'.mt_rand(1000,9999);
                    break;
                }

            }while($rs);
             $user->nickname= $nickname;
        }
        $user->spread_source = $this->spreadSource;

       if($user->save(false)){}else{  var_dump($user->getErrors());exit;}


        $user->home_id = User::generateHomeId($user->id);
        $save =  $user->save(false) ? $user : false;
        if ($save) {
            $inviteHomeId = Invite::getInviteIdCookie();
            $invitePeriodId = Invite::getPeriodIdCookie();
            if ($inviteHomeId && $invitePeriodId) {
                try {
                    $fromUser = User::findOne(['home_id'=>$inviteHomeId]);
                    $invite = new FreeInvite();
                    $invite->invite_uid = $user->id;
                    $invite->invite_time = $time;
                    $invite->period_id = $invitePeriodId;
                    $invite->buy_num = 0;
                    $invite->user_id = 0;
                    $inviteLink = InviteLink::findOne(['user_id'=>$fromUser->id]);
                    if ($inviteLink) {
                        $invite->user_id = $inviteLink->user_id;
                    }
                    if ($this->_username_type == 'phone') {
                        $buyNum = FreeBuy::buyByReg($fromUser->id, $invitePeriodId, $this->getRegSource());
                        $invite->buy_num = $buyNum;
                    }
                    $invite->save(false);
                    Invite::removePeriodIdCookie();
                } catch(\Exception $e) {

                    return false;
                }

            }elseif ($inviteHomeId) {
                $fromUser = User::findOne(['home_id'=>$inviteHomeId]);
                if ($fromUser) {
                    $invite = new Invite();
                    $invite->invite_uid = $user->id;
                    $invite->invite_time = $time;
                    $invite->status = Invite::STATUS_UNCONSUME;
                    $invite->user_id = 0;
                    $inviteLink = InviteLink::findOne(['user_id'=>$fromUser->id]);
                    if ($inviteLink) {
                        $invite->user_id = $inviteLink->user_id;
                    }
                    $invite->save(false);
                    Invite::removeInviteIdCookie();
                }
            }
            //注册红包
            $config = Config::getValueByKey('regconfig');
            if ($config['status'] == '1' && (!$config['starttime'] || $config['starttime'] < time()) && (!$config['endtime'] || $config['endtime'] > time()) ) {
                $packet = Coupon::receivePacket($config['packet_id'], $user->id, 'reg');
                if ($packet['code'] == '0') {
                   $packetId = $packet['data']['pid'];
                   Coupon::openPacket($packetId, $user->id);
                }      
            }
        }
        return $save;
    }

    public function getRegisterInfo()
    {
        $info = [];
        $info['username'] = $this->username;
        $info['password'] = $this->password;
        $info['verifyCode'] = $this->verifyCode;
        return $info;
    }

    public function sendRegisterCode()
    {
        return \app\services\User::sendCode($this->username, 1, true);
    }

    private $_regsource = 0;
    public function setRegSource($source)
    {
        $this->_regsource = $source;
    }

    public function getRegSource()
    {
       return $this->_regsource;
    }

    /*
     * 绑定手机
     */
    public function bindPhone($id){

        $user = User::findOne($id);
        $time=time();
        $user->phone = $this->username;
        $user->password = Yii::$app->security->generatePasswordHash($this->password);;
        $user->updated_at = $time;
        $rs= $user->save(false);
        return $rs;
    }

}