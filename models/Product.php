<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property integer $id
 * @property string $bn
 * @property string $barcode
 * @property string $name
 * @property integer $price
 * @property integer $marketable
 * @property integer $cost
 * @property integer $face_value
 * @property integer $cat_id
 * @property integer $brand_id
 * @property integer $delivery_id
 * @property integer $order_manage_gid
 * @property integer $store
 * @property integer $limit_num
 * @property integer $buy_unit
 * @property integer $allow_share
 * @property integer $is_recommend
 * @property integer $list_order
 * @property string $brief
 * @property string $intro
 * @property string $picture
 * @property integer $created_at
 * @property integer $updated_at
 */
class Product extends \yii\db\ActiveRecord
{

    public static $deliveries = [
        1 => '第三方平台',
        2 => '自建仓发货',
        3 => '话费卡密',
        4 => '供应商代发',
        5 => '兑吧支付宝',
        6 => '兑吧Q币',
        7 => '兑吧话费',
        8 => '京东卡密',
        9 => 'Q币直充',
        10 => '话费直充',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'price', 'cost', 'cat_id', 'brand_id', 'delivery_id',  'order_manage_gid','created_at', 'updated_at'], 'required'],
            [['price', 'cost', 'face_value', 'marketable', 'cat_id', 'brand_id', 'delivery_id', 'order_manage_gid', 'store', 'limit_num','buy_unit', 'app', 'is_recommend', 'list_order', 'allow_share', 'created_at', 'updated_at','live_time'], 'integer'],
            [['intro'], 'string'],
            [['bn', 'barcode', 'name', 'brief', 'picture', 'tag', 'keywords'], 'string', 'max' => 255],
            [['order_manage_gid'], 'validateOrderManageGid'],
            [['limit_num'], 'validateLimitNum'],
            [['price'], 'validatePrice'],
            [['buy_unit'], 'validateBuyUnit'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bn' => '商品编号',
            'barcode' => '商品条码',
            'name' => '商品名称',
            'price' => '伙购价',
            'face_value' => '面值',
            'marketable' => '是否上架',
            'cost' => '成本价',
            'cat_id' => '分类',
            'brand_id' => '品牌',
            'delivery_id' => '发货方式',
            'order_manage_gid' => '订单处理小组',
            'store' => '伙购期数',
            'limit_num' => '限购数量',
            'buy_unit' => '十元专区',
            'allow_share' => '允许晒单',
            'is_recommend' => '是否推荐',
            'list_order' => '排序',
            'brief' => '商品简介',
            'intro' => '详细介绍',
            'picture' => '商品相册',
            'tag' => '标签',
            'keywords' => '关键字',
             'live_time'=>"使用期限",
            'app' => '只限app'
        ];
    }

    public function validateOrderManageGid($attribute, $params)
    {
        if ($this->delivery_id == 1 || $this->delivery_id == 2) {
            if (!$this->order_manage_gid) {
                $this->addError($attribute, '请选择订单处理小组');
            }
        }
    }

    public function validateLimitNum($attribute, $params)
    {
        if ($this->limit_num >= $this->price) {
            $this->addError($attribute, '限购次数只能小于价格');
        }
        if ($this->buy_unit==10 && $this->limit_num>0) {
            $this->addError($attribute, '十元专区不能限购');
        }
    }

    public function validatePrice($attribute, $params)
    {
        if ($this->buy_unit==10 && $this->price%10>0) {
            $this->addError($attribute, '十元专区的价格必须是10的倍数');
        }
    }

    public function validateBuyUnit($attribute, $params)
    {
        if (!in_array($this->buy_unit,[1,10])) {
            $this->addError($attribute, '购买单位只能是1或10');
        }
    }

}
