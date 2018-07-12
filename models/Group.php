<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

/**
 * This is the model class for table "groups".
 *
 * @property string $id
 * @property string $name
 * @property string $intro
 * @property string $adminuser
 * @property string $user_count
 * @property string $topic_count
 * @property string $digest_count
 * @property string $notice
 * @property string $picture
 * @property integer $group_closed
 * @property integer $topic_closed
 * @property integer $comment_closed
 * @property integer $verify_status
 * @property string $created_at
 */
class Group extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'groups';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'adminuser'], 'required'],
            [['user_count', 'topic_count', 'digest_count', 'group_closed', 'topic_closed', 'comment_closed', 'verify_status', 'created_at'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['intro', 'notice', 'picture'], 'string', 'max' => 255],
            [['adminuser'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '圈子名称',
            'adminuser' => '圈主',
            'group_closed' => '申请加入圈子',
            'topic_closed' => '发帖权限',
            'comment_closed' => '帖子是否可回复',
            'picture' => '圈子头像',
            'intro' => '圈子介绍',
            'notice' => '圈子公告',
            'group_closed' => '关闭圈子',
            'verify_status' => '帖子回复审核',
            'comment_closed' => '关闭评论',
        ];
    }

    public static function groupList()
    {
        $query = Group::find();
        $countQuery = clone $query;
        $page = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => '10']);
        $list = $query->offset($page->offset)
            ->limit($page->limit)
            ->all();

        $pages = LinkPager::widget([
            'pagination' => $page,
            'options' => ['class' => 'am-pagination'],
            'activePageCssClass' => 'am-active',
            'disabledPageCssClass' => 'am-disabled',
        ]);

        return ['list' => $list, 'pages' => $pages];
    }

    public static function getGroup()
    {
        $groups = Group::find()->all();
        return ArrayHelper::map($groups, 'id', 'name');
    }

    //圈子话题数
    public static function groupTopciCount($groupId, $status = 1)
    {
        $group = Group::findOne($groupId);
        if($status == 1){
            $group->topic_count = $group['topic_count'] + 1;
        }else{
            $group->topic_count = $group['topic_count'] - 1;
        }

        return $group->save();
    }
}
