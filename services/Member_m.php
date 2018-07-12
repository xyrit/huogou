<?php
//会员中心主页
namespace app\services;

use app\helpers\DateFormat;
use app\models\GroupTopicComment;
use app\models\InviteLink;
use app\models\User as User_m;
use app\models\GroupTopic;
use app\models\GroupUser;
use app\models\Group;
use app\services\User;
use app\models\Order;
use app\models\Friend;
use yii;
use app\models\Image;

class Member_m
{
    
    /**
     * 邀请专属链接
     */
    public static function getLink(){
        return 'http://t.'.DOMAIN.'/'.(new InviteLink)->addLink(\Yii::$app->user->id);
    }
    
    //热门话题所属组
    public static function getGroup(){
       return  Group::getGroup();
    }
    
    //公告栏
    public static function getNotic(){
        return GroupTopic::find()->orderBy(['created_at'=>'DESC'])->limit(6)->asArray()->all();
    }
    
    //可能感兴趣的人
    public static function getUsers(){
        $users = User_m::find()->orderBy('created_at DESC')->limit(5)->asArray()->all();
         return  self::formatUser($users);
    }
    
    //用户信息(type=0[格式化] 1[数据库字段]])
    public static function getOneself($uid,$type=0){
         $users = User_m::find()->where(['id'=>$uid])->asArray()->all();
         return  self::formatUser($users,$type);
    }
    
    //格式化用户名
    public static function formatUser($users,$type=0){
        if(empty($type)){
            foreach ($users as $key=>$value){
                if(!empty($value['nickname'])){
                    $users[$key]['user'] = $value['nickname'];
                }elseif(!empty($value['phone']) && empty($value['email'])){
                    $str = preg_replace('/^([0-9]{5}).*([0-9]{2})$/', '$1****$2', $value['phone']);
                    $users[$key]['user'] = $str;
            
                }elseif(empty($value['phone']) && !empty($value['email'])) {
                    $str = preg_replace('/^(\w{2}).*(@.+)$/', '$1****$2', $value['email']);
                    $users[$key]['user'] = $str;
                }else {
                    $users[$key]['user'] = $value['phone'];
                }
            }
        }elseif($type == 1){
            foreach ($users as $key=>$value){
                if(!empty($value['nickname'])){
                    $users[$key]['user'] = $value['nickname'];
                }elseif(!empty($value['phone']) && empty($value['email'])){
                    $users[$key]['user'] = $value['phone'];
            
                }elseif(empty($value['phone']) && !empty($value['email'])) {
                    $users[$key]['user'] = $value['email'];
                }else {
                    $users[$key]['user'] = $value['phone'];
                }
            }
        } 
       
        return $users;
    }
    
    
    
    //好友产品列表信息
    public static function getFriendBuyList($uid){
        return User::buyList(10005,1);  
    }
    
    //用户所属圈子列表 
    public static function getGroupList($uid){
      $group_usersid = GroupUser::find()->select('group_id')->where(['user_id'=>$uid])->asArray()->all();
      $ids = [];
      foreach ($group_usersid as $key=>$value){
          $ids[] = $value['group_id'];
      }
      
      return $ids;

    }

    //前台用户中奖列表
    public static function userOrderList($uid, $limit = 2)
    {
        $list = Order::find()->leftJoin('periods p', 'orders.period_id=p.id')->where(['orders.user_id'=>$uid, 'orders.status'=>0])->andWhere(['<=', 'p.result_time', time()])->orderBy('orders.create_time desc')->limit($limit)->all();
        $arr = [];
        foreach($list as $key => $val){
            $productInfo = Product::info($val['product_id']);
            $arr[$key]['product_id'] = $val['product_id'];
            $arr[$key]['product_name'] = $productInfo['name'];
            $arr[$key]['id'] = $val['id'];
            $arr[$key]['period_id'] = $val['period_id'];
            $arr[$key]['delivery_id'] = $productInfo['delivery_id'];
        }

        return $arr;
    }
    
    //好友伙购记录
    public static function friendBuyList($limit = 2, $tableNum = 10)
    {
        $uid = Yii::$app->user->id;
        $allFriend = Friend::find()->where(['user_id'=>$uid])->all();
        if(!$allFriend) return [];

        $arr = [];
        foreach($allFriend as $key => $val){
            $arr[$key] = $val['friend_userid'];
        }

        $str = implode(',',$arr);
        $str = '('. $str .')';

        $sql = '';
        for( $i=0;$i<$tableNum;$i++)
        {
            $tableId = '10'.$i;
            if($i == 9){
                $sql .= '(SELECT * FROM period_buylist_'.$tableId.'  WHERE user_id in '.$str.' order by buy_time desc limit '.$limit.' ) ';
            }else{
                $sql .= '(SELECT * FROM period_buylist_'.$tableId.' WHERE user_id in '.$str.' order by buy_time desc limit '.$limit.' ) union all ';
            }
        }

        $connection = \Yii::$app->db;
        $querySql = 'SELECT * FROM ('.$sql.') as t order by t.buy_time desc limit '.$limit;
        $command = $connection->createCommand($querySql);
        $result = $command->queryAll();

        $returnArr = [];
        foreach($result as  $one){
            $productId = $one['product_id'];
            $periodId = $one['period_id'];
            $buyNum = $one['buy_num'];
            $curPeriodInfo = Product::curPeriodInfo($productId);
            $info = [];
            if (isset($curPeriodInfo['id']) && $curPeriodInfo['id']!=$periodId) {
                $info = Period::info($periodId);
                if (!$info) {
                    continue;
                }
            } else {
                $productInfo = Product::info($productId);
                if (!$productInfo) {
                    continue;
                }
                $info['goods_picture'] = $productInfo['picture'];
                $info['goods_name'] = $productInfo['name'];
                $info['period_number'] = $curPeriodInfo['period_number'];
                $info['status'] = 0;
                $info['code_sales'] = $curPeriodInfo['sales_num'];
                $info['progress'] = $curPeriodInfo['progress'];
                $info['left_num'] = $curPeriodInfo['left_num'];
                $info['code_quantity'] = $curPeriodInfo['price'];
                $info['code_price'] = sprintf('%.2f', $curPeriodInfo['price']);
            }
            $info['user_buy_num'] = $buyNum;
            $info['product_id'] = $productId;
            $info['period_id'] = $periodId;
            $friend = User::baseInfo($one['user_id']);
            $info['friend_name'] = $friend['username'];
            $info['user_buy_time'] = DateFormat::formatTime($one['buy_time']);
            $info['user_avatar'] = $friend['avatar'];
            $info['user_home_id'] = $friend['home_id'];
            $returnArr[] = $info;
        }

        return $returnArr;
    }
    
    
}

