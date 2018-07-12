<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use app\services\User as UserFind;

/**
 * This is the model class for table "friends".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $friend_userid
 * @property integer $dateline
 */
class Friend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'friends';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'friend_userid', 'dateline'], 'integer'],
            [['dateline'], 'required']
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
            'friend_userid' => 'Friend Userid',
            'dateline' => 'Dateline',
        ];
    }

    public static function userInfo($userid)
    {
        $user = User::find()->where(['id' => $userid])->asArray()->one();
        if ($user) {
            if ($user['nickname']) {
                $user['username'] = $user['nickname'];
            } elseif ($user['phone']) {
                $user['username'] = $user['phone'];
            } elseif ($user['email']) {
                $user['username'] = $user['email'];
            }
        }
        return $user;
    }

    //判断查询的内容
    public static function contentDeal($content)
    {
        $email = preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/",$content);
        $phone = preg_match('/^-?[1-9]\d*$/', $content);
        if($email){
            $where = ['like', 'email', $content];
        }elseif($phone){
            $where = ['like', 'phone', $content];
        }else{
            $where = ['like', 'nickname', $content];
        }

        $query = User::find()->where($where)->andWhere('id != '.Yii::$app->user->id);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>10 ]);
        $list = $query->orderBy('id desc')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        foreach($list as $key => $val){
            $limit = UserLimit::find()->where(['user_id'=>$val['id']])->one();
            if(isset($limit['friend_search']) && $limit['friend_search'] == 0){
                unset($list[$key]);
                $pagination->totalCount = $pagination->totalCount - 1;
            }
        }

        return ['list'=> $list, 'pagination'=>$pagination];
    }

    //添加好友
    public static function addFriend($user_id, $to_userid)
    {
        $friendModel = new Friend();
        $friendModel->user_id = $user_id;
        $friendModel->friend_userid = $to_userid;
        $friendModel->dateline = time();
        $friendModel->save();

        $otherModel = new Friend();
        $otherModel->user_id = $to_userid;
        $otherModel->friend_userid = $user_id;
        $otherModel->dateline = time();
        $otherModel->save();

        /*$info = UserFind::baseInfo($user_id);
        static::addSysMsg($to_userid, $info['username'].'已通过您的好友请求');*/
    }

    //添加系统消息
    public static function addSysMsg($toUserid, $content)
    {
        $system = new UserSystemMessage();
        $system->to_userid = $toUserid;
        $system->message = $content;
        $system->created_at = time();
        $system->save();
    }

}
