<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
/**
 * This is the model class for table "rich".
 *
 * @property string $id
 * @property string $name
 * @property integer $status
 * @property string $start_time
 * @property string $end_time
 * @property integer $time_type
 * @property string $comment
 * @property string $created_at
 * @property string $last_modify
 */
class RichSet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rich_set';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status', 'start_time', 'end_time', 'time_type', 'created_at', 'last_modify'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['comment'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'time_type' => 'Time Type',
            'comment' => 'Comment',
            'created_at' => 'Created At',
            'last_modify' => 'Last Modify',
        ];
    }

    public static function getList($page, $pageSize)
    {
        $query = RichSet::find();
        $pages = new Pagination(['defaultPageSize' => $pageSize, 'totalCount' => $query->count(), 'page' => $page - 1]);

        $roles = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        return ['rows' => $roles, 'total' => $pages->totalCount];
    }
}
