<?php

namespace app\modules\user\controllers;

use app\controllers\BaseController;
use app\models\ShareTopic;
use app\models\ShareTopicImage;
use app\models\GroupUser;
use app\models\GroupTopic;
use app\models\GroupTopicComment;
use app\models\Group;
use app\models\Image;
use app\models\UserLimit;
use yii\data\Pagination;
use app\models\User;
use app\services\User as ServiceUser;
use yii\web\NotFoundHttpException;
use app\helpers\DateFormat;
use app\models\Friend;
use app\models\FriendApply;
use app\services\Member;
use app\helpers\Ip;
use app\models\UserSystemMessage;

class DefaultController extends BaseController
{

    public $homeId;
    public $userId;
    public $isFriend;

    public function init()
    {
        parent::init();
        $request = \Yii::$app->request;
        $this->homeId = $request->get('id');
        $user = User::findOne(['home_id'=>$this->homeId]);
        if(!$user){
            throw new NotFoundHttpException("不存在该用户");
        }
        $this->userId = $user->id;

        $isFriend = Friend::findOne(['user_id'=>\Yii::$app->user->id, 'friend_userid'=>$this->userId]);
        $this->isFriend = $isFriend['id'];
    }

    public function render($view, $params = [])
    {
        $request = \Yii::$app->request;
        $conn = \Yii::$app->db;

        if($request->isGet){
            //用户登录状态 插入最近访客表
            if (!\Yii::$app->user->isGuest) {
                $uid = \Yii::$app->user->id;
                $start = strtotime(date('Y-m-d',time()));
                $nums = $conn->createCommand('select count(1) as total from recent_visitors where user_id = '.$uid.' and created_at >= '.$start.' and created_at < '.time().'');
                $queryNum = $nums->queryOne();
                if($queryNum['total'] < 50){
                    if($uid != $this->userId){
                        $command = $conn->createCommand('SELECT * FROM recent_visitors WHERE user_id='.$uid.' and visited_user_id = '.$this->userId);
                        $find = $command->queryOne();
                        if($find){
                            $conn->createCommand()->delete('recent_visitors', 'id='.$find['id'])->execute();
                        }
                        $conn->createCommand()->insert('recent_visitors', [
                            'user_id' => $uid,
                            'visited_user_id' => $this->userId,
                            'created_at' => time()
                        ])->execute();
                    }
                }
                $isFriend = Friend::findOne(['user_id'=>$uid, 'friend_userid'=>$this->userId]);
            }

        }

        //查询最近访客记录
        $visitors = [];
        $recentVisitors = $conn->createCommand('SELECT * FROM recent_visitors WHERE visited_user_id = '.$this->userId.' ORDER BY id DESC LIMIT 10 ')->queryAll();
        foreach($recentVisitors as $key=>$val){
            $allInfo = ServiceUser::allInfo($val['user_id']);
            $visitors[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
            $visitors[$key]['userinfo'] = ServiceUser::baseInfo($val['user_id']);
            $visitors[$key]['allinfo'] = $allInfo;
            if(\Yii::$app->user->id){
                $visitors[$key]['isFriend'] = Friend::find()->where(['and', 'user_id='.$val['user_id'], 'friend_userid='.\Yii::$app->user->id])->count();
            }
            $visitors[$key]['live'] = $allInfo['hometown'];
            $visitors[$key]['avatar'] = Image::getUserFaceUrl($visitors[$key]['userinfo']['avatar'], 160);
        }

        //话题和回复数量
        $num['topicNum'] = GroupTopic::find()->where(['user_id'=>$this->userId, 'status'=>1])->count();
        $num['commentNum'] = GroupTopicComment::find()->where(['user_id'=>$this->userId, 'is_topic'=>0])->count();

        //该用户信息
        $userInfo['username'] = ServiceUser::baseInfo($this->userId);
        $userInfo['info'] = ServiceUser::allInfo($this->userId);
        $avatar = Image::getUserFaceUrl($userInfo['info']['avatar'], 160);

        //页面title
        $titleInfo = $userInfo['username'];
        if (!empty($titleInfo['nickname'])) {
            $params['user_title'] = $titleInfo['nickname'] . '(' . ($titleInfo['phone'] ? ServiceUser::privatePhone($titleInfo['phone']) : ServiceUser::privateEmail($titleInfo['email'])) . ')';
        } else {
            $params['user_title'] = $titleInfo['phone'] ? ServiceUser::privatePhone($titleInfo['phone']) : ServiceUser::privateEmail($titleInfo['email']);
        }

        $params['visitors'] = $visitors;
        $params['userinfo'] = $userInfo;
        $params['isfriend'] = isset($isFriend) ? $isFriend : '';
        $params['avatar'] = $avatar;
        $params['num'] = $num;
        return parent::render($view, $params);
    }

    //个人主页
    public function actionIndex()
    {
        $page = \Yii::$app->request->get('page');
        $page = isset($page) ? $page : 1;
        //隐私设置
        $total = 'all';
        $limit = UserLimit::findOne(['user_id'=>$this->userId]);
        if($this->userId == \Yii::$app->user->id){
            $total = 'all';
            $limit['ucenter_buylist'] = 0;
        }elseif($limit){
            if($limit['ucenter_buylist'] == 1 && $limit['buylist_number'] != 0){
                $total = $limit['buylist_number'];
            }elseif($limit['ucenter_buylist'] == 2){
                if($this->isFriend) $total = 'all';
                else $total = 'zero';
            }elseif($limit['ucenter_buylist'] == 0){
                $total = 'zero';
            }
        }

        $buyList = ServiceUser::buyList(\Yii::$app->user->id, $this->homeId, $page, 8);

        $arr = [];
        foreach($buyList['list'] as $key => $val){
            $arr[$key]['goods_name'] = $val['goods_name'];
            $arr[$key]['goods_picture'] = Image::getProductUrl($val['goods_picture'], 200, 200);
            $arr[$key]['period_number'] = $val['period_number'];
            $arr[$key]['period_id'] = $val['period_id'];
            $arr[$key]['code_price'] = floor($val['code_price']);
            $arr[$key]['status'] = $val['status'];
            $arr[$key]['user_buy_num']  = $val['user_buy_num'];
            $arr[$key]['user_buy_time'] = DateFormat::userTime($val['user_buy_time']);
            $arr[$key]['product_id']  = $val['product_id'];
            $arr[$key]['limit_num'] = $val['limit_num'];
            if($val['status'] == 2){
                $arr[$key]['user_name'] = $val['user_name'];
                $arr[$key]['raff_time'] = $val['raff_time'];
                $arr[$key]['lucky_code'] = $val['lucky_code'];
            }elseif($val['status'] == 0){
                $arr[$key]['left_num'] = $val['left_num'];
                $arr[$key]['code_sales'] = $val['code_sales'];
                $per = floor(($val['code_sales'] / $val['code_price']) * 100 * 100);
                $arr[$key]['progress'] = $per / 100;
            }
        }

        return $this->render('index', [
            'arr' => $arr,
            'pagination' => $buyList['pagination'],
            'ucenter_buylist' => $limit['ucenter_buylist'],
            'user_id' => substr($this->homeId, 3, strlen($this->homeId) - 1),
            'total' => $total
        ]);
    }

    //伙购记录
    public function actionUserBuy()
    {
        $page = \Yii::$app->request->get('page');
        $page = isset($page) ? $page : 1;

        //隐私设置
        $total = 'all';
        $limit = UserLimit::findOne(['user_id'=>$this->userId]);
        if($this->userId == \Yii::$app->user->id){
            $total = 'all';
            $limit['ucenter_buylist'] = 0;
        }elseif($limit){
            if($limit['ucenter_buylist'] == 1 && $limit['buylist_number'] != 0){
                $total = $limit['buylist_number'];
            }elseif($limit['ucenter_buylist'] == 2){
                if($this->isFriend) $total = 'all';
                else $total = 'zero';
            }elseif($limit['ucenter_buylist'] == 0){
                $total = 'zero';
            }
        }

        $buyList = ServiceUser::buyList(\Yii::$app->user->id, $this->homeId, $page, 8);

        $arr = [];
        foreach($buyList['list'] as $key => $val){
            $arr[$key]['goods_name'] = $val['goods_name'];
            $arr[$key]['goods_picture'] = Image::getProductUrl($val['goods_picture'], 200, 200);
            $arr[$key]['period_number'] = $val['period_number'];
            $arr[$key]['period_id'] = $val['period_id'];
            $arr[$key]['code_price'] = floor($val['code_price']);
            $arr[$key]['status'] = $val['status'];
            $arr[$key]['user_buy_num']  = $val['user_buy_num'];
            $arr[$key]['user_buy_time'] = DateFormat::userTime($val['user_buy_time']);
            $arr[$key]['product_id']  = $val['product_id'];
            if($val['status'] == 2){
                $arr[$key]['user_name'] = $val['user_name'];
                $arr[$key]['raff_time'] = $val['raff_time'];
            }
        }

        return $this->render('user-buy', [
            'arr' => $arr,
            'pagination' => $buyList['pagination'],
            'ucenter_buylist' => $limit['ucenter_buylist'],
            'user_id' => substr($this->homeId, 3, strlen($this->homeId) - 1),
            'total' => $total
        ]);
    }

    //获得的商品
    public function actionUserRaffle()
    {
        $page = \Yii::$app->request->get('page');
        $page = isset($page) ? $page : 1;

        //隐私设置
        $total = 'all';
        $limit = UserLimit::findOne(['user_id'=>$this->userId]);
        if($this->userId == \Yii::$app->user->id){
            $total = 'all';
            $limit['ucenter_orderlist'] = 0;
        }elseif($limit){
            if($limit['ucenter_orderlist'] == 1 && $limit['orderlist_number'] != 0){
                $total = $limit['orderlist_number'];
            }elseif($limit['ucenter_orderlist'] == 2){
                if($this->isFriend) $total = 'all';
                else $total = 'zero';
            }elseif($limit['ucenter_orderlist'] == 0){
                $total = 'zero';
            }
        }

        $productList = ServiceUser::productList(\Yii::$app->user->id, $this->homeId, $page, 8);

        $arr = [];
        foreach ($productList['list'] as $key => $val) {
            $arr[$key]['goods_name'] = $val['goods_name'];
            $arr[$key]['period_number'] = $val['period_number'];
            $arr[$key]['period_id'] = $val['period_id'];
            $arr[$key]['price'] = floor($val['price']);
            $arr[$key]['lucky_code'] = $val['lucky_code'];
            $arr[$key]['goods_id'] = $val['goods_id'];
            $arr[$key]['goods_picture'] = Image::getProductUrl($val['goods_picture'], 200, 200);
            $time = strtotime($val['raff_time']);
            $arr[$key]['user_buy_time'] = DateFormat::userTime($time);
            $arr[$key]['raff_time'] = $val['raff_time'];
            $arr[$key]['buy_num'] = $val['buy_num'];
        }
        return $this->render('user-raffle', [
            'arr' => $arr,
            'pagination' => $productList['pagination'],
            'ucenter_orderlist' => $limit['ucenter_orderlist'],
            'user_id' => substr($this->homeId, 3, strlen($this->homeId) - 1),
            'total' => $total
        ]);
    }

    //晒单
    public function actionUserPost()
    {
        $page = \Yii::$app->request->get('page');
        $page = isset($page) ? $page : 1;
        //隐私设置
        $total = 'all';
        $limit = UserLimit::findOne(['user_id'=>$this->userId]);
        if($this->userId == \Yii::$app->user->id){
            $total = 'all';
            $limit['ucenter_sharelist'] = 0;
        }elseif($limit){
            if($limit['ucenter_sharelist'] == 1 && $limit['sharelist_number'] != 0){
                $total = $limit['sharelist_number'];
            }elseif($limit['ucenter_sharelist'] == 2){
                if($this->isFriend) $total = 'all';
                else $total = 'zero';
            }elseif($limit['ucenter_sharelist'] == 0){
                $total = 'zero';
            }
        }

        //$shareTopics = ShareTopic::getListByType(10, 0, 0, 10, 1, substr($this->homeId, 3, strlen($this->homeId) - 1), $total);
        $shareTopics = ServiceUser::shareList(\Yii::$app->user->id, $this->homeId, $page, 10);

        $arr = [];
        foreach($shareTopics['list'] as $key => $topic) {
            $shareImg = ShareTopicImage::find()->where(['share_topic_id'=>$topic['id']])->limit(3)->asArray()->all();
            $arr[$key]['one'] = Image::getShareInfoUrl($shareImg['0']['basename'], 'small');
            $arr[$key]['onebig'] = Image::getShareInfoUrl($shareImg['0']['basename'], 'big');
            $arr[$key]['two'] = Image::getShareInfoUrl($shareImg['1']['basename'], 'small');
            $arr[$key]['twobig'] = Image::getShareInfoUrl($shareImg['1']['basename'], 'big');
            $arr[$key]['three'] = Image::getShareInfoUrl($shareImg['2']['basename'], 'small');
            $arr[$key]['threebig'] = Image::getShareInfoUrl($shareImg['2']['basename'], 'big');
            $arr[$key]['created_at'] = $topic['created_at'];
            $arr[$key]['content'] = $topic['content'];
            $arr[$key]['title'] = $topic['title'];
            $arr[$key]['id'] = $topic['id'];
        }

        return $this->render('user-post', [
            'shareTopics' => $arr,
            'pagination' => $shareTopics['pagination'],
            'ucenter_sharelist' => $limit['ucenter_sharelist'],
            'user_id' => substr($this->homeId, 3, strlen($this->homeId) - 1),
            'total' => $total
        ]);
    }

    //加入的圈子
    public function actionUserGroup()
    {
        $joinGroups = GroupUser::find()->where(['user_id'=>$this->userId])->orderBy('group_id asc')->all();
        $arr = [];
        if($joinGroups) {
            foreach ($joinGroups as $key => $val) {
                $group = Group::findOne($val['group_id']);
                $pic = Image::getGoupIconUrl($group['picture']);
                $arr[$key] = $group;
                $arr[$key]['picture'] = $pic;
            }
        }

        return $this->render('user-group', [
            'joinGroups' => $arr,
        ]);
    }

    //话题
    public function actionUserTopic()
    {
        $user = User::findOne($this->userId);
        $query = GroupTopic::find();

        $countQuery = clone $query;
        $where['user_id'] = $user['id'];
        $where['status'] = 1;

        $count = $countQuery->where($where)->count();
        $pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' =>10 ]);
        $publicTopic = $query->where($where)->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();
        $arr =[];
        foreach($publicTopic as $key => $val){
            $content = GroupTopicComment::findOne(['topic_id'=>$val['id']]);
            $arr[$key] = $val;
            $arr[$key]['message'] = GroupTopicComment::messageDeal($content['id']);
            $arr[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
        }
        $getGroup = Group::getGroup();
        return $this->render('user-topic', [
            'publicTopic' => $arr,
            'pagination' => $pagination,
            'getGroup' => $getGroup,
        ]);
    }

    //话题回复
    public function actionUserTopicComment()
    {
        $user = User::findOne($this->userId);
        $query = GroupTopicComment::find();
        $countQuery = clone $query;
        $where['user_id'] = $user['id'];
        $where['is_topic'] = 0;
        $where['status'] = 1;

        $count = $countQuery->where($where)->count();
        if($count){
            $pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' =>10 ]);
            $topicComment = $query->where($where)->offset($pagination->offset)
                ->limit($pagination->limit)
                ->orderBy('id desc')
                ->all();
            foreach($topicComment as $key => $val){
                $subject = GroupTopic::findOne(['id'=>$val['topic_id']]);
                $topicComment[$key]['subject'] = $subject['subject'];
                $topicComment[$key]['message'] = GroupTopicComment::commentDeal($val['message']);
                $topicComment[$key]['created_at'] = DateFormat::formatTime($val['created_at']);
            }
            return $this->render('user-topic', [
                'topicComment' => $topicComment,
                'pagination' => $pagination,
                'status' => 1
            ]);
        }else{
            return $this->render('user-topic', [
                'status' => 1
            ]);
        }
    }

    //好友
    public function actionUserFriends()
    {
        $user = User::findOne(['home_id'=>$this->homeId]);
        $page = \Yii::$app->request->get('page');
        $page = isset($page) ? $page :1;

        $member = new Member();
        $friendList = $member->getFirends($page, $perpage = 9, $user['id']);

        foreach($friendList['list'] as $key => $val){
            $uid = \Yii::$app->user->id;
            if($uid){
                $friend = Friend::findOne(['user_id'=>$uid, 'friend_userid'=>$val['friend_userid']]);
            }else{
                $friend['id'] = '';
            }
            $friendList['list'][$key]['friend'] = $friend['id'];
        }

        return $this->render('user-friends', [
            'friendList' => $friendList,
        ]);
    }

    //发送好友请求
    public function actionApplyFriend()
    {
        $request = \Yii::$app->request;
        $user_id = \Yii::$app->user->id;
        if($request->isGet){
            if($user_id){
                $get['apply_userid'] = $request->get('id');
                $applyUser = User::findOne(['home_id'=>$get['apply_userid']]);
                $exits = FriendApply::findOne(['apply_userid'=>$applyUser['id'], 'user_id'=>$user_id]);
                if($exits && $exits['status'] == 1){
                    return 3;
                }elseif($exits && $exits['status'] == 0){
                    return 2;
                }else{
                    $model = new FriendApply();
                    $model->user_id = $user_id;
                    $model->apply_userid = $applyUser['id'];
                    $model->apply_time = time();
                    $model->save();

                    $name = ServiceUser::baseInfo($user_id);
                    $msgModel = new UserSystemMessage();
                    $msgModel->to_userid = $applyUser['id'];
                    $msgModel->message = $name['username'].'请求加为好友';
                    $msgModel->created_at = time();
                    $msgModel->status = 0;
                    $msgModel->save();
                    return 0;
                }
            }else{
                return 1;
            }

        }
    }

    //发送私信
   /* public function actionSendPrivMsg()
    {
        $request = \Yii::$app->request;
        $uid = \Yii::$app->user->id;
        if($request->isPost){
            $post = $request->post();
            $user = User::findOne(['home_id'=>$post['homeId']]);
            $model = new UserPrivateMessage();
            $model->user_id = $uid;
            $model->reply_userid = $user['id'];
            $model->content = $post['content'];
            $model->created_at = time();
            $model->save();
            return $this->redirect(\Yii::$app->request->referrer);
        }
    }*/

    public function actionSendMsg()
    {
        $id = $this->userId;
        $userId = \Yii::$app->user->id;
        if(!$userId) return 2;
        $isFriend = Friend::findOne(['user_id'=>$id, 'friend_userid'=>$userId]);
        if($isFriend){
            return 1;
        }else{
            return 0;
        }
    }
}
