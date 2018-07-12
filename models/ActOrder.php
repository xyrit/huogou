<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "act_orders".
 *
 * @property integer $id
 * @property string $name
 * @property string $picture
 * @property integer $act_type
 * @property integer $act_obj_id
 * @property integer $user_id
 * @property integer $status
 * @property integer $confirm
 * @property string $ship_name
 * @property string $ship_zip
 * @property string $ship_area
 * @property string $ship_addr
 * @property string $ship_mobile
 * @property string $ship_email
 * @property string $ship_time
 * @property string $mark_text
 * @property string $remark
 * @property integer $create_time
 * @property integer $confirm_addr_time
 * @property integer $confirm_goods_time
 * @property integer $last_modified
 */
class ActOrder extends \yii\db\ActiveRecord
{
    const TYPE_LOTTERY = 1;//抽奖
    const TYPE_FREE = 2;//0元购
    const TYPE_MONTH_RICH = 3;//月土豪
    const TYPE_WEEK_RICH = 4;//周土豪
    const TYPE_SEASON_RICH = 5;//季土豪


    const STATUS_INIT = 0;
    const STATUS_COMMIT_ADDRESS = 1;
    const STATUS_COMFIRM_ADDRESS = 2;
    const STATUS_PREPARE_GOODS = 3;
    const STATUS_SHIPPING = 4;
    const STATUS_DONE = 8;
    const STATUS_OVERDUE = 9;

    public static $type_name = [
        1 => '幸运大转盘',
        2 => '0元伙购',
        3 => '月度土豪榜',
        4 => '周土豪榜',
        5 => '季土豪榜',
    ];

    public static $status_name = [
        0 => '已中奖',
        1 => '待确认',
        2 => '待备货',
        3 => '待发货',
        4 => '待收货',
        8 => '已完成',
        9 => '已过期',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'picture', 'user_id', 'create_time', 'last_modified'], 'required'],
            [['act_type', 'act_obj_id', 'user_id', 'status', 'create_time', 'confirm_addr_time', 'confirm_goods_time', 'last_modified'], 'integer'],
            [['mark_text', 'remark'], 'string'],
            [['name', 'picture', 'ship_area', 'ship_addr'], 'string', 'max' => 255],
            [['ship_name', 'ship_mobile', 'ship_time'], 'string', 'max' => 25],
            [['ship_zip'], 'string', 'max' => 20],
            [['ship_email'], 'string', 'max' => 200]
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
            'picture' => 'Picture',
            'act_type' => 'Act Type',
            'act_obj_id' => 'Act Obj ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'ship_name' => 'Ship Name',
            'ship_zip' => 'Ship Zip',
            'ship_area' => 'Ship Area',
            'ship_addr' => 'Ship Addr',
            'ship_mobile' => 'Ship Mobile',
            'ship_email' => 'Ship Email',
            'ship_time' => 'Ship Time',
            'mark_text' => 'Mark Text',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
            'confirm_addr_time' => 'Confirm Addr Time',
            'confirm_goods_time' => 'Confirm Goods Time',
            'last_modified' => 'Last Modified',
        ];
    }

    /** 新增活动订单
     * @param $uid
     * @param $actType
     * @param $actObjId
     * @param $goodsName
     * @param $goodsPicture
     * @return bool
     */
    public static function add($uid, $actType, $actObjId, $goodsName, $goodsPicture)
    {
        $time = time();
        $actOrder = new ActOrder();
        $actOrder->act_type = $actType;
        $actOrder->act_obj_id = $actObjId;
        $actOrder->name = $goodsName;
        $actOrder->picture = $goodsPicture;
        $actOrder->user_id = $uid;
        $actOrder->create_time = $time;
        $actOrder->last_modified = $time;
        $saveOrder = $actOrder->save(false);

        return $saveOrder;
    }
}
