<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use app\services\User as UserFind;
use app\helpers\DateFormat;
use app\models\Image as ServiceImage;

/**
 * This is the model class for table "user_private_messages".
 *
 * @property string $id
 * @property string $user_id
 * @property string $reply_userid
 * @property string $content
 * @property string $created_at
 */
class UserPrivateMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_private_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'reply_userid','content', 'created_at'], 'required'],
            [['user_id', 'reply_userid', 'created_at'], 'integer'],
            [['content'], 'string', 'max' => 255]
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
            'reply_userid' => 'Reply Userid',
            'content' => 'Content',
            'created_at' => 'Created At',
        ];
    }

    //获取用户私信
    public static function getPrivMsg($uid, $page = 1, $limit = 10)
    {
        $arr = static::getGroup($uid);
        foreach($arr as $key => $val){
            $arr[$key] = $val.'_'.$key;
        }
        $count = count($arr, 1);
        $pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' =>$limit ]);

        $start = ($page - 1) * $limit;
        $arr = array_slice($arr, $start, $limit);

        $returnData = [];
        foreach($arr as $key => $val){
            $valArr = explode('_', $val);
            $id = $valArr[0];
            $msgId = $valArr[1];
            $where = ['or', ['and', 'user_id='.$id, 'reply_userid='.$uid], ['and', 'user_id='.$uid, 'reply_userid='.$id]];
            $info = UserPrivateMessage::findOne($msgId);
            $returnData[$key]['id'] = $info['user_id'];
            $returnData[$key]['user'] = UserFind::baseInfo($id);
            $returnData[$key]['avatar'] = ServiceImage::getUserFaceUrl($returnData[$key]['user']['avatar'], 80);
            $returnData[$key]['content'] = GroupTopicComment::commentDeal($info['content']);
            $returnData[$key]['msgId'] = $msgId;
            $returnData[$key]['created_at'] = DateFormat::formatTime($info['created_at']);
            $returnData[$key]['count'] = UserPrivateMessage::find()->where($where)->count();
        }

        return ['list'=>$returnData, 'pagination'=>$pagination];
    }

    //取得分组结果
    public static function getGroup($uid)
    {
        $where = ['or', 'user_id='.$uid, 'reply_userid='.$uid];
        $allUserid = UserPrivateMessage::find()->where($where)->asArray()->all();

        $unqiue = [];
        foreach($allUserid as $key => $val){
            if(($val['user_id'] != $uid) ){
                $unqiue[$val['id']] = $val['user_id'];
            }elseif($val['reply_userid'] != $uid){
                $unqiue[$val['id']] = $val['reply_userid'];
            }
        }
        krsort($unqiue);
        $unqiue = static::a_array_unique($unqiue);

        return $unqiue;
    }

    public static function a_array_unique($array)
    {
        $out = array();
        foreach ($array as $key=>$value) {
            if (!in_array($value, $out))
            {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    //获取用户对话详情
    public static function getMessageDetail($uid, $replyId, $limit = 10)
    {
        $where = ['or', ['and', 'user_id='.$uid, 'reply_userid='.$replyId], ['and', 'user_id='.$replyId, 'reply_userid='.$uid]];
        $query = UserPrivateMessage::find()->where($where);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>$limit ]);
        $list = $query->orderBy('id desc')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $returnData = [];
        foreach($list as $key => $val){
            $returnData[$key]['id'] = $val['id'];
            $returnData[$key]['user'] = UserFind::baseInfo($val['user_id']);
            $returnData[$key]['user_avatar'] = ServiceImage::getUserFaceUrl($returnData[$key]['user']['avatar'], 160);
            $returnData[$key]['content'] = GroupTopicComment::commentDeal($val['content']);
            $returnData[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
        }

        return['data'=>$returnData, 'pagination'=>$pagination];
    }

    /**
     * 删除私信
     * $type ($id=>针对单条记录，$toUserId=>与此相关的用户记录，$all=>所有消息)
     **/
    public static function delPrivMsg($id = '', $toUserId = '', $all = '', $userid = '')
    {

        if($id){
            $find = UserPrivateMessage::findOne($id);
            if($find->delete()){
                return 1;
            }else{
                return 0;
            }exit;
        }elseif($toUserId){
            $where = ['or', ['and', 'user_id='.$userid, 'reply_userid='.$toUserId], ['and', 'user_id='.$toUserId, 'reply_userid='.$userid]];
        }elseif($all){
            $where = ['or', 'user_id='.$userid, 'reply_userid='.$userid];
        }

        $result = UserPrivateMessage::deleteAll($where);

        if($result){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 系统消息
     */
    public static function getSystemMessage($uid, $limit = 10)
    {
        $where = ['to_userid'=>$uid];
        $query = UserSystemMessage::find()->where($where);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>$limit ]);
        $list = $query->where($where)->orderBy('id desc')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        //UserSystemMessage::updateAll(['status'=>1], 'to_userid='.$uid);

        foreach($list as $key => $val){
            $list[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
        }

        return ['list'=>$list, 'pagination'=>$pagination];
    }

}
