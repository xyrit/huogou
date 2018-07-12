<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/1/13
 * Time: 下午1:44
 */
namespace app\components;

use yii\base\Component;

/**
 * Class Getui
 * @property \IGtNotificationTemplate|\IGtLinkTemplate|\IGtNotyPopLoadTemplate|\IGtTransmissionTemplate $template
 * @package app\modules\admin\components
 */
class Getui extends Component
{
    public $appkey;                     //第三方 标识
    public $masterSecret;               //第三方 密钥
    public $host;
    public $appid;                      //应用id

    private $_template;
    private $_apn;

    public function init()
    {
        parent::init();
        require_once(dirname(__FILE__) . '/igetui/' . 'IGt.Push.php');
        require_once(dirname(__FILE__) . '/igetui/' . 'igetui/IGt.AppMessage.php');
        require_once(dirname(__FILE__) . '/igetui/' . 'igetui/IGt.APNPayload.php');
        require_once(dirname(__FILE__) . '/igetui/' . 'igetui/template/IGt.BaseTemplate.php');
        require_once(dirname(__FILE__) . '/igetui/' . 'IGt.Batch.php');
        require_once(dirname(__FILE__) . '/igetui/' . 'igetui/utils/AppConditions.php');
        require_once(dirname(__FILE__) . '/igetui/' . 'igetui/utils/ApnsUtils.php');
    }


    /**
     * @param $templateType
     * @param $templateInfo
     * @param array $apnInfo
     * @return $this
     */
    public function setTemplate($templateType, $templateInfo)
    {

        switch ($templateType) {
            case 'Notification':
                $template = $this->setNotificationTemplate($templateInfo);
                break;
            case 'Link':
                $template = $this->setLinkTemplate($templateInfo);
                break;
            case 'NotyPopLoad':
                $template = $this->setNotyPopLoadTemplate($templateInfo);
                break;
            case 'Transmission':
                $template = $this->setTransmissionTemplate($templateInfo);
                break;
        }
        $this->_template = $template;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * @return $this
     */
    public function setAPNPayloadSimple()
    {
        $apn = new \IGtAPNPayload();
        $alertmsg=new \SimpleAlertMsg();
        $alertmsg->alertMsg="";
        $apn->alertMsg=$alertmsg;
        $apn->badge = 1;
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $this->_apn = $apn;
        return $this;
    }

    /**
     * @param $apnInfo
     * @return $this
     */
    public function setAPNPayload($apnInfo)
    {
        $defaultApnInfo = [
            'body' => '',
            'actionLocKey' => '',
            'locKey' => '',
            'locArgs' => array(""),
            'launchImage' => '',
            'title' => '',
            'titleLocKey' => '',
            'titleLocArgs' => array(""),
            'badge' => 0,
            'sound' => "",
            'customMsg' => [],
            'contentAvailable' => '',
            'category' => "ACTIONABLE",
        ];
        $apnInfo = array_merge($defaultApnInfo, $apnInfo);

        //iOS推送需要设置的pushInfo字段
        //APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg = new \DictionaryAlertMsg();
        $alertmsg->body = $apnInfo['body'];
        $alertmsg->actionLocKey = $apnInfo['actionLocKey'];
        $alertmsg->locKey = $apnInfo['locKey'];
        $alertmsg->locArgs = $apnInfo['locArgs'];
        $alertmsg->launchImage = $apnInfo['launchImage'];
        //IOS8.2 支持
        $alertmsg->title = $apnInfo['title'];
        $alertmsg->titleLocKey = $apnInfo['titleLocKey'];
        $alertmsg->titleLocArgs = $apnInfo['titleLocArgs'];

        $apn->alertMsg = $alertmsg;
        $apn->badge = $apnInfo['badge'];
        $apn->sound = $apnInfo['sound'];
        if ($apnInfo['customMsg']) {
            $apnInfo['customMsg'] = (array)$apnInfo['customMsg'];
            foreach ($apnInfo['customMsg'] as $key => $val) {
                $apn->add_customMsg($key, $val);
            }
        }
        $apn->contentAvailable = $apnInfo['contentAvailable'];
        $apn->category = $apnInfo['category'];
        $this->_apn = $apn;
        return $this;
    }

    public function getApn()
    {
        return $this->_apn;
    }

    /**通知透传模板
     * @param $templateInfo
     * @param $apnInfo
     * @return \IGtNotificationTemplate
     */
    public function setNotificationTemplate($templateInfo)
    {
        $defaultTemplateInfo = [
            'transmissionType' => '',
            'transmissionContent' => '',
            'title' => '',
            'text' => '',
            'logo' => '',
            'logoURL' => '',
            'isRing' => true,
            'isVibrate' => true,
            'isClearable' => true,
            'begin' => '',
            'end' => '',
        ];
        $templateInfo = array_merge($defaultTemplateInfo, $templateInfo);
        $template = new \IGtNotificationTemplate();
        $template->set_appId($this->appid);//应用appid
        $template->set_appkey($this->appkey);//应用appkey
        $template->set_transmissionType($templateInfo['transmissionType']);//透传消息类型
        $template->set_transmissionContent($templateInfo['transmissionContent']);//透传内容
        $template->set_title($templateInfo['title']);//通知栏标题
        $template->set_text($templateInfo['text']);//通知栏内容
        $template->set_logo($templateInfo['logo']);//通知栏logo
        $template->set_logoURL($templateInfo['logoURL']);//通知栏logoURL
        $template->set_isRing($templateInfo['isRing']);//是否响铃
        $template->set_isVibrate($templateInfo['isVibrate']);//是否震动
        $template->set_isClearable($templateInfo['isClearable']);//通知栏是否可清除

        if ($templateInfo['begin'] && $templateInfo['end'] && $templateInfo['end'] >= $templateInfo['begin']) {
            $template->set_duration($templateInfo['begin'], $templateInfo['end']); //设置ANDROID客户端在此时间区间内展示消息
        }
        return $template;
    }

    /**通知链接模板
     * @param $templateInfo
     * @param $apnInfo
     * @return \IGtLinkTemplate
     */
    public function setLinkTemplate($templateInfo)
    {
        $defaultTemplateInfo = [
            'title' => '',
            'text' => '',
            'logo' => '',
            'logoURL' => '',
            'isRing' => true,
            'isVibrate' => true,
            'isClearable' => true,
            'url' => '',
            'begin' => '',
            'end' => '',
        ];
        $templateInfo = array_merge($defaultTemplateInfo, $templateInfo);
        $template = new \IGtLinkTemplate();
        $template->set_appId($this->appid);//应用appid
        $template->set_appkey($this->appkey);//应用appkey
        $template->set_title($templateInfo['title']);//通知栏标题
        $template->set_text($templateInfo['text']);//通知栏内容
        $template->set_logo($templateInfo['logo']);//通知栏logo
        $template->set_logoURL($templateInfo['logoURL']);//通知栏logoURL
        $template->set_isRing($templateInfo['isRing']);//是否响铃
        $template->set_isVibrate($templateInfo['isVibrate']);//是否震动
        $template->set_isClearable($templateInfo['isClearable']);//通知栏是否可清除
        $template->set_url($templateInfo['url']);//打开连接地址
        if ($templateInfo['begin'] && $templateInfo['end'] && $templateInfo['end'] >= $templateInfo['begin']) {
            $template->set_duration($templateInfo['begin'], $templateInfo['end']); //设置ANDROID客户端在此时间区间内展示消息
        }
        return $template;
    }

    /**通知弹框下载模板
     * @param $templateInfo
     * @param $apnInfo
     * @return \IGtNotyPopLoadTemplate
     */
    public function setNotyPopLoadTemplate($templateInfo)
    {
        $defaultTemplateInfo = [
            'notyTitle' => '',//通知栏标题
            'notyContent' => '',//通知栏内容
            'notyIcon' => '',//通知栏logo
            'isBelled' => '',//是否响铃
            'isVibrationed' => '',//是否震动
            'isCleared' => '',//通知栏是否可清除
            'popTitle' => '',//弹框标题
            'popContent' => '',//弹框内容
            'popImage' => '',//弹框图片
            'popButton1' => '',//左键
            'popButton2' => '',//右键
            'loadIcon' => '',//下载图片
            'loadTitle' => '',//下载标题
            'loadUrl' => '',//下载链接
            'isAutoInstall' => false,
            'isActived' => true,

            'begin' => '',//设置ANDROID客户端展示消息开始时间
            'end' => '',//设置ANDROID客户端展示消息结束时间
        ];
        $templateInfo = array_merge($defaultTemplateInfo, $templateInfo);
        $template = new \IGtNotyPopLoadTemplate();

        $template->set_appId($this->appid);//应用appid
        $template->set_appkey($this->appkey);//应用appkey
        //通知栏
        $template->set_notyTitle($templateInfo['notyTitle']);//通知栏标题
        $template->set_notyContent($templateInfo['notyContent']);//通知栏内容
        $template->set_notyIcon($templateInfo['notyIcon']);//通知栏logo
        $template->set_isBelled($templateInfo['isBelled']);//是否响铃
        $template->set_isVibrationed($templateInfo['isVibrationed']);//是否震动
        $template->set_isCleared($templateInfo['isCleared']);//通知栏是否可清除
        //弹框
        $template->set_popTitle($templateInfo['popTitle']);//弹框标题
        $template->set_popContent($templateInfo['popContent']);//弹框内容
        $template->set_popImage($templateInfo['popImage']);//弹框图片
        $template->set_popButton1($templateInfo['popButton1']);//左键
        $template->set_popButton2($templateInfo['popButton2']);//右键
        //下载
        $template->set_loadIcon($templateInfo['loadIcon']);//下载图片
        $template->set_loadTitle($templateInfo['loadTitle']);//下载标题
        $template->set_loadUrl($templateInfo['loadUrl']);//下载链接
        $template->set_isAutoInstall($templateInfo['isAutoInstall']);
        $template->set_isActived($templateInfo['isActived']);
        if ($templateInfo['begin'] && $templateInfo['end'] && $templateInfo['end'] >= $templateInfo['begin']) {
            $template->set_duration($templateInfo['begin'], $templateInfo['end']); //设置ANDROID客户端在此时间区间内展示消息
        }
        return $template;
    }

    /**透传模板
     * @param $templateInfo
     * @return \IGtTransmissionTemplate
     */
    public function setTransmissionTemplate($templateInfo)
    {
        $defaultTemplateInfo = [
            'transmissionType' => '',//透传消息类型
            'transmissionContent' => '',//透传内容
            'begin' => '',//设置ANDROID客户端展示消息开始时间
            'end' => '',//设置ANDROID客户端展示消息结束时间
        ];
        $templateInfo = array_merge($defaultTemplateInfo, $templateInfo);
        $template = new \IGtTransmissionTemplate();
        $template->set_appId($this->appid);//应用appid
        $template->set_appkey($this->appkey);//应用appkey
        $template->set_transmissionType($templateInfo['transmissionType']);//透传消息类型
        $template->set_transmissionContent($templateInfo['transmissionContent']);//透传内容
        if ($templateInfo['begin'] && $templateInfo['end'] && $templateInfo['end'] >= $templateInfo['begin']) {
            $template->set_duration($templateInfo['begin'], $templateInfo['end']); //设置ANDROID客户端在此时间区间内展示消息
        }
        return $template;
    }

    /** 指定用户推送消息
     * @param $clientId
     * @param bool|true $isOffline
     * @param int $offlineExpireTime
     * @param int $pushNetWorkType
     * @return Array
     */
    public function pushOne($clientId, $isOffline = true, $offlineExpireTime = 43200000, $pushNetWorkType = 0)
    {
        $this->template->set_apnInfo($this->apn);
        $igt = new \IGeTui(NULL, $this->appkey, $this->masterSecret, false);

        //个推信息体
        $message = new \IGtSingleMessage();
        $message->set_isOffline($isOffline);//是否离线
        $message->set_offlineExpireTime($offlineExpireTime);//离线时间
        $message->set_data($this->template);//设置推送消息类型
        $message->set_PushNetWorkType($pushNetWorkType);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId($this->appid);
        $target->set_clientId($clientId);
        //$target->set_alias(Alias);


        try {
            $rep = $igt->pushMessageToSingle($message, $target);
            return $rep;
        } catch (\RequestException $e) {
            $requstId = $e->getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target, $requstId);
            return $rep;
        }
    }

    /**指定用户批量推送消息
     * @param $clientId
     * @param bool|true $isOffline
     * @param int $offlineExpireTime
     * @param int $pushNetWorkType
     * @return mixed
     */
    public function pushOneBatch($clientIds, $isOffline = true, $offlineExpireTime = 43200000, $pushNetWorkType = 0)
    {
        $this->template->set_apnInfo($this->apn);
        putenv("gexin_pushSingleBatch_needAsync=false");

        $igt = new \IGeTui($this->host, $this->appkey, $this->masterSecret);
        $batch = new \IGtBatch($this->appkey, $igt);
        $batch->setApiUrl($this->host);
        //$igt->connect();

        //个推信息体
        $message = new \IGtSingleMessage();
        $message->set_isOffline($isOffline);//是否离线
        $message->set_offlineExpireTime($offlineExpireTime);//离线时间
        $message->set_data($this->template);//设置推送消息类型
        $message->set_PushNetWorkType($pushNetWorkType);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        foreach($clientIds as $clientId) {
            //接收方
            $target = new \IGtTarget();
            $target->set_appId($this->appid);
            $target->set_clientId($clientId);
//          $target->set_alias(Alias);
            $batch->add($message, $target);
        }
        try {
            $rep = $batch->submit();
            return $rep;
        } catch (\Exception $e) {
            $rep = $batch->retry();
            return $rep;
        }
    }

    /** 批量推送信息
     * @param $clientIds
     * @param bool|true $isOffline
     * @param int $offlineExpireTime
     * @param int $pushNetWorkType
     * @return Array
     */
    public function pushList($clientIds, $isOffline = true, $offlineExpireTime = 43200000, $pushNetWorkType = 0)
    {
        $this->template->set_apnInfo($this->apn);
        putenv("gexin_pushList_needDetails=true");
        putenv("gexin_pushList_needAsync=true");

        $igt = new \IGeTui($this->host, $this->appkey, $this->masterSecret);
        //个推信息体
        $message = new \IGtListMessage();
        $message->set_isOffline($isOffline);//是否离线
        $message->set_offlineExpireTime($offlineExpireTime);//离线时间
        $message->set_data($this->template);//设置推送消息类型
        $message->set_PushNetWorkType($pushNetWorkType);	//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
//    $contentId = $igt->getContentId($message);
        $contentId = $igt->getContentId($message);	//根据TaskId设置组名，支持下划线，中文，英文，数字

        foreach($clientIds as $clientId) {
            //接收方
            $target = new \IGtTarget();
            $target->set_appId($this->appid);
            $target->set_clientId($clientId);
//          $target->set_alias(Alias);
            $targetList[] = $target;
        }

        $rep = $igt->pushMessageToList($contentId, $targetList);
        return $rep;
    }

    /** 推送信息到某个APP所有用户
     * @param bool|true $isOffline
     * @param int $offlineExpireTime
     * @param int $pushNetWorkType
     * @return mixed|null
     */
    public function pushApp($phoneTypeList = [], $isOffline = true, $offlineExpireTime = 43200000, $pushNetWorkType = 0)
    {
        $this->template->set_apnInfo($this->apn);
        $igt = new \IGeTui($this->host, $this->appkey, $this->masterSecret);
        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline($isOffline);
        $message->set_offlineExpireTime($offlineExpireTime);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($this->template);
        $message->set_PushNetWorkType($pushNetWorkType);	//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        if ($phoneTypeList) {
            foreach($phoneTypeList as &$os) {
                $os = strtoupper($os);
            }
        }
        $message->set_phoneTypeList($phoneTypeList);
        $appIdList=array($this->appid);
//        $phoneTypeList=array('ANDROID');
//        $provinceList=array('浙江');
//        $tagList=array('haha');
//        //用户属性
//        $age = array("0000", "0010");
//        $cdt = new AppConditions();
//        $cdt->addCondition(AppConditions::PHONE_TYPE, $phoneTypeList);
//        $cdt->addCondition(AppConditions::REGION, $provinceList);
//        $cdt->addCondition(AppConditions::TAG, $tagList);
//        $cdt->addCondition("age", $age);

        $message->set_appIdList($appIdList);
        //$message->set_conditions($cdt->getCondition());

        $rep = $igt->pushMessageToApp($message);
        return $rep;
    }


}