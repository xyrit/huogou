<?php

namespace app\models;

use app\helpers\Brower;
use app\helpers\MyRedis;
use app\services\Member;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "share_topics".
 *
 * @property string $id
 * @property string $title
 * @property string $content
 * @property string $user_id
 * @property string $period_id
 * @property string $product_id
 * @property string $cat_id
 * @property string $view_num
 * @property string $comment_num
 * @property string $up_num
 * @property integer $is_recommend
 * @property integer $is_digest
 * @property integer $is_pass
 * @property string $created_at
 * @property string $recommended_at
 * @property string $recommended_at
 * @property string $digested_at
 * @property string $header_image
 * @property string $note
 * @property integer $from
 */
class ShareTopic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'share_topics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['content'], 'string'],
            [['from','user_id', 'period_id', 'product_id', 'cat_id', 'view_num', 'comment_num', 'up_num', 'is_recommend', 'is_digest', 'is_pass', 'created_at', 'recommended_at', 'digested_at'], 'integer'],
            [['title'], 'string', 'max' => 80],
            [['header_image'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 256],
            [['title'], 'checkTitle'],
            [['content'], 'checkContent']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '主题',
            'content' => '晒单内容',
            'user_id' => '发布者ID',
            'period_id' => '期数ID',
            'product_id' => '商品ID',
            'cat_id' => '商品类型ID',
            'view_num' => '浏览次数',
            'comment_num' => '回复次数',
            'up_num' => '羡慕次数',
            'is_recommend' => '是否推荐',
            'is_digest' => '是否精华',
            'is_pass' => '是否通过',
            'created_at' => '发布时间',
            'recommended_at' => '推荐时间',
            'digested_at' => '精华时间',
            'point' => '福分',
            'note' => '备注',
        ];
    }

    public function checkTitle()
    {
        $this->title = preg_replace('/\s+/', ' ', trim($this->title));
        $len = strlen($this->title);
        if ($len < 5 || $len > 80) {
            $this->addError("title", "标题必须在5到80字之间");
        }
    }

    public function checkContent()
    {
        $this->content = preg_replace('/\s+/', ' ', trim($this->content));
        $len = strlen($this->content);
        if ($len < 100 || $len > 800) {
            $this->addError("content", "内容必须在100到800字之间");
        }
    }

    /**
     * 根据类型获取晒单列表
     * @param int $t
     * @param int $product_id
     * @param int $cat_id
     * @param int $limit
     * @param int $isPass
     * @param int $user_id
     * @return array
     */
    public static function getListByType($t = 10, $product_id = 0, $cat_id = 0, $limit = 40, $isPass = 1, $user_id = 0, $total = 'all')
    {
        $where = [];
        $isPass == 1 && $where['is_pass'] = $isPass;
        switch ($t) {
            case 10: //最新
                $order = 'created_at DESC';
                break;
            case 20: //精华
                $where['is_digest'] = 1;
                $order = 'digested_at DESC';
                break;
            case 30: //推荐
                $where['is_recommend'] = 1;
                $order = 'recommended_at DESC';
                break;
            case 40: //人气
                $order = 'view_num DESC, created_at DESC';
                break;
        }

        $user_id != 0 && $where['user_id'] = $user_id;
        $product_id != 0 && $where['product_id'] = $product_id;
        $cat_id != 0 && $where['cat_id'] = $cat_id;

        $query = ShareTopic::find()->where($where);
        $countQuery = clone $query;

        if($total == 'all'){
            $totalCount = $countQuery->count();
            $limits = $limit;
        }else{
            if($total == 'zero') $totalCount = 0;
            else $totalCount = $total;
            $num = $totalCount / $limit;
            $curpage = ceil($num);
            if($curpage == Yii::$app->request->get('page', 1) && ($totalCount % $limit != 0)){
                $limits = $totalCount % $limit;
                if($limits < 1){
                    $limits = $totalCount;
                }else{
                    $limits = $limits;
                }
            }else{
                if($totalCount == 0) $limits = 0;
                else $limits = $limit;
            }
        }

        $pagination = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]);
        $list = $query->where($where)->orderBy($order)
                    ->offset($pagination->offset)
                    ->limit($limits)
                    ->asArray()
                    ->all();

        foreach ($list as &$one) {
            $shareTopicImage = ShareTopicImage::findAll(['share_topic_id' => $one['id']]);
            $one['pictures'] = ArrayHelper::getColumn($shareTopicImage, 'basename');
        }

        return ['list' => $list, 'pagination' => $pagination];
    }

    /**
     * 编辑话题
     * @param array $params
     * @return \yii\db\boolean|boolean
     */
    public static function updateTopic($model, $params = array())
    {
        try {
            //isset($params['id']) && $model->id = $params['id'];
            isset($params['title']) && $model->title = $params['title'];
            isset($params['user_id']) && $model->user_id = $params['user_id'];
            isset($params['content']) && $model->content = $params['content'];
            isset($params['view_num']) && $model->view_num = $params['view_num'];
            isset($params['comment_num']) && $model->comment_num = $params['comment_num'];
            isset($params['up_num']) && $model->up_num = $params['up_num'];
            isset($params['is_pass']) && $model->is_pass = $params['is_pass'];
            isset($params['header_image']) && $model->header_image = $params['header_image'];
            isset($params['note']) && $model->note = $params['note'];

            //默认的封面为第一张
            if (!isset($params['header_image']) && isset($params['picture'])) {
                $pictures = explode(',', $params['picture']);
                $header_image = Image::generateName() . '.jpg';
                $model->header_image = $header_image;
                $sourceFilePath = Image::getShareInfoFullPath($pictures[0], 'share');//原图路径
                $sourceFilePath = Yii::$app->sftp->getSFtpPath($sourceFilePath);
                Image::createShareInfoImage($sourceFilePath, $header_image, 'main', []);
            }

            if (isset($params['point']) && $params['is_pass'] == 1 && empty($model->point)) {
                //请先设置图片
                if (!$model->header_image) {
                    return ['msg' => '请先设置主图'];
                }
                if (!$model->roll_image) {
                    return ['msg' => '请先设置滚动图'];
                }

                if (isset($params['is_recommend']) && $params['is_recommend'] == 1 && empty($model->is_recommend)) {
                    $model->is_recommend = $params['is_recommend'];
                    $model->recommended_at = time();
                    if (!$model->recommend_image) {
                        return ['msg' => '请先设置推荐图'];
                    }
                }

                $pictures = ShareTopicImage::findAll(['share_topic_id' => $model->id]);
//                foreach ($pictures as $pic) {
//                    if ($pic['mobile'] == 0) {
//                        return ['msg' => '请设置手机图'];
//                    }
//                }

                // 保存图片
                foreach ($pictures as $pic) {
                    $sourceFilePath = Image::getShareInfoFullPath($pic['basename'], 'share');//原图路径
                    $sourceFilePath = Yii::$app->sftp->getSFtpPath($sourceFilePath);
                    Image::createShareInfoImage($sourceFilePath, $pic['basename'], 'big', [
                            'text' => 'www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com',
                            'angle' => -30,
                            'font_size' => 40,
                            'fill_color' => '#fff',
                            'fill_opacity' => 0.2]
                    );
                    Image::createShareInfoImage($sourceFilePath, $pic['basename'], 'small', [
                            'text' => 'www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com',
                            'angle' => -30,
                            'font_size' => 10,
                            'fill_color' => '#fff',
                            'fill_opacity' => 0.2]
                    );
                }
                $member = new Member(['id' => $model->user_id]);
                // 加福分
                $member->editPoint($params['point'], PointFollowDistribution::POINT_SHARE, "晒单获得福分");
                // 加经验
                $member->editExperience(ExperienceFollowDistribution::NUMBER_SHARE, ExperienceFollowDistribution::EXPR_SHARE, "晒单获得经验");
            }

            if (isset($params['is_digest']) && $params['is_digest'] == 1 && empty($model->is_digest)) {
                $model->is_digest = $params['is_digest'];
                $model->digested_at = time();
            }
            isset($params['point']) && $model->point = $params['point'];

            if (isset($params['period_id'])) {
                $model->period_id = $params['period_id'];
                $periodInfo = \app\models\Period::findOne(['id' => $params['period_id']]);
                if (empty($periodInfo)) {
                    return false;
                }

                $model->product_id = $periodInfo->product_id;
                $model->cat_id = $periodInfo->cat_id;
                $model->user_id = $params['user_id'];
                $model->created_at = time();
            }
            if ( $model->validate()) {
                if (!$model->save()) {
                    return false;
                }
            } else {
                return false;
            }

            $shareTopicId = $model->primaryKey;

            if (isset($params['picture'])) {
                if (!ShareTopicImage::updateImage($shareTopicId, $pictures)) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 增加或者减少某列
     * @param $id
     * @param $columName
     * @param $num
     * @return bool|int
     */
    public static function addColumnByNum($id, $columName, $num)
    {
        $query = ShareTopic::find();
        $shareTopic = $query->where(['id' => $id])->one();

        if (!empty($shareTopic)) {
            return ShareTopic::updateAll([$columName => $shareTopic->$columName + $num], ['id' => $shareTopic->id]);
        }

        return false;
    }

    /**
     * 判断用户对$id晒单是否已羡慕
     * @param int $id
     * @param int $flag  没有羡慕时，当flag=1进行羡慕
     * @return number
     */
    public static function is_up($id, $flag = 0)
    {
        $deviceId = Yii::$app->request->get('deviceId', 0);
        $userid=0;
        if(is_array($id)){
            $userid=$id['userid'];
            $id=$id['id'];
        };
        if ($deviceId === 0) {
            Yii::$app->session->open();
            $key = 'SHARE_TOPIC_' . Yii::$app->session->getId() . '_' . $id;
        } else {
            $key = 'SHARE_TOPIC_' . $deviceId . '_' . $id.'_' .$userid;
        }

        $redis = new MyRedis();

        if ($redis->get($key)) {
            return 1; //已羡慕
        } else {
            if ($flag == 1) {
                $redis->set($key, 1);
            }
            return 0;
        }
    }

    /**
     * 后台晒单管理列表
     * @param $where
     * @param int $page
     * @param int $perpage
     * @return array
     */
    public static function adminShareTopicList($where, $page = 1, $perpage = 10)
    {
        $query = ShareTopic::find()
            ->leftJoin('users u', 'share_topics.user_id=u.id')
            ->leftJoin('products p', 'share_topics.product_id=p.id')
            ->leftJoin('periods d', 'share_topics.period_id=d.id')
            ->select('share_topics.*, u.phone, u.email, p.name, d.period_number');

        if (!empty($where)) {
            if (isset($where['is_pass']) && $where['is_pass'] != -1) {
                $query->andWhere(['share_topics.is_pass' => $where['is_pass']]);
            }
            if (isset($where['is_recommend']) && $where['is_recommend'] != -1) {
                $query->andWhere(['share_topics.is_recommend' => $where['is_recommend']]);
            }
            if (isset($where['start_time']) && $where['start_time'] != '') {
                $query->andWhere(['>=', 'share_topics.created_at', $where['start_time']]);
            }
            if (isset($where['end_time']) && $where['end_time'] != '') {
                $query->andWhere(['<=', 'share_topics.created_at', $where['end_time']]);
            }
            if (isset($where['account']) && $where['account'] != '') {
                $query->andWhere(['or', 'u.phone="' . $where['account'] . '"', 'u.email="' . $where['account'] . '"']);
            }
        }

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' => $perpage, 'pageSizeLimit'=>[1,$perpage]]);
        $list = $query->offset($pagination->offset)->limit($pagination->limit)->orderBy('created_at desc')->asArray()->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    /**
     * 添加晒单
     * @param $params
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function add($params)
    {
        try {
            $trans = Yii::$app->db->beginTransaction();
            $model = new ShareTopic();
            $model->title = $params['title'];
            $model->content = $params['content'];
            $model->user_id = $params['user_id'];
            $model->period_id = $params['period_id'];
            $model->from = $params['from'];

            $periodInfo = \app\models\Period::findOne(['id' => $params['period_id']]);
            if (empty($periodInfo)) {
                return false;
            }

            $model->product_id = $periodInfo['product_id'];
            $model->cat_id = $periodInfo['cat_id'];
            $model->created_at = time();

            if (isset($params['picture'])) {
                $pictures = explode(',', $params['picture']);
                $model->header_image = $pictures[0];
            }

            if (!$model->validate()) {
                $trans->rollBack();
                return ['code' => 101, 'msg' => current($model->firstErrors)];
            }

            if (!$model->save()) {
                $trans->rollBack();
                return ['code' => 101, 'msg' => current($model->firstErrors)];
            }

            $shareTopicId = $model->primaryKey;

            if (!ShareTopicImage::updateImage($shareTopicId, $pictures)) {
                $trans->rollBack();
                return false;
            }

            $sourceFilePath = Image::getShareInfoFullPath($pictures[0], 'share');//原图路径
            $sourceFilePath = Yii::$app->sftp->getSFtpPath($sourceFilePath);
            Image::createShareInfoImage($sourceFilePath, $pictures[0], 'main', []);

            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * 编辑晒单
     * @param $id
     * @param $params
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function edit($id, $params)
    {
        $model = ShareTopic::findOne($id);

        if (!$model) {
            return false;
        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->title = $params['title'];
            $model->content = $params['content'];
            $model->is_pass = 0;
            if (isset($params['picture'])) {
                $pictures = explode(',', $params['picture']);
                $model->header_image = $pictures[0];
            }

            if (!$model->validate()) {
                $trans->rollBack();
                return ['code' => 101, 'msg' => $model->firstErrors];
            }

            if (!$model->save()) {
                $trans->rollBack();
                return ['code' => 101, 'msg' => $model->firstErrors];
            }

            if (!ShareTopicImage::updateImage($id, $pictures)) {
                $trans->rollBack();
                return false;
            }

            $sourceFilePath = Image::getShareInfoFullPath($pictures[0], 'share');//原图路径
            $sourceFilePath = Yii::$app->sftp->getSFtpPath($sourceFilePath);
            Image::createShareInfoImage($sourceFilePath, $pictures[0], 'main', []);

            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * 审核晒单
     * @param $id
     * @param $params
     * @return bool
     */
    public static function check($id, $params)
    {
        $model = ShareTopic::findOne($id);

        if (!$model || $model->is_pass == 1) {
            return false;
        }

        if ($params['is_pass'] == 1) { //审核通过
            //请先设置图片
            if (!$model->header_image) {
                return ['msg' => '请先设置主图'];
            }

            if (!$model->roll_image) {
                return ['msg' => '请先设置滚动图'];
            }

            if (isset($params['is_recommend']) && $params['is_recommend'] == 1) {
                $model->is_recommend = $params['is_recommend'];
                $model->recommended_at = time();
                if (!$model->recommend_image) {
                    return ['msg' => '请先设置推荐图'];
                }
            }

            if (isset($params['is_digest']) && $params['is_digest'] == 1) {
                $model->is_digest = $params['is_digest'];
                $model->digested_at = time();
            }

            $pictures = ShareTopicImage::findAll(['share_topic_id' => $id]);

            // 保存图片
            foreach ($pictures as $pic) {
                $sourceFilePath = Image::getShareInfoFullPath($pic['basename'], 'share');//原图路径
                $sourceFilePath = Yii::$app->sftp->getSFtpPath($sourceFilePath);
                Image::createShareInfoImage($sourceFilePath, $pic['basename'], 'big', [
                        'text' => 'www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com',
                        'angle' => -30,
                        'font_size' => 40,
                        'fill_color' => '#fff',
                        'fill_opacity' => 0.2]
                );
                Image::createShareInfoImage($sourceFilePath, $pic['basename'], 'small', [
                        'text' => 'www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com    www.huogou.com',
                        'angle' => -30,
                        'font_size' => 10,
                        'fill_color' => '#fff',
                        'fill_opacity' => 0.2]
                );
            }

            $model->point = $params['point'];
            $model->is_pass = 1;
        } elseif ($params['is_pass'] == 2) {
            $model->is_pass = 2;
            $model->note = $params['note'];
        }

        $model->admin_id = Yii::$app->admin->id;
        $model->checked_at = time();

        if (!$model->validate()) {
            return false;
        }

        if (!$model->save()) {
            return false;
        }

        if ($params['is_pass'] == 1) {
            $member = new Member(['id' => $model->user_id]);
            // 加福分
            $member->editPoint($params['point'], PointFollowDistribution::POINT_SHARE, "晒单获得福分");
            // 加经验
            $member->editExperience(ExperienceFollowDistribution::NUMBER_SHARE, ExperienceFollowDistribution::EXPR_SHARE, "晒单获得经验");
        }

        return true;
    }
}
