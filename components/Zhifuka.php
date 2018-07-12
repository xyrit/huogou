<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/12/23
 * Time: 下午5:58
 */
namespace app\components;

use yii\base\Component;
use app\models\VirtualPurchaseOrder;
use app\models\Purchase;
use app\models\Order;

class Zhifuka extends Component
{
    public $customerid;
    public $key;
    public $cardCustomerId;
    public $cardMd5Key;
    public $beforeBuyCard;
    public $buyCard;
    public $onlinePayUrl;
    public $getProductId;
    public $province = [
                'HB' => '河北省',
                'SD' => '山东省',
                'LN' => '辽宁省',
                'HL' => '黑龙江省',
                'JL' => '吉林省',
                'GS' => '甘肃省',
                'QH' => '青海省',
                'HN' => '河南省',
                'JS' => '江苏省',
                'WH' => '湖北省',
                'CS' => '湖南省',
                'JX' => '江西省',
                'ZJ' => '浙江省',
                'GD' => '广东省',
                'YN' => '云南省',
                'FJ' => '福建省',
                'TW' => '台湾省',
                'HK' => '海南省',
                'SX' => '山西省',
                'SC' => '四川省',
                'XA' => '陕西省',
                'GZ' => '贵州省',
                'AH' => '安徽省',
                'CQ' => '重庆市',
                'BJ' => '北京市',
                'SH' => '上海市',
                'TJ' => '天津市',
                'GX' => '广西',
                'NM' => '内蒙古',
                'XZ' => '西藏',
                'XJ' => '新疆',
                'LX' => '宁夏',
                'AM' => '澳门',
                'XG' => '香港',
                'NX' => '宁夏'

            ];
    public $cardType = [
                'Y' => '移动',
                'L' => '联通',
                'D' => '电信'
            ];

    /** 获取app下单参数
     * @param $sdcustomno
     * @param $orderAmount
     * @param $noticeurl
     * @param $backurl
     * @param $mark
     * @param $remarks
     */
    public function getAppOrderParams($sdcustomno, $orderAmount, $noticeurl)
    {
        $params = [
            'orderNo' => $sdcustomno,
            'orderName' => '伙购网',
            'orderDetail' => '伙购网',
            'orderAmount' => $orderAmount,
            'notifyUrl' => $noticeurl,
            'channelType' => '13',
            'customerId' => $this->customerid,
            'key' => $this->key,
        ];
        return $params;
    }

    public function qrcode($sdcustomno, $orderAmount, $noticeurl, $backurl, $mark, $remarks)
    {
        $customerid = $this->customerid;//商户注册的时候，网关自动分配的商户ID
        $cardno = 32;//支付类型，为固定值 32
        $key = $this->key;//秘钥(key)
        $Md5str = 'customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . $key;
        $sign = strtoupper(md5($Md5str));
        $remarks = mb_convert_encoding($remarks, 'GB2312', 'UTF-8');
        $remarks = urlencode($remarks);
        $url = 'http://www.zhifuka.net/gateway/weixin/weixinpay.asp?remarks=' . $remarks . '&ZFType=2&customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . '&sign=' . $sign . '&mark=' . $mark;

        $cache = \Yii::$app->cache;
        $key = 'zhifuka_qrcode_' . md5($url);
        $data = $cache->get($key);
        if ($data) {
            return $data;
        }

        for($i=0;$i<3;$i++) {
            $con = file_get_contents($url);
            $con = mb_convert_encoding($con, 'UTF-8', 'GB2312');
            if (preg_match('/\"data:image(.*?)\"/', $con, $match)) {
                $data = 'data:image' . $match[1];
//            $data = str_replace("data:image/png;base64,","",$data);
//            $data = base64_decode($data);
                $cache->set($key, $data, 300);//多宝通那边订单保留5分钟有效期，所以这里也设置5分钟
                return $data;
            } elseif (preg_match('/<item\s+name="url"\s+value="(.*?)"\s+\/>/', $con, $match)) {
                $data = $match[1];
//            $data = file_get_contents($url);
                $cache->set($key, $data, 300);//多宝通那边订单保留5分钟有效期，所以这里也设置5分钟
                return $data;
            } else {
                continue;
            }
        }
        return false;
    }

    public function wapPay($sdcustomno, $orderAmount, $noticeurl, $backurl, $mark, $remarks)
    {
        $customerid = $this->customerid;//商户注册的时候，网关自动分配的商户ID
        $cardno = 32;//支付类型，为固定值 32
        $key = $this->key;//秘钥(key)
        $Md5str = 'customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . $key;
        $sign = strtoupper(md5($Md5str));
        $remarks = mb_convert_encoding($remarks, 'GB2312', 'UTF-8');
        $remarks = urlencode($remarks);
        $url = 'http://www.zhifuka.net/gateway/weixin/wap-weixinpay.asp?remarks=' . $remarks . '&customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . '&sign=' . $sign . '&mark=' . $mark;
        return $url;
    }

    public function qqQrcode($sdcustomno, $orderAmount, $noticeurl, $backurl, $mark, $remarks)
    {
        $customerid = $this->customerid;//商户注册的时候，网关自动分配的商户ID
        $cardno = 36;//支付类型，为固定值 32
        $key = $this->key;//秘钥(key)
        $Md5str = 'customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . $key;
        $sign = strtoupper(md5($Md5str));
        $remarks = mb_convert_encoding($remarks, 'GB2312', 'UTF-8');
        $remarks = urlencode($remarks);
        $url = 'http://www.zhifuka.net/gateway/QQpay/QQpay.asp?remarks=' . $remarks . '&ZFType=2&customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . '&sign=' . $sign . '&mark=' . $mark;

        $cache = \Yii::$app->cache;
        $key = 'zhifuka_qrcode_' . md5($url);
        $data = $cache->get($key);
        if ($data) {
            return $data;
        }

        for($i=0;$i<3;$i++) {
            $con = file_get_contents($url);
            $con = mb_convert_encoding($con, 'UTF-8', 'GB2312');
            if (preg_match('/\"data:image(.*?)\"/', $con, $match)) {
                $data = 'data:image' . $match[1];
//            $data = str_replace("data:image/png;base64,","",$data);
//            $data = base64_decode($data);
                $cache->set($key, $data, 300);//多宝通那边订单保留5分钟有效期，所以这里也设置5分钟
                return $data;
            } elseif (preg_match('/<item\s+name="url"\s+value="(.*?)"\s+\/>/', $con, $match)) {
                $data = $match[1];
//            $data = file_get_contents($url);
                $cache->set($key, $data, 300);//多宝通那边订单保留5分钟有效期，所以这里也设置5分钟
                return $data;
            } else {
                continue;
            }
        }
        return false;
    }

    public function notify($successCallback, $failCallback)
    {
        $request = \Yii::$app->request;
        $state = trim($request->get('state'));// 1:充值成功 2:充值失败
        $customerid = trim($request->get('customerid'));//商户注册的时候，网关自动分配的商户ID
        $sd51no = trim($request->get('sd51no'));//该订单在网关系统的订单号
        $sdcustomno = trim($request->get('sdcustomno'));//该订单在商户系统的流水号
        $ordermoney = trim($request->get('ordermoney'));//商户订单实际金额单位：（元）
        $ordermoney = $ordermoney * 100 / 100;
        $cardno = trim($request->get('cardno'));//支付类型，为固定值 32
        $mark = trim($request->get('mark'));//未启用暂时返回空值
        $sign = trim($request->get('sign'));//发送给商户的签名字符串
        $resign = trim($request->get('resign'));//发送给商户的签名字符串


        $key = $this->key;  //key可从星启天网关客服处获取
        $sign2 = strtoupper(md5("customerid=" . $customerid . "&sd51no=" . $sd51no . "&sdcustomno=" . $sdcustomno . "&mark=" . $mark . "&key=" . $key));
        if ($sign != $sign2) {
            return "签名不正确";
            //记录日志
        }

        //**************************************************************************
        //*第三步
        //*商户系统业务逻辑处理
        //**************************************************************************

        if ($state == "1") {
            //当充值成功后同步商户系统订单状态
            //此处编写商户系统处理订单成功流程
            //............
            //............
            //商户在接受到网关通知时，应该打印出<result>1</result>标签，以供接口程序抓取信息，以便于我们获取是否通知成功的信息，否则订单会显示没有通知商户

            echo "<result>1</result>";
            call_user_func($successCallback, $_GET);
            //记录订单处理日志
        } else if ($state == "2") {
            //当充值失败后同步商户系统订单状态
            //此处编写商户系统处理订单失败流程
            //............
            //............
            //商户在接受到网关通知时，应该打印出<result>1</result>标签，以供接口程序抓取信息，以便于我们获取是否通知成功的信息，否则订单会显示没有通知商户
            echo "<result>1</result>";
            call_user_func($failCallback, $_GET);
            //记录订单处理日志
        } else {
            //异常处理部分（可选）,根据自己系统而定
            echo  "<result>0</result>";   //当返回<result>0</result>时星启天网关系统会继续通知
            //记录订单处理日志
        }
    }


    /**
     * 购买卡密订单预查询
     * @param  [type] $product 卡类代码
     * @param  [type] $money   面值
     * @param  [type] $orderId 订单Id
     * @return [type]          [description]
     */
    public function beforeOrder($product,$parMoney,$orderId){
        $data = [
            'merchantid'=>$this->cardCustomerId,
            'productid' => $product,
            'parvalue' => $parMoney,
            'tranid' => $orderId
        ];
        $query = http_build_query($data);
        $sign = md5($query.'&merchantkey='.$this->cardMd5Key);
        $data['sign'] = strtoupper($sign);
        $query = http_build_query($data);

        $url = $this->beforeBuyCard.'?'.$query;
        
        $result = file_get_contents($url);
        $content = simplexml_load_string($result);
        if ($content->resultno != '0000') {
            return array('code'=>'0','msg'=>mb_convert_encoding(urldecode($content->retmsg),'UTF-8','GB2312'));
        }else{
            return array('code'=>100,'msg'=>'可以购买');
        }
    }

    /**
     * 购买充值卡卡密
     * @param  [type] $product [description]
     * @param  [type] $parMoney   [description]
     * @param  [type] $orderId [description]
     * @return [type]          [description]
     */
    public function buyCard($product,$parMoney,$nums,$orderId,$ip){
        $canBuy = $this->beforeOrder($product,$parMoney,$orderId);
        if ($canBuy['code'] == 100) {
            $data = [
                'merchantid'=>$this->cardCustomerId,
                'productid' => $product,
                'buynumber' => $nums,
                'parvalue' => $parMoney,
                'tranid' => $orderId
            ];
            $query = http_build_query($data);
            $sign = md5($query.'&merchantkey='.$this->cardMd5Key);
            $data['sign'] = strtoupper($sign);
            $data['remoteip'] = $ip;
            $query = http_build_query($data);

            $url = $this->buyCard.'?'.$query;
            // echo $url;
            
            if (DOMAIN == 'huogou.com' || DOMAIN == 'dddb.com') {
                $result = file_get_contents($url);
            }else{
                $result = '<?xml version="1.0" encoding="gb2312" ?><order><resultno>0000</resultno><retmsg>%B9%BA%C2%F2%B3%C9%B9%A6</retmsg><exchange_no>20151231174352105448259871</exchange_no><card_s>';
                for ($i=1;$i<=$nums;$i++) {
                    $card = rand(10000000000000000000000,9999999999999999999999999999999999999999999);
                    $result .= '<card><num>'.$card.'</num><pwd>'.$card.'</pwd></card>';
                }
                $result .= '</card_s></order>';
            }
            $content = simplexml_load_string($result);
            if ($content->resultno != '0000') {
                return array('code'=>'0','msg'=>mb_convert_encoding(urldecode($content->retmsg),'UTF-8','GB2312'));
            }else{
                $cardsInfo = array();
                $_cardList = (array)$content->card_s;
                $cardList = $_cardList['card'];

                if ($nums == 1) {
                    $cardsInfo[0]['card'] = (string)$cardList->num;
                    $cardsInfo[0]['pwd'] = (string)$cardList->pwd;
                }else if ($nums > 1) {
                    foreach ($cardList as $key => $value) {
                        $cardsInfo[$key]['card'] = (string)$value->num;
                        $cardsInfo[$key]['pwd'] = (string)$value->pwd;
                    }
                }
                
                $cards = [
                    'type' => 'mobile',
                    'orderid' => $orderId,
                    'exchange_id' => (string)$content->exchange_no,
                    'cards' => $cardsInfo,
                    'result' => $result,
                    'time' => time()
                ];
                return array('code'=>100,'msg'=>$cards);
            }
        }else{
            return $canBuy;
        }
    }

    public function getMobileCardProductId($mobile,$parMoney){
        $data = [
            'merchantid' => $this->cardCustomerId,
            'mbphone' => $mobile,
            'parvalue' => $parMoney,
        ];
        $query = http_build_query($data);
        $sign = md5($query.'&key='.$this->cardMd5Key);
        $data['sign'] = strtoupper($sign);
        $url = $this->getProductId.'?'.http_build_query($data);
        $result = file_get_contents($url);
        preg_match('/name=\"resultno\" value=\"(.*?)\"/',$result,$match);
        if ($match['1'] != '0000') {
            return array('code'=>'0','data'=>['msg'=>mb_convert_encoding(urldecode($match[1]),'UTF-8','GB2312'),'result'=>mb_convert_encoding($result,'UTF-8','GB2312')]);
        }else{
            preg_match('/name=\"productid\" value=\"(.*?)\"/',$result,$match);
            $productId = $match['1'];
            preg_match('/name=\"mianzhi\" value=\"(.*?)\"/',$result,$match);
            $parValue = $match['1'];
            preg_match('/name=\"price\" value=\"(.*?)\"/',$result,$match);
            $price = $match['1'];
            preg_match('/name=\"retmsg\" value=\"(.*?)\"/',$result,$match);
            $message = $match['1'];
            $province = $this->province[substr($productId,0,2)];
            $type = substr($productId,2,1);
            $back = [
                'productId' => $productId,
                'province' => $province,
                'type' => $this->cardType[$type],
                'parvalue' => $parValue,
                'price' => $price,
                'msg' => mb_convert_encoding($message,'UTF-8','GB2312'),
                'result' => mb_convert_encoding($result,'UTF-8','GB2312')
            ];
            return array('code'=>100,'data'=>$back);
        }
    }

    /**
     * 在线充值Q币，手机充值卡
     * @param  int $payTo    QQ号或者手机号
     * @param  string $product  商品id
     * @param  int $parMoney 面值，Q币，手机充值卡都为1
     * @param  int $nums     Q币为面值，手机充值卡为1
     * @param  int $orderId  订单号
     * @param  string $ip       [description]
     * @return array           [description]
     */
    public function onlinePay($payTo,$product,$parMoney,$nums,$orderId,$ip)
    {
        $data = [
            'merchantid'=>$this->cardCustomerId,
            'productid' => $product,
            'playerusername' => $payTo,
            'buynumber' => $nums,
            'tranid' => $orderId
        ];
        $query = http_build_query($data);
        $sign = md5($query.'&merchantkey='.$this->cardMd5Key);
        $data['sign'] = strtoupper($sign);
        $data['remoteip'] = $ip;
        $data['parvalue1'] = $parMoney;
        $query = http_build_query($data);

        $url = $this->onlinePayUrl.'?'.$query;
        if (DOMAIN == 'huogou.com' || DOMAIN == 'dddb.com') {
            $result = file_get_contents($url);
        }else{
            $result = '<?xml version="1.0" encoding="gb2312" ?><items><item name="resultno" value="0000" /><item name="retmsg" value="订单提交成功" /></items>';
        }

        // $result = mb_convert_encoding($result,'UTF-8','gb2312');
        preg_match('/name=\"resultno\" value=\"(.*?)\"/',$result,$match);
        if ($match[1] != '0000') {
            return array('code'=>'0','msg'=>mb_convert_encoding(urldecode($match[1]),'UTF-8','GB2312'));
        }else{
            $back = [
                'type' => 'online',
                'orderid' => $orderId,
                'result' => mb_convert_encoding(urldecode($result),'UTF-8','GB2312'),
                'time' => time()
            ];
            return array('code'=>100,'msg'=>$back);
        }
    } 
    /**
     * 在线充值回调
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function onlinePayBack($params)
    {
        $signSource = [
            'exchange_no' => $params['Exchange_no'],
            'tranid' => $params['tranid'],
            'state' => $params['state']
        ];
        $query = http_build_query($signSource);
        $sign = strtoupper(md5($query.'&key='.$this->cardMd5Key));
        if ($sign != $params['sign']) {
            return false;
        }
        $orderInfo = VirtualPurchaseOrder::findOne(['orderid'=>$params['tranid']]);
        if (!$orderInfo) {
            return false;
        }
        
        $orderInfo->status = $params['state'];

        $orderInfo->exchange_no = $params['Exchange_no'];
        $orderInfo->update_time = time();
        $orderInfo->save();
        $purchaseInfo = Purchase::find()->where(['id'=>$orderInfo['purchaseid']])->asArray()->one();
        $extra = json_decode($purchaseInfo['extra'],true);
        $model = Order::findOne(['id'=>$extra['winOrder']]);
        if ($params['state'] == 1) {
            $model->status = 8;
            $model->save();
        }else{
            $model->status = 0;
            $model->save();
        }
        return true;
    } 
}