<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/14
 * Time: 下午8:20
 */
namespace app\oauth;

use Yii;
use yii\authclient\OAuthToken;

class WechatOAuth extends BaseOAuth
{

    public $authUrl = 'https://open.weixin.qq.com/connect/qrconnect';
    public $tokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    public $refreshTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    public $apiBaseUrl = 'https://api.weixin.qq.com';

    public $scope = ['snsapi_login'];

    public function getId()
    {
        return 2;
    }

    protected function initUserAttributes()
    {
        $accessToken = $this->getAccessToken();
        $openId = $accessToken->getParam('openid');
        $user = $this->api('sns/userinfo', 'GET', ['openid'=>$openId]);
        return $user;
    }

    /**
     * Composes user authorization URL.
     * @param array $params additional auth GET params.
     * @return string authorization URL.
     */
    public function buildAuthUrl(array $params = [])
    {
        $defaultParams = [
            'appid' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->getReturnUrl(),
            'xoauth_displayname' => Yii::$app->name,
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }

        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }

    /**
     * Fetches access token from authorization code.
     * @param string $authCode authorization code, usually comes at $_GET['code'].
     * @param array $params additional request params.
     * @return OAuthToken access token.
     */
    public function fetchAccessToken($authCode, array $params = [])
    {
        $defaultParams = [
            'appid' => $this->clientId,
            'secret' => $this->clientSecret,
            'code' => $authCode,
            'grant_type' => 'authorization_code',
        ];
        $response = $this->sendRequest('POST', $this->tokenUrl, array_merge($defaultParams, $params));
        $token = $this->createToken(['params' => $response]);
        $this->setAccessToken($token);

        return $token;
    }

    /**
     * Gets new auth token to replace expired one.
     * @param OAuthToken $token expired auth token.
     * @return OAuthToken new auth token.
     */
    public function refreshAccessToken(OAuthToken $token)
    {
        $params = [
            'appid' => $this->clientId,
            'secret' => $this->clientSecret,
            'grant_type' => 'refresh_token'
        ];
        $refreshToken = $token->getParam('refresh_token');
        $params['refresh_token'] = $refreshToken;
        $response = $this->sendRequest('POST', $this->refreshTokenUrl, $params);

        $token = $this->createToken(['params' => $response]);
        $this->setAccessToken($token);

        return $token;
    }




}