<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/9/23
 * Time: 17:03
 */

namespace app\modules\member\controllers;

use app\models\Image;
use app\models\UserSystemMessage;
use app\models\UserPrivateMessage;
use app\models\User as FindUser;
use app\services\User;
use app\models\UserMessage;
use app\models\UserLimit;

class MessageController extends BaseController
{

    public function actionUserPrivMsg()
    {
        $uid = \Yii::$app->user->id;
        $page = \Yii::$app->request->get('page');
        UserMessage::updateAll(['view'=>1], ['to_userid'=>$uid, 'type'=>3]);

        $page = $page ? $page : 1;
        $messageList = UserPrivateMessage::getPrivMsg($uid, $page);

        return $this->render('index',[
            'messageList' => $messageList,
        ]);
    }

    public function actionDelPrivMsg()
    {
        $request = \Yii::$app->request;
        $uid = \Yii::$app->user->id;
        if($uid){
            if($request->isGet){
                $id = $request->get('id');
                $home_id = $request->get('home_id');
                $msg_all = $request->get('msg_all');
                $toUserId = FindUser::findOne(['home_id'=>$home_id]);
                $result = UserPrivateMessage::delPrivMsg($id, $toUserId['id'], $msg_all, $uid);

                return $result;
            }
        }else{
           return 0;
        }
    }

    public function actionMsgDetail()
    {
        $request = \Yii::$app->request;
        $uid = \Yii::$app->user->id;
        if($request->isGet){
            $homeId = $request->get('id');
            $id = FindUser::findOne(['home_id'=>$homeId]);
            $user = User::baseInfo($id['id']);
            $u = User::baseInfo($uid);
            $userAvatar = Image::getUserFaceUrl($u['avatar'], 160);
            $list = UserPrivateMessage::getMessageDetail($uid, $id['id']);
        }

        return $this->render('detail', [
            'list' => $list,
            'toUser' => $user,
            'userAvatar' => $userAvatar
        ]);
    }

    //发送私信
    public function actionSendPrivMsg()
    {
        $request = \Yii::$app->request;
        $uid = \Yii::$app->user->id;
        if($request->isPost){
            $post = $request->post();
            $user = FindUser::findOne(['home_id'=>$post['homeId']]);
            $limit = UserLimit::findOne(['user_id'=>$user['id']]);
            if($limit['id'] && $limit['private_letter'] == 0) return 3;
            if($post['content'] != strip_tags($post['content'])) return 2;
            $model = new UserPrivateMessage();
            $model->user_id = $uid;
            $model->reply_userid = $user['id'];
            $model->content = strip_tags($post['content']);
            $model->created_at = time();
            if($model->save()){
                UserMessage::addMessage($user['id'], 3, '私信');
                return 1;
            }else{
                return 0;
            }

        }
    }

    //系统消息
    public function actionSysMsg()
    {
        $uid = \Yii::$app->user->id;
        $list = UserPrivateMessage::getSystemMessage($uid);

        return $this->render('system', [
            'list' => $list,
        ]);
    }

    //更新系统消息
    public function actionUpdate()
    {
        $uid = \Yii::$app->user->id;
        UserSystemMessage::updateAll(['status'=>1], 'to_userid='.$uid);
    }

    //删除系统消息
    public function actionDelSysMsg()
    {
        $request = \Yii::$app->request;
        $uid = \Yii::$app->user->id;
        if($uid){
            if($request->isGet){
                $id = $request->get('id');
                $sysall = $request->get('sysall');
                $result = UserSystemMessage::delSystenMessage($id, $uid, $sysall);

                return $result;
            }
        }else{
            return 0;
        }
    }

}