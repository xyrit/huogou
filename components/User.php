<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/18
 * Time: 下午5:16
 */
namespace app\components;
use app\helpers\Brower;
use app\helpers\MyRedis;
use app\models\Image;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\base\NotSupportedException;
use yii\web\Cookie;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\UserEvent;

/**
 *
 * @property \app\models\User|\yii\web\IdentityInterface|null $identity The identity object associated with the currently logged-in
 * user. `null` is returned if the user is not logged in (not authenticated).
 * @property string $nickname
 * @property string $accessToken
 * @property string $avatar
 * Class User
 * @package app\components
 */
class User extends Component
{
    const EVENT_BEFORE_LOGIN = 'beforeLogin';
    const EVENT_AFTER_LOGIN = 'afterLogin';
    const EVENT_BEFORE_LOGOUT = 'beforeLogout';
    const EVENT_AFTER_LOGOUT = 'afterLogout';


    public $identityClass;

    public $loginUrl = ['site/login'];

    public $returnUrlGetParam = 'forward';

    public $autoRenewCookie = true;

    public $tokenName = '_utoken';

    public function init()
    {
        parent::init();

        if ($this->identityClass === null) {
            throw new InvalidConfigException('User::identityClass must be set.');
        }
        $this->on(static::EVENT_AFTER_LOGIN, [$this, 'doAfterLoginThing']);
        $this->on(static::EVENT_AFTER_LOGOUT, [$this, 'doAfterLogoutThing']);
    }

    private $_identity = false;
    public function getIdentity($autoRenew = true)
    {
        if ($this->_identity === false) {
            if ($autoRenew) {
                $this->_identity = null;
                $this->renewAuthStatus();
            } else {
                return null;
            }
        }

        return $this->_identity;
    }

    public function setIdentity($identity)
    {
        if ($identity instanceof IdentityInterface) {
            $this->_identity = $identity;
        } elseif ($identity === null) {
            $this->_identity = null;
        } else {
            throw new InvalidValueException('The identity object must implement IdentityInterface.');
        }
    }

    public function getNickname()
    {
        if ($this->identity) {
            if ($this->identity->nickname) {
                $nickname = $this->identity->nickname;
            }elseif ($this->identity->phone) {
                $nickname = $this->identity->phone;
            } elseif ($this->identity->email) {
                $nickname = $this->identity->email;
            }
            return $nickname;
        }
        return '';
    }

    public function getAvatar($width = 160)
    {
        return Image::getUserFaceUrl($this->identity->avatar, $width);
    }

    public function getAccessToken()
    {
        if ($this->identity) {
            $token = $this->identity->getAccessToken();
        } else {
            $cookies = \Yii::$app->request->cookies;
            if ($token = $cookies->getValue($this->tokenName)) {
                return $token;
            }
            $token = \app\models\User::createToken();
        }
        $cookies = \Yii::$app->response->cookies;
        $expire = time() + $this->tokenExpire;
        $cookies->add(new Cookie([
            'name' => $this->tokenName,
            'value' => $token,
            'domain' => '.' . DOMAIN,
            'expire' => $expire,
        ]));
        return $token;
    }

    public function getTokenExpire()
    {
        return 1800;
    }

    public function doAfterLoginThing()
    {
        $cookies = \Yii::$app->response->cookies;
        $account = '';
        if ($this->identity->phone) {
            $account = $this->identity->phone;
        } elseif ($this->identity->email) {
            $account = $this->identity->email;
        }
        $expire = time() + 3600*24;
        $cookies->add(new Cookie([
            'name' => '_uname',
            'value' => $account,
            'domain' => '.' . DOMAIN,
            'expire' => $expire,
        ]));

        //更新最后登录ip
        \app\models\User::updateAll(['last_login_ip' => ip2long(Yii::$app->request->userIP), 'updated_at' => time()], ['id' => Yii::$app->user->id]);
    }

    public function doAfterLogoutThing()
    {

    }

    public function getReturnUrl($defaultUrl = null)
    {
        $url = Yii::$app->getRequest()->get($this->returnUrlGetParam, $defaultUrl);
        if (is_array($url)) {
            if (isset($url[0])) {
                return Yii::$app->getUrlManager()->createUrl($url);
            } else {
                $url = null;
            }
        }

        return $url === null ? Yii::$app->getHomeUrl() : $url;
    }

    public function setReturnUrl($url)
    {
        throw new NotSupportedException('不支持的方法');
    }


    public function loginRequired($checkAjax = true)
    {
        $request = Yii::$app->getRequest();
        if (!$checkAjax || !$request->getIsAjax()) {
            $forword = $request->getAbsoluteUrl();
        } else {
            $forword = '';
        }
        if ($this->loginUrl !== null) {
            $loginUrl = (array) $this->loginUrl;
            if ($loginUrl[0] !== Yii::$app->requestedRoute) {
                $returnUrlGetParam = $this->returnUrlGetParam;
                $loginUrl = $forword ? array_merge($loginUrl, [$returnUrlGetParam=>$forword]) : $this->loginUrl;
                return Yii::$app->getResponse()->redirect($loginUrl);
            }
        }
        throw new ForbiddenHttpException(Yii::t('yii', 'Login Required'));
    }

    protected function renewAuthStatus()
    {
        $cookies = Yii::$app->request->cookies;
        $token = $cookies->getValue('_utoken');

        if ($token === null) {
            $identity = null;
        } else {
            /* @var $class IdentityInterface */
            $class = $this->identityClass;
            if (Brower::isMcroMessager()) {
                $identity = $class::findIdentityByAccessToken($token,1);
            } else {
                $identity = $class::findIdentityByAccessToken($token);
            }
            if ($identity && $this->autoRenewCookie) {
                $this->sendIdentityCookie($identity, $this->tokenExpire);
            }
        }

        $this->setIdentity($identity);

        if (!$identity) {
            $this->logout(true);
        }
    }

    public function login(IdentityInterface $identity, $duration = 0)
    {
        if ($this->beforeLogin($identity, false, $duration)) {
            $this->switchIdentity($identity, $duration);
            $this->afterLogin($identity, false, $duration);
        }

        return !$this->getIsGuest();
    }

    public function loginByAccessToken($token, $type = null)
    {
        /* @var $class IdentityInterface */
        $class = $this->identityClass;
        $identity = $class::findIdentityByAccessToken($token, $type);
        if ($identity && $this->login($identity, $this->tokenExpire)) {
            return $identity;
        } else {
            return null;
        }
    }

    public function logout($destroyCookie = true)
    {
        $identity = $this->getIdentity();
        if ($identity !== null && $this->beforeLogout($identity)) {
            $this->switchIdentity(null);
            if ($destroyCookie) {
                $cookies = Yii::$app->getResponse()->cookies;
                $cookies->add(new Cookie([
                    'name' => $this->tokenName,
                    'value' => '',
                    'domain' => '.' . DOMAIN,
                ]));
            }
            $this->afterLogout($identity);
        }

        return $this->getIsGuest();
    }

    public function switchIdentity($identity, $duration = 0)
    {
        $this->setIdentity($identity);

        if ($identity) {
            $this->sendIdentityCookie($identity, $duration);
        }
    }

    protected function sendIdentityCookie($identity, $duration)
    {
        $cookies = \Yii::$app->response->cookies;
        $expire = time() + $duration;
        $token = $identity->getAccessToken();
        $cookies->add(new Cookie([
            'name' => $this->tokenName,
            'value' => $token,
            'domain' => '.' . DOMAIN,
            'expire' => $expire
        ]));
    }


    public function getIsGuest()
    {
        return $this->getIdentity() === null;
    }


    public function getId()
    {
        $identity = $this->getIdentity();

        return $identity !== null ? $identity->getId() : null;
    }

    protected function beforeLogin($identity, $cookieBased, $duration)
    {
        $event = new UserEvent([
            'identity' => $identity,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]);
        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        return $event->isValid;
    }

    protected function afterLogin($identity, $cookieBased, $duration)
    {
        $this->trigger(self::EVENT_AFTER_LOGIN, new UserEvent([
            'identity' => $identity,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]));
    }

    protected function beforeLogout($identity)
    {
        $event = new UserEvent([
            'identity' => $identity,
        ]);
        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        return $event->isValid;
    }

    protected function afterLogout($identity)
    {
        $this->trigger(self::EVENT_AFTER_LOGOUT, new UserEvent([
            'identity' => $identity,
        ]));
    }

}