<?php
/**
 * Created by PhpStorm.
 * User: chenyi
 * Date: 2015/10/8
 * Time: 15:20
 */
namespace app\modules\share\controllers;

use app\helpers\DateFormat;
use app\models\Image;
use app\models\ShareComment;
use app\models\ShareReply;
use app\services\Share;
use app\services\User;
use Yii;
use yii\base\Controller;

class CommentController extends Controller
{
    /**
     * ɹ������
     */
    public function actionIndex()
    {
        $post = Yii::$app->request->post();
        return ShareComment::addCommit($post);
    }

    /**
     * ɹ���ظ�
     */
    public function actionReply()
    {
        $post = Yii::$app->request->post();
        return ShareReply::addReply($post);
    }

    /**
     * ɾ��ɹ���ظ�
     */
    public function actionDelReply()
    {
        $id = Yii::$app->request->post('id');
        $userId = Yii::$app->user->id;

        return ShareReply::deleteAll(['id' => $id, 'user_id' => $userId]);
    }

    /**
     * �����б�
     */
    public function actionCommentList()
    {
        $id = Yii::$app->request->get('page');

        $shareComment = ShareComment::getList($id, 10);

        $userids = array();
        foreach ($shareComment['list'] as &$comment) {
            $userids[] = $comment['user_id'];
            $reply['created_at'] = DateFormat::formatTime($comment['created_at']);
        }

        $userInfos = User::baseInfo($userids);

        foreach ($shareComment['list'] as &$comment) {
            $comment['user_name'] = $userInfos[$comment['user_id']]['username'];
            $comment['user_avatar'] = Image::getUserFaceUrl($userInfos[$comment['user_id']]['avatar'], 'small');
        }

        return json_encode(['html' => "<li></li>"]);
        //return json_encode($shareComment);
    }

    /**
     * �ظ��б�
     */
    public function actionReplyList()
    {
        $id = Yii::$app->request->get('id');

        $shareReply = ShareReply::getList($id, 10);

        $userids = array();
        foreach ($shareReply['list'] as &$reply) {
            $userids[] = $reply['user_id'];
            $reply['created_at'] = DateFormat::formatTime($reply['created_at']);
        }

        $userInfos = User::baseInfo($userids);

        foreach ($shareReply['list'] as &$reply) {
            $reply['user_name'] = $userInfos[$reply['user_id']]['username'];
            $reply['user_avatar'] = Image::getUserFaceUrl($userInfos[$reply['user_id']]['avatar'], 'small');
        }

        return json_encode($shareReply);
    }
}