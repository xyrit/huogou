<?php

namespace app\models;

use app\helpers\Message;
use app\services\Coupon;
use app\services\Member;
use Yii;

/**
 * This is the model class for table "act_lottery_reward".
 *
 * @property string $id
 * @property string $rand
 * @property integer $lottery_id
 * @property string $name
 * @property string $content
 * @property integer $num
 * @property double $probability
 * @property string $basename
 * @property string $created_at
 */
class Reward extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_lottery_reward';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'content', 'probability', 'created_at'], 'required'],
            [['lottery_id', 'num', 'created_at', 'left'], 'integer'],
            [['probability'], 'number'],
            [['rand'], 'string', 'max' => 50],
            [['name', 'content', 'basename'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rand' => 'Rand',
            'lottery_id' => 'Lottery ID',
            'name' => 'Name',
            'content' => 'Content',
            'num' => 'Num',
            'probability' => 'Probability',
            'basename' => 'Basename',
            'created_at' => 'Created At',
        ];
    }

    public static function lotteryRaffle($lotteryId)
    {
        $list = Reward::find()->where(['lottery_id'=>$lotteryId, 'del'=>0])->limit(8)->orderBy('id desc')->asArray()->all();
        $prizeData = [];
        foreach($list as $key => $val){
                $prizeData[$key]['id'] = $key + 1;
                $prizeData[$key]['prize'] = $val['name'];
                $prizeData[$key]['v'] = $val['probability'] * 10000;
                $prizeData[$key]['number'] = $val['id'];
        }

        $arr = [];
        foreach ($prizeData as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $rid = Lottery::getRand($arr); //根据概率获取奖项id
        $res['yes'] = $prizeData[$rid-1]['prize']; //中奖项
        return ['id'=>$prizeData[$rid-1]['id'], 'number'=>$prizeData[$rid-1]['number']];
    }

    //json转化
    public static function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = static::object_array($value);
            }
        }
        return $array;
    }

    // type 1红包 2实物 3谢谢 4伙购币 5福分
    public static function rewardType($rewarId, $uid, $source = 4)
    {
        $reward = Reward::findOne($rewarId);
        $arr = json_decode($reward['content']);
        foreach($arr as $key => $val){
            if($reward['type'] == 1){
                $coup = Coupon::receivePacket($val, $uid, 'activity_'.$reward['lottery_id']);
                if(isset($coup['data']['pid'])){
                    return Coupon::openPacket($coup['data']['pid'], $uid);
                }else{
                    return $coup;
                }
            }elseif($reward['type'] == 2){
                ActOrder::add($uid, ActOrder::TYPE_LOTTERY, $rewarId, $val, $reward['basename']);
            }elseif($reward['type'] == 4){
                /*$user = User::findOne($uid);
                $user->money = $user['money'] + $val;
                $user->save();*/
                $member = new Member(['id'=>$uid]);
                $member->editMoney($val, 3, '抽奖伙购币', $source);
            }elseif($reward['type'] == 5){
                /*$user = User::findOne($uid);
                $user->point = $user['point'] + $val;
                $user->save();*/
                $member = new Member(['id'=>$uid]);
                $member->editPoint($val, 10, '抽奖福分');
            }
        }
    }
}
