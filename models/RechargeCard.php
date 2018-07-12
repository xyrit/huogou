<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Admin;
/**
 * This is the model class for table "recharge_card".
 *
 * @property integer $card_id
 * @property integer $user_apply
 * @property integer $user_check
 * @property integer $user_check_note
 * @property integer $is_out
 * @property string $time_start
 * @property string $time_end
 * @property string $time_check
 * @property integer $status
 */
class RechargeCard extends \yii\db\ActiveRecord
{
    public $money =[];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'recharge_card';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_apply'], 'required'],
            [['user_apply', 'user_check', 'is_out', 'status'], 'integer'],
            [['time_start', 'time_end', 'time_check', 'user_check_note'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'card_id' => 'ID',
            'user_apply' => '申请人',
            'user_check' => '审核人',
            'user_check_note' => '审核备注',
            'is_out' => '是否被导出过',
            'time_start' => '有效期开始时间',
            'time_end' => '有效期结束时间',
            'time_check' => '审核时间',
            'status' => '0提交待审，1审核通过，2审核不通过',
        ];
    }
    
    function getUseinfo()
    {
        return "";
    }
      /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['card_id' => 'card_id']);
    }
    
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplyUser()
    {
        return $this->hasOne(Admin::className(), [ 'id'=>'user_apply']);
    }
    
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getCheckUser()
    {
        return $this->hasOne(Admin::className(), [ 'id'=>'user_check']);
    }
    
    /**
     * 搜索
     * @param type $params
     * @return \app\models\ActiveDataProvider
     */
     public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
           'card_id' => $this->card_id,
            'user_apply' => $this->user_apply,
            'user_check' => $this->user_check,
            'user_check_note' => $this->user_check_note,
            'is_out' => $this->is_out,
            'time_start' => $this->time_start,
            'time_end' => $this->time_end,
            'time_check' => $this->time_check,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'password', $this->password]);

        return $dataProvider;
    }
}
