<?php

namespace app\modules\api\controllers;

use app\helpers\Brower;
use app\models\ActivityProducts;
use app\models\PkShareList;
use app\models\Product;
use app\models\User;
use app\models\WinShare;
use app\services\JSSDK;
use app\services\Coupon;
use Yii;


/**
 * 中奖分享
 */
class SharingController extends BaseController
{

    public function actionWinshare()
    {
        $id = Yii::$app->request->get('id');
        //查询分享数据
        if($id){
            $shareinfo = WinShare::findOne($id);
            if($shareinfo) {

            $user=User::findOne($shareinfo->user_id);
            $data['headimg']=$user->avatar;
            $data['nickname']=$user->nickname?$user->nickname:$user->phone;
            $share=json_decode($shareinfo->share,1);
            $data['share']=$share;
            $data['money']=0;
             $data['code']=200;
            foreach ($share as $row)
            {
                $data['money']+=$row['user_buy_num'];
            }
            return $data;
        }
        }
            return ['code'=>201,'msg'=>'参数错误'];

    }

    //中奖分享领取红包
    public function actionWinsharered(){
        $packetid=41;
        $id = Yii::$app->request->get('id');
        $user_id=$this->userId;
        if($id && $user_id){
            $winshare=WinShare::find()->where(['user_id'=>$user_id,'id'=>$id,'red_id'=>0])->one();
            if($winshare){
                $ordercount=count(json_decode($winshare->share,1));
                $trans = \Yii::$app->db->beginTransaction();
                try{
                    for($i=0;$i<$ordercount;$i++) {
                        $winshare->red_id = $packetid;
                        $rs1 = $winshare->save();
                        $source = '中奖分享领取红包';
                        $rs2 = Coupon::receivePacket($packetid, $user_id, $source);
                        $pid = $rs2['data']['pid'];
                        $info = Coupon::openPacket($pid, $user_id);
                        if(!$rs1 || !$rs2 || $info['code']!=0){
                            $trans->rollBack();
                            return ['code' => 201, 'message' => '领取失败'];

                        }
                    }
                        $trans->commit();
                        return ['code' => 200, 'message' => '领取成功','money_red'=>$ordercount];
                }catch (\Exception $e) {
                    $trans->rollBack();
                    return ['code' => 201, 'message' => '网络错误','msg'=>$e];
                }

            }
        }
               return ['code' => 201, 'message' => '参数错误或红包已领取'];
    }

    public function actionWechatshare(){
        $host = urldecode(Yii::$app->request->get('host'));
        if(Brower::whereFrom()==1) {
            //伙购
            $config = require (Yii::getAlias('@app/config/wechat.php'));
        }else{
            //滴滴
            $config = require (Yii::getAlias('@app/config/didi_wechat.php'));
        }
        //获取配置

        $wechat = Yii::createObject($config);
        $jssdk = new JSSDK($wechat->appId, $wechat->appSecret,$host);
        $signPackage = $jssdk->getSign();
        return $signPackage;
    }

    // pk分享
    public function actionPkshare(){
        if(!$this->userId){
            return ['code' => 201, 'message' =>'用户未登录'];
        }
        $shareid=Yii::$app->request->get('share');

        if($shareid){
            $pksharelist=PkShareList::findOne($shareid);
            if( $pksharelist->status==1){
                return ['code' => 200, 'message' => '记录已存在','id'=>$pksharelist->id];
            }else{
                $pksharelist->status=1;
            }
        }else{
       $product_id= Yii::$app->request->get('pid');
        $size=Yii::$app->request->get('size',2);
            if(!$product_id || !$size){
                return ['code' => 201, 'message' => '保存失败,参数错误'];
            }
         $pkshare=PkShareList::find()->where(['size'=>$size,'product_id'=>$product_id,'user_id'=>$this->userId])->one();
            if($pkshare){
                return ['code' => 200, 'message' => '记录已存在','id'=>$pkshare->id];
            }
        $Product=ActivityProducts::findOne($product_id);
            if(!$Product) {
                return ['code' => 201, 'message' => '保存失败,商品不存在'];
            }
       $pksharelist=new PkShareList();
        $pksharelist->product_id=$product_id;
        $pksharelist->product_img=$Product['picture'];
        $pksharelist->product_name=$Product['name'];
        $pksharelist->product_price=$Product['price'];
        $pksharelist->size=$size;
        $pksharelist->headimg=$this->userInfo->avatar;
        $pksharelist->user_id=$this->userId;
        $pksharelist->nickname=$this->userInfo->nickname?$this->userInfo->nickname:$this->userInfo->phone;

        }
        if($pksharelist->save()){
            return ['code' => 200, 'message' => '保存成功','id'=>$pksharelist->primaryKey];
        }else{
            return ['code' => 201, 'message' => '保存失败'];
        }

    }

    /*
     * pk落地页
     */
    public function actionPkfloor(){
        $id=Yii::$app->request->get('id');
        if($id){
            $info=PkShareList::findOne($id)->toArray();

            if($info){
                $info['code']=200;
                return $info;
            }
        }
        return ['code'=>201,'数据不存在'];

    }


}