<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\helpers\Json;

/**
 * This is the model class for table "act_rich_log".
 *
 * @property string $id
 * @property string $user_id
 * @property string $money
 * @property integer $type
 * @property string $created_at
 */
class ActRichLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_rich_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'money', 'type', 'created_at'], 'integer'],
            [['created_at'], 'required']
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
            'money' => 'Money',
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }

    public static function addLog($user_id, $money, $type, $rank, $time)
    {
        $model = new ActRichLog();
        $model->user_id = $user_id;
        $model->money = $money;
        $model->type = $type;
        $model->rank = $rank;
        $model->datetime = $time;
        $model->created_at = time();
        if($model->save()) return $model->primaryKey;
        else return 0;
    }

    public static function pastList($condition, $page = 1, $limit = 10)
    {
        $query = ActRichLog::find()->where(['type'=>$condition, 'rank'=>1])->orderBy('id desc');
        $total = $query->count(1);
        $pagination = new Pagination(['totalCount' => $total, 'page' => $page - 1, 'defaultPageSize' =>$limit ]);
        $list = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
        return ['list'=>$list, 'totalCount'=>$total, 'totalPage'=>$pagination->getPageCount()];
    }

    public static function userReward($userId, $type, $page = 1, $limit = 10)
    {
        $query = ActRichLog::find()->where(['user_id'=>$userId, 'type'=>$type]);
        $count = $query->count(1);
        $pagination = new Pagination(['totalCount' => $count, 'page'=>$page - 1, 'defaultPageSize' =>$limit ]);
        $list = $query->orderBy('id desc')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
        return ['list'=>$list, 'totalCount'=>$count, 'totalPage'=>$pagination->getPageCount()];
    }

    /**
     * 土豪榜奖品
     **/
    public static function rewards($type)
    {
        $model = Config::find()->where('`key` = "'.$type.'"')->one();
        if (!$model) {
            return [];
        }
        $content = Json::decode($model['value']);
        $rewards = [];
        foreach($content as $object){
            $rewards[$object['rank']]['type'] = $object['type'];
            $rewards[$object['rank']]['name'] = $object['name'];
            $rewards[$object['rank']]['picture'] = $object['picture'];
        }
        return $rewards;
    }
}
