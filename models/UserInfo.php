<?php

namespace app\models;

use Yii;
use app\models\User;
use app\helpers\StringHelper;
use yii\caching\Cache;

/**
 * This is the model class for table "member_info".
 *
 * @property integer $id
 * @property string $face
 * @property integer $uid
 */
class UserInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'face' => '头像',
            'uid' => '用户ID',
        ];
    }
    
    /**
     * 更新个人头像
     * @param unknown $model
     * @param unknown $data
     */
    public static function updateFace($model,$data){
        if(!StringHelper::isEmpty($data)){
            $uid = Yii::$app->user->id;
            $face = User::findOne($uid);
            $face->avatar = $data;
            $face->save();
            
//             if($face->save()){
//                 $cache = Yii::$app->cache;                
//                 $cache->add('key'.Yii::$app->user->id,$data);
//             }

        }
        
       
  
    }
    
    /**
     * 获取个人头像 
     */
    public static function getFace($uid=''){
          $uuid = Yii::$app->user->id;
          if (empty($uid) && !empty($uuid)){             
              return User::find()->select('avatar')->where(['id'=>$uuid])->asArray()->one();
          }else{
              return User::find()->select('avatar')->where(['id'=>$uid])->asArray()->one();
              
          }
          
      }

    
    /**
     * 获取个人用户信息 
     */
    public static function getUser(){

        return UserInfo::find()->where(['id'=>Yii::$app->user->id])->asArray()->one();
    }

    public static function saveUserInfo($userId, $param)
    {
        $model = UserInfo::findOne(['id' => $userId]);

        if (!$model) {
            $model = new UserInfo();
            $model->id = $userId;
            $model->create = time();
            $model->ip = Yii::$app->request->userIP;
        }

        $param['spare_phone'] && $model->spare_phone = $param['spare_phone'];
        $param['income'] && $model->income = $param['income'];
        $param['address'] && $model->address = $param['address'];
        $param['home_address'] && $model->home_address = $param['home_address'];
        $param['sign'] && $model->sign = $param['sign'];
        $param['birthday'] && $model->birthday = $param['birthday'];
        $param['gender'] && $model->sex = $param['gender'];
        $param['signature'] && $model->signature = $param['signature'];
        $param['qq'] && $model->qq = $param['qq'];

        if (!$model->save()) {
            return false;
        }

        return true;
    }
}