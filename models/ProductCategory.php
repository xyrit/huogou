<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "product_category".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $top_id
 * @property integer $level
 * @property string $name
 * @property integer $type_id
 * @property string $product_num
 * @property integer $list_order
 * @property integer $updated_at
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'top_id', 'list_order', 'level', 'name', 'updated_at'], 'required'],
            [['parent_id', 'level', 'list_order', 'updated_at'], 'integer'],
            [['top_id', 'product_num'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => '上级分类',
            'top_id' => 'Top ID',
            'level' => 'Level',
            'name' => '分类名称',
            'type_id' => '商品类型',
            'product_num' => 'Product Num',
            'list_order' => '排序',
            'updated_at' => 'Updated At',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            return true;
        } else {
            return false;
        }
    }


    public static function getLevelByPid($pid)
    {
        if ($pid) {
            $parent = static::findOne($pid);
            $level = $parent->level + 1;
        } else {
            $level = 1;
        }
        return $level;
    }

    public static function getTopIdByPid($pid)
    {
        $topId = static::topId($pid);
        $topId = $topId ? $topId : $pid;
        return $topId;
    }

    private static $_allIndexId;

    public static function allIndexId()
    {
        if (!empty(static::$_allIndexId)) {
            return static::$_allIndexId;
        }
        $all = static::all();
        static::$_allIndexId = ArrayHelper::index($all, function ($element) {
            return $element['id'];
        });
        return static::$_allIndexId;
    }

    private static $_allMap;

    public static function allMap()
    {
        if (!empty(static::$_allMap)) {
            return static::$_allMap;
        }
        $all = static::all();
        $result = ArrayHelper::map($all, 'id', 'name', 'parent_id');
        static::$_allMap = $result;
        return $result;
    }

    private static $_all;

    public static function all()
    {
        if (!empty(static::$_all)) {
            return static::$_all;
        }
        $allModel = static::find()->all();
        static::$_all = ArrayHelper::toArray($allModel);
        return static::$_all;
    }

    public static function topId($id)
    {
        $category = static::findOne($id);
        if (!$category) {
            return 0;
        }
        return $category->top_id;
    }

    public static function top($id)
    {
        $topId = static::topId($id);
        $category = static::findOne($topId);
        return ArrayHelper::toArray($category);
    }

    public static function pid($id)
    {
        $category = static::findOne($id);
        return $category->parent_id;
    }

    public static function parent($id)
    {
        $allIndexId = static::allIndexId();

        if (empty($allIndexId[$id])) {
            return [];
        }
        $pid = $allIndexId[$id]['parent_id'];
        if (empty($allIndexId[$pid])) {
            return [];
        }
        $parent = $allIndexId[$pid];
        return $parent;
    }

    public static function parents($id)
    {
        $parents = [];
        static::parentList($parents, $id);
        return array_reverse($parents);
    }

    private static function parentList(&$list, $id)
    {
        $parent = static::parent($id);
        if ($parent) {
            $list[] = $parent;
            static::parentList($list, $parent['id']);
        }
    }

    public static function children($id)
    {
        $all = static::all();
        $children = [];
        $result = static::allMap();
        if (empty($result[$id])) {
            return [];
        }
        $ids = array_keys($result[$id]);
        foreach ($all as $one) {
            if (in_array($one['id'], $ids)) {
                $children[] = $one;
            }
        }
        ArrayHelper::multisort($children, ['list_order', 'id'], [SORT_ASC, SORT_ASC]);
        return $children;
    }

    public static function firstLevel()
    {
        return static::children(0);
    }

    public static $allOrderList;

    /**
     * @param int $id
     * @return mixed
     */
    public static function allOrderList($id = 0)
    {
        static::allList($id);
        return static::$allOrderList;
    }

    private static function allList($id = 0)
    {
        $allList = static::children($id);
        static::childrenList($allList);
        return $allList;
    }

    private static function childrenList(&$one)
    {
        if (!$one) {
            return;
        }
        foreach ($one as &$value) {
            static::$allOrderList[] = $value;
            $children = static::children($value['id']);
            $value['children'] = $children;
            static::childrenList($value['children']);
        }
    }

    public static function cateName($id, $char = '-')
    {
        $cateName = '';
        $allCateIndexId = static::allIndexId();
        if (empty($allCateIndexId[$id])) {
            return '无';
        }
        $parents = static::parents($id);
        $cate = $allCateIndexId[$id];
        foreach ($parents as $parent) {
            $cateName .= $parent['name'] . $char;
        }
        $cateName .= $cate['name'];
        return $cateName;
    }

    public static function getLine($level)
    {
        if ($level > 1) {
            return str_repeat('--', $level - 1);
        } else {
            return '';
        }
    }

    public static function getTrim($level)
    {
        if ($level > 1) {
            return str_repeat('&nbsp;&nbsp;&nbsp;', $level - 1);
        } else {
            return '';
        }
    }

    public static function getInfoById($id){
        return static::find()->where(['id'=>$id])->asArray()->one();
    }
}
