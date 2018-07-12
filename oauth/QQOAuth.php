<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/14
 * Time: 下午7:27
 */
namespace app\oauth;

use yii\base\Exception;
use yii\helpers\Json;
use Yii;

/**
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'qq' => [
 *                 'class' => 'app\oauth\QqOAuth',
 *                 'clientId' => 'qq_client_id',
 *                 'clientSecret' => 'qq_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://connect.qq.com/
 *
 * @author easypao <admin@easypao.com>
 * @since 2.0
 */
class QQOAuth extends BaseOAuth
{
    const CONTENT_TYPE_QQ_CUSTOM = 'qq';

    public $authUrl = 'https://graph.qq.com/oauth2.0/authorize';
    public $tokenUrl = 'https://graph.qq.com/oauth2.0/token';
    public $apiBaseUrl = 'https://graph.qq.com';

    public $scope = ['get_user_info'];

    public function getId()
    {
        return 1;
    }

    protected function initUserAttributes()
    {
        $openId = $this->getOpenId();
        $user = $this->api("user/get_user_info", 'GET', ['oauth_consumer_key' => $this->clientId, 'openid' => $openId]);
        $user['openid'] = $openId;
        return $user;
    }

    protected function getOpenId()
    {
        $openIdInfo =  $this->api('oauth2.0/me', 'GET');
        return $openIdInfo['openid'];
    }

    protected function processResponse($rawResponse, $contentType = self::CONTENT_TYPE_AUTO)
    {
        if (empty($rawResponse)) {
            return [];
        }
        switch ($contentType) {
            case self::CONTENT_TYPE_AUTO: {
                $contentType = $this->determineContentTypeByRaw($rawResponse);
                if ($contentType == self::CONTENT_TYPE_AUTO) {
                    throw new Exception('Unable to determine response content type automatically.');
                }
                $response = $this->processResponse($rawResponse, $contentType);
                break;
            }
            case self::CONTENT_TYPE_QQ_CUSTOM: {
                $lpos = strpos($rawResponse, "(");
                $rpos = strrpos($rawResponse, ")");
                $rawResponse = substr($rawResponse, $lpos + 1, $rpos - $lpos - 1);
                $response = $this->processResponse($rawResponse, self::CONTENT_TYPE_JSON);
                break;
            }
            case self::CONTENT_TYPE_JSON: {
                $response = Json::decode($rawResponse, true);
                break;
            }
            case self::CONTENT_TYPE_URLENCODED: {
                $response = [];
                parse_str($rawResponse, $response);
                break;
            }
            case self::CONTENT_TYPE_XML: {
                $response = $this->convertXmlToArray($rawResponse);
                break;
            }
            default: {
                throw new Exception('Unknown response type "' . $contentType . '".');
            }
        }
        return $response;
    }

    /**
     * Attempts to determine the content type from raw content.
     * @param string $rawContent raw response content.
     * @return string response type.
     */
    protected function determineContentTypeByRaw($rawContent)
    {
        if (preg_match('/^\\{.*\\}$/is', $rawContent)) {
            return self::CONTENT_TYPE_JSON;
        }
        if (preg_match('/^[^=|^&]+=[^=|^&]+(&[^=|^&]+=[^=|^&]+)*$/is', $rawContent)) {
            return self::CONTENT_TYPE_URLENCODED;
        }
        if (preg_match('/^<.*>$/is', $rawContent)) {
            return self::CONTENT_TYPE_XML;
        }
        if (strpos($rawContent, "callback") !== false) {
            return self::CONTENT_TYPE_QQ_CUSTOM;
        }
        return self::CONTENT_TYPE_AUTO;
    }


}