<?php
/**
 * Created by PhpStorm.
 * User: suyan
 * Date: 2015/10/14
 * Time: 14:20
 */
namespace app\modules\member\controllers;

use app\models\Image;
use app\models\UserMessage;
use app\services\User;
use yii\helpers\Json;
use app\services\Member;
use app\models\FriendApply;
use app\models\Friend;
use app\helpers\Message;
use app\models\User as ModelUser;

class FriendController extends BaseController
{
    public function render($view, $params = [])
    {
        $user_id = \Yii::$app->user->id;
        $member = new Member(['id'=>$user_id]);
        $frinedList = $member->getFirends(1);
        $applyList = $member->getFirendsApply(1);

        $params['friendNum'] = $frinedList['totalCount'];
        $params['applyNum'] = $applyList['totalCount'];
        return parent::render($view, $params);
    }

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $page = $request->get('page');
        $page = isset($page) ? $page : 1;
        $user_id = \Yii::$app->user->id;
        $member = new Member(['id'=>$user_id]);
        $frinedList = $member->getFirends($page);

        if($request->isPost){
            $content = $request->post('content');
            $arr = [];
            foreach($frinedList['list'] as $key=>$val){
                $username = Friend::userInfo($val['user_id']);
                if(stripos($username['username'], $content) !== false){
                    $arr[$key] = $val;
                }
            }

            return $this->render('index', [
                'findFriends' => $arr,
                'num' => $frinedList['totalCount']
            ]);
        }else{
            return $this->render('index', [
                'friendList' => $frinedList,
            ]);
        }
    }

    public function actionSearchFriend()
    {
        $request = \Yii::$app->request;
        $user_id = \Yii::$app->user->id;
        $status = $request->get('status');
        $member = new Member(['id'=>$user_id]);
        $content = $request->get('content');
        $random = $member->randomFriend($status);

        if(isset($content) && $content){
            $content = $request->get('content');
            $list = Friend::contentDeal($content);
            $all = $member->findFriendList($list['list']);

            return $this->render('search', [
                'all' => $all,
                'pagination' => $list['pagination'],
                'content' => $content
            ]);
        }

        return $this->render('search', [
            'randomUser' => $random,
            'status' => $status
        ]);
    }

    public function actionFriendApply()
    {
        $uid = \Yii::$app->user->id;
        $request = \Yii::$app->request;
        UserMessage::updateAll(['view'=>1], ['to_userid'=>$uid, 'type'=>1]);

        $page = $request->get('page');
        $page = isset($page) ? $page : 1;
        $member = new Member(['id'=>$uid]);
        $applyList = $member->getFirendsApply($page);

        return $this->render('apply', [
            'applyList' => $applyList,
        ]);
    }

    //是否同意好友请求
    public function actionAgreeFriendApply()
    {
        $request = \Yii::$app->request;
        $user_id = \Yii::$app->user->id;
        $userInfo = User::baseInfo($user_id);

        if($request->isGet){
            if($user_id){
                $applyId = $request->get('applyid');
                $applyuserid = $request->get('applyuserid');
                $model = FriendApply::findOne($applyId);
                if($model){
                    if($applyuserid){
                        $status = 1;
                        $model->status = $status;
                        Friend::addFriend($user_id, $applyuserid);
                        $username = ModelUser::userName($model['user_id']);
                        //Message::send(24, $model['user_id'], ['nickname'=>$username['username'], 'oppositeNickname'=>$userInfo['username']]);
                        $member = new Member();
                        $member->editExperience(5, 8, '加好友奖励');
                    }else{
                        $status = 2;
                        $model->status = $status;
                    }
                    $model->save();
                    $otherModel = FriendApply::find()->where(['user_id'=>$model['apply_userid'], 'apply_userid'=>$model['user_id']])->one();
                    if($otherModel){
                        $otherModel->status = $status;
                        $otherModel->save();
                    }
                    return 0;
                }else{
                    return 2;
                }
            }else{
                return 1;
            }
        }
    }

    //全部同意或忽略好友请求
    public function actionAgreeAll()
    {
        $request = \Yii::$app->request;
        $uid = \Yii::$app->user->id;
        $status = $request->get('status');
        if(isset($status) && $status == 1){
            $status = 2;
        }else{
            $status = 1;
        }

        $list = FriendApply::find()->where(['status'=>0, 'apply_userid'=>$uid])->all();
        foreach($list as $key => $val){
            $model = FriendApply::findOne($val['id']);
            $userInfo = User::baseInfo($val['apply_userid']);
            $model->status = $status;
            $model->save();

            if($status == 1){
                Friend::addFriend($val['apply_userid'], $val['user_id']);
                $username = ModelUser::userName($model['user_id']);
                //Message::send(24, $model['user_id'], ['nickname'=>$username['username'], 'oppositeNickname'=>$userInfo['username']]);
                $member = new Member(['id' => $model['user_id']]);
                $member->editExperience(1000, 8, '加好友奖励');
            }
        }

         return $this->redirect(\Yii::$app->request->referrer);
    }

    //删除好友
    public function actionDelFriend()
    {
        $request = \Yii::$app->request;
        $user_id = \Yii::$app->user->id;
        $info = User::baseInfo($user_id);

        if($request->isGet){
            if($user_id){
                $friend_id = $request->get('userid');
                $friendModel = Friend::findOne(['user_id'=>$user_id, 'friend_userid'=>$friend_id]);
                $oppsiteModel = Friend::findOne(['user_id'=>$friend_id, 'friend_userid'=>$user_id]);
                $applyModel = FriendApply::findOne(['user_id'=>$friend_id, 'apply_userid'=>$user_id]);
                $applyOtherModel = FriendApply::findOne(['user_id'=>$user_id, 'apply_userid'=>$friend_id]);
                $member = new Member(['id' => $user_id]);
                $member->editExperience(-5, 8, '删除好友扣除奖励');
                if($friendModel || $oppsiteModel){
                    //Friend::addSysMsg($friend_id, '您已被'.$info['username'].'删除好友');
                    $friendModel->delete();
                    $oppsiteModel->delete();
                    if($applyModel) $applyModel->delete();
                    if($applyOtherModel) $applyOtherModel->delete();
                    if($applyModel && $applyOtherModel){
                        $applyModel->delete();
                        $applyOtherModel->delete();
                    }
                    return 0;
                }else{
                    return 2;
                }
            }else{
                return 1;
            }
        }
    }

    //在目前好友里查找
    public function actionFindFriends()
    {
        $request = \Yii::$app->request;
        $user_id = \Yii::$app->user->id;

        if($request->isGet){
            if($user_id){
                $content = $request->get('content');
                $arr = Friend::findFriend($content, $user_id);
                $member = new Member(['id'=>$user_id]);
                $returnData = $member->findFriendList($arr);

                return Json::encode($returnData);
            }else{
                return 1;
            }
        }
    }

    //发送好友请求
    public function actionApplyFriend()
    {
        $request = \Yii::$app->request;
        $user_id = \Yii::$app->user->id;
        if($request->isGet){
            if($user_id){
                $get['apply_userid'] = $request->get('id');
                $exits = FriendApply::findOne(['apply_userid'=>$get['apply_userid'], 'user_id'=>$user_id, 'status'=>0]);
                if($exits){
                    return 2;
                }else{
                    $model = new FriendApply();
                    $model->user_id = $user_id;
                    $model->apply_userid = $get['apply_userid'];
                    $model->apply_time = time();
                    $model->save();
                    return 0;
                }
            }else{
                return 1;
            }

        }
    }

}