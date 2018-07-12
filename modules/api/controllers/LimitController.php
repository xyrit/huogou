<?php
namespace app\modules\api\controllers;

use app\models\GroupTopicComment;
use Yii;
use app\models\GroupTopic;
use app\models\User;

class LimitController extends BaseController
{
    public $uid;
    public $user;

    public function init()
    {
        $this->uid = Yii::$app->user->id;
        $homeId = User::findOne($this->uid);
        $acturl = substr($homeId['home_id'], 0 , 3);
        $conn = \Yii::$app->db;
        $balsql = $conn->createCommand('SELECT count(1) as total FROM user_buylist_'.$acturl.' where user_id = '.$this->uid.'');
        $balance = $balsql->queryOne();
        if($balance['total']){
            $this->user = 1;
        }else{
            $this->user = 0;
        }
    }

    //用户发表的话题数
    public function actionTopicNum()
    {
        $conn = \Yii::$app->db;
        $start = strtotime(date('Y-m-d',time()));
        $balsql = $conn->createCommand('select count(1) as total from group_topics where user_id = "'.$this->uid.'" and created_at >= "'.$start.'" and created_at <= "'.time().'" ');
        $count = $balsql->queryOne();

        if($this->user == 1){
            if($count['total'] > 20){
                return ['code' => '101', 'message' => '您有广告的嫌疑'];
            }else{
                return ['code' => '100', 'message' => 'allow'];
            }
        }elseif($this->user == 0){
            if($count['total'] > 2){
                return ['code' => '101', 'message' => '您有广告的嫌疑'];
            }else{
                return ['code' => '100', 'message' => 'allow'];
            }
        }
    }

    //用户回帖
    public function actionCommentNum()
    {
        $conn = \Yii::$app->db;
        $start = strtotime(date('Y-m-d',time()));
        $balsql = $conn->createCommand('select count(1) as total from group_topic_comments where is_topic = 0 and user_id = "'.$this->uid.'" and created_at >= "'.$start.'" and created_at <= "'.time().'" ');
        $count = $balsql->queryOne();

        $content = Yii::$app->request->get('content');
        if($content != strip_tags($content)) return ['code' => 102, 'message' => '您有广告嫌疑'];

        if($this->user == 1){
            if($count['total'] < 5) return ['code' => 100, 'message' => 'allow'];
            if($count['total'] > 100) return ['code' => 102, 'message' => '您有广告嫌疑'];
            $num = GroupTopicComment::find()->where(['user_id'=>$this->uid])->orderBy('id desc')->limit(5)->all();
            $time = $num[0]['created_at'] - $num[4]['created_at'];
            if($time > 60){
                return ['code' => 100, 'message' => 'allow'];
            }else{
                $lefttime = (time()-$num[0]['created_at']) - 600;
                if($lefttime >= 0){
                    return ['code' => 100, 'message' => 'allow'];
                }else{
                    return ['code' => 101, 'message' => '操作频繁'];
                }
            }
        }elseif($this->user == 0){
            if($count['total'] < 3) return ['code' => 100, 'message' => 'allow'];
            if($count['total'] > 20) return ['code' => 102, 'message' => '您有广告嫌疑'];
            $num = GroupTopicComment::find()->where(['user_id'=>$this->uid])->orderBy('id desc')->limit(3)->all();
            $time = $num[0]['created_at'] - $num[2]['created_at'];
            if($time > 60){
                return ['code' => 100, 'message' => 'allow'];
            }else{
                $lefttime = (time()-$num[0]['created_at']) - 1800;
                if($lefttime >= 0){
                    return ['code' => 100, 'message' => 'allow'];
                }else{
                    return ['code' => 101, 'message' => '操作频繁'];
                }
            }
        }
    }

}