<?php
//上传文件
namespace app\services;
use yii;
use app\models\UserInfo as Info;
use yii\helpers\Json;

use yii\web\Cookie;
use yii\web\Response;
use yii\caching\Cache;
class UserInfo
{
    

    /**
     * 个人用户信息
     */
    public static function getUser(){
       $users = Info::getUser();
       if(!empty($users['nickname'])){
           $users['user'] = $users['nickname'];
       }elseif (!empty($users['phone']) && empty($users['email'])){
           $users['user'] = $users['phone'];
       }elseif (empty($users['phone']) && !empty($users['email'])){
           $users['user'] = $users['email'];
       }
       if ($users['avatar']) $users['avatar'] = json_decode($users['avatar'])->face;
       
       
       return $users;
    }
    
    
    /**
     * 获取个人头像
     * A=>300x300像素; B=>160X160 像素;C=>80X80 像素;D=>30X30像素
     */
    public static function getFacePath($uid='',$width='',$type=''){
        
        $skinUrl = 'http://skin.' . DOMAIN;
        $face = Info::getFace($uid)['avatar'];
       if(!empty($type)){
           if(!empty($face)){
               switch ($width){
                   case '80' :
                       return json_decode($face)->face->C;
                       break;
           
                   case '30' :
                       return  json_decode($face)->face->D;
                       break;
           
                   case '300' :
                       return  json_decode($face)->face->A;
                       break;
           
                   default :
                       return  json_decode($face)->face->B;
                       break;
               }
           }else{
               return './frontend/images/big.jpg';
           }
       }else{
           if(!empty($face)){
               switch ($width){
                   case 80 :
                       return $skinUrl.json_decode($face)->face->C;
                       break;
           
                   case 30 :
                       return  $skinUrl.json_decode($face)->face->D;
                       break;
           
                   case 300 :
                       return  $skinUrl.json_decode($face)->face->A;
                       break;
           
                   default :
                       return  $skinUrl.json_decode($face)->face->B;
                       break;
               }
           }else{
               return $skinUrl.'/frontend/images/big.jpg';
           }
       }
        
        

    }
    
    
    
    
    
}

