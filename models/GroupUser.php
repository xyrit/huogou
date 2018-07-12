<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "groups_users".
 *
 * @property string $id
 * @property string $group_id
 * @property string $user_id
 * @property string $username
 * @property string $created_at
 */
class GroupUser extends \yii\db\ActiveRecord
{
    public $picture;
    public $name;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'created_at', 'user_id'], 'required'],
            [['group_id', 'user_id', 'created_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'user_id' => 'User ID',
            'username' => 'Username',
            'created_at' => 'Create At',
        ];
    }

    public static function newJoinGroup($groupId, $limit = 12)
    {
        $where = ['group_id'=>$groupId];
        return GroupUser::find()->where($where)->orderBy('id desc')->limit($limit)->all();
    }

    public static function isJoin($user_id, $groupId)
    {
        return GroupUser::find()->where(['user_id'=>$user_id, 'group_id'=>$groupId])->one();
    }

    //用户加入的圈子
    public static function joinGroup($uid)
    {
        $join['num'] = GroupUser::find()->where(['user_id'=>$uid])->count();
        $join['groups'] = GroupUser::findAll(['user_id'=>$uid]);
        return $join;
    }
}
