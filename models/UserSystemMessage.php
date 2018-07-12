<?php

namespace app\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "user_system_messages".
 *
 * @property string $id
 * @property string $to_userid
 * @property string $message
 * @property string $created_at
 */
class UserSystemMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_system_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_userid', 'created_at'], 'required'],
            [['to_userid', 'created_at'], 'integer'],
            [['message'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_userid' => 'To Userid',
            'message' => 'Message',
            'created_at' => 'Created At',
        ];
    }

    //删除系统消息
    public static function delSystenMessage($id, $uid, $all)
    {
        if($id){
            $model = UserSystemMessage::findOne($id);
            $model->delete();
        }elseif($all){
            UserSystemMessage::deleteAll(['to_userid'=>$uid]);
        }

        return 1;
    }

    /**
     * 后台系统消息
     */
    public static function systemMsg($condition = '')
    {
        $where = [];
        $query = UserSystemMessage::find()->where($where);
        if ($condition['starttime']) {
            $query->andWhere(['>', 'created_at', $condition['starttime']]);
        }
        if($condition['to_userid']){
            $user = User::find()->where(['or', 'email="'.$condition['to_userid'].'"', 'phone="'.$condition['to_userid'].'"'])->one();
            $query->andWhere(['to_userid'=>$user['id']]);
        }
        if ($condition['endtime']) {
            $query->andWhere(['<', 'created_at', $condition['endtime']]);
        }

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' =>25 ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id desc')
            ->all();
        return ['list'=>$list, 'pagination'=>$pagination];
    }
}
