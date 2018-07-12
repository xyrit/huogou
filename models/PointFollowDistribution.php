<?php

namespace app\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "point_follow_100".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $current_point
 * @property integer $point
 * @property integer $type
 */
class PointFollowDistribution extends \yii\db\ActiveRecord
{
    //1=伙购消费，2=成功邀请好友并消费，3=成功晒单，4=晒单评论, 5=完善资料
    const POINT_BUY = 1;
    const POINT_FRIEND = 2;
    const POINT_SHARE = 3;
    const POINT_SHARE_COMMENT = 4;
    const POINT_PROFILE = 5;
    const POINT_MODIFY_PROFILE = 6;
    const POINT_TASK = 7; //任务
    const POINT_SIGN = 8; //签到
    const POINT_COUPON = 9;
    const POINT_LOTTERY = 10; //抽奖
    const POINT_FREE_BUY = 11; //兑换0元购码

    const NUMBER_BUY = 1;
    const NUMBER_FRIEND = 50;
    const NUMBER_SHARE_COMMENT = 1;
    const NUMBER_FREE_BUY = 30;


    private static $_userHomeId;

    public static function instantiate($row)
    {
        return new static(static::$_userHomeId);
    }

    public function __construct($userHomeId, $config = [])
    {
        parent::__construct($config);
        static::$_userHomeId = $userHomeId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        $tableId = substr(static::$_userHomeId, 0, 3);
        return 'point_follow_' . $tableId;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'current_point', 'point', 'type'], 'required'],
            [['user_id', 'current_point', 'point', 'type'], 'integer']
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
            'current_point' => 'Current Point',
            'point' => 'Point',
            'type' => 'Type',
        ];
    }

    /**
     * @param $userHomeId
     * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function findByUserHomeId($userHomeId) {
        $model = new static($userHomeId);
        return $model::find();
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord|null ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOneByUserHomeId($userHomeId, $condition)
    {
        $model = new static($userHomeId);
        return $model::findOne($condition);
    }

    /**
     * @param $userHomeId
     * @param $condition
     * @return \yii\db\ActiveRecord[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAllByUserHomeId($userHomeId, $condition)
    {
        $model = new static($userHomeId);
        return $model::findAll($condition);
    }

    public static function pointList($where = [], $page = 1, $perpage = 10)
    {
        $connection = \Yii::$app->db;
        $items_sql = '';

        for ($i = 100; $i <= 109; $i++) {
            $items_sql .= '(SELECT * FROM point_follow_' . $i . ' ) union all';
        }
        $items_sql = rtrim($items_sql, 'union all');
        $condition = ' where 1=1 ';
        if (empty($where)) {
            $condition = '';
        } else {
            if(isset($where['type']) && $where['type'] != 0){
                if($where['type'] == 1){
                    $condition .= ' and r.type = '.$where['type'] .' and r.point < 0';
                }elseif($where['type'] == 6){
                    $condition .= ' and r.type = 1 and r.point > 0';
                }else{
                    $condition .= ' and r.type = '.$where['type'].'';
                }

            }
            if(isset($where['username']) && $where['username'] != '' ) $condition .= ' and r.user_id = '.$where['username'];
            if (isset($where['start']) && !empty($where['start']) && isset($where['end']) && !empty($where['end'])) {
                $condition .= ' and r.created_at BETWEEN ' . strtotime($where['start']) . ' AND ' . strtotime($where['end']);
            }
        }

        $countSql = 'SELECT count(*) as total FROM (' . $items_sql . ') as r ' . $condition;

        $c = $connection->createCommand($countSql);
        $totalCount = $c->queryScalar();
        $pagination = new Pagination(['totalCount' => $totalCount, 'page'=>$page -1, 'defaultPageSize'=>$perpage,'pageSizeLimit'=>[1,$perpage]]);

        $querySql = 'SELECT r.* FROM (' . $items_sql . ') as r ' . $condition . ' ORDER BY r.created_at DESC limit ' . $pagination->offset . ',' . $pagination->limit;

        $command = $connection->createCommand($querySql);
        $result = $command->queryAll();

        $sumQuery = $connection->createCommand('SELECT sum(r.point) as total FROM (' . $items_sql . ') as r ' . $condition);
        $sum = $sumQuery->queryOne();

        return ['list'=>$result, 'pagination'=>$pagination, 'total'=>$sum['total']];
    }

    public static function getType($type)
    {
        switch ($type) {
            case '1':
                return array('name'=>'伙购消费');
                break;
            case '2':
                return array('name'=>'成功邀请好友并消费');
                break;
            case '3':
                return array('name'=>'成功晒单');
                break;
            case '4':
                return array('name'=>'晒单评论');
                break;
            case '5':
                return array('name'=>'完善资料');
                break;
            case '6':
                return array('name'=>'后台操作');
                break;
            default:
                return array('name'=>'伙购消费');
                break;
        }
    }
}
