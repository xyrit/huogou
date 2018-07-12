<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "act_qualification".
 *
 * @property string $id
 * @property string $user_id
 * @property string $num
 * @property string $created_at
 */
class ActQualification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_qualification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'num', 'created_at'], 'required'],
            [['user_id', 'num', 'created_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'num' => 'Num',
            'created_at' => 'Created At',
        ];
    }

    /** 根据充值金额加次数
     * @param $uid
     * @param $money
     */
    public static function addNumByRecharge($uid, $money)
    {
        try {
            $num = floor($money / 100 );
            if($num > 0){
                self::addNum($uid, 1, $num);
            }
        } catch(\Exception $e) {

        }

    }

    /**
     * type 1充值，2分享
     */
    public static function addNum($uid, $type, $num)
    {
        $time = Lottery::find()->where(['status'=>1])->andWhere('start_time <= '.time().' and end_time >= '.time())->orderBy('id desc')->one();
        if(!$time['id']) return ['code'=>299, 'msg'=>'活动未开启'];

        $user = \app\models\User::findOne($uid);
        if (!$user['id']) {
            return ['code' => 201, 'msg' => '未登录'];
        }
        if(!$user['phone']) return ['code'=>206, 'msg'=>'请先认证手机'];

        if(!($type == 2 || $type == 1)){
            return ['code'=>206, 'msg'=>'类型不正确'];
        }

        if($type == 2){
            $t = time();
            $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
            $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
            $exist = ActQualificationLog::find()->where(['user_id'=>$uid, 'type'=>2])->orderBy('id desc')->one();
            if($exist['created_at'] >= $start && $exist['created_at'] <= $end){
                return ['code'=>202, 'msg'=>'当天限领一次'];
            }
        }

        $model = new ActQualificationLog();
        $model->user_id = $uid;
        $model->created_at = time();
        $model->type = $type;
        $model->num = $num;
        if(!$model->save()){
            return ['code'=>203, 'msg'=>'领取失败'];
        }

        $qModel = ActQualification::findOne(['user_id'=>$uid]);
        if($qModel['id']){
            $qModel->num = $qModel['num'] + $num;
            $qModel->created_at = time();
            if($qModel->save()){
                return ['code'=>0, 'msg'=>'领取成功'];
            }else{
                return ['code'=>204, 'msg'=>'领取失败'];
            }
        }else{
            $newModel = new ActQualification();
            $newModel->user_id = $uid;
            $newModel->created_at = time();
            $newModel->num = $num;
            if($newModel->save()){
                return ['code'=>0, 'msg'=>'领取成功'];
            }else{
                return ['code'=>205, 'msg'=>'领取失败'];
            }
        }
    }
}
