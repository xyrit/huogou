<?php

namespace app\models;

use app\modules\admin\models\Keyword;
use app\validators\MobileValidator;
use Yii;
use yii\helpers\ArrayHelper;
use app\helpers\Brower;
use yii\validators\EmailValidator;
use yii\web\IdentityInterface;
use yii\data\Pagination;
/**
 * This is the model class for table "users".
 *
 * @property integer $id
 * @property integer $home_id
 * @property string $password
 * @property string $email
 * @property string $phone
 * @property string $nickname
 * @property string $avatar
 * @property integer $money
 * @property integer $commission
 * @property integer $point
 * @property integer $experience
 * @property string $pay_password
 * @property string $password_reset_token
 * @property string $token
 * @property integer $status
 * @property integer $created_at
 * @property integer $from
 * @property integer $updated_at
 * @property integer $micro_pay
 * @property integer $last_login_ip
 * @property integer $protected_status
 * @property integer $reg_terminal
 * @property integer $reg_ip
* @property string $spread_source
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['home_id', 'password', 'created_at', 'updated_at'], 'required'],
            [['home_id', 'money', 'commission', 'point', 'experience', 'status', 'created_at', 'updated_at'], 'integer'],
            [['password', 'email', 'avatar', 'pay_password', 'password_reset_token', 'token','spread_source'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 11],
            [['nickname'], 'string', 'max' => 60],
            [['home_id'], 'unique'],
            [['token'], 'unique'],
            [['nickname'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'home_id' => 'Home ID',
            'password' => 'Password',
            'email' => 'Email',
            'phone' => 'Phone',
            'nickname' => 'Nickname',
            'avatar' => 'Avatar',
            'money' => 'Money',
            'commission' => 'Commission',
            'point' => 'Point',
            'experience' => 'Experience',
            'pay_password' => 'Pay Password',
            'password_reset_token' => 'Password Reset Token',
            'token' => 'Token',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static $accessTokenEncryptKey = 'huogou_access_token_encrypt_key_';
    public static function findIdentityByAccessToken($accessToken, $type = null)
    {
        if (!static::isAccessTokenValid($type, $accessToken)) {
            return null;
        }
        $accessToken = base64_decode($accessToken);
        $accessToken = Yii::$app->security->decryptByKey($accessToken, static::$accessTokenEncryptKey);
        $parts = explode('_', $accessToken);
        return static::findOne(['token'=>$parts[0]]);
    }

    public static function isAccessTokenValid($type, $accessToken)
    {
        if (empty($accessToken)) {
            return false;
        }
        $accessToken = base64_decode($accessToken);
        if (!$accessToken) {
            return false;
        }
        $accessToken = Yii::$app->security->decryptByKey($accessToken, static::$accessTokenEncryptKey);
        if (!$accessToken) {
            return false;
        }
        if ($type) {
            $expire = 15552000;
        } else {
            $expire = Yii::$app->user->tokenExpire;
        }
        $parts = explode('_', $accessToken);
        $timestamp = (int)end($parts);
        return $timestamp + $expire >= time();
    }

    public function getAccessToken()
    {
        $accessToken = $this->token . '_' . time();
        $accessToken = Yii::$app->security->encryptByKey($accessToken, static::$accessTokenEncryptKey);
        return base64_encode($accessToken);
    }

    public function generateToken()
    {
        $this->token = static::createToken();
    }

    public static function createToken()
    {
        $token = microtime(true);
        $token = 'huogou_token_pre_key_' . (string) $token . mt_rand(10000000, 99999999);
        return md5($token);
    }

    public static function findByAccount($account)
    {
        $validator = new MobileValidator();
        $valid = $validator->validate($account);
        if ($valid && $user = static::findByPhone($account)) {
            return $user;
        }
        $validator = new EmailValidator();
        $valid = $validator->validate($account);
        if ($valid && $user = static::findByEmail($account)) {
            return $user;
        }
        return false;
    }

    /**
     * Finds user by phone
     *
     * @param string $phone
     * @return \app\models\User |null
     */
    public static function findByPhone($phone)
    {
        $from=Brower::whereFrom();
        return static::findOne(['phone' => $phone,'from'=>$from]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return \app\models\User |null
     */
    public static function findByEmail($email)
    {
        $from=Brower::whereFrom();
        return static::findOne(['email' => $email,'from'=>$from]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return \app\models\User|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
        ]);
    }

    /**
     * Generates home ID
     */
    public static function generateHomeId($userId)
    {
        $tableId = mt_rand(100, 109);
        return (string)$tableId . (string)$userId;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }


    public function getAuthKey()
    {
        return md5('user_auth_key_' . $this->id);
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 会员列表
     */
    public static function userList($limit = 10)
    {
        $query = User::find();
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>$limit ]);
        $list = $query->orderBy('id desc')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return ['list'=>$list, 'pagination'=>$pagination];
    }

    public static function userName($id)
    {
        $user = User::find()->where(['id' => $id])->asArray()->one();
        if ($user) {
            if ($user['nickname']) {
                $user['username'] = $user['nickname'];
            } elseif ($user['phone']) {
                $user['username'] = $user['phone'];
            } elseif ($user['email']) {
                $user['username'] = $user['email'];
            }
        }
        return $user;
    }

    //用户相关统计信息
    public static function userCount($home_id, $user_id)
    {
        $table_id = substr($home_id, 2, 1);
        $conn = \Yii::$app->db;
        $command = $conn->createCommand('SELECT buy_num FROM user_buylist_10'.$table_id.' WHERE user_id='.$user_id);
        $findAll = $command->queryAll();
        $num = 0;
        foreach($findAll as $val){
            $num += $val['buy_num'];
        }

        $command = $conn->createCommand('SELECT count(*) as orderNum FROM orders WHERE user_id='.$user_id);
        $order = $command->queryOne();

        $user['order'] = $order['orderNum'];
        $user['comsume'] = $num;
        $user['invite'] = Invite::find()->where(['user_id'=>$user_id])->count();
        return $user;
    }

    //是否是邀请用户
    public static function isInvite($id)
    {
        $result = Invite::findOne(['invite_uid'=>$id]);
        return $result['user_id'];
    }

    /**
     * 用户购买记录
     */
    public static function userBuyList($id, $where=[], $perpage = 10)
    {

    }

    public static function checkNickName($nickname, $userId)
    {
        $nickname = trim($nickname);
        if (empty($nickname)) {
            return ['code'=>102,'msg'=>'昵称不能为空'];
        }
        $len = mb_strlen($nickname,'utf-8');
        if ($len < 2 || $len > 20) {
            return ['code'=>103,'msg'=>'昵称必须在2个到20个字符之间'];
        }
        $pattern = "/^[\\x{4e00}-\\x{9fa5}A-Za-z0-9_]+$/iu";
        if (!preg_match($pattern, $nickname)) {
            return ['code'=>104,'msg'=>'昵称只能是中英文和_'];
        }
        $user = User::find()->where(['nickname' => $nickname])->andWhere(['<>', 'id', $userId])->one();
        if ($user) {
            return ['code'=>105,'msg'=>'昵称已存在'];
        }

        $keywords = Keyword::findAll(['type' => 2]);
        $keywords = ArrayHelper::getColumn($keywords, 'content');

        foreach ($keywords as $keyword) {
            if (strstr($nickname, $keyword) !== false) {
                return ['code' => 106, 'msg' => '昵称请不要设置非法词汇'];
            }
        }

        return ['code' => 100, 'msg' => '该昵称不存在'];
    }

}
