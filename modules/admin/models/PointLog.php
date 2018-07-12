<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "point_log".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $before_point
 * @property string $point
 * @property string $final_point
 * @property string $reason
 * @property string $order
 * @property string $admin_id
 * @property string $created_at
 */
class PointLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'point_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'before_point', 'point', 'final_point', 'reason', 'order', 'admin_id', 'created_at'], 'required'],
            [['user_id', 'type', 'before_point', 'point', 'final_point', 'admin_id', 'created_at'], 'integer'],
            [['reason', 'order'], 'string', 'max' => 255],
            [['admin_id', 'created_at'], 'unique', 'targetAttribute' => ['admin_id', 'created_at'], 'message' => 'The combination of Admin ID and Created At has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户名',
            'type' => '操作',
            'before_point' => '调整前福分',
            'point' => '操作福分',
            'final_point' => '调整后福分',
            'reason' => '调整原因',
            'order' => '原始单号',
            'admin_id' => 'Admin ID',
            'created_at' => 'Created At',
        ];
    }

    public static function point($condition)
    {
        $conn = Yii::$app->db;
        $items_sql = '';
        for( $i=100;$i<=109;$i++){
            if($i == 109){
                $items_sql .= '(SELECT sum(point) as comsue FROM point_follow_'.$i.' where '.$condition.' ) ';
            }else{
                $items_sql .= '(SELECT sum(point) as comsue FROM point_follow_'.$i.' where '.$condition.' ) union all';
            }
        }
        $comsuesql = $conn->createCommand('select sum(a.comsue) as comTotal from ('.$items_sql.') as a');
        $totalComsue = $comsuesql->queryOne();
        return $totalComsue['comTotal'];
    }
}
